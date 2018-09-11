<?php

namespace RebelCode\Storage\Resource\Pdo;

use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Expression\TermInterface;
use Dhii\Storage\Resource\Sql\OrderInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use OutOfRangeException;
use PDOStatement;
use stdClass;
use Traversable;

/**
 * Common functionality for objects that can delete records in a database via PDO.
 *
 * @since [*next-version*]
 */
trait DeleteCapablePdoTrait
{
    /**
     * Executes a DELETE SQL query, deleting records in the database that satisfy the given condition.
     *
     * @since [*next-version*]
     *
     * @param LogicalExpressionInterface|null            $condition An optional condition which, if specified,
     *                                                              restricts the deletion to records that satisfy this
     *                                                              condition.
     * @param OrderInterface[]|stdClass|Traversable|null $ordering  The ordering, as a list of `OrderInterface`
     *                                                              objects.
     * @param int|float|string|Stringable|null           $limit     The number of records to limit the query to.
     * @param int|float|string|Stringable|null           $offset    The number of records to offset by, zero-based.
     *
     * @return PDOStatement The executed PDO statement.
     */
    protected function _delete(
        LogicalExpressionInterface $condition = null,
        $ordering = null,
        $limit = null,
        $offset = null
    )
    {
        $fieldNames   = $this->_getSqlDeleteFieldNames();
        $valueHashMap = ($condition !== null)
            ? $this->_getPdoExpressionHashMap($condition, $fieldNames)
            : [];

        $query = $this->_buildDeleteSql(
            $this->_getSqlDeleteTable(),
            $condition,
            $ordering,
            $limit,
            $offset,
            $valueHashMap
        );

        $statement = $this->_executePdoQuery($query, array_flip($valueHashMap));

        return $statement;
    }

    /**
     * Builds a DELETE SQL query.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable                 $table        The name of the table to delete from.
     * @param LogicalExpressionInterface|null   $condition    The condition that records must satisfy to be deleted.
     * @param OrderInterface[]|Traversable|null $ordering     The ordering, as a list of OrderInterface instances.
     * @param int|null                          $limit        The number of records to limit the query to.
     * @param int|null                          $offset       The number of records to offset by, zero-based.
     * @param string[]|Stringable[]             $valueHashMap The mapping of term names to their hashes
     *
     * @throws InvalidArgumentException If an argument is invalid.
     * @throws OutOfRangeException      If the limit or offset are invalid numbers.
     *
     * @return string The built DELETE query.
     */
    abstract protected function _buildDeleteSql(
        $table,
        LogicalExpressionInterface $condition = null,
        $ordering = null,
        $limit = null,
        $offset = null,
        array $valueHashMap = []
    );

    /**
     * Retrieves the SQL database table names related to this resource model.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[] A list of SQL database table names.
     */
    abstract protected function _getSqlDeleteTable();

    /**
     * Retrieves the SQL DELETE query column "field" names.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[] A list of field names.
     */
    abstract protected function _getSqlDeleteFieldNames();

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
}
