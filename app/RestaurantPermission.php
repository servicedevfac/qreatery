<?php

namespace App;

enum RestaurantPermission: string
{
    case RestaurantView = 'restaurant.view';
    case RestaurantUpdate = 'restaurant.update';
    case MembersView = 'members.view';
    case MembersManage = 'members.manage';
    case MenuManage = 'menu.manage';
    case OrdersView = 'orders.view';
    case OrdersManage = 'orders.manage';
    case PaymentsManage = 'payments.manage';
    case ReportsView = 'reports.view';
}
