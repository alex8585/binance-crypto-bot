<?php

namespace App\Console\Commands\etc;

use Illuminate\Console\Command;
use \Carbon\Carbon as Carbon;
use App\Candidate as Candidate;
use App\Symbol as Symbol;
use App\Option  as Option;
use App\Order  as Order;
use App\Console\Commands\Includes\BinanceApi  as BinanceApi;
class clearSymbols extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear_symbols';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->excludeSymbols = config('settings.binance.symbols_exclude');
        $this->binApi =  new BinanceApi();
        $this->quoteVolumeFilter = config('settings.binance.quote_volume_filter');
       
        $stats24 =  $this->binApi->prevDay();
        $symbols = Symbol::select(['id','symbol'])->pluck('symbol','id');


        $lessVolumeSymbols = [];
        foreach($symbols as $id=>$symbol) {
            foreach($stats24 as $stat) {
                if($stat['symbol'] == $symbol) {
                    if($stat['quoteVolume'] < ($this->quoteVolumeFilter) ) {
                        $lessVolumeSymbols[] = $symbol;
                    }
                }
            }
        }

        dump($lessVolumeSymbols);
       
        Candidate::where('symbol', 'like', '%DOWNUSDT')
        ->orWhere('symbol', 'like', '%UPUSDT')->orWhere(function($query) {
            $query->whereIn('symbol', $this->excludeSymbols);
        })->orWhere(function($query) use($lessVolumeSymbols){
            $query->whereIn('symbol', $lessVolumeSymbols);
        })->delete();



        Symbol::where('symbol', 'like', '%DOWNUSDT')
        ->orWhere('symbol', 'like', '%UPUSDT')->orWhere(function($query) {
            $query->whereIn('symbol', $this->excludeSymbols);
        })->orWhere(function($query) use($lessVolumeSymbols){
            $query->whereIn('symbol', $lessVolumeSymbols);
        })->delete();



        Order::where('symbol', 'like', '%DOWNUSDT')
        ->orWhere('symbol', 'like', '%UPUSDT')->orWhere(function($query) {
            $query->whereIn('symbol', $this->excludeSymbols);
        })->orWhere(function($query) use($lessVolumeSymbols){
            $query->whereIn('symbol', $lessVolumeSymbols);
        })->delete();

       


        return 0;
    }
}
