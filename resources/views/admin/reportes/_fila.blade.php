@php
    $pctClass = $f['pct_trabajado'] < 40 ? 'low' : ($f['pct_trabajado'] < 70 ? 'mid' : '');
    $extra = $extra ?? null;
@endphp
<tr>
  <td>{{ $f['nombre'] }}</td>
  <td class="num"><b>{{ $f['total'] }}</b></td>
  <td class="num">{{ $f['pendiente'] }}</td>
  <td class="num">
    <span class="rep-pct-bar">
      <span class="rep-pct-track"><span class="rep-pct-fill {{ $pctClass }}" style="width:{{ $f['pct_trabajado'] }}%"></span></span>
      {{ $f['pct_trabajado'] }}%
    </span>
  </td>
  <td class="num">{{ $f['prospecto'] }}</td>
  <td class="num">{{ $f['volver_llamar'] }}</td>
  <td class="num">{{ $f['no_interesado'] }}</td>
  <td class="num">{{ $f['no_califica'] }}</td>
  <td class="num">{{ $f['enviadas'] }} ({{ $f['pct_enviadas'] }}%)</td>
  <td class="num"><b style="color:#5dcaa5;">{{ $f['completadas'] }}</b> ({{ $f['pct_completadas'] }}%)</td>
  <td class="num">
    @if($f['recall_vencido'] > 0 || $f['pendiente_antiguo'] > 0)
      <span class="rep-alert-badge on">⚠ {{ $f['recall_vencido'] + $f['pendiente_antiguo'] }}</span>
    @else
      <span class="rep-alert-badge off">—</span>
    @endif
  </td>
  @if($extra)<td class="num">{!! $extra !!}</td>@endif
</tr>