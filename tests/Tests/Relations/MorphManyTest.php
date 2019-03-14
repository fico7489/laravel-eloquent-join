<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Tests\Relations;

use Fico7489\Laravel\EloquentJoin\Tests\Models\Location;
use Fico7489\Laravel\EloquentJoin\Tests\TestCase;

class MorphManyTest extends TestCase
{
    public function testMorphMany()
    {
        $sql = Location::joinRelations('tags')->toSql();

        $queryTest = 'select locations.* from `locations` left join `tags` on `tags`.`taggable_id` = `locations`.`id` and `tags`.`taggable_type` = ? where `locations`.`deleted_at` is null group by `locations`.`id`';

        $this->assertEquals($queryTest, $sql);
    }
}
