<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;

use Auth;
use App\Account;
use App\AccountDomain;
use App\AllTemplate;
use App\Category;
use App\EcommerceNavBar;
use App\EcommerceHeaderFooter;
use App\EcommercePreferences;
use App\funnel;
use App\Page;
use App\Location;
use App\MiniStore;
use App\MiniStoreTheme;
use App\Popup;
use App\Models\StoreTheme;
use App\peopleCustomFieldName;
use App\ProcessedTag;
use App\UserTemplate;
use App\UsersProduct;

use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use Spatie\Sitemap\SitemapIndex;

use App\Traits\PublishedPageTrait;

class BuilderController extends Controller
{
    use PublishedPageTrait;

    /**
     * Return the settings of page / section design
     *
     * @param string $pageType
     * @param int $refkey
     */
    private function pageDesign($pageType, $refKey)
    {
        switch ($pageType) {
            case "header":
            case "footer":
                $builderSettings = EcommerceHeaderFooter::where([
                    'is_header' => $pageType === 'header',
                    'reference_key' => $refKey,
                ])->firstOrFail();
                break;
            case "page":
            case "landing":
                $builderSettings = Page::where('reference_key', $refKey)->firstOrFail();
                break;
            case "userTemplate":
                $builderSettings = UserTemplate::findOrFail($refKey);
                break;
            case 'template':
                $builderSettings = AllTemplate::findOrFail($refKey);
                break;
            case 'popup':
                $builderSettings = Popup::where('reference_key', $refKey)->firstOrFail();
                break;
            default:
                $builderSettings = null;
        }

        return $builderSettings;
    }

    /**
     * Turn path into lowercase, remove special characters other than - and
     * replace space with -
     * @param string $path
     * @param string
     */
    private function sanitizePath($path)
    {
        $path = preg_replace("/[^a-z0-9- ]/", "", strtolower($path));
        return str_replace(' ', '-', $path);
    }

    /**
     * Check whether the user-entered path existed and 
     * appended duplicated count if duplicated
     * @param string $path
     * @param string
     */
    public function generateUniquePath($path, $accountId)
    {
        $count = 0;
        $path = $this->sanitizePath($path);
        do {
            $newPath = $count == 0 ? $path : $path . "-" . $count;
            $isExisted = Page::where([
                'account_id' => $accountId,
                'path' => $newPath,
            ])->exists();
            $count++;
        } while ($isExisted);

        return $newPath;
    }

    /**
     * Edit the builder design
     *
     * @param string $pageType
     * @param int $refkey
     */
    public function edit($pageType, $refKey)
    {
        return redirect("/builder/$pageType/$refKey/editor");
    }

    /**
     * Update builder design and metadata.
     *
     * @param \Illuminate\Http\Request  $request
     * @param string  $pageType
     * @return Illuminate\Http\Response
     */
    public function update(Request $request, $pageType)
    {
        $id = $request->id;
        $design = null;
        $accountId = Auth::user()->currentAccountId;

        switch ($pageType) {
            case "header":
            case "footer":
                $design = EcommerceHeaderFooter::whereAccountId($accountId)->findOrFail($id);
                $updatedSection = $design->update([
                    'name' => $request->name,
                    'element' => $request->element,
                    'is_sticky' => $request->is_sticky,
                ]);
                break;
            case "page":
            case "landing":
                $design = Page::whereAccountId($accountId)->findOrFail($id);
                $pagePath = $request->path ?? $design->path;
                $newPagePath =
                    $design->path == $this->sanitizePath($pagePath)
                    ? $pagePath
                    : $this->generateUniquePath($pagePath, $accountId);
                $updatedSection = $design->update([
                    'element' => $request->element,
                    'name' => $request->name,
                    'path' => $newPagePath,
                    'seo_title' => $request->seo_title,
                    'seo_meta_description' => $request->seo_meta_description,
                    'fb_title' => $request->fb_title,
                    'fb_description' => $request->fb_description,
                    'fb_image' => $request->fb_image,
                    'tracking_header' => $request->tracking_header,
                    'tracking_bodytop' => $request->tracking_bodytop,
                    'page_width' => $request->page_width,
                    'fonts' => $request->fonts,
                ]);
                break;
            case 'popup':
                $design = Popup::whereAccountId($accountId)->findOrFail($id);
                $updatedSection = $design->update([
                    'name' => $request->name ?? $design->name,
                    'is_publish' => $request->is_publish ?? false,
                    'configurations' => $request->configurations,
                    'design' => $request->design,
                ]);
                break;
        }

        if ($request->theme) {
            StoreTheme::updateOrCreate(
                [
                    'account_id' => $accountId,
                ],
                [
                    'styles' => $request?->theme,
                ]
            );
        }

        return response()->json([
            'isSectionUpdated' => $updatedSection,
            'updateDesign' => $design ?? null,
        ]);
    }

