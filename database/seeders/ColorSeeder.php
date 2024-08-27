<?php

namespace Database\Seeders;
use App\Models\Color;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ai_models = [
			['code' => "#48494B"],
			['code' => "#6c757d"],
			['code' => "#ff3a6e"],
			['code' => "#ffa21d"],
			['code' => "#3ec9d6"],
			['code' => "#53d63e"],
			['code' => "#d63e9b"],
			['code' => "#4a3ed6"],
			['code' => "#e32412"],
			['code' => "#02d7c3"],
			['code' => "#bec100"],
			['code' => "#ff7b2d"],
			['code' => "#9b2dff"],
			['code' => "#007e73"]
        ];
          
        foreach ($ai_models as $key => $value) {
			$is_exists = Color::where('code', $value['code'])->count();
			if ($is_exists == 0) {
				Color::create($value);
			}
        }
    }
}
