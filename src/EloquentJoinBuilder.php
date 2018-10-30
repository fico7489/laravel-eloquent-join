<?php

namespace Fico7489\Laravel\EloquentJoin;

use Fico7489\Laravel\EloquentJoin\Exceptions\InvalidRelation;
use Fico7489\Laravel\EloquentJoin\Exceptions\InvalidRelationClause;
use Fico7489\Laravel\EloquentJoin\Exceptions\InvalidRelationGlobalScope;
use Fico7489\Laravel\EloquentJoin\Exceptions\InvalidRelationWhere;
use Illuminate\Database\Eloquent\Builder;
use Fico7489\Laravel\EloquentJoin\Relations\BelongsToJoin;
use Fico7489\Laravel\EloquentJoin\Relations\HasOneJoin;
use Fico7489\Laravel\EloquentJoin\Relations\HasManyJoin;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EloquentJoinBuilder extends Builder
{
    const AGGREGATE_SUM      = 'SUM';
    const AGGREGATE_AVG      = 'AVG';
    const AGGREGATE_MAX      = 'MAX';
    const AGGREGATE_MIN      = 'MIN ';
    const AGGREGATE_COUNT    = 'COUNT';

    //use table alias for join (real table name or uniqid())
    private $useTableAlias = false;

    //use table alias for join (real table name or uniqid())
    private $leftJoin = true;

    //aggregate method
    private $aggregateMethod = self::AGGREGATE_MAX;

    //base builder
    public $baseBuilder;

    //store if ->select(...) is already called on builder (we want only one groupBy())
    private $selected = false;

    //store joined tables, we want join table only once (e.g. when you call orderByJoin more time)
    private $joinedTables = [];

    //store clauses on relation for join
    public $relationClauses = [];

    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if ($column instanceof \Closure) {
            $query = $this->model->newModelQuery();
            $baseBuilderCurrent = $this->baseBuilder ? $this->baseBuilder : $this;
            $query->baseBuilder = $baseBuilderCurrent;

            $column($query);

            $this->query->addNestedWhereQuery($query->getQuery(), $boolean);
        } else {
            $this->query->where(...func_get_args());
        }

        return $this;
    }

    public function whereJoin($column, $operator, $value, $boolean = 'and')
    {
        $query = $this->baseBuilder ? $this->baseBuilder : $this;
        $column = $query->performJoin($column);

        return $this->where($column, $operator, $value, $boolean);
    }

    public function orWhereJoin($column, $operator, $value)
    {
        $query = $this->baseBuilder ? $this->baseBuilder : $this;
        $column = $query->performJoin($column);

        return $this->orWhere($column, $operator, $value);
    }

    public function orderByJoin($column, $direction = 'asc', $aggregate = null)
    {
        $sortByRelated = false !== strpos($column, '.');

        $query = $this->baseBuilder ? $this->baseBuilder : $this;
        $column = $query->performJoin($column);
        if ($sortByRelated) {
            $aggregate = $aggregate ? $aggregate : $this->aggregateMethod;
            $query->selectRaw($aggregate.'('.$column.') as sort');
        }

        return $this->orderBy($column, $direction);
    }

    public function relationJoin($column)
    {
        $query = $this->baseBuilder ? $this->baseBuilder : $this;
        $column = $query->performJoin($column.'.FAKE_FIELD');

        return $this;
    }

    private function performJoin($relations)
    {
        $relations = explode('.', $relations);

        $column    = end($relations);
        $baseModel = $this->getModel();
        $baseTable = $baseModel->getTable();

        $currentModel      = $baseModel;
        $currentTableAlias = $baseTable;
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

            $relationsAccumulated[]    = $relatedTableAlias;
            $relationAccumulatedString = implode('.', $relationsAccumulated);

            $joinMethod = $this->leftJoin ? 'leftJoin' : 'join';
            if (!in_array($relationAccumulatedString, $this->joinedTables)) {
                $joinQuery = $relatedTable.($this->useTableAlias ? ' as '.$relatedTableAlias : '');
                if ($relatedRelation instanceof BelongsToJoin) {
                    $relatedKey = $relatedRelation->getForeignKey();

                    $this->$joinMethod($joinQuery, function ($join) use ($relatedRelation, $relatedTableAlias, $relatedPrimaryKey, $currentTableAlias, $relatedKey) {
                        $join->on($relatedTableAlias.'.'.$relatedPrimaryKey, '=', $currentTableAlias.'.'.$relatedKey);

                        $this->joinQuery($join, $relatedRelation, $relatedTableAlias);
                    });
                } elseif ($relatedRelation instanceof HasOneJoin  ||  $relatedRelation instanceof HasManyJoin) {
                    $relatedKey = $relatedRelation->getQualifiedForeignKeyName();
                    $relatedKey = last(explode('.', $relatedKey));

                    $this->$joinMethod($joinQuery, function ($join) use ($relatedRelation, $relatedTableAlias, $relatedPrimaryKey, $currentTableAlias, $relatedKey, $currentPrimaryKey) {
                        $join->on($relatedTableAlias.'.'.$relatedKey, '=', $currentTableAlias.'.'.$currentPrimaryKey);

                        $this->joinQuery($join, $relatedRelation, $relatedTableAlias);
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
            $this->selectRaw('"'.$baseTable.'".*');
            $this->groupBy($baseTable.'.id');
        }

        return $currentTableAlias.'.'.$column;
    }

    private function joinQuery($join, $relation, $relatedTableAlias)
    {
        /** @var Builder $relationQuery */
        $relationBuilder = $relation->getQuery();

        //apply clauses on relation
        if (isset($relationBuilder->relationClauses)) {
            foreach ($relationBuilder->relationClauses as $clause) {
                foreach ($clause as $method => $params) {
                    $this->applyClauseOnRelation($join, $method, $params, $relatedTableAlias);
                }
            }
        }

        //apply global SoftDeletingScope
        foreach ($relationBuilder->scopes as $scope) {
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

    /**
     * @return bool
     */
    public function isUseTableAlias()
    {
        return $this->useTableAlias;
    }

    /**
     * @param bool $useTableAlias
     */
    public function setUseTableAlias($useTableAlias)
    {
        $this->useTableAlias = $useTableAlias;

        return $this;
    }

    /**
     * @return bool
     */
    public function isLeftJoin()
    {
        return $this->leftJoin;
    }

    /**
     * @param bool $leftJoin
     */
    public function setLeftJoin($leftJoin)
    {
        $this->leftJoin = $leftJoin;

        return $this;
    }
}
