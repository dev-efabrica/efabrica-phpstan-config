<?php

declare(strict_types = 1);

namespace Efabrica\PHPStanConfig\Rule\NotCMS;

use PHPStan\Reflection\PropertyReflection;
use PHPStan\Rules\Properties\ReadWritePropertiesExtension;
use PHPStan\Type\ObjectType;
use function strpos;

class PluginPropertiesExtension implements ReadWritePropertiesExtension
{
    public function isAlwaysRead(PropertyReflection $property, string $propertyName): bool
    {
        return false;
    }

    public function isAlwaysWritten(PropertyReflection $property, string $propertyName): bool
    {
        return $this->isInitialized($property, $propertyName);
    }

    public function isInitialized(PropertyReflection $property, string $propertyName): bool
    {
        $propertyClass = $property->getDeclaringClass()->getName();
        $basePluginControlClass = '\Efabrica\Cms\Core\Plugin\BasePluginControl';
        if (!(new ObjectType($basePluginControlClass))->isSuperTypeOf(new ObjectType($propertyClass))->yes()) {
            return false;
        }

        return !$property->isPublic() && strpos($property->getDocComment() ?? '', '@plugin-init ') !== false;
    }
}