<?php
namespace App\Http\Traits;

trait PercentsTrait {
 
  public function calcPercents($a, $b) {
      return ($a - $b)/$b*100;
  }
 
}