<?php

namespace App\Http\Controllers;

use App\Symbol;
use App\Candidate;
use App\TvTechnical;
use App\BalanceHistory;
use App\Order as Order;
use App\Balance as Balance;
use Illuminate\Http\Request;
use \Carbon\Carbon as Carbon;
use Illuminate\Database\Eloquent\Builder;
use App\Console\Commands\Includes\BotUtils;
use App\Console\Commands\Includes\BinanceApi;
use App\Console\Commands\Includes\BinanceRequest;

class TopCandidatesController extends Controller
{
    use BotUtils;

    public function __construct()
    {
        $this->middleware('auth');
        $this->binRequest = new BinanceRequest();
        $this->binApi =  new BinanceApi();
    }


    public function index(Request $request)
    {
        $type = $request->type ?? 'all';

        $elements = Candidate::select(['*', 'id as cand_id'])->where('is_pierced', true)
            ->where('max10minutes', '>', 1)

            ->addSelect(['max_price' => function ($query) {
                $query->select('max15')
                    ->from('statistics')
                    ->whereColumn('candidate_id', 'cand_id')
                    ->orderBy('max15', 'desc')
                    ->limit(1);
            }])->addSelect(['max_percent' => function ($query) {
                $query->select('percent15')
                    ->from('statistics')
                    ->whereColumn([['candidate_id', '=', 'cand_id']])
                    ->orderBy('percent15', 'desc')
                    ->limit(1);
            }])->whereExists(function ($query) {
                $query->select(\DB::raw(1))
                    ->from('statistics')
                    ->whereColumn([['statistics.candidate_id', '=', 'candidates.id']]);
            });

        if ($type == 'with_tv') {
            $elements->whereExists(function ($query) {
                $query->select(\DB::raw(1))->from('tv_technicals')
                    ->whereColumn('tv_technicals.symbol', 'candidates.symbol')
                    ->whereRaw("tv_technicals.created_at BETWEEN DATE_SUB(candidates.pierce_time, INTERVAL 1 HOUR)
                            AND DATE_ADD(candidates.pierce_time, INTERVAL 1 HOUR)");
            });
        }



        $elements = $elements->with([
            'TvTechnicals' => function ($query) {
                $query->select('symbol', 'timeframe', 'created_at');
            }
        ])->sortable(['created_at' => 'desc'])->paginate(50);

        //dd($elements->toArray());

        // $elementsIds = $elements->getCollection()->pluck('symbol');

        // $tv = TvTechnical::whereIN('candidates.symbol', $elementsIds)->select([
        //     'tv_technicals.symbol',
        //     'tv_technicals.timeframe',
        //     'tv_technicals.created_at',
        // ])->join(
        //     'candidates',
        //     'tv_technicals.symbol',
        //     '=',
        //     'candidates.symbol'
        // )->whereRaw("tv_technicals.created_at BETWEEN DATE_SUB(candidates.pierce_time, INTERVAL 1 HOUR)
        //                     AND DATE_ADD(candidates.pierce_time, INTERVAL 1 HOUR)")
        //     ->get();

        // $elements->getCollection()->transform(function ($candidate) use ($tv) {
        //     $symbol = $candidate->symbol;
        //     $TvTechnicals = $tv->filter(function ($tv, $key) use ($symbol) {
        //         if ($tv->symbol == $symbol) {
        //             return true;
        //         }
        //         return false;
        //     });
        //     $candidate->TvTechnicals = $TvTechnicals;
        //     return $candidate;
        // });



        return view('top.index', [
            'elements' => $elements,
        ]);
    }
}
