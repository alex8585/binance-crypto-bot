<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
class TvStatistic extends Model
{
    use Sortable;

    public $sortable = [
        'symbol',
        'tv_technical_id',
        'buy_price',
        'max',
        'max_percent',
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'symbol',
        'tv_technical_id',
        'buy_price',
        'max',
        'max_percent',
    ];
}
