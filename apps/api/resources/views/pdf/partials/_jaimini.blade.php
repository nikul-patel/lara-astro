<div class="subsection">
    <h2>Jaimini System</h2>
    <table>
        <tr>
            <th style="width: 25%;">Atmakaraka</th>
            <td>{{ $jaimini['atmakaraka'] }}</td>
            <th style="width: 25%;">Karakamsa</th>
            <td>{{ $jaimini['karakamsa'] }}</td>
        </tr>
        <tr>
            <th>Swamsa</th>
            <td colspan="3">{{ $jaimini['swamsa'] }}</td>
        </tr>
    </table>

    <h3>Char Dasha</h3>
    <table>
        <tr><th>Sign</th><th>Years</th><th>Start</th><th>End</th></tr>
        @foreach ($jaimini['char_dasha'] as $period)
            <tr>
                <td>{{ $period['sign'] }}</td>
                <td>{{ $period['years'] }}</td>
                <td>{{ $period['start'] }}</td>
                <td>{{ $period['end'] }}</td>
            </tr>
        @endforeach
    </table>
</div>
