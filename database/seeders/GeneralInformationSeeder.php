<?php

// File: database/seeders/GeneralInformationSeeder.php
// Description: Seeds CIS-based content for General Academic Information

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GeneralInformation;

class GeneralInformationSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'mission' => 'A university committed to producing leaders by providing a 21st century learning environment through innovations in education, multidisciplinary research, and community and industry partnerships.',
            'vision' => 'A premier national university that develops leaders in the global knowledge economy.',
            'policy' => 'Prompt and regular attendance is required. Total unexcused absences must not exceed 10% of total hours per course per semester. Proper decorum is also expected in all class activities.',
            'exams' => 'Students who miss exams may take special exams for valid reasons, such as medical conditions (with certificate). Other reasons are subject to faculty approval.',
            'dishonesty' => 'Academic dishonesty, including cheating and plagiarism, is a major offense and will be dealt with based on the University’s Student Norms of Conduct.',
            'dropping' => 'Students must officially drop by submitting a form to the Registrar before midterms. Official drops are marked "Dropped"; unofficial drops receive a grade of "5.0".',
            // Merge disability + advising into a single 'other' master section
            'other' => 'Consultation hours and support for students with disabilities or special needs are available. Students are encouraged to disclose any condition to their instructor or the university support services so appropriate academic adjustments can be provided. Please approach your instructor during consultation hours for academic advising, guidance, or support.',
        ];

        foreach ($data as $section => $content) {
            GeneralInformation::updateOrCreate(
                ['section' => $section],
                ['content' => $content]
            );
        }
    }
}


