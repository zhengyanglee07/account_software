<?php

namespace App\Services;

use App\EcommerceVisitor;
use App\ProcessedContact;
use App\UsersProduct;
use DOMDocument;
use DOMXPath;

/**
 * There's a native MJML component extension in nodeJS,
 * but it can't integrate quite well with our system, hence
 * this class is formed
 *
 * This class comprises parsing/processing of self-made custom-tag
 * from email builder to MJML-compatible tag(s). One of the examples
 * is the conversion of <abandoned-cart-products> to several mjml components.
 *
 * The whole process is performed mainly by using php native DOM parser.
 * Note that this is not purely HTML parsing, but XHTML due to presence
 * of MJML & custom tags.
 *
 * Class MjmlComponentService
 * @package App\Services
 */
class MjmlComponentService
{
    private const PRESENTATIONAL_CART = [
        [
            'qty' => 1,
            'refKey' => '',
            'image_url' => 'https://cdn.hypershapes.com/assets/product-default-image.png',
            'variation' => [],
            'hasVariant' => 0,
            'customization' => [],
            'exampleTitle' => 'example product 1',
            'currency' => 'USD',
            'examplePrice' => 100
        ],
        [
            'qty' => 1,
            'refKey' => '',
            'image_url' => 'https://cdn.hypershapes.com/assets/product-default-image.png',
            'variation' => [],
            'hasVariant' => 0,
            'customization' => [],
            'exampleTitle' => 'example product 2',
            'currency' => 'RM',
            'examplePrice' => 200
        ],
    ];

    /**
     * ----------------------------------------------------------------------------
     * Keep in mind that using Regex to search XHTML string isn't always a good solution.
     * But then, we have no choice now, too much uncertainties to directly use XML parser
     * on MJML string.
     * ----------------------------------------------------------------------------
     *
     * Standard main function for parsing custom tags from now on.
     * Using separation of concern to provide more deterministic behaviour
     * when parsing custom tags.
     *
     * For example, now we only parse a certain relevant part of MJML
     * docs instead of entire MJML. Since XML/HTML/MJML is kinda flexible,
     * you want to have a controllable behaviour when parsing these markup string.
     * Hopefully this will reduce the chance of bugs happened time to time
     * due to XML parsing error.
     *
     * Now it only consists of abandoned-cart-products custom tag. You can include
     * as much match as possible later in the future. Maybe I will make a
     * generalized func for this
     *
     * @param string $mjmlStr MJML formatted string
     * @param string|null $email
     * @return string|string[]|null
     */
    public function parse(string $mjmlStr, ?string $email = '', $accountId = null)
    {
        return preg_replace_callback(
            '|<abandoned-cart-products.*?></abandoned-cart-products>|s',
            function ($matches) use ($email, $accountId) {
                $fullMatch = $matches[0];

                if (!$fullMatch) return '';
                return $this->parseAbandonedCartProducts($fullMatch, $email, $accountId);
            },
            $mjmlStr
        );
    }

