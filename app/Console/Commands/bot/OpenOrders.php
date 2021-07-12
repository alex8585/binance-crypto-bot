<?php

namespace App\Console\Commands\bot;

use Illuminate\Console\Command;
use \Carbon\Carbon as Carbon;
use App\Candidate as Candidate;
use App\Symbol as Symbol;
use App\Order as Order;
use App\Option  as Option;
use App\Balance as Balance;
use Illuminate\Support\Facades\Log;
use Artisan;
use App\Jobs\ProcessGetStatistics;
use App\Jobs\ProcessGetOrderBook;
use App\Console\Commands\Includes\BotUtils  as BotUtils;
use App\Console\Commands\Includes\BinanceApi  as BinanceApi;
use App\Console\Commands\Includes\BinanceRequest;
use Illuminate\Support\Facades\Cache;

class OpenOrders extends Command
{
    use BotUtils;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders_open';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $openOrders;
    protected $averagedOrders;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->getAllOptions();

        $this->min_order_price = config('settings.binance.min_order_price');
        $this->tax = config('settings.binance.tax');
        $this->trade_mode = config('settings.binance.trade_mode');
        $this->trade_exclude = config('settings.binance.trade_exclude');
        $this->tax_percent = config('settings.binance.tax_percent');

        $this->ticker = [];

        $this->binRequest = new BinanceRequest();
        $this->binApi =  new BinanceApi();
        $this->binApi->caOverride = true;

        $this->excludeSymbols = config('settings.binance.symbols_exclude');
        $this->symbols = Symbol::select(['symbol', 'min_lot_size', 'data'])->get()->toArray();

