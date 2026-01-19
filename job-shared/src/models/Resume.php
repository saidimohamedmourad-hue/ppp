<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resume extends Model
{
    use HasFactory,HasUuids, SoftDeletes;

    protected $tableq = "resumes";

    protected $keytype = "string";

    public $incrementing = false;

    protected $fillable = [ 
        "filename",
        "fileUri",
        "contactDetails",
        "education",
        "summary",
        "skills",
        "experience",
        "userId",
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
  public function user (){
    return $this->belongsTo(User::class,'userId','id');

  }
  public function jobApllications (){

    return $this->hasMany(JobApplication::class,'resumeId','id');
  }
}
