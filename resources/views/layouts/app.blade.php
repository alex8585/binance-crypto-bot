<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div id="app">
       
        <nav class="navbar navbar-expand-md navbar-light navbar-laravel">
            <div class="container">
                
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">
                            @auth
                                <li class="nav-item @if($currentPage == 'details' || $currentPage == 'statistics' || $currentPage == '/') active @endif">
                                    <a class="nav-link" href="{{route('circles')}}">{{ __('Statistics') }}</a>
                                </li>
                                <li class="nav-item @if($currentPage == 'orders') active @endif">
                                    <a class="nav-link" href="{{route('orders_index')}}">{{ __('Orders') }} </a>
                                </li>
                                <li class="nav-item @if($currentPage == 'balances') active @endif">
                                    <a class="nav-link" href="{{route('balances_index')}}">{{ __('Balances') }} </a>
                                </li>
                                <li class="nav-item @if($currentPage == 'balances-history') active @endif">
                                    <a class="nav-link" href="{{route('balances_history')}}">{{ __('Balances history') }} </a>
                                </li>
                                <li class="nav-item @if($currentPage == 'orderbook') active @endif">
                                    <a class="nav-link" href="{{route('orderbook_index')}}">{{ __('Orders Book') }} </a>
                                </li>
                                <li class="nav-item @if($currentPage == 'tvstatistics') active @endif">
                                    <a class="nav-link" href="{{route('tv_statistics_index')}}">{{ __('TV Statistics') }} </a>
                                </li>
                                <li class="nav-item @if($currentPage == 'top-candidates') active @endif">
                                    <a class="nav-link" href="{{route('top_candidates_index')}}">{{ __('Top candidates 10m') }} </a>
                                </li>
                            @endauth
                    </ul>
                   
                    

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav">
                        <!-- Authentication Links -->
                        @guest
                            <li><a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a></li>
                          
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('options_index') }}">
                                        {{ __('Settings') }}
                                    </a>
                                    
                                    
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

       

        <main class="content">
            <div class="container">
                
                <div class="row justify-content-center">
                    <div class="col-md-12">
                        @yield('content')
                    </div>
                </div>
            </div>
        </main>

        
    </div>
</body>
</html>
