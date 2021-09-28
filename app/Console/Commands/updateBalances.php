<?php

namespace App\Console\Commands;

use App\Order;
use App\Balance;
use \Carbon\Carbon;
use Illuminate\Console\Command;
use App\Console\Commands\Includes\BotUtils;

class updateBalances extends Command
{
    use BotUtils;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update_balances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update balances';

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
        $lastOrder = Order::latest()->first();
        $lastBalance = Balance::latest()->first();


        $isOrdersUpdated =  $lastOrder->updated_at->addMinutes(30)->gt(now());
        $isBalancesStaled = $lastBalance->updated_at->addMinutes(120)->lt(now());


        dump(['isOrdersUpdated', $isOrdersUpdated]);
        dump(['isBalancesStaled', $isBalancesStaled]);

        if ($isOrdersUpdated || $isBalancesStaled) {
            dump(['updateBalances']);
            $this->updateBalances();
        }


        return 0;
    }
}
