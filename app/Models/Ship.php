<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ship extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_number',
        'registration_date',
        'expiration_date',
        'status',
        'name',
        'hull_number',
        'usage',
        'operation_area',
        'crew_size',
        'main_occupation',
        'secondary_occupation',
        'owner_name',
        'owner_id_card',
        'owner_phone',
        'province_id',
        'ward_id',
        'address',
        'user_id',
        'gross_tonnage',
        'deadweight',
        'length_design',
        'width_design',
        'length_max',
        'width_max',
        'depth_max',
        'draft',
        'hull_material',
        'build_year',
        'build_place',
        'engine_mark',
        'engine_number',
        'engine_hp',
        'engine_kw',
        'sub_engine_hp',
        'sub_engine_kw',
        'sub_engine_mark',
        'sub_engine_number',
        'technical_safety_number',
        'technical_safety_date',
        'record_number',
        'record_date',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'expiration_date' => 'date',
        'technical_safety_date' => 'date',
        'record_date' => 'date',
        'engine_hp' => 'array',
        'engine_kw' => 'array',
        'engine_mark' => 'array',
        'engine_number' => 'array',
        'sub_engine_hp' => 'array',
        'sub_engine_kw' => 'array',
        'sub_engine_mark' => 'array',
        'sub_engine_number' => 'array',
    ];

    public function setEngineHpAttribute($value)
    {
        $array = is_string($value) ? json_decode($value, true) : $value;
        if (!is_array($array)) {
            $this->attributes['engine_hp'] = json_encode([]);
            return;
        }
        $formatted = array_map(function($val) {
            $str = number_format((float)$val, 2, '.', '');
            return strpos($str, '.') !== false ? rtrim(rtrim($str, '0'), '.') : $str;
        }, $array);
        // We set the raw encoded JSON to bypass the array cast's standard behavior which might introduce float imprecision
        $this->attributes['engine_hp'] = json_encode($formatted);
    }

    public function setEngineKwAttribute($value)
    {
        $array = is_string($value) ? json_decode($value, true) : $value;
        if (!is_array($array)) {
            $this->attributes['engine_kw'] = json_encode([]);
            return;
        }
        $formatted = array_map(function($val) {
            $str = number_format((float)$val, 2, '.', '');
            return strpos($str, '.') !== false ? rtrim(rtrim($str, '0'), '.') : $str;
        }, $array);
        $this->attributes['engine_kw'] = json_encode($formatted);
    }

    public function setSubEngineHpAttribute($value)
    {
        $array = is_string($value) ? json_decode($value, true) : $value;
        if (!is_array($array)) {
            $this->attributes['sub_engine_hp'] = json_encode([]);
            return;
        }
        $formatted = array_map(function($val) {
            $str = number_format((float)$val, 2, '.', '');
            return strpos($str, '.') !== false ? rtrim(rtrim($str, '0'), '.') : $str;
        }, $array);
        $this->attributes['sub_engine_hp'] = json_encode($formatted);
    }

    public function setSubEngineKwAttribute($value)
    {
        $array = is_string($value) ? json_decode($value, true) : $value;
        if (!is_array($array)) {
            $this->attributes['sub_engine_kw'] = json_encode([]);
            return;
        }
        $formatted = array_map(function($val) {
            $str = number_format((float)$val, 2, '.', '');
            return strpos($str, '.') !== false ? rtrim(rtrim($str, '0'), '.') : $str;
        }, $array);
        $this->attributes['sub_engine_kw'] = json_encode($formatted);
    }

    public function setEngineMarkAttribute($value)
    {
        $array = is_string($value) ? json_decode($value, true) : $value;
        if (!is_array($array)) {
            // Support old varchar value: wrap in array
            $str = is_string($value) ? $value : '';
            $this->attributes['engine_mark'] = json_encode($str !== '' ? [$str] : []);
            return;
        }
        $this->attributes['engine_mark'] = json_encode(array_values($array));
    }

    public function setEngineNumberAttribute($value)
    {
        $array = is_string($value) ? json_decode($value, true) : $value;
        if (!is_array($array)) {
            $str = is_string($value) ? $value : '';
            $this->attributes['engine_number'] = json_encode($str !== '' ? [$str] : []);
            return;
        }
        $this->attributes['engine_number'] = json_encode(array_values($array));
    }

    public function setSubEngineMarkAttribute($value)
    {
        $array = is_string($value) ? json_decode($value, true) : $value;
        if (!is_array($array)) {
            $this->attributes['sub_engine_mark'] = json_encode([]);
            return;
        }
        $this->attributes['sub_engine_mark'] = json_encode(array_values($array));
    }

    public function setSubEngineNumberAttribute($value)
    {
        $array = is_string($value) ? json_decode($value, true) : $value;
        if (!is_array($array)) {
            $this->attributes['sub_engine_number'] = json_encode([]);
            return;
        }
        $this->attributes['sub_engine_number'] = json_encode(array_values($array));
    }

    /**

     * Get the total engine HP sum
     */
    public function getTotalEngineHpAttribute()
    {
        return collect($this->engine_hp)->sum(fn($val) => (float) $val);
    }

    /**
     * Get the total engine KW sum
     */
    public function getTotalEngineKwAttribute()
    {
        return collect($this->engine_kw)->sum(fn($val) => (float) $val);
    }

    /**
     * Get the total sub engine HP sum
     */
    public function getTotalSubEngineHpAttribute()
    {
        return collect($this->sub_engine_hp)->sum(fn($val) => (float) $val);
    }

    /**
     * Get the total sub engine KW sum
     */
    public function getTotalSubEngineKwAttribute()
    {
        return collect($this->sub_engine_kw)->sum(fn($val) => (float) $val);
    }

    /**
     * Get the inspections for this ship.
     */
    public function inspections()
    {
        return $this->hasMany(Inspection::class);
    }

    /**
     * Get the proposals for this ship.
     */
    public function proposals()
    {
        return $this->hasMany(Proposal::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Staff members assigned to manage this ship
     */
    public function managers()
    {
        return $this->belongsToMany(User::class, 'ship_user')
                    ->withPivot('assigned_at');
    }
}
