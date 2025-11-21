<?php
namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

class ExampleMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        // Example: add header, check auth, etc.
        $response = $next($request);
        return $response;
    }
}
