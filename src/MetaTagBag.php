<?php
declare (strict_types = 1);

namespace Bjuppa\MetaTagBag;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

class MetaTagBag implements Arrayable, Htmlable
{
    /**
     * @var Collection
     */
    protected $tags;

    public function __construct(...$tags)
    {
        $this->tags = self::normalizeArguments($tags);
    }

    public function add(...$tags)
    {
        $this->tags = $this->tags->merge(self::normalizeArguments($tags));
        return $this;
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

    /**
     * Parse arguments into a collection of tags
     * each item corresponding to one meta tag.
     */
    protected static function normalizeArguments($args): Collection
    {
        $tags = new Collection();
        $tag = Collection::wrap($args)->reject(function ($value, $key) use (&$tags) {
            if (is_numeric($key)) {
                // Handle serialized data
                if (is_string($value)) {
                    $value = self::unserializeString($value);
                }

                // Handle Arrayable objects
                if ($value instanceof Arrayable) {
                    $value = $value->toArray();
                }

                // Handle generic objects
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

        return @\unserialize($string) ?: null;
    }

    public function toArray(): array
    {
        return $this->tags->toArray();
    }

    public function toHtml(): string
    {
        return $this->tags->map(function ($tag) {
            return "<meta " . $tag->map(function ($value, $name) {
                return $name . '="' . $value . '"';
            })->implode(' ') . ">";
        })->implode("\n");
    }
}