    /**
     * Preview builder design
     *
     * @param \Illuminate\Http\Request $request
     * @param int  $refKey
     */
    public function preview($pageType, $refKey)
    {
        return redirect("/builder/$pageType/$refKey/preview");
    }

    /**
     * Show the published builder's page
     *
     * @param \Illuminate\Http\Request $request
     */
    public function showPublishPage(Request $request)
    {
        $funnel = null;
        $path = $request->path();
        $publishPageBaseData = $this->getPublishedPageBaseData();

        $domain = $publishPageBaseData['domain'];
        if (!$domain) abort(404);

        if ($path === '/') {
            switch ($domain->type) {
                case 'online-store':
                    $page = Page::ofPublished()->findOrFail($domain->type_id);
                    $pageType = 'page';
                    break;
                case 'mini-store':
                    $miniStoreTheme = MiniStoreTheme::firstOrFail();
                    $page = MiniStore::whereAccountId($domain->account_id)->firstOrFail();
                    $page['element'] = $miniStoreTheme->landing_page;
                    $pageType = 'mini-store';
                    break;
                case 'funnel':
                    $funnel = funnel::findOrFail($domain->type_id);
                    $page = $funnel->landingpages()->where('is_published', true)->orderBy('index')->firstOrFail();
                    $pageType = 'landing';
                    break;
                default:
                    abort(404);
            }
        } else {
            $page = Page::ofPublished()->ofPath($domain->account_id, $path)->firstOrFail();
            if ($page->is_landing) {
                $funnel = funnel::find($page->funnel_id) ?? null;
            }
            $pageType = $page->is_landing
                ? 'landing'
                : 'page';
        }

        if ($domain->type !== 'mini-store' || $path !== '/') {
            $funnel['landingpages'] = funnel::funnelLandingPages($funnel) ?? null;
        }

        $popups = Popup::where('account_id', $domain->account_id)->get();

        return Inertia::render('builder/pages/BasePublish', array_merge(
            $publishPageBaseData,
            [
                'isPublish' => true,
                'pageType' => $pageType,
                'funnel' => $funnel ?? null,
                'page' => $page ?? null,
                'popups' => $popups,
            ]
        ));
    }

    public function generateSitemapIndex(Request $request)
    {
        $sitemapIndex = SitemapIndex::create()
            ->add('/sitemap_products.xml')
            ->add('/sitemap_categories.xml')
            ->add('/sitemap_pages.xml');

        return response($sitemapIndex, 200)->header('Content-Type', 'application/xml');
    }

    public function generateSitemap(Request $request)
    {
        if ($request->getHost() === config('app.domain')) {
            return redirect('/');
        }
        $records = [];
        $sitemap = Sitemap::create();
        $domain = AccountDomain::getDomainRecord();
        $accountId = $domain['account_id'];

        switch ($request->path()) {
            case 'sitemap_products.xml':
                $records = UsersProduct::where([
                    'account_id' => $accountId,
                    'status' => 'active',
                ])->select(
                    'path',
                    'title',
                    'image_url',
                    'updated_at',
                )->get();
                break;
            case 'sitemap_categories.xml':
                $records = Category::where('account_id', $accountId)->with(array('products' => function ($query) {
                    $query->where('status', 'active');
                }))->select(
                    'path',
                    'updated_at',
                )->get();
                break;
            case 'sitemap_pages.xml':
                $records = Page::where([
                    'account_id' => $accountId,
                    'is_landing' => false,
                    'is_published' => true,
                ])->select(
                    'path',
                    'updated_at',
                )->get();
                break;
        }

        foreach ($records as $record) {
            switch ($request->path()) {
                case 'sitemap_products.xml':
                    $sitemapURL = URL::create("/products/" . $record->path)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                        ->setPriority(0.6)
                        ->addImage($record->productImagePath, $record->productTitle);
                    break;
                case 'sitemap_categories.xml':
                    $sitemapURL = URL::create("/categories/" . $record->path)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.8);
                    break;
                case 'sitemap_pages.xml':
                    $sitemapURL = URL::create($record->path)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                        ->setPriority(0.5);
                    break;
            }

            $sitemapURL->setLastModificationDate($record['updated_at']);

            $sitemap->add($sitemapURL);
        }

        return response($sitemap, 200)->header('Content-Type', 'application/xml');
    }

    public function showPricingPlan()
    {
        $publishPageBaseData = $this->getPublishedPageBaseData();

        return Inertia::render('subscription/pages/PublishedPricingPlan', array_merge($publishPageBaseData, []));
    }

    public function getRandomId($table, $type)
    {
        $condition = true;
        do {
            return $randomId = random_int(100000000001, 999999999999);
            $condition = DB::table($table)->where($type, $randomId)->exists();
        } while ($condition);
    }
}
