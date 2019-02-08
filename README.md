# Laravel Eloquent Join

This package introduces the join magic for eloquent models and relations.

## Introduction

Eloquent is a powerful ORM but its join capabilities are very poor.

#### First Eloquent Problem (sorting)

With laravel you can't perform sorting of the relationship fields without manually joining related table which is very awkward. Let me give you a few reasons why. If you have a table with **posts** and related **categories** your code might look like this:

```
$posts = Post::select('posts.*')
    ->join('categories', 'categories.id', '=', 'posts.category_id')
    ->groupBy('posts.id')
    ->where('categories.deleted_at', '=', null)
    ->orderBy('categories.name');
    
if(request()->get('date')){
    $posts->where('posts.date', $date)
}

$posts = $posts->get();
```

1.The first problem is that you need to worry about select.
```
    ->select('posts.*')
```
Reason : without **select** id from the category can be selected and hydrated into the Post model.

2.The second problem is that you need to worry about **groupBy**.

    ->groupBy('posts.id');
    
Reason : if the relation is HasOne and there are more than one categories for the post, the query will return more rows for categories.

3.The third problem is that you need to change all other where clauses from : 
```
    ->where('date', $date)
```
to
```
    ->where('posts.date', $date)
```
Reason : a **post** and **category** can have "date" attribute and in that case without selecting an attribute with table "ambiguous column" error will be thrown.

4.The fourth problem is that you are using table names(not models) and this is also bad and awkward.
```
    ->where('posts.date', $date)
```
5.The fifth problem is that you need to worry about soft deletes for joined tables. If the **category** is using SoftDeletes trait you must add : 
```
    ->where('categories.deleted_at', '=', null)
```
This package will take care of all above problems for you. 
Unlike **sorting**, you can perform **filtering** on the relationship fields without joining related tables, but this package will give you the ability to do this easier.


#### Second Eloquent Problem (subqueries)

With laravel you can perform where on the relationship attribute but laravel will generate subqueries which are more slower than joins. 
With this package you will be available to perform where on the relationship with joins in an elegant way.


## Requirements

| Laravel Version | Package Tag | Supported | Development Branch
|-----------------|-------------|-----------| -----------|
| >= 5.5.0 | 4.* | yes | master
| < 5.5.0 | - | no | -

Package is also tested for SQLite, MySql and PostgreSql

## Installation & setup

1.Install package with composer
```
composer require fico7489/laravel-eloquent-join
```
With this statement, a composer will install highest available package version for your current laravel version.

2.Use Fico7489\Laravel\EloquentJoin\Traits\EloquentJoinTrait trait in your base model or only in particular models.

```
...
use Fico7489\Laravel\EloquentJoin\Traits\EloquentJoin;
use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    use EloquentJoin;
...
```

3.IMPORTANT

For **MySql** make sure that **strict** configuration is set to **false**

config/database.php

```
        'mysql' => [
			...
            'strict'    => false,
			...
```

and that's it, you are ready to go.

## Options

Options can be set in the model  : 

```
class Seller extends BaseModel
{
    protected $useTableAlias = false;
    protected $appendRelationsCount = false;
    protected $leftJoin = false;
    protected $aggregateMethod = 'MAX';
```

or on query : 

```
    Order::setUseTableAlias(true)->get();
    Order::setAppendRelationsCount(true)->get();
    Order::setLeftJoin(true)->get();
    Order::setAggregateMethod(true)->get();
```

#### **useTableAlias**

Should we use an alias for joined tables (default = false)

With **true** query will look like this : 
```
select "sellers".* from "sellers" 
    left join "locations" as "5b5c093d2e00f" 
	...
```

With **false** query will look like this : 
```
select "sellers".* 
	from "sellers" 
	left join "locations"                    
	...
```

Alias is a randomly generated string.

#### **appendRelationsCount**

Should we automatically append relation count field to results  (default = false)

With **true** query will look like this : 
```
select "sellers".*, count(locations.id) AS locations_count
	from "sellers" 
	left join "locations" as "5b5c093d2e00f" 
	...
```

Each **relation** is glued with an underscore and at the end **_count** prefix is added. For example for 

    ->joinRelations('seller.locations')
    
field would be __seller_locations_count__

#### **leftJoin**

Should we use **inner join** or **left join** (default = true)

```
select "sellers".* 
	from "sellers" 
	inner join "locations"                    
	...
```

vs

