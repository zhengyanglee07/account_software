<?php


namespace App\Services\AdAudiences;

use App\AdAudience;
use App\Segment;
use Google\AdsApi\AdWords\AdWordsServices;
use Google\AdsApi\AdWords\AdWordsSessionBuilder;
use Google\AdsApi\AdWords\v201809\cm\Operator;
use Google\AdsApi\AdWords\v201809\mcm\CustomerService;
use Google\AdsApi\AdWords\v201809\rm\AdwordsUserListService;
use Google\AdsApi\AdWords\v201809\rm\CrmBasedUserList;
use Google\AdsApi\AdWords\v201809\rm\CustomerMatchUploadKeyType;
use Google\AdsApi\AdWords\v201809\rm\Member;
use Google\AdsApi\AdWords\v201809\rm\MutateMembersOperand;
use Google\AdsApi\AdWords\v201809\rm\MutateMembersOperation;
use Google\AdsApi\AdWords\v201809\rm\UserList;
use Google\AdsApi\AdWords\v201809\rm\UserListMembershipStatus;
use Google\AdsApi\AdWords\v201809\rm\UserListOperation;
use Google\AdsApi\Common\AdsSoapClient;
use Google\AdsApi\Common\OAuth2TokenBuilder;
use Hashids\Hashids;

class GoogleCustomerMatch
{
    /**
     * @var string
     */
    private $refresh_token;

    private $accountId;

    /**
     * @var int
     */
    private $clientCustomerId;

    private $oAuth2Credential;

    public function __construct(string $refresh_token, $accountId)
    {
        $this->refresh_token = $refresh_token;
        $this->accountId = $accountId;

        if (!$this->oAuth2Credential) {
            $this->init();
        }

        $this->fetchClientCustomerId();
    }

    /**
     * Normalize (lowercase & trim) and hash properties needed
     * by customer match UserList
     *
     * @param $value
     * @return string
     */
    private function normalizeAndHash($value): string
    {
        return hash('sha256', strtolower(trim($value)));
    }

    /**
     *
     * @param null $clientCustomerId
     * @return \Google\AdsApi\AdWords\AdWordsSession|mixed
     */
    private function buildAdWordsSession($clientCustomerId = null)
    {
        $partialSession = (new AdWordsSessionBuilder())
            ->withDeveloperToken(config('app.google-ads-developer-token'))
            ->withOAuth2Credential($this->oAuth2Credential);

        if (!$clientCustomerId) {
            return $partialSession->build();
        }

        return $partialSession
            ->withClientCustomerId($clientCustomerId)
            ->build();
    }

    /**
     * @return AdsSoapClient|\SoapClient
     */
    private function getUserListService()
    {
        $this->throwIfClientCustomerIdAbsent();

        $adWordsServices = new AdWordsServices();
        return $adWordsServices->get(
            $this->buildAdWordsSession($this->clientCustomerId),
            AdwordsUserListService::class
        );
    }

    /**
     * Fetch clientCustomerId of ads account for all actions required
     * @throws \Exception
     */
    public function fetchClientCustomerId(): void
    {
        $adWordsServices = new AdWordsServices();
        $customerService = $adWordsServices->get(
            $this->buildAdWordsSession(),
            CustomerService::class
        );

        try {
            $customer = $customerService->getCustomers()[0];

            if ($customer) {
                $this->clientCustomerId = $customer->getCustomerId();
            }
        } catch (\Exception $ex) {
            \Log::error('Something wrong when fetching clientCustomerId', [
                'exception' => $ex,
                'account_id' => $this->accountId
            ]);
            throw $ex;
        }
    }

    /**
     * Initialize OAuth2 credentials required from configs & db
     */
    public function init(): void
    {
        $this->oAuth2Credential = (new OAuth2TokenBuilder())
            ->withClientId(config('services.google.client_id'))
            ->withClientSecret(config('services.google.client_secret'))
            ->withRefreshToken($this->refresh_token)
            ->build();
    }

    /**
     * @throws \RuntimeException
     */
    private function throwIfClientCustomerIdAbsent(): void
    {
        if (!$this->clientCustomerId) {
            throw new \RuntimeException('clientCustomerId is needed to perform this action');
        }
    }

    // ===========================================================================

    /**
     * @return int
     */
    public function getClientCustomerId(): int
    {
        return $this->clientCustomerId;
    }

    /**
     * @param $segment
     * @param AdAudience $adAudience
     * @param AdsSoapClient $userListService the ads API service
     * @return mixed|UserList[]
     */
    public function queryUserLists($segment, AdAudience $adAudience, $userListService)
    {
        $this->throwIfClientCustomerIdAbsent();
        $userListName = $this->generateCrmUserListName($segment, $adAudience);

        $query = "SELECT Name WHERE Name = '$userListName'";

        $userListPage = $userListService->query($query);
        return $userListPage->getEntries();
    }

    /**
     * @param $segment
     * @param AdAudience $adAudience
     * @return string
     */
    private function generateCrmUserListName($segment, AdAudience $adAudience): string
    {
        $hashIds = new Hashids('Hypershapes Google Customer Match', 10);

        // hashId just as a fallback if list_name is missing
        return $adAudience->list_name
            ? "Hypershapes CRM list - {$adAudience->list_name}"
            : 'Hypershapes CRM list #'
            . $hashIds->encode($this->accountId)
            . $hashIds->encode($segment->id);
    }

