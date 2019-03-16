<?php

namespace Fico7489\Laravel\EloquentJoin;

use Fico7489\Laravel\EloquentJoin\Exceptions\InvalidAggregateMethod;
use Fico7489\Laravel\EloquentJoin\Exceptions\InvalidRelation;
use Fico7489\Laravel\EloquentJoin\Exceptions\InvalidRelationClause;
use Fico7489\Laravel\EloquentJoin\Exceptions\InvalidRelationGlobalScope;
use Fico7489\Laravel\EloquentJoin\Exceptions\InvalidRelationWhere;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Closure;

class EloquentJoinBuilder extends Builder
{
    //constants
    const AGGREGATE_SUM      = 'SUM';
    const AGGREGATE_AVG      = 'AVG';
    const AGGREGATE_MAX      = 'MAX';
    const AGGREGATE_MIN      = 'MIN';
    const AGGREGATE_COUNT    = 'COUNT';
    const DISABLED_COMPONENTS = [
        'aggregate',
        'columns',
        'groups',
        'havings',
        'orders',
        'limit',
        'offset',
        'unions',
        'lock',
    ];

    //use table alias for join (real table name or uniqid())
    private $useTableAlias = false;

    //appendRelationsCount
    private $appendRelationsCount = false;

    //leftJoin
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

