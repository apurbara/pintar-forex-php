<?php

namespace Resources\Application;

interface InputRequest
{

    public function get(string $key): mixed;
}
