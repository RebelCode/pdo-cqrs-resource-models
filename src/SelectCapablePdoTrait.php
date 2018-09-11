<?php

namespace RebelCode\Storage\Resource\Pdo;

use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Expression\TermInterface;
use Dhii\Storage\Resource\Sql\OrderInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use OutOfRangeException;
use PDO;
use PDOStatement;
use Psr\Container\ContainerInterface;
use stdClass;
use Traversable;

/**
 * Common functionality for objects that can retrieve records from a database using PDO.
 *
 * @since [*next-version*]
 */
trait SelectCapablePdoTrait
{
    /**
     * Retrieves records from storage.
     *
     * @since [*next-version*]
     * @see   ContainerInterface
     *
     * @param LogicalExpressionInterface|null            $condition An optional condition which, if given, restricts
     *                                                              the result set to records that satisfy this
     *                                                              condition.
     * @param OrderInterface[]|stdClass|Traversable|null $ordering  The ordering, as a list of `OrderInterface` objects.
     * @param int|float|string|Stringable|null           $limit     The number of records to limit the query to.
     * @param int|float|string|Stringable|null           $offset    The number of records to offset by, zero-based.
     *
     * @return ContainerInterface[]|Traversable A list of containers, each containing the data for a single record.
     */
    protected function _select(
        LogicalExpressionInterface $condition = null,
        $ordering = null,
        $limit = null,
        $offset = null
    )
    {
        $fields       = $this->_getSqlSelectFieldNames();
        $valueHashMap = ($condition !== null)
            ? $this->_getPdoExpressionHashMap($condition, $fields)
            : [];

        $query = $this->_buildSelectSql(
            $this->_getSqlSelectColumns(),
            $this->_getSqlSelectTables(),
            $this->_getSqlSelectJoinConditions(),
            $condition,
            $ordering,
            $limit,
            $offset,
            $valueHashMap
        );

        return $this->_executePdoQuery($query, array_flip($valueHashMap))->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Builds a SELECT SQL query.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable        $columns  The columns, as a map of aliases (as keys) mapping to
     *                                                    column names, expressions or entity field instances.
     * @param array|stdClass|Traversable        $tables   A mapping of tables aliases (keys) to their real names.
     * @param array|Traversable                 $joins    A list of JOIN logical expressions, keyed by table name.
     * @param LogicalExpressionInterface|null   $where    The WHERE logical expression condition.
     * @param OrderInterface[]|Traversable|null $ordering The ordering, as a list of OrderInterface instances.
     * @param int|null                          $limit    The number of records to limit the query to.
     * @param int|null                          $offset   The number of records to offset by, zero-based.
     * @param array                             $hashmap  Optional map of value names and their hashes.
     *
     * @throws InvalidArgumentException If an argument is invalid.
     * @throws OutOfRangeException      If the limit or offset are invalid numbers.
     *
     * @return string The built SQL query string.
     */
    abstract protected function _buildSelectSql(
        $columns,
        $tables,
        $joins = [],
        LogicalExpressionInterface $where = null,
        $ordering = null,
        $limit = null,
        $offset = null,
        array $hashmap = []
    );

    /**
     * Retrieves the SQL database table names used in SQL SELECT queries.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[] A list of SQL database table names.
     */
    abstract protected function _getSqlSelectTables();

    /**
     * Retrieves the names of the columns used in SQL SELECT queries.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[] A list of column names.
     */
    abstract protected function _getSqlSelectColumns();

    /**
     * Retrieves the SQL SELECT query column "field" names.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[] A list of field names.
     */
    abstract protected function _getSqlSelectFieldNames();

    /**
     * Retrieves the JOIN conditions used in SQL SELECT queries.
     *
     * @since [*next-version*]
     *
     * @return LogicalExpressionInterface[] An assoc. array of logical expressions, keyed by the joined table name.
     */
    abstract protected function _getSqlSelectJoinConditions();

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
