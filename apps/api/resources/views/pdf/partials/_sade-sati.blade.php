<div class="subsection">
    <h2>Sade Sati</h2>
    <p>
        Moon sign: <strong>{{ $sadeSati['moon_sign'] }}</strong>.
        @if ($sadeSati['is_active'])
            Currently in the <strong>{{ ucfirst($sadeSati['phase']) }}</strong> phase (cycle {{ $sadeSati['cycle_start'] }} to {{ $sadeSati['cycle_end'] }}).
        @else
            Not currently active as of {{ $sadeSati['reference_date'] }}.
        @endif
    </p>

    <h3>Lifetime Sade Sati Timeline</h3>
    <table>
        <tr><th>Phase</th><th>Sign</th><th>Start</th><th>End</th></tr>
        @foreach ($sadeSati['lifetime']['sade_sati'] as $interval)
            <tr>
                <td>{{ ucfirst($interval['phase']) }}</td>
                <td>{{ $interval['sign'] }}</td>
                <td>{{ $interval['start'] }}</td>
                <td>{{ $interval['end'] }}</td>
            </tr>
        @endforeach
    </table>

    @if (! empty($sadeSati['lifetime']['panoti']))
        <h3>Panoti (4th/8th from Moon) Periods</h3>
        <table>
            <tr><th>Type</th><th>Sign</th><th>Start</th><th>End</th></tr>
            @foreach ($sadeSati['lifetime']['panoti'] as $interval)
                <tr>
                    <td>{{ $interval['type'] === 'fourth_from_moon' ? '4th from Moon' : '8th from Moon' }}</td>
                    <td>{{ $interval['sign'] }}</td>
                    <td>{{ $interval['start'] }}</td>
                    <td>{{ $interval['end'] }}</td>
                </tr>
            @endforeach
        </table>
    @endif
</div>
