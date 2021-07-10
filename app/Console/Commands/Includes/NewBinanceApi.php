<?php

namespace App\Console\Commands\Includes;



class NewBinanceApi extends \Binance\API
{


    public $caOverride = true;

    public function setTimeOffset($timeOffset)
    {
        $this->info['timeOffset'] = $timeOffset;
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
