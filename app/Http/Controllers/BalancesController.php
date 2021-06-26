<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Balance as Balance;
use \Carbon\Carbon as Carbon;
use App\Order as Order;
use App\Console\Commands\Includes\BotUtils;
use App\Console\Commands\Includes\BinanceApi;
use App\BalanceHistory;
use App\Console\Commands\Includes\BinanceRequest;
use App\Symbol;

class BalancesController extends Controller
{
    use BotUtils;

    public function __construct()
    {
        $this->middleware('auth');
        $this->binRequest = new BinanceRequest();
        $this->binApi =  new BinanceApi();
    }


    public function index(Request $request)
    {
        $rawTicker = $this->binApi->prices();
        $this->symbols = Symbol::select(['symbol', 'min_lot_size', 'data'])->get()->toArray();
        $this->ticker = $this->binRequest->filterTickerSymbols($rawTicker, $this->symbols);

        $this->updateBalances();

        $balances = $this->getBalances();
        $this->balanceTotal = $this->getTotalBalances($balances);

        $currentTotalComission = $this->getCurrentSellComission();
        $balances = $this->getBalances();
        $lastTotal = $this->getTotalBalances($balances);

        $elements = Balance::select(["*"])->sortable(['created_at' => 'desc'])->paginate(100);

        $historys  = BalanceHistory::where('created_at', '>', Carbon::now()->subHour(24))
            ->orderByDesc('created_at')->get();

        $before24  = BalanceHistory::where('created_at', '<', Carbon::now()->subHour(24))
            ->orderByDesc('created_at')->first();

        $historys->push($before24);

        $now = Carbon::now();
        $now24h = (clone $now)->subHour(24);
        $now12h = (clone $now)->subHour(12);
        $now6h = (clone $now)->subHour(6);
        $now3h = (clone $now)->subHour(3);
        $now1h = (clone $now)->subHour(1);

        function getElem($e)
        {
            return [
                'total' =>  $e->total,
                'created_at' =>  $e->created_at,
                'created_at_str' =>  $e->created_at->format('d-m-yy H:i:s'),
            ];
        }

        $histrorysArr = [];
        foreach ($historys  as $e) {

            if ($e->created_at->lt($now24h)) {
                $histrorysArr['before_24h'][] =  getElem($e);
            }
            if ($e->created_at->lt($now12h)) {
                $histrorysArr['before_12h'][] =  getElem($e);
            }
            if ($e->created_at->lt($now6h)) {
                $histrorysArr['before_6h'][] =  getElem($e);
            }
            if ($e->created_at->lt($now3h)) {
                $histrorysArr['before_3h'][] =  getElem($e);
            }
            if ($e->created_at->lt($now1h)) {
                $histrorysArr['before_1h'][] = getElem($e);
            }
        }

        $hArrResults = [];
        foreach ($histrorysArr as $t => $arr) {
            $hArrResults[$t] = collect($arr)->first()['total'];
        }

        $history1h = $history3h = $history6h = $history12h = $history24h = 0;

        $history24h = $this->calcPercents($lastTotal, $hArrResults['before_24h']);
        $history12h = $this->calcPercents($lastTotal, $hArrResults['before_12h']);
        $history6h = $this->calcPercents($lastTotal, $hArrResults['before_6h']);
        $history3h = $this->calcPercents($lastTotal, $hArrResults['before_3h']);
        $history1h = $this->calcPercents($lastTotal, $hArrResults['before_1h']);


        return view('balances.index', [
            'elements' =>  $elements,
            'history1h' => round($history1h - $currentTotalComission, 5),
            'history3h' => round($history3h - $currentTotalComission, 5),
            'history6h' => round($history6h - $currentTotalComission, 5),
            'history12h' => round($history12h - $currentTotalComission, 5),
            'history24h' => round($history24h - $currentTotalComission, 5),
        ]);
    }


    private function getCurrentSellComission()
    {
        $this->tax = config('settings.binance.tax_percent');

        $orders = Order::select(['symbol', 'buy_price', 'quantity', 'created_at'])
            ->whereIn('status', ['trade', 'averaged'])
            ->get()
            ->sortBy('sell_time');

        $orderPrices = [];
        $totalResultPercent = 0;
        $currentTotalComission = 0;
        foreach ($orders as $order) {
            $symbol = $order->symbol;
            $buy_price = $order->buy_price * $order->quantity;
            $cur_price = $this->ticker[$symbol] * $order->quantity;
            $result = $cur_price - $buy_price;
            $buy_tax = $buy_price / 100 * $this->tax;
            $cur_tax = $cur_price / 100 * $this->tax;
            $buy_tax_percent = $buy_tax / $this->balanceTotal * 100;
            $cur_tax_percent = $cur_tax / $this->balanceTotal * 100;

            $result_with_tax = $result - ($buy_tax + $cur_tax);
            $result_percent = $result_with_tax / $this->balanceTotal * 100;



            $orderPrices[] = [
                'symbol' => $symbol,
                'buy_price' => $buy_price,
                'cur_price' => $cur_price,
                'result' => $result,
                'buy_tax' => $buy_tax,
                'cur_tax' => $cur_tax,
                'buy_tax_percent' => $buy_tax_percent,
                'cur_tax_percent' => $cur_tax_percent,
                'result_with_tax' => $result_with_tax,
                'result_percent' => $result_percent,
            ];
            $currentTotalComission += $cur_tax_percent;
            //$totalResultPercent += $result_percent;
        }

        return $currentTotalComission;
    }



    // public function old_index(Request $request)
    // {

