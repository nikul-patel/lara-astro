<div class="subsection">
    <h2>Planetary Aspects (Western)</h2>
    <table>
        <tr><th>From</th><th>To</th><th>Aspect</th><th>Angle</th><th>Orb</th></tr>
        @foreach ($result['aspects'] as $aspect)
            <tr>
                <td>{{ $aspect['from'] }}</td>
                <td>{{ $aspect['to'] }}</td>
                <td>{{ $aspect['aspect'] }}</td>
                <td>{{ number_format($aspect['angle'], 2) }}&deg;</td>
                <td>{{ number_format($aspect['orb'], 2) }}&deg;</td>
            </tr>
        @endforeach
    </table>

    @if (! empty($result['cuspal_aspects']))
        <h3>Aspects on Bhava Madhya (Cusps)</h3>
        <table>
            <tr><th>Planet</th><th>Cusp</th><th>Aspect</th><th>Angle</th><th>Orb</th></tr>
            @foreach ($result['cuspal_aspects'] as $aspect)
                <tr>
                    <td>{{ $aspect['from'] }}</td>
                    <td>{{ $aspect['to'] }}</td>
                    <td>{{ $aspect['aspect'] }}</td>
                    <td>{{ number_format($aspect['angle'], 2) }}&deg;</td>
                    <td>{{ number_format($aspect['orb'], 2) }}&deg;</td>
                </tr>
            @endforeach
        </table>
    @endif
</div>
