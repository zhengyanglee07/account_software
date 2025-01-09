<?php

namespace App\Services;

use App\Models\AccessList;

class ProductAccessService
{
    public function __construct(protected $productId = null, protected $contactId = null)
    {
    }

    public function getAccessList()
    {
        $constraints = [];
        if (isset($this->productId)) $constraints['product_id'] = $this->productId;
        if (isset($this->contactId)) $constraints['processed_contact_id'] = $this->contactId;

        return AccessList::with('processedContact:id,email,contactRandomId')->where($constraints);
    }

    public function getAllAccessContactIds()
    {
        return $this->getAccessList()->pluck('processed_contact_id')->toArray();
    }

    public function getPaginatedAccessList()
    {
        $accessList = $this->getAccessList();
        return (new PaginatorService())->getPaginatedData($accessList, null, '/product/access-list/' . $this->productId);
    }
}
