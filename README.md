# Heartland Retail PHP Client

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%5E8.2-8892BF.svg)](https://www.php.net/)

A complete, PHP 8.2+ client library for the [Heartland Retail REST API](https://dev.retail.heartland.us/).

---

## Features

- **Full API coverage** — Items, Customers, Inventory, Sales, Purchasing, Promotions, Gift Cards, Webhooks, Users & Roles, Tax, Locations, Reports, Custom Fields
- **OAuth 2.0 authorization-code flow** built-in
- **Typed exceptions** — `AuthenticationException`, `AuthorizationException`, `NotFoundException`, `ValidationException`, `RateLimitException`, `TransportException`
- **Automatic rate-limit retry** with `Retry-After` support and exponential back-off on 5xx
- **Proactive throttling** — optional `requestsPerSecond` cap
- **Auto-pagination** — `->all()` helpers transparently walk every page and `yield` records
- **PSR-4** autoloading, strict types throughout
- **TLS enforced** — `CURLOPT_SSL_VERIFYPEER` is always `true`; no option to disable it
- **Sensitive parameter redaction** — access tokens are marked with `#[\SensitiveParameter]`

---

## Installation

```bash
composer require mihaighita/heartland-retail
```

Requires **PHP 8.2+**, `ext-curl`, and `ext-json`.

---

## Quick Start

```php
use HeartlandRetail\Client;

$client = Client::withToken(
    accessToken:       'your_token_here',
    subdomain:         'yourstore',       // → https://yourstore.retail.heartland.us/api
    requestsPerSecond: 5.0                // optional: proactive throttle
);

// Get a single item
$item = $client->items()->get(1234);
echo $item->get('description');

// Search customers
foreach ($client->customers()->search(['email' => 'jane@example.com']) as $c) {
    echo $c['first_name'] . ' ' . $c['last_name'];
}

// Walk ALL items without managing pagination yourself
foreach ($client->items()->all(['active' => true]) as $item) {
    // $item is an associative array
}
```

---

## OAuth 2.0 Setup

```php
use HeartlandRetail\Auth\OAuthClient;
use HeartlandRetail\Client;

$oauth = new OAuthClient(clientId: 'xxx', clientSecret: 'yyy');

// 1. Redirect the user
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;
header('Location: ' . $oauth->getAuthorizationUrl($redirectUri, $scopes, $state));

// 2. In your callback
if ($_GET['state'] !== $_SESSION['oauth_state']) { die('CSRF'); }
$token  = $oauth->exchangeCodeForToken($_GET['code'], $redirectUri);
$host   = $oauth->lookupAccountHost($token->accessToken);
$client = new Client($token->baseUrlFor($host), $token->accessToken);
```

---

## Filters

```php
$client->items()->search([
    'active'      => true,                   // q[active]=1
    'price'       => ['>=', 10.00],          // q[price][>=]=10
    'description' => ['~', 'shirt'],         // q[description][~]=shirt
]);
```

Supported operators: `=`, `!=`, `<`, `>`, `<=`, `>=`, `~` (contains), `!~` (not contains)

---

## Error Handling

```php
use HeartlandRetail\Exception\{
    AuthenticationException,
    AuthorizationException,
    NotFoundException,
    ValidationException,
    RateLimitException,
    TransportException
};

try {
    $client->items()->get(99999);
} catch (AuthenticationException $e) { /* expired/invalid token */ }
  catch (NotFoundException $e)       { /* 404                   */ }
  catch (ValidationException $e)     { print_r($e->getErrors()); }
  catch (RateLimitException $e)      { /* exhausted all retries */ }
  catch (TransportException $e)      { /* network / cURL error  */ }
```

---

## License

[MIT](LICENSE)
