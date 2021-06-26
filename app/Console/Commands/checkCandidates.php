<?php

namespace App\Console\Commands;

use Artisan;
use App\Circle;
use App\Candidate;
use App\Order as Order;
use App\Symbol as Symbol;
use App\Option  as Option;
use \Carbon\Carbon as Carbon;
use Illuminate\Console\Command;
use App\Jobs\ProcessGetStatistics;
use Illuminate\Support\Facades\Log;
use App\Console\Commands\Includes\BinanceRequest;
use App\Console\Commands\Includes\BotUtils  as BotUtils;

class checkCandidates extends Command
{
    use BotUtils;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check_candidates';

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

        $this->buy_gap_percent = $this->getOption('buy_gap_percent');

        $this->trade_exclude = config('settings.binance.trade_exclude');
    }





    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {


        $output = shell_exec('ps -aux | grep [o]rders_open');
        if ($output) {

            return 0;
        }

        dump('work');
        $this->prices = $this->binRequest->getBinancePrices();

        // $severTime = $this->binRequest->get('time')->serverTime;
        // $now = Carbon::createFromTimestampMs($severTime );

        $now = Carbon::now();
        $sub5Hours =  Carbon::now()->subHours(5);


        //$candidates = Candidate::where(['is_pierced'=>false])->whereIn('status',['order_placed','working'])->get();

        $candidates = Candidate::where(['is_pierced' => false, 'status' => 'working', 'order_id' => 0])
            ->whereNull('error')->get();


        $candidates_ids = [];

        foreach ($candidates as $candidat) {
            if ($candidat->created_at < $sub5Hours) {
                $candidat->status = 'expired';
                $candidat->save();
                continue;
            }

            $symbol = $candidat->symbol;
            $currentPrice = $this->searchPrice($symbol, $this->prices);
            $isPierced = $this->checkCandidate($candidat, $currentPrice);

            if (in_array($symbol, $this->trade_exclude)) {
                continue;
            }

            if ($isPierced) {
                $yesterdayMaxChanged = $candidat->yesterday_max_price - $candidat->yesterday_max_price * $this->buy_gap_percent / 100;
                $candidat->is_pierced = true;
                $candidat->pierce_time = $now;
                $candidat->pierce_price = $yesterdayMaxChanged;
                $candidat->save();
                $candidates_ids[] = $candidat->id;
            }
        }

        $this->updateStatisticsCountStop();

        if ($candidates_ids) {
            ProcessGetStatistics::dispatch($candidates_ids)->onQueue('statistics');
        }

        return 0;
    }

    public function searchPrice($symbol, $prices)
    {
        foreach ($prices as $priceObj) {
            if ($priceObj->symbol == $symbol) {
                return $priceObj->price;
            }
        }
    }

    public function checkCandidate($candidat, $currentPrice)
    {
        $yesterdayMaxChanged = $candidat->yesterday_max_price - $candidat->yesterday_max_price * $this->buy_gap_percent / 100;
        if ($currentPrice > $yesterdayMaxChanged) {
            return true;
        }
        return false;
    }

    public function updateStatisticsCountStop()
    {
        $statistics_count_result = $this->getOption('statistics_count_result');

        if (!$statistics_count_result) {
            return;
        }

        //'statistics_count_trade_stop',
        //'statistics_count_trade_start',
        $statistics_count_trade_stop = $this->getOption('statistics_count_trade_stop');

        $sircle = Circle::select(['id'])->orderBy('created_at', 'DESC')->first();

        $cnt = Candidate::select(\DB::raw('count(*) as cnt'))
            ->where('candidates.is_pierced', true)
            ->where('circle_id', $sircle->id)
            ->first()->cnt;


        $statistics_count_result = ($cnt <= $statistics_count_trade_stop);


        if ($statistics_count_result) {
            return;
        }

        Option::updateOption('statistics_count_result', 0);
        Log::channel('app')->info(['check_candidates', 'statistics_count_result', '0']);

        $result = $this->resetOptionsCash();
        if (!$result) {
            dump('check_candidates resetOptionsCash error');
            Log::channel('app')->critical(['check_candidates', 'resetOptionsCash']);
        }
    }
}
