<?php

namespace App\Http\Controllers;

use Auth;
use App\Order;
use App\funnel;
use App\Account;
use Carbon\Carbon;
use App\OrderDetail;
use App\Notification;
use App\UsersProduct;
use App\LandingPageForm;
use App\NotificationType;
use App\ProcessedContact;
use App\NotificationSetting;
use Illuminate\Http\Request;
use App\LandingPageFormLabel;
use App\LandingPageFormContent;
use Inertia\Inertia;


class SocialProofController extends Controller
{
    public function accountId()
    {
        return Auth::user()->currentAccountId;
    }

    public function createNotification(Request $request)
    {
        $newNotification = Notification::create([
            'account_id'  => $this->accountId(),
            'name' => $request->name,
            'reference_key' => $this->generateReferenceKey(),
        ]);

        return response()->json([
            'id' => $newNotification->id,
            'reference_key' => $newNotification->reference_key,
        ]);
    }

    public function getAllNotification()
    {
        $notifications = Notification::where('account_id', $this->accountId())->orderBy('created_at', 'desc')->get();
        $accountTimeZone = Account::find($this->accountId())->timeZone;
        foreach ($notifications as $notify) {
            $notifyTime = new Carbon($notify->created_at);
            $notify->convertTime = $notifyTime->timezone($accountTimeZone)->isoFormat('MMMM D, YYYY \\at h:mm a');
        }
        return Inertia::render('social-proof/pages/SocialProofDashboard', compact('notifications'));
    }

    public function getSelectedNotification($referenceKey)
    {
        $notification = Notification::where([
            'account_id' => $this->accountId(),
            'reference_key' => $referenceKey,
        ])->first();

        $types = \DB::table('notification_types')
            ->join('notifications', 'id', '=', 'notification_types.notification_id')
            ->where([
                'account_id' => $this->accountId(),
                'reference_key' => $referenceKey,
            ])->get();

        $products = UsersProduct::where('account_id', $this->accountId())->get();
        $forms = LandingPageForm::where('account_id', $this->accountId())->get();
        $funnels = funnel::where('account_id', $this->accountId())->get();

        return Inertia::render('social-proof/pages/SocialProofEdit', compact(
            'notification',
            'types',
            'products',
            'forms',
            'funnels'
        ));
    }

    public function update(Request $request, $referenceKey)
    {
        // dd($referenceKey);
        $notification = Notification::firstWhere([
            'account_id'  => $this->accountId(),
            'reference_key' => $referenceKey,
        ]);
        // dd($request->checkedFormList);
        // $notification->funnels()->attach(['target_type' => 'funnel']);
        if ($request->eventType === 'product') {
            $notification->products()->detach();
            for ($i = 0; $i < count($request->checkedProductList); $i++) {
                $notification->products()->attach(['target_id' => $request->checkedProductList[$i]['id']]);
            }
        }
        if ($request->eventType === 'form') {
            $notification->forms()->detach();
            for ($i = 0; $i < count($request->checkedFormList); $i++) {
                $notification->forms()->attach(['target_id' => $request->checkedFormList[$i]['id']]);
            }
        }
        if ($request->displayType === 'funnel') {
            $notification->funnels()->detach();
            for ($i = 0; $i < count($request->checkedFunnelList); $i++) {
                $notification->funnels()->attach(['target_id' => $request->checkedFunnelList[$i]['id']]);
            }
        }

        $notification->update([
            'name' => $request->name,
            'event_type' => $request->eventType,
            'is_all_selected' => $request->isAllSelected,
            'display_type' => $request->displayType,
            'layout_type' => $request->layoutType,
            'message' => $request->message,
            'image_path' => $request->image,
            'mobile_position' => $request->mobilePosition,
            'desktop_position' => $request->desktopPosition,
            'greater_than_time' => $request->greaterThanTime,
            'time_type' => $request->timeType,
            'is_anonymous' => $request->isAnonymous,
            'show_first_name' => $request->showFirstName,
        ]);
        // dump($notification);
        return response()->json($notification);
    }

    public function deleteNotification($id)
    {
        $notification = Notification::where([
            'id' => $id,
            'account_id' => $this->accountId(),
        ])->firstOrFail();

        $notification->delete();

        $accountTimeZone = Account::find($this->accountId())->timeZone;

        $allNotifications = Notification::where([
            'account_id' => $this->accountId(),
        ])->get();

        foreach ($allNotifications as $notify) {
            $notifyTime = new Carbon($notify->created_at);
            $notify->convertTime = $notifyTime->timezone($accountTimeZone)->isoFormat('MMMM D, YYYY \\at h:mm a');
        }

        return response()->json([
            'message' => "Notification Deleted Successfully",
            'allNotifications' => $allNotifications,
        ]);
    }

    public function enableNotification($id)
    {
        $notification = Notification::where([
            'id' => $id,
            'account_id' => $this->accountId(),
        ])->firstOrFail();
        $notification->update([
            'is_enabled' => !$notification->is_enabled,
        ]);
        return response()->json([
            'message' => "Updated Successfully",
        ]);
    }

