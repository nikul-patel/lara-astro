<div class="subsection">
    <h2>KP System (Nakshatra Nadi)</h2>

    <h3>Planet Sub-Lords</h3>
    <table>
        <tr><th>Planet</th><th>Nakshatra</th><th>Pada</th><th>Nakshatra Lord</th><th>Sub-Lord</th></tr>
        <tr>
            <td>Ascendant</td>
            <td>{{ $kp['ascendant']['nakshatra'] }}</td>
            <td>{{ $kp['ascendant']['pada'] }}</td>
            <td>{{ $kp['ascendant']['nakshatra_lord'] }}</td>
            <td>{{ $kp['ascendant']['sub_lord'] }}</td>
        </tr>
        @foreach ($kp['sub_lords'] as $planet => $subLord)
            <tr>
                <td>{{ $planet }}</td>
                <td>{{ $subLord['nakshatra'] }}</td>
                <td>{{ $subLord['pada'] }}</td>
                <td>{{ $subLord['nakshatra_lord'] }}</td>
                <td>{{ $subLord['sub_lord'] }}</td>
            </tr>
        @endforeach
    </table>

    <h3>Cuspal Sub-Lords</h3>
    <table>
        <tr><th>House</th><th>Nakshatra</th><th>Pada</th><th>Nakshatra Lord</th><th>Sub-Lord</th></tr>
        @foreach ($kp['cusps'] as $cusp)
            <tr>
                <td>{{ $cusp['house'] }}</td>
                <td>{{ $cusp['nakshatra'] }}</td>
                <td>{{ $cusp['pada'] }}</td>
                <td>{{ $cusp['nakshatra_lord'] }}</td>
                <td>{{ $cusp['sub_lord'] }}</td>
            </tr>
        @endforeach
    </table>

    <h3>Ruling Planets</h3>
    <table>
        <tr><th>Day Lord</th><th>Asc. Sign Lord</th><th>Asc. Star Lord</th><th>Asc. Sub Lord</th><th>Moon Sign Lord</th><th>Moon Star Lord</th><th>Moon Sub Lord</th></tr>
        <tr>
            <td>{{ $kp['ruling_planets']['day_lord'] }}</td>
            <td>{{ $kp['ruling_planets']['ascendant_sign_lord'] }}</td>
            <td>{{ $kp['ruling_planets']['ascendant_star_lord'] }}</td>
            <td>{{ $kp['ruling_planets']['ascendant_sub_lord'] }}</td>
            <td>{{ $kp['ruling_planets']['moon_sign_lord'] }}</td>
            <td>{{ $kp['ruling_planets']['moon_star_lord'] }}</td>
            <td>{{ $kp['ruling_planets']['moon_sub_lord'] }}</td>
        </tr>
    </table>

    <h3>Significators of Houses</h3>
    <p class="muted">Strongest to weakest: planets in the occupant's star, the occupant, planets in the owner's star, the owner.</p>
    <table>
        <tr><th>House</th><th>Owner</th><th>Occupants</th><th>Significators</th></tr>
        @foreach ($kp['house_significators'] as $house => $significators)
            <tr>
                <td>{{ $house }}</td>
                <td>{{ $significators['owner'] }}</td>
                <td>{{ $significators['occupants'] ? implode(', ', $significators['occupants']) : '—' }}</td>
                <td>{{ implode(', ', $significators['combined']) }}</td>
            </tr>
        @endforeach
    </table>

    <h3>Planet Significations</h3>
    <table>
        <tr><th>Planet</th><th>Houses Signified</th></tr>
        @foreach ($kp['planet_significations'] as $planet => $houses)
            <tr>
                <td>{{ $planet }}</td>
                <td>{{ implode(', ', $houses) }}</td>
            </tr>
        @endforeach
    </table>
</div>
