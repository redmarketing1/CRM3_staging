<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AiModel;

class AiModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ai_models = [
			['provider' => 'ChatGPT', 'model' => 'gpt-3.5-turbo', 'model_label' => 'GPT-3.5 Turbo', 'max_tokens' => '16,385'],
			['provider' => 'ChatGPT', 'model' => 'gpt-4', 'model_label' => 'GPT-4', 'max_tokens' => '8,192'],
			['provider' => 'ChatGPT', 'model' => 'gpt-4o', 'model_label' => 'GPT-4o', 'max_tokens' => '128,000'],
            ['provider' => 'GeminiAI', 'model' => 'gemini-1.5-pro', 'model_label' => 'Gemini 1.5 Pro'],
        ];
          
        foreach ($ai_models as $key => $value) {
			$ai_mdl = AiModel::where('model', $value['model'])->count();
			if ($ai_mdl == 0) {
				AiModel::create($value);
			}
        }
    }
}
