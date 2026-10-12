<?php

// Model ActivityLog: jejak audit operasional, append-only.
// Tidak ada endpoint update/delete; tulis hanya via ActivityLog::catat().

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'waktu',
        'user_id',
        'aksi',
        'entitas_tipe',
        'entitas_id',
        'hasil',
        'perubahan',
        'ip',
    ];

    protected $casts = [
        'waktu' => 'datetime',
        'perubahan' => 'array',
    ];

    // Kunci yang tidak boleh masuk ke kolom perubahan (rahasia/PII berlebih).
    public const REDACTED = [
        'password', 'password_confirmation', 'token', 'remember_token',
        'nip', 'domisili_alamat', 'domisili_lat', 'domisili_lng',
        'alamat', 'email', 'ip', 'cookie', 'authorization',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Catat satu kejadian audit. Gagal pencatatan tidak boleh menggagalkan
     * aksi bisnis utama — panggil di dalam try/catch oleh pemanggil bila perlu.
     */
    public static function catat(
        string $aksi,
        ?Model $entitas = null,
        string $hasil = 'sukses',
        ?array $perubahan = null
    ): void {
        if ($perubahan !== null) {
            $perubahan = static::redaksi($perubahan);
        }

        static::create([
            'waktu' => now(),
            'user_id' => Auth::id(),
            'aksi' => $aksi,
            'entitas_tipe' => $entitas ? class_basename($entitas) : null,
            'entitas_id' => $entitas?->getKey(),
            'hasil' => $hasil,
            'perubahan' => $perubahan,
            // IP tidak disimpan: log operasional tidak memerlukan identitas jaringan pengguna.
            'ip' => null,
        ]);
    }

    /** Redaksi rekursif agar kunci sensitif tidak lolos melalui array bersarang. */
    private static function redaksi(array $data): array
    {
        foreach ($data as $key => $value) {
            if (in_array(strtolower((string) $key), self::REDACTED, true)) {
                $data[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $data[$key] = static::redaksi($value);
            }
        }

        return $data;
    }

    /** Riwayat untuk satu entitas, terbaru dulu. */
    public static function riwayat(Model $entitas)
    {
        return static::where('entitas_tipe', class_basename($entitas))
            ->where('entitas_id', $entitas->getKey())
            ->orderByDesc('waktu')
            ->with('user');
    }

    /**
     * Versi aman: kegagalan pencatatan dilaporkan ke log server tanpa
     * menggagalkan aksi bisnis utama.
     */
    public static function catatAman(
        string $aksi,
        ?Model $entitas = null,
        string $hasil = 'sukses',
        ?array $perubahan = null
    ): void {
        try {
            static::catat($aksi, $entitas, $hasil, $perubahan);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
