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

    public function __construct(...$tags) {
        $this->tags = self::normalizeArguments($tags);
    }

    /**
     * Parse arguments into a collection of tags
     * each item corresponding to one meta tag.
     */
    protected static function normalizeArguments($args): Collection
    {
        return Collection::wrap($args);
    }

    public function toArray(): array {
        return $this->tags->toArray();
    }
}
