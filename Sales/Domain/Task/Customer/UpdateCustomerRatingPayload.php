<?php

namespace Sales\Domain\Task\Customer;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;

readonly class UpdateCustomerRatingPayload
{

    public ?string $customerAssignmentId;
    public ?int $rating;

    public function setCustomerAssignmentId(?string $customerAssignmentId)
    {
        $this->customerAssignmentId = $customerAssignmentId;
        return $this;
    }

    public function setRating(?int $rating)
    {
        $this->rating = $rating;
        return $this;
    }
}
