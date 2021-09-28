<?php

namespace App\Http\Controllers;

use App\Option as Option;
use Illuminate\Http\Request;
use \Carbon\Carbon as Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\OptionsRequest;
use Illuminate\Support\Facades\Cache;
use App\Console\Commands\Includes\BotUtils;

class OptionsController extends Controller
{
    use BotUtils;
    public function __construct()
    {
        $this->middleware('auth');

        $this->fields = [
            "api_key",
            "api_secret",
            "order_size",
            "profit_percent",
            'profit_step_percent',
            "buy_gap_percent",
            'volume_percent',
            'get_order_book_percent',
            'range_book_orders',
            'stop_loss_persent',
            'buy_analysis_disable',
            'order_lifetime',
            'sell_all_after_profit_percent',
            'statistics_count_trade_stop',
            'statistics_count_trade_start',
            'averaging_percentage',
            'sales_perc_averaged',
            'green_cnt_update_interval'
        ];
    }

    public function index()
    {

        $optons = Option::select(['key', 'value'])->whereIn('key', $this->fields)->pluck('value', 'key')->toArray();
        $newOptions = [];
        foreach ($this->fields as $f) {
            if (isset($optons[$f])) {
                $newOptions[$f] = $optons[$f];
            } else {
                $newOptions[$f] = '';
            }
        }

        //$this->updateBalances();
        $balances = $this->getBalances();
        $usdBalance = 0;
        if (isset($balances['USDT']['available'])) {
            $usdBalance = round($balances['USDT']['available'], 2);
        }


        $orderSize = $newOptions['order_size'];
        if ($orderSize) {
            $ordersCount = floor($usdBalance / $orderSize);
        }


        return view('options.edit', [
            'name_element' => 'option',
            'name_elements' => 'options',
            'optons' => $newOptions,
            'usdBalance' => $usdBalance ?? '',
            'orderSize' => $orderSize ?? '',
            'ordersCount' => $ordersCount ?? '',
        ]);
    }


    public function update(OptionsRequest $request)
    {

        $buy_analysis_disable = $request->input('buy_analysis_disable') ? 1 : 0;
        $option = Option::firstOrNew(['key' => 'buy_analysis_disable']);
        $option->value = $buy_analysis_disable;
        $option->save();


        foreach ($request->input() as $k => $v) {
            if (in_array($k, $this->fields)) {
                if ($k == 'buy_analysis_disable') {
                    continue;
                }

                $option = Option::firstOrNew(['key' => $k]);
                $option->value = $v;
                $option->save();
            }
        }

        $result = $this->resetOptionsCash();
        if (!$result) {
            Log::channel('app')->critical(['OptionsController', 'resetOptionsCash']);
        }
        //$c = Cache::get('bot_options');
        //dd($c);
        // $order_lifetime = $this->getOption('order_lifetime');


        $request->session()->flash('messages', ["Settings have been stored!"]);
        return redirect()->route('options_update');
    }
}
