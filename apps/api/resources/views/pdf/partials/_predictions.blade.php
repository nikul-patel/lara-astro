<div class="subsection">
    <h2>Predictions</h2>

    @if (! empty($predictions['ascendant']))
        <h3>Your Ascendant</h3>
        <p>{{ $predictions['ascendant']['text'] }}</p>
    @endif

    <h3>Marriage &amp; Partnerships</h3>
    <p>{{ $predictions['marriage']['text'] }}</p>

    <h3>Career, Job &amp; Business</h3>
    <p>{{ $predictions['career']['text'] }}</p>

    <h3>Education</h3>
    <p>{{ $predictions['education']['text'] }}</p>

    <h3>Foreign Settlement &amp; Overseas Opportunities</h3>
    <p>{{ $predictions['foreign_settlement']['text'] }}</p>

    @if (! empty($predictions['dasha_narrative']))
        <h3>Vimshottari Mahadasha Predictions</h3>
        @foreach ($predictions['dasha_narrative'] as $narrative)
            <h4 style="font-size: 11px; margin: 8px 0 2px 0; color: #92400e;">{{ $narrative['lord'] }} Mahadasha (house {{ $narrative['house'] }})</h4>
            <p>{{ $narrative['text'] }}</p>
        @endforeach
    @endif
</div>
