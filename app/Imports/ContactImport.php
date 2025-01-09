<?php

namespace App\Imports;

use App\ProccessedContact;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ContactImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new ProccessedContact([
            // 'fname'     => 'fname',
            // 'email'    => 'email',
            // 'lname' => 'lname',
        ]);
    }
}