    /**
     * Convert <abandoned-cart-products> tag to MJML-compatible tags
     *
     * @param string $mjmlStr
     * @param string|null $email
     * @return string
     */
    private function parseAbandonedCartProducts(string $mjmlStr, ?string $email = '', $accountId = null): string
    {
        $processedContact = ProcessedContact::firstWhere([
            'email' => $email,
            'account_id' => $accountId
        ]);

        if($email) {
            $visitor = EcommerceVisitor::firstWhere('processed_contact_id', $processedContact->id);
            $products = json_decode($visitor->abandonedCart->product_detail ?? null, true) ?? [];
        } else {
            $products = self::PRESENTATIONAL_CART;
        }

        // nothing to show in abandoned cart
        if (count($products) === 0) {
            return $mjmlStr;
        }

        $dom = new DOMDocument();
        $dom->loadXML($mjmlStr);

        $xpath = new DOMXPath($dom);
        $node = $xpath->query('//abandoned-cart-products')[0];

        if (is_null($node)) {
            \Log::error('abandoned-cart-products node not found. Probably something wrong in preg_replace_callback match in parse() function.', [
                'mjmlString' => $mjmlStr,
                'email' => $email
            ]);
            return '';
        }

        // create a group to hold all related elems
        $mjGroup = $dom->createElement('mj-group');
        $mjTable = $dom->createElement('mj-table', '');
        $mjTable->setAttribute('cellpadding', '5');

        foreach ($products as $product) {
            $usersProduct = UsersProduct::firstWhere('reference_key', $product['refKey']);
            $tr = $dom->createElement('tr');

            $imgColTd = $dom->createElement('td');
            $img = $dom->createElement('img');
            $img->setAttribute('src', config('app.url') . $product['image_url']);
            $img->setAttribute('alt', 'product-image');
            $img->setAttribute('width', '50px');
            $img->setAttribute('height', '50px');
            $imgColTd->appendChild($img);
            $tr->appendChild($imgColTd);

            $productDetailTd = $dom->createElement('td');
            $titleLi = $dom->createElement('li');
            $titleLiText = $dom->createTextNode($usersProduct->productTitle ?? ($product['exampleTitle'] ?? 'Undefined name'));
            $titleLi->setAttribute('style', 'font-weight: bold;');
            $titleLi->appendChild($titleLiText);

            $productDetailTd->appendChild($titleLi);

            if ($product['hasVariant'] === 1) {
                foreach ($product['variation'] as $variation) {
                    $variationLi = $dom->createElement('li');
                    $variationLiText = $dom->createTextNode($variation['label'] . ': ' . $variation['value']);
                    $variationLi->appendChild($variationLiText);
                    $productDetailTd->appendChild($variationLi);
                }
            }

            // TODO: customization
//                    if (count($product['customization']) !== 0) {
//                        foreach ($product['customization'] as $customization) {
//                            $customizationLi = $dom->createElement('li');
//                            $customizationLiText = $dom->createTextNode($customization['label']);
//
//                            foreach ($customization['value'] as $cusValue) {
//
//                            }
//                        }
//                    }

            $tr->appendChild($productDetailTd);

            $quantityTd = $dom->createElement('td');
            $quantityTd->appendChild($dom->createTextNode('x ' . $product['qty']));
            $tr->appendChild($quantityTd);

            $totalTd = $dom->createElement('td');
            $totalTd->setAttribute('align', 'right');
            $totalTdText = $dom->createTextNode($product['currency'] ?? '$' . ' ' . ($usersProduct->productPrice ?? $product['examplePrice']) * $product['qty']);
            $totalTd->appendChild($totalTdText);
            $tr->appendChild($totalTd);

            $mjTable->appendChild($tr);
        }

        $cartTitle = $dom->createElement('mj-text');
        $cartTitle->setAttribute('font-weight', '700');
        $cartTitle->appendChild($dom->createTextNode('Your have some items left in cart'));

        $topDivider = $dom->createElement('mj-divider');
        $topDivider->setAttribute('border-width', '1px');
        $topDivider->setAttribute('border-color', 'lightgrey');

        $topSpacer = $dom->createElement('mj-spacer');

        $btmDivider = $dom->createElement('mj-divider');
        $btmDivider->setAttribute('border-width', '1px');
        $btmDivider->setAttribute('border-color', 'lightgrey');

        // container styles
        $mjGroup->setAttribute('width', '100%');
        $mjGroup->setAttribute('container-background-color', $node->getAttribute('container-background-color'));
        $mjGroup->setAttribute('padding', $node->getAttribute('padding'));

        // append all children to group here
        $mjGroup->appendChild($cartTitle);
        $mjGroup->appendChild($topSpacer);
        $mjGroup->appendChild($topDivider);
        $mjGroup->appendChild($mjTable);
        $mjGroup->appendChild($btmDivider);

        // note: recall that returned value is MJML-formatted string despite that function name
        return $dom->saveHTML($mjGroup);
    }

    /**
     * @deprecated
     *
     * Remark: Temporary deprecated, don't use this
     *
     * @param string $mjmlStr
     * @return string
     */
    private function addDefaultStyles(string $mjmlStr): string
    {
        $dom = new DOMDocument();
        $dom->loadXML(html_entity_decode($mjmlStr));

        $xpath = new DOMXPath($dom);
        $mjml = $xpath->query('/mjml')[0];
        $mjBody = $xpath->query('//mj-body')[0];

        // override styles now
        $mjHead = $dom->createElement('mj-head');
        $mjAttributes = $dom->createElement('mj-attributes');
        $mjAll = $dom->createElement('mj-all');
        $mjAll->setAttribute('font-size', '16px');
        $mjAttributes->appendChild($mjAll);
        $mjHead->appendChild($mjAttributes);

        // directly taken from buyer.mjml
        $mjStyle = $dom->createElement('mj-style');
        $styleText = <<<EOL
          li{
              list-style:none;
          }

          @media only screen and (max-width: 760px) {
              /* Force table to not be like tables anymore */
              table, thead, tbody, th, td, tr {
                  display: block !important;
              }

              /* Hide table headers (but not display: none;, for accessibility) */
              thead tr {
                  position: absolute;
                  top: -9999px;
                  left: -9999px;
              }

              td {
                  /* Behave  like a "row" */
                  border: none;
                  position: relative;
                  width:auto;
              }

              td:before {
                  /* Now like a table header */
                  position: absolute;
                  /* Top/left values mimic padding */
                  top: 6px;
                  left: 6px;
                  width: 45%;
                  padding-right: 10px;
                  white-space: nowrap;
              }

              .hyper-logo{
                  width: 200px;
              }
          }
EOL;
        $mjStyleText = $dom->createTextNode($styleText);
        $mjStyle->appendChild($mjStyleText);
        $mjHead->appendChild($mjStyle);
        $mjml->insertBefore($mjHead, $mjBody);

        return $dom->saveHTML();
    }
}
