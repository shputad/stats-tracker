<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\NetworkChannel;

class NetworkChannelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        NetworkChannel::create([
            'name' => 'Propellerads',
            'website' => 'https://propellerads.com/',
            'has_api' => true,
        ]);

        NetworkChannel::create([
            'name' => 'PopAds',
            'website' => 'https://www.popads.net',
            'has_api' => true,
        ]);

        NetworkChannel::create([
            'name' => 'Galaksion',
            'website' => 'https://galaksion.com',
            'has_api' => false,
        ]);

        NetworkChannel::create([
            'name' => 'Bidvertiser',
            'website' => 'https://www.bidvertiser.com/',
            'has_api' => true,
        ]);

        NetworkChannel::create([
            'name' => 'Bidmag',
            'website' => 'https://bidmag.net',
            'has_api' => false,
        ]);
    }
}
