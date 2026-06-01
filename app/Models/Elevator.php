<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Elevator extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, Notifiable;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'code',
        'building_id',
        'branch_id',
        'customer_name',
        'customer_phone',
        'province',
        'district',
        'address',
        'manufacturer',
        'model',
        'type',
        'capacity',
        'floors',
        'cycle_days',
        'status',
        'note',
        'map',
        'maintenance_deadline',
        'maintenance_end_date',
        'inspection_date',
    ];

    protected $casts = [
        'maintenance_deadline' => 'date',
        'maintenance_end_date' => 'date',
        'inspection_date'      => 'date',
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function maintenanceChecks()
    {
        return $this->hasMany(MaintenanceCheck::class);
    }

    protected static function booted()
    {
        static::saved(function ($elevator) {
            $parseDays = function($days) {
                if (empty($days)) return [];
                if (is_string($days)) {
                    $days = trim($days);
                    if (str_starts_with($days, '[') && str_ends_with($days, ']')) {
                        return array_map('intval', json_decode($days, true) ?? []);
                    }
                    return array_filter(array_map('intval', explode(',', $days)));
                }
                return array_map('intval', (array)$days);
            };

            $zalo = app(\App\Services\ZaloService::class);
            $maintDays = $parseDays($zalo->readEnvValue('ZALO_MAINTENANCE_DAYS_BEFORE') ?: config('services.zalo.maintenance_days_before'));
            $inspDays = $parseDays($zalo->readEnvValue('ZALO_INSPECTION_DAYS_BEFORE') ?: config('services.zalo.inspection_days_before'));

            if ($elevator->status === 'active' && $elevator->customer_phone) {
                // 1. Kiểm tra nhắc lịch bảo trì
                if ($elevator->isDirty('maintenance_deadline') || $elevator->wasRecentlyCreated || $elevator->isDirty('customer_phone') || $elevator->isDirty('status')) {
                    if ($elevator->maintenance_deadline) {
                        $diffInDays = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($elevator->maintenance_deadline)->startOfDay(), false);
                        if (in_array($diffInDays, $maintDays)) {
                            $maintTemplateId = $zalo->readEnvValue('ZALO_MAINTENANCE_TEMPLATE_ID') ?: config('services.zalo.maintenance_template_id');
                            if ($maintTemplateId) {
                                try {
                                    $elevator->notify(new \App\Notifications\MaintenanceReminderNotification($elevator, $maintTemplateId, 'maintenance'));
                                } catch (\Exception $e) {
                                    \Illuminate\Support\Facades\Log::error("Immediate Zalo Maintenance Notification Error [{$elevator->code}]: " . $e->getMessage());
                                }
                            }
                        }
                    }
                }

                // 2. Kiểm tra nhắc lịch kiểm định
                if ($elevator->isDirty('inspection_date') || $elevator->wasRecentlyCreated || $elevator->isDirty('customer_phone') || $elevator->isDirty('status')) {
                    if ($elevator->inspection_date) {
                        $diffInDays = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($elevator->inspection_date)->startOfDay(), false);
                        if (in_array($diffInDays, $inspDays)) {
                            $inspTemplateId = $zalo->readEnvValue('ZALO_INSPECTION_TEMPLATE_ID') ?: config('services.zalo.inspection_template_id');
                            if ($inspTemplateId) {
                                try {
                                    $elevator->notify(new \App\Notifications\MaintenanceReminderNotification($elevator, $inspTemplateId, 'inspection'));
                                } catch (\Exception $e) {
                                    \Illuminate\Support\Facades\Log::error("Immediate Zalo Inspection Notification Error [{$elevator->code}]: " . $e->getMessage());
                                }
                            }
                        }
                    }
                }
            }
        });
    }

    /**
     * Alias for customer_phone to work with notification channels
     */
    public function getPhoneAttribute()
    {
        return $this->customer_phone;
    }
}
