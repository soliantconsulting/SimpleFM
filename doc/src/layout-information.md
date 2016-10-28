# Retrieving information about a layout

Sometimes you need to programatically retrieve information about a specific layout, like the available fields, field set
options and the like. For this purpose SimpleFM provides a layout client, which can be used similary to the result set
client:

```php
<?php
use Soliant\SimpleFM\Client\Layout\LayoutClient;
use Soliant\SimpleFM\Connection\Command;

$layoutClient = new LayoutClient($connection);
$sampleLayout = $layoutClient->execute(new Command(
    'sample-layout',
    ['-view' => null]
));
```

The result will be a `Layout` object, which contains all information about the layout and its fields:

```php
<?php
sprintf("Database: %s\n", $sampleLayout->getDatabase());
sprintf("Layout: %s\n", $sampleLayout->getName());

print "Fields:\n";

foreach ($sampleLayout->getFields() as $field) {
    sprintf("    Name: %s\n", $field->getName());
    sprintf("    Type: %s\n", $field->getType());
    
    if (!$field->hasValueList()) {
        continue;
    }
    
    $valueList = $field->getValueList();
    sprintf("    Value list (%s):\n", $valueList->getName());
    
    foreach ($valueList->getValues() as $value) {
        sprintf("        %s: %s\n", $value->getValue(), $value->getDisplay());
    }
}
```

You can also retrieve individual fields instead of looping over all fields:

```php
<?php
if ($sampleLayout->hasField('foo')) {
    $field = $sampleLayout->getField('foo');
}
```

