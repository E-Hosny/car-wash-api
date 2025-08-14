<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function getValue(string $key, $default = null)
    {
        $record = static::where('key', $key)->first();
        if (!$record) {
            return $default;
        }
        $value = $record->value;
        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }

    public static function setValue(string $key, $value): self
    {
        $stored = is_array($value) || is_object($value)
            ? json_encode($value)
            : (is_bool($value) ? ($value ? '1' : '0') : (string) $value);

        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $stored]
        );
    }
} 