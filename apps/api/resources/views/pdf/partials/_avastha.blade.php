<div class="subsection">
    <h2>Avastha (Planetary States)</h2>
    <table>
        <tr><th>Planet</th><th>Baladi (Age State)</th><th>Jagrat/Swapna/Sushupta (Consciousness State)</th></tr>
        @foreach ($avastha as $planet => $states)
            <tr>
                <td>{{ $planet }}</td>
                <td>{{ $states['baladi'] }}</td>
                <td>{{ $states['jagrat_swapna_sushupta'] }}</td>
            </tr>
        @endforeach
    </table>
</div>
