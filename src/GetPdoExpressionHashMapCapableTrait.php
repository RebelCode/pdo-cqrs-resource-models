<?php

namespace RebelCode\Storage\Resource\Pdo;

use Exception as RootException;
use Dhii\Expression\ExpressionInterface;
use Dhii\Expression\LiteralTermInterface;
use Dhii\Expression\TermInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use OutOfRangeException;

/**
 * Common functionality for objects that can generate an expression value hash map for use in PDO parameter binding.
 *
 * @since [*next-version*]
 */
trait GetPdoExpressionHashMapCapableTrait
{
    /**
     * Retrieves the expression value hash map for a given SQL condition, for use in PDO parameter binding.
     *
     * @since [*next-version*]
     *
     * @param TermInterface         $condition    The condition instance.
     * @param string[]|Stringable[] $ignore       A list of term names to ignore, typically column names.
     * @param array                 $valueHashMap The value hash map reference to write to.
     *
     * @throws OutOfRangeException If the condition contains an invalid value.
     *
     * @return array A map of value names to their respective hashes.
     */
    protected function _getPdoExpressionHashMap(TermInterface $condition, array $ignore = [], array &$valueHashMap = [])
    {
        if ($valueHashMap === null) {
            $valueHashMap = [];
        }

        if ($condition instanceof LiteralTermInterface) {
            $value = $condition->getValue();

            try {
                $value = $this->_normalizeString($value);

                if (!in_array($value, $ignore)) {
                    $valueHashMap[$value] = $this->_getPdoValueHashString($value);
                }
            } catch (InvalidArgumentException $invalidArgumentException) {
                throw $this->_createOutOfRangeException(
                    $this->__('The condition contains an invalid value'),
                    null,
                    $invalidArgumentException,
                    $value
                );
            }
        }

        if ($condition instanceof ExpressionInterface) {
            foreach ($condition->getTerms() as $_idx => $_term) {
                $this->_getPdoExpressionHashMap($_term, $ignore, $valueHashMap);
            }
        }

        return $valueHashMap;
    }

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
    abstract protected function _getPdoValueHashString($value);

    /**
     * Normalizes a value to its string representation.
     *
     * The values that can be normalized are any scalar values, as well as
     * {@see StringableInterface).
     *
     * @since [*next-version*]
     *
     * @param string|int|float|bool|Stringable $subject The value to normalize to string.
     *
     * @throws InvalidArgumentException If the value cannot be normalized.
     *
     * @return string The string that resulted from normalization.
     */
    abstract protected function _normalizeString($subject);

    /**
     * Creates a new Dhii Out Of Range exception.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|int|float|bool|null $message  The message, if any.
     * @param int|float|string|Stringable|null      $code     The numeric error code, if any.
     * @param RootException|null                    $previous The inner exception, if any.
     * @param mixed|null                            $argument The value that is out of range, if any.
     *
     * @return OutOfRangeException The new exception.
     */
    abstract protected function _createOutOfRangeException(
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
