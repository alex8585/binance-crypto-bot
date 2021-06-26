<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;


class Statistic extends Model
{
    use Sortable;
    
    public $sortable = [
        'buy_price',
        'symbol',
        'buy_price',
        'max15',
        'max30',
        'max60',
        'percent15',
        'percent30',
        'percent60',
        'created_at',
        'updated_at',
    ];
  

    public $sortableAs = [
       
        
    ];

    protected $fillable = ['symbol','circles_id'];
    
    
}
