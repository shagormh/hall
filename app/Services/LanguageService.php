<?php

namespace App\Services;

use App\Models\Language;
use Illuminate\Support\Facades\Schema;

class LanguageService extends BaseModelService
{
    public function model(): string
    {
        return Language::class;
    }

    public function getLanguages($isActive = true)
    {
        if (!Schema::hasTable('languages')) {
            return collect([
                (object)['name' => 'English', 'code' => 'en', 'is_active' => true],
                (object)['name' => 'Bengali', 'code' => 'bn', 'is_active' => true],
            ]);
        }
        $languages = $this->model()::where('is_active', $isActive)->get();
        return $languages;
    }

    public function getLanguageByCode($locale)
    {
        $language = $this->model()::where('code', $locale)->first();
        return $language;
    }

    public function getLanguageOptions()
    {
        $languages = $this->getLanguages();
        $result = $languages->map(function ($language) {
            return [
                'name' => $language->name,
                'code' => $language->code
            ];
        });
        return $result->all();
    }
}
