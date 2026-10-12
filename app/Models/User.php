<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'guru_id',
        'sekolah_id',
        'kecamatan',
    ];

    // Daftar role yang valid + label tampilannya (Indonesia)
    public const ROLES = [
        'guru'           => 'Guru',
        'kepala_sekolah' => 'Kepala Sekolah',
        'ketua_pgri'     => 'Ketua PGRI Kecamatan',
        'kabid_ptk'      => 'Kabid PTK Kabupaten',
        'kadis'          => 'Kadis Pendidikan',
        'pemda'          => 'Pemerintah Daerah',
    ];

    /**
     * Relasi: user dengan role 'guru' terhubung ke data gurunya.
     */
    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    /**
     * Relasi: user dengan role 'kepala_sekolah' terhubung ke sekolahnya.
     */
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }

    /**
     * Cek apakah user punya salah satu role yang diberikan.
     * Contoh: $user->hasRole('kadis', 'kabid_ptk')
     */
    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /**
     * Label role dalam Bahasa Indonesia untuk ditampilkan.
     */
    public function labelRole(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }

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
}
