<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Console\Commands\Includes\BinanceApi;
use App\Option;
use Illuminate\Support\Facades\Cache;

class get24Change extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get_24_change';

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

        $ticker24 = $this->binApi->prevDay();

        $allCnt = 0;
        $greenCnt = 0;
        foreach ($ticker24 as $t) {
            $symbol = $t['symbol'];
            if (stripos($symbol, "USDT") > 0) {
                $allCnt++;
                if ($t['priceChangePercent'] > 0) {
                    $greenCnt++;
                }
            }
        }


        $greenPercent = $greenCnt / $allCnt * 100;

        $ticker24 = [
            'green_percent' => round($greenPercent, 2),
            'green_count' => $greenCnt,
            'all_cnt' => $allCnt,
        ];

        //dump( $ticker24 );

        $o = Option::firstOrNew(['key' => 'ticker24']);
        $o->value = json_encode($ticker24);
        $o->save();
        Cache::forget('ticker24');
    }
}
