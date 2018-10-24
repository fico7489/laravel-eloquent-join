<?php

namespace Fico7489\Laravel\EloquentJoin;

use Fico7489\Laravel\EloquentJoin\Exceptions\InvalidRelation;
use Fico7489\Laravel\EloquentJoin\Exceptions\InvalidRelationClause;
use Fico7489\Laravel\EloquentJoin\Exceptions\InvalidRelationGlobalScope;
use Fico7489\Laravel\EloquentJoin\Exceptions\InvalidRelationWhere;
use Illuminate\Database\Eloquent\Builder;
use Fico7489\Laravel\EloquentJoin\Relations\BelongsToJoin;
use Fico7489\Laravel\EloquentJoin\Relations\HasOneJoin;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EloquentJoinBuilder extends Builder
{
    //use table alias for join (real table name or uniqid())
    private $useTableAlias = false;

    //store if ->select(...) is already called on builder (we want only one groupBy())
    private $selected = false;

    //store joined tables, we want join table only once (e.g. when you call orderByJoin more time)
    private $joinedTables = [];

    //store clauses on relation for join
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

    public function orderByJoin($column, $direction = 'asc', $leftJoin = true)
    {
        $column = $this->performJoin($column, $leftJoin);

        return $this->orderBy($column, $direction);
    }

    private function performJoin($relations, $leftJoin = true)
    {
        $relations = explode('.', $relations);

        $column    = end($relations);
        $baseModel = $this->getModel();
        $baseTable = $baseModel->getTable();

        $currentModel      = $baseModel;
        $currentTableAlias = $baseTable;
        $currentPrimaryKey = $baseModel->getKeyName();

        $relationsAccumulated = [];

        foreach ($relations ?? [] as $relation) {
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

            $relationsAccumulated[]    = $relatedTableAlias;
            $relationAccumulatedString = implode('.', $relationsAccumulated);

            $joinMethod = $leftJoin ? 'leftJoin' : 'join';
            if (!in_array($relationAccumulatedString, $this->joinedTables)) {
                $joinQuery = $relatedTable.($this->useTableAlias ? ' as '.$relatedTableAlias : '');
                if ($relatedRelation instanceof BelongsToJoin) {
                    $relatedKey = $relatedRelation->getForeignKey();

                    $this->$joinMethod($joinQuery, function ($join) use ($relatedRelation, $relatedTableAlias, $relatedPrimaryKey, $currentTableAlias, $relatedKey) {
                        $join->on($relatedTableAlias.'.'.$relatedPrimaryKey, '=', $currentTableAlias.'.'.$relatedKey);

                        $this->joinQuery($join, $relatedRelation, $relatedTableAlias);
                    });
                } elseif ($relatedRelation instanceof HasOneJoin) {
                    $relatedKey = $relatedRelation->getQualifiedForeignKeyName();
                    $relatedKey = last(explode('.', $relatedKey));

                    $this->$joinMethod($joinQuery, function ($join) use ($relatedRelation, $relatedTableAlias, $relatedPrimaryKey, $currentTableAlias, $relatedKey, $currentPrimaryKey) {
                        $join->on($relatedTableAlias.'.'.$relatedKey, '=', $currentTableAlias.'.'.$currentPrimaryKey);

                        $this->joinQuery($join, $relatedRelation, $relatedTableAlias);

                        $join->whereRaw(
                            $relatedTableAlias.'.'.$relatedPrimaryKey.' =  (
                                SELECT '.$relatedPrimaryKey.'
                                    FROM '.$relatedTableAlias.'
                                    WHERE '.$relatedTableAlias.'.'.$relatedKey.' = '.$currentTableAlias.'.'.$currentPrimaryKey.'
                                    LIMIT 1
                                )
                            ');
                    });
                } else {
                    throw new InvalidRelation();
                }
            }

            $currentModel      = $relatedModel;
            $currentTableAlias = $relatedTableAlias;
            $currentPrimaryKey = $relatedPrimaryKey;

            $this->joinedTables[] = implode('.', $relationsAccumulated);
        }

        if (!$this->selected && count($relations) > 1) {
            $this->selected = true;
            $this->select($baseTable.'.*');
        }

        return $currentTableAlias.'.'.$column;
    }

    private function joinQuery($join, $relation, $relatedTableAlias)
    {
        /** @var Builder $relationQuery */
        $relationBuilder = $relation->getQuery();

        //apply clauses on relation
        foreach ($relationBuilder->relationClauses ?? [] as $clause) {
            foreach ($clause as $method => $params) {
                $this->applyClauseOnRelation($join, $method, $params, $relatedTableAlias);
            }
        }

        //apply global SoftDeletingScope
        foreach ($relationBuilder->scopes ?? [] as $scope) {
            if ($scope instanceof SoftDeletingScope) {
                $this->applyClauseOnRelation($join, 'withoutTrashed', [], $relatedTableAlias);
            } else {
                throw new InvalidRelationGlobalScope();
            }
        }
    }

    private function applyClauseOnRelation($join, $method, $params, $relatedTableAlias)
    {
        if (in_array($method, ['where', 'orWhere'])) {
            try {
                if (is_array($params[0])) {
                    foreach ($params[0] as $k => $param) {
                        $params[0][$relatedTableAlias.'.'.$k] = $param;
                        unset($params[0][$k]);
                    }
                } else {
                    $params[0] = $relatedTableAlias.'.'.$params[0];
                }

                call_user_func_array([$join, $method], $params);
            } catch (\Exception $e) {
                throw new InvalidRelationWhere();
            }
        } elseif (in_array($method, ['withoutTrashed', 'onlyTrashed', 'withTrashed'])) {
            if ('withTrashed' == $method) {
                //do nothing
            } elseif ('withoutTrashed' == $method) {
                call_user_func_array([$join, 'where'], [$relatedTableAlias.'.deleted_at', '=', null]);
            } elseif ('onlyTrashed' == $method) {
                call_user_func_array([$join, 'where'], [$relatedTableAlias.'.deleted_at', '<>', null]);
            }
        } else {
            throw new InvalidRelationClause();
        }
    }
}
