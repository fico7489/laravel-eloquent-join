<?php

namespace Fico7489\Laravel\SortJoin\Traits;

use Fico7489\Laravel\SortJoin\Relations\BelongsToJoin;
use Fico7489\Laravel\SortJoin\Relations\HasOneJoin;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

trait SortJoinTrait
{
    private $selected = false;
    private $joinedTables = [];
    private $relationClauses = [];

    public function scopeWhereJoinRelation(Builder $builder, $column, $operator = null, $value = null, $boolean = 'and')
    {
        $this->relationClauses[] = ['column' => $column, 'operator' => $operator, 'value' => $value, 'boolean' => $boolean];
    }

    public function scopeOrWhereJoinRelation(Builder $builder, $column, $operator = null, $value)
    {
        $this->relationClauses[] = ['column' => $column, 'operator' => $operator, 'value' => $value, 'boolean' => 'or'];
    }


    public function scopeWhereJoin(Builder $builder, $column, $operator = null, $value = null, $boolean = 'and')
    {
        $column = $this->performJoin($builder, $column);
        return $builder->where($column, $operator, $value, $boolean);
    }

    public function scopeOrWhereJoin(Builder $builder, $column, $operator = null, $value)
    {
        $column = $this->performJoin($builder, $column);
        return $builder->orWhere($column, $operator, $value);
    }

    public function scopeOrderByJoin(Builder $builder, $column, $sortBy = 'asc')
    {
        $column = $this->performJoin($builder, $column);
        return $builder->orderBy($column, $sortBy);
    }


    private function performJoin($builder, $relations)
    {
        $relations = explode('.', $relations);

        $column = end($relations);
        $baseTable = $this->getTable();
        $baseModel = $this;

        $currentTable = $this->getTable();
        $currentModel = $this;

        foreach ($relations as $relation) {
            if ($relation == $column) {
                //last item in $relations argument is sort|where column
                continue;
            }

            $relatedRelation = $currentModel->$relation();
            $relatedModel = $relatedRelation->getRelated();
            $relatedPrimaryKey = $relatedModel->primaryKey;
            $relatedTable = $relatedModel->getTable();

            if (array_key_exists($relation, $this->joinedTables)) {
                $relatedTableAlias = $this->joinedTables[$relation];
            } else {
                $relatedTableAlias = uniqid();

                $keyRelated = $relatedRelation->getForeignKey();
                if ($relatedRelation instanceof BelongsTo) {
                    $builder->leftJoin($relatedTable . ' as ' . $relatedTableAlias, $relatedTableAlias . '.' . $relatedPrimaryKey, '=', $currentTable . '.' . $keyRelated);
                } elseif ($relatedRelation instanceof HasOne) {
                    $keyRelated = last(explode('.', $keyRelated));
                    $builder->leftJoin($relatedTable . ' as ' . $relatedTableAlias, $relatedTableAlias . '.' . $keyRelated, '=', $currentTable . '.' . $relatedPrimaryKey);
                }

                //by default apply where deleted_at is null if model is using soft deletes, if any where clause have deleted_at columnn do not apply
                $columnsWhere = collect($relatedModel->relationClauses)->pluck('column')->toArray();
                if (method_exists($relatedModel, 'getQualifiedDeletedAtColumn') &&  ! in_array('deleted_at', $columnsWhere)) {
                    $builder->where([$relatedTableAlias . '.deleted_at' => null]);
                }

                foreach ($relatedModel->relationClauses as $relationClause) {
                    $builder->where($relationClause['column'], $relationClause['operator'], $relationClause['value'], $relationClause['boolean']);
                }
            }

            $currentTable = $relatedTableAlias;
            $currentModel = $relatedModel;

            $this->joinedTables[$relation] = $relatedTableAlias;
        }

        if (! $this->selected) {
            $this->selected = true;
            $builder->select($baseTable . '.*')->groupBy($baseTable . '.' . $baseModel->primaryKey);
        }

        return $currentTable . '.' . $column;
    }

    /**
     * Define a one-to-one relationship.
     *
     * @param  string  $related
     * @param  string  $foreignKey
     * @param  string  $localKey
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasOneJoin($related, $foreignKey = null, $localKey = null)
    {
        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $instance = new $related;
        $localKey = $localKey ?: $this->getKeyName();

        return new HasOneJoin($instance->newQuery(), $this, $instance->getTable().'.'.$foreignKey, $localKey);
    }

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @param  string  $related
     * @param  string  $foreignKey
     * @param  string  $otherKey
     * @param  string  $relation
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToJoin($related, $foreignKey = null, $otherKey = null, $relation = null)
    {
        if (is_null($relation)) {
            list($current, $caller) = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            $relation = $caller['function'];
        }

        if (is_null($foreignKey)) {
            $foreignKey = Str::snake($relation).'_id';
        }

        $instance = new $related;
        $query = $instance->newQuery();
        $otherKey = $otherKey ?: $instance->getKeyName();

        return new BelongsToJoin($query, $this, $foreignKey, $otherKey, $relation);
    }
}
