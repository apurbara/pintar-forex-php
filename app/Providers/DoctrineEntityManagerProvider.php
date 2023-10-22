<?php

namespace App\Providers;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Illuminate\Support\ServiceProvider;
use function env;

define('BASE_PATH', dirname(__FILE__, 3));

class DoctrineEntityManagerProvider extends ServiceProvider
{

//    const PATH = [
//            BASE_PATH . "/src/SharedContext/Domain/ValueObject",
//            BASE_PATH . "/src/SharedContext/Domain/Model",
//            BASE_PATH . "/src/Innov/Domain/Model",
//    ];

    public function register()
    {
        $this->app->singleton(EntityManager::class, function ($app) {
            $connection = [
                'driver' => env('DOCTRINE_DB_CONNECTION'),
                'user' => env('DB_USERNAME'),
                'password' => env('DB_PASSWORD'),
                'dbname' => env('DB_DATABASE')
            ];
//            $config = ORMSetup::createAttributeMetadataConfiguration(self::PATH, boolval(env('DOCTRINE_IS_DEV_MODE')));
            $config = ORMSetup::createAttributeMetadataConfiguration([], boolval(env('DOCTRINE_IS_DEV_MODE')));
            $config->setProxyDir(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . "doctrine_proxy");
            $em = EntityManager::create($connection, $config);
            $em->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
            return $em;
        });
    }

}
