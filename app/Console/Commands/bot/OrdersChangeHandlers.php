<?php

namespace App\Console\Commands\bot;

use Illuminate\Console\Command;
use \Carbon\Carbon as Carbon;
use App\Candidate as Candidate;
use App\Symbol as Symbol;
use App\Order as Order;
use App\Balance as Balance;
use App\BalanceHistory as BalanceHistory;
use App\Option  as Option;
use App\Console\Commands\Includes\BotUtils  as BotUtils;
use App\Console\Commands\Includes\BinanceApi  as BinanceApi;
use App\Console\Commands\Includes\BinanceRequest;

class OrdersChangeHandlers extends Command
{
    use BotUtils;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders_change';

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

        $this->binApi =  new BinanceApi();
        $this->binRequest = new BinanceRequest();


        $this->symbols = Symbol::select(['symbol', 'min_lot_size'])->get()->toArray();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $balanceUpdate = function ($api, $balances) {
            $this->onBalanceUpdate($api, $balances);
        };

        $orderUpdate = function ($api, $report) {
            $this->onOrderUpdate($api, $report);
        };

        $this->binApi->userData($balanceUpdate, $orderUpdate);
        return 0;
    }

    public function onBalanceUpdate($api, $balances)
    {
        $this->updateBalances();
    }

    public function onOrderUpdate($api, $binOrder)
    {
        dump($binOrder);
        if ($binOrder['side'] == 'BUY') {
            if ($binOrder['executionType']  == "TRADE") {
                if ($binOrder['orderStatus'] == "FILLED") {
                    //$this->setSellLimitOrder($binOrder);
                }
            }
        }

        if ($binOrder['side'] == 'SELL') {
            if ($binOrder['executionType']  == "TRADE") {
                if ($binOrder['orderStatus'] == "FILLED") {
                    if ($binOrder['orderType'] != 'MARKET') {
                        //$this->completeOrder($binOrder);
                    }
                }
            }
        }

        if ($binOrder['orderStatus'] == "FILLED") {
            $this->updateBalances();
        }
    }
}
