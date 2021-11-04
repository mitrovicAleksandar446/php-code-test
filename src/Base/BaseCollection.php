<?php

namespace Tymeshift\PhpTest\Base;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;
use Tymeshift\PhpTest\Exceptions\InvalidCollectionDataProvidedException;
use Tymeshift\PhpTest\Interfaces\CollectionInterface;
use Tymeshift\PhpTest\Interfaces\EntityInterface;
use Tymeshift\PhpTest\Interfaces\FactoryInterface;

abstract class BaseCollection implements IteratorAggregate, Countable, ArrayAccess, JsonSerializable, CollectionInterface
{
    /** @var EntityInterface[] */
    protected array $items = [];

    /**
     * BaseCollection constructor.
     * @param array $data
     * @throws InvalidCollectionDataProvidedException
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $item) {
            if ($this->isEntity($item)) {
                $this->items[$key] = $item;
            } else {
                throw new InvalidCollectionDataProvidedException();
            }
        }
    }

    /**
     * @param EntityInterface $entity
     * @return BaseCollection
     */
    public function add(EntityInterface $entity):CollectionInterface
    {
        $this->items[] = $entity;
        return $this;
    }

    /**
     * @param array $data
     * @param FactoryInterface $factory
     * @return BaseCollection
     * @throws InvalidCollectionDataProvidedException
     * @see buildFromArray
     */
    public function createFromArray(array $data, FactoryInterface $factory): CollectionInterface
    {
        foreach ($data as $item) {
            if (is_array($item)) {
                $this->items[] = $factory->createEntity($item);
            } else {
                throw new InvalidCollectionDataProvidedException();
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function toArray():array
    {
        $result = [];

        foreach ($this->items as $item) {
            $result[] = $item->toArray();
        }

        return $result;
    }

    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Searches for an element. $key will be transformed into getKey method and result of it will be compared to value
     * Comparison is strict
     * @param mixed $key
     * @param bool $value
     * @return mixed
     */
    public function search($key, $value)
    {
        foreach ($this->items as $item) {
            if ($item->{'get' . $key}() === $value) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Remove item from collection
     * @param string $key
     * @param mixed $value
     * @return bool|null
     */
    public function remove($key, $value)
    {
        foreach ($this->items as $index => $item) {
            if ($item->{'get' . $key}() === $value) {
                unset($this->items[$index]);
                return true;
            }
        }
        return null;
    }

    /**
     * @param callable $callback
     * @return $this
     * @throws InvalidCollectionDataProvidedException
     */
    public function map(callable $callback)
    {
        $keys = array_keys($this->items);

        $newItems = [];
        foreach ($this->items as $value) {
            $clonedValue = clone $value;
            $newItems[] = $callback($clonedValue);
        }

        return new static(array_combine($keys, $newItems));
    }

    /**
     * @param callable $callback
     * @return $this
     * @throws InvalidCollectionDataProvidedException
     */
    public function filter(callable $callback)
    {
        $newItems = [];
        foreach ($this->items as $value) {
            $clonedValue = clone $value;
            if ($callback($clonedValue)) {
                $newItems[] = $clonedValue;
            }
        }

        return new static($newItems);
    }

    //phpcs: enable

    /**
     * @return ArrayIterator|Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param mixed $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Get an item at a given offset.
     *
     * @param mixed $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->items[$key];
    }

    /**
     * Set the item at a given offset.
     *
     * @param mixed $key
     * @param mixed $value
     * @return void
     * @throws InvalidCollectionDataProvidedException
     */
    public function offsetSet($key, $value)
    {
        if (!$this->isEntity($value)) {
            throw new InvalidCollectionDataProvidedException();
        }
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }

    /**
     * Unset the item at a given offset.
     *
     * @param string $key
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->items[$key]);
    }

    /**
     * @param $item
     * @return bool
     */
    protected function isEntity($item)
    {
        return ($item instanceof EntityInterface);
    }

    /**
     * @return array|mixed
     */
    // phpcs:disable TymeshiftCodeStandard.Classes.EachMethodMustHaveReturnType
    public function jsonSerialize()
    {
        return $this->toArray();
    }
    //phpcs:enable

    /**
     * Execute a callback over each item.
     *
     * @param  callable  $callback
     * @return $this
     */
    public function each(callable $callback): self
    {
        foreach ($this->items as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }
        return $this;
    }

    /**
     * @param array $data
     * @param FactoryInterfaceAlias $factory
     * @return BaseCollection
     * @throws InvalidCollectionDataProvidedException
     */
    public function buildFromArray(array $data, FactoryInterfaceAlias $factory): self
    {
        foreach ($data as $item) {
            if (is_array($item)) {
                $this->items[] = $factory->build($item);
            } else {
                throw new InvalidCollectionDataProvidedException();
            }
        }

        return $this;
    }

    /**
     * @param mixed $id
     * @return EntityInterface|null
     */
    public function getById($id): ?EntityInterface
    {
        foreach ($this->items as $entity) {
            if ($entity->getId() == $id) {
                return $entity;
            }
        }
        return null;
    }

    /**
     * @param string $property
     * @return array
     */
    public function pluck(string $property): array
    {
        $data = [];
        foreach ($this->items as $entity) {
            $data[] = $entity->{'get' . ucfirst($property)}();
        }
        return $data;
    }

    /**
     * @param string $property
     * @return Collection
     * @throws InvalidCollectionDataProvidedException
     */
    public function getAssoc(string $property = 'id'):Collection
    {
        $items = [];
        foreach ($this->items as $index => $entity) {
            $newKey = $entity->{'get' . ucfirst($property)}();
            $items[$newKey] = $this->items[$index];
        }

        return new static($items);
    }

    /**
     * @return array
     */
    public function getIds(): array
    {
        $result = [];
        foreach ($this->items as $entity) {
            $result[] = $entity->getId();
        }
        return array_unique($result);
    }

    /**
     * @return EntityInterface
     */
    public function last():EntityInterface
    {
        $lastItem = end($this->items);
        reset($this->items);
        return $lastItem;
    }
}