<?php
declare (strict_types = 1);

namespace Bjuppa\MetaTagBag;

use Bjuppa\MetaTagBag\Contracts\MetaTagProvider;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;

class MetaTagBag implements Arrayable, Jsonable, Htmlable, \Countable, \JsonSerializable, \Serializable
{
    /**
     * @var Collection
     */
    protected $tags;

    public function __construct(...$tags)
    {
        $this->tags = self::normalizeArguments($tags);
    }

    public static function make(...$tags)
    {
        return new static($tags);
    }

    public function add(...$tags)
    {
        $this->tags = $this->tags->merge(self::normalizeArguments($tags));
        return $this;
    }

    public function merge(...$tags)
    {
        return $this->add(self::normalizeArguments($tags)->map(function ($tag) {
            foreach (['name', 'http-equiv', 'itemprop', 'property'] as $attribute) {
                if ($tag->has($attribute)) {
                    if (isset($tag['content']) and is_array($tag['content'])) {
                        $tag['content'] = array_unique(array_merge($tag['content'], (array) $this->content($tag->only($attribute))));
                    }
                    $this->forget($tag->only($attribute));
                }
            }
            return $tag;
        }));
    }

    public function forget(...$attributes)
    {
        $forget = self::normalizeArguments($attributes);
        $this->tags = $this->tags->reject(function ($tag) use ($forget) {
            return (bool) $forget->first(function ($attributes) use ($tag) {
                return $attributes->diffAssoc($tag)->isEmpty();
            });
        })->values();
        return $this;
    }

    /**
     * Sort through each item with a callback.
     *
     * @param  callable|null  $callback
     * @return static
     */
    public function sort(callable $callback = null)
    {
        $callback = $callback ?: function ($a, $b) {
            if (!empty($a['charset'])) {
                return -1;
            }
            if (!empty($b['charset'])) {
                return 1;
            }
            if ($a['http-equiv'] ?? null == 'X-UA-Compatible') {
                return -1;
            }
            if ($b['http-equiv'] ?? null == 'X-UA-Compatible') {
                return 1;
            }
            return 0;
        };
        return new static($this->tags->sort($callback));
    }

    public function unique(...$attributes)
    {
        $tags = $this->tags->reverse();
        self::normalizeArguments($attributes)->each(function ($attributes) use (&$tags) {
            $tags = $tags->unique(function ($tag) use ($attributes) {
                if ($attributes->diffAssoc($tag)->isEmpty()) {
                    return $attributes;
                }
                return $tag;
            });
        });
        return new static($tags->unique()->reverse());
    }

    public function match(...$attributes)
    {
        $match = self::normalizeArguments($attributes);
        return new static($this->tags->filter(function ($tag) use ($match) {
            return (bool) $match->first(function ($attributes) use ($tag) {
                return $attributes->diffAssoc($tag)->isEmpty();
            });
        })->values());
    }

    public function count(...$attributes): int
    {
        if (empty($attributes)) {
            return $this->tags->count();
        }
        return $this->match($attributes)->count();
    }

    public function has(...$attributes): bool
    {
        return (bool) $this->count($attributes);
    }

    public function content($attributes)
    {
        return $this->getLastMatchingAttributeValue('content', $attributes);
    }

    public function getLastMatchingAttributeValue($attributeToGet, $attributesToMatch)
    {
        return $this->match($attributesToMatch)->tags->map->get($attributeToGet)->last();
    }

    /**
     * Pass the bag to the given callback and return the result.
     *
     * @param  callable $callback
     * @return mixed
     */
    public function pipe(callable $callback)
    {
        return $callback($this);
    }

    /**
     * Parse arguments into a collection of tags
     * each item corresponding to one meta tag.
     */
    protected static function normalizeArguments($args): Collection
    {
        $tags = new Collection();
        $tag = Collection::wrap($args)->reject(function ($value, $key) use (&$tags) {
            if (is_numeric($key)) {
                if ($value instanceof MetaTagProvider) {
                    $value = $value->getMetaTagBag();
                }

                if (is_string($value)) {
                    $value = self::unserializeString($value);
                }

                if ($value instanceof Arrayable) {
                    // This also handles other instances of MetaTagBag as they are Arrayable too
                    $value = $value->toArray();
                }

                if (\is_object($value)) {
                    $value = (array) $value;
                }

                $tags = $tags->merge(self::normalizeArguments($value));
                return true;
            }
        });
        if ($tag->count()) {
            $tags->push($tag);
        }
        return $tags;
    }

    /**
     * Unserialize string to array
     * @return array|null
     */
    protected static function unserializeString(string $string)
    {
        $decoded_json = json_decode($string, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded_json;
        }

        return null;
    }

    public function toArray(): array
    {
        return $this->tags->toArray();
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toHtml(): string
    {
        return $this->unique()->sort()
            ->tags->map(function ($tag) {
                return "<meta " . $tag->map(function ($value, $name) {
                    return $name . '="' . htmlspecialchars(Collection::wrap($value)->flatten()->implode(',')) . '"';
                })->implode(' ') . ">";
            })
            ->implode("\n");
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    public function __toString()
    {
        return $this->toHtml();
    }

    public function serialize()
    {
        return $this->toJson();
    }

    public function unserialize($data)
    {
        $this->__construct($data);
    }
}
