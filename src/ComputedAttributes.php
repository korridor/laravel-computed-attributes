<?php

namespace Korridor\LaravelComputedAttributes;

use Str;

trait ComputedAttributes
{
    /**
     * @param string $attributeName
     * @return mixed
     */
    public function getComputedAttributeValue(string $attributeName)
    {
        $functionName = 'get'.Str::studly($attributeName).'Computed';
        $value = $this->{$functionName}();

        return $value;
    }

    /**
     * @param string $attributeName
     */
    public function setComputedAttributeValue(string $attributeName)
    {
        $computed = $this->getComputedAttributeValue($attributeName);
        $this->{$attributeName} = $computed;
    }

    /**
     * @return array
     */
    public function getComputedAttributeConfiguration()
    {
        if (isset($this->computed)) {
            return $this->computed;
        } else {
            return [];
        }
    }
}
