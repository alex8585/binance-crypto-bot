<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Balance;
use \Carbon\Carbon;
use App\BalanceHistory;
use App\Order;
use App\Http\Traits\PercentsTrait;
use App\Circle;
use Illuminate\Database\Eloquent\Collection;
use App\Console\Commands\Includes\BinanceApi;
use App\Console\Commands\Includes\BinanceRequest;
use App\Console\Commands\Includes\BotUtils;
use App\Symbol as Symbol;

class BalancesHistoryController extends Controller
{

    use BotUtils;

    public function __construct()
    {
        $this->middleware('auth');
        $this->binRequest = new BinanceRequest();
        $this->binApi =  new BinanceApi();
    }

    public function index()
    {
        $rawTicker = $this->binApi->prices();
        $this->symbols = Symbol::select(['symbol', 'min_lot_size', 'data'])->get()->toArray();
        $this->ticker = $this->binRequest->filterTickerSymbols($rawTicker, $this->symbols);

        $this->updateBalances();

        $historys  = BalanceHistory::select([

            'balance_histories.id as start_id',
            'balance_histories.created_at as start_created_at',
            'balance_histories.updated_at as start_updated_at',
            'balance_histories.total as start_total',

            'histories2.id as end_created_id',
            'histories2.created_at as end_created_at',
            'histories2.updated_at as end_updated_at',
            'histories2.total as end_total',
        ])->where('balance_histories.type',  'start')
            ->leftJoin('balance_histories as histories2', function ($join) {
                $join->on('histories2.created_at', '>', 'balance_histories.created_at')
                    ->where('histories2.type', 'start')
                    ->whereRaw(
                        "histories2.created_at < DATE_ADD(balance_histories.created_at ,INTERVAL '25' HOUR)"
                    );
            })->sortable(['updated_at' => 'desc'])
            ->paginate(10);

        //dd($historys);

        return view('balances_history.index', [
            'elements' =>  $historys,
        ]);
    }


    // public function old_index(Request $request)
    // {

    //     $circlesDates = Circle::select('created_at')
    //         ->where('hour', 23)
    //         ->orderBy('created_at')
    //         ->paginate(10)
    //         ->pluck('created_at');

    //     $dates = new Collection(); //23:59:59
    //     foreach ($circlesDates as $circleDate) {
    //         $day = $circleDate->format('d-m-Y');
    //         $dates->push([
    //             'day' => $day,
    //             'start' => Carbon::createFromFormat('d-m-Y',  $day)->setTime(0, 0, 0),
    //             'end' => Carbon::createFromFormat('d-m-Y',  $day)->setTime(23, 59, 59),
    //         ]);
    //     }

    //     $firstDateStart = $dates->first()['start'];
    //     $lastDateEnd = $dates->last()['end'];

    //     //dd($lastDateEnd);

    //     $historys  = BalanceHistory::where('created_at', '>', $firstDateStart)
    //         ->where('created_at', '<', $lastDateEnd)->get();

    //     $dates = $dates->toArray();
    //     foreach ($dates as &$date) {
    //         foreach ($historys as $history) {
    //             if ($history->created_at->gt($date['start']) && $history->created_at->lt($date['end'])) {
    //                 $date['history'][] = $history;
    //             }
    //         }
    //     }
    //     unset($date);

    //     dd($dates);

    //     return view('balances_history.index', [
    //         'elements' =>  $history,
    //     ]);
    // }

    // private function getLastTotal()
    // {
    //     $total = BalanceHistory::select(['total'])->orderBy('created_at', 'DESC')->first()->total;
    //     return  $total;
    // }



}
