<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Order as Order;
use \Carbon\Carbon as Carbon;
use App\Candidate as Candidate;
use App\Jobs\ProcessGetOrderBook;
use App\Option  as Option;
use App\Console\Commands\Includes\BotUtils  as BotUtils;
use App\Console\Commands\Includes\BinanceApi  as BinanceApi;
use App\Symbol as Symbol;
use App\OrderBook as OrderBook;
use App\TvTechnical;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use App\Console\Commands\Includes\BinanceRequest;

class test extends Command
{
    use BotUtils;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test3';

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
        $this->binApi =  new BinanceApi();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        dd('1');

        $parcer_map = config('settings.binance.tv_parcer_map');


        $symbols = Symbol::select('symbol')->pluck('symbol')->toArray();

        foreach ($symbols as $symbol) {
            $start = microtime(true);
            $tvSymbol = $symbol;
            if (isset($parcer_map[$symbol])) {
                $tvSymbol = $parcer_map[$symbol];
            }

            if (!$tvSymbol) {
                continue;
            }

            $nodePath = exec('whereis node | cut -c 7-');
            if (!$nodePath) {
                $nodePath = '/home/alex/.nvm/versions/node/v8.15.0/bin/node';
            }

            $process = new Process([
                $nodePath,
                '/home/alex/projects/statistics/tv_parcer/start.js',
                $tvSymbol
            ]);

            $process->run();

            if (!$process->isSuccessful()) {
                foreach ($process as $type => $data) {
                    if ($process::OUT === $type) {
                        $result = json_decode(json_decode($data));
                    } else {
                        echo "\nRead from stderr: " . $data;
                    }
                }
                return;
            }



            $result = $process->getOutput();
            $data = json_decode($result, TRUE);

            if (isset($data['error'])) {
                dump($data);
                continue;
            } else {
                if ($data["hour"] == "STRONG BUY") {
                    TvTechnical::create([
                        'symbol' => $symbol,
                        'timeframe' => 'hour',
                        'status' => 'run',
                        'indicator_value' => $data["hour"],
                    ]);
                    dd();
                }
                if ($data["minute5"] == "STRONG BUY") {
                    TvTechnical::create([
                        'symbol' => $symbol,
                        'timeframe' => 'minute5',
                        'status' => 'run',
                        'indicator_value' => $data["minute5"],
                    ]);
                    dd();
                }


                // $tt = TvTechnical::firstOrCreate(
                //     ['symbol' => $symbol],
                //     [
                //         'oscillators' => json_encode($data['oscillators']), 
                //         'summary' => json_encode($data['summary']), 
                //         'moving_averages' => json_encode($data['moving_averages']), 
                //         'summary_status' =>$data['summary']['txt'],
                //     ]
                // );

            }

            dump([$symbol, microtime(true) - $start]);
        }
    }
    //SELECT * FROM `orders` WHERE `order_result` <> '0' AND `status` = 'losted'

    //STRONG BUY

    // array:2 [
    //     "symbol" => "linkusdt"
    //     "error" => "page not found"
    //   ]
    //   array:2 [
    //     "symbol" => "lendusdt"
    //     "error" => "page not found"
    //   ]
    //   array:2 [
    //     "symbol" => "btcusdt"
    //     "error" => "page not found"
    //   ]


    //complete
    //losted
    // SELECT * FROM `orders`  WHERE `sell_time` IS NULL


    // UPDATE `orders`
    //SET `status` = 'losted'
    //WHERE `sell_time` IS NULL ; 

    //UPDATE `orders`
    //SET `status` = 'complete'
    //WHERE `order_result` <> '0' AND `status` = 'losted'
}
