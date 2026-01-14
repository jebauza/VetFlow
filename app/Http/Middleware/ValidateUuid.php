<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ValidateUuid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $parameter = 'id'): Response
    {
        $id = $request->route($parameter);

        if ($id && !Str::isUuid($id)) {
            return response()->json([
                'message' => __('The :parameter must be a valid UUID.', ['parameter' => $parameter]),
            ], 422);
        }

        return $next($request);
    }
}
