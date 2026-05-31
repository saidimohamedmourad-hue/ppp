<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = "schools";

    protected $keyType = "string";

    public $incrementing = false;

    protected $fillable = [
        "name",
        "address",
        "industry",
        "description",
        "website",
        "phone",
        "ownerId",
    ];
    protected $dates = [
        "deleted_at"
    ];

    public function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'ownerId', 'id');
    }

    public function trainingSessions()
    {
        return $this->hasMany(TrainingSession::class, 'schoolId', 'id');
    }

    public function trainingApplications()
    {
        return $this->hasManyThrough(TrainingApplication::class, TrainingSession::class, 'schoolId', 'trainingSessionId', 'id', 'id');
    }
}
