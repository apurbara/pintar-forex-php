<?php

use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

define('BASE_PATH', dirname(__FILE__));

require_once "vendor/autoload.php";

$config = new PhpFile('migration.php');
$path = array(
    BASE_PATH . "/src/SharedContext/Domain/ValueObject",
//    BASE_PATH . "/src/SharedContext/Domain/Model",
    BASE_PATH . "/src/SharedContext/Domain/Enum",
    BASE_PATH . "/src/Company/Domain/Model",
    BASE_PATH . "/src/Sales/Domain/Model",
);
$isDevMode = true; //generate proxy manually if entity not found

// $ormConfig = Setup::createAttributeMetadataConfiguration($path, $isDevMode);
$ormConfig = ORMSetup::createAttributeMetadataConfiguration($path, $isDevMode);
$ormConfig->setSchemaIgnoreClasses([
        \Sales\Domain\Model\Personnel::class,
        \Sales\Domain\Model\CustomerVerification::class,
        \Sales\Domain\Model\SalesActivity::class,
        \Sales\Domain\Model\Personnel\Sales::class,
        \Sales\Domain\Model\AreaStructure\Area::class,
    
]);

$conn = array(
    'driver' => 'pdo_mysql',
    'host' => 'localhost',
    'user' => 'root',
    'password' => 'astarte1',
    'dbname' => 'pintar-forex_test',
);
$entityManager = EntityManager::create($conn, $ormConfig);

return DependencyFactory::fromEntityManager($config, new ExistingEntityManager($entityManager));
