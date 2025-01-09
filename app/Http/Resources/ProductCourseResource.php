<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductCourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  request
     * @return array$
     */
    public function toArray($request)
    {
        return $this->modules->sortBy('order')->values()->map(function ($module) {
            return [
                'id' => $module->id,
                'name' => $module->title,
                'order' => $module->order,
                'isPublished' => $module->is_published,
                'hasCollapse' => true,
                'elements' => $module->lessons->sortBy('order')->values()->map(function ($lesson) use ($module) {
                    return [
                        'id' => $lesson->id,
                        'moduleId' => $module->id,
                        'name' => $lesson->title,
                        'videoUrl' => $lesson->video_url,
                        'description' => $lesson->description,
                        'isPublished' => $lesson->is_published,
                        'order' => $lesson->order,
                        'parameter' => json_decode($lesson->parameter ?? '{}'),
                        'elements' => [],
                    ];
                })
            ];
        });
    }
}
