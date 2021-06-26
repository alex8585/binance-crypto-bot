<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use \Carbon\Carbon;
use App\Console\Commands\Includes\BotUtils;
use App\TvTechnical;
use App\TvStatistic;
use App\Console\Commands\Includes\BinanceRequest;

class getTvStatistics extends Command
{
    use BotUtils;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get_tv_statistics';

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
        $statArr['max'] =  $max;

        return $statArr;
    }

    public function addPercentsToStat($statArr)
    {
        $buy_price = $statArr['buy_price'];

        $statArr['max_percent'] = round($this->calcPercents($statArr['max'], $buy_price), 5);

        return $statArr;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {


        $globalNow = Carbon::now();

        $tf = 5;
        $expiredTime =   Carbon::now()->subMinutes($tf);
        $candidates = TvTechnical::where(['status' => 'run'])->where(function ($q) use ($expiredTime) {
            $q->where('stat_last_time', '<', $expiredTime)
                ->where('last_stat_tf', '<', 3)
                ->orWhere('stat_last_time', null);
        })->get();

        $this->handleTvStatistic($candidates,  $globalNow, $tf);



        $tf = 15;
        $expiredTime =   Carbon::now()->subMinutes($tf);
        $candidates = TvTechnical::where(['status' => 'run'])->where(function ($q) use ($expiredTime) {
            $q->where('stat_last_time', '<', $expiredTime)
                ->where('last_stat_tf', '>=', 3);
        })->get();

        $this->handleTvStatistic($candidates,  $globalNow, $tf);
    }


    public function handleTvStatistic($candidates,  $globalNow, $tf)
    {
        $sub6Hours =  Carbon::now()->subHours(6);
        foreach ($candidates as $candidat) {
            $candidat->stat_last_time = $globalNow;
            $candidat->last_stat_tf = $candidat->last_stat_tf + 1;
            $candidat->save();

            if ($candidat->created_at < $sub6Hours) {
                $candidat->status = 'done';
                $candidat->save();
                continue;
            }

            $symbol = $candidat->symbol;
            $createdAt = $candidat->created_at;
            $now =  Carbon::now();

            $candles = floor($createdAt->diffInSeconds($now) / 60 / $tf) + 1;
            $params = [
                'symbol' => $symbol,
                'interval' => "{$tf}m",
                'limit' => $candles
            ];


            $response = $this->binRequest->get('klines', $params);

            $statArr = [];
            $statArr['tv_technical_id'] = $candidat->id;
            $statArr['buy_price'] = $candidat->start_price;
            $statArr['symbol'] = $symbol;
            $statArr['created_at'] = $now;
            $statArr['updated_at'] = $now;

            $statArr = $this->getMaxPrices($statArr, $response);
            $statArr = $this->addPercentsToStat($statArr);

            TvStatistic::insert($statArr);
        }
    }
}
