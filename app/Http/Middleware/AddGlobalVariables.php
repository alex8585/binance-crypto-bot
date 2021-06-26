<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\View;

class AddGlobalVariables
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $urlPart = str_replace($request->root(),'', $request->url());
        $parts = explode('/', $urlPart);
        $page = isset($parts[1]) ? $parts[1] :'/';
        $subpage = isset($parts[2]) ? $parts[2] :'';
        
        View::share(
            ['currentPage'=> $page,
            'currentSubpage'=> $subpage]
        ); 

        return $next($request);
    }
}
