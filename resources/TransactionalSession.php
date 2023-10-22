<?php

namespace Resources;

interface TransactionalSession {
    /**
     * @param callable $operation
     */
    public function executeAtomically(callable $operation);
}
