<?php

namespace SharedContext\Domain\Enum;

enum ManagementApprovalStatus: string
{
    case WAITING_FOR_APPROVAL = 'WAITING_FOR_APPROVAL';
    case REJECTED = 'REJECTED';
    case APPROVED = 'APPROVED';
}
