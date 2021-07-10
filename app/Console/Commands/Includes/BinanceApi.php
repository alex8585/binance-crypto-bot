<?php

namespace App\Console\Commands\Includes;

use App\Option  as Option;
//use \ccxt\binance as CcxtApi;
use App\Console\Commands\Includes\NewBinanceApi;
use App\Console\Commands\Includes\BotUtils  as BotUtils;
use App\Console\Commands\Includes\BinanceRequest;


class BinanceApi
{
    use BotUtils;

    public function __construct()
    {
        $this->key = $this->getOption('api_key');
        $this->secret = $this->getOption('api_secret');
        $this->binRequest = new BinanceRequest();

        $binApi = new NewBinanceApi($this->key, $this->secret);

        $binTimeDiff = $this->binRequest->load_time_difference();
        $binApi->setTimeOffset($binTimeDiff);

        $this->api =  new \Binance\RateLimiter($binApi);
        // $this->ccxt = new CcxtApi(
        //     [
        //         'rateLimit' => 100,
        //         'options' => array(
        //             'adjustForTimeDifference' => true,
        //         ),
        //         'apiKey' => $this->key,
        //         'secret' => $this->secret,
        //     ]
        // );
    }

    public function placeBuyOrder($symbol, $quantity)
    {
        // $type = 'MARKET';
        //$params['type'] = 'future';
        // $side = 'buy';
        // return $this->ccxt->create_order($symbol, $type, $side, $quantity, null, $params = []);
        $result = $this->api->marketBuy($symbol, $quantity);
        return $result;
    }

    public function placeSellOrder($symbol, $quantity, $orderId)
    {
        dump(['orderId', $orderId]);

        // $type = 'MARKET';
        // $side = 'sell';
        // return $this->ccxt->create_order($symbol, $type, $side, $quantity, null, ["newClientOrderId" => $orderId]);

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
