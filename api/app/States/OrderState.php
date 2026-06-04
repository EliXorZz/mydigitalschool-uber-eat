<?php

namespace App\States;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

/**
 * Order state abstract class.
 */
abstract class OrderState extends State
{
    public static function config(): StateConfig
    {
        return parent::config()
            ->default(OrderPending::class)
            ->allowTransition(OrderPending::class, OrderConfirmed::class)
            ->allowTransition(OrderConfirmed::class, OrderPreparing::class)
            ->allowTransition(OrderPreparing::class, OrderReady::class)
            ->allowTransition(OrderReady::class, OrderDelivered::class);
    }
}
