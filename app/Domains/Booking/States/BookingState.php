<?php

namespace App\Domains\Booking\States;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

/**
 * FSM переходы:
 *   pending  → confirmed → completed
 *   pending  → cancelled
 *   confirmed → cancelled
 *   confirmed → no_show
 */
abstract class BookingState extends State
{
    abstract public function label(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(PendingState::class)
            ->allowTransition(PendingState::class, ConfirmedState::class)
            ->allowTransition(PendingState::class, CancelledState::class)
            ->allowTransition(ConfirmedState::class, CompletedState::class)
            ->allowTransition(ConfirmedState::class, CancelledState::class)
            ->allowTransition(ConfirmedState::class, NoShowState::class);
    }
}
