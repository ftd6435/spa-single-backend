<?php

namespace App\Traits;

trait ResourceHelpers
{
    protected function hasVisibleAttribute(string $attribute): bool
    {
        if (! method_exists($this->resource, 'getAttributes')) {
            return isset($this->{$attribute});
        }

        return array_key_exists($attribute, $this->resource->getAttributes())
            && ! in_array($attribute, $this->resource->getHidden(), true);
    }

    protected function attribute(string $attribute)
    {
        return $this->when(
            $this->hasVisibleAttribute($attribute),
            fn () => $this->{$attribute}
        );
    }

    protected function dateAttribute(string $attribute)
    {
        return $this->when(
            $this->hasVisibleAttribute($attribute),
            fn () => $this->{$attribute}?->format('d-m-Y H:i:s')
        );
    }
}
