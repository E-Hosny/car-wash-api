<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ProviderOnlyMiddleware
{
    /**
     * يسمح فقط للمستخدمين بدور "provider" (مقدم الخدمة).
     * لا يسمح لـ worker أو customer أو admin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if (Auth::user()->role !== 'provider') {
            return response()->json([
                'success' => false,
                'message' => 'هذا الإجراء مسموح فقط لمقدم الخدمة (provider).',
            ], 403);
        }

        return $next($request);
    }
}
