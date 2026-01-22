<?php

namespace App\Enums;

enum FuelType: string
{
    case Gasoline = 'gasoline';
    case Ethanol = 'ethanol';
    case Flex = 'flex';
    case Diesel = 'diesel';
    case Electric = 'electric';
    case Hybrid = 'hybrid';
}
