<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AccessList;
use App\Services\ProductAccessService;
use Illuminate\Http\Request;

class ProductAccessController extends Controller
{
    public function getAccessList(Request $request)
    {
        return (new ProductAccessService($request->productId))->getPaginatedAccessList();
    }

    public function addAccess(Request $request)
    {
        $existingAccessList = AccessList::withTrashed()->where('product_id', $request->productId)->pluck('processed_contact_id')->toArray();
        $removedAccess = array_values(array_diff($existingAccessList, $request->accessListIds));
        $addedAccess = array_values(array_diff(array_merge($existingAccessList, $request->accessListIds), $removedAccess));
        foreach ($addedAccess as $id) {
            $accessList =   AccessList::withTrashed()->updateOrCreate(
                [
                    'product_id' => $request->productId,
                    'processed_contact_id' => $id,
                ],
                [
                    'is_active' => 1,
                    'join_at'  => date('Y-m-d H:i:s'),
                ]
            );
            if ($accessList->trashed()) $accessList->restore();
        }

        AccessList::whereIn('processed_contact_id', $removedAccess)?->each(function ($student) {
            $student->delete();
        });

        return response()->json([
            'accessList' => (new ProductAccessService($request->productId))->getPaginatedAccessList()
        ]);
    }

    public function removeAccess(Request $request)
    {
        AccessList::find($request->query('alid'))->delete();
        return response()->json([
            'accessList' => (new ProductAccessService($request->productId))->getPaginatedAccessList()
        ]);
    }
}
