<?php

namespace App\Traits;

use App\Order;
use App\Account;
use App\EcommerceVisitor;
use App\Notification;
use App\UsersProduct;
use App\LandingPageForm;
use App\NotificationType;
use App\ProcessedContact;
use App\NotificationSetting;
use Carbon\Carbon;

trait SocialProofTrait
{
    private $notificationDetails;
    private $products;
    private $forms;
    private $events;
    private $orders;

	//Social Proof Settings
	public function socialProofSetting($accountId){
		return NotificationSetting :: where('account_id', $accountId)->first();
	}

	public function targetFunnelId($accountId){
		$notificationDetails = $this->notificationDetails;
		$funnelsId = [];
		$targetFunnelsId = \DB::table('notification_types')
        ->join('notifications','id','=','notification_types.notification_id')->select('target_id')
        ->where([
            'account_id' => $accountId,
            'notification_id' => $notificationDetails->id,
			'target_type'  => 'funnel',
        ])->get();
		foreach($targetFunnelsId as $targetFunnelId){
			$funnelId =  $targetFunnelId->target_id;
			array_push($funnelsId, $funnelId);
		}
		return($funnelsId);
	}
	//retrieve selected product/form id
	public function eventsId(){
		$notification = $this->notificationDetails;
        $products = $this->products;
        $forms = $this->forms;
		if($notification['event_type'] === 'product' && boolval($notification['is_all_selected']) === true) {
            $events = $products?->map(function ($product) {
                return $product->id;
            });
        }
        else if($notification['event_type'] === 'form' && boolval($notification['is_all_selected']) === true){
            $events = $forms?->map(function ($form) {
                return $form->id;
            });
        }
        elseif($notification['event_type']==='product'||'form'){
			$events = NotificationType :: select('target_id')->where([
				'notification_id'=> $notification['id'],
				'target_type' => $notification['event_type'],
			])->get();
		}
		return $events;
	}
	//return event id
	public function eventDetails(){
		$notification = $this->notificationDetails;
		if($notification['event_type']==='product'){
			$orderProducts = [];
			foreach($this->events as $event){
				$event_id = boolval($notification['is_all_selected'])===true ? $event : $event->target_id;

				foreach($this->orders as $order ){
					$order_id = $order->id;

					$orderDetail =  $this->orders->find($order_id)->orderDetails->where('users_product_id', $event_id)->first();
                    $product = $this->products->find($event_id);

					if(count((array)$orderDetail)>0){
						array_push($orderProducts, (object)[
						'order_id' => $order_id,
						'users_product_id' => $orderDetail->users_product_id,
						'image_url' => $product?->productImagePath ?? null,
						'paid_at' => $orderDetail->paid_at,
						'product_name'=> $orderDetail->product_name,
						'product_path'=> $product?->productImagePath ?? null,
						'created_at' => $orderDetail->created_at,
						]);
					}
			}}
			return $orderProducts;
		}
		elseif ($notification['event_type']==='form') {

			$formsDetail = [];

			foreach($this->events as $event){
				$event_id = boolval($notification['is_all_selected'])===true ? $event : $event->target_id;
                $formDetail = $this->forms->find($event_id);
				if($formDetail?->submit_count > 0 && count((array)$formDetail)>0 )
                    array_push($formsDetail, $formDetail);
			}

			return $formsDetail;
		}
        return;
	}
	//processed contact
	public function processedContacts($accountId, $eventDetails){
		$notificationDetails = $this->notificationDetails;
        $orders = $this->orders;

		$funnelId = $this->targetFunnelId($accountId);
		$processedContacts =[];
		if($notificationDetails['event_type']==='product'){
			foreach($eventDetails as $eventDetail){
                $order_id = $eventDetail->order_id;
                $order = $orders->find($order_id);
                $processedContact = $order->processedContact;
				$processedContactId = is_object($processedContact) ? $processedContact->id : '';
				$processedContactName = is_object($processedContact) ? $processedContact->fname: null;
				array_push($processedContacts, (object)[
				'order_id' => $order_id,
				'processed_contact_id' => $processedContactId,
				'fname' => $processedContactName,
				'city' => $order?->billing_city ?? null,
				'country' =>  $order?->billing_country ?? null,
				'product_id' => $eventDetail->users_product_id,
				'product_image' => $eventDetail->image_url,
				'paid_at' => $eventDetail->paid_at,
				'product_name'=> $eventDetail->product_name,
				'funnel_id' => $funnelId,
				'url' => "/products/$eventDetail->product_path",
				'created_at' => $eventDetail->created_at,
				]);
			}

			return $processedContacts;
		}
		elseif($notificationDetails['event_type']==='form'){
            $processedContactsInfo = ProcessedContact::where('account_id', $accountId)->get();
			foreach($eventDetails as $eventDetail){
				$event_id = $eventDetail->id;
				$formId = $this->forms->find($event_id);
                if($formId?->submit_count<1){
                    continue;
                };
				$visitorsId = $this->forms->find($event_id)->formContents->where('visitor_id', '!=', null)->unique('visitor_id')->map(function($el){
                    return $el->visitor_id;
                });
                $processedContactsId =  EcommerceVisitor::whereIn('id', $visitorsId)->get()->take(200)->unique('processed_contact_id');

				foreach($processedContactsId as $processedContactId){
					$processedContact = null;

                    if($processedContactId['processed_contact_id']!==null)
                        $processedContact = $processedContactsInfo->find($processedContactId['processed_contact_id']);


					array_push($processedContacts, (object)[
						'form_id' => $formId?->id,
						'processed_contact_id' => $processedContact?->processed_contact_id ?? null,
						'fname' => $processedContact?->fname ?? null,
						'city' => $processedContact?->city ?? null,
						'country' => $processedContact?->country ?? null,
						'created_at' => $processedContact?->created_at ?? null,
						'form_title' => $formId?->title?? null,
						'funnel_id' => $funnelId ?? null,
					]);
				}

			}
			$accountTimeZone = Account::find($accountId)->timeZone;
			foreach($processedContacts as $processedContact){
				$processedContactTime = new Carbon($processedContact->created_at);
				$processedContact->convertTime = $processedContactTime->timezone($accountTimeZone)
				->isoFormat('YYYY-MM-D H:mm:s');
			}
			return $processedContacts;
		}
		return;
	}