    //query methods
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if ($column instanceof Closure) {
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

    public function whereInJoin($column, $values, $boolean = 'and', $not = false)
    {
        $query = $this->baseBuilder ? $this->baseBuilder : $this;
        $column = $query->performJoin($column);

        return $this->whereIn($column, $values, $boolean, $not);
    }

    public function whereNotInJoin($column, $values, $boolean = 'and')
    {
        $query = $this->baseBuilder ? $this->baseBuilder : $this;
        $column = $query->performJoin($column);

        return $this->whereNotIn($column, $values, $boolean);
    }

    public function orWhereInJoin($column, $values)
    {
        $query = $this->baseBuilder ? $this->baseBuilder : $this;
        $column = $query->performJoin($column);

        return $this->orWhereIn($column, $values);
    }

    public function orWhereNotInJoin($column, $values)
    {
        $query = $this->baseBuilder ? $this->baseBuilder : $this;
        $column = $query->performJoin($column);

        return $this->orWhereNotIn($column, $values);
    }

    public function orderByJoin($column, $direction = 'asc', $aggregateMethod = null)
    {
        $dotPos = strrpos($column, '.');

        $query = $this->baseBuilder ? $this->baseBuilder : $this;
        $column = $query->performJoin($column);
        if (false !== $dotPos) {
            //order by related table field
            $aggregateMethod = $aggregateMethod ? $aggregateMethod : $this->aggregateMethod;
            $this->checkAggregateMethod($aggregateMethod);

            $sortsCount = count($this->query->orders ?? []);
            $sortAlias = 'sort'.(0 == $sortsCount ? '' : ($sortsCount + 1));

            $query->selectRaw($aggregateMethod.'('.$column.') as '.$sortAlias);

            return $this->orderByRaw($sortAlias.' '.$direction);
        }

        //order by base table field

        return $this->orderBy($column, $direction);
    }

    public function joinRelations($relations, $leftJoin = null)
    {
        $leftJoin = null !== $leftJoin ? $leftJoin : $this->leftJoin;

        $query = $this->baseBuilder ? $this->baseBuilder : $this;
        $column = $query->performJoin($relations.'.FAKE_FIELD', $leftJoin);

        return $this;
    }

    //helpers methods
    private function performJoin($relations, $leftJoin = null)
    {
        //detect join method
        $leftJoin   = null !== $leftJoin ? $leftJoin : $this->leftJoin;
        $joinMethod = $leftJoin ? 'leftJoin' : 'join';

        //detect current model data
        $relations = explode('.', $relations);
        $column    = end($relations);
        $baseModel = $this->getModel();
        $baseTable = $baseModel->getTable();
        $basePrimaryKey = $baseModel->getKeyName();

        $currentModel      = $baseModel;
        $currentTableAlias = $baseTable;

        $relationsAccumulated = [];

        foreach ($relations as $i => $relation) {
            if ($relation == $column) {
                //last item in $relations argument is sort|where column
                break;
            }

            /** @var Relation $relatedRelation */
            $relatedRelation   = $currentModel->$relation();
            $relatedModel      = $relatedRelation->getRelated();
            $relatedPrimaryKey = $relatedModel->getKeyName();
            $relatedTable      = $relatedModel->getTable();

            $relatedTableAlias = $this->parseAlias($relatedModel, array_slice($relations, 0, $i + 1));

            $relationsAccumulated[]    = $relatedTableAlias;
            $relationAccumulatedString = implode('_', $relationsAccumulated);

            //relations count
            if ($this->appendRelationsCount) {
                $this->selectRaw('COUNT('.$relatedTableAlias.'.'.$relatedPrimaryKey.') as '.$relationAccumulatedString.'_count');
            }

            if (!in_array($relationAccumulatedString, $this->joinedTables)) {
                $this->joinRelation($relatedRelation, $currentTableAlias, $relatedTableAlias, $joinMethod);
            }

            $currentModel      = $relatedModel;
            $currentTableAlias = $relatedTableAlias;

            $this->joinedTables[] = implode('_', $relationsAccumulated);
        }

        if (!$this->selected && count($relations) > 1) {
            $this->selected = true;
            $this->selectRaw($baseTable.'.*');
            $this->groupBy($baseTable.'.'.$basePrimaryKey);
        }

        return $currentTableAlias.'.'.$column;
    }

    public function getKeyFromRelation(Relation $relation, string $keyName)
    {
        $getQualifiedKeyMethod = "getQualified".ucfirst($keyName)."Name";

        if (method_exists($relation, $getQualifiedKeyMethod)) {
            return last(explode(".", $relation->$getQualifiedKeyMethod()));
        }

        $getKeyMethod = "get".ucfirst($keyName);

        if (method_exists($relation, $getKeyMethod)) {
            return $relation->$getKeyMethod();
        }

        // relatedKey is protected before 5.7 in BelongsToMany

        $reflection = new \ReflectionClass($relation);
        $property = $reflection->getProperty($keyName);
        $property->setAccessible(true);

        return $property->getValue($relation);
    }

    public function joinRelation(Relation $relation, string $currentTableAlias, string $relatedTableAlias, string $joinMethod)
    {
        $relatedModel = $relation->getRelated();
        $relatedTable = $relatedModel->getTable();
        $joinQuery = $relatedTable.($relatedTableAlias !== $relatedTable ? ' as '.$relatedTableAlias : '');

        if ($relation instanceof MorphTo) {
            return;
        }

        if ($relation instanceof BelongsToMany) {

            $joinPivotQuery = $relation->getTable();
            $pivotTableAlias = $relation->getTable();

            $this->$joinMethod(
                $joinPivotQuery,
                $this->parseAliasableKey($pivotTableAlias, $this->getKeyFromRelation($relation, 'relatedPivotKey')), 
                '=', 
                $this->parseAliasableKey($currentTableAlias, $this->getKeyFromRelation($relation, 'parentKey'))
            );

            $relatedKey = $this->getKeyFromRelation($relation, 'relatedKey');

            $currentTableAlias = $pivotTableAlias;
            $currentKey = $this->getKeyFromRelation($relation, 'foreignPivotKey');
        }

        if ($relation instanceof BelongsTo) {
            $relatedKey = $this->getKeyFromRelation($relation, 'ownerKey');
            $currentKey = $this->getKeyFromRelation($relation, 'foreignKey');
        } elseif ($relation instanceof HasOneOrMany) {
            $currentKey = $this->getKeyFromRelation($relation, 'parentKey');
            $relatedKey = $this->getKeyFromRelation($relation, 'foreignKey');
        }

        if (!isset($relatedKey)) {
            throw new InvalidRelation();
        }

        $this->$joinMethod($joinQuery, function ($join) use ($relation, $relatedTableAlias, $relatedKey, $currentTableAlias, $currentKey) {
            $join->on($this->parseAliasableKey($relatedTableAlias, $relatedKey), '=', $this->parseAliasableKey($currentTableAlias, $currentKey));

            $this->joinQuery($join, $relation, $relatedTableAlias);
        });
    }

    protected function parseAlias(Model $relatedModel, array $relations): string
    {
        return $this->useTableAlias ? uniqid() : $relatedModel->getTable();
    }

    protected function parseAliasableKey(string $alias, string $key)
    {
        return $alias.'.'.$key;
    }

    protected function skipClausesByClassRelation(Relation $relation)
    {
        if ($relation instanceof BelongsTo) {
            return 1;
        }
        
        if ($relation instanceof HasOneOrMany) {
            return 2;
        }

        if ($relation instanceof BelongsToMany) {
            return 3;
        }
    }

    private function joinQuery($join, $relation, $relatedTableAlias)
    {
        /** @var Builder $relationQuery */
        $relationBuilder = $relation->getQuery();

        foreach (static::DISABLED_COMPONENTS as $component) {
            if (!empty($relationBuilder->getQuery()->$component)) {
                throw new InvalidRelationClause();
            }
        }

        $wheres = array_slice($relationBuilder->getQuery()->wheres, $this->skipClausesByClassRelation($relation));

        foreach ($wheres as $clause) {
            $method = 'Basic' === $clause['type'] ? 'where' : 'where'.$clause['type'];
            unset($clause['type']);

            if (!isset($clause['column'])) {
                throw new InvalidRelationWhere();
            }

            // Remove first alias table name
            $partsColumn = explode('.', $clause['column']);

            if (count($partsColumn) > 1) {
                $clause['column'] = implode('.', array_slice($partsColumn, 1));
            }

            $clause['column'] = $this->parseAliasableKey($relatedTableAlias, $clause['column']);

            $join->$method(...array_values($clause));
        }

        //apply global SoftDeletingScope
        foreach ($relationBuilder->scopes as $scope) {
            if ($scope instanceof SoftDeletingScope) {
                $this->applyScopeOnRelation($join, 'withoutTrashed', [], $relatedTableAlias);
            } else {
                throw new InvalidRelationGlobalScope();
            }
        }
    }

    private function applyScopeOnRelation(JoinClause $join, string $method, array $params, string $relatedTableAlias)
    {
        if ('withoutTrashed' == $method) {
            call_user_func_array([$join, 'where'], [$this->parseAliasableKey($relatedTableAlias, 'deleted_at'), '=', null]);
        } elseif ('onlyTrashed' == $method) {
            call_user_func_array([$join, 'where'], [$this->parseAliasableKey($relatedTableAlias, 'deleted_at'), '<>', null]);
        }
    }

    private function checkAggregateMethod($aggregateMethod)
    {
        if (!in_array($aggregateMethod, [
            self::AGGREGATE_SUM,
            self::AGGREGATE_AVG,
            self::AGGREGATE_MAX,
            self::AGGREGATE_MIN,
            self::AGGREGATE_COUNT,
        ])) {
            throw new InvalidAggregateMethod();
        }
    }

    //getters and setters
    public function isUseTableAlias(): bool
    {
        return $this->useTableAlias;
    }

    public function setUseTableAlias(bool $useTableAlias)
    {
        $this->useTableAlias = $useTableAlias;

        return $this;
    }

    public function isLeftJoin(): bool
    {
        return $this->leftJoin;
    }

    public function setLeftJoin(bool $leftJoin)
    {
        $this->leftJoin = $leftJoin;

        return $this;
    }

    public function isAppendRelationsCount(): bool
    {
        return $this->appendRelationsCount;
    }

    public function setAppendRelationsCount(bool $appendRelationsCount)
    {
        $this->appendRelationsCount = $appendRelationsCount;

        return $this;
    }

    public function getAggregateMethod(): string
    {
        return $this->aggregateMethod;
    }

    public function setAggregateMethod(string $aggregateMethod)
    {
        $this->checkAggregateMethod($aggregateMethod);
        $this->aggregateMethod = $aggregateMethod;

        return $this;
    }
}
