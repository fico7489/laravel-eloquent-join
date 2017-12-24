<?php

namespace Fico7489\Laravel\EloquentJoin\Traits;

use Fico7489\Laravel\EloquentJoin\Relations\BelongsToJoin;
use Fico7489\Laravel\EloquentJoin\Exceptions\EloquentJoinException;
use Fico7489\Laravel\EloquentJoin\Relations\HasOneJoin;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait EloquentJoinTrait
{
    use ExtendRelationsTrait;

    //use table alias for join (real table name or uniqid())
    private $useTableAlias = false;

    //store if ->select(...) is already called on builder (we want only one select)
    private $selected = false;

    //store joined tables, we want join table only once (e.g. when you call orderByJoin more time)
    private $joinedTables = [];


    //store not allowed clauses on join relations for throw exception (e.g. whereHas, orderBy etc.)
    public $relationNotAllowedClauses = [];

    //store where clauses which we will use for join
    public $relationWhereClauses = [];

    //store soft delete clauses (withoutTrashed|onlyTrashed|WithTrashed)
    public $softDelete = 'withoutTrashed';


    //set invalid clauses on join relations
    public function scopeSetInvalidJoin(Builder $builder, $method, $parameters = [])
    {
        $this->relationNotAllowedClauses[$method] = $method;
    }

    //set where clause for join relations
    public function scopeSetWhereForJoin(Builder $builder, $column, $operator = null, $value = null, $boolean = 'and')
    {
        $this->relationWhereClauses[] = ['column' => $column, 'operator' => $operator, 'value' => $value, 'boolean' => $boolean];
    }

    //set orWhere clause for join relations
    public function scopeSetOrWhereForJoin(Builder $builder, $column, $operator = null, $value)
    {
        $method = 'orWhere';
        $this->relationNotAllowedClauses[$method] = $method;
    }

    //set soft delete clauses for join relations
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
            $relatedPrimaryKey = $currentModel->getKeyName();
            $relatedTable = $relatedModel->getTable();

            $this->validateJoinQuery($relatedModel);

            if (array_key_exists($relation, $this->joinedTables)) {
                $relatedTableAlias = $this->joinedTables[$relation];
            } else {
                $relatedTableAlias = $this->useTableAlias ? uniqid() : $relatedTable;

                $joinQuery = $relatedTable . ($this->useTableAlias ? ' as ' . $relatedTableAlias : '');
                if ($relatedRelation instanceof BelongsToJoin) {
                    $keyRelated = $relatedRelation->getForeignKey();

                    $builder->leftJoin($joinQuery, function ($join) use ($relatedTableAlias, $keyRelated, $currentTable, $relatedPrimaryKey, $relatedModel) {
                        $join->on($relatedTableAlias . '.' . $relatedPrimaryKey, '=', $currentTable . '.' . $keyRelated);

                        $this->leftJoinQuery($join, $relatedModel, $relatedTableAlias);
                    });
                } elseif ($relatedRelation instanceof HasOneJoin) {
                    $keyRelated = $relatedRelation->getForeignKey();
                    $keyRelated = last(explode('.', $keyRelated));

                    $builder->leftJoin($joinQuery, function ($join) use ($relatedTableAlias, $keyRelated, $currentTable, $relatedPrimaryKey, $relatedModel) {
                        $join->on($relatedTableAlias . '.' . $keyRelated, '=', $currentTable . '.' . $relatedPrimaryKey);

                        $this->leftJoinQuery($join, $relatedModel, $relatedTableAlias);
                    });
                } else {
                    throw new EloquentJoinException('Only allowed relations for whereJoin, orWhereJoin and orderByJoin are BelongsToJoin, HasOneJoin');
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

    private function leftJoinQuery($join, $relatedModel, $relatedTableAlias)
    {
        foreach ($relatedModel->relationWhereClauses as $relationClause) {
            $join->where($relatedTableAlias . '.' . $relationClause['column'], $relationClause['operator'], $relationClause['value'], $relationClause['boolean']);
        }

        if (method_exists($relatedModel, 'getQualifiedDeletedAtColumn')) {
            if ($relatedModel->softDelete == 'withTrashed') {
                //do nothing
            } elseif ($relatedModel->softDelete == 'withoutTrashed') {
                $join->whereNull($relatedTableAlias . '.deleted_at');
            } elseif ($relatedModel->softDelete == 'onlyTrashed') {
                $join->whereNotNull($relatedTableAlias . '.deleted_at');
            }
        }
    }

    private function validateJoinQuery($relatedModel)
    {
        foreach ($relatedModel->relationNotAllowedClauses as $method => $relationNotAllowedClause) {
            $text = $method . ' is not allowed on HasOneJoin and BelongsToJoin relations.';
            $text = ($method == 'orWhere') ? $text . ' (for laravel <=5.2.*)' : $text;
            throw new EloquentJoinException($text);
        }

        foreach ($relatedModel->relationWhereClauses as $relationWhereClause) {
            if (empty($relationWhereClause['column'])  ||  ! is_string($relationWhereClause['column'])) {
                throw new EloquentJoinException("Only this where type ->where('column', 'operator', 'value') is allowed on HasOneJoin and BelongsToJoin relations.");
            }
        }
    }
}
