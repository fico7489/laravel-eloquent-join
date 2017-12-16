<?php

namespace Fico7489\Laravel\EloquentJoin\Traits;

use Fico7489\Laravel\EloquentJoin\Relations\BelongsToJoin;
use Fico7489\Laravel\EloquentJoin\Exceptions\EloquentJoinException;
use Fico7489\Laravel\EloquentJoin\Relations\HasOneJoin;
use Illuminate\Database\Eloquent\Builder;

trait EloquentJoinTrait
{
    use ExtendRelationsTrait;

    private $useTableAlias = false;
    private $selected = false;
    private $joinedTables = [];

    private $relationNotAllowedClauses = [];
    private $relationWhereClauses = [];
    private $softDelete = 'withoutTrashed';

    public function scopeSetInvalidJoin(Builder $builder, $method, $parameters)
    {
        $this->relationNotAllowedClauses[$method] = $parameters;
    }

    public function scopeSetWhereForJoin(Builder $builder, $column, $operator = null, $value = null, $boolean = 'and')
    {
        $this->relationWhereClauses[] = ['column' => $column, 'operator' => $operator, 'value' => $value, 'boolean' => $boolean];
    }

    public function scopeSetOrWhereForJoin(Builder $builder, $column, $operator = null, $value)
    {
        $this->relationWhereClauses[] = ['column' => $column, 'operator' => $operator, 'value' => $value, 'boolean' => 'or'];
    }

    public function scopeSetSoftDelete(Builder $builder, $param)
    {
        $this->softDelete = $param;
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

            $this->validateJoinQuery($relatedModel);

            if (array_key_exists($relation, $this->joinedTables)) {
                $relatedTableAlias = $this->joinedTables[$relation];
            } else {
                $relatedTableAlias = $this->useTableAlias ? uniqid() : $relatedTable;

                $joinQuery = $relatedTable . ($this->useTableAlias ? ' as ' . $relatedTableAlias : '');
                if ($relatedRelation instanceof BelongsToJoin) {
                    $keyRelated = $relatedRelation->getForeignKey();

                    $builder->leftJoin($joinQuery, $relatedTableAlias . '.' . $relatedPrimaryKey, '=', $currentTable . '.' . $keyRelated);
                } elseif ($relatedRelation instanceof HasOneJoin) {
                    $keyRelated = $relatedRelation->getQualifiedForeignKeyName();
                    $keyRelated = last(explode('.', $keyRelated));

                    $builder->leftJoin($joinQuery, function ($join) use ($relatedTableAlias, $keyRelated, $currentTable, $relatedPrimaryKey, $relatedModel) {
                        $join->on($relatedTableAlias . '.' . $keyRelated, '=', $currentTable . '.' . $relatedPrimaryKey);

                        foreach ($relatedModel->relationWhereClauses as $relationClause) {
                            $join->where($relatedTableAlias . '.' . $relationClause['column'], $relationClause['operator'], $relationClause['value'], $relationClause['boolean']);
                        }

                        if (method_exists($relatedModel, 'getQualifiedDeletedAtColumn')) {
                            if ($this->softDelete == 'withTrashed') {
                                //do nothing
                            } elseif ($this->softDelete == 'withoutTrashed') {
                                $join->where($relatedTableAlias . '.deleted_at', '=', null);
                            } elseif ($this->softDelete == 'onlyTrashed') {
                                $join->where($relatedTableAlias . '.deleted_at', '<>', null);
                            }
                        }
                    });
                } else {
                    throw new \Exception('Only allowed relations for join queries: BelongsToJoin, HasOneJoin');
                }
            }

            $currentTable = $relatedTableAlias;
            $currentModel = $relatedModel;

            $this->joinedTables[$relation] = $relatedTableAlias;
        }

        if (! $this->selected  &&  count($relations) > 1) {
            $this->selected = true;
            $builder->select($baseTable . '.*')->groupBy($baseTable . '.' . $baseModel->primaryKey);
        }

        return $currentTable . '.' . $column;
    }

    private function validateJoinQuery($relatedModel)
    {
        foreach ($relatedModel->relationNotAllowedClauses as $method => $relationNotAllowedClause) {
            throw new EloquentJoinException($method . ' is not allowed on HasOneJoin and BelongsToJoin relations.');
        }

        foreach ($relatedModel->relationWhereClauses as $relationWhereClause) {
            if (empty($relationWhereClause['column'])  ||  ! is_string($relationWhereClause['column'])) {
                throw new EloquentJoinException("Only this where type ->where('column', 'operator', 'value') is allowed on HasOneJoin and BelongsToJoin relations.");
            }
        }
    }
}
