# Creating a hydrator and extractor

For creating a repository completely manually, you have to define a hydrator and an extractor which take care of
converting an entity to record data and visa-versa. Let's say that you have a layout with a simple `name` field, these
could look something like this:

```php
<?php
use Soliant\SimpleFM\Repository\ExtractionInterface;
use Soliant\SimpleFM\Repository\HydrationInterface;

final class SampleHydration implements HydrationInterface
{
    public function hydrateNewEntity(array $data)
    {
        return new SampleEntity($data['name']);
    }

    public function hydrateExistingEntity(array $data, $entity)
    {
        $entity->setName($data['name']);
    }
}

final class SampleExtraction implements ExtractionInterface
{
    public function extract($entity) : array
    {
        return [
            'name' => $entity->getName(),
        ];
    }
}
```

# Initializing a repository

With these two classes and your result set client in place, you can craft your very first repository:

```php
<?php
use Soliant\SimpleFM\Repository\Repository;

$repository = new Repository(
    $resultSetClient,
    'sample-layout',
    new SampleHydration(),
    new SampleExtraction()
);
```

