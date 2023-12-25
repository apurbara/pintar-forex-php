<?php

namespace App\Providers;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\ManagerBC\ManagerRoleInterface;
use App\Http\Controllers\SalesBC\SalesRoleInterface;
use App\Http\Controllers\UserBC\ByPersonnel\PersonnelRoleInterface;
use App\Http\Controllers\UserRole\UserRoleBuilder;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Resources\Exception\RegularException;
use function request;

class UserRoleProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->singleton(CompanyUserRoleInterface::class,
                fn(Application $app) => $this->generateCompanyUserRole());
        $this->app->singleton(ManagerRoleInterface::class,
                fn(Application $app) => $this->generateManagerUserRole());
        $this->app->singleton(SalesRoleInterface::class,
                fn(Application $app) => $this->generateSalesUserRole());
        $this->app->singleton(PersonnelRoleInterface::class,
                fn(Application $app) => $this->generatePersonnelUserRole());
    }

    private function generateCompanyUserRole()
    {
        $userRole = UserRoleBuilder::generateUserRole(request());
        if (!($userRole instanceof CompanyUserRoleInterface)) {
            throw RegularException::forbidden('unauhtorized to access company asset');
        }
        return $userRole;
    }

    private function generatePersonnelUserRole()
    {
        $userRole = UserRoleBuilder::generateUserRole(request());
        if (!($userRole instanceof PersonnelRoleInterface)) {
            throw RegularException::forbidden('unauhtorized to access personnel asset');
        }
        return $userRole;
    }

    private function generateManagerUserRole()
    {
        $userRole = UserRoleBuilder::generateUserRole(request());
        if (!($userRole instanceof ManagerRoleInterface)) {
            throw RegularException::forbidden('unauhtorized to access manager asset');
        }
        return $userRole;
    }

    private function generateSalesUserRole()
    {
        $userRole = UserRoleBuilder::generateUserRole(request());
        if (!($userRole instanceof SalesRoleInterface)) {
            throw RegularException::forbidden('unauhtorized to access sales asset');
        }
        return $userRole;
    }
}
