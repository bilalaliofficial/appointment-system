<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'patient_id',
        'counsellor_id',
        'appointment_date',
    ];

    /**
     * @param Builder $query
     * @param $date
     * @return Builder
     */
    public function scopeDateAfter(Builder $query, $date): Builder
    {
        return $query->where('appointment_date', '>=', Carbon::parse($date));
    }

    /**
     * @param Builder $query
     * @param $date
     * @return Builder
     */
    public function scopeDateBefore(Builder $query, $date): Builder
    {
        return $query->where('appointment_date', '<=', Carbon::parse($date));
    }
}
