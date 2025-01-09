<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AccessList;
use App\Traits\AuthAccountTrait;
use App\UsersProduct;
use Illuminate\Http\Request;

class EcommerceVirtualAssetController extends Controller
{
    use AuthAccountTrait;

    public function getVirtualAssets(Request $request)
    {
        $processedContact = $request->user()->processedContact;
        return response()->json([
            'assets' =>  $processedContact->accessLists->load('products'),
        ]);
    }

    public function recordAccess(Request $request)
    {
        AccessList::find($request->id)->update(['last_access_at' =>  date('Y-m-d H:i:s')]);
    }
}
