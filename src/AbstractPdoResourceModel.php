<?php

namespace RebelCode\Storage\Resource\Pdo;

/**
 * Abstract common functionality for resource models.
 *
 * @since [*next-version*]
 */
abstract class AbstractPdoResourceModel
{
    /*
     * Provides storage functionality for a PDO instance.
     *
     * @since [*next-version*]
     */
    use PdoAwareTrait;

    /*
     * Provides query execution functionality through a PDO instance.
     *
     * @since [*next-version*]
     */
    use ExecutePdoQueryCapableTrait;
}
