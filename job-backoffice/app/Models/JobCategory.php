<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobCategory extends Model
{
    use HasFactory , hasUuids, SoftDeletes;
    
    protected $table="job_categories";
    protected $keytype = "string";

    public $incrementing = false;

    protected $fillable = [
        "name",

    ];
    protected $dates = [
        "deleted_at"
    ];

    public function casts():array
    {
        return [   
                'delete_at' => 'datetime',
        ];

    }
    public function JobVacancies(){
        return $this->hasMany(JobVacancy::class,'jobCategoryId','id');
    }
}
