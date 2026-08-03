@extends('layouts.app')

@section('title', 'Editar Venta')
@section('subtitle', $venta->razon_social . ' — ' . ($venta->estado === 'rechazada' ? 'Corregir y reenviar' : 'Editar venta'))

@section('topbar-actions')
  <a href="{{ route('asesor.ventas.show', $venta) }}" style="
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.5);
    border: 1px solid rgba(255,255,255,0.08);
    padding: 8px 16px; border-radius: 8px;
    font-size: 13px; font-weight: 600;
    text-decoration: none;
  ">← Volver al detalle</a>
@endsection

@section('content')

{{-- Banner motivo rechazo --}}
@if($venta->estado === 'rechazada' && $venta->motivo_rechazo)
  <div style="background:rgba(255,80,80,0.08);border:1px solid rgba(255,80,80,0.2);
              border-radius:10px;padding:14px 18px;margin-bottom:20px;">
    <div style="font-size:12px;font-weight:600;color:#ff9090;margin-bottom:4px;">
      ❌ Motivo de rechazo — corrige los datos indicados y reenvía
    </div>
    <div style="font-size:13px;color:rgba(255,255,255,0.65);line-height:1.5;">
      {{ $venta->motivo_rechazo }}
    </div>
  </div>
@endif

{{-- El formulario de edición es idéntico al de create, pero apunta a PUT --}}
<form method="POST" action="{{ route('asesor.ventas.update', $venta) }}"
      enctype="multipart/form-data" id="formVenta">
  @csrf
  @method('PUT')

  {{-- Incluir el mismo partial del form de creación, pasando $venta para pre-cargar --}}
  @include('asesor.ventas._form', ['venta' => $venta, 'lead' => $venta->lead])

</form>

@endsection