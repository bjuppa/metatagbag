<?php
declare (strict_types = 1);

namespace Bjuppa\MetaTagBag\Tests;

use Bjuppa\MetaTagBag\MetaTagBag;
use PHPUnit\Framework\TestCase;

final class MetaTagBagTest extends TestCase
{
    public function testCanBeCreated(): void
    {
        $this->assertInstanceOf(
            MetaTagBag::class,
            new MetaTagBag()
        );
    }

    public function testCanBeCreatedFromSingleArray(): void
    {
        $bag = new MetaTagBag([
            'name' => 'description',
            'content' => 'A description',
        ]);

        $this->assertEquals(
            [
                [
                    'name' => 'description',
                    'content' => 'A description',
                ],
            ],
            $bag->toArray()
        );
    }

    public function testCanBeCreatedFromMultipleArrays(): void
    {
        $bag = new MetaTagBag([
            'name' => 'description',
            'content' => 'A description',
        ], [
            'name' => 'keywords',
            'content' => 'key,words',
        ]);

        $this->assertEquals(
            [
                [
                    'name' => 'description',
                    'content' => 'A description',
                ],
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
        $bag = new MetaTagBag([[
            'name' => 'description',
            'content' => 'A description',
        ], [
            'name' => 'keywords',
            'content' => 'key,words',
        ]]);

        $this->assertEquals(
            [
                [
                    'name' => 'description',
                    'content' => 'A description',
                ],
                [
                    'name' => 'keywords',
                    'content' => 'key,words',
                ],
            ],
            $bag->toArray()
        );
    }
}
