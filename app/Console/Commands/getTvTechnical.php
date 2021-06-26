<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Console\Commands\Includes\BinanceApi;
use App\Symbol;
use App\TvTechnical;
use App\TvCircle;
use \Carbon\Carbon;
use Symfony\Component\Process\Process;
use App\Console\Commands\Includes\BinanceRequest;

class getTvTechnical extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get_tv_technical';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'get TradingView Technical';

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
        $this->symbols = Symbol::select(['symbol', 'min_lot_size', 'data'])->get()->toArray();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $parcer_map = config('settings.binance.tv_parcer_map');
        $now = Carbon::now();

        $tvCircle = TvCircle::create(['hour' => $now->format('H')]);


        $cnt = 0;
        foreach ($this->symbols as $symbolArr) {
            $symbol = $symbolArr['symbol'];

            $start = microtime(true);
            $tvSymbol = $symbol;
            if (isset($parcer_map[$symbol])) {
                $tvSymbol = $parcer_map[$symbol];
            }

            if (!$tvSymbol) {
                continue;
            }

            $nodePathStr = exec('whereis node | cut -c 7-');
            $pieces = explode(" ", $nodePathStr);
            $nodePath = $pieces[0];
            //dd($pieces);
            if (!$nodePath) {
                $nodePath = '/usr/bin/node';
            }

            $process = new Process([
                $nodePath,
                base_path() . '/tv_parcer/start.js',
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
                if ($data["hour"] == "STRONG BUY" || $data["minute5"] == "STRONG BUY") {
                    $rawTicker = $this->binApi->prices();
                    $this->ticker = $this->binRequest->filterTickerSymbols($rawTicker, $this->symbols);
                    $cnt++;
                }


                if ($data["hour"] == "STRONG BUY") {
                    TvTechnical::create([
                        'symbol' => $symbol,
                        'timeframe' => 'hour',
                        'status' => 'run',
                        'indicator_value' => $data["hour"],
                        'start_price' => $this->ticker[$symbol],
                        'circle_id' => $tvCircle->id,
                    ]);
                }
                if ($data["minute5"] == "STRONG BUY") {
                    TvTechnical::create([
                        'symbol' => $symbol,
                        'timeframe' => 'minute5',
                        'status' => 'run',
                        'indicator_value' => $data["minute5"],
                        'start_price' => $this->ticker[$symbol],
                        'circle_id' => $tvCircle->id,
                    ]);
                }
                //if($cnt > 2) return;
            }

            dump([$symbol, microtime(true) - $start]);
        }
    }
}
