<?php

namespace App\Http\Controllers;

enum UserRole: string
{

    case ADMIN = "ADMIN";
    case MANAGER = "MANAGER";
    case SALES = "SALES";
}
