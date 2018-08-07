<?php

namespace Fico7489\Laravel\EloquentJoin;

use Fico7489\Laravel\EloquentJoin\Exceptions\InvalidRelationClause;
use Fico7489\Laravel\EloquentJoin\Exceptions\InvalidRelationScope;
use Illuminate\Database\Eloquent\Builder;
use Fico7489\Laravel\EloquentJoin\Relations\BelongsToJoin;
use Fico7489\Laravel\EloquentJoin\Relations\HasOneJoin;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EloquentJoinBuilder extends Builder
{
    //use table alias for join (real table name or uniqid())
    private $useTableAlias = false;

    //store if ->select(...) is already called on builder (we want only one select)
    private $selected = false;

    //store joined tables, we want join table only once (e.g. when you call orderByJoin more time)
    private $joinedTables = [];

    //store relation clauses for join
    public $relationClauses = [];

    public function whereJoin($column, $operator = null, $value = null, $boolean = 'and')
    {
        $column = $this->performJoin($column);

        return $this->where($column, $operator, $value, $boolean);
    }

    public function orWhereJoin($column, $operator = null, $value = null)
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
        $currentTable      = $baseTable;
        $currentPrimaryKey = $baseModel->getKeyName();

        $relationsAccumulated = [];

        foreach ($relations as $relation) {
            if ($relation == $column) {
                //last item in $relations argument is sort|where column
                break;
            }

            /** @var Relation $relatedRelation */
            $relatedRelation   = $currentModel->$relation();
            $relatedModel      = $relatedRelation->getRelated();
            $relatedTable      = $relatedModel->getTable();
            $relatedPrimaryKey = $relatedModel->getKeyName();
            $relatedTableAlias = $this->useTableAlias ? uniqid() : $relatedTable;

            $relationAccumulated[]      = $relatedTableAlias;

            $relationAccumulatedString = implode('.', $relationAccumulated);
            if (!in_array($relationAccumulatedString, $this->joinedTables)) {
                $relatedTableAlias = $this->useTableAlias ? uniqid() : $relatedTable;

                $joinQuery = $relatedTable.($this->useTableAlias ? ' as '.$relatedTableAlias : '');
                if ($relatedRelation instanceof BelongsToJoin) {
                    $keyRelated = $relatedRelation->getForeignKey();

                    $this->leftJoin($joinQuery, function ($join) use ($relatedTableAlias, $keyRelated, $currentTable, $relatedPrimaryKey, $relatedModel, $relatedRelation) {
                        $join->on($relatedTableAlias.'.'.$relatedPrimaryKey, '=', $currentTable.'.'.$keyRelated);

                        $this->leftJoinQuery($join, $relatedRelation, $relatedTableAlias);
                    });
                } elseif ($relatedRelation instanceof HasOneJoin) {
                    $keyRelated = $relatedRelation->getQualifiedForeignKeyName();
                    $keyRelated = last(explode('.', $keyRelated));

                    $this->leftJoin($joinQuery, function ($join) use ($relatedTableAlias, $keyRelated, $currentTable, $relatedPrimaryKey, $relatedModel, $currentPrimaryKey, $relatedRelation) {
                        $join->on($relatedTableAlias.'.'.$keyRelated, '=', $currentTable.'.'.$currentPrimaryKey);

                        $this->leftJoinQuery($join, $relatedRelation, $relatedTableAlias);
                    });
                } else {
                    throw new InvalidRelation('Package allows only following relations : BelongsToJoin and HasOneJoin');
                }
            }

            $currentModel      = $relatedModel;
            $currentTable      = $relatedTableAlias;
            $currentPrimaryKey = $relatedPrimaryKey;

            $this->joinedTables[] = implode('.', $relationAccumulated);
        }

        if (!$this->selected && count($relations) > 1) {
            $this->selected = true;
            $this->select($baseTable.'.*')->groupBy($baseTable.'.'.$baseModel->getKeyName());
        }

        return $currentTable.'.'.$column;
    }

    private function leftJoinQuery($join, $relation, $relatedTableAlias)
    {
        /** @var Builder $relationQuery */
        $relationBuilder = $relation->getQuery();

        foreach ($relationBuilder->relationClauses as $clause) {
            foreach ($clause as $method => $params) {
                $this->applyClauseOnRelation($join, $method, $params, $relatedTableAlias);
            }
        }

        foreach ($relationBuilder->getScopes() as $scope) {
            if ($scope instanceof SoftDeletingScope) {
                $this->applyClauseOnRelation($join, 'withoutTrashed', [], $relatedTableAlias);
            } else {
                throw new InvalidRelationScope('Package allows only SoftDeletingScope scope .');
            }
        }
    }

    private function applyClauseOnRelation($join, $method, $params, $relatedTableAlias)
    {
        if (in_array($method, ['where', 'orWhere'])) {
            if (is_array($params[0])) {
                foreach ($params[0] as $k => $param) {
                    $params[0][$relatedTableAlias.'.'.$k] = $param;
                    unset($params[0][$k]);
                }
            } else {
                $params[0] = $relatedTableAlias.'.'.$params[0];
            }

            call_user_func_array([$join, $method], $params);
        } elseif (in_array($method, ['withoutTrashed', 'onlyTrashed', 'withTrashed'])) {
            if ('withTrashed' == $method) {
                //do nothing
            } elseif ('withoutTrashed' == $method) {
                call_user_func_array([$join, 'where'], [$relatedTableAlias.'.deleted_at', '=', null]);
            } elseif ('onlyTrashed' == $method) {
                call_user_func_array([$join, 'where'], [$relatedTableAlias.'.deleted_at', '<>', null]);
            }
        } else {
            throw new InvalidRelationClause('Package allows only following clauses on relation : where, orWhere, withTrashed, onlyTrashed and withoutTrashed.');
        }
    }

    /**
     * @return array
     */
    public function getScopes()
    {
        return $this->scopes;
    }
}
