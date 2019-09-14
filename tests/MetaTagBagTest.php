<?php

declare(strict_types=1);

namespace Bjuppa\MetaTagBag\Tests;

use Bjuppa\MetaTagBag\MetaTagBag;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

final class MetaTagBagTest extends TestCase
{
    protected $descriptionTag = [
        'name' => 'description',
        'content' => 'A description',
    ];

    protected $keywordsTag = [
        'name' => 'keywords',
        'content' => ['key', 'words'],
    ];

    protected $charsetTag = [
        'charset' => 'UTF-8',
    ];

    protected $XUACompatibleTag = [
        'http-equiv' => 'X-UA-Compatible',
        'content' => "IE=Edge",
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

    public function testCanBeCreatedThroughStaticMethod(): void
    {
        $bag = MetaTagBag::make($this->descriptionTag);
        $this->assertInstanceOf(MetaTagBag::class, $bag);

        $this->assertEquals(
            [$this->descriptionTag],
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

    public function testCanBeCreatedFromArrayable(): void
    {
        $bag = new MetaTagBag(new Collection($this->descriptionTag));

        $this->assertEquals(
            [$this->descriptionTag],
            $bag->toArray()
        );
    }

    public function testCanBeCreatedFromMetaTagBag(): void
    {
        $bag = new MetaTagBag(new MetaTagBag($this->descriptionTag));

        $this->assertEquals(
            [$this->descriptionTag],
            $bag->toArray()
        );
    }

    public function testCanBeCreatedFromMetaTagProviderContract(): void
    {
        $bag = new MetaTagBag(new \Bjuppa\MetaTagBag\Tests\Fakes\MetaTagProvider($this->descriptionTag));

        $this->assertEquals(
            [$this->descriptionTag],
            $bag->toArray()
        );
    }

    public function testCanBeCreatedFromObject(): void
    {
        $bag = new MetaTagBag((object) $this->descriptionTag);

        $this->assertEquals(
            [$this->descriptionTag],
            $bag->toArray()
        );
    }

    public function testIgnoresNumericKeys(): void
    {
        $bag = new MetaTagBag(['a', 1 => 'b', '1' => 'c', '0.1' => 'd', '-1' => 'e', 'keep' => 'me']);

        $this->assertEquals(
            [['keep' => 'me']],
            $bag->toArray()
        );
    }

    public function testIgnoresEmptyKeys(): void
    {
        $bag = new MetaTagBag(['' => 'a', 'keep' => 'me']);

        $this->assertEquals(
            [['keep' => 'me']],
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

    public function testCantBeCreatedFromPHPSerialization(): void
    {
        $bag = new MetaTagBag(\serialize($this->descriptionTag));

        $this->assertEmpty($bag->toArray());
    }

    public function testCanBeCreatedWithListAttribute(): void
    {
        $bag = new MetaTagBag(['a' => [1, 2]]);

        $this->assertEquals(
            [['a' => [1, 2]]],
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

    public function testAddCanBeChained(): void
    {
        $bag = new MetaTagBag();

        $this->assertSame($bag, $bag->add(['a' => 'b']));
    }

    public function testCanFilterUniqueTags(): void
    {
        $bag = new MetaTagBag(['a' => 'b', 'c' => 'd']);
        $bag->add(['c' => 'd', 'a' => 'b']);

        $this->assertEquals(
            [['c' => 'd', 'a' => 'b']],
            $bag->unique()->toArray()
        );
    }

    public function testCanFilterUniqueTagsByAttribute(): void
    {
        $bag = new MetaTagBag(['a' => 'b', 'c' => 'd', 'loose' => 'me']);
        $bag->add(['c' => 'd', 'a' => 'b']);
        $bag->add(['keep' => 'me']);
        $bag->add(['keep' => 'me too']);

        $this->assertEquals(
            [
                ['c' => 'd', 'a' => 'b'],
                ['keep' => 'me'],
                ['keep' => 'me too'],
            ],
            $bag->unique(['a' => 'b'])->toArray()
        );
    }

    public function testCanFilterUniqueTagsByAttributes(): void
    {
        $bag = new MetaTagBag(['a' => 'b', 'c' => 'd', 'loose' => 'me']);
        $bag->add(['a' => 'b', 'c' => 'd']);
        $bag->add(['a' => 'b', 'keep' => 'me']);

        $this->assertEquals(
            [
                ['a' => 'b', 'c' => 'd'],
                ['a' => 'b', 'keep' => 'me'],
            ],
            $bag->unique(['a' => 'b', 'c' => 'd'])->toArray()
        );
    }

    public function testCanFilterMultipleUniqueTags(): void
    {
        $bag = new MetaTagBag(
            ['a' => 'b', 'c' => 'd', 'loose' => 'me'],
            ['aa' => 'b', 'c' => 'd', 'loose' => 'me']
        );
        $bag->add(['c' => 'd', 'a' => 'b']);
        $bag->add(['aa' => 'b', 'c' => 'd']);
        $bag->add(['keep' => 'me']);
        $bag->add(['keep' => 'me too']);

        $this->assertEquals(
            [
                ['c' => 'd', 'a' => 'b'],
                ['aa' => 'b', 'c' => 'd'],
                ['keep' => 'me'],
                ['keep' => 'me too'],
            ],
            $bag->unique(['a' => 'b'], ['aa' => 'b'])->toArray()
        );
    }

    public function testUniqueReturnsNewInstance(): void
    {
        $bag = new MetaTagBag();

        $this->assertNotSame($bag, $bag->unique());
    }

    public function testCanBeConvertedToHTML(): void
    {
        $bag = new MetaTagBag(['a' => 'b', 'c' => 'd']);

        $this->assertEquals(
            "<meta a=\"b\" c=\"d\">",
            $bag->toHtml()
        );
    }

    public function testEncodesHtmlSpecialCharacters(): void
    {
        $bag = new MetaTagBag(['a' => '<&>"']);

        $this->assertStringContainsString(
            'a="&lt;&amp;&gt;&quot;"',
            $bag->toHtml()
        );
    }

    public function testHtmlIsFreeFromDuplicates(): void
    {
        $bag = new MetaTagBag(
            ['a' => 'b', 'c' => 'd'],
            ['c' => 'd', 'a' => 'b'],
            ['a' => 'b', 'c' => 'd', 'e' => 'f']
        );

        $this->assertEquals(
            implode(['<meta c="d" a="b">', '<meta a="b" c="d" e="f">'], "\n"),
            $bag->toHtml()
        );
    }

    public function testListAttributesAreCommaSeparatedInHTML(): void
    {
        $bag = new MetaTagBag(['a' => [1, 2]]);

        $this->assertEquals(
            '<meta a="1,2">',
            $bag->toHTML()
        );
    }

    public function testMergeCanBeChained(): void
    {
        $bag = new MetaTagBag(['a' => 'b']);

        $this->assertSame($bag, $bag->merge(['a' => 'b']));
    }

    public function testMergeAdds(): void
    {
        $bag = new MetaTagBag($this->descriptionTag);
        $bag->merge($this->keywordsTag);

        $this->assertEquals(
            [
                $this->descriptionTag,
                $this->keywordsTag,
            ],
            $bag->toArray()
        );
    }

    public function testMergeOverwritesByName(): void
    {
        $bag = new MetaTagBag(['name' => 'a', 'loose' => 'me']);
        $bag->merge(['name' => 'a']);

        $this->assertEquals(
            [['name' => 'a']],
            $bag->toArray()
        );
    }

    public function testMergeOverwritesByHttpEquiv(): void
    {
        $bag = new MetaTagBag(['http-equiv' => 'a', 'loose' => 'me']);
        $bag->merge(['http-equiv' => 'a']);

        $this->assertEquals(
            [['http-equiv' => 'a']],
            $bag->toArray()
        );
    }

    public function testMergeOverwritesByItemprop(): void
    {
        $bag = new MetaTagBag(['itemprop' => 'a', 'loose' => 'me']);
        $bag->merge(['itemprop' => 'a']);

        $this->assertEquals(
            [['itemprop' => 'a']],
            $bag->toArray()
        );
    }

    public function testMergeOverwritesByProperty(): void
    {
        $bag = new MetaTagBag(['property' => 'a', 'loose' => 'me']);
        $bag->merge(['property' => 'a']);

        $this->assertEquals(
            [['property' => 'a']],
            $bag->toArray()
        );
    }

    public function testListAttributesAreMerged(): void
    {
        $bag = new MetaTagBag(['name' => 'a', 'content' => [1, 2]]);
        $bag->merge(['name' => 'a', 'content' => [3, 2]]);

        $this->assertEquals(
            [['name' => 'a', 'content' => [3, 2, 1]]],
            $bag->toArray()
        );
    }

    public function testForgetCanBeChained(): void
    {
        $bag = new MetaTagBag(['a' => 'b']);

        $this->assertSame($bag, $bag->forget(['a' => 'b']));
    }

    public function testCanForgetTagsByAttribute(): void
    {
        $bag = new MetaTagBag(['loose' => 'me'], ['keep' => 'me']);
        $bag->forget(['loose' => 'me']);

        $this->assertEquals([['keep' => 'me']], $bag->toArray());
    }

    public function testCanForgetTagsByAttributes(): void
    {
        $bag = new MetaTagBag(['a' => 'b', 'loose' => 'me']);
        $bag->add(['a' => 'b', 'keep' => 'me']);
        $bag->add(['a' => 'b', 'loose_me' => 'too']);
        $bag->forget(['loose' => 'me', 'a' => 'b'], ['loose_me' => 'too']);

        $this->assertEquals([['a' => 'b', 'keep' => 'me']], $bag->toArray());
    }

    public function testMatchReturnsNewInstance(): void
    {
        $bag = new MetaTagBag();

        $this->assertNotSame($bag, $bag->match());
    }

    public function testEmptyMatchMatchesNothing(): void
    {
        $bag = new MetaTagBag($this->descriptionTag);

        $this->assertEquals([], $bag->match()->toArray());
    }

    public function testCanMatchTagsByAttribute(): void
    {
        $bag = new MetaTagBag(['a' => 'b', 'c' => 'd']);
        $bag->add(['e' => 'f', 'a' => 'b']);
        $bag->add(['loose' => 'me']);
        $bag->add(['a' => 'c', 'loose' => 'me too']);

        $this->assertEquals(
            [
                ['a' => 'b', 'c' => 'd'],
                ['e' => 'f', 'a' => 'b'],
            ],
            $bag->match(['a' => 'b'])->toArray()
        );
    }

    public function testCanMatchTagsByAttributes(): void
    {
        $bag = new MetaTagBag(['a' => 'b', 'c' => 'd']);
        $bag->add(['a' => 'b', 'loose' => 'me']);

        $this->assertEquals(
            [
                ['a' => 'b', 'c' => 'd'],
            ],
            $bag->match(['a' => 'b', 'c' => 'd'])->toArray()
        );
    }

    public function testCanMatchMultipleTags(): void
    {
        $bag = new MetaTagBag(['a' => 'b', 'c' => 'd']);
        $bag->add(['e' => 'f', 'a' => 'b']);
        $bag->add(['c' => 'd', 'e' => 'f']);
        $bag->add(['loose' => 'me']);
        $bag->add(['a' => 'c', 'loose' => 'me too']);

        $this->assertEquals(
            [
                ['a' => 'b', 'c' => 'd'],
                ['e' => 'f', 'a' => 'b'],
                ['c' => 'd', 'e' => 'f'],
            ],
            $bag->match(['a' => 'b'], ['c' => 'd'])->toArray()
        );
    }

    public function testCountOfEmptyBag(): void
    {
        $bag = new MetaTagBag();

        $this->assertEquals(0, $bag->count());
    }

    public function testCount(): void
    {
        $bag = new MetaTagBag($this->descriptionTag, $this->keywordsTag);

        $this->assertEquals(2, $bag->count());
    }

    public function testCountTagsByAttribute(): void
    {
        $bag = new MetaTagBag(
            ['a' => 'b', 'count' => 'me'],
            ['a' => 'b', 'count' => 'me too'],
            ['count' => 'me out']
        );

        $this->assertEquals(2, $bag->count(['a' => 'b']));
    }

    public function testCountTagsByAttributes(): void
    {
        $bag = new MetaTagBag(
            ['a' => 'b', 'count' => 'me'],
            ['a' => 'b', 'count' => 'me', 'c' => 'd'],
            ['a' => 'b', 'count' => 'me out']
        );

        $this->assertEquals(2, $bag->count(['a' => 'b', 'count' => 'me']));
    }

    public function testCountByMultipleTags(): void
    {
        $bag = new MetaTagBag(
            ['a' => 'b', 'count' => 'me'],
            ['a' => 'b', 'count' => 'me too'],
            ['a' => 'b', 'count' => 'me out']
        );

        $this->assertEquals(2, $bag->count(['count' => 'me'], ['count' => 'me too']));
    }

    public function testIsCountable(): void
    {
        $bag = new MetaTagBag($this->descriptionTag, $this->keywordsTag);

        $this->assertEquals(2, count($bag));

        $bag->add(['a' => 'b']);

        $this->assertEquals(3, count($bag));
    }

    public function testEmptyHasMatchesNothing(): void
    {
        $bag = new MetaTagBag($this->descriptionTag);

        $this->assertEquals(false, $bag->has());
    }

    public function testHas(): void
    {
        $bag = new MetaTagBag($this->descriptionTag);

        $this->assertEquals(true, $bag->has($this->descriptionTag));
    }

    public function testConvertingToJson(): void
    {
        $bag = new MetaTagBag($this->descriptionTag);

        $json = $bag->toJson();

        $this->assertIsString($json);

        $this->assertIsArray(json_decode($json, true));
        $this->assertEquals(json_last_error(), JSON_ERROR_NONE);

        $this->assertEquals(new MetaTagBag($json), $bag);
    }

    public function testConvertingToStringReturnsHtml(): void
    {
        $bag = new MetaTagBag($this->descriptionTag);

        $this->assertEquals($bag->toHtml(), (string) $bag);
    }

    public function testPipe(): void
    {
        $bag = new MetaTagBag($this->descriptionTag);

        $this->assertEquals([$this->descriptionTag], $bag->pipe(function ($bag) {
            return $bag->toArray();
        }));
    }

    public function testContentReturnsLastMatch(): void
    {
        $bag = MetaTagBag::make(['a' => 'b', 'content' => 'skip'])
            ->add(['a' => 'b', 'content' => 'yes'])
            ->add(['c' => 'd', 'content' => 'no match']);

        $this->assertEquals('yes', $bag->content(['a' => 'b']));
    }

    public function testNoMatchingContentReturnsNull(): void
    {
        $bag = MetaTagBag::make(['a' => 'b', 'content' => 'skip']);

        $this->assertNull($bag->content(['c' => 'd']));
    }

    public function testGetLastMatchingAttributeValue(): void
    {
        $bag = MetaTagBag::make(['a' => 'b', 'content' => 'skip'])
            ->add(['a' => 'b', 'content' => 'no', 'other' => 'yes']);

        $this->assertEquals('yes', $bag->getLastMatchingAttributeValue('other', ['a' => 'b']));
    }

    public function testSorting(): void
    {
        $bag = MetaTagBag::make(
            ['no' => '1'],
            $this->XUACompatibleTag,
            ['no' => '2'],
            ['no' => '3'],
            $this->charsetTag
        );

        $this->assertEquals("<meta charset=\"UTF-8\">\n<meta http-equiv=\"X-UA-Compatible\" content=\"IE=Edge\">\n<meta no=\"1\">\n<meta no=\"2\">\n<meta no=\"3\">", $bag->toHtml());
    }

    public function testSerializesToJson(): void
    {
        $bag = MetaTagBag::make($this->descriptionTag, $this->keywordsTag);
        $serialized = serialize($bag);

        $this->assertStringContainsString($bag->toJson(), $serialized);
        $this->assertEquals($bag->toArray(), unserialize($serialized)->toArray());
    }
}
