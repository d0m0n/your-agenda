<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminBasicAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $username = config('admin_security.basic_auth_username');
        $password = config('admin_security.basic_auth_password');

        if (! $username || ! $password) {
            return $next($request);
        }

        $providedUsername = (string) $request->getUser();
        $providedPassword = (string) $request->getPassword();

        if (hash_equals($username, $providedUsername) && hash_equals($password, $providedPassword)) {
            return $next($request);
        }

        return response('管理画面にアクセスするには認証が必要です。', 401, [
            'WWW-Authenticate' => 'Basic realm="Admin Area"',
        ]);
    }
}
