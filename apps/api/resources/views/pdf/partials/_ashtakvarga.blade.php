<div class="subsection">
    <h2>Ashtakvarga</h2>

    <h3>Sarvashtakavarga (Combined)</h3>
    <table class="table-dense">
        <tr>
            @foreach ($ashtakvarga['sarvashtakavarga'] as $sign => $total)
                <th>{{ \App\Services\Astrology\ZodiacSigns::ABBREVIATIONS[$sign] }}</th>
            @endforeach
        </tr>
        <tr>
            @foreach ($ashtakvarga['sarvashtakavarga'] as $sign => $total)
                <td>{{ $total }}</td>
            @endforeach
        </tr>
    </table>

    <h3>Bhinnashtakavarga (Per Planet)</h3>
    <table class="table-dense">
        <tr>
            <th>Planet</th>
            @foreach (array_keys($ashtakvarga['sarvashtakavarga']) as $sign)
                <th>{{ \App\Services\Astrology\ZodiacSigns::ABBREVIATIONS[$sign] }}</th>
            @endforeach
        </tr>
        @foreach ($ashtakvarga['bhinnashtakavarga'] as $planet => $signTotals)
            <tr>
                <td>{{ $planet }}</td>
                @foreach ($signTotals as $total)
                    <td>{{ $total }}</td>
                @endforeach
            </tr>
        @endforeach
    </table>
</div>
