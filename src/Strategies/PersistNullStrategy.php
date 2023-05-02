<?php

namespace Anfischer\Cloner\Strategies;

use Illuminate\Database\Eloquent\Model;

class PersistNullStrategy implements PersistRelationInterface
{
    /**
     * @param Model $baseModel
     */
    public function __construct(Model $baseModel)
    {
        $this->baseModel = $baseModel;
    }

    /**
     * @param string $relationName
     * @param $model
     */
    public function persist(string $relationName, $model) : void
    {
    }
}
