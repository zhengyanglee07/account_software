<?php

namespace App\Http\Controllers;

use App\Account;
use App\AccountOauth;
use App\IntegrationSetting;
use App\Services\AdAudiences\GoogleCustomerMatch;
use Illuminate\Support\Facades\Http;
use App\Traits\AuthAccountTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class IntegrationController extends Controller
{
    use AuthAccountTrait;

    public function settings()
    {
        $currentAccountId = $this->getCurrentAccountId();

        // TODO: move to ajax call
        $accountOauths = AccountOauth
            ::where('account_id', $currentAccountId)
            ->with(['socialMediaProvider', 'account'])
            ->get()
            ->map(function ($accountOauth) use ($currentAccountId) {
                $provider = $accountOauth->socialMediaProvider->name;
                $identifierId = null;

                switch (strtolower($provider)) {
                    case 'google':
                        $identifierId = (new GoogleCustomerMatch(
                            $accountOauth->refresh_token,
                            $currentAccountId
                        ))->getClientCustomerId();
                        break;
                    case 'facebook':
                        // TODO
                        break;
                    default:
                        break;
                }

                return [
                    'id' => $accountOauth->id,
                    'provider' => $provider,
                    'identifier' => $identifierId
                ];
            });

// NOTE: for testing purpose, don't remove
//        $accountOauths = [
//            [
//                'id' => 2,
//                'provider' => 'google',
//                'identifier' => 123232222,
//            ],
//            [
//                'id' => 3,
//                'provider' => 'facebook',
//                'identifier' => 1232
//            ]
//        ];
        $allIntegrationSettings = IntegrationSetting
        ::where('account_id',$this->getCurrentAccountId())
        ->get();
        return view('settings.integrationSetting', compact('accountOauths','allIntegrationSettings'));
    }

    /**
     * Disconnect social media, aka ads account from hypershapes.
     * This is achieved by detaching associated segment account oauth,
     * and finally delete saved tokens in database. With this, hypershapes
     * won't be able to perform action on behalf of users to social media
     * again.
     *
     * @param int $id AccountOauth id
     * @return JsonResponse
     */
    public function disconnect($id): JsonResponse
    {
        $accountOauth = AccountOauth::findOrFail($id);

        // finally delete account oauth
        try {
            $accountOauth->delete();
        } catch (\Exception $e) {
            \Log::error($e);

            return response()->json([
                'status' => 'failed',
            ], 500);
        }

        return response()->json(['status' => 'success']);
    }

    // public function getIntegrationSettings($type)
    // {
    //     $accountId = $this->getCurrentAccountId();
    //     $defaultIntegrationSettings = (object)[
    //         'id' => null,
    //         'display_name' => '',
    //         'webinar_type' => $type,
    //         'api_key' => null,
    //         'reference_key' => null,
    //     ];
    //     $integrationSetting = is_numeric($type)
    //     ? IntegrationSetting::where(['account_id' => $accountId, 'reference_key' => $type])->first()
    //     : $defaultIntegrationSettings;

    //     return view('settings.addIntegrationSetting',compact('integrationSetting'));
    // }

    public function saveIntegrationSettings(request $request)
    {
        $accountId = $this->getCurrentAccountId();
        $status = ['status' => 'success'];
        $reference_key = $request->reference_key ?? $this->getRandomId('integration_settings','reference_key');
        try{
            integrationSetting::updateOrCreate(
                [
                    'reference_key' => $reference_key,
                    'account_id' => $accountId,
                    'webinar_type' => $request->webinar_type
                ],
                [
                    'display_name' => $request->display_name,
                    'api_key' => $request->api_key,
                ]
            );
        }
        catch (\Exception $e)
        {
            \Log::error($e);
            $status = ['status' => 'fail'];
        }
        return response()->json($status);
    }

    public function deleteIntegrationSettings($refKey)
    {
        try{
            IntegrationSetting::where(
                [
                    'account_id' => $this->getCurrentAccountId(),
                     'reference_key' => $refKey,
                ]
            )->delete();
            $integrationSettings = IntegrationSetting
            ::where('account_id',$this->getCurrentAccountId())
            ->get();
            $status = [
                'status' => 'success',
                 'integrationSettings' => $integrationSettings
            ];
        }
        catch (Exception $e){
            \Log::error($e);
            $status = ['status' => 'fail'];
        }
        return response()->json($status);
    }

    public function getAllIntegration()
    {
        $account_id = $this->getCurrentAccountId();
        $intergration = IntegrationSetting
        ::where('account_id',$account_id)
        ->select('display_name','webinar_type','reference_key')
        ->get();
        return response()->json(['integrations' => $intergration]);
    }

    private function getSelectedIntegration($refKey)
    {
        $account_id = $this->getCurrentAccountId();
        $integration = IntegrationSetting::where(
            [
                'account_id' => $account_id,
                'reference_key' => $refKey,
            ]
        )->first();
        return $integration;
    }

    private function getRequestUrl($webinar_type,$isSingle,$isRegister = false)
    {
        $webinar_type = str_replace(' ','',$webinar_type);
        $webinar = $isSingle ? 'webinar' : 'webinars';
        return $isRegister
        ? 'https://api.webinarjam.com/'.$webinar_type.'/register'
        : 'https://api.webinarjam.com/'.$webinar_type.'/'.$webinar;
    }

    public function getAvailableWebinar($refKey)
    {
        $webinars = [];
        $errors = [];
        $integration = $this->getSelectedIntegration($refKey);
        $url = $this->getRequestUrl($integration->webinar_type,false);
        $response = Http::post($url, [
            'api_key' => $integration->api_key,
        ]);
        $response = $response->json();
        $response['status'] === 'success'
            ? $webinars = $response['webinars']
            : $errors = $response['errors'];
        return response()->json([
            'status' => $response['status'],
            'webinars' => $webinars,
            'errors' => $errors,
        ]);
    }

    private function getSelectedWebinar($refKey,$webinarId)
    {
        $integration = $this->getSelectedIntegration($refKey);
        $url = $this->getRequestUrl($integration->webinar_type,true);
        $response = Http::post($url, [
            'api_key' => $integration->api_key,
            'webinar_id' => $webinarId,
        ]);
        return $response->json();
    }

    public function getWebinarSession(request $request)
    {
        $selectedWebinar = $this->getSelectedWebinar($request->referenceKey,$request->webinarId);
        return response()->json([
            'webinarSessions' => $selectedWebinar['webinar']['schedules'],
            'timezone' => $selectedWebinar['webinar']['timezone']
        ]);
    }

    public function register($request)
    {
        $selectedWebinar = $this->getSelectedWebinar($request->integrationRefKey,$request->selectedWebinar);
        $integration = $this->getSelectedIntegration($request->integrationRefKey);
        $url = $this->getRequestUrl($integration->webinar_type,true,true);
        $response = Http::post($url, [
            'api_key' => $integration->api_key,
            'webinar_id' => $request->selectedWebinar,
            'schedule' => (int)$request->selectedSession,
            'first_name' => $this->filteredField('First Name',$request->fields),
            'last_name' => $this->filteredField('Last Name',$request->fields),
            'email' => $this->filteredField('Email Address',$request->fields),
            'phone' => $this->filteredField('Phone Number',$request->fields),
        ]);
        $user = $response->json()['user'];
        $originalDate = $this->filteredField('Webinar Session',$request->fields);
        $timezone = $this->filteredField('Timezone',$request->fields);
        $date = Carbon::parse($originalDate)->isoformat('dddd, MMMM D, YYYY h:mm A');
        return [
            'live_room_url' => $user['live_room_url'],
            'replay_room_url' => $user['replay_room_url'],
            'thank_you_url' => $user['thank_you_url'],
            'date' => [
                'webinar' => $selectedWebinar['webinar']['name'],
                'description' => $selectedWebinar['webinar']['description'],
                'formated' => $date,
                'original' => $originalDate,
                'timezone' => $timezone,
            ]
        ];
    }
    private function filteredField($type,$field)
    {
        $fieldLists = collect($field);
        $this->type = $type;
        $filteredField = $fieldLists->first(function ($value, $key) {
            return $value['label'] === $this->type;
        });
        return $filteredField === null ? '' : $filteredField['value'];
    }
}
