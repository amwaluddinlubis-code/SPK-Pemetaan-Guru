<?php

// Controller Aktivitas: baca jejak audit operasional (append-only).
// Hanya kabid_ptk. Filter: rentang waktu, aktor, tipe aksi, hasil.

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class AktivitasController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->orderByDesc('waktu');

        if ($request->filled('aksi')) {
            $query->where('aksi', $request->aksi);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('hasil')) {
            $query->where('hasil', $request->hasil);
        }
        if ($request->filled('dari')) {
            $query->whereDate('waktu', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $query->whereDate('waktu', '<=', $request->sampai);
        }

        $daftarAksi = ActivityLog::distinct()->orderBy('aksi')->pluck('aksi');
        $daftarUser = User::orderBy('name')->get(['id', 'name', 'role']);

        $log = $query->paginate(20)->withQueryString();

        return view('aktivitas.index', compact('log', 'daftarAksi', 'daftarUser'));
    }
}
