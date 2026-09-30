<?php

declare(strict_types=1);

namespace App\Database;

use Illuminate\Database\PostgresConnection as BasePostgresConnection;
use Illuminate\Database\Schema\PostgresBuilder;

class PostgresConnection extends BasePostgresConnection
{
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        $builder = new PostgresBuilder($this);
        $builder->blueprintResolver(function ($table, $callback, $prefix) {
            return new PostgresBlueprint($table, $callback, $prefix);
        });

        return $builder;
    }
}
