<?php

namespace App\Providers;

use App\Http\Controllers\JwtHelper;
use App\Http\Controllers\SalesBC\SalesRole;
use App\Http\Controllers\UserRole;
use Illuminate\Support\ServiceProvider;
use Resources\Exception\RegularException;
use function request;

class SalesRoleProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->singleton(SalesRole::class,
                function () {
            $userData = JwtHelper::decodeJwtToken(request());
            return match ($userData['userRole']) {
                UserRole::SALES->value => new SalesRole($userData['userId']),
                default => throw RegularException::unauthorized('no privilege to access sales resources'),
            };
        });
    }
}
