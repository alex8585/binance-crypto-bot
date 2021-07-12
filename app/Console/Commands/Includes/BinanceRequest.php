<?php


namespace App\Console\Commands\Includes;

use App\Symbol as Symbol;

class BinanceRequest
{
    public function __construct()
    {
        $this->symbols = [];
        $this->excludeSymbols = config('settings.binance.symbols_exclude');

        $this->key = config('settings.binance.api_key');
        //$this->secret = config('settings.binance.api_secret');
    }

    // public function nonce()
    // {
    //     return $this->milliseconds() - $this->options['timeDifference'];
    // }

    public static function milliseconds()
    {
        list($msec, $sec) = explode(' ', microtime());
        return $sec . substr($msec, 2, 3);
    }

    public function load_time_difference($params = array())
    {
        $serverTime = $this->fetch_time($params);
        $after = $this->milliseconds();
        $timeDifference =  $after - $serverTime;
        return  $timeDifference;
    }

    public function fetch_time($params = array())
    {
        $method = 'time';
        $response = $this->get($method, $params);
        if (isset($response->serverTime)) {
            return $response->serverTime;
        }
        return null;
    }

    public function get($endpoint, $params = [])
    {
        $apiUrl = config('settings.binance.api_url');
        $baseUrl = "{$apiUrl}v3/{$endpoint}";


        $qs = http_build_query($params);
        $url = "{$baseUrl}?{$qs}";


        $client = new \GuzzleHttp\Client(['http_errors' => false]);
        $res = $client->request('GET', $url);
        $response =  json_decode($res->getBody());
        return $response;
    }

    public function post($endpoint, $params = [])
    {
        $apiUrl = config('settings.binance.api_url');
        $baseUrl = "{$apiUrl}v3/{$endpoint}";


        $qs = http_build_query($params);
        $url = "{$baseUrl}?{$qs}";


        $client = new \GuzzleHttp\Client(['http_errors' => false]);
        $res = $client->request('POST', $url, ['headers' => [
            'X-MBX-APIKEY' => $this->key,
        ]]);
        $response =  json_decode($res->getBody());
        return $response;
    }

    // public function getlistenKey() {
    //     $r = $this->post('userDataStream');
    //     return $r->listenKey;
    // }


    public function getDbSumbols()
    {
        if ($this->symbols) return;
        $this->symbols = Symbol::select('symbol')->pluck('symbol')->toArray();
    }

    public function isExclude($symbol)
    {
        return in_array($symbol, $this->excludeSymbols);
    }



    public function usdtFilterPrices($priceElem)
    {
        if ((stripos($priceElem->symbol, "USDT") > 0) &&  in_array($priceElem->symbol, $this->symbols)) {
            if (stripos($priceElem->symbol, "DOWNUSDT") === false  && stripos($priceElem->symbol, "UPUSDT") === false) {
                return !$this->isExclude($priceElem->symbol);
            } else {
                //dump($priceElem->symbol);
            }
        }
        return false;
    }

    public function usdtFilterInfoSymbols($priceElem)
    {
        if ((stripos($priceElem->symbol, "USDT") > 0) && $priceElem->quoteAsset == 'USDT') {
            if ($priceElem->status == 'TRADING') {
                if (stripos($priceElem->symbol, "DOWNUSDT") === false  &&  stripos($priceElem->symbol, "UPUSDT") === false) {

                    return !$this->isExclude($priceElem->symbol);
                }
            }
        }
        return false;
    }



    public function getBinancePrices()
    {
        $this->getDbSumbols();
        $response = $this->get('ticker/price');
        $prices = array_values(array_filter($response, [$this, "usdtFilterPrices"]));
        return $prices;
    }

    public function exchangeInfoSymbols()
    {
        $response = $this->get('exchangeInfo');
        foreach ($response->symbols as $r) {
        }
        $prices = array_values(array_filter($response->symbols, [$this, "usdtFilterInfoSymbols"]));
        return $prices;
    }


    public function filterTickerSymbols($ticker, $symbols)
    {

        $newTicker = [];
        $symbols = array_column($symbols, 'symbol');
        foreach ($ticker as $s => $p) {
            if (in_array($s, $symbols) && !$this->isExclude($s)) {
                $newTicker[$s] = $p;
            }
        }
        return $newTicker;
    }
}
