# Getting rid of manual code

While it is feasible to create all your repositories manully, it can become a daunting task to do this for a lot of
entities. Thus it is recommended to only define the metadata about your entity, and let SimpleFM create the
repositories and their hydration and extraction strategies for you automatically.

# Defining your metadata file

The first thing you want to do is to create your metadata file. This is a simple XML file which describes all fields
and relations of the entity in the given layout. You should choose one folder in which you place all those XML files,
and each XML file must have a filename which equals the class name of the entity, with the namespace delimiters replaced
by periods. So when your entity is named `My\Entity\SampleEntity`, the acccording filename would be
`My.Entity.SampleEntity.xml`:

```xml
<?xml version="1.0" encoding="utf-8"?>
<entity
    xmlns="uri:soliantconsulting.com:simplefm:entity-metadata"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="uri:soliantconsulting.com:simplefm:entity-metadata
        https://soliantconsulting.github.io/SimpleFM/xsd/entity-metadata-5-0.xml"
    class-name="My\Entity\SampleEntity"
    layout="sample-layout"
>
    <field name="Name" property="name" type="string"/>
</entity>
```

You can read more about metadata definitions in the the [Metadata chapter](repositories/metadata.md)

# Creating a repository builder

Now that we have our metadata defined, we only need to instantiate a repository builder for it:

```php
<?php
use Soliant\SimpleFM\Repository\Builder\Metadata\MetadataBuilder;
use Soliant\SimpleFM\Repository\Builder\Proxy\ProxyBuilder;
use Soliant\SimpleFM\Repository\Builder\RepositoryBuilder;

$repositoryBuilder = new RepositoryBuilder(
    $resultSetClient,
    new MetadataBuilder('/path/to/xml/folder'),
    new ProxyBuilder()
);
```

# Retrieving a repository

Since we have our repository builder created, we can now use it to retrieve repositories for any entity we have defined:

```php
<?php
$sampleEntityRepository = $repositoryBuilder->buildRepository(SampleEntity::class);
```
