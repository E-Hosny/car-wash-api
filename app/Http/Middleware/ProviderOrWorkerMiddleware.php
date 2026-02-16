<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ProviderOrWorkerMiddleware
{
    /**
     * يسمح للمستخدمين بدور "provider" أو "worker" (عرض المواعيد فقط).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $role = Auth::user()->role;
        if (! in_array($role, ['provider', 'worker'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'هذا المحتوى متاح فقط لمقدم الخدمة أو العامل.',
            ], 403);
        }

        return $next($request);
    }
}
