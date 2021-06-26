@if (count($errors) > 0 || $errors->any())
    <div class="alert alert-danger  alert-dismissible fade show" role="alert">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>  
@endif

@php 
    $messages = session('messages') ;
@endphp
@if (isset($messages) && count(session('messages')) > 0 )
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <ul>
            @foreach(session('messages') as $msg) 
                <li>{{ $msg }}</li>
            @endforeach
        </ul>    
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
        </button>
    </div>  
@endif

