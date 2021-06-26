@extends('layouts.app')

@section('content')

   
<table class="table ">
	@php 
	$page = $elements->currentPage(); 
	@endphp
	<h2 style="display:block;margin-right:20px; margin-bottom:20px">{{__('Balances history')}}</h2>
	

	<thead>
		<tr>
			<th scope="col">@sortablelink('updated_at',__('Дата'),['page' => $page])  </th>
			

			<th scope="col">@sortablelink('end_total', __('Баланс'),['page' => $page]) </th>

			<th scope="col"> {{__('Прирост / убыток USDT') }}</th>

			<th scope="col"> {{__('Прирост / убыток (%)')}}</th>
		</tr>
	</thead>
	
	@foreach ($elements as $e)
	
		
		<tr>
			<td>
				<b>{{ $e->start_created_at->format('d-m-Y') }} </b>
			</td>
			
			<td>
				<b>{{ round($e->end_total,2) }} </b>
			</td>

			@php

				$difUsd = $e->end_total - $e->start_total;
				$difPercent = $e->getTotalPercent();
			@endphp
			<td class="{{ (  $difUsd > 0 )?'text-success':'text-danger'}}" >
				{{ round($difUsd,2) }} 
			</td>

		
			<td class="{{ (  $difUsd > 0 )?'text-success':'text-danger'}}">  
				{{$difPercent }}
			</td>
		
			
		</tr>
		
	@endforeach		
</table>

<div>
	{{ $elements->appends(Request::except('page'))->links() }}
</div>

@endsection
