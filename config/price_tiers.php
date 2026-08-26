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
        'tier_100'  => 100,
        'tier_200'  => 200,
        'tier_300'  => 300,
        'tier_400'  => 400,
        'tier_500'  => 500,
        'tier_1000' => 1000,
        'tier_1500' => 1500,
        'tier_2000' => 2000,
        'tier_3000' => 3000,
        'tier_5000' => 5000,
    ],
];
