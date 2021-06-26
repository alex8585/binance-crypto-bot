<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Option extends Model
{
    protected $fillable = ['key', 'value'];

    // public static function getTicker24()
    // {
    //     $ticker24 = Cache::remember('ticker24', 9999, function () {
    //         return Option::select(['value'])->where('key', 'ticker24')->pluck('value')->first();
    //     });
    //     return  json_decode($ticker24, true);
    // }

    public static function updateOption($k, $v)
    {
        $o = Option::firstOrNew(['key' => $k]);
        $o->value =  $v;
        $o->save();
    }
}
