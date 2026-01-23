<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkerAvailability extends Model
{
    protected $table = 'worker_availabilities';

    protected $fillable = [
        'worker_id',
        'available_dates',
        'available_times',
        'status'
    ];

    protected $casts = [
        'available_dates' => 'array',
        'available_times' => 'array',
        'status' => 'boolean'
    ];

    /**
     * Relationship: Availability belongs to Worker
     */
    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }

    /**
     * Check if worker is available on given date & time
     */
    public function isAvailable(string $date, string $startTime, string $endTime): bool
    {
        if (!$this->status) {
            return false;
        }

        // Check date exists
        if (!in_array($date, $this->available_dates ?? [])) {
            return false;
        }

        // Check time slot exists
        foreach ($this->available_times ?? [] as $slot) {
            if (
                $slot['start'] <= $startTime &&
                $slot['end'] >= $endTime
            ) {
                return true;
            }
        }

        return false;
    }
}
