<div class="subsection">
    <h2>Lal Kitab Chart</h2>
    <p class="muted">A fixed Aries-based chart (house N always has sign N) — distinct from the whole-sign chart above, which starts counting from the Ascendant.</p>
    <table>
        <tr><th>House</th><th>Sign</th><th>Planets</th></tr>
        @foreach ($lalKitab['houses'] as $house)
            <tr>
                <td>{{ $house['number'] }}</td>
                <td>{{ $house['sign'] }}</td>
                <td>{{ $house['planets'] ? implode(', ', $house['planets']) : '—' }}</td>
            </tr>
        @endforeach
    </table>
    <p>
        Ascendant falls in house {{ $lalKitab['ascendant_house'] }}.
        @if ($lalKitab['empty_houses'])
            Empty houses: {{ implode(', ', $lalKitab['empty_houses']) }}.
        @endif
    </p>

    @php $puccaGharPlanets = array_keys(array_filter($lalKitab['pucca_ghar'])); @endphp
    @if ($puccaGharPlanets)
        <p><strong>Pucca Ghar (own-sign placements):</strong> {{ implode(', ', $puccaGharPlanets) }}.</p>
    @endif
</div>
