<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Console\Commands\Includes\BotUtils;
use App\Console\Commands\Includes\BinanceApi;
use Illuminate\Support\Facades\Cache;
use Kyslik\ColumnSortable\Sortable;

class BalanceHistory extends Model
{
    //protected $fillable = ['total','balances','created_at','updated_at'];
    use BotUtils;
    use Sortable;
    protected $guarded = [];

    public $sortable = [
        'updated_at'
    ];

    public $sortableAs = ['end_total'];

    protected $dates = [
        'start_created_at',
        'start_updated_at',
        'end_created_at',
        'end_updated_at',
    ];

    public function getTotalPercent($end_total = 0)
    {
        if (!$this->end_total && !$end_total) {
            return 0;
        }

        return round($this->calcPercents($this->end_total, $this->start_total), 5);
    }

    public function getEndTotalAttribute()
    {
        if (isset($this->attributes['end_total'])) {
            return $this->attributes['end_total'];
        }

        $this->binApi =  new BinanceApi();

        $balances = $this->getBalances();
        $total = $this->getTotalBalances($balances);

        return  $total;
    }
}
