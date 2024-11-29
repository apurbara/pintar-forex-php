<?php

namespace Sales\Domain\Task\Customer;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;

readonly class UpdateCustomerRatingPayload extends AbstractEntityMutationPayload
{

    public ?int $rating;

    public function setRating(?int $rating)
    {
        $this->rating = $rating;
        return $this;
    }
}
