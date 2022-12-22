<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder; 
use Spatie\Permission\Models\Role; 
use DB; 
class StateTableSeeder extends Seeder
{
    public function run()
    {
        $path = public_path('sql/states.sql');
        $sql = file_get_contents($path);
        DB::unprepared($sql);
    }
}
