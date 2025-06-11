<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Contact;
use App\Models\Addresse;
use GuzzleHttp\Promise\Create;

class ContactSeeder extends Seeder
{
   
    public function run(): void
    {
       Addresse::factory()->count(30)->Create();
       Contact::factory()->count(20)->create();
    }
}
