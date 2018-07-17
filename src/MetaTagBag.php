<?php
declare (strict_types = 1);

namespace Bjuppa\MetaTagBag;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

class MetaTagBag implements Arrayable
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
}
