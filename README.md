# Laravel Eloquent Join

This package introduces the join capability for sorting and filtering on eloquent relations.

NEW 3.* PACKAGE VERSION WITH MANY NEW FEATURES RELEASED  (26.8.2018)

SUPPORT FOR HASMANY RELATION COMMIONG SOON !

## Eloquent Problems

You can't perform sorting on the relationship field without manually joining related table which is very awkward. Let me give you a few reasons why. If you have a table with posts and related categories your code might look like this:

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

    ->select('posts.*')
    
reason : without select() id from category can be selected and hydrated into Post model.

2.The second problem is that you need to worry about groupBy.

    ->groupBy('posts.id');
    
reason : if the relation is HasOne and there are more than one categories for the post, the query will return more rows for categories.

3.The third problem is that you need to change all other where clauses from : 

    ->where('date', $date)

to

    ->where('posts.date', $date)
    
reason : post and category can have "date" attribute and in that case without selecting attribute with table "ambiguous column" error will be thrown.

4.The fourth problem is that you are using table names(not models) and this is also bad and awkward.

    ->where('posts.date', $date)
    
5.The fifth problem is that you need to worry about soft deletes for joined tables. If the category is using SoftDeletes trait you must add : 

    ->where('categories.deleted_at', '=', null)
    
This package will take care of all above problems for you out of the box.
Unlike **sorting**, you can perform **filtering** on the relationship fields without joining related tables but this package will give you the ability to do this easier.

## Version Compatibility

NEW 3.* PACKAGE VERSION WITH MANY NEW FEATURES RELEASED

New version

| Laravel Version | Package Tag | Supported | Development Branch
|-----------------|-------------|-----------| -----------|
| >= 5.5.0 | 3.* | yes | master
| < 5.5.0 | - | no | -

 * new package version with many improvments and bug fixes is 3.*
 * 3.* version is not backward compatible with any 2.* version
 * 3.* version is tested for laravel 5.5 and 5.6.

Old deprecated versions

| Laravel Version | Package Tag | Supported | Development Branch
|-----------------|-------------|-----------| -----------|
| 5.6.* | 2.2.* | no | master
| 5.5.* | 2.1.* | no | 2.1
| 5.4.* | 2.0.* | no | 2.0
| 5.3.* | 1.3.* | no | 1.3
| 5.2.* | 1.2.* | no | 1.2
| <5.2 | - | no |

## Install

1.Install package with composer
```
composer require fico7489/laravel-eloquent-join:"*"
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

and that's it, you are ready to go.

## Options

##### Use table alias
Should we use alias for joined tables (default = false)

With true query will look like this : 
```
select "sellers".* from "sellers" 
    left join "locations" as "5b5c093d2e00f" 
	...
```

With false query will look like this : 
```
select "sellers".* from "sellers" 
	left join "locations"                    
	...
```

Set option in your base model : 
```
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        $this->useTableAlias = true;
    }
