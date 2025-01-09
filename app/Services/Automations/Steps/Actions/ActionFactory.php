<?php

namespace App\Services\Automations\Steps\Actions;

class ActionFactory
{
    public static function createAction(string $kind): IActionStrategy
    {
        if ($kind === 'automationSendEmailAction') {
            return new SendEmailStrategy;
        }

        if ($kind === 'automationAddTagAction') {
            return new AddTagStrategy;
        }

        if ($kind === 'automationRemoveTagAction') {
            return new RemoveTagStrategy;
        }
    }
}
