<div class="subsection">
    <h2>Avkahada Chakra</h2>
    <table>
        <tr>
            <th style="width: 25%;">Varna</th>
            <td>{{ $avkahada['varna'] }}</td>
            <th style="width: 25%;">Yoni</th>
            <td>{{ $avkahada['yoni'] }}</td>
        </tr>
        <tr>
            <th>Gana</th>
            <td>{{ $avkahada['gana'] }}</td>
            <th>Vashya</th>
            <td>{{ $avkahada['vashya'] }}</td>
        </tr>
        <tr>
            <th>Nadi</th>
            <td>{{ $avkahada['nadi'] }}</td>
            <th>Lucky Day</th>
            <td>{{ $avkahada['lucky_day'] }}</td>
        </tr>
        <tr>
            <th>Lucky Stone</th>
            <td>{{ $avkahada['lucky_stone'] }}</td>
            <th>Good Planets</th>
            <td>{{ implode(', ', $avkahada['good_planets']) }}</td>
        </tr>
        <tr>
            <th>Friendly Signs</th>
            <td colspan="3">{{ implode(', ', $avkahada['friendly_signs']) }}</td>
        </tr>
    </table>
</div>
