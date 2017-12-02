<?php

namespace Fico7489\Laravel\SortJoin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait SortJoinTrait
{
    private $selected = false;
    private $joinedTables = [];

    public function scopeOrderByJoin(Builder $builder, $relations, $sortBy = 'asc')
    {
        list($currentTable, $sort) = $this->performJoin($builder, $relations);

        $builder->orderBy($currentTable . '.' . $sort, $sortBy);
    }

    private function performJoin($builder, $relations){
        $relations = explode('.', $relations);

        $field = end($relations);
        $baseTable = $this->getTable();
        $baseModel = $this;

        $currentTable = $this->getTable();
        $currentModel = $this;

        foreach($relations as $relation){
            if($relation == $field){
                //last item in $relations argument is sort|where field
                continue;
            }

            $relatedRelation = $currentModel->$relation();
            $relatedModel = $relatedRelation->getRelated();
            $relatedPrimaryKey = $relatedModel->primaryKey;
            $relatedTable = $relatedModel->getTable();

            if(array_key_exists($relation, $this->joinedTables)){
                $relatedTableAlias = $this->joinedTables[$relation];
            }else{
                $relatedTableAlias = uniqid();

                $keyRelated = $relatedRelation->getForeignKey();
                if($relatedRelation instanceof BelongsTo){
                    $builder->leftJoin($relatedTable . ' as ' . $relatedTableAlias, $relatedTableAlias . '.' . $relatedPrimaryKey, '=', $currentTable . '.' . $keyRelated);
                }elseif($relatedRelation instanceof HasOne){
                    $keyRelated = last(explode('.', $keyRelated));
                    $builder->leftJoin($relatedTable . ' as ' . $relatedTableAlias, $relatedTableAlias . '.' . $keyRelated, '=', $currentTable . '.' . $relatedPrimaryKey);
                }
            }

            $currentTable = $relatedTableAlias;
            $currentModel = $relatedModel;

            $this->joinedTables[$relation] = $relatedTableAlias;
        }

        if( ! $this->selected){
            $this->selected = true;
            $builder->select ($baseTable . '.*')->groupBy ($baseTable . '.' . $baseModel->primaryKey);
        }

        return [$currentTable, $field];
    }
}
