<div class="subsection">
    <h2>Shodashvarga (Divisional Charts)</h2>
    <p class="muted">Each planet's sign in each of the 16 classical divisional charts, counted forward from D1 by its own fixed rule (see docs/API_CONTRACT.md).</p>
    <table>
        <tr>
            <th>Varga</th>
            <th>Lagna</th>
            @foreach (($divisionalCharts[0]['planets'] ?? []) as $planet => $sign)
                <th>{{ $planet }}</th>
            @endforeach
        </tr>
        @foreach ($divisionalCharts as $row)
            <tr>
                <td>{{ $row['varga'] }}</td>
                <td>{{ $row['ascendant'] }}</td>
                @foreach ($row['planets'] as $sign)
                    <td>{{ $sign }}</td>
                @endforeach
            </tr>
        @endforeach
    </table>
</div>