```
select "sellers".* 
	from "sellers" 
	left join "locations"                    
	...
```

#### **aggregateMethod**

Which aggregate method to use for ordering (default = 'MAX'). 

When join is performed on the joined table we must apply aggregate functions on the sorted field so we could perform group by clause and prevent duplication of results.

```
select "sellers".*, MAX("locations" ."number") AS sort
	from "sellers" 
	left join "locations" 
	group by "locations" ."id"
	order by sort
	...
```

Options are : **SUM**, **AVG**, **MAX**, **MIN**, **COUNT**

## Usage

### Currently available relations for join queries

* **BelongsTo**
* **HasOne**
* **HasMany**

### New clauses for eloquent builder on BelongsTo and HasOne relations : 

 **joinRelations($relations, $leftJoin = null)**

* ***$relations*** which relations to join
* ***$leftJoin*** use **left join** or **inner join**, default **left join**

**orderByJoin($column, $direction  = 'asc', $aggregateMethod = null)**

* ***$column*** and ***$direction***  arguments are the same as in default eloquent **orderBy()**
* ***$aggregateMethod*** argument defines which aggregate method to use ( **SUM**, **AVG**, **MAX**, **MIN**, **COUNT**), default **MAX**
    
**whereJoin($column, $operator, $value, $boolean = 'and')**

* arguments are the same as in default eloquent **where()**
    
**orWhereJoin($column, $operator, $value)**

* arguments are the same as in default eloquent **orWhere()**


**whereInJoin($column, $values, $boolean = 'and', $not = false)**

* arguments are the same as in default eloquent **whereIn()**

**whereNotInJoin($column, $operator, $value)**

* arguments are the same as in default eloquent **whereNotIn()**

**orWhereInJoin($column, $operator, $value)**

* arguments are the same as in default eloquent **orWhereIn()**

**orWhereNotInJoin($column, $operator, $value)**

* arguments are the same as in default eloquent **orWhereNotIn()**


### Allowed clauses on BelongsTo, HasOne and HasMany relations on which you can use join clauses on the query

* Relations that you want to use for join queries can only have these clauses : **where**, **orWhere**, **withTrashed**, **onlyTrashed**, **withoutTrashed**. 
* Clauses **where** and **orWhere** can only have these variations 
** **->where($column, $operator, $value)** 
** **->where([$column => $value])**
* Closures are not allowed.
* Other clauses like **whereHas**, **orderBy** etc. are not allowed.
* You can add not allowed clauses on relations and use them in the normal eloquent way, but in these cases, you can't use those relations for join queries.

Allowed relation:

```
public function locationPrimary()
{
    return $this->hasOne(Location::class)
        ->where('is_primary', '=', 1)
        ->orWhere('is_primary', '=', 1)
        ->withTrashed();
}
```
Not allowed relation: 

```
public function locationPrimary()
{
    return $this->hasOne(Location::class)
        ->where('is_primary', '=', 1)
        ->orWhere('is_primary', '=', 1)
        ->withTrashed()
        ->whereHas('state', function($query){return $query;}
        ->orderBy('name')
        ->where(function($query){
            return $query->where('is_primary', '=', 1);
        });
}
```

The reason why the second relation is not allowed is that this package should apply all those clauses on the join clause,  eloquent use all those clauses isolated with subqueries NOT on join clause and that is more simpler to do.

You might get a picture that there are too many rules and restriction, but it is really not like that. 
Don't worry, if you do create the query that is not allowed appropriate exception will be thrown and you will know what happened.

### Other 

* If the model uses the SoftDelete trait, where deleted_at != null will be automatically applied
* You can combine new clauses unlimited times
* If you combine clauses more times on same relation package will join related table only once

```
Seller::whereJoin('city.title', '=', 'test')
    ->orWhereJoin('city.title', '=', 'test2');
```

* You can call  new clauses inside closures

```
Seller::where(function ($query) {
    $query
        ->whereJoin('city.title', '=', 'test')
        ->orWhereJoin('city.title', '=', 'test2');
});
```

* You can combine join clauses e.g. whereJoin() with eloquent clauses e.g. orderBy()

```
Seller::whereJoin('title', '=', 'test')
    ->whereJoin('city.title', '=', 'test')
    ->orderByJoin('city.title')
    ->get();
```

## See action on real example

Database schema : 

