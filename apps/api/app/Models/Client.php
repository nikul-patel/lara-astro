<?php

namespace App\Models;

use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Public-site contact/account. Every booking or enrollment creates or
 * reuses a Client (per docs/API_CONTRACT.md); password is null until they
 * register a real account. Deliberately separate from App\Models\User,
 * which is the admin-panel (TailAdmin) auth model.
 */
class Client extends Authenticatable
{
    /** @use HasFactory<ClientFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function birthCharts(): HasMany
    {
        return $this->hasMany(BirthChart::class);
    }
}
