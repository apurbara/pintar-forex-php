<?php

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Setup;

define('BASE_PATH', dirname(__FILE__));

require_once "vendor/autoload.php";

$isDevMode = true; //generate proxy manually if entity not found
$generateDbPath = array(
    BASE_PATH . "/src/SharedContext/Domain/ValueObject",
    BASE_PATH . "/src/Innov/Domain/Model",
);

//$generateProxyPath = [
//    BASE_PATH . "/src/SharedContext/Infrastructure/Persistence/Doctrine/Mapping/ValueObject",
//    BASE_PATH . "/src/SharedContext/Infrastructure/Persistence/Doctrine/Mapping",
//];

//$doctrineConfig = Setup::createXMLMetadataConfiguration($generateDbPath, $isDevMode);
$doctrineConfig = Setup::createAttributeMetadataConfiguration($generateDbPath, $isDevMode);
//$doctrineConfig->setSchemaIgnoreClasses($schemaIgnoreClasses)
//$doctrineConfig = Setup::createXMLMetadataConfiguration($generateProxyPath, false);
//$doctrineConfig->setProxyDir(dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . "bara-innov_proxy_cache");

$conn = array(
    'driver' => 'pdo_mysql',
    'user' => 'root',
    'password' => 'astarte1',
    'dbname' => 'bara-vinov_dev',
);
//$conn = array(
//    'driver' => 'pdo_sqlite',
//    'path' => BASE_PATH . "/tests/database.sqlite",
//);

$entityManager = EntityManager::create($conn, $doctrineConfig);
$entityManager->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

return ConsoleRunner::createHelperSet($entityManager);