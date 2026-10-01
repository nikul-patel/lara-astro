<div class="subsection">
    <h2>Transit Today ({{ $generatedAt->toFormattedDateString() }})</h2>
    @foreach ($transits as $planet => $transit)
        <h3>{{ $planet }} is in {{ $transit['sign'] }} ({{ \App\Services\Astrology\Predictions\Ordinal::suffix($transit['house_from_moon']) }} house from your Moon)</h3>
        <p>{{ $transit['text'] }}</p>
    @endforeach
</div>
