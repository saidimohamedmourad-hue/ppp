<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $user_id
 * @property string $provider           e.g. 'google', 'facebook', 'phone'
 * @property string $provider_user_id   stable identifier on the provider side
 * @property array<string,mixed>|null $meta
 */
class AuthProvider extends Model
{
    use HasUuids;

    protected $table = 'auth_providers';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'provider',
        'provider_user_id',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
