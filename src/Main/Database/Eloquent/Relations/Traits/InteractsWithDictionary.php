<?php

namespace Src\Main\Database\Eloquent\Relations\Traits;

use BackedEnum;
use InvalidArgumentException;
use UnitEnum;

trait InteractsWithDictionary
{
    protected function getDictionaryKey(mixed $attribute): mixed
    {
        if (is_object($attribute)) {
            if (method_exists($attribute, '__toString')) {
                return $attribute->__toString();
            }

            if ($attribute instanceof UnitEnum) {
                return $attribute instanceof BackedEnum ? $attribute->value : $attribute->name;
            }

            throw new InvalidArgumentException('Model attribute value is an object but does not have a __toString method.');
        }

        return $attribute;
    }
}
