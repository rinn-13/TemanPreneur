<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Utils\ImageUrl;

class BlogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'image' => ImageUrl::normalize($this->image),
            'author' => $this->business->user->name,
            'author_avatar' => $this->business->user->avatar,
            'business' => [
                'id' => $this->business->id,
                'name' => $this->business->name,
                'slug' => \Illuminate\Support\Str::slug($this->business->name),
            ],
            'category' => $this->business->category,
            'created_at' => $this->created_at->format('d M Y'),
            'read_time' => $this->estimateReadTime(),
        ];
    }

    private function estimateReadTime(): string
    {
        $words = str_word_count(strip_tags($this->excerpt ?? $this->content));
        $minutes = ceil($words / 200);
        return $minutes . ' menit';
    }
}

