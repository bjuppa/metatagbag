<?php
namespace Bjuppa\MetaTagBag\Contracts;

interface MetaTagProvider {
    /**
     * Get the meta-tags for the instance.
     */
    public function getMetaTagBag(): \Bjuppa\MetaTagBag\MetaTagBag;
}
