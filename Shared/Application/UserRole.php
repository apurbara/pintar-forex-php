<?php

namespace Shared\Application;

enum UserRole: string
{

    case ADMIN = "ADMIN";
    case MANAGER = "MANAGER";
    case SALES = "SALES";
}
