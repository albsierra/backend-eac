<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
        ];
    }

    public function userRoles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
                    ->withPivot('ecosistema_laboral_id')
                    ->withTimestamps();
    }

    // Ecosistemas en los que está matriculado (como estudiante)
    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class, 'estudiante_id');
    }

    public function ecosistemasMatriculado(): BelongsToMany
    {
        return $this->belongsToMany(
            EcosistemaLaboral::class,
            'matriculas',
            'estudiante_id'
        )->withTimestamps();
    }

    // Perfiles de habilitación del estudiante
    public function perfilesHabilitacion(): HasMany
    {
        return $this->hasMany(PerfilHabilitacion::class, 'estudiante_id');
    }

    public function perfilEn(EcosistemaLaboral $ecosistema): ?PerfilHabilitacion
    {
        return $this->perfilesHabilitacion()
                    ->where('ecosistema_laboral_id', $ecosistema->id)
                    ->first();
    }

    // Método helper que consulta la relación roles y devuelve true/false
    public function hasRole(string $role): bool
    {
        // Se usa la relación 'userRoles' definida en el modelo User
        return $this->userRoles()->where('name', $role)->exists();
    }
}
