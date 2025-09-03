<?php

namespace App\Models\Traits;

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
