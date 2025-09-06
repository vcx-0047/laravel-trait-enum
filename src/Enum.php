<?php


namespace Vcx\Enum;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

trait Enum
{
    /**
     * Check if the enum column is declared in the model
     */
    public static function hasEnumColumn(string $column): bool
    {
        $instance = new static;

        return in_array($column, $instance->enum ?? [], true);
    }

    /**
     * Get ENUM options for a given column
     */
    public static function getEnum(string $column): array
    {
        if (!self::hasEnumColumn($column)) {
            throw new \InvalidArgumentException("Enum '{$column}' is not declared in the model.");
        }

        $instance = new static;
        $table = $instance->getTable();

        $type = DB::selectOne("SHOW COLUMNS FROM {$table} WHERE Field = '{$column}'")->Type;

        preg_match('/^enum\((.*)\)$/', $type, $matches);

        return array_map(fn($value) => trim($value, "'"), explode(',', $matches[1]));
    }

    /**
     * Get ENUMs for multiple columns
     */
    public static function getEnums(?array $columns = null): array
    {
        $instance = new static;
        $columns = $columns ?? ($instance->enum ?? []);

        $result = [];
        foreach ($columns as $col) {
            $result[$col] = self::getEnum($col);
        }

        return $result;
    }

    /**
     * Add a new ENUM option for a given column
     */
    public static function setEnum(string $column, string $new): void
    {
        if (!self::hasEnumColumn($column)) {
            throw new \InvalidArgumentException("Enum '{$column}' is not declared in the model.");
        }

        $instance = new static;
        $table = $instance->getTable();

        $type = DB::selectOne("SHOW COLUMNS FROM {$table} WHERE Field = '{$column}'")->Type;
        preg_match('/^enum\((.*)\)$/', $type, $matches);
        $options = array_map(fn($value) => trim($value, "'"), explode(',', $matches[1]));

        if (in_array($new, $options, true)) {
            throw new \InvalidArgumentException("Value '{$new}' already exists in ENUM column '{$column}'.");
        }

        $options[] = $new;
        $enumString = implode("','", $options);

        DB::statement("ALTER TABLE {$table} MODIFY {$column} ENUM('{$enumString}')");

        return;
    }

    /**
     * Remove an ENUM option for a given column
     */
    public static function removeEnum(string $column, string $value): void
    {
        if (!self::hasEnumColumn($column)) {
            throw new \InvalidArgumentException("Enum '{$column}' is not declared in the model.");
        }

        $instance = new static;
        $table = $instance->getTable();

        $col = DB::selectOne("SHOW FULL COLUMNS FROM {$table} WHERE Field = '{$column}'");

        preg_match('/^enum\((.*)\)$/', $col->Type, $matches);
        $options = array_map(fn($v) => trim($v, "'"), explode(',', $matches[1]));

        if (!in_array($value, $options, true)) {
            throw new \InvalidArgumentException("Value '{$value}' does not exist in ENUM column '{$column}'.");
        }

        $count = DB::table($table)->where($column, $value)->count();
        if ($count > 0) {
            throw new \RuntimeException("Cannot remove '{$value}' because {$count} rows still use it.");
        }

        // Remove the value
        $options = array_diff($options, [$value]);
        $enumString = implode("','", $options);

        // Preserve definition
        $null = $col->Null === 'NO' ? 'NOT NULL' : 'NULL';
        $default = $col->Default !== null ? "DEFAULT '{$col->Default}'" : '';
        $collation = $col->Collation ? "COLLATE {$col->Collation}" : '';
        $extra = $col->Extra ?? '';

        DB::statement("ALTER TABLE {$table} MODIFY {$column} ENUM('{$enumString}') {$collation} {$null} {$default} {$extra}");
    }


    /**
     * Validate ENUM values before saving
     */
    protected static function validateEnum(Model $model): void
    {
        foreach ($model->enum ?? [] as $column) {
            $options = self::getEnum($column);

            if (!in_array($model->{$column}, $options, true)) {
                throw new \InvalidArgumentException(
                    "Invalid value '{$model->{$column}}' for column '{$column}'. Allowed: " . implode(', ', $options)
                );
            }
        }
    }
}
