<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Order as Order;
use App\Console\Commands\Includes\BinanceApi  as BinanceApi;
use App\Symbol as Symbol;
use \Carbon\Carbon as Carbon;
use Illuminate\Support\Facades\Cache;
use App\Console\Commands\Includes\BotUtils;
use App\Console\Commands\Includes\BinanceRequest;

class test2 extends Command
{
    use BotUtils;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test2';

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

        $this->binRequest = new BinanceRequest();
        $this->binApi =  new BinanceApi();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //system('systemctl status orders_change');

        //dd('1111');
        //Cache::forget('balances2');
        //Cache::forget('balance_total2');
        //Cache::put('balances');
        //Cache::put('balance_total');
        // $balances = Cache::get('balances2', []);
        // $balance_total = Cache::get('balance_total2');
        // dump($balances);
        // dump($balance_total);


        //dd($b);

        // $balances = $this->binApi->balances();

        // foreach ($balances as $symbol => $balance) {
        //     if (($balance['available'] == 0) && ($balance['onOrder'] == 0)) continue;

        //     $insertData[$symbol] = [
        //         'symbol' => $symbol,
        //         'available' => $balance['available'],
        //         'on_order' => $balance['onOrder'],
        //     ];
        // }
        // $c = Cache::put('balances', $insertData);
        // dd($c);
    }
}
