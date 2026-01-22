<?php

namespace App\Enums;

enum Transmission: string
{
    case Manual = 'manual';
    case Automatic = 'automatic';
    case Cvt = 'cvt';
    case Automated = 'automated';
}
