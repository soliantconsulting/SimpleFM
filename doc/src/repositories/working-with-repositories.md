# Managing entity persistence

No matter how you created your repository, they always implement the same `RepositoryInterface` and while act exactly
the same. Let's say you want to insert a new entity into your layout, you'd do something like this:

```php
<?php
$repository->insert(new SampleEntity('test'));
```

Similary, when you want to update the record, you'd call the `update()` method:

```php
<?php
$repository->update($changedSampleEntity);
```

In the same way, you can also delete a record from the layout:

```php
<?php
$repository->delete($oldSampleEntity);
```

Please note that both `update()` and `delete()` only work with entities which you retrieved via the repository (also
known as managed entities). If you try to pass an entity to those methods which is not known by the repository, you will
get an exception. By default, both `update()` and `delete()` will instruct the FileMaker Server to only execute that
command when the `mod-id` is the same as the one from the local entity to avoid changing records which were modified in
another place. You can disable that behaviour by passing `true` to the second `$force` parameter of those methods.

# Retrieving entities through a simple search

Repositories offer multiple ways, to retrieve entites. The simplest one is by calling the `find()` method with a record
ID of the record you want to retrieve. The result will either be an instance of that entity or null:

```php
<?php
$sampleEntity = $repository->find(1);
```

Similary, there is a `findBy()` and a `findOneBy()` method for either retrieving multiple records or a single one with
a simple field search. Both methods take an array field search values, where the array key is the field name and the
array value is the actual search. By default, the search value is automatically quoted. If you need to perform more
advanced searches, you can pass `false` to the `$autoQuoteSearch` parameter and do required quoting manually by passing
the value to quote to the `quoteString()` method. For details about the different search options, please refer to the
FileMaker documentation.

If you only want to retrieve all records from the layout without filterinig, there's also a `findAll()` method. All
methods which return multiple records also accept a `$sort` parameter for sorting the results, as well as a `$limit` and
`$offset` parameter.

# Performing complex searches

While the `findBy()` and `findOneBy()` methods will always perform an `AND` query. something you may want to make `OR`
queries or mixed queries. To allow this, the repository exposes two methods, namely `findByQuery()` and
`findOneByQuery()`. Both work similar to the prior two methods, but instead of a search array they take a `Query`
object:

```php
<?php
use Soliant\SimpleFM\Query;

$query = new Query\Query(
    new Query\Conditions(
        false,
        new Query\Field('ID', '1'),
        new Query\Field('Name', 'foo'),
        new Query\Field('Status', '!=closed')
    ),
    new Query\Conditions(
        false,
        new Query\Field('ID', '2'),
        new Query\Field('Name', 'foo'),
        new Query\Field('Status', '!=closed')
    )
);

$sampleEntities = $respository->findByQuery($query);
```

This will query will look for any record which `ID` is either 1 or 2 and which `Name` equals "foo", but the `Status`
field must not be "closed".
