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

    public function add(...$tags) {
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
            if (!is_string($key)) { //TODO: change !is_string to is_numeric, as numeric attribute names are not allowed anyway?
                // Handle json
                if(is_string($value)) {
                    $decoded_json = json_decode($value, true);
                    if(json_last_error() === JSON_ERROR_NONE) {
                        $value = $decoded_json;
                    } else {
                        $value = null;
                    }
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

    public function toArray(): array
    {
        return $this->tags->toArray();
    }
}
