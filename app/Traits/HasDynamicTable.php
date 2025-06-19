<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

trait HasDynamicTable
{
    protected $baseTableName;
    protected $currentDatabaseName; // Make sure this property exists

    /**
     * Initialize the trait
     */
    public function initializeHasDynamicTable()
    {
        if (!$this->baseTableName) {
            $this->baseTableName = $this->getTable();
        }
    }

    /**
     * Set table with database prefix dynamically
     */
    public function setDynamicTable($databaseName)
    {
        if (!$this->baseTableName) {
            $this->baseTableName = $this->getTable();
        }

        if ($databaseName) {
            $this->currentDatabaseName = $databaseName; // Store database name
            $this->setTable($databaseName . '.' . $this->baseTableName);
        }
        return $this;
    }

    /**
     * Create instance for specific database
     */
    public static function forDatabase($databaseName)
    {
        $instance = new static();
        return $instance->setDynamicTable($databaseName);
    }

    /**
     * Get the base table name without database prefix
     */
    public function getBaseTableName()
    {
        return $this->baseTableName ?: $this->getTable();
    }

    /**
     * Reset table to base name (without database prefix)
     */
    public function resetTable()
    {
        $this->setTable($this->getBaseTableName());
        return $this;
    }

    // ADD ALL THE RELATIONSHIP METHODS FROM ABOVE HERE
    // (Copy all the methods from the first part of this artifact)

    /**
     * Load relationships for paginated results
     */
    public function loadRelationsForPaginator($paginator, $relations)
    {
        if ($paginator->isEmpty()) {
            return $paginator;
        }

        $this->loadRelationsForModels($paginator->items(), $relations);
        return $paginator;
    }

    /**
     * Load relations for a collection of models
     */
    public function loadRelationsForModels($models, $relations)
    {
        $relations = is_string($relations) ? [$relations] : $relations;

        foreach ($relations as $relationName) {
            $this->loadSingleRelation($models, $relationName);
        }
    }

    /**
     * Load a single relation for all models
     */
    protected function loadSingleRelation($models, $relationName)
    {
        $firstModel = collect($models)->first();

        if (!$firstModel || !method_exists($firstModel, $relationName)) {
            return;
        }

        $relation = $firstModel->$relationName();

        if ($relation instanceof \Illuminate\Database\Eloquent\Relations\BelongsTo) {
            $this->loadBelongsToForCollection($models, $relationName, $relation);
        } elseif ($relation instanceof \Illuminate\Database\Eloquent\Relations\HasMany) {
            $this->loadHasManyForCollection($models, $relationName, $relation);
        } elseif ($relation instanceof \Illuminate\Database\Eloquent\Relations\HasOne) {
            $this->loadHasOneForCollection($models, $relationName, $relation);
        }
    }

    /**
     * Load belongsTo relation for collection
     */
    protected function loadBelongsToForCollection($models, $relationName, $relation)
    {
        $foreignKey = $relation->getForeignKeyName();
        $ownerKey = $relation->getOwnerKeyName();
        $relatedModel = $relation->getRelated();

        $foreignKeys = collect($models)->pluck($foreignKey)->unique()->filter();

        if ($foreignKeys->isEmpty()) {
            return;
        }

        if (method_exists($relatedModel, 'setDynamicTable') && isset($this->currentDatabaseName)) {
            $relatedModel->setDynamicTable($this->currentDatabaseName);
        }

        $relatedRecords = $relatedModel->whereIn($ownerKey, $foreignKeys)
            ->get()
            ->keyBy($ownerKey);

        collect($models)->each(function ($model) use ($relationName, $foreignKey, $relatedRecords) {
            $model->setRelation($relationName, $relatedRecords->get($model->$foreignKey));
        });
    }

    /**
     * Load hasMany relation for collection
     */
    protected function loadHasManyForCollection($models, $relationName, $relation)
    {
        $foreignKey = $relation->getForeignKeyName();
        $localKey = $relation->getLocalKeyName();
        $relatedModel = $relation->getRelated();

        $localKeys = collect($models)->pluck($localKey)->unique()->filter();

        if ($localKeys->isEmpty()) {
            return;
        }

        if (method_exists($relatedModel, 'setDynamicTable') && isset($this->currentDatabaseName)) {
            $relatedModel->setDynamicTable($this->currentDatabaseName);
        }

        $relatedRecords = $relatedModel->whereIn($foreignKey, $localKeys)->get();
        $groupedRecords = $relatedRecords->groupBy($foreignKey);

        collect($models)->each(function ($model) use ($relationName, $localKey, $groupedRecords) {
            $related = $groupedRecords->get($model->$localKey, collect());
            $model->setRelation($relationName, $related);
        });
    }

    /**
     * Load hasOne relation for collection
     */
    protected function loadHasOneForCollection($models, $relationName, $relation)
    {
        $foreignKey = $relation->getForeignKeyName();
        $localKey = $relation->getLocalKeyName();
        $relatedModel = $relation->getRelated();

        $localKeys = collect($models)->pluck($localKey)->unique()->filter();

        if ($localKeys->isEmpty()) {
            return;
        }

        if (method_exists($relatedModel, 'setDynamicTable') && isset($this->currentDatabaseName)) {
            $relatedModel->setDynamicTable($this->currentDatabaseName);
        }

        $relatedRecords = $relatedModel->whereIn($foreignKey, $localKeys)
            ->get()
            ->keyBy($foreignKey);

        collect($models)->each(function ($model) use ($relationName, $localKey, $relatedRecords) {
            $model->setRelation($relationName, $relatedRecords->get($model->$localKey));
        });
    }

    /**
     * Scope for loading relations with pagination support
     */
    public function scopeWithDynamic($query, $relations)
    {
        // For get() - return collection with relations
        $models = $query->get();

        if ($models->isEmpty()) {
            return $models;
        }

        $this->loadRelationsForModels($models, $relations);
        return $models;
    }
}
