<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use App\Http\Traits\PercentsTrait as PercentsTrait;
class OrderBook extends Model
{
    use Sortable, PercentsTrait;

    //protected $fillable = ['order_id'];
    protected $guarded = ['id'];
    
    public $timestamps = true;
   
    protected $dates = ['created'];

    public $sortable = [
        'symbol'
    ];
    
    public $sortableAs = [
        'pp',
        'created'
    ];


    public function formatCurrency($val) {
       
        $fmt = new \NumberFormatter( 'en_US', \NumberFormatter::CURRENCY );
        $fmt->setSymbol(\NumberFormatter::CURRENCY_SYMBOL, '');
        return $fmt->formatCurrency(floatval($val), "USD");
    }


    public function getAsksVolume() {
       return  $this->formatCurrency($this->asks_volume);
    }

    public function getBidsVolume() {
        return  $this->formatCurrency($this->bids_volume);
       
    }

    public function getDiffVolume() {
        return round($this->calcPercents($this->bids_volume, $this->asks_volume), 5);
    }

    public function candidate()
    {
        return $this->belongsTo('App\Candidate');
    }

    public function circle()
    {
        return $this->belongsTo('App\Circle');
    }
}
