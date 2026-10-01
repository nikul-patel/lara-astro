{{-- Shared by Vimshottari and Yogini dasha sections — both produce the same {lord, start, end, antardashas} shape. --}}
@foreach ($periods as $period)
    <h3>{{ $period['lord'] }} &mdash; {{ $period['start'] }} to {{ $period['end'] }}</h3>
    <table>
        <tr><th>Antardasha</th><th>Start</th><th>End</th></tr>
        @foreach ($period['antardashas'] as $antardasha)
            <tr>
                <td>{{ $antardasha['lord'] }}</td>
                <td>{{ $antardasha['start'] }}</td>
                <td>{{ $antardasha['end'] }}</td>
            </tr>
        @endforeach
    </table>
@endforeach
