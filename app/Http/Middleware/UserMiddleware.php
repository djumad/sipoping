<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header("Authorization");
        if(!$token){
            throw new HttpResponseException(response([
                "errors" => "Unauthorize"
            ] , 401));
        }

        $user = User::where("token", $token)->first();
        
        if(!$user){
            throw new HttpResponseException(response([
                "errors" => "Unauthorize"
            ] , 401));
        }
                
        Auth::login($user);

        return $next($request);
    }
}
