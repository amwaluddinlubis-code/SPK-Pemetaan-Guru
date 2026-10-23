<?php

// Reservasi formasi lintas alur (SPK + usulan memakai perhitungan
// KebutuhanGuru::sisaDenganAlokasi() yang sama): satu slot tidak dapat
// dialokasikan dua kali; persetujuan memvalidasi ulang di dalam
// transaksi; penolakan melepaskan reservasi; persetujuan mengisi
// formasi lewat mutasi guru (target tidak berubah) agar Distribusi
// konsisten lintas alur.

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\HasilSpk;
use App\Models\KebutuhanGuru;
use App\Models\Sekolah;
use App\Models\User;
use App\Models\UsulanPenempatan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FormasiReservasiTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 0;

    private function buatUser(string $role): User
    {
        return User::create([
            'name' => 'User '.$role.' '.++$this->seq,
            'email' => $role.$this->seq.'@test.local',
            'password' => Hash::make('rahasia123'),
            'role' => $role,
        ]);
    }

    private function buatSekolah(float $lng, string $nama): Sekolah
    {
        return Sekolah::create([
            'npsn' => '7'.++$this->seq,
            'nama' => $nama,
            'alamat' => 'Alamat',
            'kecamatan' => 'Panyabungan',
            'kategori_daerah' => 'biasa',
            'jenjang' => 'SMP',
            'lat' => 0.0,
            'lng' => $lng,
            'koordinat_terverifikasi_lat' => 0.0,
            'koordinat_terverifikasi_lng' => $lng,
        ]);
    }

    private function buatGuru(Sekolah $asal, string $nama): Guru
    {
        return Guru::create([
            'nip' => 'NIP'.++$this->seq,
            'nama' => $nama,
            'mapel' => 'Matematika',
            'jenjang' => 'SMP',
            'domisili_alamat' => 'Domisili',
            'kecamatan' => 'Panyabungan',
            'status_pernikahan' => 'belum_kawin',
            'sudah_sertifikasi' => false,
            'sekolah_id' => $asal->id,
            'sekolah_saat_ini' => $asal->nama,
            'domisili_lat' => 0.0,
            'domisili_lng' => 100.0,
        ]);
    }

    /** Sekolah asal + sekolah tujuan dengan tepat 1 formasi Matematika. */
    private function skenarioSatuSlot(): array
    {
        $asal = $this->buatSekolah(100.30, 'SMP ASAL');
        $tujuan = $this->buatSekolah(100.10, 'SMP TUJUAN');
        KebutuhanGuru::create(['sekolah_id' => $tujuan->id, 'mapel' => 'Matematika', 'jumlah' => 1]);

        return [$asal, $tujuan];
    }

    /** Sisa formasi Matematika di sekolah tujuan (perhitungan resmi). */
    private function sisaFormasi(Sekolah $tujuan, ?int $abaikanUsulan = null, ?int $abaikanSpk = null): int
    {
        $sisa = KebutuhanGuru::sisaDenganAlokasi(['Matematika'], $abaikanSpk, $abaikanUsulan);

        return $sisa[$tujuan->id.'|matematika'] ?? 0;
    }

    private function rekomendasiSpk(Guru $guru, Sekolah $asal, Sekolah $tujuan): HasilSpk
    {
        return HasilSpk::create([
            'guru_id' => $guru->id,
            'sekolah_asal_id' => $asal->id,
            'sekolah_tujuan_id' => $tujuan->id,
            'jarak_km' => 10.0,
            'jarak_sekarang_km' => 22.2,
            'hemat_km' => 20.0,
            'status' => 'rekomendasi',
        ]);
    }

    private function usulan(Guru $guru, Sekolah $tujuan, string $status = 'diverifikasi'): UsulanPenempatan
    {
        return UsulanPenempatan::create([
            'guru_id' => $guru->id,
            'sekolah_id' => $tujuan->id,
            'alasan' => 'Dekat dengan domisili keluarga',
            'status' => $status,
            'diajukan_oleh' => $this->buatUser('kabid_ptk')->id,
        ]);
    }

    public function test_usulan_setujui_gagal_bila_slot_direservasi_spk(): void
    {
        $kadis = $this->buatUser('kadis');
        [$asal, $tujuan] = $this->skenarioSatuSlot();
        $guruSpk = $this->buatGuru($asal, 'GURU SPK');
        $guruUsulan = $this->buatGuru($asal, 'GURU USULAN');
        $this->rekomendasiSpk($guruSpk, $asal, $tujuan);
        $usulan = $this->usulan($guruUsulan, $tujuan);

        $this->assertSame(0, $this->sisaFormasi($tujuan, abaikanUsulan: $usulan->id));

        $this->actingAs($kadis)
            ->post(route('usulan.setujui', $usulan), ['catatan' => 'Setuju'])
            ->assertSessionHas('gagal');

        $this->assertSame($asal->id, $guruUsulan->fresh()->sekolah_id);
        $this->assertSame('diverifikasi', $usulan->fresh()->status);
        $this->assertSame(1, (int) KebutuhanGuru::where('sekolah_id', $tujuan->id)->value('jumlah'));
    }

    public function test_usulan_setujui_berhasil_dan_formasi_terisi(): void
    {
        $kadis = $this->buatUser('kadis');
        [$asal, $tujuan] = $this->skenarioSatuSlot();
        $guru = $this->buatGuru($asal, 'GURU USULAN');
        $usulan = $this->usulan($guru, $tujuan);

        $this->actingAs($kadis)
            ->post(route('usulan.setujui', $usulan), ['catatan' => 'Setuju'])
            ->assertSessionHas('sukses');

        $this->assertSame($tujuan->id, $guru->fresh()->sekolah_id);
        // Target kebutuhan TIDAK berubah; formasi terisi karena guru kini
        // tercatat bertugas di tujuan (konsisten dengan persetujuan SPK
        // dan angka Distribusi).
        $this->assertSame(1, (int) KebutuhanGuru::where('sekolah_id', $tujuan->id)->value('jumlah'));
        $this->assertSame(0, $this->sisaFormasi($tujuan));
    }

    public function test_spk_setujui_gagal_bila_slot_direservasi_usulan_aktif(): void
    {
        $kadis = $this->buatUser('kadis');
        [$asal, $tujuan] = $this->skenarioSatuSlot();
        $guruSpk = $this->buatGuru($asal, 'GURU SPK');
        $guruUsulan = $this->buatGuru($asal, 'GURU USULAN');
        $spk = $this->rekomendasiSpk($guruSpk, $asal, $tujuan);
        $this->usulan($guruUsulan, $tujuan, 'diajukan');

        $this->actingAs($kadis)
            ->post(route('spk.setujui', $spk))
            ->assertSessionHas('gagal');

        $this->assertSame($asal->id, $guruSpk->fresh()->sekolah_id);
        $this->assertSame('rekomendasi', $spk->fresh()->status);
    }

    public function test_penolakan_spk_melepaskan_reservasi_untuk_usulan(): void
    {
        $kadis = $this->buatUser('kadis');
        [$asal, $tujuan] = $this->skenarioSatuSlot();
        $guruSpk = $this->buatGuru($asal, 'GURU SPK');
        $guruUsulan = $this->buatGuru($asal, 'GURU USULAN');
        $spk = $this->rekomendasiSpk($guruSpk, $asal, $tujuan);
        $usulan = $this->usulan($guruUsulan, $tujuan);

        // Selama rekomendasi SPK aktif, slot tereservasi penuh.
        $this->assertSame(0, $this->sisaFormasi($tujuan, abaikanUsulan: $usulan->id));

        $this->actingAs($kadis)
            ->post(route('spk.tolak', $spk), ['catatan_admin' => 'Tidak disetujui'])
            ->assertSessionHas('sukses');

        // Reservasi lepas → usulan kini dapat disetujui.
        $this->assertSame(1, $this->sisaFormasi($tujuan, abaikanUsulan: $usulan->id));
        $this->actingAs($kadis)
            ->post(route('usulan.setujui', $usulan), ['catatan' => 'Setuju'])
            ->assertSessionHas('sukses');
        $this->assertSame($tujuan->id, $guruUsulan->fresh()->sekolah_id);
    }

    public function test_pengajuan_usulan_ditolak_bila_formasi_tereservasi_penuh(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        [$asal, $tujuan] = $this->skenarioSatuSlot();
        $guruSpk = $this->buatGuru($asal, 'GURU SPK');
        $guruBaru = $this->buatGuru($asal, 'GURU PENGAJU');
        $this->rekomendasiSpk($guruSpk, $asal, $tujuan);

        $pengaju = User::create([
            'name' => 'Akun Guru Pengaju',
            'email' => 'pengaju@test.local',
            'password' => Hash::make('rahasia123'),
            'role' => 'guru',
            'guru_id' => $guruBaru->id,
        ]);

        $this->actingAs($pengaju)
            ->post(route('usulan.store'), [
                'guru_id' => $guruBaru->id,
                'sekolah_id' => $tujuan->id,
                'alasan' => 'Ingin lebih dekat dengan keluarga',
            ])
            ->assertSessionHasErrors('sekolah_id');

        $this->assertSame(0, UsulanPenempatan::where('guru_id', $guruBaru->id)->count());
    }

    public function test_generate_tidak_menjanjikan_slot_yang_direservasi_usulan(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        [$asal, $tujuan] = $this->skenarioSatuSlot();
        $kandidat = $this->buatGuru($asal, 'GURU KANDIDAT');
        $pemegang = $this->buatGuru($asal, 'GURU PEMEGANG USULAN');
        $this->usulan($pemegang, $tujuan, 'diajukan');

        $this->actingAs($kabid)->post(route('spk.generate'))->assertRedirect();

        $this->assertSame(0, HasilSpk::where('sekolah_tujuan_id', $tujuan->id)->count(),
            'Generate tidak boleh menjanjikan formasi yang sedang tereservasi usulan aktif');
        $this->assertSame(0, HasilSpk::where('guru_id', $kandidat->id)->count());
    }
}
