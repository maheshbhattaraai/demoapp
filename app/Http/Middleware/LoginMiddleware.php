<?php

namespace App\Http\Middleware;

use Closure;

class LoginMiddleware
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
         $authorizationHeader = $request->header('Authorization');

        if(!isset($authorizationHeader)){
            return response()->json(['message'=>'You did not have permission to access routes'],401);
        }
        if(!(Hash::check("harisharanamtrading", $authorizationHeader))){
            return response()->json(['message'=>'You did not have permission to access routes'],401);
        }
        return $next($request);
    }
}
