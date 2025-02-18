<?php

namespace Shared\Application\Providers;

use Illuminate\Support\ServiceProvider;
use Resources\Application\InputRequest;
use Resources\Infrastructure\Rest\RestInputRequest;
use function request;

class InputRequestProvider extends ServiceProvider
{


    public function register()
    {
        $this->app->singleton(InputRequest::class, function() {
            return new RestInputRequest(request());
        });
    }

}
