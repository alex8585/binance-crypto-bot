@extends('layouts.app')

@section('content')

<div>
	<a style="margin-bottom:15px; margin-right:10px" class="btn btn-secondary back-button" 
	href="{{ route('top_candidates_index',[ 'type'=>'all']) }}" 
	role="button">
		<i class="fa fa-long-arrow-alt-left"></i>
		{{_('Все')}}
	</a> 
	<a style="margin-bottom:15px; margin-right:10px" class="btn btn-secondary back-button" 
	href="{{ route('top_candidates_index',[ 'type'=>'with_tv']) }}" 
	role="button">
		<i class="fa fa-long-arrow-alt-left"></i>
		{{_('С TV Statistics')}}
	</a> 
</div>
<table class="table ">
	@php 
	$page = $elements->currentPage(); 
	@endphp
		<h2 style="display: inline-block;margin-right:20px">{{__('Top candidates 10m')}}</h2>
	<thead>
		<tr>
			<th scope="col">@sortablelink('pierce_time',__('Дата'),['page' => $page])  </th>
			<th scope="col">@sortablelink('pierce_time',__('Время'),['page' => $page]) </th>
			<th scope="col">@sortablelink('symbol',__('Название пары'),['page' => $page])</th>
			<th scope="col">@sortablelink('pierce_price', __('By price'),['page' => $page]) </th>

			<th scope="col">@sortablelink('max_price', __('Max price'),['page' => $page]) </th>
			<th scope="col">@sortablelink('max_percent', __('Percent'),['page' => $page]) </th>
			<th scope="col">@sortablelink( __('TV Indicators перед') ) </th>
			<th scope="col">@sortablelink( __('TV Indicators после') ) </th>
			<th></th>

		</tr>
	</thead>
	
	@foreach ($elements as $e)
	
		<tr>
			<td>
				
				<b>{{ $e->pierce_time->format('d-m-Y')   }} </b>
			</td>
			
			<td>
				<b>{{ $e->pierce_time->format('H:i') }} </b>
			</td>

			<td> {{$e->symbol}} </td>   

			<td>{{$e->pierce_price}}  </td>
			
			<td>{{$e->max_price}}  </td>
			
			<td class="{{ ($e->max_percent>=0)?'text-success':'text-danger' }}"> {{$e->max_percent}}</td>


			<td> 
				@isset($e->before_tehnical_1m_new)
					<div > 
						<b>
							<span >1h </span>
							{{$e->before_tehnical_1m_new->created_at->format('d-m-Y H:i')}}
						</b>
					</div>
				@endisset
				@isset($e->before_tehnical_5m_new)
					<div > 
						<b>
							<span>5m </span>
							{{$e->before_tehnical_5m_new->created_at->format('d-m-Y H:i')}}
						</b>
						
					</div>
				@endisset
			</td>
			<td> 
				@isset($e->after_tehnical1h_new)
					<div> 
						<b>
							<span>1h </span>
							{{ $e->after_tehnical1h_new->created_at->format('d-m-Y H:i') }}
						</b> 
					</div>
				@endisset
				@isset($e->after_tehnical5m_new)
					<div > 
						<b>
							<span>5m </span>
							{{ $e->after_tehnical5m_new->created_at->format('d-m-Y H:i') }}
						</b>
					</div>
				@endisset
			</td>
			<td>
				<a class="btn btn-secondary back-button" 
			href="{{ route('statistics_details',[$e->id]) }}" 
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