    //     $this->updateBalances();
    //     $balances = $this->getBalances();
    //     $lastTotal = $this->getTotalBalances($balances);


    //     $elements = Balance::select(["*"])->sortable(['created_at' => 'desc'])->paginate(100);


    //     $sub1Hours =  Carbon::now()->subHours(1);
    //     $sub3Hours =  Carbon::now()->subHours(3);
    //     $sub6Hours =  Carbon::now()->subHours(6);
    //     $sub12Hours =  Carbon::now()->subHours(12);
    //     $sub24Hours =  Carbon::now()->subHours(24);


    //     $orders = Order::select(['order_result_percent', 'buy_price', 'quantity', 'created_at', 'sell_time'])
    //         ->whereDate('sell_time', '>', $sub24Hours)
    //         ->where('status', '=', 'complete')
    //         ->get()
    //         ->sortBy('sell_time');

    //     $history = BalanceHistory::select(['total', 'created_at'])->whereDate('created_at', '>', $sub24Hours)->get()
    //         ->sortBy('created_at');

    //     $histoysArr = [];

    //     foreach ($history as $h) {
    //         if ($h->created_at > $sub1Hours) {
    //             $histoysArr['1h'][$h->created_at->format('Y-m-d H:i:s')] = $h->total;
    //         }
    //         if ($h->created_at > $sub3Hours) {
    //             $histoysArr['3h'][$h->created_at->format('Y-m-d H:i:s')] = $h->total;
    //         }
    //         if ($h->created_at > $sub6Hours) {
    //             $histoysArr['6h'][$h->created_at->format('Y-m-d H:i:s')] = $h->total;
    //         }
    //         if ($h->created_at > $sub12Hours) {
    //             $histoysArr['12h'][$h->created_at->format('Y-m-d H:i:s')] = $h->total;
    //         }
    //         if ($h->created_at > $sub24Hours) {
    //             $histoysArr['24h'][$h->created_at->format('Y-m-d H:i:s')] = $h->total;
    //         }
    //     }

    //     $newHistory = [];
    //     foreach ($histoysArr as $k => $hisArr) {
    //         $newHistory[$k] = array_shift($hisArr);
    //     }

    //     // dd( $newHistory);

    //     $ordersArr = [];
    //     foreach ($orders as $order) {
    //         if ($order->sell_time > $sub1Hours) {
    //             $newHistory['1h'] = isset($newHistory['1h']) ? $newHistory['1h'] : $lastTotal;


    //             $order_part_percent = $newHistory['1h'] / ($order->buy_price * $order->quantity);
    //             $ordersArr['1h'][$order->sell_time->format('Y-m-d H:i:s')]
    //                 =  $order->order_result_percent / $order_part_percent;
    //         }
    //         if ($order->sell_time > $sub3Hours) {
    //             $newHistory['3h'] = isset($newHistory['3h']) ? $newHistory['3h'] : $lastTotal;


    //             $order_part_percent = $newHistory['3h'] / ($order->buy_price * $order->quantity);
    //             $ordersArr['3h'][$order->sell_time->format('Y-m-d H:i:s')]
    //                 =  $order->order_result_percent / $order_part_percent;
    //         }
    //         if ($order->sell_time > $sub6Hours) {
    //             $newHistory['6h'] = isset($newHistory['6h']) ? $newHistory['6h'] : $lastTotal;


    //             $order_part_percent = $newHistory['6h'] / ($order->buy_price * $order->quantity);
    //             $ordersArr['6h'][$order->sell_time->format('Y-m-d H:i:s')]
    //                 =  $order->order_result_percent / $order_part_percent;
    //         }
    //         if ($order->sell_time > $sub12Hours) {
    //             $newHistory['12h'] = isset($newHistory['12h']) ? $newHistory['12h'] : $lastTotal;


    //             $order_part_percent = $newHistory['12h'] / ($order->buy_price * $order->quantity);
    //             $ordersArr['12h'][$order->sell_time->format('Y-m-d H:i:s')]
    //                 =  $order->order_result_percent / $order_part_percent;
    //         }
    //         if ($order->sell_time > $sub24Hours) {
    //             $newHistory['24h'] = isset($newHistory['24h']) ? $newHistory['24h'] : $lastTotal;


    //             $order_part_percent = $newHistory['24h'] / ($order->buy_price * $order->quantity);
    //             $ordersArr['24h'][$order->sell_time->format('Y-m-d H:i:s')]
    //                 =  $order->order_result_percent / $order_part_percent;
    //         }
    //     }



    //     $ordersSum = [];
    //     foreach ($ordersArr as $k => $o) {
    //         $ordersSum[$k] = round(array_sum($o), 5);
    //     }


    //     $history1h = isset($ordersSum['1h']) ? $ordersSum['1h'] : 0;
    //     $history3h = isset($ordersSum['3h']) ? $ordersSum['3h'] : 0;
    //     $history6h = isset($ordersSum['6h']) ? $ordersSum['6h'] : 0;
    //     $history12h = isset($ordersSum['12h']) ? $ordersSum['12h'] : 0;
    //     $history24h = isset($ordersSum['24h']) ? $ordersSum['24h'] : 0;


    //     return view('balances.index', [
    //         'elements' =>  $elements,
    //         'history1h' => $history1h,
    //         'history3h' => $history3h,
    //         'history6h' => $history6h,
    //         'history12h' => $history12h,
    //         'history24h' => $history24h,
    //     ]);
    // }

    // private function getLastTotal()
    // {
    //     $total = BalanceHistory::select(['total'])->orderBy('created_at', 'DESC')->first()->total;
    //     return  $total;
    // }
}
