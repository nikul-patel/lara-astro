<div class="subsection">
    <h2>Yogas Detected</h2>
    <table>
        <tr><th style="width: 22%;">Yoga</th><th>Description</th></tr>
        @foreach ($yogas as $yoga)
            <tr>
                <td>{{ $yoga['name'] }}</td>
                <td>{{ $yoga['description'] }}</td>
            </tr>
        @endforeach
    </table>
</div>
