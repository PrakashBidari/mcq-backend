<?php

// Fixed catalog of purchasable question-set price tiers. Each tier must correspond
// to a real IAP product created in App Store Connect and Google Play Console with
// the same tier key as the product ID suffix (e.g. tier_500 -> com.ikigaiconnect.app.tier_500
// on iOS, tier_500 on Android). Adding a tier here does NOT create the store product -
// that has to be done manually in both consoles first.
//
// Prices are whole Japanese yen (JPY has no decimal subunit).

return [
    'bundle_id' => env('APPSTORE_BUNDLE_ID', 'com.ikigaiconnect.app'),

    'currency' => 'JPY',

    'tiers' => [
        'tier_300'  => 300,
        'tier_500'  => 500,
        'tier_800'  => 800,
        'tier_1200' => 1200,
        'tier_1800' => 1800,
        'tier_2800' => 2800,
    ],
];
