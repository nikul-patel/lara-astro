<?php

namespace App\Services\Astrology;

/**
 * Resolves a free-text birth place to coordinates, timezone, and region —
 * needed for the chart's house calculation (Houses::ascendant()) and for
 * RegionRecommendation.
 *
 * This build environment has no route to a live geocoding API (PRD §9
 * lists "Geocoding/timezone lookup" as a third-party integration to wire
 * up per-deployment) and no IANA timezone-boundary dataset bundled, so
 * this ships a small offline gazetteer instead: the state/city examples
 * named in PRD §8's region table, plus enough other major Indian and
 * international cities to make the demo useful. Unmatched places fall back
 * to Delhi/Asia-Kolkata (documented via `matched: false` on the result) —
 * swapping in a real geocoding provider only means replacing this class.
 */
class PlaceLookup
{
    /**
     * @var list<array{names: list<string>, latitude: float, longitude: float, timezone: string, state: ?string, country: string}>
     */
    private const GAZETTEER = [
        // North Indian states (PRD §8: recommend Vedic, North Indian style)
        ['names' => ['delhi', 'new delhi'], 'latitude' => 28.6139, 'longitude' => 77.2090, 'timezone' => 'Asia/Kolkata', 'state' => 'Delhi', 'country' => 'India'],
        ['names' => ['lucknow'], 'latitude' => 26.8467, 'longitude' => 80.9462, 'timezone' => 'Asia/Kolkata', 'state' => 'Uttar Pradesh', 'country' => 'India'],
        ['names' => ['kanpur'], 'latitude' => 26.4499, 'longitude' => 80.3319, 'timezone' => 'Asia/Kolkata', 'state' => 'Uttar Pradesh', 'country' => 'India'],
        ['names' => ['varanasi'], 'latitude' => 25.3176, 'longitude' => 82.9739, 'timezone' => 'Asia/Kolkata', 'state' => 'Uttar Pradesh', 'country' => 'India'],
        ['names' => ['amritsar'], 'latitude' => 31.6340, 'longitude' => 74.8723, 'timezone' => 'Asia/Kolkata', 'state' => 'Punjab', 'country' => 'India'],
        ['names' => ['ludhiana'], 'latitude' => 30.9010, 'longitude' => 75.8573, 'timezone' => 'Asia/Kolkata', 'state' => 'Punjab', 'country' => 'India'],
        ['names' => ['gurugram', 'gurgaon'], 'latitude' => 28.4595, 'longitude' => 77.0266, 'timezone' => 'Asia/Kolkata', 'state' => 'Haryana', 'country' => 'India'],
        ['names' => ['faridabad'], 'latitude' => 28.4089, 'longitude' => 77.3178, 'timezone' => 'Asia/Kolkata', 'state' => 'Haryana', 'country' => 'India'],
        ['names' => ['jaipur'], 'latitude' => 26.9124, 'longitude' => 75.7873, 'timezone' => 'Asia/Kolkata', 'state' => 'Rajasthan', 'country' => 'India'],
        ['names' => ['jodhpur'], 'latitude' => 26.2389, 'longitude' => 73.0243, 'timezone' => 'Asia/Kolkata', 'state' => 'Rajasthan', 'country' => 'India'],
        ['names' => ['udaipur'], 'latitude' => 24.5854, 'longitude' => 73.7125, 'timezone' => 'Asia/Kolkata', 'state' => 'Rajasthan', 'country' => 'India'],
        ['names' => ['bhopal'], 'latitude' => 23.2599, 'longitude' => 77.4126, 'timezone' => 'Asia/Kolkata', 'state' => 'Madhya Pradesh', 'country' => 'India'],
        ['names' => ['indore'], 'latitude' => 22.7196, 'longitude' => 75.8577, 'timezone' => 'Asia/Kolkata', 'state' => 'Madhya Pradesh', 'country' => 'India'],

        // South Indian states (PRD §8: recommend Vedic, South Indian style)
        ['names' => ['chennai', 'madras'], 'latitude' => 13.0827, 'longitude' => 80.2707, 'timezone' => 'Asia/Kolkata', 'state' => 'Tamil Nadu', 'country' => 'India'],
        ['names' => ['coimbatore'], 'latitude' => 11.0168, 'longitude' => 76.9558, 'timezone' => 'Asia/Kolkata', 'state' => 'Tamil Nadu', 'country' => 'India'],
        ['names' => ['madurai'], 'latitude' => 9.9252, 'longitude' => 78.1198, 'timezone' => 'Asia/Kolkata', 'state' => 'Tamil Nadu', 'country' => 'India'],
        ['names' => ['bengaluru', 'bangalore'], 'latitude' => 12.9716, 'longitude' => 77.5946, 'timezone' => 'Asia/Kolkata', 'state' => 'Karnataka', 'country' => 'India'],
        ['names' => ['mysuru', 'mysore'], 'latitude' => 12.2958, 'longitude' => 76.6394, 'timezone' => 'Asia/Kolkata', 'state' => 'Karnataka', 'country' => 'India'],
        ['names' => ['visakhapatnam', 'vizag'], 'latitude' => 17.6868, 'longitude' => 83.2185, 'timezone' => 'Asia/Kolkata', 'state' => 'Andhra Pradesh', 'country' => 'India'],
        ['names' => ['vijayawada'], 'latitude' => 16.5062, 'longitude' => 80.6480, 'timezone' => 'Asia/Kolkata', 'state' => 'Andhra Pradesh', 'country' => 'India'],
        ['names' => ['hyderabad'], 'latitude' => 17.3850, 'longitude' => 78.4867, 'timezone' => 'Asia/Kolkata', 'state' => 'Telangana', 'country' => 'India'],
        ['names' => ['kochi', 'cochin'], 'latitude' => 9.9312, 'longitude' => 76.2673, 'timezone' => 'Asia/Kolkata', 'state' => 'Kerala', 'country' => 'India'],
        ['names' => ['thiruvananthapuram', 'trivandrum'], 'latitude' => 8.5241, 'longitude' => 76.9366, 'timezone' => 'Asia/Kolkata', 'state' => 'Kerala', 'country' => 'India'],

        // East Indian states (PRD §8: recommend Vedic, East Indian style)
        ['names' => ['kolkata', 'calcutta'], 'latitude' => 22.5726, 'longitude' => 88.3639, 'timezone' => 'Asia/Kolkata', 'state' => 'West Bengal', 'country' => 'India'],
        ['names' => ['howrah'], 'latitude' => 22.5958, 'longitude' => 88.2636, 'timezone' => 'Asia/Kolkata', 'state' => 'West Bengal', 'country' => 'India'],
        ['names' => ['bhubaneswar'], 'latitude' => 20.2961, 'longitude' => 85.8245, 'timezone' => 'Asia/Kolkata', 'state' => 'Odisha', 'country' => 'India'],
        ['names' => ['cuttack'], 'latitude' => 20.4625, 'longitude' => 85.8830, 'timezone' => 'Asia/Kolkata', 'state' => 'Odisha', 'country' => 'India'],
        ['names' => ['guwahati'], 'latitude' => 26.1445, 'longitude' => 91.7362, 'timezone' => 'Asia/Kolkata', 'state' => 'Assam', 'country' => 'India'],

        // Other major Indian cities (no explicit PRD region rule — default recommendation applies)
        ['names' => ['mumbai', 'bombay'], 'latitude' => 19.0760, 'longitude' => 72.8777, 'timezone' => 'Asia/Kolkata', 'state' => 'Maharashtra', 'country' => 'India'],
        ['names' => ['pune'], 'latitude' => 18.5204, 'longitude' => 73.8567, 'timezone' => 'Asia/Kolkata', 'state' => 'Maharashtra', 'country' => 'India'],
        ['names' => ['ahmedabad'], 'latitude' => 23.0225, 'longitude' => 72.5714, 'timezone' => 'Asia/Kolkata', 'state' => 'Gujarat', 'country' => 'India'],
        ['names' => ['surat'], 'latitude' => 21.1702, 'longitude' => 72.8311, 'timezone' => 'Asia/Kolkata', 'state' => 'Gujarat', 'country' => 'India'],
        ['names' => ['patna'], 'latitude' => 25.5941, 'longitude' => 85.1376, 'timezone' => 'Asia/Kolkata', 'state' => 'Bihar', 'country' => 'India'],
        ['names' => ['chandigarh'], 'latitude' => 30.7333, 'longitude' => 76.7794, 'timezone' => 'Asia/Kolkata', 'state' => 'Chandigarh', 'country' => 'India'],

        // Major international cities (NRI/international audience, PRD §8 point 1's "outside India" case)
        ['names' => ['new york', 'nyc'], 'latitude' => 40.7128, 'longitude' => -74.0060, 'timezone' => 'America/New_York', 'state' => null, 'country' => 'United States'],
        ['names' => ['los angeles'], 'latitude' => 34.0522, 'longitude' => -118.2437, 'timezone' => 'America/Los_Angeles', 'state' => null, 'country' => 'United States'],
        ['names' => ['toronto'], 'latitude' => 43.6532, 'longitude' => -79.3832, 'timezone' => 'America/Toronto', 'state' => null, 'country' => 'Canada'],
        ['names' => ['london'], 'latitude' => 51.5074, 'longitude' => -0.1278, 'timezone' => 'Europe/London', 'state' => null, 'country' => 'United Kingdom'],
        ['names' => ['dubai'], 'latitude' => 25.2048, 'longitude' => 55.2708, 'timezone' => 'Asia/Dubai', 'state' => null, 'country' => 'United Arab Emirates'],
        ['names' => ['singapore'], 'latitude' => 1.3521, 'longitude' => 103.8198, 'timezone' => 'Asia/Singapore', 'state' => null, 'country' => 'Singapore'],
        ['names' => ['sydney'], 'latitude' => -33.8688, 'longitude' => 151.2093, 'timezone' => 'Australia/Sydney', 'state' => null, 'country' => 'Australia'],
    ];

    /**
     * @return array{latitude: float, longitude: float, timezone: string, state: ?string, country: string, matched: bool}
     */
    public static function resolve(string $place): array
    {
        $normalized = self::normalize($place);

        foreach (self::GAZETTEER as $entry) {
            foreach ($entry['names'] as $name) {
                if ($normalized === $name || str_contains($normalized, $name)) {
                    return [
                        'latitude' => $entry['latitude'],
                        'longitude' => $entry['longitude'],
                        'timezone' => $entry['timezone'],
                        'state' => $entry['state'],
                        'country' => $entry['country'],
                        'matched' => true,
                    ];
                }
            }
        }

        return [
            'latitude' => 28.6139,
            'longitude' => 77.2090,
            'timezone' => 'Asia/Kolkata',
            'state' => 'Delhi',
            'country' => 'India',
            'matched' => false,
        ];
    }

    private static function normalize(string $place): string
    {
        return trim(strtolower(preg_replace('/[^a-z\s]/i', ' ', $place) ?? ''));
    }
}
