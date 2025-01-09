<?php

namespace App\Traits\Automations;

use App\Automation;
use App\ProcessedTag;

trait TriggerableTag
{
    protected function matchProcessedTagId($automation, ProcessedTag $processedTag, string $kind): bool
    {
        return $automation->automationTriggers->contains(function ($at) use ($processedTag, $kind) {
            $tagTriggerKind = $at->$kind;

            if (!$tagTriggerKind) {
                return false;
            }

            return $tagTriggerKind->processed_tag_id === $processedTag->id;
        });
    }
}
