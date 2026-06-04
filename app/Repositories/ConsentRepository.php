<?php

namespace App\Repositories;

use App\Models\ConsentType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class ConsentRepository
{
    public function getAllActive(): Collection
    {
        return ConsentType::all();
    }

    /**
     * Получить коллекцию ID по коллекции slug
     * @param SupportCollection<string> $slugs
     * @return SupportCollection<int>
     */
    public function getIdsBySlugs(SupportCollection $slugs): SupportCollection
    {
        return ConsentType::whereIn('slug', $slugs)->pluck('id');
    }
}
