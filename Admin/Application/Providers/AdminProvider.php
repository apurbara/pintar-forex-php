<?php

namespace Admin\Application\Providers;

use Admin\Domain\Model\Admin;
use Doctrine\ORM\EntityManager;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Resources\Exception\RegularException;
use Shared\Application\JwtHelper;
use Shared\Application\UserRole;
use function app;
use function request;

class AdminProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->app->singleton(Admin::class, function () {
            $userData = JwtHelper::decodeJwtToken(request());
            return match ($userData['userRole']) {
                UserRole::ADMIN->value => app(EntityManager::class)->getRepository(Admin::class)->ofId($userData['userId']),
                default => throw RegularException::unauthorized('no privilege to access admin resources'),
            };
        });
    }
    
    public function provides(): array
    {
        return [Admin::class];
    }
}
