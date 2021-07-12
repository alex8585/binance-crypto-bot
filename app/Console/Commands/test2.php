<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
//use App\Order as Order;
//use App\Console\Commands\Includes\BinanceApi  as BinanceApi;
//use App\Symbol as Symbol;
//use \Carbon\Carbon as Carbon;
//use Illuminate\Support\Facades\Cache;
use App\Console\Commands\Includes\BotUtils;
//use App\Console\Commands\Includes\BinanceRequest;

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



        // $this->binRequest = new BinanceRequest();
        // $this->binApi =  new BinanceApi();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $t = $this->getCacheTicker();
        dd($t);

        $diff =  [
            "BNB" =>  [
                "available" => "1",
                "onOrder" => "0.00000000"
            ],
            "USDT" => [
                "available" => "0",
                "onOrder" => "0.00000000"
            ],
            "LIT" => [
                "available" => "0.00000000",
                "onOrder" => "0.00000000"
            ]
        ];
        //dd('2');
        // $diff = [];
        $this->updateBalancesOnBalanceUpdate();
    }
}
