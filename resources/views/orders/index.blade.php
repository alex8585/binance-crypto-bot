@extends('layouts.app')

@section('content')
@if($current)
   <div>
      <div class='row'>
         <div class="col-sm-6">
            <div>{{_('Среднее количество зелёных валют за прошлый час')}} {{round($avg,2)}}</div>
               <div> {{_('Количество зелёных валют в начале часа')}} 
                     <span class="{{ $green_above_avg ? 'text-success':'text-danger'}}">{{ $current}}</span> 
               </div>
         </div>
         <div class="col-sm-6">
               
            @if($count_result)
               <div class="text-success"> {{ __('Счетчик статистики не превышен') }}</div>
            @else
               <div class="text-danger"> {{ __('Счетчик статистики был превышен') }}</div>
               
            @endif
         </div> 


         <div class="col-sm-12" style='text-align: center; padding:5px;font-size:16px'>
            @if($green_above_avg && $count_result)
               <div class="text-success"> <b>{{_('Будем покупать')}}</b></div>
            @else
               <div class="text-danger"> <b>{{_('Не будем покупать')}}</b></div>
            @endif
         </div> 
      </div>
   </div>
@endif
   <div id='orders'></div>

@endsection
