<?php

namespace App;

use Kyslik\ColumnSortable\Sortable;
use Illuminate\Database\Eloquent\Model;
use App\BalanceHistory;

class Circle extends Model
{
    use Sortable;

    protected $fillable = ['hour'];

    public $sortable = ['hour', 'created_at'];

    public $sortableAs = [
        'cnt',
    ];

    // public function balance_history()
    // {
    //     //$now = Carbon::now();
    //     //$sub5Hours =  Carbon::now()->subHours(5);
    //     // return $this->hasMany(BalanceHistory::class)
    //     //     ->whereRaw('balance_histories.created_at > 1');
    // }
}
