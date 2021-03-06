<?php

namespace RebelCode\Storage\Resource\Pdo;

use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Expression\TermInterface;
use Dhii\Storage\Resource\Sql\OrderInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;
use PDOStatement;
use stdClass;
use Traversable;

/**
 * Common functionality for objects that can update records in a database using PDO.
 *
 * @since [*next-version*]
 */
trait UpdateCapablePdoTrait
{
    /**
     * Executes an UPDATE SQL query, updating records in the database that satisfy the given condition.
     *
     * @since [*next-version*]
     *
     * @param array|TermInterface[]|Traversable|stdClass $changeSet The change set, mapping field names to their new
     *                                                              values. The values don't have to be all of the same
     *                                                              type.
     * @param LogicalExpressionInterface|null            $condition An optional condition which, if specified, restricts
     *                                                              the affected records to those that satisfy this
     *                                                              condition.
     * @param OrderInterface[]|stdClass|Traversable|null $ordering  The ordering, as a list of `OrderInterface` objects.
     * @param int|float|string|Stringable|null           $limit     The number of records to limit the query to.
     *
     * @return PDOStatement The executed PDO statement.
     */
    protected function _update($changeSet, LogicalExpressionInterface $condition = null, $ordering = null, $limit = null)
    {
        $fields       = array_keys($this->_getSqlUpdateFieldColumnMap());
        $valueHashMap = ($condition !== null)
            ? $this->_getPdoExpressionHashMap($condition, $fields)
            : [];

        $changeSet = $this->_preProcessChangeSet($changeSet, $valueHashMap);

        $query = $this->_buildUpdateSql(
            $this->_getSqlUpdateTable(),
            $changeSet,
            $condition,
            $ordering,
            $limit,
            $valueHashMap
        );

        $statement = $this->_executePdoQuery($query, array_flip($valueHashMap));

        return $statement;
    }

    /**
     * Process the change set to alias field names to column names and populate the value hash map.
     *
     * @since [*next-version*]
     *
     * @param array|TermInterface[]|Traversable $changeSet    The change set, mapping field names to their new values or
     *                                                        value expressions.
     * @param array                             $valueHashMap The value hash map to populate with new hashes.
     *
     * @return array The processed change set.
     */
    protected function _preProcessChangeSet($changeSet, &$valueHashMap = [])
    {
        if ($valueHashMap === null) {
            $valueHashMap = [];
        }

        $newChangeSet = [];
        $fcMap        = $this->_getSqlUpdateFieldColumnMap();

        foreach ($changeSet as $_field => $_value) {
            // If unknown field name, skip
            if (!isset($fcMap[$_field])) {
                continue;
            }
            // Add to new change set, but keyed with column name
            $_column                = $fcMap[$_field];
            $newChangeSet[$_column] = $_value;

            // Get hash for value
            $_hash = ($_value instanceof TermInterface)
                ? $this->_getPdoExpressionHashMap($_value)
                : $this->_getPdoValueHashString($_value);
            $_valueStr = $this->_normalizeString($_value);
            // Add to value hash map
            $valueHashMap[$_valueStr] = $_hash;
        }

        return $newChangeSet;
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
     * Builds a UPDATE SQL query.
     *
     * Consider using a countable argument for the $changeSet parameter for better performance.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable                 $table        The name of the table to insert into.
     * @param array|TermInterface[]|Traversable $changeSet    The change set, mapping field names to their new values
     *                                                        or value expressions.
     * @param LogicalExpressionInterface|null   $condition    Optional WHERE clause condition.
     * @param OrderInterface[]|Traversable|null $ordering     The ordering, as a list of OrderInterface instances.
     * @param int|null                          $limit        The number of records to limit the query to.
     * @param array                             $valueHashMap Optional map of value names and their hashes.
     *
     * @throws InvalidArgumentException If the change set is empty.
     *
     * @return string The built UPDATE query.
     */
    abstract protected function _buildUpdateSql(
        $table,
        $changeSet,
        LogicalExpressionInterface $condition = null,
        $ordering = null,
        $limit = null,
        array $valueHashMap = []
    );

    /**
     * Retrieves the SQL database table names related to this resource model.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[] A list of SQL database table names.
     */
    abstract protected function _getSqlUpdateTable();

    /**
     * Retrieves the fields-to-columns mapping for use in UPDATE SQL queries.
     *
     * @since [*next-version*]
     *
     * @return array A map containing the field names as keys and the matching column names as values.
     */
    abstract protected function _getSqlUpdateFieldColumnMap();

    /**
     * Retrieves the expression value hash map for a given SQL condition, for use in PDO parameter binding.
     *
     * @since [*next-version*]
     *
     * @param TermInterface         $condition    The condition instance.
     * @param string[]|Stringable[] $ignore       A list of term names to ignore, typically column names.
     * @param array                 $valueHashMap The value hash map reference to write to.
     *
     * @return array A map of value names to their respective hashes.
     */
    abstract protected function _getPdoExpressionHashMap(
        TermInterface $condition,
        array $ignore = [],
        array &$valueHashMap = []
    );

    /**
     * Executes a given SQL query using PDO.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $query     The query to invoke.
     * @param array             $inputArgs The input arguments to use when executing the query.
     *
     * @return PDOStatement The executed statement.
     */
    abstract protected function _executePdoQuery($query, array $inputArgs = []);

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
