# Basic metadata structure

Metadata for entities are always organized into individual XML files. Each file uses the SimpleFM metadata namespace and
the root element must specify at least the class name of the entity, including its namespace, as well as the layout on
which the repository will operate on.

To support auto completion in IDEs, it is advised to include the schema location of the official XSD file. It will be
included in the following example but excluded from all further ones for simplicity reasons:

```xml
<?xml version="1.0" encoding="utf-8"?>
<entity
    xmlns="uri:soliantconsulting.com:simplefm:entity-metadata"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="uri:soliantconsulting.com:simplefm:entity-metadata
        https://soliantconsulting.github.io/SimpleFM/entity-metadata-5-1.xsd"
    class-name="My\Entity\SampleEntity"
    layout="sample-layout"
/>
```

Additionally, you can define an interface name on the root element, which will be used for automatically creating entity
proxies for lazy loading:

```xml
<?xml version="1.0" encoding="utf-8"?>
<entity … interface-name="My\Entity\SampleEntityInterface"/>
```

# Field properties

The most common type you are going to define in your metadata are field properties. These are any kind of fields in the
layout which can be mapped to scalar values, `Decimal` or `DateTimeImmutable`. When defining a field type, the following
three attributes are mandatory:

name
:   The name of the field in your layout

property
:   The property name on your entity

type
:   The type to which the value will be cast to on your entity. The following built-in types are supported:

    * **boolean**: will treat any value other than "0" or "" as true
    * **date-time**: will treat the value as `DateTimeImmutable`
    * **date**: will treat the value as `DateTimeImmutable` and convert it to a pure date
    * **float**: will treat the numeric value as float
    * **integer**: will treat the numeric value as integer
    * **nullable-string**: will treat the value as string but convert an empty string to null
    * **stream**: will treat the value as lazy loaded `StreamInterface`
    * **string**: will treat the value as string
    * **time**: will treat the value as `DateTimeImmutable` and convert it to a pure time

```xml
<?xml version="1.0" encoding="utf-8"?>
<entity …>
    <field name="Name" property="name" type="string"/>
</entity>
```

Additionally, the field element supports the following two boolean attributes which default to `false`:

read-only
:   Tells the repository to not persist this property

repeatable
:   Treats the field as a set of values

# Record ID

If you need to access the record ID of a record, you can specify it via a special `<record-id/>' element:

```xml
<?xml version="1.0" encoding="utf-8"?>
<entity …>
    <record-id property="recordId"/>
</entity>
```

# Relations

Sometimes your layout defines relations to other tables. For that reason, SimpleFM supports many-to-one, one-to-many and
one-to-one relations. For one-to-many relations, SimpleFM will create lazy-loaded collections, while for both
many-to-one and one-to-one relations it will create a lazy-loaded proxy.

!!!note "Proxy interface"
    When using any to-one relation, you need to define an interface name on the target entity's metadata, so that the
    repository can create a proxy for it. The only exception is when the relation has
    [eager hydration](#eager-hydration) enabled.

## One-to-many

A one-to-many relation is the simplest of the three relations. It is defined by the following attributes:

property
:   The property name on your entity

target-table
:   The table of the foreign entity as it shows on the layout

target-entity
:   The class name of the mapped entity

target-field-name
:   The name of the identifying ID field

```xml
<?xml version="1.0" encoding="utf-8"?>
<entity …>
    <one-to-many
        property="sampleChildren"
        target-table="children"
        target-entity="My\Entity\SampleEntityChild"
        target-field-name="ID"
    />
</entity>
```

## Many-to-one

A many-to-one relation is set up similarly as the [one-to-many](#one-to-many) relation and requires the same attributes
including a few additional ones:

name
:   The name of the field containing the foreign ID

target-property-name
:   The property name on the mapped entity containing the ID

Optionally, you can mark the relation as read-only by setting the `read-only` property to `true`.

```xml
<?xml version="1.0" encoding="utf-8"?>
<entity …>
    <many-to-one
        name="ParentID"
        property="sampleParent"
        target-table="parents"
        target-entity="My\Entity\SampleEntityParent"
        target-field-name="ID"
        target-property-name="id"
    />
</entity>
```

## One-to-one

When it comes to one-to-one relations, there are two types of it: the owning side and the inverse side.

### Owning side

The owning side takes the exact same attributes as the [many-to-one](#many-to-one) relation, including the optional
`read-only` attribute.

```xml
<?xml version="1.0" encoding="utf-8"?>
<entity …>
    <one-to-one-owning
        name="ParentID"
        property="sampleParent"
        target-table="parents"
        target-entity="My\Entity\SampleEntityParent"
        target-field-name="ID"
        target-property-name="id"
    />
</entity>
```

### Inverse side

While the owning side mirrors the behavior of the many-to-one relation, the inverse side mirrors that of the
[one-to-many](#one-to-many) relation and requires the same arguments:

```xml
<?xml version="1.0" encoding="utf-8"?>
<entity …>
    <one-to-one-inverse
        property="sampleChildren"
        target-table="children"
        target-entity="My\Entity\SampleEntityChild"
        target-field-name="ID"
    />
</entity>
```

## Eager hydration

Every relation can be eagerly hydrated when the sparse record contains all information to fully hydrate the parent or
child entity. To enable eager hydration, set the `eager-hydration` property on the relation to `true`.

While this gives you additional hydration overhead for each relation, it also saves you from doing additional queries
against FileMaker. Using eager hydration should only be considered when you are using specific relations often enough.
