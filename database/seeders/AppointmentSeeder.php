<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Location;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patient1 = Patient::create([
            'name' => 'Carlos González',
            'email' => 'carlos85g@gmail.com',
        ]);

        $patient2 = Patient::create([
            'name' => 'Isaac Cruz',
            'email' => 'isaac@elproyecto.com',
        ]);

        $patient3 = Patient::create([
            'name' => 'John',
            'email' => 'john@doe.com',
        ]);

        $location1 = Location::create([
            'name' => 'Clínica 42',
            'city' => 'Puerto Vallarta',
        ]);

        $location2 = Location::create([
            'name' => 'Clínica 170',
            'city' => 'Puerto Vallarta',
        ]);

        $location3 = Location::create([
            'name' => 'Medical City Dallas',
            'city' => 'Dallas',
        ]);

        Appointment::create([
            'patient_id' => $patient3->id,
            'location_id' => $location3->id,
            'date' => now()->toDateTimeString(),
            'status' => 'confirmed',
        ]);

        Appointment::create([
            'patient_id' => $patient1->id,
            'location_id' => $location1->id,
            'date' => now()->toDateTimeString(),
            'status' => 'unconfirmed',
        ]);

        Appointment::create([
            'patient_id' => $patient2->id,
            'location_id' => $location2->id,
            'date' => now()->toDateTimeString(),
            'status' => 'unconfirmed',
        ]);
    }
}
