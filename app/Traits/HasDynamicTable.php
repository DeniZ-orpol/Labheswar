<?php

namespace App\Traits;

trait HasDynamicTable
{
    protected $baseTableName;

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
            $this->currentDatabaseName = $databaseName;
            
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

    /**
     * Override belongsTo to use dynamic database
     */
    public function belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null)
    {
        $relation = parent::belongsTo($related, $foreignKey, $ownerKey, $relation);

        if (isset($this->currentDatabaseName) && $this->currentDatabaseName) {
            $relatedModel = $relation->getRelated();

            if (method_exists($relatedModel, 'setDynamicTable')) {
                $relatedModel->setDynamicTable($this->currentDatabaseName);
                $relation->getQuery()->from($relatedModel->getTable());
            }
        }

        return $relation;
    }

    /**
     * Override hasOne to use dynamic database
     */
    public function hasOne($related, $foreignKey = null, $localKey = null)
    {
        $relation = parent::hasOne($related, $foreignKey, $localKey);

        if (isset($this->currentDatabaseName) && $this->currentDatabaseName) {
            $relatedModel = $relation->getRelated();

            if (method_exists($relatedModel, 'setDynamicTable')) {
                $relatedModel->setDynamicTable($this->currentDatabaseName);
                $relation->getQuery()->from($relatedModel->getTable());
            }
        }

        return $relation;
    }

    /**
     * Override hasMany to use dynamic database
     */
    public function hasMany($related, $foreignKey = null, $localKey = null)
    {
        $relation = parent::hasMany($related, $foreignKey, $localKey);

        if (isset($this->currentDatabaseName) && $this->currentDatabaseName) {
            $relatedModel = $relation->getRelated();

            if (method_exists($relatedModel, 'setDynamicTable')) {
                $relatedModel->setDynamicTable($this->currentDatabaseName);
                $relation->getQuery()->from($relatedModel->getTable());
            }
        }

        return $relation;
    }
}
