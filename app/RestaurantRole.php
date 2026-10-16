<?php

namespace App;

enum RestaurantRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Manager = 'manager';
    case Staff = 'staff';
    case Cashier = 'cashier';
}
