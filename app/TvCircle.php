<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class TvCircle extends Model
{
    use Sortable;

    protected $fillable = ['hour'];

    public $sortable = ['hour','created_at'];

    public $sortableAs = [
        'cnt',
    ];
}
