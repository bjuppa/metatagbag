# Meta Tag Bag

A PHP class for collecting and manipulating HTML meta tags before echoing.
Works well with Laravel, and without.

Inspired by [Laravel's `MessageBag`](https://laravel.com/api/master/Illuminate/Support/MessageBag.html).

`composer require bjuppa/metatagbag`

## Creating a `MetaTagBag`

```php
use Bjuppa\MetaTagBag\MetaTagBag;

$bag = new MetaTagBag(
  ['name' => 'description', 'content' => 'A description'],
  ['name' => 'keywords', 'content' => 'key,words']
);

// ...or using a static creator:

$bag = MetaTagBag::make(
  ['name' => 'description', 'content' => 'A description'],
  ['name' => 'keywords', 'content' => 'key,words']
);

```

## Output

```php
// Return a string of HTML tags from the bag's contents
$bag->toHtml();
```

`MetaTagBag` implements
[Laravel's `Htmlable` contract](https://laravel.com/api/master/Illuminate/Contracts/Support/Htmlable.html)
so in a [Blade template](https://laravel.com/docs/blade) you can echo the tags
by putting any instance within curly braces:

```php
{{ Bjuppa\MetaTagBag\MetaTagBag::make(['name' => 'description', 'content' => 'A description']) }}
```

## Adding tags

The `add()` method will modify the `MetaTagBag` instance, adding any tags supplied without checking for duplicates.

The `merge()` method will also modify the `MetaTagBag` instance, but will overwrite any existing tags having the same
`name`, `http-equiv`, `itemprop`, or `property` attributes.

## Removing tags

The `forget()` method will remove all matching tags from the `MetaTagBag` instance.

## Filtering tags

The `unique()` method returns a new `MetaTagBag` instance where all duplicate tags have been removed
(keeping the last).

In addition, if `unique()` is called with parameters,
matching tags will only appear once in the new `MetaTagBag`
(also keeping the last).
