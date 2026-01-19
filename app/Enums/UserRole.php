<?php
namespace App\Enums;

enum UserRole: string {
    case ADMIN          = 'admin';
    case COORDINATOR    = 'coordinator';
    case OPERATION_HEAD = 'operation_head';
    case CUSTOMER       = 'customer';
    case WORKER         = 'worker';
}
