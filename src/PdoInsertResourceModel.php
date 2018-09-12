<?php

namespace RebelCode\Storage\Resource\Pdo;

use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeContainerCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Storage\Resource\InsertCapableInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use PDO;
use RebelCode\Storage\Resource\Sql\BuildInsertSqlCapableTrait;
use RebelCode\Storage\Resource\Sql\BuildSqlRecordValuesCapableTrait;
use RebelCode\Storage\Resource\Sql\EscapeSqlReferenceCapableTrait;
use RebelCode\Storage\Resource\Sql\EscapeSqlReferenceListCapableTrait;
use RebelCode\Storage\Resource\Sql\NormalizeSqlValueCapableTrait;
use RebelCode\Storage\Resource\Sql\SqlColumnNamesAwareTrait;
use RebelCode\Storage\Resource\Sql\SqlFieldColumnMapAwareTrait;
use RebelCode\Storage\Resource\Sql\SqlTableAwareTrait;

/**
 * Concrete implementation of an INSERT resource model for use with a PDO database connection.
 *
 * This generic implementation can be instantiated to INSERT into any table. An optional field-to-column map may be
 * provided which is used to translate consumer-friendly field names in insertion data sets to  their actual column
 * counterpart names.
 *
 * @since [*next-version*]
 */
class PdoInsertResourceModel extends AbstractPdoResourceModel implements InsertCapableInterface
{
    /*
     * Provides PDO SQL INSERT functionality.
     *
     * @since [*next-version*]
     */
    use InsertCapablePdoTrait;

    /*
     * Provides functionality for building INSERT SQL queries.
     *
     * @since [*next-version*]
     */
    use BuildInsertSqlCapableTrait;

    /*
     * Provides functionality for building the VALUES portion for INSERT records.
     *
     * @since [*next-version*]
     */
    use BuildSqlRecordValuesCapableTrait;

    /*
     * Provides normalization functionality of SQL values.
     *
     * @since [*next-version*]
     */
    use NormalizeSqlValueCapableTrait;

    /* @since [*next-version*] */
    use EscapeSqlReferenceListCapableTrait;

    /*
     * Provides SQL reference escaping functionality.
     *
     * @since [*next-version*]
     */
    use EscapeSqlReferenceCapableTrait;

    /*
     * Provides PDO value hash generation functionality.
     *
     * @since [*next-version*]
     */
    use GetPdoValueHashStringCapableTrait;

    /*
     * Provides SQL table list storage functionality.
     *
     * @since [*next-version*]
     */
    use SqlTableAwareTrait;

    /*
     * Provides SQL field-to-column map storage functionality.
     *
     * @since [*next-version*]
     */
    use SqlFieldColumnMapAwareTrait;

    /*
     * Provides SQL column name list storage functionality.
     *
     * @since [*next-version*]
     */
    use SqlColumnNamesAwareTrait;

    /*
     * Provides functionality for reading from any kind of container.
     *
     * @since [*next-version*]
     */
    use ContainerGetCapableTrait;

    /*
     * Provides functionality for checking for key existence in any kind of container.
     *
     * @since [*next-version*]
     */
    use ContainerHasCapableTrait;

    /*
     * Provides functionality for normalizing containers.
     *
     * @since [*next-version*]
     */
    use NormalizeContainerCapableTrait;

    /*
     * Provides string normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeStringCapableTrait;

    /*
     * Provides key normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeKeyCapableTrait;

    /*
     * Provides array normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIterableCapableTrait;

    /*
     * Provides functionality for creating invalid argument exceptions.
     *
     * @since [*next-version*]
     */
    use CreateInvalidArgumentExceptionCapableTrait;

    /*
     * Provides functionality for creating container exceptions.
     *
     * @since [*next-version*]
     */
    use CreateContainerExceptionCapableTrait;

    /*
     * Provides functionality for creating not-found container exceptions.
     *
     * @since [*next-version*]
     */
    use CreateNotFoundExceptionCapableTrait;

    /*
     * Provides functionality for creating out-of-range exceptions.
     *
     * @since [*next-version*]
     */
    use CreateOutOfRangeExceptionCapableTrait;

    /*
     * Provides string translating functionality.
     *
     * @since [*next-version*]
     */
    use StringTranslatingTrait;

    public function __construct(PDO $pdo, $table, $fieldColumnMap)
    {
        $this->_setPdo($pdo);
        $this->_setSqlTable($table);
        $this->_setSqlFieldColumnMap($fieldColumnMap);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function insert($records)
    {
        $statement = $this->_insert($records);

        return [$this->_getPdo()->lastInsertId()];
    }

    /**
     * Retrieves the SQL database table name for use in SQL INSERT queries.
     *
     * @since [*next-version*]
     *
     * @return string|Stringable The table.
     */
    protected function _getSqlInsertTable()
    {
        return $this->_getSqlTable();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getSqlInsertColumnNames()
    {
        return $this->_getSqlColumnNames();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getSqlInsertFieldColumnMap()
    {
        return $this->_getSqlFieldColumnMap();
    }
}
