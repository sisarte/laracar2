<?php

namespace App\Enums;

enum VehicleStatus: string
{
    case Active = 'active';
    case Sold = 'sold';
    case Paused = 'paused';
}
