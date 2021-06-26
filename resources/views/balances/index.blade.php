@extends('layouts.app')

@section('content')

   
<table class="table ">
	@php 
	$page = $elements->currentPage(); 
	@endphp
	<h2 style="display:block;margin-right:20px; margin-bottom:20px">{{__('Balances')}}</h2>
	

	<ul class="list-group">
		<li class="list-group-item">
			<span style="display:inline-block; width:10px;margin-right:25px">1h</span>
			<span class="{{ ( $history1h>=0)?'text-success':'text-danger' }}" >{{ $history1h }} %</span>
		</li>
		<li class="list-group-item">
			<span style="display:inline-block; width:10px; margin-right:25px">3h</span>
			<span class="{{ ( $history3h>=0)?'text-success':'text-danger' }}" >{{ $history3h }} %</span>
		</li>
		<li class="list-group-item">
			<span style="display:inline-block;  width:10px;margin-right:25px">6h</span>
			<span class="{{ ( $history6h>=0)?'text-success':'text-danger' }}" >{{ $history6h }} %</span>
		</li>
		<li class="list-group-item">
			<span style="display:inline-block;  width:10px;margin-right:25px">12h</span>
			<span class="{{ ( $history12h>=0)?'text-success':'text-danger' }}" >{{ $history12h }} %</span>
		</li>
		<li class="list-group-item">
			<span style="display:inline-block; width:10px; margin-right:25px">24h</span>
			<span class="{{ ( $history24h>=0)?'text-success':'text-danger' }}" >{{ $history24h }} %</span>
		</li>
	  </ul>
	<thead>
		<tr>
			<th scope="col">@sortablelink('updated_at',__('Дата'),['page' => $page])  </th>
			<th scope="col">@sortablelink('updated_at',__('Время'),['page' => $page]) </th>

			<th scope="col">@sortablelink('symbol', __('Название пары'),['page' => $page]) </th>

			<th scope="col">@sortablelink('available', __('Available'),['page' => $page]) </th>

			<th scope="col">@sortablelink('on_order', __('On order'),['page' => $page]) </th>

			


		</tr>
	</thead>
	
	@foreach ($elements as $e)
	
		@if( (round($e->available, 5) > 0.0001) OR (round($e->on_order, 5) >  0.0001) )  
		<tr>
			<td>
				<b>{{ $e->updated_at->format('d-m-Y') }} </b>
			</td>
			
			<td>
				<b>{{ $e->updated_at->format('H:i') }} </b>
			</td>

			<td>
				<b>{{ $e->symbol}} </b>
			</td>

			<td>
				<b>{{ round($e->available,5) }} </b>
			</td>

		
			<td> {{ round($e->on_order,5) }}  </td>
		
			
		</tr>
		@endif
	@endforeach		
</table>

<div>
	{{ $elements->appends(Request::except('page'))->links() }}
</div>
 {{-- <a class="btn btn-primary back-button" 
	href="{{ route('statistics',[$candidate->circle_id]) }}" 
	role="button">
		<i class="fa fa-long-arrow-alt-left"></i>
		{{_('Back')}}
	</a>   --}}
@endsection
