<?php

namespace Fico7489\Laravel\SortJoin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait SortJoinTrait
{
    public function scopeOrderByJoin(Builder $builder, $relations, $sortBy = 'asc')
    {
        $relations = explode('.', $relations);

        $sort = end($relations);
        $tableBase    = $this->getTable();
        $tableCurrent = $this->getTable();
        $modelCurrent = $this;

        foreach($relations as $relation){
            if($relation == $sort){
                continue;
            }

            $relatedRelation = $modelCurrent->$relation();
            $relatedModel = $relatedRelation->getRelated();
            $tableRelated = $relatedModel->getTable();
            $tableRelatedAlias = uniqid();

            $keyRelated = $relatedRelation->getForeignKey();
            if($relatedRelation instanceof BelongsTo){
                $builder->leftJoin($tableRelated . ' as ' . $tableRelatedAlias, $tableRelatedAlias . '.id', '=', $tableCurrent . '.' . $keyRelated);
            }elseif($relatedRelation instanceof HasOne){
                $keyRelated = last(explode('.', $keyRelated));
                $builder->leftJoin($tableRelated . ' as ' . $tableRelatedAlias, $tableRelatedAlias . '.' . $keyRelated, '=', $tableCurrent . '.id');
            }

            $tableCurrent = $tableRelatedAlias;
            $modelCurrent = $relatedModel;
        }

        return $builder
            ->select ($tableBase . '.*')
            ->orderBy($tableCurrent . '.' . $sort, $sortBy);
    }
}
