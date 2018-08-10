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

### Input formats

All methods that operate on some kind of list of meta tags will accept almost any type of map-like (key-value) input, optionally nested in some kind of list.

#### Tags in separate arguments

The most terse syntax can be seen in the creation examples above, where multiple tags are supplied, each as its own argument to the method.

#### Flattening lists

If some kind of nested list is encountered, it will be flattened so that any item lacking a "string" key will become its own tag in the resulting one-dimensional list of tags.

#### Serialized tags

If a string is encountered within a supplied list, attempts will be made to deserialize it from json or PHP's stored representation.

#### Object tags

If an object is encountered within a supplied list, it will be converted to an array, and merged into the flattened list.
Implementations of Laravel's `Arrayable`, like `Collection` and other `MetaTagBag`s will work just fine,
and implementations of `MetaTagProvider` will pull out that instance's `MetaTagBag`.

## Output

The `MetaTagBag` should usually be rendered first within the `<head>` element, before any other elements like `<title>`.
This is because it may contain a `charset` meta tag, and that needs to come before any other content.

```php
// Return a string of HTML tags from the bag's contents
$bag->toHtml();
```

`MetaTagBag` implements
[Laravel's `Htmlable` contract](https://laravel.com/api/master/Illuminate/Contracts/Support/Htmlable.html)
so in a [Blade template](https://laravel.com/docs/blade) you can echo the tags
by putting any instance within curly braces:

```php
<head>
{{ Bjuppa\MetaTagBag\MetaTagBag::make(['name' => 'description', 'content' => 'A description']) }}
<title>Page title</title>
</head>
```

Casting a `MetaTagBag` to a string will also bring out the HTML representation:

```php
echo $bag; //Implicit string casting
$html = (string) $bag; //Explicit string casting
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

## Sorting tags

The `sort()` method called without arguments will return a new `MetaTagBag` instance where `charset`
and `http-equiv="X-UA-Compatible"` tags are placed first.

If a `callable` is given, it will be used just like [PHP's `uasort` parameters](https://secure.php.net/manual/en/function.uasort.php#refsect1-function.uasort-parameters).
