# KeyToBD — Cox's Bazar Travel Booking Platform

Full-featured WordPress site for **[KeyToBD](https://keytobd.com)**, a Cox's Bazar-based travel agency offering online bookings for tour packages, hotels & resorts, rent-a-car, Saint Martin ship tickets, and houseboat trips.

**Designed & developed by [3s-Soft](https://3s-soft.com).**

---

## Repository overview

This repo tracks the `wp-content/` directory of the WordPress installation — the custom code that makes KeyToBD unique. WordPress core, third-party plugins, and uploaded media are **not** version-controlled.

```
wp-content/
├── themes/keytobd/             ← Custom theme (v1.0.0)
├── plugins/keytobd-booking/    ← Custom booking engine plugin (v1.2.0)
├── uploads/                    ← Media library (git-ignored)
└── .gitignore
```

| Component | Description | Docs |
|-----------|-------------|------|
| **keytobd** theme | Front-end UI — homepage hero, service archives, booking search, Customizer options, WooCommerce integration, Elementor support | [Theme README](themes/keytobd/README.md) |
| **keytobd-booking** plugin | Back-end booking engine — services CPT, booking CPT, AJAX availability, email notifications, shortcodes, payment hooks | [Plugin README](plugins/keytobd-booking/README.md) |

---

## Requirements

| Requirement | Version |
|-------------|---------|
| WordPress   | 6.4+    |
| PHP         | 7.4+    |
| WooCommerce | Latest  |

### Payment gateway

- **SSLCommerz** (cards + bKash / Nagad / Rocket) and/or a dedicated **bKash** gateway

### Recommended plugins

- Fluent Forms or Contact Form 7 (visa & event enquiries)
- Rank Math (SEO)
- WP Rocket + ShortPixel (performance & image optimization)
- Wordfence (security)
- Elementor / Elementor Pro (optional — theme cooperates natively)

---

## Getting started

### 1. Local development (Local by Flywheel)

This project is set up with [Local](https://localwp.com/). To get started:

```bash
# Clone into your Local site's wp-content directory
git clone <repo-url> /path/to/Local Sites/keytobd/app/public/wp-content
```

### 2. Activate theme & plugin

1. **Appearance → Themes** → Activate **KeyToBD**
2. **Plugins** → Activate **KeyToBD Booking**

On activation the theme auto-creates:
- **Destination** taxonomy terms
- WooCommerce product categories: `tour-packages`, `hotels-resorts`, `rent-a-car`, `ship-tickets`, `houseboat`

### 3. Configure WooCommerce

- Set currency to **BDT ৳**
- Store address: Cox's Bazar, Bangladesh
- Services don't need shipping or tax

### 4. Create pages

| Page | Slug | Template |
|------|------|----------|
| Home | `home` | *(default)* — set as static front page |
| About Us | `about` | **About** |
| Contact | `contact` | **Contact** |
| FAQ | `faq` | **FAQ** |
| Visa Processing | `visa-processing` | **Service Enquiry** |
| Event Management | `event-management` | **Service Enquiry** |
| Blog | `blog` | *(default)* — set as Posts page |

### 5. Configure SSLCommerz

Install and configure SSLCommerz gateway (sandbox first, then production).

---

## Customization

Everything front-facing is editable from **Appearance → Customize → KeyToBD Options**:

- **Brand Colors** — Navy, Blue, Sky, Accent, Teal, Body text (CSS variables, live preview)
- **Contact & Business** — Phones, WhatsApp, email, address, hours
- **Social Links** — Facebook, Instagram, YouTube
- **Homepage Hero** — Background image, headings, search widget toggle
- **Homepage Sections** — Show/hide & edit every block
- **Footer** — CTA, about text, credits

See the [Theme README](themes/keytobd/README.md) for full details.

---

## Booking engine

The [KeyToBD Booking](plugins/keytobd-booking/README.md) plugin provides:

- **Services** — bookable items with type-specific pricing (per person / per night / per day / per seat)
- **Front-end booking form** — live availability & pricing via AJAX
- **Admin dashboard** — sortable columns, color status badges, one-click status change
- **Email notifications** — admin alerts + customer confirmations
- **Capacity control** — real-time availability enforcement
- **Security** — nonce verification, rate limiting, honeypot, optional Cloudflare Turnstile

### Shortcodes

```
[ktb_booking_form]                          Full form
[ktb_booking_form service="12"]             Locked to service ID
[ktb_services type="tour" count="6"]        Services grid
```

---

## Project structure

```
wp-content/
├── .gitignore
├── index.php
│
├── themes/
│   ├── index.php
│   └── keytobd/
│       ├── .gitignore
│       ├── style.css                  Theme header
│       ├── theme.json                 Design tokens
│       ├── functions.php              Setup, assets, menus
│       ├── header.php / footer.php    Global shell
│       ├── front-page.php             Homepage
│       ├── page-templates/            About · Contact · FAQ · Service Enquiry
│       ├── template-parts/            Reusable partials
│       ├── inc/                       Options · Customizer · Taxonomies · CPTs
│       └── assets/                    css/ · js/ · img/
│
├── plugins/
│   ├── index.php
│   └── keytobd-booking/
│       ├── .gitignore
│       ├── keytobd-booking.php        Bootstrap
│       ├── includes/                  Post types · Meta · Shortcodes · AJAX ·
│       │                              Emails · Admin · Security · Export
│       ├── templates/                 Overridable templates
│       └── assets/                    css/ · js/
│
└── uploads/                           Media library (git-ignored)
```

---

## Git workflow

```bash
# Check status
git status

# Stage and commit
git add -A
git commit -m "feat: describe your change"

# Push
git push origin main
```

### Branch naming

| Prefix | Purpose | Example |
|--------|---------|---------|
| `feat/` | New features | `feat/gallery-section` |
| `fix/` | Bug fixes | `fix/booking-email` |
| `style/` | Design changes | `style/hero-redesign` |
| `chore/` | Maintenance | `chore/update-deps` |

---

## Deployment

> **Note:** Only `themes/keytobd/` and `plugins/keytobd-booking/` need to be deployed. WordPress core, other plugins, and uploads are managed separately on the server.

1. Push changes to the remote repository
2. On the production server, pull the latest changes into `wp-content/`
3. Clear any caches (WP Rocket, object cache, CDN)

---

## License

- **Theme:** [GPL-2.0-or-later](https://www.gnu.org/licenses/gpl-2.0.html)
- **Plugin:** [GPL-2.0-or-later](https://www.gnu.org/licenses/gpl-2.0.html)

---

© 2026 [KeyToBD](https://keytobd.com) · Built by [3s-Soft](https://3s-soft.com)