    public function fetchAllNotification($accountId, $saleChannel = 'store'){


        $displayType = $saleChannel === 'mini-store' ? 'online-store' : $saleChannel;
        $socialProof = Notification::where('account_id', $accountId)->where(['display_type'=> $displayType, 'is_enabled'=>true])->latest()->first();
        if(!$socialProof) return ;

		$saleChannel = $saleChannel !== 'online-store' ? 'Mini Store' : 'Online Store';
        $this->notificationDetails = $socialProof;
        $notificationContent =(array)[];
        $notificationContent['notification'] = (object)[];
        $notificationContent['eventId']= [];
        $notificationContent['eventDetails']= [];
        $notificationContent['processedContacts'] = [];
        $notificationContent['socialProofSetting'] = (object)[];


        $this->orders = Order ::without('orderDiscount')->where(['account_id'=> $accountId, 'payment_status' => 'Paid', 'acquisition_channel' => $saleChannel])
        ->where(function($query){
            $query->where('payment_process_status', 'Success')->orWhereNull('payment_process_status');
        })->orderBy('paid_at', 'desc')->take(20)->get();
        if($this->notificationDetails['event_type']==='product') $this->products = UsersProduct::where('account_id', $accountId)->get();
        if($this->notificationDetails['event_type']==='form') $this->forms = LandingPageForm::where('account_id', $accountId)->get();
        $this->events = $this->eventsId();
        $eventDetails = $this->eventDetails($accountId);



		if($this->notificationDetails){
			$notificationContent['notification'] = $this->notificationDetails;
            $notificationContent['eventId'] = $this->events;
            $notificationContent['eventDetails'] = $eventDetails;
			$notificationContent['processedContacts'] = $this->processedContacts($accountId, $eventDetails);
			$notificationContent['socialProofSetting'] = $this->socialProofSetting($accountId);
		}

        return $notificationContent;

    }
}
