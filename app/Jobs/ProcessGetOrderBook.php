<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\OrderBook as OrderBook;
use App\Candidate as Candidate;
use App\Order;
use \Carbon\Carbon as Carbon;
use App\Console\Commands\Includes\BinanceApi  as BinanceApi;
use App\Console\Commands\Includes\BotUtils  as BotUtils;

class ProcessGetOrderBook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, BotUtils;

    protected $params;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        $type = $this->params['type'];
        $candidate_id = $this->params['candidate_id'];
        $symbol = $this->params['symbol'];
       
        
        if($type == 'candidate') {
            $candidat = Candidate::whereNull('volume_status')->firstWhere(['id'=> $candidate_id]);
            
            if(!$candidat) { return; }
    
            dump('ProcessGetOrderBookcandidate',$candidate_id);
    
            $candidat->volume_status = 'processing';
            $candidat->save();

            //$symbol = $candidat->symbol;

        } elseif($type == 'order') {
            dump('candidate_id',$candidate_id);
            $candidat = Candidate::firstWhere(['id'=> $candidate_id]);
            //dump($candidat);
        }
        




        $this->buy_gap_percent = $this->getOption('buy_gap_percent'); 
        $this->profit_percent = $this->getOption('profit_percent'); 
        $this->volume_percent = $this->getOption('volume_percent'); 

        $this->range_book_orders = $this->getOption('range_book_orders'); 
       

       

        $this->binApi =  new BinanceApi();
        $depth_limit  = config('settings.binance.depth_limit'); 
        $depth = $this->binApi->depth($symbol, $depth_limit);
            
       
        if($type == 'candidate') {
            $addData = $this->handleCandidate($candidat, $depth);
        } elseif($type == 'order') {
            $order_id = $this->params['order_id'];
            $order =  Order::find( $order_id);
            $addData = $this->handleOrder($order, $depth);
        }
       

       


        $this->handleOrderBook($candidat, $depth, $addData);
       
    }

    public function handleOrder($order, $depth) {
        dump(['handleOrder',$order->id]);
        $buy_price = $order->buy_price ;
        $sell_price = $order->sell_price;
        //$price_down_border = $buy_price - ($buy_price / 100  * $this->profit_percent);
        $lastProfitPrice = $order->last_profit_price;
        $pierce_price = $this->params['pierce_price'];


        
        $asks_stop_range = $lastProfitPrice + ($lastProfitPrice / 100  * $this->range_book_orders);
        $asksValue = 0;
        foreach($depth['asks'] as $p => $q) {
            if($p >= $lastProfitPrice && $p <= $asks_stop_range) {
                $asksValue += $p * $q;
            }
        }

        $bids_stop_range = $lastProfitPrice - ($lastProfitPrice / 100  * $this->range_book_orders);
        $bidsValue = 0;
        foreach($depth['bids'] as $p => $q) {
            if($p <= $lastProfitPrice && $p >= $bids_stop_range) {
                $bidsValue += $p * $q;
            }
        }

        $order->volume_status = 'ok';
        if($asksValue + $asksValue * $this->volume_percent/100 > $bidsValue ) {
            $order->volume_status = 'bad';
            $order->save();
        }

        
        
        return [
            'type' => 'order',
            'order_id' => $order->id,
            'pierce_price' => $pierce_price,
            'buy_price' =>$buy_price,
            'sell_price' =>$sell_price,
            'price_down_border' =>$bids_stop_range,
            'bids_volume' => $bidsValue,
            'asks_volume' => $asksValue,
            'volume_status' => $order->volume_status,
        ];
    }

    public function handleCandidate($candidat, $depth) {
        $buy_price = $candidat->yesterday_max_price - $candidat->yesterday_max_price * $this->buy_gap_percent / 100;
        $sell_price = $buy_price + ($buy_price / 100  * $this->profit_percent);
        //$price_down_border = $buy_price - ($buy_price / 100  * $this->profit_percent);
        $pierce_price = $this->params['pierce_price'];
    


        
        $asks_stop_range = $pierce_price + ($pierce_price / 100  * $this->range_book_orders);
        $asksValue = 0;
        foreach($depth['asks'] as $p => $q) {
            if($p >= $pierce_price && $p <= $asks_stop_range) {
                $asksValue += $p * $q;
            }
        }

        $bids_stop_range = $pierce_price - ($pierce_price / 100  * $this->range_book_orders);
        $bidsValue = 0;
        foreach($depth['bids'] as $p => $q) {
            if($p <= $pierce_price && $p >= $bids_stop_range) {
                $bidsValue += $p * $q;
            }
        }

        $candidat->volume_status = 'bad';
        if($asksValue + $asksValue * $this->volume_percent/100 < $bidsValue ) {
            $candidat->volume_status = 'ok';
        }

        $candidat->save();

        return [
            'type' => 'candidate',
            'order_id' => null,
            'pierce_price' => $pierce_price,
            'buy_price' =>$buy_price,
            'sell_price' =>$sell_price,
            'price_down_border' =>$bids_stop_range,
            'bids_volume' => $bidsValue,
            'asks_volume' => $asksValue,
            'volume_status' => $candidat->volume_status,
        ];
    }

    public function handleOrderBook($candidat, $depth, $addData) {
        
        $order_id = $addData['order_id'] ?? null;
        //dump(['handleOrderBook', $order_id]);
       
        $circle_id = $this->params['circle_id'];
        $symbol = $candidat->symbol;
        $candidate_id = $candidat->id;
         
        $now = Carbon::now();
        $insertData = $addData;
        $insertData['symbol'] = $symbol;
       
        $insertData['circle_id'] = $circle_id;
        $insertData['candidate_id'] = $candidate_id;

        $insertData['bid'] = json_encode($depth['bids']);
        $insertData['ask'] =  json_encode($depth['asks']);

        $insertData['created_at'] =  $now;
        $insertData['updated_at'] =  $now;

      
        if(!empty($order_id )) {
            dump(['!empty($order_id )', $order_id]);
            // $orderBook = OrderBook::updateOrCreate(
            //     ['order_id' => $order_id],
            //     $insertData,
            // );
            OrderBook::create($insertData);
        } else {
            OrderBook::create($insertData);
        }

    }
}
