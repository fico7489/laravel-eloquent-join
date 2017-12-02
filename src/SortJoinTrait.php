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
        $tableBase    = $this->getTable();
        $tableCurrent = $this->getTable();
        $modelCurrent = $this;

        foreach($relations as $relation){
            if($relation == $sort){
                //last item in $relations argument is sort fiels
                continue;
            }

            $relatedRelation = $modelCurrent->$relation();
            $relatedModel = $relatedRelation->getRelated();
            $tableRelated = $relatedModel->getTable();

            if(array_key_exists($relation, $this->joinedTables)){
                $tableRelatedAlias = $this->joinedTables[$relation];
            }else{
                $tableRelatedAlias = uniqid();

                $keyRelated = $relatedRelation->getForeignKey();
                if($relatedRelation instanceof BelongsTo){
                    $builder->leftJoin($tableRelated . ' as ' . $tableRelatedAlias, $tableRelatedAlias . '.id', '=', $tableCurrent . '.' . $keyRelated);
                }elseif($relatedRelation instanceof HasOne){
                    $keyRelated = last(explode('.', $keyRelated));
                    $builder->leftJoin($tableRelated . ' as ' . $tableRelatedAlias, $tableRelatedAlias . '.' . $keyRelated, '=', $tableCurrent . '.id');
                }
            }

            $tableCurrent = $tableRelatedAlias;
            $modelCurrent = $relatedModel;

            $this->joinedTables[$relation] = $tableRelatedAlias;
        }

        $builder->orderBy($tableCurrent . '.' . $sort, $sortBy);

        if($this->selected){
            return $builder;
        }else{
            $this->selected = true;

            return $builder->select ($tableBase . '.*')->groupBy ($tableBase . '.id');
        }
    }
}
