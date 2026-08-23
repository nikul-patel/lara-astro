<div class="subsection">
    <h2>Remedies</h2>
    <table>
        <tr>
            <th>Planet</th><th>Reason</th><th>Gemstone</th><th>Mantra</th><th>Donation</th><th>Fasting Day</th>
        </tr>
        @foreach ($remedies as $remedy)
            <tr>
                <td>{{ $remedy['planet'] }}</td>
                <td>{{ implode(', ', array_map(fn ($r) => str_replace('_', ' ', $r), $remedy['afflictions'])) }}</td>
                <td>{{ $remedy['gemstone'] }}</td>
                <td>{{ $remedy['mantra'] }}</td>
                <td>{{ $remedy['donation'] }}</td>
                <td>{{ $remedy['fasting_day'] }}</td>
            </tr>
        @endforeach
    </table>
    <p class="muted">{{ $remedies[0]['caution_note'] ?? '' }}</p>
</div>
