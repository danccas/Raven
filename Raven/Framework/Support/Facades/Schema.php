<?php

namespace Raven\Support\Facades;

/**
 * @method static \Raven\Database\Schema\Builder create(string $table, \Closure $callback)
 * @method static \Raven\Database\Schema\Builder drop(string $table)
 * @method static \Raven\Database\Schema\Builder dropIfExists(string $table)
 * @method static \Raven\Database\Schema\Builder table(string $table, \Closure $callback)
 * @method static \Raven\Database\Schema\Builder rename(string $from, string $to)
 * @method static void defaultStringLength(int $length)
 * @method static bool hasTable(string $table)
 * @method static bool hasColumn(string $table, string $column)
 * @method static bool hasColumns(string $table, array $columns)
 * @method static \Raven\Database\Schema\Builder disableForeignKeyConstraints()
 * @method static \Raven\Database\Schema\Builder enableForeignKeyConstraints()
 * @method static void registerCustomDoctrineType(string $class, string $name, string $type)
 *
 * @see \Raven\Database\Schema\Builder
 */
class Schema extends Facade
{
    /**
     * Get a schema builder instance for a connection.
     *
     * @param  string|null  $name
     * @return \Raven\Database\Schema\Builder
     */
    public static function connection($name)
    {
        return static::$app['db']->connection($name)->getSchemaBuilder();
    }

    /**
     * Get a schema builder instance for the default connection.
     *
     * @return \Raven\Database\Schema\Builder
     */
    protected static function getFacadeAccessor()
    {
        return static::$app['db']->connection()->getSchemaBuilder();
    }
}
