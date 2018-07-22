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
        $bag = new MetaTagBag(['a', 1 => 'b', '1' => 'c', '0.1' => 'd', 'keep' => 'me']);

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

    public function testCanBeConvertedToHTML()
    {
        $bag = new MetaTagBag(['a' => 'b', 'c' => 'd']);

        $this->assertEquals(
            "<meta a=\"b\" c=\"d\">",
            $bag->toHtml());
    }

    public function testEncodesHtmlSpecialCharacters()
    {
        $bag = new MetaTagBag(['a' => '<&>"']);

        $this->assertContains(
            'a="&lt;&amp;&gt;&quot;"',
            $bag->toHtml()
        );
    }
}
