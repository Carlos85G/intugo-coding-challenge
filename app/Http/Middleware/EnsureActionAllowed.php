<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\RuleEvaluator;

class EnsureActionAllowed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $action): Response
    {
        /** @var bool */
        $isAllowed = app()
                    ->make(RuleEvaluator::class)
                    ->isUserAllowed(
                        $action,
                        $request->user()
                    );

        if(!$isAllowed) {
            return response()->json([
                "message" => 'Access denied - User does not have permission for this action'
            ], 403);
        }

        return $next($request);
    }
}
