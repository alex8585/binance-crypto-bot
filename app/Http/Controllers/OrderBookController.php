<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\OrderBook as OrderBook;
use App\Console\Commands\Includes\BotUtils  as BotUtils;
use App\Order;

class OrderBookController extends Controller
{
    use BotUtils;

    public function __construct()
    {
        $this->middleware('auth');
        $this->ticker = [];
    }

    public function index(Request $request)
    {


        $type =  $request->type ?? 'candidate';

        $elements = OrderBook::select(
            ['id', 'circle_id', 'symbol', 'created_at as created', 'pierce_price as pp']
        )
            ->with('candidate')
            ->with('circle')
            ->sortable(['created' => 'desc'])
            ->where('order_books.type', $type)
            ->paginate(100);

        return view('orderbook.index', ['elements' =>  $elements]);
    }


    public function details(Request $request, $id)
    {

        $this->range_book_orders = $this->getOption('range_book_orders');
        $book = OrderBook::where(['id' => $id])->first();

        $order = null;
        if ($book->type == 'order') {
            $order = Order::find($book->order_id);
        }

        $bids = json_decode($book->bid, true);
        $asks = json_decode($book->ask, true);

        krsort($bids);

        $pierce_price = $book->pierce_price;


        // dd($price_down_border);

        $maxOrderVal = 0;


        $asks_stop_range = $pierce_price + ($pierce_price / 100  * $this->range_book_orders);
        $newAsks = [];
        foreach ($asks as $p => $q) {
            if ($p >= $pierce_price && $p <= $asks_stop_range) {
                $newAsks[$p] = $q;

                if ($maxOrderVal < $p * $q) {
                    $maxOrderVal = $p * $q;
                }
            }
        }

        //dump($bids);
        // dump($newAsks);
        $bids_stop_range = $pierce_price - ($pierce_price / 100  * $this->range_book_orders);
        $newBids = [];
        foreach ($bids as $p => $q) {
            if ($p <= $pierce_price && $p >=  $bids_stop_range) {
                $newBids[$p] = $q;
                if ($maxOrderVal < $p * $q) {
                    $maxOrderVal = $p * $q;
                }
            }
        }

        $percentVal = $maxOrderVal / 100;

        return view('orderbook.details', [
            'order' => $order,
            'book' =>  $book,
            'bids' =>  $newBids,
            'asks' =>  $newAsks,
            'percentVal' =>  $percentVal,
            'asks_stop_range' => $asks_stop_range,
            'bids_stop_range' => $bids_stop_range
        ]);
    }
}
