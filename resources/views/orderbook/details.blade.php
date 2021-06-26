@extends('layouts.app')

@section('content')
@if($order) 
<h2 style="display: inline-block;margin-right:20px">
    {{__('Orders Book Details')}} : {{$book->symbol}}  
</h2>

<div style="font-weight:bold; font-size:20px" class='text-success'>
    {{ ( $book->type == 'candidate') ? __('Анализ покупки:') : __('Анализ продажи:')}}  
</div>


@if($book->volume_status)
<ul class="list-group">
    <li class="list-group-item">
        <span style="display:inline-block; font-weight:bold; margin-right:25px">{{ __('Время анализа:') }}</span>
        <span style="font-weight:bold; " class="" >{{$book->updated_at->format('d-m-Y H:i')}}  </span>
    </li>
    @if($book->type == 'candidate')
        <li class="list-group-item">
            <span style="display:inline-block; font-weight:bold; margin-right:25px">{{ __('Цена в момент анализа стакана:') }}</span>
            <span style="font-weight:bold; " class="" >{{ $book->pierce_price }} </span>
        </li>
        <li class="list-group-item">
            <span style="display:inline-block; font-weight:bold; margin-right:25px">{{ __('Предполагаемая цена покупки:') }}</span>
            <span style="font-weight:bold; " class="" >{{ $book->buy_price }} </span>
        </li>
    @else
        <li class="list-group-item">
            <span style="display:inline-block; font-weight:bold; margin-right:25px">{{ __('Цена покупки:') }}</span>
            <span style="font-weight:bold; " class="" >{{ $order->buy_price }} </span>
        </li>
        <li class="list-group-item">
            <span style="display:inline-block; font-weight:bold; margin-right:25px">{{ __('Цена в момент анализа стакана:') }}</span>
            <span style="font-weight:bold; " class="" >{{ $order->last_profit_price }} </span>
        </li>
    @endif


    {{-- <li class="list-group-item">
        <span style="display:inline-block;  font-weight:bold; margin-right:25px">{{ __('Предполагаемая цена продажи:') }}</span>
        <span style="font-weight:bold; " class="" >{{ $book->sell_price }} </span>
    </li> --}}


    <li class="list-group-item">
        <span style="display:inline-block;  font-weight:bold; margin-right:25px">{{ __('Граничная цена диапазона стакана покупок:') }}</span>
        <span style="font-weight:bold; " style="font-weight:bold; " class="" >{{ $bids_stop_range }} </span>
    </li>

    <li class="list-group-item">
        <span style="display:inline-block;  font-weight:bold; margin-right:25px">{{ __('Граничная цена диапазона стакана продаж:') }}</span>
        <span style="font-weight:bold; " style="font-weight:bold; " class="" >{{ $asks_stop_range }} </span>
    </li>

    <li class="list-group-item">
        <span style="display:inline-block; font-weight:bold; margin-right:25px">{{ __('Объем покупок:') }}</span>
       
        <span style="font-weight:bold; " class="text-success" >{{  $book->getBidsVolume() }}  </span>
    </li>
    <li class="list-group-item">
        <span style="display:inline-block; font-weight:bold; margin-right:25px">{{ __('Объем продаж:') }}</span>
        <span style="font-weight:bold; " class="text-danger" >{{  $book->getAsksVolume() }}  </span>
    </li>

    <li class="list-group-item">
        @php $diff = $book->getDiffVolume() @endphp
        <span style="display:inline-block; font-weight:bold; margin-right:25px">{{ __('Процент:') }}</span>
        <span style="font-weight:bold; " class="{{ (  $book->volume_status == 'ok' )?'text-success':'text-danger' }}" >{{ $diff }} %</span>
    </li>

    <li class="list-group-item">
        @if($book->type == 'candidate')
            <span style="display:inline-block; font-weight:bold; margin-right:25px">{{ __('Решение о покупке:') }}</span>
            <span style="font-weight:bold; font-size:16px"  class="{{ ( $book->volume_status == 'ok')?'text-success':'text-danger' }}" >
                {{ ( $book->volume_status == 'ok') ? __('Будем покупать') : __('Не будем покупать')}}  
            </span>
        @else
            <span style="display:inline-block; font-weight:bold; margin-right:25px">{{ __('Решение о продаже:') }}</span>
            <span style="font-weight:bold; font-size:16px"  class="{{ ( $book->volume_status == 'bad')?'text-danger':'text-success' }}" >
                {{ ( $book->volume_status == 'bad') ? __('Будем продавать') : __('Не будем продавать')}}  
            </span>
        @endif


    </li>
  </ul>
@endif

<div class='row'>
<div class="col-md-6">
    <div style="font-size:22px;font-weight:bold" >{{__('Bids')}}</div>
    @php 
       $red = "rgb(248, 73, 96)";
       $green = "rgb(2, 192, 118)";
       $transp = "rgba(255, 255, 255, 0)";
    @endphp
    <table  class="table ">
        <thead>
            <tr>
                <th scope="col">{{__('Price')}}</th>
    
                <th scope="col">{{__('Quantity')}}</th>

                <th scope="col">{{__('Total')}}</th>
            </tr>
        </thead>

        @foreach ($bids as $k=>$v)
            @php 
                $percent = $k*$v/$percentVal;
                $style = "background-image: linear-gradient(to right, {$green} {$percent}%, {$transp} {$percent}%,  {$transp} 100%, {$green} 100%)";
            @endphp
            <tr style="{{$style}}">
                <td> {{$k}} </td>
                <td> {{$v}} </td>   
                <td style="font-weight:bold;" > {{$book->formatCurrency($k*$v) }}  </td>   
            </tr>
        @endforeach		
    </table>
</div>

<div class="col-md-6">
    <div style="font-size:22px;font-weight:bold" >{{__('Asks')}}</div>
    <table  class="table ">
        <thead>
            <tr >
                <th scope="col">{{__('Price')}}</th>
    
                <th scope="col">{{__('Quantity')}}</th>

                <th scope="col">{{__('Total')}}</th>
            </tr>
        </thead>
        @foreach ($asks as $k=>$v)
            @php 
                $percent = $k*$v/$percentVal;
                $style = "background-image: linear-gradient(to right, {$red} {$percent}%, {$transp} {$percent}%,  {$transp} 100%, {$red} 100%)";
            @endphp
            <tr style="{{$style}}" >
                    <td> {{$k}} </td>
                    <td> {{$v}} </td>  
                    <td style="font-weight:bold;" > {{$book->formatCurrency($k*$v)  }}  </td> 
               
            </tr>
            <div class="progress-bar ask-bar" style="transform: translateX(-6.59093%); left: 100%;"></div>
        @endforeach		
    </table>
</div>
</div>




<a class="btn btn-primary back-button" 
	href="{{ route('orderbook_index') }}" 
	role="button">
		<i class="fa fa-long-arrow-alt-left"></i>
		{{_('Back')}}
	</a> 




@else
<h3>Ордер был удалён</h3>
@endif



@endsection
