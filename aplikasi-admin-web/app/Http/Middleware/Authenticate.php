<?php

namespace App\Http\Middleware;

use Closure, JWTAuth;
use Illuminate\Contracts\Auth\Factory as Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use App\Helpers\Api;
use Concerns\InteractsWithInput;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (! $token = $request->bearerToken()) 
            return response()->json(Api::format('false', '', 'Token is not provided'), 200);
            
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (TokenExpiredException $e) {
            return response()->json(Api::format('false', '', 'JWT Token Expired'), 200);
        } catch (TokenInvalidException $e) {
            $message = $e->getMessage();
            return response()->json(Api::format('false', '', $message), 200);
        } catch (JWTException $e) {
            return response()->json(Api::format('false', '', 'There is a problem with JWT Token'), 200);
        }

        if (! $user)
            return response()->json(Api::format('false', '', 'User not Found'), 200);

        return $next($request);
    }
}
