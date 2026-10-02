<div class="subsection">
    <h2>Predictions &amp; Life Reading</h2>

    @if (! empty($detailedReading['overview']))
        <h3>Your Chart at a Glance</h3>
        <div class="card">
            @foreach ($detailedReading['overview']['paragraphs'] as $paragraph)
                <p>{{ $paragraph }}</p>
            @endforeach
        </div>
    @endif

    @if (! empty($predictions['ascendant']))
        <h3>Your Ascendant</h3>
        <p>{{ $predictions['ascendant']['text'] }}</p>
    @endif

    @if (! empty($predictions['nakshatra']))
        <h3>Nakshatra Phal</h3>
        <p>{{ $predictions['nakshatra']['text'] }}</p>
    @endif

    @if (! empty($currentPeriod))
        <h3>Your Current Planetary Period</h3>
        @foreach ($currentPeriod['paragraphs'] as $paragraph)
            <p>{{ $paragraph }}</p>
        @endforeach

        @if (! empty($currentPeriod['upcoming']))
            <h4>Coming Up Next</h4>
            <table>
                <thead>
                    <tr>
                        <th style="width: 22%;">Sub-period</th>
                        <th style="width: 22%;">Dates</th>
                        <th>What it emphasises</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($currentPeriod['upcoming'] as $period)
                        <tr>
                            <td>{{ $period['mahadasha_lord'] }} / {{ $period['lord'] }}</td>
                            <td>{{ \Carbon\CarbonImmutable::parse($period['start'])->format('M Y') }} – {{ \Carbon\CarbonImmutable::parse($period['end'])->format('M Y') }}</td>
                            <td>{{ $period['text'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endif

    <h3>Key Highlights</h3>
    <p><strong>Marriage &amp; Partnerships:</strong> {{ $predictions['marriage']['text'] }}</p>
    <p><strong>Career, Job &amp; Business:</strong> {{ $predictions['career']['text'] }}</p>
    <p><strong>Education:</strong> {{ $predictions['education']['text'] }}</p>
    <p><strong>Foreign Settlement &amp; Overseas:</strong> {{ $predictions['foreign_settlement']['text'] }}</p>
</div>

@if (! empty($detailedReading['life_areas']))
    <div class="subsection">
        <h2>Detailed Life Reading — The Twelve Houses</h2>
        <p class="muted">Each area of life is judged by the classical method (Phaladeepika ch. 15): the condition of its ruling planet, the planets placed in it, the planets aspecting it, its natural significator and its Ashtakvarga strength. House-ruler results follow Brihat Parashara Hora Shastra ch. 24; planet-in-house results follow Phaladeepika ch. 8.</p>

        @foreach ($detailedReading['life_areas'] as $area)
            <div class="life-area">
                <h3>{{ \App\Services\Astrology\Predictions\Ordinal::suffix($area['house']) }} House · {{ $area['title'] }} &nbsp;<span class="badge badge-{{ $area['strength'] }}">{{ $area['strength_label'] }}</span></h3>
                <p class="area-facts">
                    Sign: {{ $area['sign'] }} &nbsp;·&nbsp; Ruler: {{ $area['lord'] }}@if ($area['lord_house']) (in {{ \App\Services\Astrology\Predictions\Ordinal::suffix($area['lord_house']) }})@endif
                    &nbsp;·&nbsp; Planets: {{ $area['occupants'] === [] ? 'none' : implode(', ', $area['occupants']) }}
                    @if ($area['sav_bindus'] !== null) &nbsp;·&nbsp; Ashtakvarga: {{ $area['sav_bindus'] }} bindus @endif
                </p>
                @foreach ($area['sections'] as $section)
                    <p><span class="reading-label">{{ $section['label'] }}</span>&nbsp; {{ $section['text'] }}</p>
                @endforeach
            </div>
        @endforeach
    </div>
@endif

@if (! empty($predictions['dasha_narrative']))
    <div class="subsection">
        <h2>Vimshottari Mahadasha Predictions</h2>
        @foreach ($predictions['dasha_narrative'] as $narrative)
            <h4>{{ $narrative['lord'] }} Mahadasha · {{ \App\Services\Astrology\Predictions\Ordinal::suffix($narrative['house']) }} house</h4>
            <p>{{ $narrative['text'] }}</p>
        @endforeach
    </div>
@endif
