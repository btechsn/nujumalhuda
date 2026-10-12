<?php

declare(strict_types=1);

namespace Modules\Core\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\Process;

/**
 * Écrit un instantané SQL. Préfère pg_dump quand il est installé ;
 * sinon parcourt les tables publiques.
 */
final class DatabaseSqlDumper
{
    public function write(string $path): void
    {
        $binary = $this->pgDumpBinary();
        if ($binary !== null) {
            $this->writeWithPgDump($binary, $path);

            return;
        }

        $this->writeFromTables($path);
    }

    private function pgDumpBinary(): ?string
    {
        $configured = config('backup.pg_dump');
        if (is_string($configured) && $configured !== '' && is_file($configured)) {
            return $configured;
        }

        $found = $this->searchPath('pg_dump');

        return $found !== null && is_file($found) ? $found : null;
    }

    private function searchPath(string $name): ?string
    {
        $finder = PHP_OS_FAMILY === 'Windows' ? 'where' : 'which';
        $process = new Process([$finder, $name]);
        $process->run();
        if (!$process->isSuccessful()) {
            return null;
        }

        $line = trim(strtok($process->getOutput(), "\r\n") ?: '');

        return $line !== '' ? $line : null;
    }

    private function writeWithPgDump(string $binary, string $path): void
    {
        $connection = config('database.connections.'.config('database.default'));
        $process = new Process([
            $binary,
            '--host', (string) ($connection['host'] ?? '127.0.0.1'),
            '--port', (string) ($connection['port'] ?? '5432'),
            '--username', (string) ($connection['username'] ?? ''),
            '--dbname', (string) ($connection['database'] ?? ''),
            '--no-owner',
            '--format=plain',
            '--file', $path,
        ], null, ['PGPASSWORD' => (string) ($connection['password'] ?? '')]);
        $process->setTimeout(600);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(trim($process->getErrorOutput()) ?: 'pg_dump a échoué.');
        }
    }

    private function writeFromTables(string $path): void
    {
        $handle = fopen($path, 'wb');
        if ($handle === false) {
            throw new \RuntimeException('Impossible d\'écrire la sauvegarde.');
        }

        fwrite($handle, "-- Nujum Al-Huda\n");
        fwrite($handle, '-- '.now()->toIso8601String()."\n");
        fwrite($handle, "SET client_encoding = 'UTF8';\n");

        $tables = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename");

        foreach ($tables as $table) {
            $name = (string) $table->tablename;
            $columns = Schema::getColumnListing($name);
            if ($columns === []) {
                continue;
            }

            $quotedTable = $this->quoteIdent($name);
            $quotedColumns = implode(', ', array_map($this->quoteIdent(...), $columns));
            fwrite($handle, "\n-- ".$name."\n");

            foreach (DB::table($name)->cursor() as $row) {
                $values = [];
                foreach ($columns as $column) {
                    $values[] = $this->literal($row->{$column} ?? null);
                }
                fwrite($handle, 'INSERT INTO '.$quotedTable.' ('.$quotedColumns.') VALUES ('.implode(', ', $values).");\n");
            }
        }

        fclose($handle);
    }

    private function quoteIdent(string $name): string
    {
        return '"'.str_replace('"', '""', $name).'"';
    }

    private function literal(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        }

        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return "'".str_replace("'", "''", (string) $value)."'";
    }
}