    public function getReferenceKey($id)
    {
        $notification = Notification::firstWhere([
            'account_id'  => $this->accountId(),
            'id' => $id,
        ]);

        return response()->json([
            'reference_key' => $notification->reference_key,
        ]);
    }

    public function generateReferenceKey()
    {
        do {
            $randomId = random_int(100000000001, 999999999999);
            $condition = Notification::where('reference_key', $randomId)->exists();
        } while ($condition);

        return $randomId;
    }

    public function getSocialProofNotificationSetting()
    {
        $settings = NotificationSetting::where('account_id', $this->accountId())->first();
        if ($settings) {
            $settings->is_loop_notification = (bool)$settings->is_loop_notification;
            $settings->is_random_notification = (bool)$settings->is_random_notification;
        }
        return Inertia::render('setting/pages/SocialProofSettings', compact('settings'));
    }

    public function editSocialProofGlobalSetting(Request $request)
    {
        $settings = NotificationSetting::firstwhere([
            'account_id' => $this->accountId(),
        ]);

        if ($settings !== null) {
            $settings->update([
                'is_loop_notification' => $request->isLoopNotification,
                'is_random_notification' => $request->isRandomNotification,
                'display_time' => $request->displayTime,
                'delay_time' => $request->delayTime,
            ]);
        } else {
            $settings = NotificationSetting::create([
                'account_id' => $this->accountId(),
                'is_loop_notification' => $request->isLoopNotification,
                'is_random_notification' => $request->isRandomNotification,
                'display_time' => $request->displayTime,
                'delay_time' => $request->delayTime,
            ]);
        }
        return response()->json($settings);
    }

