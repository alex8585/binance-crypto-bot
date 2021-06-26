@extends('layouts.app')

@section('content')
<h2 style="display: inline-block;margin-right:20px">{{__('TV Statistics ')}}</h2>
<div>
		<a style="margin-bottom:15px; margin-right:10px" class="btn btn-secondary back-button" 
			href="{{ route('tv_statistics',[$tvCircle->id]) }}" 
			role="button">
			<i class="fa fa-long-arrow-alt-left"></i>
			{{_('Все')}}
		</a> 
		<a style="margin-bottom:15px; margin-right:10px" class="btn btn-secondary back-button" 
			href="{{ route('tv_statistics',[$tvCircle->id,'timeframe'=>'hour']) }}" 
			role="button">
			<i class="fa fa-long-arrow-alt-left"></i>
			{{_('Час')}}
		</a> 
		<a style="margin-bottom:15px; margin-right:10px" class="btn btn-secondary back-button" 
			href="{{ route('tv_statistics',[$tvCircle->id,'timeframe'=>'minute5']) }}" 
			role="button">
			<i class="fa fa-long-arrow-alt-left"></i>
			{{_('5 минут ')}}
		</a> 
	</div>
<table class="table ">
	@php 
	$page = $elements->currentPage(); 
	@endphp
	
	<thead>
		<tr>
			<th scope="col">@sortablelink('created_at',__('Дата'),['page' => $page])  </th>
			<th scope="col">@sortablelink('created_at',__('Время'),['page' => $page]) </th>
			<th scope="col">@sortablelink('symbol',__('Название пары'),['page' => $page])</th>
			<th scope="col">@sortablelink('timeframe',__('Time frame'),['page' => $page])</th>


			<th scope="col">@sortablelink('start_price', __('Start price'),['page' => $page]) </th>

			<th scope="col">@sortablelink('max', __('Max price'),['page' => $page]) </th>
			<th scope="col">@sortablelink('max_percent', __('Percent'),['page' => $page]) </th>
			
			<th> {{__('Actions')}}</th>

		</tr>
	</thead>
	
	@foreach ($elements as $e)
	
          
		<tr>
			<td>
				
				<b>{{ $e->created_at->format('d-m-Y')   }} </b>
			</td>
			
			<td>
				<b>{{ $e->created_at->format('H:i') }} </b>
			</td>

			<td> {{$e->symbol}} </td>   

			<td> 
				@if($e->timeframe == 'hour')
					{{__('Час') }}
				@else
					{{__('5 минут') }}
				@endif
				
			
			
			</td>  

			<td>{{$e->start_price}}  </td>

			
			<td>{{$e->max}}  </td>
			
			<td class="{{ ($e->max_percent>=0)?'text-success':'text-danger' }}"> {{$e->max_percent}}</td>
			<td>
				<a class="btn btn-secondary back-button" 
            href="{{ route('tv_statistics_details',[$e->id]) }}" 
            role="button">
                <i class="fa fa-long-arrow-alt-left"></i>
                {{_('Details')}}
		</a>
			</td>
		</tr>
		
	@endforeach		
</table>

<div>
	{{ $elements->appends(Request::except('page'))->links() }}
</div>
<a class="btn btn-primary back-button" 
	href="{{ route('tv_statistics_index') }}" 
	role="button">
		<i class="fa fa-long-arrow-alt-left"></i>
		{{_('Back')}}
	</a> 
@endsection
