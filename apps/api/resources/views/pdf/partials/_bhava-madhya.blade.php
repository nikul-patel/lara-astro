<div class="subsection">
    <h2>Bhava Madhya (Chalit / Placidus Cusps)</h2>
    <p class="muted">Real house-cusp boundaries, distinct from the whole-sign houses above — a planet near a sign boundary can fall in a different house here than in the whole-sign chart.</p>
    <table>
        <tr><th>House</th><th>Sign</th><th>Degree</th><th>Planets</th></tr>
        @foreach ($result['bhava_madhya'] as $cusp)
            <tr>
                <td>{{ $cusp['house'] }}</td>
                <td>{{ $cusp['sign'] }}</td>
                <td>{{ $cusp['degree'] }}</td>
                <td>{{ $cusp['planets'] ? implode(', ', $cusp['planets']) : '—' }}</td>
            </tr>
        @endforeach
    </table>
</div>
