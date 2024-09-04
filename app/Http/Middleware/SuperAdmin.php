<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Providers\RouteServiceProvider;
use Symfony\Component\HttpFoundation\Response;

class SuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next) : Response
    {
        /**
         * NOTICE: The auth is not working that's why I made comment that code
         * Before un-comment this code please fix auth loggin service
         * @BUG: Loggin authentication is not worked. 
         */

        // $user = auth()->user();

        // if (! empty($user) && $user->type != 'super admin') {
        //     return redirect(RouteServiceProvider::HOME);
        // } 


        return $next($request);
    }
}