```

## Instructions for use

##### Currently available relations for join queries
* **BelongsTo**
* **HasOne**.

##### New clauses for eloquent builder on BelongsTo and HasOne relations

* **orderByJoin($column, $sortBy = 'asc', $leftJoin = true)**
    ***$column*** argument is same as in default eloquent orderBy()
    ***$direction*** argument is same as in default eloquent orderBy()
    ***$leftJoin*** argument defines if eloquent should perform left join or inner join
    
* **whereJoin($column, $operator = null, $value = null, $boolean = 'and')**
    ***$column***, ***$operator***, ***$value*** and ***$boolean*** arguments are the same as in default eloquent where()
    
* **orWhereJoin($column, $operator = null, $value)**
    ***$column***, ***$operator*** and ***$value*** arguments are the same as in default eloquent where()

##### Rules for column parameter in whereJoin, orWhereJoin and orderByJoin   

*  current table attributes
```
->where('title', '=', 'test')
```
* related table attributes (relationship names with dots)
```
->where('relationName.title', '=', 'test')
```
* related tables can be nested unlimited with any combination of HasOne and BelongsTo relations, they only need to meet **relation rules** for join queries.
```
->where('relationName.relationNameSecond.title', '=', 'test')
```

##### Allowed clauses on BelongsTo and HasOne relations on which you can use join clauses on the query

* Relations that you want to use for join queries can only have this clauses : **where**, **orWhere**, **withTrashed**, **onlyTrashed**, **withoutTrashed**. 
* Clauses **where** and **orWhere** can only have this variations **->where($column, $operator, $value)** and **->where([$column => $value])**, closures are not allowed.
* Other clauses like **whereHas**, **orderBy** etc. are not allowed.
* You can add not allowed clauses on relations and use them in the normal eloquent way, but in that case, you can't use those relations for join queries.

Allowed relation
```
public function locationPrimary()
{
    return $this->hasOne(Location::class)
        ->where('is_primary', '=', 1)
        ->orWhere('is_primary', '=', 1)
        ->withTrashed();
}
```
Not allowed relation : 

```
public function locationPrimary()
{
    return $this->hasOne(Location::class)
        ->where('is_primary', '=', 1)
        ->orWhere('is_primary', '=', 1)
        ->withTrashed()
        ->whereHas('state', function($query){return $query;}
        ->orderBy('name');
}
```

The reason why the second relation is not allowed is that this package should apply all those clauses on the join clause,  eloquent use all those clauses isolated with subqueries NOT on join clause and that is more simpler.

##### Other 
* You can combine new clauses unlimited times
* If you combine clauses more times on same relation package will join related table only once
* You can combine join clauses e.g. whereJoin() with elouent clauses e.g. orderBy()

```
Seller::whereJoin('title', 'test')->whereJoin('city.title', 'test')->orderByJoin('city.title')->get();
```

You might get a picture that there are to many rules and restriction, but it is really not like that. 
Don't worry, if you anyway create the query that is not allowed appropriate exception will be thrown and you will know what happened.

## See action on real example

Database schema : 

![Database schema](https://raw.githubusercontent.com/fico7489/laravel-eloquent-join/master/readme/era.png)

Models : 

```
class Seller extends BaseModel
{
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
class LocationAddress extends BaseModel
{
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
```
class State extends BaseModel
{
```

### Ordering

##### Order sellers by seller title
```Seller::orderByJoin('title')```

##### Order sellers by city name
```Seller::orderByJoin('city.title')```

##### Order sellers by state name
```Seller::orderByJoin('city.state.title')```

##### Order sellers by primary location address
```Seller::orderByJoin('locationPrimary.address')```

##### Order sellers by locationAddress name of primary location
```Seller::orderByJoin('locationPrimary.locationAddressPrimary.address')```

##### You can also combine orderBy more times
```Seller::orderByJoin('title')->orderBy('city.title')```

### Filtering

##### Filter sellers which have title = 'test'
```Seller::whereJoin('title', 'test')```

##### Filter sellers which have city name = 'test'
```Seller::whereJoin('city.title', '=', 'test')```

##### Filter sellers which have state name = 'test'
```Seller::whereJoin('city.state.title', '=', 'test')```

##### Filter sellers which have primary location address = 'test'
```Seller::whereJoin('locationPrimary.address', '=', 'test')```

##### Filter sellers which have locationAddress name of primary location = 'test'
```Seller::whereJoin('locationPrimary.locationAddressPrimary.address', '=', 'test')```

##### You can also combine orderBy more times
```Seller::whereJoin('title', 'test')->whereJoin('city.title', 'test')```

Lets look how first example from documentation now looks like. This code : 

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

is now : 

```
$posts = Post::orderByJoin('category.name');
    
if(request()->get('date')){
    $posts->where('posts.date', $date)
}

$posts = $posts->get();
```

Both snippets generates the same MySql query.

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