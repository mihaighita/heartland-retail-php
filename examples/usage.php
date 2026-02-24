<?php

/**
 * Heartland Retail PHP Client — Usage Examples
 * =============================================
 * Requires Composer autoloader.  Run:  composer install
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use HeartlandRetail\Auth\OAuthClient;
use HeartlandRetail\Client;
use HeartlandRetail\Exception\{
    AuthenticationException,
    NotFoundException,
    RateLimitException,
    ValidationException
};

// ─── 1. Authentication ────────────────────────────────────────────────────────

// Option A: Simple token auth (for server-side integrations with a static token)
$client = Client::withToken(
    accessToken: 'your_access_token_here',
    subdomain:   'yourstore',            // becomes https://yourstore.retail.heartland.us/api
    requestsPerSecond: 5.0               // optional: proactive throttle to 5 req/s
);

// Option B: Full OAuth2 authorization-code flow
$oauth = new OAuthClient(
    clientId:     'your_oauth_client_id',
    clientSecret: 'your_oauth_client_secret'
);

// Step 1: Generate a CSRF state token and redirect the user
$state       = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;          // store for validation in callback

$authorizeUrl = $oauth->getAuthorizationUrl(
    redirectUri: 'https://yourapp.example.com/oauth/callback',
    scopes:      ['item.manage', 'customer.manage', 'inventory.transaction.read'],
    state:       $state
);
// header("Location: $authorizeUrl");  // redirect the user here

// Step 2: In your callback handler — validate state then exchange the code
// if ($_GET['state'] !== $_SESSION['oauth_state']) { die('CSRF mismatch'); }
// $token = $oauth->exchangeCodeForToken($_GET['code'], 'https://yourapp.example.com/oauth/callback');
// $host  = $oauth->lookupAccountHost($token->accessToken);
// $client = new Client($token->baseUrlFor($host), $token->accessToken);


// ─── 2. Error handling pattern ────────────────────────────────────────────────

try {
    $item = $client->items()->get(9999999);

} catch (AuthenticationException $e) {
    echo "Token is invalid or expired. Re-authenticate.\n";

} catch (NotFoundException $e) {
    echo "Item not found.\n";

} catch (ValidationException $e) {
    echo "Validation errors:\n";
    foreach ($e->getErrors() as $field => $messages) {
        echo "  {$field}: " . implode(', ', $messages) . "\n";
    }

} catch (RateLimitException $e) {
    echo "Rate limited. Retry after {$e->getRetryAfter()}s\n";
    // The client retries automatically — this is only thrown after maxRetries is exhausted.
}


// ─── 3. Items ─────────────────────────────────────────────────────────────────

// Create an item
$response = $client->items()->create([
    'description' => 'Classic White T-Shirt',
    'price'       => 29.99,
    'cost'        => 8.00,
    'sku'         => 'TSHIRT-WHT-M',
    'active'      => true,
]);
$newItem = $response->getBody();
echo "Created item ID: {$newItem['id']}\n";

// Retrieve an item with embedded inventory levels
$response = $client->items()->get(1234, embed: ['inventory_levels', 'custom_fields']);
$item     = $response->getBody();
echo "Item: {$item['description']}, Price: {$item['price']}\n";

// Update an item
$client->items()->update(1234, ['price' => 34.99, 'active' => true]);

// Search items — equality filter + "contains" filter
$results = $client->items()->search([
    'active'      => true,
    'description' => ['~', 'shirt'],   // LIKE %shirt%
], page: 1, perPage: 25);

echo "Found {$results->getTotal()} items across {$results->getPageCount()} pages\n";
foreach ($results as $item) {
    echo "  [{$item['id']}] {$item['description']} — \${$item['price']}\n";
}

// Auto-paginate ALL active items (walks every page automatically)
foreach ($client->items()->all(['active' => true]) as $item) {
    // process $item ...
}

// Merge item 200 into item 100 (100 survives, 200 is deleted)
$client->items()->merge(targetId: 100, sourceId: 200);


// ─── 4. Customers ─────────────────────────────────────────────────────────────

$response = $client->customers()->create([
    'first_name' => 'Jane',
    'last_name'  => 'Doe',
    'email'      => 'jane@example.com',
    'phone'      => '555-123-4567',
]);
$customerId = $response->get('id');

$client->customers()->createAddress($customerId, [
    'address1' => '123 Main St',
    'city'     => 'Springfield',
    'state'    => 'IL',
    'zip'      => '62701',
    'country'  => 'US',
    'type'     => 'billing',
]);

// Search by email
$results = $client->customers()->search(['email' => 'jane@example.com']);


// ─── 5. Inventory ─────────────────────────────────────────────────────────────

// Create an adjustment set (e.g. shrinkage)
$adj = $client->inventory()->createAdjustmentSet([
    'location_id' => 1,
    'reason_id'   => 3,
]);
$adjId = $adj->get('id');

// Add items to the adjustment
$client->inventory()->addItemToAdjustmentSet($adjId, [
    'item_id'  => 1234,
    'quantity' => -2,    // negative = shrinkage
]);

// Check current inventory value totals
$totals = $client->inventory()->getInventoryValueTotals(['location_id' => 1]);
echo "Total inventory value: {$totals->get('total_value')}\n";

// Create a transfer (Location 1 → Location 2)
$transfer = $client->inventory()->createTransfer([
    'from_location_id' => 1,
    'to_location_id'   => 2,
]);
$transferId = $transfer->get('id');

$client->inventory()->createTransferLine($transferId, ['item_id' => 1234, 'quantity' => 5]);
$client->inventory()->createTransferShipment($transferId);


// ─── 6. Sales ─────────────────────────────────────────────────────────────────

// Create a ticket and add an item + payment
$ticket = $client->sales()->createTicket([
    'location_id' => 1,
    'station_id'  => 1,
    'customer_id' => $customerId,
]);
$ticketId = $ticket->get('id');

$client->sales()->addTicketItemLine($ticketId, [
    'item_id'  => 1234,
    'quantity' => 1,
    'price'    => 29.99,
]);

$client->sales()->addTicketPayment($ticketId, [
    'payment_type_id' => 1,   // 1 = cash
    'amount'          => 29.99,
]);

// Search completed tickets for today
$tickets = $client->sales()->searchTickets([
    'status'          => 'completed',
    'completed_at'    => ['>=', date('Y-m-d') . 'T00:00:00Z'],
]);


// ─── 7. Purchasing ────────────────────────────────────────────────────────────

// Find or create vendor
$vendors = $client->purchasing()->searchVendors(['name' => ['~', 'Acme']]);
$vendorId = $vendors->getResults()[0]['id'] ?? null;

if (!$vendorId) {
    $vendor   = $client->purchasing()->createVendor(['name' => 'Acme Supplies', 'email' => 'orders@acme.com']);
    $vendorId = $vendor->get('id');
}

// Create a PO
$po   = $client->purchasing()->createOrder(['vendor_id' => $vendorId, 'location_id' => 1]);
$poId = $po->get('id');

$client->purchasing()->addOrderItem($poId, ['item_id' => 1234, 'quantity' => 50, 'unit_cost' => 8.00]);

// Receive goods
$receipt   = $client->purchasing()->createReceipt(['order_id' => $poId, 'location_id' => 1]);
$receiptId = $receipt->get('id');

$client->purchasing()->addReceiptItem($receiptId, ['item_id' => 1234, 'quantity_received' => 50]);


// ─── 8. Promotions & Coupons ──────────────────────────────────────────────────

$rule = $client->promotions()->createRule([
    'name'           => 'Summer 20% Off',
    'action_type_id' => 1,   // percentage discount
    'amount'         => 20,
    'active'         => true,
]);
$ruleId = $rule->get('id');

$client->promotions()->createCoupon([
    'code'                => 'SUMMER20',
    'promotion_rule_id'   => $ruleId,
    'max_uses'            => 1000,
    'expires_at'          => '2025-09-01T00:00:00Z',
]);


// ─── 9. Gift Cards ────────────────────────────────────────────────────────────

$giftCard = $client->giftCards()->create([
    'number'          => 'GC-1234567890',
    'initial_balance' => 50.00,
]);

// Add $25 to the card
$reasons  = $client->giftCards()->getAdjustmentReasons();
$reasonId = $reasons->getResults()[0]['id'];

$client->giftCards()->createAdjustment([
    'gift_card_number' => 'GC-1234567890',
    'amount'           => 25.00,
    'reason_id'        => $reasonId,
]);


// ─── 10. Webhooks ─────────────────────────────────────────────────────────────

$client->webhooks()->create([
    'url'         => 'https://yourapp.example.com/webhooks/heartland',
    'event_types' => ['ticket.completed', 'item.updated', 'customer.created'],
    'active'      => true,
]);


// ─── 11. Users ────────────────────────────────────────────────────────────────

$user = $client->users()->create([
    'login'      => 'newstaff',
    'password'   => bin2hex(random_bytes(12)),  // always generate strong passwords
    'first_name' => 'Alex',
    'last_name'  => 'Smith',
]);
$userId = $user->get('id');

// Assign a role
$roles  = $client->users()->searchRoles(['name' => 'Cashier']);
$roleId = $roles->getResults()[0]['id'] ?? null;

if ($roleId) {
    $client->users()->addUserRole($userId, ['role_id' => $roleId]);
}

// Grant access to Location 1
$client->users()->addUserLocation($userId, ['location_id' => 1]);


// ─── 12. Reports ──────────────────────────────────────────────────────────────

// Capture the query URL from your browser DevTools and replicate the params here.
// E.g. analyzer?per_page=50&metrics[]=location_sales&groups[]=date&...
foreach ($client->reports()->allRows([
    'metrics' => ['location_sales'],
    'groups'  => ['date'],
    'from'    => date('Y-m-d', strtotime('-30 days')),
    'to'      => date('Y-m-d'),
]) as $row) {
    echo "{$row['date']}: \${$row['net_sales']}\n";
}


// ─── 13. Who am I? ────────────────────────────────────────────────────────────

$me = $client->system()->whoAmI();
echo "Authenticated as: {$me->get('login')} ({$me->get('email')})\n";
