<?php

namespace Bjuppa\MetaTagBag\Tests\Fakes;

use Bjuppa\MetaTagBag\MetaTagBag;
use Bjuppa\MetaTagBag\Contracts\MetaTagProvider as MetaTagProviderContract;

class MetaTagProvider implements MetaTagProviderContract
{
    public function __construct(...$args) {
        $this->metaTagBag = new MetaTagBag($args);
    }

    public function getMetaTagBag(): \Bjuppa\MetaTagBag\MetaTagBag
    {
        return $this->metaTagBag;
    }
}
