<?php

namespace App\Http\Controllers;

use Auth;
use App\Account;
use App\AccountDomain;
use App\AllTemplate;
use App\EcommerceHeaderFooter;
use App\EcommercePreferences;
use App\Page;
use App\Subscription;
use Inertia\Inertia;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class EcommerceController extends Controller
{
    private function currentAccountId()
    {
        return Auth::user()->currentAccountId;
    }

    private function generateReferenceKey($table)
    {
        do {
            $randomId = random_int(100000000001, 999999999999);
            $condition = DB::table($table)->where('reference_key', $randomId)->exists();
        } while ($condition);

        return $randomId;
    }

    private function generateUniquePath($path)
    {
        $count = 0;
        $path =  preg_replace("/[^a-z0-9-]/", "", strtolower($path));
        do {
            $newPath = $count == 0 ? $path : $path . "-" . $count;
            $isExisted = Page::where([
                'account_id' => $this->currentAccountId(),
                'path' => $newPath,
            ])->exists();
            $count++;
        } while ($isExisted);

        return $newPath;
    }

    public function showHeaderFooterPage()
    {
        $dbHeaders = EcommerceHeaderFooter::ofType(true, $this->currentAccountId())->get();
        $dbFooters = EcommerceHeaderFooter::ofType(false, $this->currentAccountId())->get();

        return Inertia::render(
            'online-store/pages/EcommerceTheme', 
            compact('dbHeaders', 'dbFooters')
        );
    }

    public function showPages()
    {
        $currentAccountId = $this->currentAccountId();
        $dbStoreDomain = AccountDomain::whereType('online-store')->select('type_id', 'is_subdomain', 'domain')->first();
        $dbPages = Page::where([
            'account_id' => $currentAccountId,
            'is_landing' => false,
        ])->select("id", "account_id", "name", "is_published", "path", "reference_key")->get();

        return Inertia::render('online-store/pages/EcommercePages', compact(
            'dbPages',
            'dbStoreDomain'
        ));
    }

    public function editPreferences()
    {
        $environment = app()->environment();
        $timeZone = Auth::user()->currentAccount()->timeZone;
        $dbPreferences =  EcommercePreferences::whereAccountId($this->currentAccountId())->first();

        return Inertia::render('online-store/pages/EcommercePreferences', compact(
            'timeZone',
            'environment',
            'dbPreferences'
        ));
    }

    public function updatePreferences(Request $request)
    {
        $preferences = EcommercePreferences::updateOrCreate(
            [
                'account_id' => $this->currentAccountId()
            ],
            [
                'ga_header' => $request->ga_header,
                'ga_bodytop' => $request->ga_bodytop,
                'has_affiliate_badge' => $request->canDisableBadges ? $request->has_affiliate_badge : 1,
            ]
        );
        return response()->json([
            'status' => 'Success',
            'message' => "Store Preferences Updated Successfully",
            'updatedPreferences' => $preferences,
        ]);
    }

    public function createNewSection(Request $request)
    {
        do {
            $randomId = random_int(100000000001, 999999999999);
            $condition = EcommerceHeaderFooter::where('reference_key', $randomId)->exists();
        } while ($condition);

        $newHeaderFooter = $this->createHeaderFooter(
            $request->newObject,
            $request->pageName,
            $request->sectionType
        );

        return response()->json([
            'type' => $newHeaderFooter->is_header,
            'reference_key' => $newHeaderFooter->reference_key
        ]);
    }

    public function createTheme(Request $request)
    {
        $templateElements = json_decode($request->newObject);
        $header = AllTemplate::find($templateElements->header);
        $page = AllTemplate::find($templateElements->page);
        $footer = AllTemplate::find($templateElements->footer);

        $this->createHeaderFooter($header->template_objects, $request->pageName, $header->type);
        $this->createHeaderFooter($footer->template_objects, $request->pageName, $footer->type);

        Page::create([
            'element' => $page->template_objects,
            'account_id' => $this->currentAccountId(),
            'name' => $request->pageName,
            'path' => $this->generateUniquePath($request->pageName),
            'reference_key' => $this->generateReferenceKey('landingpage'),
            'is_landing' => false,
        ]);
    }

    private function createHeaderFooter($designObject, $name, $sectionType)
    {
        $isActiveSectionExisted = EcommerceHeaderFooter::where([
            'account_id' => $this->currentAccountId(),
            'is_header' => $sectionType === 'header',
            'is_active' => true,
        ])->exists();

        return EcommerceHeaderFooter::create([
            'name' => $name,
            'element' => $designObject,
            'is_header' => $sectionType === 'header',
            'account_id' => $this->currentAccountId(),
            'is_active' => !$isActiveSectionExisted,
            'reference_key' => $this->generateReferenceKey('ecommerce_header_footers'),
        ]);
    }

    public function saveSection(Request $request)
    {
        $pageId = $request->input('pageSettings.id');
        EcommerceHeaderFooter::find($pageId)->update([
            'name' => $request->input('pageSettings.name'),
            'element' => $request->element,
            'is_sticky' => $request->input('pageSettings.is_sticky'),
        ]);

        return response()->json(
            EcommerceHeaderFooter::find($pageId)
        );
    }

    // public function renameSection(EcommerceHeaderFooter $headerFooter, Request $request)
    // {
    // 	$headerFooter->update([
    // 		'name' => $request->newName
    // 	]);

    // 	return response()->json([
    // 		'status' => 'Success',
    // 		'message' => 'Name updated successfully',
    // 		'updatedRecord' => $headerFooter
    // 	]);
    // }

    public function updateStatus(EcommerceHeaderFooter $headerFooter)
    {
        $activeSection = EcommerceHeaderFooter::ofType($headerFooter->is_header, $this->currentAccountId())->where([
            'is_active' => true
        ])->first();

        $headerFooter->update([
            'is_active' => true
        ]);

        if ($activeSection) {
            $activeSection->update([
                'is_active' => false,
            ]);
        }

        return response()->json([
            'status' => "Success",
            'updatedRecords' => EcommerceHeaderFooter::ofType($headerFooter->is_header, $this->currentAccountId())->get()
        ]);
    }

    public function deleteHeaderFooter(EcommerceHeaderFooter $headerFooter)
    {
        $headerFooter->delete();

        return response()->json([
            'status' => 'Success',
            'message' => "{$headerFooter->is_header} {$headerFooter->name} is successfully deleted"
        ]);
    }

    public function createOnlineStoreTheme(Request $request)
    {
        $accountId = $this->currentAccountId();
        $account = Account::find($accountId);

        $templateElements = json_decode($request->newObject);
        $header = AllTemplate::find($templateElements->header);
        $page = AllTemplate::find($templateElements->page);
        $footer = AllTemplate::find($templateElements->footer);

        $this->createHeaderFooter($header->template_objects, 'Main Header', $header->type);
        $this->createHeaderFooter($footer->template_objects, 'Main Footer', $footer->type);

        $landingPage = Page::find($account->domains->first()->type_id);
        $landingPage->update(['element' => $page->template_objects]);
    }
}
