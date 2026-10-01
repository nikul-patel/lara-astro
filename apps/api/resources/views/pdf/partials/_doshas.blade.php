<div class="subsection">
    <h2>Dosha Analysis</h2>

    <h3>Manglik (Mangal) Dosha</h3>
    <p><strong>{{ $doshas['manglik']['is_manglik'] ? 'Manglik' : 'Not Manglik' }}</strong>
        @if ($doshas['manglik']['cancelled']) &mdash; cancelled ({{ $doshas['manglik']['cancellation_reason'] }}) @endif
    </p>
    <p>{{ $doshas['manglik']['description'] }}</p>

    <h3>Kaal Sarp Dosha</h3>
    <p><strong>{{ $doshas['kaal_sarp']['present'] ? 'Present' : 'Not Present' }}</strong>
        @if ($doshas['kaal_sarp']['present']) ({{ str_replace('_', ' ', ucfirst($doshas['kaal_sarp']['type'])) }}) @endif
    </p>
    <p>{{ $doshas['kaal_sarp']['description'] }}</p>
</div>
