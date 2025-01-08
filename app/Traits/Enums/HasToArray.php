<?php

namespace App\Traits\Enums;

use BackedEnum;
use ReflectionClass;
use InvalidArgumentException;
use LogicException;

trait HasToArray
{
    private const VALUE_OPTION = 'value';
    private const NAME_OPTION = 'name';

    /**
     * Converts enum cases to an array.
     *
     * @param string $type One of 'value' or 'name'. Default is 'value'.
     * @return array The array of enum cases.
     * @throws LogicException if the calling class is not an enum.
     * @throws InvalidArgumentException if $type is not 'value' or 'name'.
     */
    public static function toArray(string $type = self::VALUE_OPTION): array
    {
        $reflectionClass = new ReflectionClass(static::class);
        if (!static::isEnum($reflectionClass)) {
            throw new LogicException('The calling class (' . static::class . ') must be an enum.');
        }

        if (!in_array($type, [static::VALUE_OPTION, static::NAME_OPTION])) {
            $message = 'Value of $type must be either ' . static::VALUE_OPTION . ' or ' . static::NAME_OPTION . '.';
            throw new InvalidArgumentException($message);
        }

        if ($type === static::VALUE_OPTION && !static::isBackedEnum($reflectionClass)) {
            $message = 'Cannot convert to array with type "' . static::VALUE_OPTION . '" because the enum is not a backed enum.';
            throw new LogicException($message);
        }

        return array_map(
            fn ($case) => $type === self::VALUE_OPTION ? $case->value : $case->name,
            static::cases()
        );
    }

    /**
     * Checks if the calling class is an enum.
     */
    private static function isEnum(ReflectionClass $reflectionClass): bool
    {
        return $reflectionClass->isEnum();
    }

    /**
     * Check if the calling class is a backed enum
     */
    private static function isBackedEnum(ReflectionClass $reflectionClass): bool
    {
        return $reflectionClass->isSubclassOf(BackedEnum::class);
    }
}
