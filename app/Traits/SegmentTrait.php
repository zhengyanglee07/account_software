<?php

namespace App\Traits;

use App\ProcessedContact;
use App\Segment;

trait SegmentTrait
{
    public function getConditionsJson()
    {
        return config('conditions');
    }

    public function matchSegmentId($automation, $segmentId, $action)
    {
        return $automation->automationTriggers->contains(function ($at) use ($segmentId, $action) {
            $triggerKind = $action === 'enter' ? 'automationEnterSegmentTrigger' : 'automationExitSegmentTrigger';
            $esTrigger = $at->{$triggerKind};

            if (!$esTrigger) return false;

            $segment = $esTrigger->segment;
            return is_null($segment) || $segment->id === $segmentId;
        });
    }

    public function getSegmentIdsByContact(ProcessedContact $processContact)
    {
        $segmentIds = [];
        Segment::where('account_id', $processContact->account_id)->each(function ($row) use (&$segmentIds, $processContact) {
            $contactIDs = $row->contacts()->pluck('id')->toArray();
            if (in_array($processContact->id, $contactIDs)) $segmentIds[] = $row->id;
        });
        return $segmentIds;
    }
}
