# KeyToBD — WordPress Travel Booking Theme

Custom theme for **KeyToBD** (keytobd.com), a Cox's Bazar travel agency.
Full online booking engine for tours, hotels, rent-a-car, Saint Martin ship
tickets and houseboat trips, built on WooCommerce.

**Designed & developed by [3s-Soft](https://3s-soft.com).**

---

## Requirements

- WordPress 6.4+
- PHP 7.4+
- WooCommerce (cart / checkout / accounts / orders)
- A bookings plugin — **WooCommerce Bookings** (or YITH Booking & Appointment) for date/seat/availability
- A Bangladesh payment gateway — **SSLCommerz** (cards + bKash/Nagad/Rocket) and/or a dedicated **bKash** gateway

### Recommended companions
Fluent Forms / Contact Form 7 (visa & event enquiries) · Rank Math (SEO) ·
WP Rocket + ShortPixel (speed/images) · Wordfence (security).

---

## Install

1. Copy this `keytobd` folder into `wp-content/themes/` and **activate** it.
   On activation the theme seeds the **Destination** taxonomy terms and the five
   WooCommerce product categories: `tour-packages`, `hotels-resorts`,
   `rent-a-car`, `ship-tickets`, `houseboat`.
2. Install & activate WooCommerce → run its setup (set currency to **BDT ৳**,
   store address to Cox's Bazar; services need no shipping/tax).
3. Install the bookings plugin and create each service as a **bookable product**
   under the matching category.
4. Install and configure the **SSLCommerz** (sandbox first) gateway.

## Pages to create (Pages → Add New)

Assign the page template in the **Page Attributes → Template** box:

| Page | Slug | Template |
|------|------|----------|
| Home | `home` | *(default)* — then set as static front page |
| About Us | `about` | **About** |
| Contact | `contact` | **Contact** |
| FAQ | `faq` | **FAQ** |
| Visa Processing | `visa-processing` | **Service Enquiry** |
| Event Management | `event-management` | **Service Enquiry** |
| Blog | `blog` | *(default)* — set as Posts page |

Then **Settings → Reading → Your homepage displays → A static page →** Home.

For Contact / Visa / Event, paste a Fluent Forms or CF7 shortcode into the page
body to capture leads by email; otherwise a styled fallback form is shown.

## Menus (Appearance → Menus)

- **Primary Menu** — Home, Tours, Hotels, Rent A Car, Ship Tickets, Destinations,
  About, Contact. (Until assigned, a sensible fallback menu renders automatically.)
- **Legal Menu** — Terms, Privacy Policy, Refund & Cancellation Policy.
- **Footer Menu** — optional.

## Fully customizable — no code needed

Everything front-facing is editable from **Appearance → Customize → KeyToBD Options**
(plus **Site Identity** for the logo). Changes are live-previewed.

| Customizer section | Controls |
|--------------------|----------|
| **Brand Colors** | Navy, Blue, Sky, Accent (+ hover), Teal, Body text — applied site-wide via CSS variables, instant live preview |
| **Contact & Business** | Phones, WhatsApp, email, address, hours, website label, map query |
| **Social Links** | Facebook, Instagram, YouTube (icons appear only when filled) |
| **Homepage Hero** | Background image, eyebrow, heading, subtitle, rating text, show/hide search widget |
| **Homepage Sections** | Show/hide + edit the eyebrow / title / text of every block (services, packages, destinations, why-us, steps, testimonials, blog, CTA) and how many packages to show |
| **Footer** | CTA title/text, about text, toggle the 3s-Soft credit |

- **Testimonials** are managed as their own admin menu (**Testimonials → Add New**):
  title = name, body = quote, side box = location + star rating. Until you add
  any, three sample reviews show.
- **Logo** via Customize → Site Identity (else the "KeyToBD" wordmark shows).
- Defaults for every option live in `inc/options.php`; templates read them through
  `keytobd_mod()`. Fonts/spacing tokens live in `theme.json` + `assets/css/main.css`.
- Add real photos in `assets/img/` (see that folder's README) or rely on the
  bundled SVG placeholder.

## Elementor & Elementor Pro

The theme is built to cooperate with Elementor, not fight it.

- **Any page with Elementor**: edit a page → "Edit with Elementor". Pick a page
  template in Elementor's settings:
  - **Full Width (Elementor)** — theme header + footer, edge-to-edge content (added by this theme).
  - **Elementor Full Width** / **Elementor Canvas** — Elementor's own blank templates also work.
  - **Default** — uses the theme `page.php` (narrow, article-style) for simple pages.
- **Elementor Pro Theme Builder**: header, footer, single and archive
  **locations are registered** (`register_all_core_location`). Build a header or
  footer in Elementor and it replaces the theme's automatically; with none
  assigned, the theme's own header/footer render as fallback (see
  `keytobd_do_elementor_location()` in `inc/elementor.php`).
- `add_theme_support( 'elementor' )` is declared, so no compatibility notice.
- Brand colors set in the Customizer expose CSS variables
  (`--accent`, `--blue`, …) you can reference inside Elementor custom CSS to stay
  on-brand.

> The homepage (`front-page.php`) is coded for performance + the booking search
> widget. To rebuild it in Elementor instead, create a page, set it as the static
> front page, and use the **Full Width (Elementor)** template.

## Structure

```
keytobd/
├── style.css                 Theme header (Author: 3s-Soft)
├── theme.json                Brand color & type tokens
├── functions.php             Setup, assets, menus, contact details, Woo support
├── header.php / footer.php   Global shell, topbar, WhatsApp float, mobile bar
├── front-page.php            Homepage (hero search → services → packages → …)
├── index / archive / single / search / 404 / searchform.php
├── page.php                  Default page
├── page-templates/           About · Contact · FAQ · Service Enquiry
├── template-parts/           hero-search · tour-card · page-hero
├── inc/                       options · customizer · customizer-css ·
│                              template-tags · taxonomies · post-types · woocommerce
└── assets/                    css/main.css · js/main.js · js/customize-preview.js · img/
```

## Verification checklist

- Activate theme → categories + destinations auto-created.
- Homepage renders hero search, services, demo packages, destinations, footer.
- Create a bookable tour → it replaces demo cards on the homepage and shows in
  the Tours shop archive.
- Add to cart → checkout → pay via **SSLCommerz sandbox** → "Booking confirmed"
  voucher note on the thank-you page + confirmation email.
- Submit the Contact / Enquiry form (with a forms plugin wired).
- Check mobile: drawer nav, sticky Call/WhatsApp/Book bar, tabbed search collapses.
