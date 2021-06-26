@extends('layouts.app')

@section('content')


<table class="table ">
	@php 
	$page = $elements->currentPage(); 
	@endphp
    <h2 style="display: inline-block;margin-right:20px">{{__('TV Statistics ')}}</h2>
	

	<thead>
		<tr>
			<th scope="col">@sortablelink('created_at',__('Дата'),['page' => $page])  </th>
			<th scope="col">@sortablelink('created_at',__('Время'),['page' => $page]) </th>
			<th scope="col">@sortablelink('cnt',__('Количество'),['page' => $page]) </th>
            <th> {{__('Actions')}}</th>
			
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
				<b>{{ $e->cnt}} </b>
			</td>
			
			<td>
				<a class="btn btn-secondary back-button {{ ($e->cnt)?'':'disabled' }}" 
				href="{{ route('tv_statistics',[$e->id]) }}" 
				role="button">
					<i class="fa fa-long-arrow-alt-left"></i>
				{{_('Валюты')}}
				</a>
			</td>
			
			
		</tr>
		
	@endforeach		
</table>

<div>
	{{ $elements->appends(Request::except('page'))->links() }}
</div>

@endsection
