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

    public function testCanBeCreated(): void
    {
        $bag = new MetaTagBag();
        $this->assertInstanceOf(MetaTagBag::class, $bag);

        $this->assertEquals([], $bag->toArray());
    }

    public function testCanBeCreatedFromSingleArray(): void
    {
        $bag = new MetaTagBag($this->descriptionTag);

        $this->assertEquals([$this->descriptionTag], $bag->toArray());
    }

    public function testCanBeCreatedFromMultipleArrays(): void
    {
        $bag = new MetaTagBag($this->descriptionTag, [
            'name' => 'keywords',
            'content' => 'key,words',
        ]);

        $this->assertEquals(
            [
                $this->descriptionTag,
                [
                    'name' => 'keywords',
                    'content' => 'key,words',
                ],
            ],
            $bag->toArray()
        );
    }

    public function testCanBeCreatedFromArrayOfArrays(): void
    {
        $bag = new MetaTagBag([$this->descriptionTag, [
            'name' => 'keywords',
            'content' => 'key,words',
        ]]);

        $this->assertEquals(
            [
                $this->descriptionTag,
                [
                    'name' => 'keywords',
                    'content' => 'key,words',
                ],
            ],
            $bag->toArray()
        );
    }

    public function testCanAddDuplicate(): void
    {
        $bag = new MetaTagBag($this->descriptionTag);
        $bag->add($this->descriptionTag);

        $this->assertEquals([$this->descriptionTag, $this->descriptionTag], $bag->toArray());
    }
}
