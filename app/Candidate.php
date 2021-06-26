<?php

namespace App;

use App\Circle;
use App\TvTechnical;
use \Carbon\Carbon as Carbon;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use Sortable;

    public $sortable = [
        'pierce_time',
        'symbol',
        'buy_price',
        'pierce_price',
    ];

    public $sortableAs = [
        'max_price',
        'max_percent'

    ];

    protected $fillable = ['symbol', 'candles', 'yesterday_max_price', 'circle_id', 'status'];

    protected $dates = ['pierce_time'];

    public function sircle()
    {
        return $this->belongsTo(Circle::class, 'circle_id');
    }
    public function TvTechnicals()
    {
        return $this->hasMany(TvTechnical::class, 'symbol', 'symbol');
    }

    // public function befor_tehnical_1h()
    // {
    //     return $this->TvTechnicals()
    //         ->where('timeframe', 'hour')
    //         ->where('tv_technicals.created_at', '<', $this->pierce_time)
    //         ->where('tv_technicals.created_at', '>', $this->pierce_time->subHours(1))
    //         ->orderByDesc('tv_technicals.created_at')->limit(1);
    // }

    public function getBeforeTehnical1mNewAttribute()
    {
        if (!isset($this->TvTechnicals)) return null;

        $result = $this->TvTechnicals->filter(function ($tv, $key) {
            if ($tv->timeframe == 'hour') {
                if ($tv->created_at->lt($this->pierce_time)) {
                    if ($tv->created_at->gt($this->pierce_time->subHours(1))) {
                        return true;
                    }
                }
            }
        });
        $result = $result->sortByDesc(
            function ($elem, $key) {
                return $elem['created_at']->timestamp;
            }
        );
        return $result->first();
    }

    // public function before_tehnical_5m()
    // {
    //     return $this->TvTechnicals()
    //         ->where('timeframe', 'minute5')
    //         ->where('tv_technicals.created_at', '<', $this->pierce_time)
    //         ->where('tv_technicals.created_at', '>', $this->pierce_time->subHours(1))
    //         ->orderByDesc('tv_technicals.created_at')->limit(1);
    // }

    public function getBeforeTehnical5mNewAttribute()
    {
        if (!isset($this->TvTechnicals)) return null;

        $result = $this->TvTechnicals->filter(function ($tv, $key) {
            if ($tv->timeframe == 'minute5') {
                if ($tv->created_at->lt($this->pierce_time)) {
                    if ($tv->created_at->gt($this->pierce_time->subHours(1))) {
                        return true;
                    }
                }
            }
        });
        $result = $result->sortByDesc(
            function ($elem, $key) {
                return $elem['created_at']->timestamp;
            }
        );
        // foreach ($result as $r) {
        //     echo $r->created_at->format('d-m-Y H:i') . PHP_EOL . '<br>';
        // }

        return $result->first();
    }




    // public function after_tehnical_1h()
    // {
    //     return $this->TvTechnicals()
    //         ->where('timeframe', 'hour')
    //         ->where('tv_technicals.created_at', '>', $this->pierce_time)
    //         ->where('tv_technicals.created_at', '<', $this->pierce_time->addHours(1))
    //         ->orderBy('tv_technicals.created_at')->limit(1);
    // }

    public function getAfterTehnical1hNewAttribute()
    {
        if (!isset($this->TvTechnicals)) return null;

        $result = $this->TvTechnicals->filter(function ($tv, $key) {
            if ($tv->timeframe == 'hour') {
                if ($tv->created_at->gt($this->pierce_time)) {
                    if ($tv->created_at->lt($this->pierce_time->addHours(1))) {
                        return true;
                    }
                }
            }
        });

        $result = $result->sortBy(
            function ($elem, $key) {
                return $elem['created_at']->timestamp;
            }
        );

        return $result->first();
    }

    // public function after_tehnical_5m()
    // {
    //     return $this->TvTechnicals()
    //         ->where('timeframe', 'minute5')
    //         ->where('tv_technicals.created_at', '>', $this->pierce_time)
    //         ->where('tv_technicals.created_at', '<', $this->pierce_time->addHours(1))
    //         ->orderBy('tv_technicals.created_at')->limit(1);
    // }

    public function getAfterTehnical5mNewAttribute()
    {

        if (!isset($this->TvTechnicals)) return null;

        $result = $this->TvTechnicals->filter(function ($tv, $key) {
            if ($tv->timeframe == 'minute5') {
                if ($tv->created_at->gt($this->pierce_time)) {
                    if ($tv->created_at->lt($this->pierce_time->addHours(1))) {
                        return true;
                    }
                }
            }
        });

        $result = $result->sortBy(
            function ($elem, $key) {
                return $elem['created_at']->timestamp;
            }
        );

        return $result->first();
    }
}
