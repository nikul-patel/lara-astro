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
</div>
