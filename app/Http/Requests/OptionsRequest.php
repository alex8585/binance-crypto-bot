<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OptionsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'api_key' => 'required',
            'api_secret' => 'required',
            'buy_gap_percent' => 'required|numeric',
            'order_size' => 'required|numeric',
            'buy_analysis_disable' => 'nullable|boolean',
            'get_order_book_percent' => 'required|numeric',
            'range_book_orders' => 'required|numeric',
            'volume_percent' => 'required|numeric',
            'profit_percent' => 'required|numeric',
            'profit_step_percent' => 'required|numeric',
            'stop_loss_persent' => 'nullable|numeric',
            'order_lifetime' => 'nullable|numeric',
            'sell_all_after_profit_percent' => 'nullable|numeric',
            'statistics_count_trade_stop' => 'required|numeric',
            'statistics_count_trade_start' => 'required|numeric',
            'averaging_percentage' => 'nullable|numeric',
            'sales_perc_averaged' => 'nullable|numeric',
            "green_cnt_update_interval" => 'nullable|numeric|min:5|',
        ];
    }
}
