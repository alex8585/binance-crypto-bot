<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Candidate as Candidate;
use \Carbon\Carbon as Carbon;
use App\Statistic as Statistic;
use App\Console\Commands\Includes\BotUtils  as BotUtils;
use App\Console\Commands\Includes\BinanceApi  as BinanceApi;
use App\Order as Order;
use App\Console\Commands\Includes\BinanceRequest;

class getStatistics extends Command
{
    use BotUtils;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get_statistics {candidates_ids?*}';

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
        $this->candidates = [];
    }



    public function getMaxPrices($statArr, $response)
    {

        $max = 0;
        foreach ($response as $candle) {
            if ($candle[2] >  $max) {
                $max = $candle[2];
            }
        }
        $statArr['max15'] =  $max;

        return $statArr;
    }




    public function addPercentsToStat($statArr)
    {
        $buy_price = $statArr['buy_price'];

        $statArr['percent15'] = round($this->calcPercents($statArr['max15'], $buy_price), 5);

        return $statArr;
    }
    /**
     * Execute the console command.
     *
     * @return mixed
     */



    public function handleStatistic()
    {
        $sub24Hours =  Carbon::now()->subHours(24);
        $sub10Minutes =  Carbon::now()->subMinutes(10);
        $now = Carbon::now();


        $candidatesIds = $this->argument('candidates_ids');


        \DB::transaction(function () use ($candidatesIds) {
            if ($candidatesIds) {
                $this->candidates = Candidate::where(
                    [
                        'is_pierced' => true,
                    ]
                )->whereIn('id', $candidatesIds)->lockForUpdate()->get();
            } else {
                $expiredTime =   Carbon::now()->subMinutes(15);
                $this->candidates = Candidate::where(['is_pierced' => true])->whereIn('status', ['order_placed', 'working'])
                    ->where(function ($q) use ($expiredTime) {
                        $q->where('stat_last_time', '<', $expiredTime)
                            ->orWhere('stat_last_time', null);
                    })
                    ->lockForUpdate()->get();
            }

            $now =  Carbon::now();

            foreach ($this->candidates as $candidat) {
                $candidat->stat_last_time = $now;
                $candidat->save();
            }
        });


        foreach ($this->candidates as $candidat) {
            if ($candidat->pierce_time < $sub24Hours) {
                $candidat->status = 'done';
                $candidat->save();
                continue;
            }




            $buyPrice = $candidat->pierce_price;
            $symbol = $candidat->symbol;
            $pierceTime = $candidat->pierce_time;
            $now =  Carbon::now();

            $candles = floor($pierceTime->diffInSeconds($now) / 60 / 15) + 1;



            //dump(($candles));

            $params = [
                'symbol' => $symbol,
                'interval' => '15m',
                'limit' => $candles
            ];




            $response = $this->binRequest->get('klines', $params);

            // dd(count($response));

            $statArr = [];
            $statArr['candidate_id'] = $candidat->id;
            $statArr['buy_price'] = $buyPrice;
            $statArr['symbol'] = $symbol;
            $statArr['created_at'] = $now;
            $statArr['updated_at'] = $now;

            $statArr = $this->getMaxPrices($statArr, $response);
            $statArr = $this->addPercentsToStat($statArr);

            Statistic::insert($statArr);
            // dd($statArr );


            if ($candidat->pierce_time > $sub10Minutes) {
                $candidat->max10minutes = $statArr['percent15'];
                $candidat->save();
            }
        }
    }



    public function handle()
    {
        $this->handleStatistic();
    }
}
