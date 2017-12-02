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
        $relations = explode('.', $relations);

        $sort = end($relations);
        $baseTable = $this->getTable();
        $baseModel = $this;

        $currentTable = $this->getTable();
        $currentModel = $this;

        foreach($relations as $relation){
            if($relation == $sort){
                //last item in $relations argument is sort fiels
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

        $builder->orderBy($currentTable . '.' . $sort, $sortBy);

        if($this->selected){
            return $builder;
        }else{
            $this->selected = true;

            return $builder->select ($baseTable . '.*')->groupBy ($baseTable . '.' . $baseModel->primaryKey);
        }
    }
}