![Database schema](https://raw.githubusercontent.com/fico7489/laravel-eloquent-join/master/readme/era.png)

Models : 

```
class Seller extends BaseModel
{
    public function locations()
    {
        return $this->hasMany(Location::class);
    }
    
    public function locationPrimary()
    {
        return $this->hasOne(Location::class)
            ->where('is_primary', '=', 1);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }
```
```
class Location extends BaseModel
{
    public function locationAddressPrimary()
    {
        return $this->hasOne(LocationAddress::class)
            ->where('is_primary', '=', 1);
    }
    
```
```
class City extends BaseModel
{
    public function state()
    {
        return $this->belongsTo(State::class);
    }
}
```

### Join

##### Join BelongsTo
```Seller::joinRelations('city')```

##### Join HasOne
```Seller::joinRelations('locationPrimary')```

##### Join HasMany
```Seller::joinRelations('locations')```

##### Join Mixed
```Seller::joinRelations('city.state')```

### Join (mix left join)

```Seller::joinRelations('city', true)->joinRelations('city.state', false)```

### Ordering

##### Order BelongsTo
```Seller::orderByJoin('city.title')```

##### Order HasOne
```Seller::orderByJoin('locationPrimary.address')```

##### Order HasMany
```Seller::orderByJoin('locations.title')```

##### Order Mixed
```Seller::orderByJoin('city.state.title')```

### Ordering (special cases with aggregate functions)

##### Order by relation count
```Seller::orderByJoin('locations.id', 'asc', 'COUNT')```

##### Order by relation field SUM
```Seller::orderByJoin('locations.is_primary', 'asc', 'SUM')```

##### Order by relation field AVG
```Seller::orderByJoin('locations.is_primary', 'asc', 'AVG')```

##### Order by relation field MAX
```Seller::orderByJoin('locations.is_primary', 'asc', 'MAX')```

##### Order by relation field MIN
```Seller::orderByJoin('locations.is_primary', 'asc', 'MIN')```

### Filtering (where or orWhere)

##### Filter BelongsTo
```Seller::whereJoin('city.title', '=', 'test')```

##### Filter HasOne
```Seller::whereJoin('locationPrimary.address', '=', 'test')```

##### Filter HasMany
```Seller::whereJoin('locations.title', '=', 'test')```

##### Filter Mixed
```Seller::whereJoin('city.state.title', '=', 'test')```

### Relation count

```
$sellers = Seller::setAppendRelationsCount(true)->join('locations', '=', 'test')
    ->get();
    
foreach ($sellers as $seller){
    echo 'Number of location = ' . $seller->locations_count;
}

```

### Filter (mix left join)

```
Seller::joinRelations('city', true)
    ->joinRelations('city.state', false)
    ->whereJoin('city.id', '=', 1)
    ->orWhereJoin('city.state.id', '=', 1)
```

## Generated queries

Query : 
```
Order::whereJoin('seller.id', '=', 1)->get();
```

Sql : 
```
select "orders".* 
    from "orders" 
    left join "sellers" on "sellers"."id" = "orders"."seller_id" 
    where "sellers"."id" = ? 
    and "orders"."deleted_at" is null 
    group by "orders"."id"
```

Query : 
```
Order::orderByJoin('seller.id', '=', 1)->get();
```

Sql : 
```
select "orders".*, MAX(sellers.id) as sort
    from "orders" 
    left join "sellers" on "sellers"."id" = "orders"."seller_id" 
    where "orders"."deleted_at" is null 
    group by "orders"."id"
    order by sort asc
```

## Elegance of package

Lets look how first example from documentation now looks like. This code : 

```
$posts = Post::select('posts.*')
    ->join('categories', 'categories.id', '=', 'posts.category_id')
    ->groupBy('posts.id')
    ->where('categories.deleted_at', '=', null)
    ->orderBy('categories.name');
    
if(request()->get('date')){
    $posts->where('date', $date)
}

$posts = $posts->get();
```

is now : 

```
$posts = Post::orderByJoin('category.name');
    
if(request()->get('date')){
    $posts->where('posts.date', $date)
}

$posts = $posts->get();
```

Both snippets do the same thing.

## Tests

This package is well covered with tests. If you want run tests just run **composer update** and then run tests with **"vendor/bin/phpunit"**

## Contribution

Feel free to create new issue for : 
* bug
* notice
* request new feature
* question
* clarification
* etc...



License
----

MIT


**Free Software, Hell Yeah!**
