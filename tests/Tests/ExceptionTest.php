<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Tests;

use Fico7489\Laravel\EloquentJoin\Exceptions\InvalidRelation;
use Fico7489\Laravel\EloquentJoin\Exceptions\InvalidRelationClause;
use Fico7489\Laravel\EloquentJoin\Exceptions\InvalidRelationGlobalScope;
use Fico7489\Laravel\EloquentJoin\Exceptions\InvalidRelationWhere;
use Fico7489\Laravel\EloquentJoin\Tests\Models\Seller;
use Fico7489\Laravel\EloquentJoin\Tests\TestCase;

class ExceptionTest extends TestCase
{
    public function testInvalidRelation()
    {
        try {
            Seller::whereJoin('locations.address', '=', 'test')->get();
        } catch (InvalidRelation $e) {
            $this->assertEquals('Package allows only following relations : BelongsToJoin and HasOneJoin.', $e->getMessage());

            return;
        }

        $this->assertTrue(false);
    }

    public function testInvalidRelationWhere()
    {
        try {
            Seller::whereJoin('locationPrimaryInvalid2.name', '=', 'test')->get();
        } catch (InvalidRelationWhere $e) {
            $this->assertContains('Package allows only following where(orWhere) clauses type on relation : ->where($column, $operator, $value) and ->where([$column => $value]).', $e->getMessage());

            return;
        }

        $this->assertTrue(false);
    }

    public function testInvalidRelationClause()
    {
        try {
            Seller::whereJoin('locationPrimaryInvalid.name', '=', 'test')->get();
        } catch (InvalidRelationClause $e) {
            $this->assertEquals('Package allows only following clauses on relation : where, orWhere, withTrashed, onlyTrashed and withoutTrashed.', $e->getMessage());

            return;
        }

        $this->assertTrue(false);
    }

    public function testInvalidRelationGlobalScope()
    {
        try {
            Seller::whereJoin('locationPrimaryInvalid3.id', '=', 'test')->get();
        } catch (InvalidRelationGlobalScope $e) {
            $this->assertEquals('Package allows only SoftDeletingScope global scope.', $e->getMessage());

            return;
        }

        $this->assertTrue(false);
    }
}
