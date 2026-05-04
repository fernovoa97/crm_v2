<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\CacImport;
use App\Models\Cac;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CacController extends Controller
{
    public function index()
    {
        if (!auth()->user()->isAdmin()) abort(403);
        $cacs = Cac::orderBy('nombre')->paginate(50);
        return view('admin.cacs.index', compact('cacs'));
    }

    public function import(Request $request)
    {
        if (!auth()->user()->isAdmin()) abort(403);

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        Excel::import(new CacImport(), $request->file('file'));

        return redirect()->route('admin.cacs.index')->with('success', 'CACs importados correctamente.');
    }

    public function search(Request $request)
    {
        $cacs = Cac::where('nombre', 'like', '%' . $request->q . '%')
                   ->orWhere('direccion', 'like', '%' . $request->q . '%')
                   ->limit(10)
                   ->get(['id', 'nombre', 'direccion']);

        return response()->json($cacs);
    }
}