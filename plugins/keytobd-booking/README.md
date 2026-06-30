# KeyToBD Booking

Self-contained WordPress booking engine for travel services — **no premium add-ons
required**. Built for KeyToBD by **3s-Soft**.

Handles tour packages, hotels/resorts, rent-a-car, Saint Martin ship tickets and
houseboats with the right pricing unit for each (per person / per night / per day
/ per seat), live availability, customer bookings, email notifications and a
pluggable payment hook.

## Features

- **Services** (`Bookings → Services`) — bookable items with type, price, capacity
  per date, duration, location, featured image and description.
- **Front-end booking form** — date(s), quantity, customer details, **live total +
  availability** via AJAX. Range services (hotel/car/houseboat) auto-show an end
  date and price by nights/days.
- **Bookings** (`Bookings → All Bookings`) — every request stored with a unique
  reference, sortable columns, color status badges, one-select status change.
- **Statuses** — Pending → Confirmed → Completed / Cancelled. Confirming a booking
  emails the customer.
- **Emails** — branded admin notification + customer acknowledgement on submit,
  customer confirmation on status change.
- **Capacity control** — overlapping pending/confirmed bookings count against a
  service's per-date capacity (0 = unlimited).
- **Settings** — currency, notification email, auto-confirm toggle, success
  message.
- **Theme-aware styling** — inherits the active theme's brand CSS variables
  (`--accent`, `--blue`, …) with safe fallbacks; templates are overridable.

## Shortcodes

| Shortcode | Purpose |
|-----------|---------|
| `[ktb_booking_form]` | Full form; customer picks the service |
| `[ktb_booking_form service="12"]` | Form locked to service ID 12 |
| `[ktb_booking_form title="Reserve now"]` | Custom heading |
| `[ktb_services type="tour" count="6" columns="3"]` | Grid of services (type optional) |

A booking form is also **auto-appended** to every single Service page.

## Template overrides

Copy `templates/booking-form.php` or `templates/service-card.php` into your theme
at `your-theme/keytobd-booking/` to customize markup.

## Payment integration (SSLCommerz / bKash / etc.)

The core stays gateway-agnostic. After a booking is created the plugin applies:

```php
$pay_url = apply_filters( 'ktb_payment_url', '', $booking_id, $meta );
```

Return a redirect URL to send the customer to checkout; return `''` (default) to
keep "pay on confirmation". Example bridge to SSLCommerz:

```php
add_filter( 'ktb_payment_url', function ( $url, $booking_id, $meta ) {
    // Build an SSLCommerz session for $meta['total'] with $meta['ref']
    // as the transaction id, then return the gateway GatewayPageURL.
    return my_sslcommerz_create_session( $meta );
}, 10, 3 );

// Mark paid on the gateway IPN/success callback:
update_post_meta( $booking_id, '_ktb_payment', 'Paid · SSLCommerz' );
wp_update_post( array( 'ID' => $booking_id, 'post_status' => 'ktb-confirmed' ) );
```

### Optional WooCommerce bridge
If you prefer WooCommerce checkout, hook `ktb_booking_created`, create a WC order
from `$meta`, and return its `$order->get_checkout_payment_url()` via
`ktb_payment_url`.

## Action / filter reference

| Hook | Type | Fires |
|------|------|-------|
| `ktb_booking_created` | action `($booking_id, $meta)` | after a booking is saved |
| `ktb_booking_status_changed` | action `($booking_id, $new, $old)` | on status change |
| `ktb_payment_url` | filter `($url, $booking_id, $meta)` | to inject a payment redirect |

## Data model

- CPT `ktb_service` — public; meta `_ktb_type`, `_ktb_price`, `_ktb_capacity`,
  `_ktb_duration`, `_ktb_location`.
- CPT `ktb_booking` — private; meta `_ktb_ref`, `_ktb_service_id`, `_ktb_name`,
  `_ktb_email`, `_ktb_phone`, `_ktb_date`, `_ktb_date_end`, `_ktb_qty`,
  `_ktb_units`, `_ktb_total`, `_ktb_notes`, `_ktb_payment`. Custom post statuses
  `ktb-pending|confirmed|cancelled|completed`.

## Structure

```
keytobd-booking/
├── keytobd-booking.php          bootstrap, constants, activation/seed
├── includes/
│   ├── ktb-functions.php        helpers (settings, availability, pricing, template)
│   ├── class-ktb-post-types.php services + bookings CPTs + statuses
│   ├── class-ktb-meta.php       service config + booking detail meta boxes
│   ├── class-ktb-shortcodes.php form + services grid + auto-append
│   ├── class-ktb-ajax.php       availability + create-booking (nonce-guarded)
│   ├── class-ktb-emails.php     admin + customer notifications
│   ├── class-ktb-admin.php      columns, settings page, plugin links
│   └── class-ktb-plugin.php     singleton loader + asset enqueue
├── templates/                   booking-form.php · service-card.php (overridable)
└── assets/                      css/ktb.css · js/ktb.js
```

## Security (v1.2)

Public booking endpoints are unauthenticated by design; these controls protect them.

- **Nonce** (`ktb_nonce`) on every AJAX action (guests + logged-in).
- **Rate limiting** per client IP (configurable count/window). IP is resolved from
  `REMOTE_ADDR` only — forwarded headers (`X-Forwarded-For`, `CF-Connecting-IP`)
  are trusted **only** when you define `KTB_TRUSTED_PROXY` true (origin behind a
  real proxy/CDN), and then the last hop is used. Filter: `ktb_client_ip`.
- **Anti-bot**: salt-derived honeypot field name (rotates per install) + an
  HMAC-**signed** form-open token (`time|hmac(time, wp_salt)`) verified with
  `hash_equals` and an age window — a bot cannot back-date it.
- **Optional Cloudflare Turnstile**: set Site + Secret keys in
  Bookings → Settings → Security to require a captcha (verified via the
  `ktb_validate_booking` filter; no-op when unset).
- **Input hardening**: all fields sanitized + length-clamped (`ktb_clamp`);
  phone format validated; service must be published; date past/lead-time/blackout
  enforced; party-size bounds enforced; availability re-checked at submit.
- **CSV-injection safe**: every export cell passes through `ktb_csv_cell()` which
  neutralizes leading `= + - @` / tab / CR and strips control chars.
- **Export** is `manage_options` + nonce; not exposed to anonymous requests.
- **Booking lookup** requires reference **and** matching phone (`hash_equals`),
  never reveals which field failed, and is throttled.

### Accepted risks / notes
- The anonymous `ktb_nonce` is shared across logged-out visitors (WordPress
  design); it is not used to gate any privileged action — rate-limit + validation
  are the real controls.
- Capacity check → insert is not transactional; at extreme concurrency two
  last-seat bookings could both land (acceptable at SMB volume).
- Customer phone/email are stored in plaintext `postmeta` (required for booking
  management); restrict admin access and apply a retention policy.
- Extend validation via the `ktb_validate_booking` filter (return `WP_Error` to
  reject) for blocklists or alternative captchas.

### Site-level hardening (outside this plugin)
`WP_DEBUG=false` + no `display_errors` in production, block web access to
`debug.log`, admin 2FA + strong passwords, a WAF / limit-login (e.g. Wordfence),
disable XML-RPC if unused, keep core/themes/plugins updated, least-privilege DB
user, HTTPS + HSTS.
