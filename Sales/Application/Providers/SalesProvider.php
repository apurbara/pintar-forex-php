<?php

namespace Sales\Application\Providers;

use Doctrine\ORM\EntityManager;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Resources\Exception\RegularException;
use Sales\Domain\Model\Sales;
use Shared\Application\JwtHelper;
use Shared\Application\UserRole;
use function app;
use function request;

class SalesProvider extends ServiceProvider implements DeferrableProvider
{

    public function register(): void
    {
        $this->app->singleton(Sales::class, function () {
            $userData = JwtHelper::decodeJwtToken(request());
            return match ($userData['userRole']) {
                UserRole::SALES->value => app(EntityManager::class)->getRepository(Sales::class)->ofId($userData['userId']),
                default => throw RegularException::unauthorized('no privilege to access sales resources'),
            };
        });
    }
    
    public function provides(): array
    {
        return [Sales::class];
    }
}
