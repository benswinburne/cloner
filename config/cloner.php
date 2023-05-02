<?php

use Anfischer\Cloner\Enums\MissingStrategies;

return [
    /*
    |--------------------------------------------------------------------------
    | Missing Persistence Strategy Behaviour
    |--------------------------------------------------------------------------
    |
    | When a persistance strategy is missing, the default behaviour of the
    | cloner is to throw an exception which allows a transaction to roll
    | back and cancel any perstance. This behaviour may be changed
    |
    */
    'missing_stragegies_should' => MissingStrategies::SHOULD_THROW,

    /*
    |--------------------------------------------------------------------------
    | Persistence Strategies
    |--------------------------------------------------------------------------
    |
    | These relationship types and persistence strategies are those which
    | will be used at runtime to determine how to persist a particular
    | type of relationship.
    |
    */

    'persistence_strategies' => [
        Illuminate\Database\Eloquent\Relations\HasOne::class =>
            Anfischer\Cloner\Strategies\PersistHasOneRelationStrategy::class,
        Illuminate\Database\Eloquent\Relations\HasMany::class =>
            Anfischer\Cloner\Strategies\PersistHasManyRelationStrategy::class,
        Illuminate\Database\Eloquent\Relations\BelongsToMany::class =>
            Anfischer\Cloner\Strategies\PersistBelongsToManyRelationStrategy::class,
    ]
];
