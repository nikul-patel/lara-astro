<div class="subsection">
    <h2>Prastharashtakvarga (Ashtakvarga Contributor Detail)</h2>
    <p class="muted">Each contributor's individual bindu (1 = grants a point, 0 = doesn't) before they're summed into the Bhinnashtakavarga totals above.</p>
    @foreach ($ashtakvarga['prastharashtakvarga'] as $subject => $contributorBindus)
        <h3>{{ $subject }}</h3>
        <table class="table-dense">
            <tr>
                <th>Contributor</th>
                @foreach (array_keys($ashtakvarga['sarvashtakavarga']) as $sign)
                    <th>{{ \App\Services\Astrology\ZodiacSigns::ABBREVIATIONS[$sign] }}</th>
                @endforeach
            </tr>
            @foreach ($contributorBindus as $contributor => $bindus)
                <tr>
                    <td>{{ $contributor }}</td>
                    @foreach ($bindus as $value)
                        <td>{{ $value }}</td>
                    @endforeach
                </tr>
            @endforeach
        </table>
    @endforeach
</div>
