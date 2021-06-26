<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use App\Http\Traits\PercentsTrait as PercentsTrait;

class Order extends Model
{
    use Sortable, PercentsTrait;

    protected $guarded = [];

    public $sortable = [
        'buy_price',
        'symbol',
        'buy_price',
        'created_at',
        'updated_at',
        'stop_price1',
        'stop_price2',
        'buy_time',
        'sell_time',
        'sell_price'
    ];

    public $sortableAs = [
        'sell_percent',
        'order_hour',
    ];

    protected $dates = ['buy_time', 'sell_time', 'averaged_at'];

    public function getCurrentPercent($cur)
    {
        return round($this->calcPercents($cur, $this->buy_price), 5);
    }


    public function getSellPercentAttribute()
    {
        return round($this->calcPercents($this->sell_price, $this->buy_price), 5);
    }

    // public function getCurPercentAttribute() {
    //     return round($this->calcPercents($this->stop_price1, $this->buy_price), 5);

    // }
}
