<?php

namespace App\Exports;

use Auth;
use App\LandingPageFormLabel;
use App\LandingPageFormContent;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class FormContentsExport implements FromView
{
    public function __construct(string $formId)
    {
        $this->formId = $formId;
    }

    public function view(): View
    {
        $formContents = [];
        
        $conditions = [
            'account_id' => Auth::user()->currentAccountId,
            'landing_page_form_id' => $this->formId
        ];

        $formLabels = LandingPageFormLabel::where($conditions)->pluck(
            'landing_page_form_label',
            'id'
        );

        $formSubmissions = LandingPageFormContent::where($conditions)->get()->groupBy('reference_key');

        foreach ($formSubmissions->values() as $submissions) {
            $row = [];
            foreach ($submissions as $submission) {
                $row[$submission['landing_page_form_label_id']] = $submission['landing_page_form_content'];
            }
            $row['Submitted At'] = $submission['created_at'];
            array_push($formContents, $row);
        }

        return view('exports.formContents', compact(
            'formLabels',
            'formContents',
        ));
    }
}
