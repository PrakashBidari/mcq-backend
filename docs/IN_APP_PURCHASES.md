# In-App Purchases Setup

Real per-question-set purchases, verified server-side against Apple and Google directly
(no third-party payment service). This doc covers the one-time manual setup required in
App Store Connect and Google Play Console before purchases can be tested end-to-end.

## How it works

- Prices are in **Japanese yen (JPY)** - yen has no decimal subunit, so all tier amounts
  are whole numbers (e.g. `¥500`, never `¥500.00`).
- Each paid question set has a `price_tier` (e.g. `tier_500`) instead of a freeform price.
  Tiers are defined in `config/price_tiers.php` and must match real store products.
- iOS product ID: `{bundle_id}.{tier}` (e.g. `com.ikigaiconnect.app.tier_500`)
- Android product ID: `{tier}` (e.g. `tier_500`)
- The mobile app buys the tier product via StoreKit / Play Billing (`react-native-iap`),
  then sends the receipt/purchase token to `POST /api/purchases/verify`, which checks it
  directly against Apple/Google and records a `purchases` row unlocking that question set
  for that user.
- Products are treated as **repeatable/consumable** on both stores, since the same tier
  product can be "bought" again for a different question set. Our `purchases` table (not
  the store) is the source of truth for what a user owns.

## 1. App Store Connect (iOS)

1. Apple Developer Program membership must be active, with Agreements/Tax/Banking complete
   (required before any IAP - sandbox or production - can be tested).
2. Create one **Consumable** In-App Purchase per tier in `config/price_tiers.php`:
   - Product ID: `com.ikigaiconnect.app.tier_300`, `.tier_500`, `.tier_800`,
     `.tier_1200`, `.tier_1800`, `.tier_2800`
   - Reference name: anything internal, e.g. "Question Set Tier - ¥500"
   - Price: pick the price point matching each tier's yen amount. App Store Connect lets
     you set a price in any storefront's local currency (e.g. set the Japan territory
     price to exactly ¥300/¥500/etc.) - other territories get Apple's auto-converted
     equivalent, which is fine since Japan is the primary market.
3. Generate the **In-App Purchase Shared Secret**: App Store Connect > your app >
   App Information > "App-Specific Shared Secret". Put it in `.env` as `APPSTORE_PASSWORD`.
4. Add a **Sandbox Tester** account: App Store Connect > Users and Access > Sandbox Testers.
   Sign into this account on a test device (Settings > App Store > Sandbox Account) to test
   purchases without real charges.

## 2. Google Play Console (Android)

1. The app must have at least one signed build uploaded to a testing track (Internal
   Testing is enough) before in-app products can be created.
2. Create one **managed in-app product** per tier, Product ID matching the tier exactly:
   `tier_300`, `tier_500`, `tier_800`, `tier_1200`, `tier_1800`, `tier_2800`. Set the
   default price in JPY to match each tier's yen amount (Play Console lets you set a
   default price and auto-converts other territories, or you can set the Japan price
   explicitly under "Manage countries/regions").
3. Create a **service account** for server-to-server verification:
   - Play Console > Setup > API access > "Create new service account" (opens Google Cloud
     Console) > create a key (JSON) for it > download the JSON file.
   - Back in Play Console, grant that service account "View financial data" and access to
     the app.
   - Enable the **Google Play Android Developer API** in Google Cloud Console for that project.
4. Set `GOOGLE_APPLICATION_CREDENTIALS` in `.env` to the absolute path of the downloaded
   JSON key file, and `GOOGLE_PLAY_PACKAGE_NAME` to `com.ikigaiconnect.app`.
5. Add a **License Tester** account: Play Console > Setup > License testing, so purchases
   made with that Google account are test purchases (no real charge, auto-refunded).

## 3. Backend `.env` values to fill in

```
APPSTORE_BUNDLE_ID=com.ikigaiconnect.app
APPSTORE_PASSWORD=            # shared secret from step 1.3
GOOGLE_PLAY_PACKAGE_NAME=com.ikigaiconnect.app
GOOGLE_APPLICATION_CREDENTIALS=   # absolute path to the service account JSON from step 2.3
```

## 4. Admin panel

Question Sets > Create/Edit now has a **Price Tier** dropdown instead of a freeform price
field - pick one of the fixed tiers. The set stays purchasable only once the matching
store products above exist in both consoles.

**Paid question sets with no tier, or a tier that no longer exists in `config/price_tiers.php`
(e.g. after the tier list is renamed/repriced), need their tier reassigned** - they show a
"needs a tier" warning in the edit screen until an admin picks a current, valid tier. Until
then they're paid but unbuyable (the app shows them as needing purchase but there's no
matching store product to buy).

## 5. Testing checklist

- [ ] Sign into a real device with the Apple Sandbox Tester / Google License Tester account
- [ ] Buy a paid question set - confirm it unlocks immediately
- [ ] Kill the app mid-purchase (after the native purchase sheet completes, before returning
      to the app) - relaunch and confirm it still unlocks (tests the pending-purchase replay)
- [ ] Log out and confirm free question sets are still fully accessible with no auth
- [ ] Confirm a purchased set shows "Owned" and cannot be bought again
