<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description'
    ];

    protected $casts = [
        'value' => 'array'
    ];

    /**
     * Busca uma configuração por chave
     */
    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        // Converte o valor baseado no tipo
        switch ($setting->type) {
            case 'boolean':
                return (bool) $setting->value;
            case 'number':
                return is_numeric($setting->value) ? (float) $setting->value : $default;
            case 'json':
                return is_array($setting->value) ? $setting->value : $default;
            default:
                return $setting->value;
        }
    }


    /**
     * Define uma configuração
     */
    public static function set($key, $value, $type = 'string', $description = null)
    {
        // Converte o valor baseado no tipo
        $convertedValue = $value;
        switch ($type) {
            case 'boolean':
                $convertedValue = (bool) $value;
                break;
            case 'number':
                $convertedValue = is_numeric($value) ? (float) $value : 0;
                break;
            case 'json':
                $convertedValue = is_array($value) ? $value : [$value];
                break;
            default:
                $convertedValue = (string) $value;
        }

        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $convertedValue,
                'type' => $type,
                'description' => $description
            ]
        );
    }

    /**
     * Busca múltiplas configurações por prefixo
     */
    public static function getByPrefix($prefix)
    {
        return static::where('key', 'like', $prefix . '%')
            ->get()
            ->mapWithKeys(function ($setting) {
                return [$setting->key => static::get($setting->key)];
            })
            ->toArray();
    }

    /**
     * Define múltiplas configurações
     */
    public static function setMultiple($settings, $prefix = '')
    {
        foreach ($settings as $key => $value) {
            $fullKey = $prefix ? $prefix . '.' . $key : $key;
            $type = is_bool($value) ? 'boolean' : (is_numeric($value) ? 'number' : 'string');
            static::set($fullKey, $value, $type);
        }
    }
}
