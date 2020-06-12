<?php

namespace App\Http\Middleware;

use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

use Closure;

class RoleAuthorization
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */

    public function handle($request, Closure $next, ...$roles)
    {
        try {
            $token = JWTAuth::parseToken();
            $user = $token->authenticate();

        } catch (TokenExpiredException $e) {
            return $this->unauthorized('Your token has expired. Please, login again.');

        } catch (TokenInvalidException $e) {
            return $this->unauthorized('Your token is invalid. Please, login again.');

        } catch (JWTException $e) {
            return $this->unauthorized('Please, attach a Bearer Token to your request');
        }

        if ($user && in_array($user->role, $roles)) {
            return $next($request);
        }

        return $this->unauthorized();
    }

    private function unauthorized($message = null)
    {
        return response()->json([
            'message' => $message ? $message : 'You are not authorized to access this resource',
            'success' => false
        ], 403);
    }
}
