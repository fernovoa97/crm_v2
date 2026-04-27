<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'supervisor_id',
        'status',
        'contract_start',
        'contract_end',
        'salary',
        'mobility_bonus',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'contract_start'    => 'date',
            'contract_end'      => 'date',
            'salary'            => 'decimal:2',
            'mobility_bonus'    => 'decimal:2',
        ];
    }

    // Un usuario pertenece a un supervisor
    public function supervisor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    // Un usuario tiene muchos subordinados
    public function subordinates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class, 'supervisor_id');
    }

    // Helpers de rol
    public function isAdmin(): bool        { return $this->role === 'admin'; }
    public function isJefe(): bool         { return $this->role === 'jefe'; }
    public function isSupervisor(): bool   { return $this->role === 'supervisor'; }
    public function isAsesor(): bool       { return $this->role === 'asesor'; }
    public function isMesaControl(): bool  { return $this->role === 'mesa_control'; }
}