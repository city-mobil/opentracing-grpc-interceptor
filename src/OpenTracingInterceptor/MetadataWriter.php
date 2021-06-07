<?php

declare(strict_types=1);

namespace OpenTracingInterceptor;

use ArrayAccess;

class MetadataWriter implements ArrayAccess
{
    private $metadata;

    /**
     * MetadataWriter constructor.
     * @param array $metadata
     */
    public function __construct(array &$metadata)
    {
        $this->metadata = &$metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->metadata[] = [$value];
        } elseif (array_key_exists($offset, $this->metadata)) {
            array_push($this->metadata[$offset], $value);
        } else {
            $this->metadata[$offset] = [$value];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset): bool
    {
        return isset($this->metadata[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset): void
    {
        unset($this->metadata[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->metadata[$offset] ?? null;
    }
}
