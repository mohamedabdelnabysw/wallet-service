<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class EnsureIdempotency
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('Idempotency-Key');

        if (!$key) {
            return response()->json(['message' => 'Idempotency-Key header is required'], 400);
        }

        $cacheKey = 'idempotency_' . $key;

        if (Cache::has($cacheKey)) {
            $data = Cache::get($cacheKey);
            return response($data['content'], $data['status'], $data['headers']);
        }

        $lock = Cache::lock($cacheKey . '_lock', 10);

        if (!$lock->get()) {
            return response()->json(['message' => 'Request currently processing'], 409);
        }

        try {
            $response = $next($request);

            if ($response->isSuccessful()) {
                Cache::put($cacheKey, [
                    'content' => $response->getContent(),
                    'status' => $response->getStatusCode(),
                    'headers' => $response->headers->all(),
                ], now()->addDay());
            }

            return $response;
        } finally {
            $lock->release();
        }
    }
}
