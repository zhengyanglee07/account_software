<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\EcommerceAddressBook;
use App\Traits\PublishedPageTrait;
use Inertia\Inertia;

class EcommerceAddressBookController extends Controller
{

    use PublishedPageTrait;

    public function user()
    {
        return Auth::guard('ecommerceUsers')->user();
    }

    public function showAddressBook()
    {
        $publishPageBaseData = $this->getPublishedPageBaseData();
        $pageName = 'Address Book Page';
        $addressBook = $this->user()->addressBook[0];
        
        return Inertia::render(
            'customer-account/pages/Address',
            array_merge(
                $publishPageBaseData, 
                compact('addressBook', 'pageName')
            ),
        );
    }

    public function saveAddressBook(Request $request)
    {
        EcommerceAddressBook::find($request->id)->update($request->form);
        return response()->json('success');
    }
}
