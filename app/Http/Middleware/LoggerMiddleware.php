<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LoggerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $message = "API request:\n"
            . "Method: {$request->method()}\n"
            . "URI: {$request->fullUrl()}\n"
            . "Parameters: " . json_encode($request->all(), JSON_PRETTY_PRINT) . "\n"
            . "IP: {$request->ip()}\n"
            . "Time: " . now();

        Log::info($message);
        return $next($request);
    }
}