        $this->initOpenOrders();
        $this->initAveragedOrders();
    }


    public function getAllOptions()
    {

        $this->get_order_book_percent = $this->getOption('get_order_book_percent');
        $this->buy_gap_percent = $this->getOption('buy_gap_percent');
        $this->profit_percent = $this->getOption('profit_percent');
        $this->profit_step_percent = $this->getOption('profit_step_percent');
        $this->buy_analysis_disable = $this->getOption('buy_analysis_disable');
        $this->stop_loss_percent = $this->getOption('stop_loss_persent');
        $this->order_size = $this->getOption('order_size');
        $this->order_lifetime = $this->getOption('order_lifetime');
        $this->sell_all_after_profit_percent = $this->getOption('sell_all_after_profit_percent');
        $this->green_above_avg = $this->getOption('green_above_avg');
        $this->statistics_count_result = $this->getOption('statistics_count_result');

        $this->averaging_percentage = $this->getOption('averaging_percentage');
        $this->sales_perc_averaged = $this->getOption('sales_perc_averaged');
    }

    public function initTicker()
    {
        $rawTicker = $this->binApi->prices();
        $this->ticker = $this->binRequest->filterTickerSymbols($rawTicker, $this->symbols);
        $this->balances = $this->getBalances();

        $this->setCacheTicker($rawTicker);
    }
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->updateBalances();
        $this->initTicker();



        while (true) {

            $this->getAllOptions();

            $this->openOrders = $this->getOpenOrders();
            $this->averagedOrders = $this->getAveragedOrders();

            //sleep(1);
            $this->initTicker();

            $this->sellStopLossOrders();

            $this->sellAllIfTotalProfit();

            $this->sellTimeOutOrders();

            $this->sellTakeProfitOrders();

            $this->takeProfitOrdersAnalysis();

            $this->checkAveragingPercentage();

            $this->sellProfitedAveragedOrders();

            $this->placeNewOrders();
        }

        return 0;
    }



    public function takeProfitOrdersAnalysis()
    {
        if (!$this->profit_percent || !$this->profit_step_percent) {
            return;
        }

        if ($this->openOrders->isEmpty()) {
            return;
        }

        foreach ($this->openOrders as $k => $order) {

            $symbol = $order->symbol;
            $currentPrice = $this->ticker[$symbol];
            $buyPrice  = $order->buy_price;
            $lastProfitPrice = $order->last_profit_price;

            //$newProfitPrice =  ($buyPrice * $this->profit_percent / 100) - $buyPrice;

            if ($lastProfitPrice > 0) {
                $newProfitPrice =  $lastProfitPrice + ($buyPrice * $this->profit_step_percent / 100);
            } else {
                $newProfitPrice =  $buyPrice + ($buyPrice * $this->profit_percent / 100);
            }


            if ($currentPrice  > $newProfitPrice) {
                dump(['currentPrice', $currentPrice]);
                dump(['lastProfitPrice', $lastProfitPrice]);
                dump(['newProfitPrice', $newProfitPrice]);

                $order->last_profit_price = $newProfitPrice;
                $order->save();
                $this->getOrderBookforOrder($order, $currentPrice);
            }
        }
    }

    public function getOrderBookforOrder($order, $currentPrice)
    {
        $params = [
            'type' => 'order',
            'order_id' => $order->id,
            'symbol' => $order->symbol,
            'pierce_price' => $currentPrice,
            'circle_id' => $order->circle_id,
            'candidate_id' => $order->candidate_id,
        ];
        ProcessGetOrderBook::dispatch($params)->onQueue('orderbook');
    }

    public function sellStopLossOrders()
    {

        if (!$this->stop_loss_percent) {
            return;
        }

        if ($this->openOrders->isEmpty()) {
            return;
        }

        foreach ($this->openOrders as $k => $order) {

            $symbol = $order->symbol;
            $currentPrice = $this->ticker[$symbol];
            $buyPrice  = $order->buy_price;

            $stopPrice = $buyPrice - ($buyPrice * $this->stop_loss_percent / 100);

            if ($currentPrice  < $stopPrice) {
                $this->openOrders->pull($k);
                $this->updateOpenOrders($this->openOrders);
                $this->placeMarketSellOrder($order);
            }
        }
    }

    public function checkAveragingPercentage()
    {

        if (!$this->averaging_percentage || !$this->sales_perc_averaged) {
            return;
        }

        if ($this->openOrders->isEmpty()) {
            return;
        }

        foreach ($this->openOrders as $k => $order) {


            if ($order->error == 'convert_to_averaged') {
                //dump(['$order->error convert_to_averaged', $order->symbol]);
                continue;
            }

            $symbol = $order->symbol;
            $currentPrice = $this->ticker[$symbol];
            $buyPrice  = $order->buy_price;

            $buyMorePrice = $buyPrice - ($buyPrice * $this->averaging_percentage / 100);

            if ($currentPrice  < $buyMorePrice) {


                // if ($order->averaged_at) {
                //     continue;
                // }
                dump(['checkAveragingPercentage']);
                if (!$this->checkIsEnoughtsFunds()) {
                    dump(['!checkIsEnoughtsFunds convertOrderToAveraged']);
                    continue;
                }

                //dump($this->openOrders);
                $this->openOrders->pull($k);
                $this->updateOpenOrders($this->openOrders);
                //dump($this->openOrders);
                //dd('1');
                $this->convertOrderToAveraged($order, $currentPrice);
            }
        }
    }

    public function sellProfitedAveragedOrders()
    {
        if (!$this->sales_perc_averaged) {
            return;
        }

        if ($this->averagedOrders->isEmpty()) {
            return;
        }

        foreach ($this->averagedOrders as $k => $order) {
            //dump($order);
            $symbol = $order->symbol;
            $currentPrice = $this->ticker[$symbol];
            $buyPrice  = $order->buy_price;

            $takePrice = $buyPrice + ($buyPrice * $this->sales_perc_averaged / 100);

            if ($currentPrice  >= $takePrice) {
                $this->averagedOrders->pull($k);
                $this->updateAveragedOrders($this->averagedOrders);
                $this->placeMarketSellOrder($order);
            }
        }
    }

    public function sellTimeOutOrders()
    {
        if (!$this->order_lifetime) {
            return;
        }

        if ($this->openOrders->isEmpty()) {
            return;
        }

        foreach ($this->openOrders as $k => $order) {

            $buy_time = $order->buy_time;
            $end_time = (clone $buy_time)->addMinutes($this->order_lifetime);

            if (now()->gt($end_time)) {
                $this->openOrders->pull($k);
                $this->updateOpenOrders($this->openOrders);
                $this->placeMarketSellOrder($order);
            }
        }
    }

    public function sellAllIfTotalProfit()
    {
        if (!$this->sell_all_after_profit_percent) {
            return;
        }

        if ($this->openOrders->isEmpty()) {
            return;
        }
        $allOpenOrdersCnt = $this->openOrders->count();
        $totalPercent = 0;
        foreach ($this->openOrders as $k => $order) {
            $curPrice = $this->ticker[$order->symbol];
            $totalPercent += $order->getCurrentPercent($curPrice);
        }
        $totalPercent = $totalPercent - $allOpenOrdersCnt * $this->tax_percent * 2;
        if ($totalPercent >= $this->sell_all_after_profit_percent) {
            $orders = $this->openOrders;
            $this->openOrders = collect();
            $this->updateOpenOrders($this->openOrders);

            foreach ($orders as $order) {
                $this->placeMarketSellOrder($order);
            }
        }
    }

    public function sellTakeProfitOrders()
    {
        $orders = Order::where('status', 'trade')->where('volume_status', 'bad')->get();

        if ($orders->isEmpty()) {
            return;
        }

        $this->openOrders = $this->openOrders->diff($orders);
        $this->updateOpenOrders($this->openOrders);
        foreach ($orders as $order) {
            $this->placeMarketSellOrder($order);
        }
    }


    public function placeNewOrders()
    {

        $now = Carbon::now();
        $sub5Hours =  Carbon::now()->subHours(5);

        $candidates = Candidate::where(['is_pierced' => false, 'status' => 'working', 'order_id' => 0])
            ->whereNull('error')->get();


        $candidates_ids = [];
        foreach ($candidates as $candidat) {

            $symbol = $candidat->symbol;


            if (!isset($this->ticker[$symbol])) {
                $this->initTicker();
            }
            if (!isset($this->ticker[$symbol])) {
                dump($symbol);
                dump($this->ticker);
            }

            $currentPrice = $this->ticker[$symbol];
            $buyPrice = $candidat->yesterday_max_price;


            if (in_array($symbol, $this->excludeSymbols)) {
                $candidat->status = 'expired';
                $candidat->save();
                continue;
            }

            if ($candidat->created_at < $sub5Hours) {
                $candidat->status = 'expired';
                $candidat->save();
                continue;
            }

            if (!$this->buy_analysis_disable) {
                $this->getOrderBook($candidat, $currentPrice);
            }


            $yesterdayMaxChanged = $candidat->yesterday_max_price - $candidat->yesterday_max_price * $this->buy_gap_percent / 100;
            $isPierced = $currentPrice > $yesterdayMaxChanged;

            if (in_array($symbol, $this->trade_exclude)) {
                continue;
            }
            //dump([$isPierced,$symbol]);
            if ($isPierced) {
                $candidates_ids[] = $candidat->id;

                dump(['isPierced', $symbol]);
                $candidat->is_pierced = true;
                $candidat->pierce_time = $now;
                $candidat->pierce_price =  $yesterdayMaxChanged;
                $candidat->save();

                if (!$this->statistics_count_result) {
                    dump(['!statistics_count_result']);
                    continue;
                }

                if (!$this->green_above_avg) {
                    dump(['!green_above_avg']);
                    continue;
                }


                if (!$this->buy_analysis_disable) {
                    dump(['!buy_analysis_disable']);
                    if ($candidat->volume_status != 'ok') {
                        continue;
                    }
                }
                dump(['placeNewOrders checkIsEnoughtsFunds']);
                if (!$this->checkIsEnoughtsFunds()) {
                    dump(['!checkIsEnoughtsFunds']);
                    continue;
                }


                $quantity = $this->calcQuantitySumbols($buyPrice);
                $this->createNewMarketOrder($candidat, $symbol, $quantity);
            }
        }

        if ($candidates_ids) {
            ProcessGetStatistics::dispatch($candidates_ids)->onQueue('statistics');
        }
    }

    public function getOrderBook($candidat, $currentPrice)
    {
        $yesterdayMaxChanged = $candidat->yesterday_max_price -
            $candidat->yesterday_max_price *  $this->get_order_book_percent / 100;

        $isTimeTogetOrderBook = $currentPrice > $yesterdayMaxChanged;


        if (!$isTimeTogetOrderBook) {
            return;
        }

        if ($candidat->volume_status) {
            return;
        }

        $params = [
            'type' => 'candidate',
            'symbol' => $candidat->symbol,
            'pierce_price' => $currentPrice,
            'circle_id' => $candidat->circle_id,
            'candidate_id' => $candidat->id,
        ];
        ProcessGetOrderBook::dispatch($params)->onQueue('orderbook');
    }

    public function isSybolAlreadyBought($symbol)
    {
        $sub24Hours =  Carbon::now()->subHours(24);
        $r = Candidate::where([
            ['status', '=', 'order_placed'],
            ['symbol', '=', $symbol]
        ])->where('created_at', '>', $sub24Hours)->exists();
        return  $r;
    }

    public function convertOrderToAveraged($order, $currentPrice)
    {
        $symbol = $order->symbol;
        $quantity = $order->quantity;
        dump(['convertOrderToAveraged', $symbol]);

        $quantityPrecision = $this->getQuantityPrecision($symbol);
        $quantity = round($quantity, $quantityPrecision);

        $cost = $currentPrice * $quantity;

        if ($cost <  $this->min_order_price) {
            dump(['cost < min_order_price', $cost]);
            $order->error = 'convert_to_averaged';
            $order->save();
            return null;
        }


        try {
            $binOrder = $this->binApi->placeBuyOrder($symbol, $quantity);
        } catch (\Exception $e) {
            echo  $e->getMessage(), "\n";
        }

        if (isset($binOrder['orderId'])) {
            $avgResultPrice = $this->calcResultPrice($binOrder);

            dump(['convertOrderToAveraged OK', $symbol]);

            $newAvgResultPrice = ($avgResultPrice + $order->buy_price) / 2;

            $order->fill([
                'buy_price' => $newAvgResultPrice,
                'quantity' => $quantity + $order->quantity,
                'binance_id' => $binOrder['orderId'],
                'client_orde_id' => $binOrder['clientOrderId'],
                'status' => 'averaged',
                'averaged_at' => now(),
            ]);
            $order->save();
            $this->averagedOrders->push($order);
        } else {
            $order->error = 'convert_to_averaged';
            $order->save();
            $this->updateBalances();
            dump(['convertOrderToAveraged ERROR', $symbol]);
            dump($binOrder);
        }
        return $binOrder;
    }


    public function createNewMarketOrder($candidat, $symbol, $quantity)
    {
        dump(['createNewMarketOrder', $symbol]);

        $quantityPrecision = $this->getQuantityPrecision($symbol);
        $quantity = round($quantity, $quantityPrecision);

        try {
            $binOrder = $this->binApi->placeBuyOrder($symbol, $quantity);
        } catch (\Exception $e) {
            echo  $e->getMessage(), "\n";
        }


        if (isset($binOrder['orderId'])) {
            $avgResultPrice = $this->calcResultPrice($binOrder);

            dump(['createNewMarketOrder OK', $symbol]);
            dump($binOrder);
            $now = Carbon::now();
            $order = new Order();
            $order->symbol = $symbol;
            $order->buy_time = $now;
            $order->pierce_price = $candidat->pierce_price;
            $order->buy_price = $avgResultPrice;
            $order->quantity = $quantity;
            $order->binance_id = $binOrder['orderId'];
            $order->client_orde_id = $binOrder['clientOrderId'];
            $order->circle_id = $candidat->circle_id;
            $order->candidate_id = $candidat->id;
            $order->status = 'trade';
            $order->created_at = $now;
            $order->updated_at = $now;
            $order->stop_loss_id = 0;

            $order->save();
            $this->openOrders->push($order);
            // $this->setSellLimitOrder($order);

            $candidat->order_id = $order->id;
            $candidat->status  = 'order_placed';
        } else {
            dump(['createNewOrder ERROR', $symbol]);
            $this->updateBalances();
            dump($binOrder);

            if (isset($binOrder['code']) && isset($binOrder['msg'])) {
                $candidat->error == $binOrder['code'];
                $candidat->error_text == json_encode($binOrder['msg']);
            }
        }

        $candidat->save();
        //$this->updateBalances();
        return $binOrder;
    }

    public function checkIsEnoughtsFunds()
    {
        $this->balances = $this->getBalances();
        //$this->updateBalances();
        $balances = $this->balances;
        $usdArr = $balances['USDT'];


        if ($usdArr['available'] > $this->order_size) {
            return true;
        }
        return false;
    }

    public function calcQuantitySumbols($buyPrice)
    {
        $quantity = $this->order_size  / $buyPrice;
        return $quantity;
    }
}
