<?php
declare (strict_types = 1);

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

    public function testCanBeCreatedFromSerialization(): void
    {
        $bag = new MetaTagBag(\serialize($this->descriptionTag));

        $this->assertEquals(
            [$this->descriptionTag],
            $bag->toArray()
        );
    }

    public function testIgnoresInvalidSerialization(): void
    {
        $bag = new MetaTagBag('a:2:{s:4:"name";s:11:"description";s:7:"content";s:13:"A description";');

        $this->assertEquals([], $bag->toArray());
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
            $bag->toHtml());
    }

    public function testEncodesHtmlSpecialCharacters(): void
    {
        $bag = new MetaTagBag(['a' => '<&>"']);

        $this->assertContains(
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
}
