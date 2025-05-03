<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class RateLimitOpenAiRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        $key = 'openai_requests:' . $user->id;
        
        // Получаем количество запросов за последние 24 часа
        $count = Redis::get($key) ?: 0;
        
        // Лимит - 50 запросов в сутки
        if ($count >= 50) {
            return response()->json([
                'success' => false,
                'message' => 'Превышен лимит запросов к AI-ассистенту. Попробуйте позже.'
            ], 429);
        }
        
        // Увеличиваем счетчик и устанавливаем время жизни (24 часа)
        Redis::incr($key);
        Redis::expire($key, 86400);
        
        return $next($request);
    }
} 