    public function notificationDetails($accountId)
    {
        return Notification::select('id', 'display_type', 'event_type')->where('account_id', $accountId)
            ->orderBy('created_at', 'desc')->first();
    }
    //Social Proof Settings
    public function socialProofSetting($accountId)
    {
        return NotificationSetting::where('account_id', $accountId)->first();
    }
    //notification
    public function notification($accountId)
    {

        $notificationDetails = $this->notificationDetails($accountId);

        $notification = Notification::where([
            'account_id' => $accountId,
            'id' => $notificationDetails['id'],
        ])->first();
        return $notification;
    }
    public function targetFunnelId($accountId)
    {
        $notificationDetails = $this->notificationDetails($accountId);
        $funnelsId = [];

        $targetFunnelsId = \DB::table('notification_types')
            ->join('notifications', 'id', '=', 'notification_types.notification_id')->select('target_id')
            ->where([
                'account_id' => $accountId,
                'notification_id' => $notificationDetails['id'],
                'target_type'  => 'funnel',
            ])->get();
        foreach ($targetFunnelsId as $targetFunnelId) {
            $funnelId =  $targetFunnelId->target_id;
            array_push($funnelsId, $funnelId);
        }
        return ($funnelsId);
    }
    //retrieve selected product/form id
    public function eventsId($accountId)
    {
        $notification = $this->notification($accountId);
        if ($notification['event_type'] === 'product' && boolval($notification['is_all_selected']) === true) {
            $events = UsersProduct::select('id')->where('account_id', $accountId)->get();
        } else if ($notification['event_type'] === 'form' && boolval($notification['is_all_selected']) === true) {
            $events = LandingPageForm::select('id')->where('account_id', $accountId)->get();
        } elseif ($notification['event_type'] === 'product' || 'form') {
            $events = NotificationType::select('target_id')->where([
                'notification_id' => $notification['id'],
                'target_type' => $notification['event_type'],
            ])->get();
        }
        return $events;
    }
    //return event id
    public function eventDetails($accountId)
    {
        $notification = $this->notification($accountId);
        $events = $this->eventsId($accountId);
        if ($notification['event_type'] === 'product') {
            $orders = Order::where(['account_id' => $accountId, 'payment_status' => 'Paid'])
                ->where(function ($query) {
                    $query->where('payment_process_status', 'Success')->orWhereNull('payment_process_status');
                })->orderBy('paid_at', 'desc')->limit(20)->get();
            $orderProducts = [];


            foreach ($events as $event) {
                $event_id = boolval($notification['is_all_selected']) === true ? $event->id : $event->target_id;

                foreach ($orders as $order) {
                    $order_id = $order->id;

                    $orderDetail =  Order::find($order_id)->orderDetails->where('users_product_id', $event_id)->first();
                    $productFirstImage = UsersProduct::select('image_url')->where([
                        'account_id' => $accountId, 'id' => $event_id
                    ])->first();

                    $product_path = UsersProduct::where('id', $event_id)->select('path')->first();

                    if (count((array)$orderDetail) > 0) {
                        array_push($orderProducts, (object)[
                            'order_id' => $order_id,
                            'users_product_id' => $orderDetail->users_product_id,
                            'image_url' => $productFirstImage->productImagePath,
                            'paid_at' => $orderDetail->paid_at,
                            'product_name' => $orderDetail->product_name,
                            'product_path' => $product_path->path,
                            'created_at' => $orderDetail->created_at,
                        ]);
                    }
                }
            }
            return $orderProducts;
        } elseif ($notification['event_type'] === 'form') {

            $formsDetail = [];

            foreach ($events as $event) {
                $event_id = boolval($notification['is_all_selected']) === true ? $event->id : $event->target_id;
                $formDetail = LandingPageForm::select('id', 'title')->where([
                    'id'  => $event_id, 'account_id' => $accountId,
                ])->where('submit_count', '>', 0)->first();

                if (count((array)$formDetail) > 0) array_push($formsDetail, $formDetail);
            }

            return $formsDetail;
        }
        return;
    }
    //processed contact
    public function processedContacts($accountId)
    {

        $notificationDetails = $this->notificationDetails($accountId);
        $eventDetails = $this->eventDetails($accountId);
        $funnelId = $this->targetFunnelId($accountId);
        $processedContacts = [];
        if ($notificationDetails['event_type'] === 'product') {
            foreach ($eventDetails as $eventDetail) {
                $order_id = $eventDetail->order_id;
                $processedContact = Order::find($order_id)->processedContact;
                $processedContactId = is_object($processedContact) ? $processedContact->id : '';
                $processedContactName = is_object($processedContact) ? $processedContact->fname : null;
                $processedContactLocation = Order::select('shipping_city', 'shipping_country')->where([
                    'id' => $order_id, 'account_id' => $accountId
                ])->first();

                array_push($processedContacts, (object)[
                    'order_id' => $order_id,
                    'processed_contact_id' => $processedContactId,
                    'fname' => $processedContactName,
                    'city' => $processedContactLocation['shipping_city'],
                    'country' => $processedContactLocation['shipping_country'],
                    'product_id' => $eventDetail->users_product_id,
                    'product_image' => $eventDetail->image_url,
                    'paid_at' => $eventDetail->paid_at,
                    'product_name' => $eventDetail->product_name,
                    'funnel_id' => $funnelId,
                    'url' => "/products/$eventDetail->product_path",
                    'created_at' => $eventDetail->created_at,
                ]);
            }

            return $processedContacts;
        } elseif ($notificationDetails['event_type'] === 'form') {

            foreach ($eventDetails as $eventDetail) {
                $event_id = $eventDetail->id;
                $formId = LandingPageForm::select('id', 'title', 'submit_count')->where([
                    'id'  => $event_id, 'account_id' => $accountId
                ])->first();
                if ($formId->submit_count < 1) {
                    continue;
                };
                $labelId = LandingPageForm::find($event_id)->formLabels->where('account_id', $accountId)->first();
                $processedContactsId = LandingPageForm::find($event_id)->formContents
                    ->where('landing_page_form_label_id', $labelId['id']);

                foreach ($processedContactsId as $processedContactId) {
                    $processedContactName['fname'] = null;
                    $processedContactLocation['city'] = null;
                    $processedContactLocation['country'] = null;

                    if ($processedContactId['processed_contact_id'] !== null) {
                        $processedContactName = ProcessedContact::select('fname')->where([
                            'id' => $processedContactId['processed_contact_id'], 'account_id' => $accountId
                        ])->first();
                        $processedContactLocation = ProcessedContact::find($processedContactId['processed_contact_id'])
                            ->addresses->first()->only(['city', 'country']);
                    }
                    array_push($processedContacts, (object)[
                        'form_id' => $formId['id'],
                        'processed_contact_id' => $processedContactId['processed_contact_id'],
                        'fname' => $processedContactName['fname'],
                        'city' => $processedContactLocation['city'],
                        'country' => $processedContactLocation['country'],
                        'created_at' => $processedContactId['created_at'],
                        'form_title' => $formId['title'],
                        'funnel_id' => $funnelId,
                    ]);
                }
            }
            $accountTimeZone = Account::find($accountId)->timeZone;
            foreach ($processedContacts as $processedContact) {
                $processedContactTime = new Carbon($processedContact->created_at);
                $processedContact->convertTime = $processedContactTime->timezone($accountTimeZone)
                    ->isoFormat('YYYY-MM-D H:mm:s');
            }
            return $processedContacts;
        }
        return;
    }

    public function fetchAllNotification($accountId)
    {

        $notificationDetails = $this->notificationDetails($accountId);

        $notificationContent = (array)[];
        $notificationContent['notification'] = (object)[];
        $notificationContent['eventId'] = [];
        $notificationContent['eventDetails'] = [];
        $notificationContent['processedContacts'] = [];
        $notificationContent['socialProofSetting'] = (object)[];

        if ($notificationDetails !== null) {
            $notificationContent['notification'] = $this->notification($accountId);
            $notificationContent['eventId'] = $this->eventsId($accountId);
            $notificationContent['eventDetails'] = $this->eventDetails($accountId);
            $notificationContent['processedContacts'] = $this->processedContacts($accountId);
            $notificationContent['socialProofSetting'] = $this->socialProofSetting($accountId);
        }
        return response($notificationContent);
    }
}
