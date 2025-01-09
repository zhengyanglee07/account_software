<?php

namespace App\Http\Requests;

use App\Traits\AuthAccountTrait;
use Illuminate\Foundation\Http\FormRequest;
use App\Services\Checkout\FormServices;
use Auth;

class CheckoutFormRequest extends FormRequest
{
    use AuthAccountTrait;

    protected $ecommercePreference;
    protected $formService;

    protected $rules = [
        'string' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email:rfc,dns', 'max:255'],
        'phoneNumber' => ['required', 'regex:/\+(9[976]\d|8[987530]\d|6[987]\d|5[90]\d|42\d|3[875]\d|2[98654321]\d|9[8543210]|8[6421]|6[6543210]|5[87654321]|4[987654310]|3[9643210]|2[70]|7|1)\W*\d\W*\d\W*\d\W*\d\W*\d\W*\d\W*\d\W*\d\W*(\d{1,2})$/'],
        'zipCode' => ['required', 'regex:/(?i)^[a-z0-9][a-z0-9\- ]{0,10}[a-z0-9]$/']
    ];

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $formInput = $this->request->all();
        $isFunnel = !empty($formInput['isFunnel']);
        $deliveryMethods = $formInput['delivery']['type'] ?? null;
        $hasAddBillingAddress = (bool)optional($formInput['shipping'])['hasBillingsAddress'];
        $this->formService = new FormServices($deliveryMethods, $hasAddBillingAddress);

        if ($isFunnel) {
            $this->formService->setPreferences($formInput);
        }

        $rules =  array_merge($this->customerInfoRules(), $this->shippingRules(), $this->billingRules());
        return $rules;
    }

    public function customerInfoRules(): array
    {
        $formInput = $this->request->all();
        $isAuthenticated = Auth::guard('ecommerceUsers')->check();

        $rules = [
            'customerInfo.email' => $this->rules['email'],
            'customerInfo.fullName' => $this->rules['string'],
            'customerInfo.phoneNumber' => $this->rules['phoneNumber'],
        ];
        if ($isAuthenticated || !$this->formService->isFullnameRequired()) unset($rules['customerInfo.fullName']);
        if ($isAuthenticated || !$this->formService->isEmailAddressRequired()) unset($rules['customerInfo.email']);
        if (!$this->formService->isMobileNumberRequired()) unset($rules['customerInfo.phoneNumber']);

        if ($this->formService->isEitherEmailOrMobileNumber()) {
            $customerInfo = $formInput['customerInfo'];
            $rules['customerInfo.email'] = $this->rules['email'];
            $rules['customerInfo.phoneNumber'] = $this->rules['phoneNumber'];
            // Skip validation for empty field if either one field is filled
            if (isset($customerInfo['email']) || isset($customerInfo['phoneNumber'])) {
                if (!isset($customerInfo['email'])) unset($rules['customerInfo.email']);
                if (!isset($customerInfo['phoneNumber'])) unset($rules['customerInfo.phoneNumber']);
            }
        }
        return $rules;
    }

    public function shippingRules(): array
    {
        $rules = [];
        if ($this->formService->isShippingAddressRequired()) {
            $rules = [
                'shipping.address' => $this->rules['string'],
                'shipping.city' => $this->rules['string'],
                'shipping.companyName' => $this->rules['string'],
                'shipping.country' => $this->rules['string'],
                'shipping.fullName' => $this->rules['string'],
                'shipping.phoneNumber' => $this->rules['phoneNumber'],
                'shipping.state' => $this->rules['string'],
                'shipping.zipCode' => $this->rules['zipCode'],
            ];
            if (!$this->formService->isCompanyNameRequired())  unset($rules['shipping.companyName']);
        }
        return $rules;
    }

    public function billingRules(): array
    {
        $rules = [];
        if ($this->formService->isBillingAddressRequired() && $this->formService->hasAddBillingAddress) {
            $rules = [
                'billing.address' => $this->rules['string'],
                'billing.city' => $this->rules['string'],
                'billing.companyName' => $this->rules['string'],
                'billing.country' => $this->rules['string'],
                'billing.fullName' => $this->rules['string'],
                'billing.state' => $this->rules['string'],
                'billing.zipCode' => $this->rules['zipCode']
            ];
            if (!$this->formService->isCompanyNameRequired()) unset($rules['billing.companyName']);
        }
        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'customerInfo.email' => 'email address',
            'customerInfo.fullName' => 'full name',
            'customerInfo.phoneNumber' => 'phone number',
            'shipping.address' => 'full address',
            'shipping.city' => 'city',
            'shipping.companyName' => 'company name',
            'shipping.country' => 'country',
            'shipping.fullName' => 'full name',
            'shipping.phoneNumber' => 'phone number',
            'shipping.state' => 'state',
            'shipping.zipCode' => 'zip code',
            'billing.address' => 'full address',
            'billing.city' => 'city',
            'billing.companyName' => 'company name',
            'billing.country' => 'country',
            'billing.fullName' => 'full name',
            'billing.state' => 'state',
            'billing.zipCode' => 'zip code',
        ];
    }
}
