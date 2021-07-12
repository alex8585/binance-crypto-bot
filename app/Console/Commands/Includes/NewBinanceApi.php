<?php

namespace App\Console\Commands\Includes;

//use App\Console\Commands\Includes\BinanceRequest;

class NewBinanceApi extends \Binance\API
{


    public $caOverride = true;

    public function setTimeOffset($timeOffset)
    {
        $this->info['timeOffset'] = $timeOffset;
    }

    public static function milliseconds()
    {
        list($msec, $sec) = explode(' ', microtime());
        return $sec . substr($msec, 2, 3);
    }

    protected function httpRequest(string $url, string $method = "GET", array $params = [], bool $signed = false)
    {
        if (function_exists('curl_init') === false) {
            throw new \Exception("Sorry cURL is not installed!");
        }

        if ($this->caOverride === false) {
            if (file_exists(getcwd() . '/ca.pem') === false) {
                $this->downloadCurlCaBundle();
            }
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_VERBOSE, $this->httpDebug);
        $query = http_build_query($params, '', '&');

        // signed with params
        if ($signed === true) {
            if (empty($this->api_key)) {
                throw new \Exception("signedRequest error: API Key not set!");
            }

            if (empty($this->api_secret)) {
                throw new \Exception("signedRequest error: API Secret not set!");
            }

            dump([$url]);

            $base = $this->base;
            $ts =  $this->milliseconds();

            $params['timestamp'] = $ts - $this->info['timeOffset'];


            if (isset($params['wapi'])) {
                unset($params['wapi']);
                $base = $this->wapi;
            }

            if (isset($params['sapi'])) {
                unset($params['sapi']);
                $base = $this->sapi;
            }

            $query = http_build_query($params, '', '&');
            $signature = hash_hmac('sha256', $query, $this->api_secret);
            if ($method === "POST") {
                $endpoint = $base . $url;
                $params['signature'] = $signature; // signature needs to be inside BODY
                $query = http_build_query($params, '', '&'); // rebuilding query
            } else {
                $endpoint = $base . $url . '?' . $query . '&signature=' . $signature;
            }

            curl_setopt($curl, CURLOPT_URL, $endpoint);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'X-MBX-APIKEY: ' . $this->api_key,
            ));
        }
        // params so buildquery string and append to url
        else if (count($params) > 0) {
            curl_setopt($curl, CURLOPT_URL, $this->base . $url . '?' . $query);
        }
        // no params so just the base url
        else {
            curl_setopt($curl, CURLOPT_URL, $this->base . $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'X-MBX-APIKEY: ' . $this->api_key,
            ));
        }
        curl_setopt($curl, CURLOPT_USERAGENT, "User-Agent: Mozilla/4.0 (compatible; PHP Binance API)");
        // Post and postfields
        if ($method === "POST") {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
        }
        // Delete Method
        if ($method === "DELETE") {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        }

        // PUT Method
        if ($method === "PUT") {
            curl_setopt($curl, CURLOPT_PUT, true);
        }

        // proxy settings
        if (is_array($this->proxyConf)) {
            curl_setopt($curl, CURLOPT_PROXY, $this->getProxyUriString());
            if (isset($this->proxyConf['user']) && isset($this->proxyConf['pass'])) {
                curl_setopt($curl, CURLOPT_PROXYUSERPWD, $this->proxyConf['user'] . ':' . $this->proxyConf['pass']);
            }
        }
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);

        // set user defined curl opts last for overriding
        foreach ($this->curlOpts as $key => $value) {
            curl_setopt($curl, constant($key), $value);
        }

        if ($this->caOverride === false) {
            if (file_exists(getcwd() . '/ca.pem') === false) {
                $this->downloadCurlCaBundle();
            }
        }

        $output = curl_exec($curl);
        // Check if any error occurred
        if (curl_errno($curl) > 0) {
            // should always output error, not only on httpdebug
            // not outputing errors, hides it from users and ends up with tickets on github
            throw new \Exception('Curl error: ' . curl_error($curl));
        }

        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($output, 0, $header_size);
        $output = substr($output, $header_size);

        curl_close($curl);

        $json = json_decode($output, true);

        $this->lastRequest = [
            'url' => $url,
            'method' => $method,
            'params' => $params,
            'header' => $header,
            'json' => $json
        ];

        if (isset($header['x-mbx-used-weight'])) {
            $this->setXMbxUsedWeight($header['x-mbx-used-weight']);
        }

        if (isset($header['x-mbx-used-weight-1m'])) {
            $this->setXMbxUsedWeight1m($header['x-mbx-used-weight-1m']);
        }

        if (isset($json['msg'])) {
            dump($json['msg']);
            return $json;
            // should always output error, not only on httpdebug
            // not outputing errors, hides it from users and ends up with tickets on github
            //throw new \Exception('signedRequest error: ' . print_r($output, true));
        }
        $this->transfered += strlen($output);
        $this->requestCount++;
        return $json;
    }

    public function order(string $side, string $symbol, $quantity, $price, string $type = "LIMIT", array $flags = [], bool $test = false)
    {
        $opt = [
            "symbol" => $symbol,
            "side" => $side,
            "type" => $type,
            "quantity" => $quantity,
            "recvWindow" => 60000,
        ];

        // someone has preformated there 8 decimal point double already
        // dont do anything, leave them do whatever they want
        if (gettype($price) !== "string") {
            // for every other type, lets format it appropriately
            $price = number_format($price, 8, '.', '');
        }

        if (is_numeric($quantity) === false) {
            // WPCS: XSS OK.
            echo "warning: quantity expected numeric got " . gettype($quantity) . PHP_EOL;
        }

        if (is_string($price) === false) {
            // WPCS: XSS OK.
            echo "warning: price expected string got " . gettype($price) . PHP_EOL;
        }

        if (isset($flags['newClientOrderId'])) {
            $opt['newClientOrderId'] = $flags['newClientOrderId'];
        }

        if ($type === "LIMIT" || $type === "STOP_LOSS_LIMIT" || $type === "TAKE_PROFIT_LIMIT") {
            $opt["price"] = $price;
            $opt["timeInForce"] = "GTC";
        }

        if (isset($flags['stopPrice'])) {
            $opt['stopPrice'] = $flags['stopPrice'];
        }

        if (isset($flags['icebergQty'])) {
            $opt['icebergQty'] = $flags['icebergQty'];
        }

        if (isset($flags['newOrderRespType'])) {
            $opt['newOrderRespType'] = $flags['newOrderRespType'];
        }

        $qstring = ($test === false) ? "v3/order" : "v3/order/test";

        try {
            $result = $this->httpRequest($qstring, "POST", $opt, true);
        } catch (\Exception $e) {
            dump(['Exception NewBinanceApi->order']);
            $msg = $e->getMessage();
            echo $msg, "\n";
            $result['msg'] = $msg;
            return $result;
        }

        return $result;
    }
}
