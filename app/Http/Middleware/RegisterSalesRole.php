<?php

namespace App\Http\Middleware;

use App\Http\Controllers\SalesBC\BySales\SalesRoleInterface;
use App\Http\Controllers\UserBC\ByPersonnel\PersonnelRoleInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use function app;

class RegisterSalesRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $salesId = $request->route()->parameter('salesId');
        app()->singleton(SalesRoleInterface::class, fn() => app(PersonnelRoleInterface::class)->authorizedAsSales($salesId));
        return $next($request);
    }
}
