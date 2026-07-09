<?php

declare(strict_types=1);



namespace Gy\Core\Structure;

use Countable;
use Gy\Core\Contract\ItemInterface;
use IteratorAggregate;
use Traversable;

/**
 * @template TItem of ItemInterface
 * @template-implements IteratorAggregate<int<0, max>, TItem>
 */
abstract class Collection implements IteratorAggregate, Countable
{
    /** @var array<int<0, max>, TItem> */
    protected array $items;

    /**
     * @param TItem ...$items
     */
    public function __construct(ItemInterface ...$items)
    {
        foreach ($items as $item) {
            $this->insert($item);
        }
    }

    /**
     * @param TItem $item
     * @return $this
     */
    public function insert(ItemInterface $item): static
    {
        $this->items[] = $item;
        return $this;
    }

    public function getIterator(): Traversable
    {
        yield from $this->items;
    }
}
