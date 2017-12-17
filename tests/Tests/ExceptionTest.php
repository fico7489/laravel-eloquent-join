<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Tests;

use Fico7489\Laravel\EloquentJoin\Exceptions\EloquentJoinException;
use Fico7489\Laravel\EloquentJoin\Tests\Models\Seller;
use Fico7489\Laravel\EloquentJoin\Tests\TestCase;

class ExceptionTest extends TestCase
{
    public function testInvalidCondition()
    {
        try {
            Seller::whereJoin('locationPrimaryInvalid.name', '=', 'test')->get();
        } catch (EloquentJoinException $e) {
            $this->assertEquals('orderBy is not allowed on HasOneJoin and BelongsToJoin relations.', $e->getMessage());
        }
    }

    public function testInvalidWhere()
    {
        try {
            Seller::whereJoin('locationPrimaryInvalid2.name', '=', 'test')->get();
        } catch (EloquentJoinException $e) {
            $this->assertEquals("Only this where type ->where('column', 'operator', 'value') is allowed on HasOneJoin and BelongsToJoin relations.", $e->getMessage());
        }
    }

    public function testInvalidOrWhere()
    {
        try {
            Seller::whereJoin('locationPrimaryOrSecondary.name', '=', 'test')->get();
        } catch (EloquentJoinException $e) {
            $this->assertEquals("orWhere is not allowed on HasOneJoin and BelongsToJoin relations. (for laravel <=5.2.*)", $e->getMessage());
        }
    }

    public function testInvalidRelation()
    {
        try {
            Seller::whereJoin('locations.address', '=', 'test')->get();
        } catch (EloquentJoinException $e) {
            $this->assertEquals("Only allowed relations for whereJoin, orWhereJoin and orderByJoin are BelongsToJoin, HasOneJoin", $e->getMessage());
        }
    }
}
