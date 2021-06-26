<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
class TvTechnical extends Model
{
    use Sortable;

    public $sortable = [
        'symbol',
        'timeframe',
        'status',
        'stat_last_time',
        'indicator_value',
        'start_price',
        'created_at',
        'updated_at',
        'circle_time',
        'circle_id',
    ];

    protected $fillable = [
        'symbol',
        'timeframe',
        'status',
        'stat_last_time',
        'indicator_value',
        'start_price',
        'circle_time',
        'circle_id'
    ];
}
