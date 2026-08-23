<div class="subsection">
    <h2>Chart Summary</h2>
    <table>
        <tr>
            <th style="width: 25%;">Ascendant</th>
            <td>{{ $result['ascendant']['sign'] }} ({{ $result['ascendant']['degree'] }})</td>
            <th style="width: 25%;">Timezone</th>
            <td>{{ $result['timezone'] }}</td>
        </tr>
        @if (! empty($result['nakshatra']))
            <tr>
                <th>Moon Nakshatra</th>
                <td>{{ $result['nakshatra']['name'] }}, Pada {{ $result['nakshatra']['pada'] }}</td>
                <th>Nakshatra Lord</th>
                <td>{{ $result['nakshatra']['lord'] }}</td>
            </tr>
        @endif
    </table>

    <h3>Planetary Positions</h3>
    <table>
        <tr><th>Planet</th><th>Sign</th><th>Degree</th></tr>
        @foreach ($result['planetary_positions'] as $planet)
            <tr><td>{{ $planet['name'] }}</td><td>{{ $planet['sign'] }}</td><td>{{ $planet['degree'] }}</td></tr>
        @endforeach
    </table>

    <h3>Houses</h3>
    <table>
        <tr><th>House</th><th>Sign</th><th>Planets</th></tr>
        @foreach ($result['houses'] as $house)
            <tr>
                <td>{{ $house['number'] }}</td>
                <td>{{ $house['sign'] }}</td>
                <td>{{ $house['planets'] ? implode(', ', $house['planets']) : '—' }}</td>
            </tr>
        @endforeach
    </table>
</div>
