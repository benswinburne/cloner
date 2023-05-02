<?php

namespace Anfischer\Cloner;

use Anfischer\Cloner\Exceptions\NoCompatiblePersistenceStrategyFound;
use Anfischer\Cloner\Enums\MissingStrategies;
use Anfischer\Cloner\Strategies\PersistNullStrategy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection;
use ReflectionObject;

class PersistenceService implements PersistenceServiceInterface
{
    /**
     * Relationships will be cloned except for those with the specified names
     *
     * @var array
     */
    private $except = [];

    /**
     * Only relationships will be cloned with these specified names
     *
     * @var array
     */
    private $only = [];

    /**
     * Set the list of relationships which should not be cloned
     *
     * @param array $relationships
     * @return self
     */
    public function except(array $relationships)
    {
        $this->except = $relationships;

        return $this;
    }

    /**
     * Set the list of relationships which should be the only ones cloned
     *
     * @param array $relationships
     * @return self
     */
    public function only(array $relationships)
    {
        $this->only = $relationships;

        return $this;
    }

    /**
     * Persists a model and its relationships
     *
     * @param Model $model
     * @return Model
     */
    public function persist(Model $model) : Model
    {
        $model->save();
        return $this->persistRecursive($model);
    }

    /**
     * Recursively persists a model and its relationships
     *
     * @param $parent
     * @return mixed
     */
    private function persistRecursive($parent)
    {
        Collection::wrap($parent)->each(function ($model) {
            Collection::wrap($model->getRelations())->filter(function ($relationModel) {
                return ! is_a($relationModel, Pivot::class);
            })
            ->filter(function ($relationModel, $relationName) {
                return collect($this->only)->when(
                    !empty($this->only),
                    fn ($only) => $only->contains($relationName),
                    fn () => true
                );
            })->reject(function ($relationModel, $relationName) {
                return collect($this->except)->when(
                    !empty($this->except),
                    fn ($except) => $except->contains($relationName),
                    fn () => false
                );
            })->each(function ($relationModel, $relationName) use ($model) {
                $className = get_class((new ReflectionObject($model))->newInstance()->{$relationName}());
                $strategy = $this->getPersistenceStrategy($className);
                (new $strategy($model))->persist($relationName, $relationModel);

                $this->persistRecursive($relationModel);
            });
        });

        return $parent;
    }

    /**
     * Gets the strategy to use for persisting a relation type
     *
     * @param string $relationType
     * @return string
     */
    public function getPersistenceStrategy(string $relationType): string
    {
        $config = config('cloner.persistence_strategies');

        return collect($config)->get($relationType, function () use ($relationType) {
            $behaviour = config('cloner.missing_stragegies_should');

            return match($behaviour) {
                MissingStrategies::SKIP_SILENTLY => PersistNullStrategy::class,
                MissingStrategies::SHOULD_THROW =>
                    fn () => throw NoCompatiblePersistenceStrategyFound::forType($relationType),
            };
        });
    }
}
