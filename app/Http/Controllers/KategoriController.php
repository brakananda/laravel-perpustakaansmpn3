<?php
namespace App\Http\Controllers;
use App\Models\Kategori;
use Illuminate\Http\Request;

class KategoriController extends Controller {
    public function index() {
        $kategoris = Kategori::withCount('bukus')
            ->withSum('bukus','jumlah_buku')
            ->get();
        return view('kategori.index', compact('kategoris'));
    }
    public function create() { return view('kategori.create'); }
    public function store(Request $request) {
        $request->validate(['nama_kategori' => 'required|unique:kategoris']);
        Kategori::create($request->all());
        return redirect()->route('kategori.index')->with('success','Kategori ditambahkan!');
    }
    public function edit(Kategori $kategori) { return view('kategori.edit', compact('kategori')); }
    public function update(Request $request, Kategori $kategori) {
        $request->validate(['nama_kategori' => 'required|unique:kategoris,nama_kategori,'.$kategori->id]);
        $kategori->update($request->all());
        return redirect()->route('kategori.index')->with('success','Kategori diperbarui!');
    }
    public function destroy(Kategori $kategori) {
        $kategori->delete();
        return redirect()->route('kategori.index')->with('success','Kategori dihapus!');
    }
    public function show(Kategori $kategori) {}
}