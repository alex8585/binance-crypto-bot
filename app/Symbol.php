<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Symbol extends Model
{
    protected $fillable = ['symbol','data','min_lot_size'];
}
