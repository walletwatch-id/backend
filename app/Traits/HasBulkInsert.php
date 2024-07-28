<?php

// Taken from https://github.com/n1215/eloquent-bulk-save

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use LogicException;

trait HasBulkInsert
{
    /**
     * @param  Collection|Model[]  $models
     */
    public static function bulkInsert(Collection $models): bool
    {
        if (! is_subclass_of(static::class, Model::class)) {
            throw new LogicException(
                'Class using HasBulkInsert trait should be a subclass of '.Model::class
            );
        }

        // Check the argument
        foreach ($models as $model) {
            if (! $model instanceof static) {
                throw new LogicException(static::class.'::bulkInsert() cannot be used for '.\get_class($model));
            }

            if ($model->exists) {
                throw new LogicException(
                    'This Eloquent model has already been persisted: class = '.static::class.', primary key ='.$model->getKey()
                );
            }
        }

        if ($models->isEmpty()) {
            return true;
        }

        // Before save
        foreach ($models as $model) {
            if ($model->fireModelEvent('saving') === false) {
                return false;
            }
        }

        // Before insert
        foreach ($models as $model) {
            if ($model->usesUniqueIds()) {
                $model->setUniqueIds();
            }

            if ($model->fireModelEvent('creating') === false) {
                return false;
            }

            if ($model->usesTimestamps()) {
                $model->updateTimestamps();
            }
        }

        // Perform insert
        $attributesArray = static::convertModelsToArray($models);
        $saved = (new static)->newQueryWithoutScopes()->insert($attributesArray);
        if (! $saved) {
            return false;
        }

        // After insert
        foreach ($models as $model) {
            $model->exists = true;
            $model->wasRecentlyCreated = true;
            $model->fireModelEvent('created', false);
        }

        // After save
        $options = [];
        foreach ($models as $model) {
            $model->finishSave($options);
        }

        return true;
    }

    /**
     * Create array of model attributes
     *
     * @param  Collection|Model[]  $models
     */
    private static function convertModelsToArray(Collection $models): array
    {
        $attributesCollection = $models
            ->map(function (Model $model) {
                return $model->attributes;
            });

        $columns = $attributesCollection
            ->flatMap(function (array $attributesArray) {
                return array_keys($attributesArray);
            })
            ->unique()
            ->values();

        // Fill non-existent columns
        return $attributesCollection
            ->map(function (array $attributes) use ($columns) {
                foreach ($columns as $column) {
                    if (! array_key_exists($column, $attributes)) {
                        $attributes[$column] = null;
                    }
                }

                return $attributes;
            })
            ->toArray();
    }
}
