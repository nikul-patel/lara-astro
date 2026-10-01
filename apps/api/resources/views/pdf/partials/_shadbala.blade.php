<div class="subsection">
    <h2>Shadbala &amp; Bhavabala</h2>

    <h3>Shadbala (Six-Fold Planetary Strength, in Rupas)</h3>
    <table class="table-dense">
        <tr><th>Planet</th><th>Sthana</th><th>Dig</th><th>Kala</th><th>Chesta</th><th>Naisargika</th><th>Drik</th><th>Total Rupas</th><th>Required</th><th>Strong?</th></tr>
        @foreach ($shadbala['total_rupas'] as $planet => $totalRupas)
            <tr>
                <td>{{ $planet }}</td>
                <td>{{ number_format($shadbala['sthana']['total'][$planet] / 60, 2) }}</td>
                <td>{{ number_format($shadbala['dig'][$planet] / 60, 2) }}</td>
                <td>{{ number_format($shadbala['kala']['total'][$planet] / 60, 2) }}</td>
                <td>{{ number_format($shadbala['chesta'][$planet] / 60, 2) }}</td>
                <td>{{ number_format($shadbala['naisargika'][$planet] / 60, 2) }}</td>
                <td>{{ number_format($shadbala['drik'][$planet] / 60, 2) }}</td>
                <td>{{ number_format($totalRupas, 2) }}</td>
                <td>{{ number_format($shadbala['minimum_required_rupas'][$planet], 2) }}</td>
                <td>{{ $shadbala['is_strong'][$planet] ? 'Yes' : 'No' }}</td>
            </tr>
        @endforeach
    </table>

    <h3>Bhavabala (House Strength, in Rupas)</h3>
    <table class="table-dense">
        <tr>
            @foreach (array_keys($bhavabala['total_rupas']) as $house)
                <th>House {{ $house }}</th>
            @endforeach
        </tr>
        <tr>
            @foreach ($bhavabala['total_rupas'] as $totalRupas)
                <td>{{ number_format($totalRupas, 2) }}</td>
            @endforeach
        </tr>
    </table>
</div>