    /**
     * Closing a user list. Return value of true indicates a success operation,
     * else failed.
     *
     * @param $segment
     * @param AdAudience $adAudience
     * @return bool
     */
    public function closeCrmBasedUserList($segment, AdAudience $adAudience): bool
    {
        $userListService = $this->getUserListService();
        $userList = $this->queryUserLists($segment, $adAudience, $userListService)[0];

        if (!$userList) return false;

        // set account status to 'CLOSED'. This is equivalent to delete a list
        // from third-party integration POV
        $userList->setStatus(UserListMembershipStatus::CLOSED);

        $operations = [];
        $operation = new UserListOperation();
        $operation->setOperand($userList);
        $operation->setOperator(Operator::SET);
        $operations[] = $operation;

        try {
            // Create the user list on the server and print out some information.
            $userListService->mutate($operations);
        } catch (\Exception $ex) {
            \Log::error($ex, [
                'account_id' => $segment->account_id,
                'segment_id' => $segment->id,
            ]);

            return false;
        }

        return true;
    }

    /**
     * Add CRM-based user list to ads account audience manager
     * Reminder: it may take several hours for the list to be
     *           populated with members.
     *
     * @param Segment $segment
     * @param AdAudience $adAudience
     */
    public function addCrmBasedUserList(Segment $segment, AdAudience $adAudience): void
    {
        $this->throwIfClientCustomerIdAbsent();

        $emails = $segment->contacts()->pluck('email');
        $adWordsServices = new AdWordsServices();
        $userListService = $adWordsServices->get(
            $this->buildAdWordsSession($this->clientCustomerId),
            AdwordsUserListService::class
        );

        $userListName = $this->generateCrmUserListName($segment, $adAudience);

        $userList = new CrmBasedUserList();
        $userList->setName($userListName);
        $userList->setDescription(
            'List of segmented customers that originated from email addresses'
        );

        // CRM-based user lists can use a membershipLifeSpan of 10000 to
        // indicate unlimited; otherwise normal values apply.
        // Sets the membership life span to 30 days.
        $userList->setMembershipLifeSpan(30);
        $userList->setUploadKeyType(CustomerMatchUploadKeyType::CONTACT_INFO);

        // Create a user list operation and add it to the list.
        $operations = [];
        $operation = new UserListOperation();
        $operation->setOperand($userList);
        $operation->setOperator(Operator::ADD);
        $operations[] = $operation;

        // Create the user list on the server and print out some information.
        $userList = $userListService->mutate($operations)->getValue()[0];

        // Create operation to add members to the user list based on email
        // addresses.
        $mutateMembersOperations = [];
        $mutateMembersOperation = new MutateMembersOperation();
        $operand = new MutateMembersOperand();
        $operand->setUserListId($userList->getId());

        $members = [];

        // Hash normalized email addresses based on SHA-256 hashing algorithm.
        foreach ($emails as $email) {
            $memberByEmail = new Member();
            $memberByEmail->setHashedEmail($this->normalizeAndHash($email));
            $members[] = $memberByEmail;
        }

        // Add members to the operand and add the operation to the list.
        $operand->setMembersList($members);
        $mutateMembersOperation->setOperand($operand);
        $mutateMembersOperation->setOperator(Operator::ADD);
        $mutateMembersOperations[] = $mutateMembersOperation;

        // Add members to the user list based on email addresses.
        $userListService->mutateMembers($mutateMembersOperations);

        // update hypershapes ad_audience table
        $adAudience->list_id = $userList->getId();
        $adAudience->save();
    }

    /**
     * Sync CRM-based user list to ads account audience manager.
     * Sync = delete ALL members in ads account user list,
     *        then add all existing contacts in segment to the list.
     *
     * More efficient solution such as remove and add necessary contacts only
     * is quite impossible due to limitation of Adwords API, in which
     * most info of user list members can't be retrieved.
     *
     * Reminder: it may take several hours for the list to be
     *           populated with members.
     *
     * @param Segment $segment
     * @param AdAudience $adAudience
     * @return bool
     */
    public function syncCrmBasedUserList(Segment $segment, AdAudience $adAudience): bool
    {
        $userListService = $this->getUserListService();
        $userList = $this->queryUserLists($segment, $adAudience, $userListService)[0];

        if (!$userList) return false;

        // general array storing all operations
        $mutateMembersOperations = [];

        // first remove all user list members
        $removeMembersOperation = new MutateMembersOperation();
        $removeMembersOperand = new MutateMembersOperand();
        $removeMembersOperand->setUserListId($userList->getId());
        $removeMembersOperand->setRemoveAll(true);
        $removeMembersOperation->setOperand($removeMembersOperand);
        $removeMembersOperation->setOperator(Operator::REMOVE);
        $mutateMembersOperations[] = $removeMembersOperation;


        // then add latest segment contacts to the list
        $members = [];
        $emails = $segment->contacts()->pluck('email');

        // Hash normalized email addresses based on SHA-256 hashing algorithm.
        foreach ($emails as $email) {
            $memberByEmail = new Member();
            $memberByEmail->setHashedEmail($this->normalizeAndHash($email));
            $members[] = $memberByEmail;
        }

        $addMembersOperation = new MutateMembersOperation();
        $addMemberOperand = new MutateMembersOperand();
        $addMemberOperand->setUserListId($userList->getId());
        $addMemberOperand->setMembersList($members);
        $addMembersOperation->setOperand($addMemberOperand);
        $addMembersOperation->setOperator(Operator::ADD);
        $mutateMembersOperations[] = $addMembersOperation;

        // commit all operations to userList
        $userListService->mutateMembers($mutateMembersOperations);

        // update hypershapes ad_audience table
        $adAudience->sync_status = 'synced';
        $adAudience->save();

        return true;
    }
}
