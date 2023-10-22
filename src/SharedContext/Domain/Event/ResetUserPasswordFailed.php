<?php

namespace SharedContext\Domain\Event;

use Resources\Event\EventInterface;

class ResetUserPasswordFailed implements EventInterface
{

    const NAME = 'reset-password-failed';

    public function getName(): string
    {
        return self::NAME;
    }

}
