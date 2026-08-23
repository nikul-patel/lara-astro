<div class="subsection">
    <h2>Year-Wise Forecast &mdash; {{ $forecast->year }} ({{ ucfirst($forecast->style) }})</h2>

    @if ($forecast->style === 'simplified')
        <table>
            <tr>
                <th style="width: 25%;">Jupiter Transit</th>
                <td>
                    {{ $forecast->result['jupiter_transit']['sign'] }}
                    (house {{ $forecast->result['jupiter_transit']['house_from_ascendant'] }} from Ascendant,
                    house {{ $forecast->result['jupiter_transit']['house_from_moon'] }} from Moon)
                </td>
            </tr>
            <tr>
                <th>Saturn Transit</th>
                <td>
                    {{ $forecast->result['saturn_transit']['sign'] }}
                    (house {{ $forecast->result['saturn_transit']['house_from_ascendant'] }} from Ascendant,
                    house {{ $forecast->result['saturn_transit']['house_from_moon'] }} from Moon)
                </td>
            </tr>
        </table>

        @if (! empty($forecast->result['governing_dasha']))
            <h3>Governing Dasha This Year</h3>
            <table>
                <tr><th>Mahadasha</th><th>Antardasha</th><th>Start</th><th>End</th></tr>
                @foreach ($forecast->result['governing_dasha'] as $period)
                    <tr>
                        <td>{{ $period['mahadasha_lord'] }}</td>
                        <td>{{ $period['antardasha_lord'] }}</td>
                        <td>{{ $period['start'] }}</td>
                        <td>{{ $period['end'] }}</td>
                    </tr>
                @endforeach
            </table>
        @endif
    @else
        <table>
            <tr>
                <th style="width: 25%;">Solar Return</th>
                <td>{{ $forecast->result['solar_return_moment'] }}</td>
            </tr>
            <tr>
                <th>Return Ascendant</th>
                <td>{{ $forecast->result['ascendant']['sign'] }} ({{ $forecast->result['ascendant']['degree'] }})</td>
            </tr>
            <tr>
                <th>Muntha</th>
                <td>{{ $forecast->result['muntha']['sign'] }} (lord {{ $forecast->result['muntha']['lord'] }})</td>
            </tr>
            <tr>
                <th>Varshesh (Year Lord)</th>
                <td>{{ $forecast->result['varshesh']['lord'] }}</td>
            </tr>
        </table>

        <h3>Sahams (Sensitive Points)</h3>
        <table>
            <tr><th>Saham</th><th>Sign</th></tr>
            @foreach ($forecast->result['sahams'] as $saham)
                <tr><td>{{ $saham['name'] }}</td><td>{{ $saham['sign'] }}</td></tr>
            @endforeach
        </table>
    @endif
</div>
