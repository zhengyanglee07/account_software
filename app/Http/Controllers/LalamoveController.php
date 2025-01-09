<?php

namespace App\Http\Controllers;

use App\Jobs\UpdateLalamoveOrderDetails;
use App\LalamoveDeliveryOrder;
use App\Services\LalamoveAPIService;
use App\Services\Checkout\ShippingService;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Traits\AuthAccountTrait;


class LalamoveController extends Controller
{
    use AuthAccountTrait;

    private LalamoveAPIService $lalamoveAPIService;

    public function __construct(LalamoveAPIService $lalamoveAPIService)
    {
        $this->lalamoveAPIService = $lalamoveAPIService;
    }

    /**
     * Get quotation via lalamove API.
     *
     * For all available attrs in $request->fields please refer
     * to LalamoveAPIService@getQuotation
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \JsonException
     */
    public function quotationChecking(Request $request): JsonResponse
    {
        $accountId = $this->getCurrentAccountId();
        $shippingService = new ShippingService();
        $fields = $request->fields ?? (array)$shippingService->getShippingParams(ShippingService::LALAMOVE_APP);

        try {
            $quotations = $this->lalamoveAPIService->getQuotation($accountId, $fields);
        } catch (HttpException $ex) {
            // rethrow every HTTP exception (abort)
            throw $ex;
        } catch (ClientException $ex) {
            // for all available codae and its msg, kindly refer to
            // https://developers.lalamove.com/?plaintext--sandbox#get-quotation
            $resBody = json_decode($ex->getResponse()->getBody(), true, 512, JSON_THROW_ON_ERROR);
            $msg = $resBody['message'];

            // handle Lalamove 409 conflict related err with meaningful msg
            if ($msg === 'ERR_OUT_OF_SERVICE_AREA') {
                conflictAbort('Address is out of Lalamove service area');
            }

            if ($msg === 'ERR_INVALID_PHONE_NUMBER') {
                conflictAbort('Invalid phone number format');
            }

            if ($msg === 'ERR_DELIVERY_MISMATCH') {
                $debugMsg = 'Stops and Deliveries mismatch.';
                \Log::error($debugMsg, [
                    'account_id' => $accountId,
                    'fields' => $fields
                ]);

                conflictAbort('Stops and Deliveries mismatch. Please contact our support team');
            }

            if ($msg === 'ERR_INSUFFICIENT_STOPS') {
                conflictAbort('Not enough stops, number of stops should be between 2 and 10');
            }

            if ($msg === 'ERR_TOO_MANY_STOPS') {
                conflictAbort('Reached maximum stops, Number of stops should be between 2 and 10');
            }

            if ($msg === 'ERR_INVALID_PAYMENT_METHOD') {
                conflictAbort('Invalid payment method. Kindly settle this with Lalamove or contact our support team');
            }

            if ($msg === 'ERR_INVALID_LOCALE') {
                conflictAbort('Invalid locale in your location');
            }

            if ($msg === 'ERR_INVALID_COUNTRY') {
                conflictAbort('Invalid country. Lalamove Malaysia can only be used in Malaysia');
            }

            if ($msg === 'ERR_INVALID_SCHEDULE_TIME') {
                conflictAbort('Scheduled delivery is at the past');
            }

            if ($msg === 'ERR_INVALID_SERVICE_TYPE') {
                \Log::error('Invalid serviceType', [
                    'account_id' => $accountId,
                    'fields' => $fields
                ]);

                conflictAbort('Invalid service type provided. Kindly contact our support team');
            }

            if ($msg === 'ERR_INVALID_SPECIAL_REQUEST') {
                \Log::error('Invalid specialRequests. Make sure that special requests match with selected Service types.', [
                    'account_id' => $accountId,
                    'fields' => $fields
                ]);

                conflictAbort('No such special requests');
            }

            if ($msg === 'ERR_REVERSE_GEOCODE_FAILURE') {
                \Log::error('Failed to reverse geocode', [
                    'account_id' => $accountId,
                    'fields' => $fields
                ]);

                conflictAbort('Fail to reverse from address to location');
            }

            // generic client exception
            abort(500, 'Unexpected error: ' . $ex->getMessage());
        } catch (\Throwable $e) {
            // generic unknown error
            \Log::error('Unknown error in getting Lalamove quotation: ', [
                'msg' => $e->getMessage()
            ]);

            abort(500, 'Unexpected error: ' . $e->getMessage());
        }

        return response()->json([
            'quotations' => $quotations
        ]);
    }

    public function orderDetailsChecking(LalamoveDeliveryOrder $lalamoveDeliveryOrder)
    {
        try {
            $orderDetails = $this->lalamoveAPIService->getOrderDetails(
                $lalamoveDeliveryOrder->order_ref,
                $lalamoveDeliveryOrder->lalamoveQuotation->order->account_id
            );

            dispatch(new UpdateLalamoveOrderDetails($lalamoveDeliveryOrder, $orderDetails));
        } catch (ClientException $ex) {
            $resBody = json_decode($ex->getResponse()->getBody(), true, 512, JSON_THROW_ON_ERROR);
            conflictAbort($resBody['message'] ?? 'Unknown HTTP error');
        } catch (\Throwable $e) {
            // generic unknown error
            \Log::error('Unknown error in getting Lalamove order details: ', [
                'msg' => $e->getMessage(),
                'lalamove_delivery_order_id' => $lalamoveDeliveryOrder->id
            ]);

            abort(500, 'Unexpected error: ' . $e->getMessage());
        }

        return response()->json([
            'details' => $orderDetails
        ]);
    }

    public function driverDetailsChecking(LalamoveDeliveryOrder $lalamoveDeliveryOrder, string $driverId)
    {
        try {
            $driverDetails = $this->lalamoveAPIService->getDriverDetails(
                $lalamoveDeliveryOrder->order_ref,
                $driverId,
                $lalamoveDeliveryOrder->lalamoveQuotation->order->account_id
            );
        } catch (ClientException $ex) {
            $resBody = json_decode($ex->getResponse()->getBody(), true, 512, JSON_THROW_ON_ERROR);
            conflictAbort($resBody['message'] ?? 'Unknown HTTP error');
        } catch (\JsonException $e) {
            conflictAbort('Error in decoding JSON. Please contact support');
        }

        return response()->json([
            'driverDetails' => $driverDetails
        ]);
    }
}
