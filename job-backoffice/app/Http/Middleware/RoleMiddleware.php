<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next,  ...$roles): Response
    {
        // check has no access
        if(auth()->check()){
            $role = auth()->user()->role;
            $hassAccess = in_array($role, $roles);
            if(!$hassAccess){
                abort(403);
            }
        }
        //has access
        return $next($request);
    }
}
