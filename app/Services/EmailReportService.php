<?php

namespace App\Services;

use App\Email;
use App\EmailBounce;
use App\Models\EmailReport;
use Illuminate\Support\Facades\Log;

class EmailReportService
{

	public function __construct(public Email $email)
	{
	}

	public function updateTotalEmailOpened()
	{
		$totalOpened = $this->email->sentEmails()
			->where('opens', '!=', 0)
			->count();

		EmailReport::updateOrCreate(
			['email_id' => $this->email->id],
			['total_opened' => $totalOpened]
		);
	}

	public function updateTotalEmailClicked()
	{
		$totalClicked = $this->email->sentEmails()
			->where('clicks', '!=', 0)
			->count();

		EmailReport::updateOrCreate(
			['email_id' => $this->email->id],
			['total_clicked' => $totalClicked]
		);
	}

	public function updateTotalEmailBounced()
	{
		$totalBounced = EmailBounce::where('account_id', $this->email->account_id)
			->whereIn('email_address', $this->email->sentEmails()->pluck('recipient_email')->toArray())
			->count();

		EmailReport::updateOrCreate(
			['email_id' => $this->email->id],
			['total_bounced' => $totalBounced]
		);
	}

	public function updateTotalEmailUnsubscribed()
	{
		$totalUnsubscribed = $this->email->processedContacts()->wherePivot('status', 'unsubscribe')->count();

		EmailReport::updateOrCreate(
			['email_id' => $this->email->id],
			['total_unsubscribed' => $totalUnsubscribed]
		);
	}
}
