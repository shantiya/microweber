<?php

namespace MicroweberPackages\App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;
use MicroweberPackages\User\User;

class ApiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        if (Auth::check() &&  Auth::user()->is_admin == 1) {
            return $next($request);
        }

        $expiration = config('sanctum.expiration');

        $token = $request->bearerToken();
        if (!$token){
            return $this->_returnError($request);
        }

        $model = Sanctum::$personalAccessTokenModel;
        $accessToken = $model::findToken($token);

        if (! $accessToken || ($expiration && $accessToken->created_at->lte(now()->subMinutes($expiration)))) {
            return $this->_returnError($request);
        }

        return $next($request);
    }


    private function _returnError($request){
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Api unauthorized'], 401);
        }
        return abort(403, 'Api unauthorized');
    }
}