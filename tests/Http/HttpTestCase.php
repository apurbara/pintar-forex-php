<?php

namespace Tests\Http;

use App\Exceptions\Handler;
use DateTimeImmutable;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Throwable;
use TypeError;

abstract class HttpTestCase extends \Tests\TestCase
{
    //     use \Laravel\Lumen\Testing\DatabaseMigrations; //comment me after link to db made (migration and sqlite_sequence table exist, or its actually simpler to just copy a database.sqlite from template :D), if not this will always reset db state

    protected ConnectionInterface $connection;

    protected function setUp(): void
    {
        parent::setUp();
        //        $this->disableExceptionHandling();
        $this->connection = DB::connection();
        $this->connection->statement('set global max_connections = 800;'); //sometime bulk test cause 'to many connection' error - mysql env
        $this->connection->statement('SET FOREIGN_KEY_CHECKS=0;'); //to enable table truncate without hassle - mysql env
    }

    protected function disableExceptionHandling()
    {
        $this->oldExceptionHandler = $this->app->make(ExceptionHandler::class);
        $this->app->instance(
            ExceptionHandler::class,
            new class extends Handler
            {

                public function __construct()
                {
                }

                public function report(Throwable $e)
                {
                }

                public function render($request, Throwable $e)
                {
                    throw $e;
                }
            }
        );
    }

    //
    protected function assertTypeErrorThrown()
    {
        $this->disableExceptionHandling();
        $this->expectException(TypeError::class);
    }

    //
    protected function stringOfCurrentTime(): string
    {
        return (new DateTimeImmutable('now', new \DateTimeZone('Asia/Jakarta')))->format('Y-m-d H:i:sP');
    }
    
    protected function stringOfJakartaCurrentTime(): string
    {
        return (new DateTimeImmutable('now', new \DateTimeZone('Asia/Jakarta')))->format('Y-m-d H:i:sP');
    }

    protected function jakartaDateTimeFormat(string $datetime): string
    {
        return (new DateTimeImmutable($datetime, new \DateTimeZone('Asia/Jakarta')))->format('Y-m-d H:i:sP');
    }
}
