<?php

namespace App\Console\Commands;

use App\Circle;
use App\Option;
//use App\GreenCount;
use App\Candidate;
use App\GreenCount;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Console\Commands\Includes\BotUtils;

class checkGreenAndStatisticCounts extends Command
{
    use BotUtils;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check_counts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'check_counts';

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

    private function updateGreen()
    {
        $circle = Circle::orderBy('created_at', 'DESC')->first();

        $greenCounts = GreenCount::select('created_at', 'cnt')
            ->where('circle_id', $circle->id)
            ->orderBy('created_at', 'DESC')->get();


        if (!$greenCounts->count()) {
            Option::updateOption('green_above_avg', 0);
            Option::updateOption('green_avg_cnt', 0);
            Option::updateOption('green_current_cnt', 0);
            return;
        }

        $current =  $greenCounts->first()->cnt;
        $average = $greenCounts->avg('cnt');
        $green_above_avg = ($current > $average);

        Option::updateOption('green_above_avg', $green_above_avg);
        Option::updateOption('green_avg_cnt', $average);
        Option::updateOption('green_current_cnt', $current);
        // dump($current);
        // dump($average);
        // dump($green_above_avg);
    }

    public function updateStatisticsCountStart()
    {
        $statistics_count_result = $this->getOption('statistics_count_result');

        if ($statistics_count_result) {
            return;
        }

        $statistics_count_trade_start = $this->getOption('statistics_count_trade_start');

        $sircle = Circle::select(['id'])->orderBy('created_at', 'DESC')->first();

        $cnt = Candidate::select(\DB::raw('count(*) as cnt'))
            ->where('candidates.is_pierced', true)
            ->where('circle_id', $sircle->id)
            ->first()->cnt;

        $statistics_count_result = ($cnt <= $statistics_count_trade_start);

        if ($statistics_count_result) {
            Log::channel('app')->info(['check_counts', 'statistics_count_result', '1']);
            Option::updateOption('statistics_count_result', 1);
        }
    }

    public function handle()
    {
        $this->updateGreen();
        $this->updateStatisticsCountStart();


        $result = $this->resetOptionsCash();
        if (!$result) {
            dump('check_counts resetOptionsCash error');
            Log::channel('app')->critical(['check_counts', 'resetOptionsCash']);
        }


        return 0;
    }
}
