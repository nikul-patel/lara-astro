<?php

use App\Models\BirthChart;
use App\Models\Client;
use App\Models\YearWiseForecast;
use App\Services\Astrology\BirthChartCalculator;

function realBirthChartForReport(): array
{
    return BirthChartCalculator::calculate([
        'name' => 'Ananya Singh', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Jaipur, India', 'system' => 'vedic',
    ]);
}

test('downloading a report requires authentication', function () {
    $chart = BirthChart::factory()->create(['result' => realBirthChartForReport()]);

    $this->getJson("/api/v1/charts/{$chart->id}/report")->assertStatus(401);
});

test('a non-owner cannot download another client\'s report', function () {
    $owner = Client::factory()->create();
    $intruder = Client::factory()->create();
    $chart = BirthChart::factory()->create(['client_id' => $owner->id, 'result' => realBirthChartForReport()]);
    $token = $intruder->createToken('test')->plainTextToken;

    $this->get("/api/v1/charts/{$chart->id}/report", ['Authorization' => "Bearer {$token}"])
        ->assertStatus(403);
});

test('an owner can download their chart\'s PDF report', function () {
    $client = Client::factory()->create();
    $chart = BirthChart::factory()->create([
        'client_id' => $client->id,
        'name' => 'Ananya Singh',
        'result' => realBirthChartForReport(),
    ]);
    $token = $client->createToken('test')->plainTextToken;

    $response = $this->get("/api/v1/charts/{$chart->id}/report", ['Authorization' => "Bearer {$token}"]);

    $response->assertOk()->assertHeader('content-type', 'application/pdf');
    expect($response->headers->get('content-disposition'))->toContain('ananya-singh');
});

test('a report can embed a year-wise forecast section, computing and caching it', function () {
    $client = Client::factory()->create();
    $chart = BirthChart::factory()->create([
        'client_id' => $client->id,
        'dob' => '1994-05-12',
        'time' => '14:30',
        'place' => 'Jaipur, India',
        'result' => realBirthChartForReport(),
    ]);
    $token = $client->createToken('test')->plainTextToken;

    $response = $this->get(
        "/api/v1/charts/{$chart->id}/report?year_wise_style=simplified&year=2026",
        ['Authorization' => "Bearer {$token}"]
    );

    $response->assertOk()->assertHeader('content-type', 'application/pdf');
    expect(YearWiseForecast::where(['birth_chart_id' => $chart->id, 'year' => 2026, 'style' => 'simplified'])->count())->toBe(1);
});
