<?php
declare (strict_types = 1);

namespace Bjuppa\MetaTagBag\Tests;

use Bjuppa\MetaTagBag\MetaTagBag;
use PHPUnit\Framework\TestCase;

final class MetaTagBagTest extends TestCase
{
    protected $descriptionTag = [
        'name' => 'description',
        'content' => 'A description',
    ];

    protected $keywordsTag = [
        'name' => 'keywords',
        'content' => 'key,words',
    ];

    public function testCanBeCreated(): void
    {
        $bag = new MetaTagBag();
        $this->assertInstanceOf(MetaTagBag::class, $bag);

        $this->assertEquals(
            [],
            $bag->toArray()
        );
    }

    public function testCanBeCreatedFromSingleArray(): void
    {
        $bag = new MetaTagBag($this->descriptionTag);

        $this->assertEquals(
            [$this->descriptionTag],
            $bag->toArray()
        );
    }

    public function testCanBeCreatedFromMultipleArrays(): void
    {
        $bag = new MetaTagBag(
            $this->descriptionTag,
            $this->keywordsTag
        );

        $this->assertEquals(
            [
                $this->descriptionTag,
                $this->keywordsTag,
            ],
            $bag->toArray()
        );
    }

    public function testCanBeCreatedFromArrayOfArrays(): void
    {
        $bag = new MetaTagBag([$this->descriptionTag, $this->keywordsTag]);

        $this->assertEquals(
            [
                $this->descriptionTag,
                $this->keywordsTag,
            ],
            $bag->toArray()
        );
    }

    public function testCanAddDuplicate(): void
    {
        $bag = new MetaTagBag($this->descriptionTag);
        $bag->add($this->descriptionTag);

        $this->assertEquals(
            [
                $this->descriptionTag,
                $this->descriptionTag,
            ],
            $bag->toArray()
        );
    }

    public function testCanBeCreatedFromJson(): void
    {
        $bag = new MetaTagBag('{"a": "b"}');

        $this->assertEquals(
            [['a' => 'b']],
            $bag->toArray()
        );
    }

    public function testIgnoresInvalidJson(): void
    {
        $bag = new MetaTagBag("{'a': 'b}");

        $this->assertEquals([], $bag->toArray());
    }
}
