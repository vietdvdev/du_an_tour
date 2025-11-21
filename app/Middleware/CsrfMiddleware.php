<?php
namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class CsrfMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        if ($request->method === 'POST') {
            $token = $request->input('_token');
            if (!$token || $token !== Session::token()) {
                return Response::make('<h1>419 Page Expired (CSRF)</h1>', 419);
            }
        }
        return $next($request);
    }
}
