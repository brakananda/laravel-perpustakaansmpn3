<?php
namespace App\Http\Controllers;
use App\Models\Anggota;
use Illuminate\Http\Request;

class AnggotaController extends Controller {
    public function index(Request $request)
    {
        $query = Anggota::query();

        // SEARCH
        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('nis', 'like', "%{$search}%")
                ->orWhere('nama_siswa', 'like', "%{$search}%");

            });
        }

        // FILTER KELAS
        if ($request->filled('kelas')) {

            $query->where('kelas', $request->kelas);

        }

        $anggotas = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('anggota.index', compact('anggotas'));
    }
    public function create() { return view('anggota.create'); }
    public function store(Request $request) {
       $request->validate([
            'nis'            => 'required|numeric|unique:anggotas',
            'nama_siswa'     => 'required|string|max:100',
            'kelas'          => 'required',
            'jenis_kelamin'  => 'required|in:L,P',
            'no_telp'        => 'nullable|numeric',
        ]);
        Anggota::create($request->all());
        return redirect()->route('anggota.index')->with('success','Anggota ditambahkan!');
    }
    public function edit(Anggota $anggota) { return view('anggota.edit', compact('anggota')); }
    public function update(Request $request, Anggota $anggota) {
       $request->validate([
            'nis'            => 'required|numeric|unique:anggotas,nis,' . $anggota->id,
            'nama_siswa'     => 'required|string|max:100',
            'kelas'          => 'required',
            'jenis_kelamin'  => 'required|in:L,P',
            'no_telp'        => 'nullable|numeric',
        ]);
        $anggota->update($request->all());
        return redirect()->route('anggota.index')->with('success','Anggota diperbarui!');
    }
    public function destroy(Anggota $anggota) {
        $anggota->delete();
        return redirect()->route('anggota.index')->with('success','Anggota dihapus!');
    }
    public function show(Anggota $anggota) {}
}