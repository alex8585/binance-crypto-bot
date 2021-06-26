<?php

namespace App\Console\Commands;

use App\Circle;
use App\GreenCount;
use Illuminate\Console\Command;
use App\Console\Commands\Includes\BinanceApi;
//use App\GreenCount as GreenCount;

class getGreenCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get_green_count';

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

        $circle = Circle::orderBy('created_at', 'DESC')->first();


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

        GreenCount::create([
            'circle_id' => $circle->id,
            'cnt' => $greenCnt,
        ]);

        return 0;
    }
}
