<?php
namespace App\Core;

class DB
{
    public static function table(string $table): QueryBuilder
    {
        return (new QueryBuilder(Database::pdo()))->table($table);
    }

    public static function query(): QueryBuilder
    {
        return new QueryBuilder(Database::pdo());
    }
}
