<?php

// ------------------
// Enum for Rental Status
// ------------------

namespace App\Enums;

enum RentalStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case OUT_FOR_DELIVERY = 'out_for_delivery';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
}
