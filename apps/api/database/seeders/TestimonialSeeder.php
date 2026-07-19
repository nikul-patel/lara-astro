<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

/**
 * Seeds fictional client testimonials for the "Jyotish Path" demo (PRD §5.1).
 * The Testimonial model's `quote` is translatable (en/hi/gu); `name` is not.
 * Idempotent via updateOrCreate keyed on name.
 */
class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $testimonials = [
            [
                'name' => 'Riya M.',
                'rating' => 5,
                'quote' => [
                    'en' => 'The reading was clear and grounded. I left with practical next steps instead of vague predictions.',
                    'hi' => 'परामर्श स्पष्ट और व्यावहारिक था। मुझे अस्पष्ट भविष्यवाणियों के बजाय उपयोगी अगले कदम मिले।',
                    'gu' => 'પરામર્શ સ્પષ્ટ અને વ્યવહારુ હતો. અસ્પષ્ટ આગાહીઓને બદલે ઉપયોગી આગળનાં પગલાં મળ્યાં.',
                ],
            ],
            [
                'name' => 'Kunal P.',
                'rating' => 5,
                'quote' => [
                    'en' => 'Every question was handled patiently, and the career timing guidance helped me plan with confidence.',
                    'hi' => 'हर प्रश्न को धैर्य से समझाया गया और करियर संबंधी मार्गदर्शन ने आत्मविश्वास दिया।',
                    'gu' => 'દરેક પ્રશ્ન ધીરજથી સમજાવવામાં આવ્યો અને કારકિર્દી માર્ગદર્શનથી આત્મવિશ્વાસ મળ્યો.',
                ],
            ],
            [
                'name' => 'Ananya S.',
                'rating' => 5,
                'quote' => [
                    'en' => 'A warm, respectful consultation that made complex chart details easy to understand.',
                    'hi' => 'एक गर्मजोशी भरा और सम्मानजनक परामर्श जिसने जटिल कुंडली विवरणों को समझना आसान बना दिया।',
                    'gu' => 'એક હૂંફાળો અને સન્માનજનક પરામર્શ જેણે જટિલ કુંડળી વિગતોને સમજવી સરળ બનાવી.',
                ],
            ],
        ];

        foreach ($testimonials as $data) {
            Testimonial::updateOrCreate(
                ['name' => $data['name']],
                [
                    'quote' => $data['quote'],
                    'rating' => $data['rating'],
                    'is_active' => true,
                ],
            );
        }
    }
}
