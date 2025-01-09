<?php

namespace App\Services;

use App\Account;
use App\Email;
use App\ProcessedContact;

class EmailMergeTagService
{
    /**
     * Merge all tags (mostly footer) in an email html, and return a
     * new html contained converted tags with data. For details what
     * a merge tag is, refer to ConvertMergeTags service.
     *
     * @param \App\Email $email
     * @param \App\ProcessedContact $contact
     * @param string|null $html
     * @return string
     */
    public function mergeTags(Email $email, ProcessedContact $contact, ?string $html): string
    {
        $account = Account::findOrFail($email->account_id);
        $convertMergeTags = new ConvertMergeTags($html ?? $email->emailDesign->html, $contact, $account, $email);
        return $convertMergeTags->execute();
    }

    /**
     * Check if required merge tags absent before sending. Refer to
     * $requiredMergeTags array below for list of required merge tags.
     *
     * Note: please take merge tags by using config() func only, don't hardcode
     *
     * @param Email $email
     * @return bool
     */
    public function checkIfRequiredMergeTagsAbsent(Email $email): bool
    {
        $requiredMergeTags = [
            config('merge-tags.COMPANY_ADDRESS_TAG'),
            config('merge-tags.UNSUBSCRIBE_TAG')
        ];

        // do nothing if user doesn't design new email yet
        if (!$email->emailDesign) return false;

        $html = $email->emailDesign->html;

        foreach ($requiredMergeTags as $requiredMergeTag) {
            if (strpos($html, $requiredMergeTag) === false) {
                return true;
            }
        }

        return false;
    }
}
