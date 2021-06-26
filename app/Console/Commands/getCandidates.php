<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use \Carbon\Carbon as Carbon;
use App\Candidate as Candidate;
use App\Symbol as Symbol;
use App\Circle as Circle;
use App\Console\Commands\Includes\BinanceApi;
use App\Console\Commands\Includes\BinanceRequest;

class getCandidates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get_candidates';

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
    }



    public function searchPrice($symbol, $prices)
    {
        foreach ($prices as $priceObj) {
            if ($priceObj->symbol == $symbol) {
                return $priceObj->price;
            }
        }
    }


    public function getDaysCandles($response)
    {
        $candels = array_chunk($response, 24);

        $daysCandles =  [];
        foreach ($candels as $day => $candlesArr) {
            $firstElem = reset($candlesArr);
            $dayElem = [];
            $dayMax = $firstElem[2];
            $dayMin = $firstElem[3];

            foreach ($candlesArr as $candle) {
                if ($candle[2] > $dayMax) {
                    $dayMax = $candle[2];
                }

                if ($candle[3] < $dayMin) {
                    $dayMin = $candle[3];
                }
            }
            $dayElem['max'] = $dayMax;
            $dayElem['min'] = $dayMin;
            $dayElem['range']  = $dayMax - $dayMin;
            $daysCandles[] = $dayElem;
        }
        return $daysCandles;
    }

    public function checkIsCandidateByRange($daysCandles, $lastDayCandle)
    {
        $isCandidate = true;

        foreach ($daysCandles as $candle) {
            if ($lastDayCandle['range'] > $candle['range']) {
                $isCandidate = false;
                break;
            }
        }
        return $isCandidate;
    }



    public function checkSymbol($symbol, $now, $circleId)
    {



        $params = [
            'symbol' => $symbol,
            'interval' => '1h',
            'limit' => 97
        ];

        $response = $this->binRequest->get('klines', $params);

        if (count($response) < 97) return false;


        $currenHourCandle = array_pop($response);

        $daysCandles = $this->getDaysCandles($response);

        $yesterdayCandle = array_pop($daysCandles);


        $isCandidate = $this->checkIsCandidateByRange($daysCandles, $yesterdayCandle);

        if ($isCandidate) {
            $isCandidate = $this->checkIsCandidateByCurrentPrice($symbol, $yesterdayCandle['max']);
        }

        if ($isCandidate) {
            $newCandidate['status'] = 'working';
            $newCandidate['circle_id'] = $circleId;
            $newCandidate['symbol'] = $symbol;
            $newCandidate['created_at'] = $now;
            $newCandidate['updated_at'] = $now;
            $newCandidate['candles'] = '';
            $newCandidate['yesterday_max_price'] =   $yesterdayCandle['max'];

            $candidate = Candidate::create($newCandidate);
        }
    }

    public function checkIsCandidateByCurrentPrice($symbol, $yesterdayCandleMax)
    {
        $currentPrice = $this->searchPrice($symbol, $this->prices);
        if ($yesterdayCandleMax >= $currentPrice) {
            return true;
        }
        return false;
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->quoteVolumeFilter = config('settings.binance.quote_volume_filter');
        $this->binApi =  new BinanceApi();
        $this->prices = $this->binRequest->getBinancePrices();
        $now = Carbon::now();
        $symbols = Symbol::select('symbol')->pluck('symbol')->toArray();
        $this->stats24 =  $this->binApi->prevDay();


        $circle = Circle::create([
            'hour' => $now->format('H'),
        ]);

        foreach ($symbols as $symbol) {

            if (!$this->isQuoteVolumeOk($symbol)) {
                continue;
            }

            $this->checkSymbol($symbol, $now, $circle->id);
        }
    }

    public function isQuoteVolumeOk($symbol)
    {
        $quoteVolume = $this->getQuoteVolume($symbol);
        return $quoteVolume > $this->quoteVolumeFilter;
    }



    public function getQuoteVolume($symbol)
    {
        foreach ($this->stats24 as $stat) {
            if ($stat['symbol'] == $symbol) {
                return $stat['quoteVolume'];
            }
        }
    }
}
