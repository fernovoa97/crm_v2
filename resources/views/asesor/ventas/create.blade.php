@extends('layouts.app')

@section('title', 'Nueva Venta')
@section('subtitle', ($lead->id ? $lead->razon_social . ' — RUC ' . $lead->ruc : 'Venta directa'))

@section('topbar-actions')
  <a href="{{ route('asesor.leads.index') }}#prospectos" style="
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.5);
    border: 1px solid rgba(255,255,255,0.08);
    padding: 8px 16px; border-radius: 8px;
    font-size: 13px; font-weight: 600;
    text-decoration: none; transition: all 0.2s;
  ">← Volver a prospectos</a>
@endsection

@section('content')

<form method="POST" action="{{ route('asesor.ventas.store') }}" enctype="multipart/form-data" id="formVenta">
@csrf
<input type="hidden" name="lead_id" value="{{ $lead->id ?? '' }}">

@include('asesor.ventas._form', ['venta' => null, 'lead' => $lead])

</form>

@endsection