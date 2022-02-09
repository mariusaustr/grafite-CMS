<?php

namespace Grafite\Cms\Services;

class Normalizer
{
    public function __construct(private ?string $value = null)
    {
    }

    public function __toString(): string
    {
        if (is_null($this->value)) {
            return "";
        }

        return $this->value;
    }

    public function plain(): string
    {
        return strip_tags($this->value);
    }
}
