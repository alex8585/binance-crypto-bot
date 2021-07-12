<?php

namespace App\Console\Commands\Includes;

use \Carbon\Carbon as Carbon;
use App\Balance as Balance;
use Illuminate\Support\Facades\Log;
use App\Option as Option;
use App\Order as Order;
use App\BalanceHistory as BalanceHistory;
use App\Symbol as Symbol;
use Illuminate\Support\Facades\Cache;
use App\Console\Commands\Includes\BinanceApi;
use App\Console\Commands\Includes\BinanceRequest;

trait BotUtils
{
    public $options = [];



    public function initOpenOrders()
    {
        Cache::forget('open_orders');
    }

    public function setCacheTicker($ticker)
    {
        Cache::put('ticker', $ticker);
        return $ticker;
    }

    public function getCacheTicker()
    {
        return Cache::remember('ticker', 180, function () {

            if (!isset($this->binRequest)) {
                $this->binRequest = new BinanceRequest();
            }

            if (!isset($this->binApi)) {
                $this->binApi =  new BinanceApi();
            }

            $rawTicker = $this->binApi->prices();
            $this->symbols = Symbol::select(['symbol', 'min_lot_size', 'data'])->get()->toArray();
            $this->ticker = $this->binRequest->filterTickerSymbols($rawTicker, $this->symbols);
            return $this->ticker;
        });
    }

    public function getOpenOrders()
    {
        //dump(['getOpenOrders']);
        return Cache::remember('open_orders', 180, function () {
            //dump(['db_get']);
            return Order::where('status', 'trade')->get();
        });
    }

    public function updateOpenOrders($openOrders)
    {
        dump(['updateOpenOrders']);
        Cache::put('open_orders', $openOrders);
        return $openOrders;
    }

    public function initAveragedOrders()
    {
        Cache::forget('averaged_orders');
    }

    public function getAveragedOrders()
    {
        return Cache::remember('averaged_orders', 180, function () {
            return Order::where('status', 'averaged')->get();
        });
    }

    public function updateAveragedOrders($averagedOrders)
    {
        dump(['updateAveragedOrders']);
        Cache::put('averaged_orders', $averagedOrders);
        return $averagedOrders;
    }

    public function placeMarketSellOrder($order)
    {
        $symbol = $order->symbol;
        dump(['placeMarketSellOrder', $symbol]);


        $quantity = $order->quantity;
        $quantityPrecision = $this->getQuantityPrecision($symbol);
        $quantity = round($quantity, $quantityPrecision);

        if (!isset($this->binApi)) {
            $this->binApi =  new BinanceApi();
        }

        $binOrder = $this->binApi->placeSellOrder($symbol, $quantity, $order->id);
        if (isset($binOrder['orderId'])) {
            $this->completeOrder($binOrder);
        } else {
            dump(['binOrder', $binOrder]);
            if (isset($binOrder['code']) && $binOrder['code'] == '-2010') {
                $order = Order::where('id', $order->id)->first();
                if ($order->status != 'complete') {
                    $order->status = 'losted';
                    $order->save();
                }
            }
        }
    }

    public function getBinanceBalances()
    {

        if (!isset($this->binApi)) {
            $this->binApi =  new BinanceApi();
        }
        $balances = $this->binApi->balances();
        $returnData = [];
        foreach ($balances as $symbol => $balance) {
            if (($balance['available'] == 0) && ($balance['onOrder'] == 0)) continue;

            $returnData[$symbol] = [
                'symbol' => $symbol,
                'available' => $balance['available'],
                'on_order' => $balance['onOrder'],
            ];
        }
        return $returnData;
    }

    public function updateBalanceHistory($balances, $type = null)
    {
        $total = $this->getTotalBalances($balances);
        BalanceHistory::create([
            'type' => $type,
            'total' => $total,
            'balances' => json_encode($balances),
        ]);
    }

    public function updateBalances()
    {

        //dump(['updateBalances']);
        $balances = $this->getBinanceBalances();
        if (!$balances) {
            return false;
        }


        $oldBalances = Cache::get('balances2', []);

        Cache::put('balances2', $balances);
        $this->setTotalBalances($balances);

        if ($oldBalances != $balances) {
            $this->updateBalanceHistory($balances);
            $this->updateDbBalances($balances);
        }

        return true;
    }

    public function updateBalancesOnBalanceUpdate($diff = [])
    {

        dump(["updateBalancesOnBalanceUpdate", $diff]);
        if (!$diff) {
            //return false;
        }

        $balances = $this->getBalances();
        //dump($balances);

        foreach ($diff as $symbol => $balance) {
            $balances[$symbol] = [
                "symbol" => $symbol,
                "available" => $balance['available'],
                "on_order" => $balance['onOrder'],
            ];
        }
        Cache::put('balances2', $balances);
        // dump($balances);
        $this->setTotalBalances($balances);

        $this->updateBalanceHistory($balances);
        $this->updateDbBalances($balances);
        return true;
    }

    public function setTotalBalances($balances)
    {
        //dump($balances);
        dump(['setTotalBalances']);
        $balance_total = $this->calcTotalBalancesPrice($balances);
        dump($balance_total);
        Cache::put('balance_total2', $balance_total);
    }

    public function getTotalBalances($balances = [])
    {
        //$balances = $this->getBalances();
        //dump($balances);
        //Cache::forget('balance_total2');
        $total = Cache::remember('balance_total2', 180, function () {
            $balances = $this->getBalances();
            dump(['getTotalBalances2']);
            return $this->calcTotalBalancesPrice($balances);
        });
        //dump(['getTotalBalances', $total]);
        return $total;
    }


