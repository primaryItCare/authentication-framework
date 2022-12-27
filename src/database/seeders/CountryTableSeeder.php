<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\CountryModel;
use Spatie\Permission\Models\Role; 
class CountryTableSeeder extends Seeder
{
    public function run(){
        CountryModel::create([
            'code' 		        => '+1',
            'country_name' 		=> 'United States',
            'short_code' 		=> 'US',
        ]);
    }
}
