<?php

test('calculating a Sade Sati report returns phase and cycle dates', function () {
    $response = $this->postJson('/api/v1/sade-sati', [
        'name' => 'Ananya Singh', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Jaipur, India',
        'reference_date' => '2024-01-01',
    ]);

    $response->assertOk()->assertJsonStructure([
        'moon_sign', 'phase', 'is_active', 'cycle_start', 'peak_phase_start', 'setting_phase_start', 'cycle_end',
    ]);

    expect($response->json('phase'))->toBeIn(['rising', 'peak', 'setting', 'none']);

    // The four dates must be in chronological order regardless of which
    // phase is currently active (all four are always computed).
    $dates = collect(['cycle_start', 'peak_phase_start', 'setting_phase_start', 'cycle_end'])
        ->map(fn ($key) => $response->json($key));
    expect($dates->sort()->values()->all())->toBe($dates->all());
});

test('reference_date defaults to today when omitted', function () {
    $this->postJson('/api/v1/sade-sati', [
        'name' => 'Test', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Delhi, India',
    ])->assertOk();
});
