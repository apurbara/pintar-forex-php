<?php

namespace Tests\Http\Record;

use Illuminate\Database\ConnectionInterface;

interface IRecord
{

    public function insert(ConnectionInterface $connection): void;
}
