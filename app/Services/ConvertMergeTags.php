<?php

namespace App\Services;

use App\Account;
use App\ProcessedContact;
use App\Email;

/**
 * Class ConvertMergeTags
 * @package App\Services
 *
 * Step-by-step How to add new merge tag:
 * 1) Add new entry in config/merge-tags.php file.
 *    E.g. 'NEW_TAG' => '{{NEW_TAG}}',
 * 2) Add new private constant field of your new tag.
 *    E.g. private $NEW_TAG;
 * 3) Initialize your new tag from config in __construct.
 *    E.g. $this->NEW_TAG = config('merge-tags.NEW_TAG');
 * 4) Add new private merge*Tag method that uses $this->replaceTagInHtml helper.
 *    E.g. private function mergeNewTag() {}
 *    For more details please take a look on any existing $this->merge*Tag method.
 * 5) Add/Call the new $this->merge*Tag method in $this->execute();
 *
 */
class ConvertMergeTags
{
    // merge tags
    private $CURRENT_YEAR_TAG;
    private $COMPANY_NAME_TAG;
    private $COMPANY_ADDRESS_TAG;
    private $REWARDS_TAG;
    private $FIRST_NAME_TAG;
    private $LAST_NAME_TAG;
    private $UNSUBSCRIBE_TAG;

    // core fields
    private $processedHtml;
    private $contact;
    private $account;
    private $email;

    public function __construct(string $html, ProcessedContact $contact, Account $account, Email $email)
    {
        $this->processedHtml = $html;
        $this->contact = $contact;
        $this->account = $account;
        $this->email = $email;

        // initialize tags from configs
        $this->CURRENT_YEAR_TAG = config('merge-tags.CURRENT_YEAR_TAG');
        $this->COMPANY_NAME_TAG = config('merge-tags.COMPANY_NAME_TAG');
        $this->COMPANY_ADDRESS_TAG = config('merge-tags.COMPANY_ADDRESS_TAG');
        $this->REWARDS_TAG = config('merge-tags.REWARDS_TAG');
        $this->FIRST_NAME_TAG = config('merge-tags.FIRST_NAME_TAG');
        $this->LAST_NAME_TAG = config('merge-tags.LAST_NAME_TAG');
        $this->UNSUBSCRIBE_TAG = config('merge-tags.UNSUBSCRIBE_TAG');
    }

    // ===================================================
    // Helpers
    // ===================================================
    /**
     * Wrapper around str_replace to ease the tag replace process
     *
     * @param $tag string Tag to search for
     * @param $replace string Replace term for tag searched
     */
    private function replaceTagInHtml($tag, $replace): void
    {
        $this->processedHtml = str_replace($tag, $replace, $this->processedHtml);
    }
    // ===================================================
    // End Helpers
    // ===================================================

    // ===================================================
    // Merge tag methods
    // ===================================================
    private function mergeCurrentYearTag(): void
    {
        $this->replaceTagInHtml($this->CURRENT_YEAR_TAG, date('Y'));
    }

    private function mergeCompanyNameTag(): void
    {
        $companyName = $this->account->company;
        $this->replaceTagInHtml($this->COMPANY_NAME_TAG, $companyName);
    }

    private function mergeCompanyAddressTag(): void
    {
        // TODO: combine address, zip, state, ...
        $companyAddress = $this->account->address;
        $this->replaceTagInHtml($this->COMPANY_ADDRESS_TAG, $companyAddress);
    }

    /**
     * This tag kinda special, it's a referral badge which will redirect
     * email receiver to our site.
     *
     * For details go and ask your boss
     */
    private function mergeRewardsTag(): void
    {
        // TODO: change img url of https://staging.hypershapes.com to production site one
        $clickableRewardsImg = '
            <div style="margin: 0 auto; max-width: 400px;">
                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px; width: 100%;">
                    <tbody>
                        <tr>
                            <td>
                                <a href="https://hypershapes.com" target="_blank">
                                    <img height="auto"
                                         alt="hypershapes-referral-badge"
                                         src="https://staging.hypershapes.com/images/hypershapes-logo.png"
                                         style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;"
                                         width="400">
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>';

        $this->replaceTagInHtml($this->REWARDS_TAG, $clickableRewardsImg);
    }

    private function mergeFirstNameTag(): void
    {
        $firstName = $this->contact->fname;
        $this->replaceTagInHtml($this->FIRST_NAME_TAG, $firstName);
    }

    private function mergeLastNameTag(): void
    {
        $lastName = $this->contact->lname;
        $this->replaceTagInHtml($this->LAST_NAME_TAG, $lastName);
    }

    private function mergeUnsubscribeTag(): void
    {
        $appUrl = rtrim(config('app.url'), '/');
        // $port = config('app.env') === 'local' ? ':8000' : '';
        // add :8000 your .env APP_URL
        $unsubUrl = $appUrl . '/emails/unsubscribe/' . $this->contact->contactRandomId . '/' . $this->email->id;
        $unsubAnchorTag = "<a href='$unsubUrl'>unsubscribe</a>";
        $this->replaceTagInHtml($this->UNSUBSCRIBE_TAG, $unsubAnchorTag);
    }

    /**
     * Main function of ConvertMergeTags service. Execute all merge tag
     * operations above in this service, then return a processed html (email)
     * with tag converted
     *
     * @return string
     */
    public function execute(): string
    {
        $this->mergeCurrentYearTag();
        $this->mergeCompanyNameTag();
        $this->mergeCompanyAddressTag();
        $this->mergeRewardsTag();
        $this->mergeFirstNameTag();
        $this->mergeLastNameTag();
        $this->mergeUnsubscribeTag();

        return $this->processedHtml;
    }
}
