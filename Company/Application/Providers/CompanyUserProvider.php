<?php

namespace Company\Application\Providers;

use Company\Domain\Model\Admin;
use Company\Domain\Model\CompanyUser;
use Company\Domain\Model\Manager;
use Company\Domain\Model\Manager\Sales;
use Doctrine\ORM\EntityManager;
use Illuminate\Support\ServiceProvider;
use Resources\Exception\RegularException;
use Shared\Application\JwtHelper;
use Shared\Application\UserRole;
use function app;
use function request;

class CompanyUserProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CompanyUser::class, function () {
            $userData = JwtHelper::decodeJwtToken(request());
            return match ($userData['userRole']) {
                UserRole::ADMIN->value => app(EntityManager::class)->getRepository(Admin::class)->ofId($userData['userId']),
                UserRole::MANAGER->value => app(EntityManager::class)->getRepository(Manager::class)->ofId($userData['userId']),
                UserRole::SALES->value => app(EntityManager::class)->getRepository(Sales::class)->ofId($userData['userId']),
                default => throw RegularException::unauthorized('no privilege to access manager resources'),
            };
        });
    }
}
