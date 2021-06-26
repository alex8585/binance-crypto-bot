<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\TvTechnical;
use App\TvStatistic;
use App\TvCircle;

class TvStatisticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        //tv_statistics
        $elements = TvCircle::select(['*', 'id as c_id'])->sortable(['created_at' => 'desc'])
            ->addSelect(['cnt' => function ($query) {
                $query->select(\DB::raw('count(*)'))
                    ->from('tv_technicals')
                    ->whereColumn('circle_id', 'c_id');
            }]);
        $elements = $elements->paginate(100);

        return view('tvstatistics.technical')->withElements($elements);
    }

    public function statistics(Request $request,   $id)
    {
        //tv_statistics_details
        $tvCircle = TvCircle::findOrFail($id);

        $elements = TvTechnical::select(['*', 'id as technical_id'])->where('circle_id', $tvCircle->id)
            ->addSelect(['max' => function ($query) {
                $query->select('max')
                    ->from('tv_statistics')
                    ->whereColumn('tv_technical_id', 'technical_id')
                    ->orderBy('max', 'desc')
                    ->limit(1);
            }])->addSelect(['max_percent' => function ($query) {
                $query->select('max_percent')
                    ->from('tv_statistics')
                    ->whereColumn('tv_technical_id', '=', 'technical_id')
                    ->orderBy('max_percent', 'desc')
                    ->limit(1);
            }])->sortable(['created_at' => 'desc']);


        if ($request->sort) {
            if ($request->sort == 'max' || $request->sort == 'max_percent') {
                $elements->reorder($request->sort, $request->direction);
            }
        }

        if ($request->timeframe) {
            $elements->where('timeframe', $request->timeframe);
        }


        $elements = $elements->paginate(100);

        return view('tvstatistics.tvstatistics', [
            'elements' => $elements,
            'tvCircle' => $tvCircle

        ]);
    }

    public function details(Request $request,   $id)
    {

        $candidate = TvTechnical::findOrFail($id);


        $elements = TvStatistic::select(['*'])->where(
            ['tv_technical_id' => $id]
        )->sortable(['created_at' => 'asc']);

        $elements = $elements->paginate(100);

        return view('tvstatistics.details', [
            'elements' => $elements,
            'candidate' => $candidate

        ]);
    }
}
