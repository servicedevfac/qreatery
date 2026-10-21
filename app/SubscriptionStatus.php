<?php

namespace App;

enum SubscriptionStatus: string
{
    case Trialing = 'trialing';
    case Active = 'active';
    case GracePeriod = 'grace_period';
    case Suspended = 'suspended';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
}
