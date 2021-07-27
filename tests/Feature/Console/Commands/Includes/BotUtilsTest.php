<?php

namespace Tests\Feature\Console\Commands\Includes;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Console\Commands\bot\OrdersChangeHandlers;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BotUtilsTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testCalcTotalBalancesPrice()
    {
        $this->testedClass = new OrdersChangeHandlers();
        $env = env('DB_DATABASE');
        dump($env);
        $balances =  [
            "USDT" => [
                "available" => "250",
                "on_order" => "10"
            ],
        ];
        $total = $this->testedClass->calcTotalBalancesPrice($balances);
        //dd($total);
        $this->assertSame($total, 260);
    }
}
