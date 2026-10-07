<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lab404\Impersonate\Models\Impersonate;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, Impersonate;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role_utama',
        'avatar',
        'siswa_id',
        'device_id',
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

    // Relationship dengan Siswa (untuk siswa login)
    public function siswa()
    {
        return $this->hasOne(Siswa::class);
    }

    // Relationship dengan GTK (untuk GTK login)
    public function gtk()
    {
        return $this->hasOne(GTK::class);
    }

    // ── Impersonate: hanya developer (email di DEVELOPER_EMAIL) yang boleh impersonate ──

    /**
     * Hanya user dengan email developer yang boleh melakukan impersonate.
     */
    public function canImpersonate(): bool
    {
        $developerEmails = array_map(
            'trim',
            explode(',', env('DEVELOPER_EMAIL', ''))
        );

        return in_array($this->email, array_filter($developerEmails));
    }

    /**
     * Semua user bisa di-impersonate KECALI developer itu sendiri.
     */
    public function canBeImpersonated(): bool
    {
        $developerEmails = array_map(
            'trim',
            explode(',', env('DEVELOPER_EMAIL', ''))
        );

        return ! in_array($this->email, array_filter($developerEmails));
    }
}
