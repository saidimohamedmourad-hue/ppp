<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property string $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $role
 * @property string|null $profile_picture
 * @property string|null $cv_url
 * @property \Illuminate\Support\Carbon|null $last_login_at
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids, SoftDeletes, HasApiTokens;

    protected $keyType = "string";

    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'last_login_at',
        'profile_picture',
        'cv_url',
        'avatar_url',
        'email_verified_at',
    ];

    protected $dates = [
        'deleted_at',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'deleted_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }
    public function resumes()
    {
        return $this->hasMany(Resume::class,'userId','id');
    }
    public function jobApplications (){
        return $this->hasMany(JobApplication::class,'userId','id');
    }
    public function trainingApplications()
    {
        return $this->hasMany(TrainingApplication::class, 'userId', 'id');
    }

    public function company(){
        return $this->hasOne(Company::class,'ownerId','id');
    }

    public function school()
    {
        return $this->hasOne(School::class, 'ownerId', 'id');
    }

    /**
     * Social-auth providers linked to this account (Google, Facebook, Phone…).
     */
    public function authProviders()
    {
        return $this->hasMany(AuthProvider::class, 'user_id', 'id');
    }

    /**
     * Use our French-language reset password notification with an SPA URL.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\ResetPasswordFr($token));
    }
}