<?php

namespace App\Services;

use App\Account;
use App\ProcessedAddress;
use App\ProcessedContact;

use App\Repository\Checkout\FormDetail;

use App\Services\RefKeyService;

use App\Traits\AuthAccountTrait;
use Carbon\Carbon;

class ProcessedContactService
{
	use AuthAccountTrait;

	public $processedContact;
	public $incrementField = ['ordersCount', 'totalSpent'];


	public function generateContactRandomId()
	{
		$refKeyService = new RefKeyService();
		return $refKeyService->getRefKey(new ProcessedContact, 'contactRandomId');
	}

	/**
	 * Create or update processed contact
	 *
	 * $params used for extra data
	 * 
	 * @param  mixed $formDetail
	 * @param  mixed $order
	 * @return ProcessedContact
	 */
	public function updateOrCreateProcessedContact(FormDetail $formDetail, $params)
	{
		$customerInfo = $formDetail->customerInfo;

		// Primarily check people by email
		$constrains = !empty($customerInfo->email)
			? ['email' => $customerInfo->email]
			: ['phone_number' => $customerInfo->phoneNumber];

		$accountId = $this->getCurrentAccountId();
		$processedContact = ProcessedContact::where('account_id', $accountId)
			->firstOrNew($constrains);
		
		$processedContact->account_id = $accountId;

		if (empty($processedContact->contactRandomId)) $processedContact->contactRandomId = $this->generateContactRandomId();
		if (!empty($customerInfo->fullName)) $processedContact->fname = $customerInfo->fullName;
		if (!empty($customerInfo->email)) $processedContact->email = $customerInfo->email;
		if (!empty($customerInfo->phoneNumber)) $processedContact->phone_number = $customerInfo->phoneNumber;

		$processedContact->dateCreated = $this->datetimeToSelectedTimezone(date('Y-m-d H:i:s'));

		try {
			foreach ($params as $key => $value) {
				if (in_array($key, $this->incrementField)) {
					$processedContact->{$key} += $value;
					continue;
				}
				$processedContact->{$key} = $value;
			}
		} catch (\Throwable $th) {
			throw $th;
		}

		$processedContact->save();
		ProcessedAddress::updateOrCreate(['processed_contact_id' => $processedContact->id]);

		return $processedContact;
	}

	public function getProcessedContactByEmail($email)
	{
		if(empty($email)) return;
		return ProcessedContact::firstWhere([
			'account_id' => $this->getCurrentAccountId(),
			'email' => $email,
		]);
	}

	private function datetimeToSelectedTimezone($datetime): string
    {
        $accountTimeZone = Account::find($this->getCurrentAccountId())->timeZone;
        $datetime = new Carbon($datetime);
        return $datetime->timezone($accountTimeZone)->isoFormat('YYYY-MM-DD\\ H:mm:ss');
    }

    public function getPaginatedContacts()
    {
        $contact = ProcessedContact::whereAccountId($this->getCurrentAccountId())->without('orders');
        return (new PaginatorService())->getPaginatedData($contact, null, '/paginated/contacts');
    }
}
