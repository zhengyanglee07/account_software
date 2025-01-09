<?php

namespace App\Services\Automations\Steps\Actions;

use App\TriggeredStep;
use App\ProcessedTag;

class RemoveTagStrategy implements IActionStrategy
{
    public function perform(TriggeredStep $triggeredStep): void
    {
        $processedContactId = $triggeredStep->processed_contact_id;

        if (!$processedContactId) {
            throw new RuntimeException('processed_contact_id not present in TriggeredStep');
        }

        $properties = $triggeredStep->step['properties'];
        $processedTag = ProcessedTag::find($properties['processed_tag_id'] ?? null);

        if ($processedTag) {
            $processedTag->processedContacts()->detach($processedContactId);
        }
    }
}
