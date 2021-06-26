<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use \Carbon\Carbon as Carbon;
use App\Symbol as Symbol;
use App\Console\Commands\Includes\BinanceRequest;

class getSymbols extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get_symbols';

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
        //$this->prices = $this->binRequest->getBinancePrices();

    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $now = Carbon::now();
        $symbols = $this->binRequest->exchangeInfoSymbols();

        foreach ($symbols as $symbol) {


            $newElem['created_at'] = $now;
            $newElem['updated_at'] = $now;
            $newElem['symbol'] = $symbol->symbol;
            $newElem['data'] = json_encode($symbol);
            $newElem['min_lot_size'] = $this->getMinLotSIze($symbol);

            Symbol::firstOrCreate(['symbol' => $newElem['symbol']], $newElem);
        }
    }

    public function getMinLotSIze($symbol)
    {
        foreach ($symbol->filters as $f) {
            if ($f->filterType == "LOT_SIZE") {
                return $f->minQty;
            }
        }
    }
}
