<?php

return [
    /*
    |--------------------------------------------------------------------------
    | First condition
    |--------------------------------------------------------------------------
    |
    | As the property name suggests, this value is the first condition's
    | name in the people filter. As of December 2020, there are 7 conditions
    | as shown below. You can add new value to the bottom as you like, just
    | remember that this value is CASE SENSITIVE, due to historical reason.
    |
    */
    "firstCondition" => [
        "Total Sales",
        "Tags",
        "Custom Field",
        "People Profile",
        'Site Activity',
        "Form Submission",
        "Purchases",
        "Orders",
        "Average Order Value",
        "Marketing Email Status"
    ],

    /*
    |--------------------------------------------------------------------------
    | Email sub
    |--------------------------------------------------------------------------
    |
    | As of Dec 2020, this property is deprecated temporarily. It might be added
    | back later, depends on your boss
    |
    */
    "emailSub" => [
        "has received",
        "has not received",
        "has opened",
        "has not opened",
        "has clicked",
        "has not clicked"
    ],

    /*
    |--------------------------------------------------------------------------
    | Total sales sub
    |--------------------------------------------------------------------------
    |
    | This value is the options for total sales second dropdown (refer to frontend).
    | It consists of some kind of operator-like action to measure value, e.g.
    | greater than or equal to.
    |
    */
    "totalSalesSub" => [
        "is greater than or equal to",
        "is less than or equal to",
        "equals",
        "between"
    ],

    /*
    |--------------------------------------------------------------------------
    | Purchases sub
    |--------------------------------------------------------------------------
    |
    | Similar to total sales sub, this value is the options for purchases second
    | dropdown (refer to frontend). It consists of merely two values as shown
    | in array below.
    |
    */
    "purchasesSub" => [
        "have been made",
        "have not been made"
    ],

    /*
    |--------------------------------------------------------------------------
    | Tag sub
    |--------------------------------------------------------------------------
    |
    | Similar to subs above, this value is the options for tags second dropdown
    | (refer to frontend). It consists of merely two values; contain/do not contain.
    |
    */
    "tagSub" => [
        "contain",
        "do not contain"
    ],

    /*
    |--------------------------------------------------------------------------
    | Orders sub
    |--------------------------------------------------------------------------
    |
    | Exactly like total sales sub. They could be grouped together in the future.
    |
    */
    "ordersSub" => [
        "is greater than or equal to",
        "is less than or equal to",
        "equals",
        "between"
    ],

    /*
    |--------------------------------------------------------------------------
    | AOV sub
    |--------------------------------------------------------------------------
    |
    | Exactly like total sales sub. They could be grouped together in the future.
    |
    */
    "aovSub" => [
        "is greater than or equal to",
        "is less than or equal to",
        "equals",
        "between"
    ],

    /*
    |--------------------------------------------------------------------------
    | Marketing Email Status sub
    |--------------------------------------------------------------------------
    | Somewhat like tagSub
    |
    */
    "marketingEmailStatusSub" => [
        'is',
        'is not'
    ],

    /*
    |--------------------------------------------------------------------------
    | Site activity sub
    |--------------------------------------------------------------------------
    |
    | Temporarily deprecated, you can use this if site activity filter comes back.
    |
    */
    "siteActivitySub" => [
        // "has viewed a product",
        // "has not viewed a product",
        "has visited a page",
        "has not visited a page"
    ],

    /*
    |--------------------------------------------------------------------------
    | Site activity
    |--------------------------------------------------------------------------
    | Allow user to determine whether find contacts who visit
    | all pages or specific page in the selected sales channel
    |
    */
    "pageSelect" => [
        "any" => "Any Pages",
        "some" => "Include Any"
    ],

    /*
    |--------------------------------------------------------------------------
    | Product select
    |--------------------------------------------------------------------------
    |
    | Third dropdown options for purchases filter (refer to frontend). Use key
    | as <option> tag value attribute, and its value as text.
    |
    */
    "productSelect" => [
        "any" => "Any Products",
        "some" => "Include Any"
    ],

    /*
    |--------------------------------------------------------------------------
    | Email select
    |--------------------------------------------------------------------------
    |
    | Temporarily deprecated (Dec 2020).
    |
    */
    "emailSelect" => [
        "Any Emails",
        "include any"
    ],

    /*
    |--------------------------------------------------------------------------
    | Email options
    |--------------------------------------------------------------------------
    |
    | Temporarily deprecated (Dec 2020).
    |
    */
    "emailOptions" => [
        "email 1",
        "email 2",
        "email 3"
    ],

    /*
    |--------------------------------------------------------------------------
    | Product options
    |--------------------------------------------------------------------------
    |
    | 4th dropdown for purchases filter, if "Include Any" is selected in 3rd dropdown.
    | Contains properties for products/[variant maybe], such as product name, SKU, etc.
    |
    | IMPORTANT:
    | Use valid database table column name as key, and display value as value. E.g. like
    | productTitle and "Product Name" below, productTitle is a column in users_products table.
    |
    */
    "productOptions" => [
        "productTitle" => "Product Name"
    ],

    /*
    |--------------------------------------------------------------------------
    | Product options (original)
    |--------------------------------------------------------------------------
    |
    | Legacy product options, keep this as reference only. Might be removed.
    |
    */
    "productOptions_ori" => [
        "Product Name",
        "Product Category",
        "Product Tag",
        "Product ID",
        "Product SKU"
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom select
    |--------------------------------------------------------------------------
    |
    | 3rd dropdown for custom field filter (refer to frontend).
    |
    */
    "customSelect" => [
        "is set",
        "is not set",
        "is",
        "is not",
        "is less than or equal to",
        "is greater than or equal to"
    ],

    /*
    |--------------------------------------------------------------------------
    | People profile labels
    |--------------------------------------------------------------------------
    |
    | 2nd dropdown for people profile filter (refer to frontend).
    |
    | IMPORTANT:
    | Use valid database table column name as key, and display name as value. E.g.
    | fname is a column in processed_contacts, "First name" is used as dropdown text
    | in UI.
    |
    */
    "peopleProfileLabels" => [
        // processed_contacts
        "fname" => "First name",
        "lname" => "Last name",
        "email" => "Email",
        "phone_number" => "Mobile number",
        "gender" => "Gender",
        "birthday" => "Birth date",

        // processed_addresses
        "address1" => "Address 1",
        "address2" => "Address 2",
        "zip" => "Postcode",
        "city" => "City",
        "state" => "State",
        "country" => "Country"
    ],

    /*
    |--------------------------------------------------------------------------
    | People profile select
    |--------------------------------------------------------------------------
    |
    | 3rd dropdown for people profile filter (refer to frontend). Nerfed customSelect
    |
    */
    "peopleProfileSelect" => [
        "is set",
        "is not set",
        "is",
        "is not"
    ],

    /*
    |--------------------------------------------------------------------------
    | Accuracy
    |--------------------------------------------------------------------------
    |
    | Only in purchases filter (as of Dec 2020), used to describe amount.
    |
    */
    "accuracy" => [
        "at least",
        "at most",
        "exactly"
    ],

    /*
    |--------------------------------------------------------------------------
    | Timeframe
    |--------------------------------------------------------------------------
    |
    | Used in some filters, as a time-range descriptor.
    |
    | - in the last N days: counting back from NOW to N days
    | - between X and Y: X, Y are dates, inclusive range between dates
    | - over all time: entire range
    |
    */
    "timeFrame" => [
        "in the last",
        "between",
        "over all time"
    ],

    /*
    |--------------------------------------------------------------------------
    | Duration
    |--------------------------------------------------------------------------
    |
    | Mostly as the last dropdown in filters (refer to frontend). Don't confuse
    | this with timeFrame above. Values are as shown in array below, with an
    | invisible 4th member "custom", which is a placeholder as "between" timeFrame.
    | It's not used in any code.
    |
    | For details about "custom" duration refer to filters' frontend code which
    | implemented timeFrame & duration, such as total sales.
    |
    */
    "duration" => [
        "days",
        "months",
        "years"
    ]
];
