<div class="subsection">
    <h2>Vimshottari Dasha (Mahadasha &amp; Antardasha)</h2>
    @foreach ($dasha['mahadasha'] as $mahadasha)
        <h3>{{ $mahadasha['lord'] }} Mahadasha &mdash; {{ $mahadasha['start'] }} to {{ $mahadasha['end'] }}</h3>
        <table>
            <tr><th>Antardasha</th><th>Start</th><th>End</th></tr>
            @foreach ($mahadasha['antardashas'] as $antardasha)
                <tr>
                    <td>{{ $antardasha['lord'] }}</td>
                    <td>{{ $antardasha['start'] }}</td>
                    <td>{{ $antardasha['end'] }}</td>
                </tr>
            @endforeach
        </table>
    @endforeach
</div>
