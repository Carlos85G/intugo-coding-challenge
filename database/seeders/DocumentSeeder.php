<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Document;

class DocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Document::create([
            'name' => 'Draft document',
            'state' => 'draft',
        ]);

        Document::create([
            'name' => 'Submitted document',
            'state' => 'submitted',
        ]);

        Document::create([
            'name' => 'Approved document',
            'state' => 'approved',
        ]);

        Document::create([
            'name' => 'Rejected document',
            'state' => 'rejected',
        ]);
    }
}
