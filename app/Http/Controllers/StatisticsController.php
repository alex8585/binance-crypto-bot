<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Candidate as Candidate;
use \Carbon\Carbon as Carbon;
use App\Statistic as Statistic;
use App\Circle as Circle;

class StatisticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $sub2m = Carbon::now()->subMonths(3);

        $elements = Circle::select(['*', 'id as cir_id'])
            ->where([['created_at', '>',  $sub2m]])
            ->sortable(['created_at' => 'desc'])
            ->addSelect(['cnt' => function ($query) {
                $query->select(\DB::raw('count(*)'))
                    ->from('candidates')
                    ->where('candidates.is_pierced', true)
                    ->whereColumn('circle_id', 'cir_id')
                    ->whereExists(function ($query) {
                        $query->from('candidates')->join(
                            'statistics',
                            'candidates.id',
                            '=',
                            'statistics.candidate_id'
                        )->whereColumn('circle_id', 'cir_id');
                    });
            }]);
        //dd($elements->get());
        $elements = $elements->paginate(100);

        return view('statistics.circles')->withElements($elements);
    }

    public function statistics(Request $request,   $id)
    {
        $type = $request->type ?? 'all';
        //dd($type);

        $circle = Circle::findOrFail($id);

        $elements = Candidate::select(['*', 'id as cand_id'])->where(
            [
                'circle_id' => $circle->id,
                'is_pierced' => true,
                //'max_price' > 0
            ]
        )
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


        $elements = $elements->with('TvTechnicals')->sortable(['created_at' => 'desc'])->paginate(100);

        return view('statistics.statistics', [
            'elements' => $elements,
            'circle_id' => $circle->id

        ]);
    }

    public function details(Request $request,   $id)
    {

        $candidate = Candidate::findOrFail($id);

        $elements = Statistic::select(['*'])->where(
            ['candidate_id' => $id]
        )->sortable(['created_at' => 'asc']);

        $elements = $elements->paginate(100);

        return view('statistics.details', [
            'elements' => $elements,
            'candidate' => $candidate

        ]);
    }
}
