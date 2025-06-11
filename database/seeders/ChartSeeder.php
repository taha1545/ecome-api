<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Chart;

class ChartSeeder extends Seeder
{
   
    public function run(): void
    {
       Chart::factory()->count(15)->create();
    }
}
