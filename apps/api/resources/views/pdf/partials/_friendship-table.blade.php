@php
    $friendshipPlanets = array_values(array_unique(array_column($friendshipTable, 'from')));
    $friendshipPivot = [];
    foreach ($friendshipTable as $pair) {
        $friendshipPivot[$pair['from']][$pair['to']] = $pair;
    }
@endphp
<div class="subsection">
    <h2>Planetary Friendship Table</h2>

    @foreach (['natural' => 'Natural (Permanent) Friendship', 'temporal' => 'Temporal Friendship', 'combined' => 'Five-Fold (Combined) Friendship'] as $key => $label)
        <h3>{{ $label }}</h3>
        <table>
            <tr>
                <th></th>
                @foreach ($friendshipPlanets as $planet)
                    <th>{{ $planet }}</th>
                @endforeach
            </tr>
            @foreach ($friendshipPlanets as $from)
                <tr>
                    <th>{{ $from }}</th>
                    @foreach ($friendshipPlanets as $to)
                        <td>{{ $from === $to ? '—' : ucfirst(str_replace('_', ' ', $friendshipPivot[$from][$to][$key])) }}</td>
                    @endforeach
                </tr>
            @endforeach
        </table>
    @endforeach
</div>
