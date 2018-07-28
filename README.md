# Meta Tag Bag

A PHP class for collecting HTML meta tags.
Works well with Laravel, and without.

`composer require bjuppa/metatagbag`

## Creating a `MetaTagBag`

```php
use Bjuppa\MetaTagBag\MetaTagBag;

$bag = new MetaTagBag(
  [
    'name' => 'description',
    'content' => 'A description',
  ],
  [
    'name' => 'keywords',
    'content' => 'key,words',
  ]
);

// ...or using static creator:

$bag = MetaTagBag::make(
  [
    'name' => 'description',
    'content' => 'A description',
  ],
  [
    'name' => 'keywords',
    'content' => 'key,words',
  ]
);

```

## Output


## Adding tags

## Removing tags

## Filtering tags

## Development & Testing

`composer test` from the project directory will run the default test suite.

If you want your own local configuration for phpunit,
copy the file `phpunit.xml.dist` to `phpunit.xml` and modify the latter to your needs.
