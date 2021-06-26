@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            @include('common.messages')
            <div>
                <div class='row'>
                    <div class="col-sm-6">
                        <div>{{_('Всего доступно на балансе:')}} {{$usdBalance}} USDT </div>
                        <div> {{_('Размер одного ордера:')}} {{$orderSize}} USDT</div>
                        <div> {{_('Количество ордеров для покупки:')}} {{$ordersCount}} </div>
                    </div>
                </div>
            </div>
            <h3> {{_("Update {$name_elements}:")}} </h3>
            
            <form action="{{ route("options_update") }}" method="POST" 
                class="form-horizontal" enctype="multipart/form-data" >
                @method('PUT') @csrf
                <div class='row'>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="api_key" class="control-label">
                                {{_('Api key')}}
                                <div class='text-danger' >{{ $errors->first('api_key') }}</div>
                            </label>
                                <input type="password" name="api_key"  
                                    value="{{$optons['api_key']}}" 
                                        class="form-control {{$errors->has('api_key') ? 'is-invalid':''}}">
                            
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="api_secret" class="control-label">
                                {{_('Api secret')}}
                                <div class='text-danger' >{{ $errors->first('api_secret') }}</div>
                            </label>
                                <input type="password" name="api_secret" 
                                        value="{{$optons['api_secret']}}" 
                                        class="form-control {{$errors->has('api_secret') ? 'is-invalid':''}}">
                        </div>
                    </div>
                </div>

                <div class='row'>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="buy_gap_percent" class="control-label">
                                {{_('Цена покупки до пробития (%)')}}
                                <div class='text-danger' >{{ $errors->first('buy_gap_percent') }}</div>
                            </label>
                                <input type="text" name="buy_gap_percent" 
                                        value="{{$optons['buy_gap_percent']}}" 
                                        class="form-control {{$errors->has('buy_gap_percent') ? 'is-invalid':''}}">
                            
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="order_size" class="control-label">
                                {{_('Размер ордера ($)')}}
                                <div class='text-danger' >{{ $errors->first('order_size') }}</div>
                            </label>
                                <input type="text" name="order_size" 
                                        value="{{$optons['order_size']}}" 
                                        class="form-control {{$errors->has('order_size') ? 'is-invalid':''}}">
                            
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="buy_analysis_disable" class="control-label">
                                {{_('Отключить анализ стаканов при покупке')}}
                                <div class='text-danger' >{{ $errors->first('buy_analysis_disable') }}</div>
                            </label><br>
                                <input {{$optons['buy_analysis_disable'] ? 'checked=checked' :''}}  type="checkbox" name="buy_analysis_disable" 
                                        value="1" 
                                        class=" {{$errors->has('buy_analysis_disable') ? 'is-invalid':''}}">
                            
                        </div>
                    </div>

                </div>


                <div class='row'>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="get_order_book_percent" class="control-label">
                                {{_('Цена получения стаканов для анализа (%)')}}
                                <div class='text-danger' >{{ $errors->first('get_order_book_percent') }}</div>
                            </label>
                                <input type="text" name="get_order_book_percent" 
                                        value="{{$optons['get_order_book_percent']}}" 
                                        class="form-control {{$errors->has('get_order_book_percent') ? 'is-invalid':''}}">
                            
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="range_book_orders" class="control-label">
                                {{_('Диапазон анализа стаканов (%)')}}
                                <div class='text-danger' >{{ $errors->first('range_book_orders') }}</div>
                            </label>
                                <input type="text" name="range_book_orders" 
                                        value="{{$optons['range_book_orders']}}" 
                                        class="form-control {{$errors->has('range_book_orders') ? 'is-invalid':''}}">
                            
                        </div>
                    </div>

                    
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="volume_percent" class="control-label">
                                {{_('Разница объемов (%)')}}
                                <div class='text-danger' >{{ $errors->first('volume_percent') }}</div>
                            </label>
                                <input type="text" name="volume_percent" 
                                        value="{{$optons['volume_percent']}}" 
                                        class="form-control {{$errors->has('volume_percent') ? 'is-invalid':''}}">
                            
                        </div>
                    </div>



                </div>

                <div class='row'>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="statistics_count_trade_stop" class="control-label">
                                {{_('Остановить торговлю если пробивших больше или равно:')}}
                                <div class='text-danger' >{{ $errors->first('statistics_count_trade_stop') }}</div>
                            </label>
                            <input type="text" name="statistics_count_trade_stop" 
                                    value="{{$optons['statistics_count_trade_stop']}}" 
                                    class="form-control {{$errors->has('statistics_count_trade_stop') ? 'is-invalid':''}}">
                        
                        </div>
                    </div> 

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="statistics_count_trade_start" class="control-label">
                                {{_('Начать торговлю если пробивших меньше или равно:')}}
                                <div class='text-danger' >{{ $errors->first('statistics_count_trade_start') }}</div>
                            </label>
                            <input type="text" name="statistics_count_trade_start" 
                                    value="{{$optons['statistics_count_trade_start']}}" 
                                    class="form-control {{$errors->has('statistics_count_trade_start') ? 'is-invalid':''}}">
                        
                        </div>
                    </div> 

                    {{-- <div class="col-sm-6">
                        <div class="form-group">
                            <label for="green_percent_required" class="control-label">
                                {{_('Процент растущих валют требуемый для торговли (%)')}}
                                <div class='text-danger' >{{ $errors->first('green_percent_required') }}</div>
                            </label>
                            <input type="text" name="green_percent_required" 
                                    value="{{$optons['green_percent_required']}}" 
                                    class="form-control {{$errors->has('green_percent_required') ? 'is-invalid':''}}">
                        
                        </div>
                    </div> --}}
                </div>

                <div class='row'>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="profit_percent" class="control-label">
                                {{_('Минимальная цена продажы (%)')}}
                                <div class='text-danger' >{{ $errors->first('profit_percent') }}</div>
                            </label>
                                <input type="text" name="profit_percent" 
                                        value="{{$optons['profit_percent']}}" 
                                        class="form-control {{$errors->has('profit_percent') ? 'is-invalid':''}}">
                            
                        </div>
                    </div>
                        
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="profit_step_percent" class="control-label">
                                {{_('Шаг цены продажы (%)')}}
                                <div class='text-danger' >{{ $errors->first('profit_step_percent') }}</div>
                            </label>
                                <input type="text" name="profit_step_percent" 
                                        value="{{$optons['profit_step_percent']}}" 
                                        class="form-control {{$errors->has('profit_step_percent') ? 'is-invalid':''}}">
                            
                        </div>
                    </div>
                    
                </div>


                <div class='row'>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="stop_loss_persent" class="control-label">
                                {{_('Стоп-лосс (%)')}}
                                <div class='text-danger' >{{ $errors->first('stop_loss_persent') }}</div>
                            </label>
                            <input type="text" name="stop_loss_persent" 
                                    value="{{$optons['stop_loss_persent']}}" 
                                    class="form-control {{$errors->has('stop_loss_persent') ? 'is-invalid':''}}">
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="order_lifetime" class="control-label">
                                {{_('Время жизни ордера (минут)')}}
                                <div class='text-danger' >{{ $errors->first('order_lifetime') }}</div>
                            </label>
                            <input type="text" name="order_lifetime" 
                                    value="{{$optons['order_lifetime']}}" 
                                    class="form-control {{$errors->has('order_lifetime') ? 'is-invalid':''}}">
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="sell_all_after_profit_percent" class="control-label">
                                {{_('Продать все после достижения прибыли (%)')}}
                                <div class='text-danger' >{{ $errors->first('sell_all_after_profit_percent') }}</div>
                            </label>
                            <input type="text" name="sell_all_after_profit_percent" 
                                    value="{{$optons['sell_all_after_profit_percent']}}" 
                                    class="form-control {{$errors->has('sell_all_after_profit_percent') ? 'is-invalid':''}}">
                        </div>
                    </div>

                </div>

                <div class='row'>


                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="averaging_percentage " class="control-label">
                                {{_('Процент усреднения (%)')}}
                                <div class='text-danger' >{{ $errors->first('averaging_percentage') }}</div>
                            </label>
                            <input type="text" name="averaging_percentage" 
                                    value="{{$optons['averaging_percentage']}}" 
                                    class="form-control {{$errors->has('averaging_percentage') ? 'is-invalid':''}}">
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="sales_perc_averaged" class="control-label">
                                {{_('Процент продажи при усреднении (%)')}}
                                <div class='text-danger' >{{ $errors->first('sales_perc_averaged') }}</div>
                            </label>
                            <input type="text" name="sales_perc_averaged" 
                                    value="{{$optons['sales_perc_averaged']}}" 
                                    class="form-control {{$errors->has('sales_perc_averaged') ? 'is-invalid':''}}">
                        </div>
                    </div>

                    

                </div>


                <div class="form-group">
                    <div class="">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-check"></i> {{_('Сохранить')}} 
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
