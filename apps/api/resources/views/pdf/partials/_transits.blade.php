<div class="subsection">
    <h2>Transit Today ({{ $generatedAt->toFormattedDateString() }})</h2>
    @foreach ($transits as $planet => $transit)
        <h3>{{ $planet }} is in {{ $transit['sign'] }} ({{ $transit['house_from_moon'] }}{{ match ($transit['house_from_moon']) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' } }} house from your Moon)</h3>
        <p>{{ $transit['text'] }}</p>
    @endforeach
</div>
