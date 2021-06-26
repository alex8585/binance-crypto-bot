@extends('layouts.app')

@section('content')


<table class="table ">
	@php 
	$page = $elements->currentPage(); 
	@endphp
	<h2 style="display: inline-block;margin-right:20px">{{$candidate->symbol}}</h2>
	<div>{{__('Время получения индикатора')}} {{$candidate->created_at->format('d-m-Y H:i')}}</div>

	<thead>
		<tr>
			<th scope="col">@sortablelink('created_at',__('Дата'),['page' => $page])  </th>
			<th scope="col">@sortablelink('created_at',__('Время'),['page' => $page]) </th>

			<th scope="col">@sortablelink('created_at',__('Прошло после получения'),['page' => $page]) </th>

			<th scope="col">@sortablelink('buy_price', __('By price'),['page' => $page]) </th>

			<th scope="col">@sortablelink('max', __('Max price'),['page' => $page]) </th>
			<th scope="col">@sortablelink('max_percent', __('Max price percent'),['page' => $page]) </th>
			
			
		

		</tr>
	</thead>
	
	@foreach ($elements as $e)
	
          
		<tr>
			<td>
				
				
				<b>{{ $e->created_at->format('d-m-Y') }} </b>
			</td>
			
			<td>
				<b>{{ $e->created_at->format('H:i') }} </b>
			</td>

			 <td>
				<b>{{ gmdate("H:i", $e->created_at->diffInSeconds($candidate->pierce_time))     }} </b>
			</td> 
			

			<td> {{$e->buy_price}}  </td>
			
			<td >{{$e->max}}  </td>
			
			<td class="{{ ($e->max_percent>=0)?'text-success':'text-danger' }}" > {{$e->max_percent}}</td>

			
			
		</tr>
		
	@endforeach		
</table>

<div>
	{{ $elements->appends(Request::except('page'))->links() }}
</div>
 <a class="btn btn-primary back-button" 
	href="{{ route('tv_statistics',[$candidate->circle_id]) }}" 
	role="button">
		<i class="fa fa-long-arrow-alt-left"></i>
		{{_('Back')}}
	</a>  
@endsection
