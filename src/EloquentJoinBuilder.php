<?php

namespace Fico7489\Laravel\EloquentJoin;

use Illuminate\Database\Eloquent\Builder;
use Fico7489\Laravel\EloquentJoin\Relations\BelongsToJoin;
use Fico7489\Laravel\EloquentJoin\Exceptions\EloquentJoinException;
use Fico7489\Laravel\EloquentJoin\Relations\HasOneJoin;

class EloquentJoinBuilder extends Builder
{
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

    public function whereJoin($column, $operator = null, $value = null, $boolean = 'and')
    {
        $column = $this->performJoin($column);

        return $this->where($column, $operator, $value, $boolean);
    }

    public function orWhereJoin($column, $operator = null, $value)
    {
        $column = $this->performJoin($column);

        return $this->orWhere($column, $operator, $value);
    }

    public function orderByJoin($column, $sortBy = 'asc')
    {
        $column = $this->performJoin($column);

        return $this->orderBy($column, $sortBy);
    }

    private function performJoin($relations)
    {
        $relations = explode('.', $relations);

        $column    = end($relations);
        $baseModel = $this->getModel();
        $baseTable = $baseModel->getTable();

        $currentModel      = $baseModel;
        $currentPrimaryKey = $baseModel->getKeyName();
        $currentTable      = $baseModel->getTable();

        foreach ($relations as $relation) {
            if ($relation == $column) {
                //last item in $relations argument is sort|where column
                break;
            }

            $relatedRelation   = $currentModel->$relation();
            $relatedModel      = $relatedRelation->getRelated();
            $relatedPrimaryKey = $relatedModel->getKeyName();
            $relatedTable      = $relatedModel->getTable();

            $this->validateJoinQuery();

            if (array_key_exists($relation, $this->joinedTables)) {
                $relatedTableAlias = $this->joinedTables[$relation];
            } else {
                $relatedTableAlias = $this->useTableAlias ? uniqid() : $relatedTable;

                $joinQuery = $relatedTable . ($this->useTableAlias ? ' as ' . $relatedTableAlias : '');
                if ($relatedRelation instanceof BelongsToJoin) {
                    $keyRelated = $relatedRelation->getForeignKey();

                    $this->leftJoin($joinQuery, function ($join) use ($relatedTableAlias, $keyRelated, $currentTable, $relatedPrimaryKey, $relatedModel) {
                        $join->on($relatedTableAlias . '.' . $relatedPrimaryKey, '=', $currentTable . '.' . $keyRelated);

                        $this->leftJoinQuery($join, $relatedModel, $relatedTableAlias);
                    });
                } elseif ($relatedRelation instanceof HasOneJoin) {
                    $keyRelated = $relatedRelation->getQualifiedForeignKeyName();
                    $keyRelated = last(explode('.', $keyRelated));

                    $this->leftJoin($joinQuery, function ($join) use ($relatedTableAlias, $keyRelated, $currentTable, $relatedPrimaryKey, $relatedModel, $currentPrimaryKey) {
                        $join->on($relatedTableAlias . '.' . $keyRelated, '=', $currentTable . '.' . $currentPrimaryKey);

                        $this->leftJoinQuery($join, $relatedModel, $relatedTableAlias);
                    });
                } else {
                    throw new EloquentJoinException('Only allowed relations for whereJoin, orWhereJoin and orderByJoin are BelongsToJoin, HasOneJoin');
                }
            }

            $currentModel      = $relatedModel;
            $currentPrimaryKey = $relatedPrimaryKey;
            $currentTable      = $relatedTableAlias;

            $this->joinedTables[$relation] = $relatedTableAlias;
        }

        if (! $this->selected  &&  count($relations) > 1) {
            $this->selected = true;
            $this->select($baseTable . '.*')
                //->groupBy($baseTable . '.' . $baseModel->primaryKey)
            ;
        }

        return $currentTable . '.' . $column;
    }

    private function leftJoinQuery($join, $relatedModel, $relatedTableAlias)
    {
        $relatedModel = $this;

        foreach ($relatedModel->relationWhereClauses as $relationClause) {
            $join->where($relatedTableAlias . '.' . $relationClause['column'], $relationClause['operator'], $relationClause['value'], $relationClause['boolean']);
        }
    }

    private function validateJoinQuery()
    {
        foreach ($this->relationNotAllowedClauses as $method => $relationNotAllowedClause) {
            throw new EloquentJoinException($method . ' is not allowed on HasOneJoin and BelongsToJoin relations.');
        }

        foreach ($this->relationWhereClauses as $relationWhereClause) {
            if (empty($relationWhereClause['column'])  ||  ! is_string($relationWhereClause['column'])) {
                throw new EloquentJoinException("Only this where types are allowed on HasOneJoin and BelongsToJoin relations : 
                    ->where('column', 'operator', 'value') 
                    ->where('column', 'value') 
                    ->where(['column' => 'value']) 
                ");
            }
        }
    }
}
