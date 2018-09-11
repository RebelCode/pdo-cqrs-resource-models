<?php

namespace RebelCode\Storage\Resource\Pdo;

use Exception as RootException;
use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;

trait GetPdoValueHashStringCapableTrait
{
    /**
     * Hashes a query value for use in PDO queries when parameter binding.
     *
     * @since [*next-version*]
     *
     * @param int|float|bool|string|Stringable $value The value to hash.
     *
     * @throws InvalidArgumentException If the value is not a valid hash-able value.
     *
     * @return string The string hash.
     */
    protected function _getPdoValueHashString($value)
    {
        try {
            return ':' . hash('crc32b', $this->_normalizeString($value));
        } catch (InvalidArgumentException $invalidArgumentException) {
            throw $this->_createInvalidArgumentException(
                $this->__('Cannot hash given value'),
                null,
                $invalidArgumentException,
                $value
            );
        }
    }

    /**
     * Normalizes a value to its string representation.
     *
     * The values that can be normalized are any scalar values, as well as
     * {@see StringableInterface).
     *
     * @since [*next-version*]
     *
     * @param int|float|bool|string|Stringable $subject The value to normalize to string.
     *
     * @throws InvalidArgumentException If the value cannot be normalized.
     *
     * @return string The string that resulted from normalization.
     */
    abstract protected function _normalizeString($subject);

    /**
     * Creates a new Dhii invalid argument exception.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|null $message  The error message, if any.
     * @param int|null               $code     The error code, if any.
     * @param RootException|null     $previous The inner exception for chaining, if any.
     * @param mixed|null             $argument The invalid argument, if any.
     *
     * @return InvalidArgumentException The new exception.
     */
    abstract protected function _createInvalidArgumentException(
        $message = null,
        $code = null,
        RootException $previous = null,
        $argument = null
    );

    /**
     * Translates a string, and replaces placeholders.
     *
     * @since [*next-version*]
     * @see   sprintf()
     *
     * @param string $string  The format string to translate.
     * @param array  $args    Placeholder values to replace in the string.
     * @param mixed  $context The context for translation.
     *
     * @return string The translated string.
     */
    abstract protected function __($string, $args = [], $context = null);
}
