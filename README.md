# Meta Tag Bag

A PHP class for collecting and manipulating [HTML meta tags](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/meta)
before echoing in the [`<head>`](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/head).
Works well with Laravel, and without.

Inspired by [Laravel's `MessageBag`](https://laravel.com/api/master/Illuminate/Support/MessageBag.html).

```bash
composer require bjuppa/metatagbag
```

## Table of contents

* [Creating a `MetaTagBag`](#creating-a-metatagbag)
* [Input Formats](#input-formats)
* [HTML Output](#html-output)
* [Adding Tags](#adding-tags)
* [Removing Tags](#removing-tags)
* [Filtering Tags](#filtering-tags)
* [Inspecting a `MetaTagBag`](#inspecting-a-metatagbag)
* [Sorting Tags](#sorting-tags)
* [Optional Manipulation](#optional-manipulation)
* [Converting to json](#converting-to-json)

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

## Input Formats

All methods that operate on some kind of list of meta tags will accept almost any type of map-like (key-value) input, optionally nested in some kind of list.

### Tags can be in separate arguments

The most terse syntax can be seen in the creation examples above, where multiple tags are supplied, each as its own argument to the method.

### Lists of tags are flattened

If some kind of nested list is encountered, it will be flattened so that any item lacking a "string" key will become its own
tag in the resulting *one-dimensional* list of tags.

```php
$bag = new MetaTagBag(
  [
    ['name' => 'description', 'content' => 'A description'],
    ['name' => 'keywords', 'content' => 'key,words'],
    [
      ['name' => 'nested', 'content' => 'This will end up in the top-level with the other tags'],
    ]
  ]
);
```

### Json strings are deserialized

If a string is encountered within a supplied list, attempts will be made to deserialize it from json.

```php
MetaTagBag::make('[{"name":"description","content":"A description"},{"name":"keywords","content":["key","words"]}]');
```

### Objects are converted to arrays

If an object is encountered within a supplied list, it will be converted to an array, and merged into the flattened list.
Implementations of [Laravel's `Arrayable`](https://laravel.com/api/master/Illuminate/Contracts/Support/Arrayable.html),
like [Laravel's `Collection`](https://laravel.com/api/master/Illuminate/Support/Collection.html)
and other `MetaTagBag`s will work just fine.
Implementations of
[`Bjuppa\MetaTagBag\Contracts\MetaTagProvider`](https://github.com/bjuppa/metatagbag/blob/master/src/Contracts/MetaTagProvider.php)
will pull out that instance's `MetaTagBag`.

```php
MetaTagBag::make(new MetaTagBag(['name' => 'description', 'content' => 'A description']));
```

## HTML Output

The `MetaTagBag` should usually be rendered first within the `<head>` element, before any other elements like `<title>`.
This is because it may contain a `charset` meta tag that should come before any other content.

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

Casting a `MetaTagBag` to `string` will also bring out the HTML representation:

```php
echo $bag; //Implicit string casting
$html = (string) $bag; //Explicit string casting
```

### Output of comma-separated list attributes

For HTML, any array attribute will be imploded into a comma-separated list.
This can for example be used with a `name="keywords"` meta tag,
where the keywords in the `content` attribute can be treated as a list until the time of rendering.

## Adding Tags

The `add(...$tags)` method will modify the `MetaTagBag` instance, adding any tags supplied without checking for duplicates.

The `merge(...$tags)` method will also modify the `MetaTagBag` instance, but will overwrite any existing tags having the same
`name`, `http-equiv`, `itemprop`, or `property` attributes.

### Merging array attributes

If a tag to be merged has an array as its `content` attribute,
that array will be merged with the `content` of any existing matching tag in the bag.
This can for example be used with `name="keywords"` meta tags,
where one may want to add keywords, rather than overwriting them.

## Removing Tags

The `forget(...$attributes)` method will remove all matching tags from the `MetaTagBag` instance.

## Filtering Tags

The `match(...$attributes)` method can be used to filter out matching tags into a new `MetaTagBag`.

The `unique()` method returns a new `MetaTagBag` where all duplicate tags have been removed
(keeping the last).

In addition, if `unique(...$attributes)` is called with arguments,
matching tags will only appear once in the returned `MetaTagBag`
(also keeping the last).

## Inspecting a `MetaTagBag`

The methods `count(...$attributes)` and `has(...$attributes)` can be used to count matching tags
or check if any matching tags exist in a bag.
Of course, `count()` can be called without arguments to return the total number of tags in the bag,
while calling `has()` without arguments will always return `false`.

The `content($attributes)` method will pull out the *value* of the `content` attribute of the last matching tag.
It's a wrapper around `getLastMatchingAttributeValue($attributeToGet, $attributesToMatch)`
that does the same for any attribute.

## Sorting Tags

The `sort()` method called without arguments will return a new `MetaTagBag` instance where `charset`
and `http-equiv="X-UA-Compatible"` tags are placed first.

If a callback is given, it will be used just like
[PHP's `uasort` parameters](https://secure.php.net/manual/en/function.uasort.php#refsect1-function.uasort-parameters).

## Optional Manipulation

The `pipe(callable $callback)` method passes the `MetaTagBag` to the given callback and returns the result.
For example it can be used to fluently check if a `MetaTagBag` contains some tag, and if so add or remove some other tag.

## Converting to json

`MetaTagBag` is [`JsonSerializable`](http://php.net/manual/en/class.jsonserializable.php)
so instances can be supplied directly to [PHP's `json_encode()`](http://php.net/manual/en/function.json-encode.php) function.
Also, because `MetaTagBag` implements
[Laravel's `Jsonable` contract](https://laravel.com/api/master/Illuminate/Contracts/Support/Jsonable.html),
there's also the `toJson()` method.
