<?php

namespace App\Providers;

use App\Http\Controllers\CompanyBC\AdminRole;
use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\CompanyBC\ManagerRole;
use App\Http\Controllers\CompanyBC\SalesRole;
use App\Http\Controllers\JwtHelper;
use App\Http\Controllers\UserRole;
use Illuminate\Support\ServiceProvider;
use function request;

class CompanyUserRoleProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CompanyUserRoleInterface::class, function (){
            $userData = JwtHelper::decodeJwtToken(request());
            return match ($userData['userRole']) {
                UserRole::ADMIN->value => new AdminRole($userData['userId']),
                UserRole::MANAGER->value => new ManagerRole($userData['userId']),
                UserRole::SALES->value => new SalesRole($userData['userId']),
            };
        });
    }
}
