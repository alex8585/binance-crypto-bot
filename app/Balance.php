<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Balance extends Model
{
    use Sortable;
    protected $fillable = ['symbol','available','on_order'];

    public $sortable = [
        'available',
        'symbol',
        'on_order',
        'created_at',
        'updated_at',
    ];

}
