<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kundali Report - {{ $chart->name }}</title>
    <style>
        @page {
            margin: 112px 42px 86px 42px;
        }

        * { box-sizing: border-box; }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 10.5px;
            line-height: 1.5;
            color: #2b2420;
        }

        h1, h2, h3, h4 { font-family: "Times New Roman", Times, serif; color: #6b2a12; }

        h1 { font-size: 26px; margin: 0 0 2px 0; letter-spacing: 0.3px; }

        h2 {
            font-size: 14px;
            margin: 0 0 10px 0;
            padding: 7px 12px;
            background: #fbf0dc;
            border-left: 4px solid #a8632a;
            color: #6b2a12;
        }

        h2, h3 { page-break-after: avoid; }

        h3 {
            font-size: 11.5px;
            margin: 14px 0 6px 0;
            padding-bottom: 3px;
            border-bottom: 1px solid #e9d9bb;
            color: #8a4420;
        }

        h4 { font-size: 10.5px; margin: 10px 0 2px 0; color: #8a4420; }

        p { margin: 4px 0; }

        a { color: #8a4420; }

        .muted { color: #7a7067; }

        .eyebrow {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 8.5px;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #a8632a;
            margin: 0 0 4px 0;
        }

        /* Repeating header/footer: dompdf positions position:fixed elements
           relative to the page's content box, not the full page — so the
           header's `top` must equal the NEGATIVE of @page's margin-top (and
           the footer's `bottom` the negative of margin-bottom) to land the
           fixed element inside the reserved margin area instead of
           overlapping the first/last line of body content. */
        .page-header {
            position: fixed;
            top: -112px;
            left: 0;
            right: 0;
            height: 80px;
            padding: 0 0 10px 0;
        }

        .page-header .masthead {
            display: block;
            border-bottom: 2px solid #c6893f;
            padding-bottom: 8px;
        }

        .page-header .site-name {
            font-family: "Times New Roman", Times, serif;
            font-size: 15px;
            font-weight: bold;
            color: #6b2a12;
        }

        .page-header .tagline {
            font-size: 9px;
            color: #8a7c6a;
        }

        .page-header .generated {
            float: right;
            font-size: 8.5px;
            color: #8a7c6a;
            padding-top: 3px;
        }

        .page-footer {
            position: fixed;
            bottom: -86px;
            left: 0;
            right: 0;
            height: 66px;
            padding-top: 8px;
            border-top: 1px solid #e9d9bb;
            font-size: 7.5px;
            color: #8a7c6a;
        }

        /* Title block: page 1 only, sits in normal content flow (not fixed) above Chart Summary. */
        .cover {
            margin-bottom: 4px;
        }

        .cover .subject-meta {
            font-size: 10px;
            color: #7a7067;
            margin: 2px 0 0 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0 12px 0;
            /* "auto" (the default) sizes columns from content width, so a
               wide table (e.g. one column per zodiac sign, 12+ columns)
               can demand more total width than the page has and overflow
               past the right margin instead of shrinking. "fixed" sizes
               columns from the first row only (equal shares, unless a cell
               sets an explicit width, as _chart-summary's label/value table
               already does) and wraps/truncates overflow text instead. */
            table-layout: fixed;
        }

        th, td {
            padding: 5px 8px;
            text-align: left;
            font-size: 9.5px;
            border-bottom: 1px solid #efe4cf;
            overflow-wrap: break-word;
        }

        thead th, tr > th {
            background: #8a4420;
            color: #fdf6ea;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-bottom: none;
        }

        tbody tr:nth-child(even) td { background: #fbf3e6; }

        /* For the handful of very wide tables (one column per zodiac sign,
           12+ columns) — table-layout:fixed above stops them overflowing
           the page, but a smaller font fits full sign names without
           wrapping to 2 lines in every cell. */
        .table-dense th, .table-dense td {
            padding: 4px 3px;
            font-size: 7.5px;
        }

        .subsection {
            margin-bottom: 16px;
        }

        /* Scoped to the row, not the whole (often multi-table) .subsection
           above it: forcing an entire large section to stay atomic pushed it
           to the next page wholesale whenever it didn't quite fit the
           remaining space on the current one, wasting most of that page.
           Avoiding mid-row breaks (and orphaned headings, above) gets the
           same "nothing looks awkwardly split" result without that cost —
           the one tradeoff is a long table can still split across a page
           boundary without its header row repeating, acceptable here since
           no table in this report runs much past a single page. */
        tr { page-break-inside: avoid; }

        .card {
            border: 1px solid #e9d9bb;
            border-radius: 6px;
            background: #fffdf8;
            padding: 10px 14px;
            margin: 6px 0 12px 0;
        }

        .card p { margin: 3px 0 5px 0; }

        /* Detailed life reading: a small uppercase label leads each factor
           paragraph, and a coloured pill carries the house's strength band. */
        .reading-label {
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: #a8632a;
        }

        .life-area { margin: 0 0 14px 0; }

        .life-area h3 { margin-bottom: 4px; }

        .badge {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            padding: 2px 7px;
            border-radius: 8px;
            color: #ffffff;
        }

        .badge-strong { background: #4d7c3a; }
        .badge-moderate { background: #b07a1f; }
        .badge-care { background: #a8432a; }

        .area-facts { font-size: 8.5px; color: #7a7067; margin: 0 0 4px 0; }
    </style>
</head>
<body>
    <div class="page-header">
        <div class="masthead">
            <span class="site-name">{{ $siteName }}</span>
            <span class="tagline">&nbsp;&mdash;&nbsp;Detailed Kundali Report</span>
            <span class="generated">Generated {{ $generatedAt->toFormattedDateString() }}</span>
        </div>
    </div>

    {{-- The "Page N of M" counter is NOT in this HTML — dompdf's CSS counter(pages)
         isn't reliable, so KundaliReportGenerator draws it directly on the canvas
         via page_text()'s {PAGE_NUM}/{PAGE_COUNT} placeholders, positioned to land
         in the blank space this padding-top reserves at the top of the footer. --}}
    <div class="page-footer" style="padding-top: 20px;">
        {{ \App\Services\Astrology\Remedies\RemedyEngine::CAUTION_NOTE }} This report is generated by a self-hosted, low-precision calculation engine — see docs/API_CONTRACT.md for its stated accuracy limitations.
    </div>

    <div class="cover">
        <p class="eyebrow">Detailed Kundali Report</p>
        <h1>{{ $chart->name }}</h1>
        <p class="subject-meta">
            Born {{ $chart->dob->toFormattedDateString() }} at {{ $chart->time }}, {{ $chart->place }}
            &middot; {{ ucfirst($result['system']) }} system
            @if ($result['chart_style']) &middot; {{ str_replace('_', ' ', ucfirst($result['chart_style'])) }} chart @endif
        </p>
    </div>

    @include('pdf.partials._chart-summary', ['result' => $result])

    @if (! empty($result['avkahada']))
        @include('pdf.partials._avkahada', ['avkahada' => $result['avkahada']])
    @endif

    @if (! empty($result['ashtakvarga']))
        @include('pdf.partials._ashtakvarga', ['ashtakvarga' => $result['ashtakvarga']])
    @endif

    @if (! empty($result['ashtakvarga']['prastharashtakvarga']))
        @include('pdf.partials._prastharashtakvarga', ['ashtakvarga' => $result['ashtakvarga']])
    @endif

    @if (! empty($divisionalCharts))
        @include('pdf.partials._divisional-charts', ['divisionalCharts' => $divisionalCharts])
    @endif

    @if (! empty($result['bhava_madhya']))
        @include('pdf.partials._bhava-madhya', ['result' => $result])
    @endif

    @if (! empty($result['dasha']['mahadasha']))
        @include('pdf.partials._dasha-timeline', ['dasha' => $result['dasha']])
    @endif

    @if (! empty($result['dasha']['yogini']))
        @include('pdf.partials._yogini-dasha', ['dasha' => $result['dasha']])
    @endif

    @if (! empty($result['jaimini']))
        @include('pdf.partials._jaimini', ['jaimini' => $result['jaimini']])
    @endif

    @if (! empty($result['kp']))
        @include('pdf.partials._kp', ['kp' => $result['kp']])
    @endif

    @if (! empty($result['shadbala']) && ! empty($result['bhavabala']))
        @include('pdf.partials._shadbala', ['shadbala' => $result['shadbala'], 'bhavabala' => $result['bhavabala']])
    @endif

    @if (! empty($result['avastha']))
        @include('pdf.partials._avastha', ['avastha' => $result['avastha']])
    @endif

    @if (! empty($result['lal_kitab']))
        @include('pdf.partials._lal-kitab', ['lalKitab' => $result['lal_kitab']])
    @endif

    @if (! empty($result['friendship_table']))
        @include('pdf.partials._friendship-table', ['friendshipTable' => $result['friendship_table']])
    @endif

    @if (! empty($result['aspects']))
        @include('pdf.partials._aspects', ['result' => $result])
    @endif

    @if (! empty($result['yogas']))
        @include('pdf.partials._yogas', ['yogas' => $result['yogas']])
    @endif

    @if (! empty($doshas))
        @include('pdf.partials._doshas', ['doshas' => $doshas])
    @endif

    @if (! empty($sadeSati))
        @include('pdf.partials._sade-sati', ['sadeSati' => $sadeSati])
    @endif

    @if (! empty($transits))
        @include('pdf.partials._transits', ['transits' => $transits, 'generatedAt' => $generatedAt])
    @endif

    @if (! empty($result['predictions']))
        @include('pdf.partials._predictions', [
            'predictions' => $result['predictions'],
            'detailedReading' => $detailedReading,
            'currentPeriod' => $currentPeriod,
        ])
    @endif

    @if (! empty($result['remedies']))
        @include('pdf.partials._remedies', ['remedies' => $result['remedies']])
    @endif

    @if ($forecast)
        @include('pdf.partials._year-wise', ['forecast' => $forecast])
    @endif
</body>
</html>
