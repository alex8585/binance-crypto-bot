@extends('layouts.app')

@section('content')


<h2 style="display: inline-block;margin-right:20px">{{__('Orders Book ')}}</h2>
<div>
	<a style="margin-bottom:15px; margin-right:10px" class="btn btn-secondary back-button" 
	href="{{ route('orderbook_index',['type'=>'candidate']) }}" 
	role="button">
		<i class="fa fa-long-arrow-alt-left"></i>
		{{_('Анализ покупок')}}
	</a> 
	<a style="margin-bottom:15px; margin-right:10px" class="btn btn-secondary back-button" 
	href="{{ route('orderbook_index',['type'=>'order']) }}" 
	role="button">
		<i class="fa fa-long-arrow-alt-left"></i>
		{{_('Анализ продаж')}}
	</a> 
</div>	
<table class="table ">
	@php 
	$page = $elements->currentPage(); 
	@endphp
	
	<thead>
		<tr>
			
			<th scope="col">@sortablelink('symbol',__('Название пары'),['page' => $page])</th>

			<th scope="col">@sortablelink('created',__('Время'),['page' => $page]) </th>

			<th scope="col"> {{__('Circle') }} </th>

			<th scope="col">@sortablelink('pp', __('Pierce price'),['page' => $page]) </th>

			
			
			<th></th>

		</tr>
	</thead>
	
	@foreach ($elements as $e)
	

		<tr>
			
			<td> {{$e->symbol}} </td>


			<td>
				<b>{{ $e->created->format('d-m-Y H:i') }} </b>
			</td>

			<td> {{$e->circle->hour}} </td>   

			<td>{{$e->pp}}  </td>
			
		

			<td>
					<a class="btn btn-secondary back-button" 
				href="{{ route('orderbook_details',[$e->id]) }}" 
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
	href="{{ route('circles') }}" 
	role="button">
		<i class="fa fa-long-arrow-alt-left"></i>
		{{_('Back')}}
	</a> 
@endsection
