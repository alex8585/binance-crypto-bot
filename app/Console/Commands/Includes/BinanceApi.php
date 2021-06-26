<?php

namespace App\Console\Commands\Includes;

use App\Option  as Option;
use App\Console\Commands\Includes\BotUtils  as BotUtils;

class BinanceApi
{
    use BotUtils;

    public function __construct()
    {
        $this->key = $this->getOption('api_key');
        $this->secret = $this->getOption('api_secret');
        $this->api =  new NewBinanceApi($this->key, $this->secret);
    }

    public function placeBuyOrder($symbol, $quantity)
    {
        $result = $this->api->marketBuy($symbol, $quantity);
        return $result;
    }

    public function placeSellOrder($symbol, $quantity, $orderId)
    {
        dump(['orderId', $orderId]);
        $result = $this->api->marketSell($symbol, $quantity, ["newClientOrderId" => $orderId]);
        return $result;
    }

    public function placeSellLimitOrder($sumbol, $quantity, $price)
    {
        $result = $this->api->sell($sumbol, $quantity, $price);
        return  $result;
    }

    public function __call($function_name, $arguments)
    {
        return $this->api->{$function_name}(...$arguments);
    }
}
