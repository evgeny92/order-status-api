<?php

namespace App\Traits;

use App\Models\Tag;
use Illuminate\Support\Str;

trait OrderTags
{
    protected function prepareTagIds(array $tags): array
    {
        $tagIds = [];

        foreach ($tags as $tag) {
            $slug = Str::slug($tag, '-');

            $tagItem = Tag::query()->firstOrCreate(
                ['slug' => $slug],
                ['name' => $tag]
            );

            $tagIds[$tagItem->id] = ['added_at' => now()];
        }

        return $tagIds;
    }

    public function getTagNames(): array
    {
        return $this->tags->pluck('name')->toArray();
    }

    public function syncTags(array $tags): void
    {
        $tagIds = $this->prepareTagIds($tags);
        $this->tags()->sync($tagIds);
    }
}
