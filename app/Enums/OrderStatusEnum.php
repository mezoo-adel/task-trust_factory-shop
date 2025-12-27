<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case PAID = 'paid';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
}
