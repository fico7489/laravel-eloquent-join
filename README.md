# Laravel Eloquent Join

This package introduces the join capability for sorting and filtering on eloquent relations.

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
You can perform filtering on the relationship field without joining but this package will give you the ability to do this easier.

## Install

1.Install package with composer
```
composer require fico7489/laravel-eloquent-join:"*"
```
With this statement, a composer will install highest available package version for your current laravel version.

2.Use Fico7489\Laravel\EloquentJoin\Traits\EloquentJoinTrait trait in your base model or only in particular models.

```
...
use Fico7489\Laravel\EloquentJoin\Traits\EloquentJoinTrait;
use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    use EloquentJoinTrait;
...
```

and that's it, you are ready to go.

## Instructions for use

##### Currently available relations for join queries
* **BelongsTo**
* **HasOne**.

##### New clauses for eloquent builder on BelongsTo and HasOne relations

* **orderByJoin($column, $sortBy = 'asc')**
    $sortBy argument is same as in default eloquent sortBy()
* **whereJoin($column, $operator = null, $value = null, $boolean = 'and')**
    $operator, $value, $boolean arguments are the same as in default eloquent where()
* **orWhereJoin($column, $operator = null, $value)**
    $operator and $value arguments are the same as in default eloquent orWhere()

##### Rules for column parameter in whereJoin, orWhereJoin and orderByJoin   

*  current table attributes
* related table attributes (relationship names with dots)
* related tables can be nested unlimited with any combination of HasOne and BelongsTo relations, they only need to meet **relation rules** for join queries.

```
->where('title', '=', 'test')
->where('relationName.title', '=', 'test')
->where('relationName.relationNameSecond.title', '=', 'test')
```

##### Allowed clauses on BelongsTo and HasOne relations on which you want use join clauses on the query

* Relations that you want to use for join queries can only have this clauses : **where**, **orWhere**, **withTrashed**, **onlyTrashed**, **withoutTrashed**. 
* Clauses **where** and **orWhere** can only have this variation **->where($columnn, $operator, $attribute)**, closures are not allowed.
* Other clauses like whereHas, orderBy etc. are not allowed.
* You can add not allowed clauses on relations and use them in the normal eloquent way, but in that case, you can't use those relations for join queries.
* If **withTrashed**, **onlyTrashed** or **withoutTrashed** is not applied for relation modes that use SoftDeletes, default behavior is **withoutTrashed**, this means that join query will by default only look for related tables that are not soft deleted.

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

The reason why the second relation is not allowed is that this package applies where, orWhere and other clauses on the left join (all eloquent clauses can't be performed on join). Eloquent can use all those clauses because eloquent use subqueries not join.

##### Other 
* You can combine new clauses unlimited times
* If you combine clauses more times on same relation package will join related table only once
* You can combine join clauses e.g. whereJoin() with elaquent clauses e.g. orderBy()

```
Seller::whereJoin('title', 'test')->whereJoin('city.title', 'test')->orderByJoin('city.title')->get();
```

You might get a picture that there are many rules and restriction, but it is really not like that. 
Don't worry, if you anyway create the query that is not allowed **EloquentJoinException** will be thrown with explaining what happened.

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