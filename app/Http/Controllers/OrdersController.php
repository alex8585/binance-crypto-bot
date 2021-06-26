<?php

namespace App\Http\Controllers;

use App\Circle;
use App\Option;
use App\Symbol;
use \Carbon\Carbon;
use App\GreenCount;
use App\Order as Order;
use Illuminate\Http\Request;
use App\Console\Commands\Includes\BotUtils;
use App\Console\Commands\Includes\BinanceApi;

class OrdersController extends Controller
{
    use BotUtils;

    public function __construct()
    {
        $this->middleware('auth');
        $this->ticker = [];
    }


    public function index(Request $request)
    {
        return view('orders.index', [
            'current' =>  $this->getOption('green_current_cnt'),
            'avg' => $this->getOption('green_avg_cnt'),
            'green_above_avg' =>  $this->getOption('green_above_avg'),
            'count_result' => $this->getOption('statistics_count_result'),
            'trade_start' => $this->getOption('statistics_count_trade_start'),
            'trade_stop' => $this->getOption('statistics_count_trade_stop'),
        ]);
    }

    public function getorders(Request $request)
    {
        $request->status = $request->status != 'all' ? $request->status : null;

        $this->tax_percent = config('settings.binance.tax_percent');

        $elements = Order::select([
            'orders.id',
            'candidates.volume_status',
            'order_books.id as order_books_id',
            'orders.candidate_id',
            'orders.symbol',
            'orders.pierce_price',
            'orders.buy_price',
            'buy_time',
            'sell_time',
            'orders.status as order_status',
            'candidates.status as candidate_status',
            'quantity',
            'circles.hour as order_hour',
            'stop_price1',
            'stop_price2',
            'orders.sell_price',
            'averaged_at',

        ])->addSelect(\DB::raw('(orders.sell_price - orders.buy_price)/orders.buy_price*100 as sell_percent'))

            ->sortable(['buy_time' => 'desc'])
            ->join(
                'circles',
                'orders.circle_id',
                '=',
                'circles.id'
            )
            ->join(
                'candidates',
                'orders.candidate_id',
                '=',
                'candidates.id'
            )
            ->leftJoin('order_books', function ($join) {
                $join->on('orders.candidate_id', '=', 'order_books.candidate_id')
                    ->where('type', 'candidate');
            });


        if ($request->status) {
            if ($request->status == 'trade') {
                $elements = $elements->whereIn('orders.status', ['trade', 'averaged']);
            } else {
                $elements = $elements->where(['orders.status' => $request->status]);
            }
        }


        $allOpenOrdersIds = [];
        $totalPercent = 0;
        $allOpenOrdersCnt = 0;
        //$totalCost = 0;
        if ($request->status == 'trade' && $elements->count()) {

            $elements = $elements->paginate(7);
            $this->binApi =  new BinanceApi();
            $this->ticker = $this->binApi->prices();

            foreach ($elements as $e) {
                $curPrice = $this->ticker[$e->symbol];
                $e->cur_percent = $e->getCurrentPercent($curPrice);


                $e->buy_time_d = $e->buy_time->format('d-m-Y');
                $e->buy_time_t = $e->buy_time->format('H:i');

                if ($e->averaged_at) {
                    $e->averaged_at = $e->averaged_at->format('d-m-Y H:i');
                }
            }


            $allOpenOrders = Order::select(['id', 'symbol', 'buy_price'])
                ->whereIn('orders.status', ['trade', 'averaged'])->get();


            foreach ($allOpenOrders as $order) {
                $curPrice = $this->ticker[$order->symbol];
                $totalPercent += $order->getCurrentPercent($curPrice);
                $allOpenOrdersIds[] = $order->id;
                //$totalCost += $order->buy_price * $order->quantity;
            }

            $allOpenOrdersCnt = count($allOpenOrders);
        } else if ($request->status == 'complete') {
            $elements = $elements->where('orders.sell_price', '>', 0)->paginate(50);
            foreach ($elements as $e) {

                $e->calc_sell_percent = $e->sellPercent;

                $e->buy_time_d = $e->buy_time->format('d-m-Y');
                $e->buy_time_t = $e->buy_time->format('H:i');

                if ($e->sell_time) {
                    $e->sell_time_d = $e->sell_time->format('d-m-Y');
                    $e->sell_time_t = $e->sell_time->format('H:i');
                }
            }
        } else {
            $elements = $elements->paginate(50);

            foreach ($elements as $e) {
                $e->buy_time_d = $e->buy_time->format('d-m-Y');
                $e->buy_time_t = $e->buy_time->format('H:i');
            }
        }


        //dd($totalCost*$this->tax*2/100);
        $elements = $elements->toArray();
        $elements['allOpenOrdersIds'] = $allOpenOrdersIds;
        $elements['totalPercent'] = $totalPercent - $allOpenOrdersCnt * $this->tax_percent * 2;

        return  $elements;
    }


    public function sellorders(Request $request)
    {

        ob_start();
        $ids = $request->ids;

        $orders = Order::whereIN('status', ['trade', 'averaged'])->whereIN('id', $ids)->get();

        $openOrders = $this->getOpenOrders();
        $openOrders = $openOrders->diff($orders);
        $this->updateOpenOrders($openOrders);


        $averagedOrders = $this->getAveragedOrders();
        $averagedOrders = $averagedOrders->diff($orders);
        $this->updateAveragedOrders($averagedOrders);


        $this->binApi =  new BinanceApi();
        $this->symbols = Symbol::select(['symbol', 'min_lot_size', 'data'])->get()->toArray();

        foreach ($orders as $order) {
            $this->placeMarketSellOrder($order);
        }

        $content = ob_get_contents();
        //dd($content);
        ob_end_clean();

        return response()
            ->json([
                'ids' =>  $ids,
                'result' => 'success',
                'sold_time' => Carbon::now()->timestamp,
            ]);
    }
}
