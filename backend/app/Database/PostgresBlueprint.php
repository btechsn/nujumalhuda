<?php

declare(strict_types=1);

namespace App\Database;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\Grammar;

/**
 * Place les clés primaires et uniques avant les clés étrangères.
 * Laravel ajoute ->primary() en fin de blueprint, après les foreign(),
 * ce qui casse les tables Postgres dont l'identifiant n'est pas un serial.
 */
class PostgresBlueprint extends Blueprint
{
    public function toSql(Connection $connection, Grammar $grammar)
    {
        $this->addImpliedCommands($connection, $grammar);

        $creating = false;
        foreach ($this->commands as $command) {
            if (($command->name ?? null) === 'create') {
                $creating = true;
                break;
            }
        }

        if ($creating) {
            $create = [];
            $keys = [];
            $rest = [];

            foreach ($this->commands as $command) {
                $name = $command->name ?? null;

                if ($name === 'create') {
                    $create[] = $command;
                } elseif (in_array($name, ['primary', 'unique'], true)) {
                    $keys[] = $command;
                } else {
                    $rest[] = $command;
                }
            }

            $this->commands = array_merge($create, $keys, $rest);
        }

        return parent::toSql($connection, $grammar);
    }
}
