<?php

namespace App\Console\Commands;

use App\Circle;
use App\Option;
use App\GreenCount;
use Illuminate\Console\Command;
use App\Console\Commands\Includes\BinanceApi;
//use App\GreenCount as GreenCount;
use App\Console\Commands\Includes\BotUtils;

class getGreenCount extends Command
{
    use BotUtils;
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

        $this->update_interval = $this->getOption('green_cnt_update_interval');
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $lastGreenCount = GreenCount::orderBy('created_at', 'DESC')->select('updated_at')->first()->updated_at;

        if ($lastGreenCount->addMinutes(5) < now()) {
            $this->updateGreenCounts();
        }

        $lastUpdate = Option::where('key', 'green_above_avg')->select('updated_at')->first()->updated_at;

        if ($lastUpdate->addMinutes($this->update_interval) < now()) {
            $this->updateGreen();
        }

        return 0;
    }

    private function updateGreen()
    {
        $circle = Circle::orderBy('created_at', 'DESC')->skip(1)
            ->first();

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

        //dd($average);
        Option::updateOption('green_above_avg', $green_above_avg);
        Option::updateOption('green_avg_cnt', $average);
        Option::updateOption('green_current_cnt', $current);
        $result = $this->resetOptionsCash();
        // dump($current);
        // dump($average);
        // dump($green_above_avg);
    }

    private function updateGreenCounts()
    {
        $circle = Circle::orderBy('created_at', 'DESC')->first();

        $ticker24 = $this->binApi->prevDay();

        $greenCnt = 0;
        foreach ($ticker24 as $t) {
            $symbol = $t['symbol'];
            if (stripos($symbol, "USDT") > 0) {
                if ($t['priceChangePercent'] > 0) {
                    $greenCnt++;
                }
            }
        }

        GreenCount::create([
            'circle_id' => $circle->id,
            'cnt' => $greenCnt,
        ]);
    }
}
