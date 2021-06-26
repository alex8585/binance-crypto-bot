<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Console\Commands\Includes\BotUtils;
use App\Console\Commands\Includes\BinanceApi;
use \Carbon\Carbon;
use App\BalanceHistory;

class updateBalanceHistory extends Command
{
    use BotUtils;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update_balance_history';

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
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $balances = $this->getBinanceBalances();
        $total = $this->getTotalBalances($balances);

        $start = Carbon::now()->hour(0)->minute(0)->second(0);
        //$end = Carbon::now()->hour(23)->minute(59)->second(59);


        $insertData = [];
        $insertData[] = [
            'created_at' => $start,
            'updated_at' => $start,
            'type' => 'start',
            'total' => $total,
            'balances' => json_encode($balances),
        ];
        // $insertData[] = [
        //     'created_at' => $end,
        //     'updated_at' => $end,
        //     'type' => 'end',
        //     'total' => $total,
        //     'balances' => json_encode($balances),
        // ];

        dump('command updateBalanceHistory');
        BalanceHistory::insert($insertData);

        return 0;
    }
}
