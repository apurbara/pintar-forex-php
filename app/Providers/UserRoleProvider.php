<?php

namespace App\Providers;

use App\Http\Controllers\JwtHelper;
use App\Http\Controllers\UserBC\ByManager\ManagerRole;
use App\Http\Controllers\UserBC\BySales\SalesRole;
use App\Http\Controllers\UserRole;
use Illuminate\Support\ServiceProvider;
use Resources\Exception\RegularException;
use function request;

class UserRoleProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->singleton(ManagerRole::class, function (){
            $userData = JwtHelper::decodeJwtToken(request());
            return match ($userData['userRole']) {
                UserRole::MANAGER->value => new ManagerRole($userData['userId']),
                default  => throw RegularException::unauthorized('no privilege to access manager resources'),
            };
        });
        $this->app->singleton(SalesRole::class, function (){
            $userData = JwtHelper::decodeJwtToken(request());
            return match ($userData['userRole']) {
                UserRole::SALES->value => new SalesRole($userData['userId']),
                default  => throw RegularException::unauthorized('no privilege to access sales resources'),
            };
        });
    }
}