    public function getBalances()
    {
        //dump(['getBalances1']);
        return Cache::remember('balances2', 180, function () {
            dump(['getBalances2']);
            return $this->getBinanceBalances();
        });
    }

    public function updateDbBalances($balances)
    {
        $dbBalanses = Balance::all();
        $updatedSumbols = [];
        foreach ($dbBalanses as $dbBalance) {
            $dbSymbol = $dbBalance->symbol;
            if (isset($balances[$dbSymbol])) {
                $dbBalance->fill($balances[$dbSymbol])->save();
                $updatedSumbols[$dbSymbol] = $dbSymbol;
            } else {
                $dbBalance->delete();
            }
        }

        foreach ($balances as $symbol => $b) {
            if (!in_array($symbol, $updatedSumbols)) {
                Balance::create($b);
            }
        }
    }

    public function completeOrder($binOrder)
    {
        if (isset($binOrder['orderId'])) {
            dump('completeOrder start');
            $order = Order::where('stop_loss_id', $binOrder['orderId'])->first();

            if (is_null($order)) {
                $order = Order::where('id', $binOrder['clientOrderId'])->first();
            }


            if (!is_null($order)) {
                $order->status = 'complete';
                dump($binOrder);
                if (isset($binOrder['type'])  && $binOrder['type'] == 'MARKET') {
                    dump('called from  open_orders');
                    $order->sell_price = $this->calcResultPrice($binOrder);
                } else {
                    dump('called from  change_orders');
                    $order->sell_price = $binOrder['price'];
                }

                $order->sell_time = Carbon::now();
                $order->order_result = ($order->sell_price - $order->buy_price) * $order->quantity;
                $order->order_result_percent  = round($this->calcPercents($order->sell_price, $order->buy_price), 5);
                dump($order->order_result);
                dump($order->order_result_percent);
                $order->save();
                dump('completeOrder success');
            } else {
                dump('completeOrder error1 binOrder');
                dump($binOrder['orderId']);
            }
        } else {
            dump('completeOrder error 2');
        }
    }

    public function getOption($k, $default = '')
    {   //Cache::get('bot_options')
        $options =  Cache::remember('bot_options', 180, function () {
            return Option::select(['key', 'value'])->pluck('value', 'key')->toArray();
        });

        return $options[$k] ?? $default;
    }

    public function resetOptionsCash()
    {
        return Cache::forget('bot_options');
    }

    public function getPrecision($str)
    {
        $str = (string)$str;
        dump(['getPrecision', $str]);
        $arr = explode('.', $str);
        if (!isset($arr[1])) {
            return 0;
        }

        $str = rtrim($arr[1], '0');
        return strlen($str);
    }

    public function getSymbolPrecision($curSumbol)
    {

        $data = [];
        foreach ($this->symbols as $symbol) {
            if ($symbol['symbol'] == $curSumbol) {
                $data = $symbol['data'];
                break;
            }
        }

        $jsonObj = json_decode($data);
        $filters =  $jsonObj->filters;


        $tickSize = '0.000001';
        foreach ($filters as $f) {
            if (isset($f->filterType) && $f->filterType == 'PRICE_FILTER') {
                $tickSize = $f->tickSize;
                break;
            }
        }
        //dd(['getSymbolPrecision',$tickSize] );
        return $this->getPrecision($tickSize);
    }

    public function getQuantityPrecision($curSumbol)
    {
        foreach ($this->symbols as $symbol) {
            if ($symbol['symbol'] == $curSumbol) {
                $minLotSize = $symbol['min_lot_size'];
                //dd($minLotSize );
                return $this->getPrecision($minLotSize);
            }
        }
    }

    public function calcPercents($a, $b)
    {
        if ($b == 0) {
            return 100;
        }
        return ($a - $b) / $b * 100;
    }

    public function calcTotalBalancesPrice($balances)
    {
        //dump($balances);
        // if (!isset($this->ticker)) {
        // if (!isset($this->binRequest)) {
        //     $this->binRequest = new BinanceRequest();
        // }
        // if (!isset($this->binApi)) {
        //     $this->binApi =  new BinanceApi();
        // }

        // $rawTicker = $this->binApi->prices();
        // $this->symbols = Symbol::select(['symbol', 'min_lot_size', 'data'])->get()->toArray();
        //$this->ticker = $this->binRequest->filterTickerSymbols($rawTicker, $this->symbols);

        // dump($this->ticker);
        // }


        $this->ticker = $this->getCacheTicker();
        //dd($this->ticker);
        $total = 0;

        if (isset($balances['USDT'])) {
            $usdArr = $balances['USDT'];
            $total = $usdArr['available'] + $usdArr['on_order'];
        }

        foreach ($balances as $symbol => $balance) {
            if (!$balance['available'] && !$balance['on_order']) continue;
            if ($symbol == 'USDT') continue;
            if (!isset($this->ticker[$symbol . 'USDT'])) {
                dump('!isset' . $symbol . 'USDT');
                continue;
            }
            $curPrice = $this->ticker[$symbol . 'USDT'];
            $available = $balance['available'];
            $on_order = $balance['on_order'];

            $total += $curPrice * $available + $curPrice * $on_order;
        }
        return $total;
    }

    public function calcResultPrice($result)
    {
        $executedQty = $result['executedQty'];
        $totalOrderPrice = $result['cummulativeQuoteQty'];

        $firstFillsPrice = $result['fills'][0]['price'];

        $precision = $this->getPrecision($firstFillsPrice);
        $avgResultPrice = $totalOrderPrice / $executedQty;
        $avgResultPrice = round($avgResultPrice, $precision);

        return $avgResultPrice;
    }
}
