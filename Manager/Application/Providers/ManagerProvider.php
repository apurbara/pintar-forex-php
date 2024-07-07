<?php

namespace Manager\Application\Providers;

use Doctrine\ORM\EntityManager;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Manager\Domain\Model\Manager;
use Resources\Exception\RegularException;
use Shared\Application\JwtHelper;
use Shared\Application\UserRole;
use function app;
use function request;

class ManagerProvider extends ServiceProvider implements DeferrableProvider
{

    public function register(): void
    {
        $this->app->singleton(Manager::class, function () {
            $userData = JwtHelper::decodeJwtToken(request());
            return match ($userData['userRole']) {
                UserRole::MANAGER->value => app(EntityManager::class)->getRepository(Manager::class)->ofId($userData['userId']),
                default => throw RegularException::unauthorized('no privilege to access manager resources'),
            };
        });
    }
    
    public function provides(): array
    {
        return [Manager::class];
    }
}
