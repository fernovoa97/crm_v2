@extends('layouts.app')

@section('title', 'CACs')
@section('subtitle', 'Gestiona las oficinas de atención al cliente')

@section('content')
<div style="background:#15151c;border:1px solid rgba(255,255,255,0.07);border-radius:14px;overflow:hidden;">
  <div style="padding:16px 20px;border-bottom:1px solid rgba(255,255,255,0.07);display:flex;justify-content:space-between;align-items:center;">
    <span style="font-size:14px;font-weight:600;color:#fff;">Oficinas CAC</span>
    <form method="POST" action="{{ route('admin.cacs.import') }}" enctype="multipart/form-data" style="display:flex;gap:8px;align-items:center;">
      @csrf
      <input type="file" name="file" accept=".xlsx,.xls,.csv" required style="font-size:12px;color:rgba(255,255,255,0.5);">
      <button type="submit" style="padding:7px 14px;border-radius:8px;background:#2FCAF5;color:#0f0f13;border:none;font-size:12px;font-weight:600;cursor:pointer;font-family:'Sora',sans-serif;">
        Importar Excel
      </button>
    </form>
  </div>

  <table style="width:100%;border-collapse:collapse;">
    <thead>
      <tr>
        <th style="font-size:11px;color:rgba(255,255,255,0.25);text-transform:uppercase;letter-spacing:.6px;padding:12px 16px;text-align:left;border-bottom:1px solid rgba(255,255,255,0.05);">Nombre</th>
        <th style="font-size:11px;color:rgba(255,255,255,0.25);text-transform:uppercase;letter-spacing:.6px;padding:12px 16px;text-align:left;border-bottom:1px solid rgba(255,255,255,0.05);">Dirección</th>
      </tr>
    </thead>
    <tbody>
      @forelse($cacs as $cac)
      <tr>
        <td style="font-size:13px;color:#fff;padding:12px 16px;border-bottom:1px solid rgba(255,255,255,0.04);">{{ $cac->nombre }}</td>
        <td style="font-size:13px;color:rgba(255,255,255,0.6);padding:12px 16px;border-bottom:1px solid rgba(255,255,255,0.04);">{{ $cac->direccion }}</td>
      </tr>
      @empty
      <tr><td colspan="2" style="text-align:center;padding:40px;color:rgba(255,255,255,0.25);font-size:13px;">No hay CACs registrados. Importa un Excel.</td></tr>
      @endforelse
    </tbody>
  </table>

  <div style="padding:16px 20px;border-top:1px solid rgba(255,255,255,0.05);font-size:12px;color:rgba(255,255,255,0.35);">
    {{ $cacs->links() }}
  </div>
</div>
@endsection