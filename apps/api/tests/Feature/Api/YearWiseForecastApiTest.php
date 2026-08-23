<?php

use App\Models\BirthChart;
use App\Models\Client;
use App\Models\YearWiseForecast;
use App\Services\Astrology\BirthChartCalculator;

/**
 * TransitForecast/VarshphalCalculator need real houses/planetary_positions
 * data (unlike BirthChartFactory's default empty placeholders), so build a
 * genuinely computed chart for the given birth details.
 */
function realBirthChartResult(string $dob, string $time, string $place): array
{
    return BirthChartCalculator::calculate(['name' => 'Test', 'dob' => $dob, 'time' => $time, 'place' => $place, 'system' => 'vedic']);
}

test('a guest can calculate a simplified year-wise forecast without saving anything', function () {
    $response = $this->postJson('/api/v1/year-wise', [
        'name' => 'Ananya Singh', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Jaipur, India',
        'system' => 'vedic', 'year' => 2026, 'style' => 'simplified',
    ]);

    $response->assertOk()->assertJsonStructure([
        'year', 'jupiter_transit' => ['sign', 'house_from_ascendant', 'house_from_moon'],
        'saturn_transit', 'governing_dasha',
    ]);
    expect(YearWiseForecast::count())->toBe(0);
});

test('a guest can calculate a full varshphal forecast without saving anything', function () {
    $response = $this->postJson('/api/v1/year-wise', [
        'name' => 'Ananya Singh', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Jaipur, India',
        'system' => 'vedic', 'year' => 2026, 'style' => 'varshphal',
    ]);

    $response->assertOk()->assertJsonStructure([
        'year', 'solar_return_moment', 'ascendant', 'planetary_positions', 'houses', 'muntha', 'varshesh', 'sahams',
    ]);
    expect(YearWiseForecast::count())->toBe(0);
});

test('the style defaults to simplified when omitted', function () {
    $response = $this->postJson('/api/v1/year-wise', [
        'name' => 'Test', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Delhi, India',
    ]);

    $response->assertOk()->assertJsonStructure(['jupiter_transit']);
    expect($response->json())->not->toHaveKey('solar_return_moment');
});

test('fetching a saved chart\'s forecast requires authentication', function () {
    $chart = BirthChart::factory()->create();

    $this->getJson("/api/v1/charts/{$chart->id}/year-wise")->assertStatus(401);
});

test('a non-owner cannot fetch another client\'s saved chart forecast', function () {
    $owner = Client::factory()->create();
    $intruder = Client::factory()->create();
    $chart = BirthChart::factory()->create(['client_id' => $owner->id]);
    $token = $intruder->createToken('test')->plainTextToken;

    $this->getJson("/api/v1/charts/{$chart->id}/year-wise", ['Authorization' => "Bearer {$token}"])
        ->assertStatus(403);
});

test('an owner can fetch their saved chart\'s forecast, and a repeat request reuses the cached row', function () {
    $client = Client::factory()->create();
    $chart = BirthChart::factory()->create([
        'client_id' => $client->id,
        'dob' => '1994-05-12',
        'time' => '14:30',
        'place' => 'Jaipur, India',
        'result' => realBirthChartResult('1994-05-12', '14:30', 'Jaipur, India'),
    ]);
    $token = $client->createToken('test')->plainTextToken;

    $first = $this->getJson("/api/v1/charts/{$chart->id}/year-wise?year=2026&style=simplified", ['Authorization' => "Bearer {$token}"]);
    // The first fetch computes and creates the cached row, so Laravel's
    // resource response reports 201 (Illuminate\Http\Resources\Json\JsonResource
    // sets this automatically when the underlying model was just created).
    $first->assertStatus(201)->assertJsonPath('year', 2026)->assertJsonPath('style', 'simplified');

    expect(YearWiseForecast::count())->toBe(1);
    $firstId = $first->json('id');

    $second = $this->getJson("/api/v1/charts/{$chart->id}/year-wise?year=2026&style=simplified", ['Authorization' => "Bearer {$token}"]);

    $second->assertOk()->assertJsonPath('id', $firstId);
    expect(YearWiseForecast::count())->toBe(1);
});

test('different styles for the same chart and year are cached as separate rows', function () {
    $client = Client::factory()->create();
    $chart = BirthChart::factory()->create([
        'client_id' => $client->id,
        'dob' => '1994-05-12',
        'time' => '14:30',
        'place' => 'Jaipur, India',
        'result' => realBirthChartResult('1994-05-12', '14:30', 'Jaipur, India'),
    ]);
    $token = $client->createToken('test')->plainTextToken;

    $this->getJson("/api/v1/charts/{$chart->id}/year-wise?year=2026&style=simplified", ['Authorization' => "Bearer {$token}"])->assertStatus(201);
    $this->getJson("/api/v1/charts/{$chart->id}/year-wise?year=2026&style=varshphal", ['Authorization' => "Bearer {$token}"])->assertStatus(201);

    expect(YearWiseForecast::count())->toBe(2);
});
