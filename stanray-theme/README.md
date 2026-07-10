# StanRay Custom Theme

A clean, editorial custom WooCommerce WordPress theme built from scratch — no Elementor, no pre-built theme, just clean PHP, CSS, and JS.

---

## 📁 Folder Structure

```
stanray-theme/
├── style.css                    ← Theme registration (required)
├── functions.php                ← Theme setup, scripts, menus
├── index.php                    ← Blog/fallback template
├── front-page.php               ← Homepage
├── page.php                     ← Static pages
├── 404.php                      ← 404 error page
├── header.php                   ← Site header + nav + drawers
├── footer.php                   ← Site footer
│
├── inc/
│   ├── helpers.php              ← Utility functions
│   ├── woocommerce.php          ← WC hooks & customizations
│   ├── ajax.php                 ← AJAX handlers
│   └── customizer.php          ← WP Customizer settings
│
├── assets/
│   ├── css/
│   │   ├── main.css             ← Global styles
│   │   └── woocommerce.css      ← WooCommerce page styles
│   ├── js/
│   │   └── main.js              ← Theme JavaScript
│   └── images/
│       └── placeholder.jpg      ← Add a 600x750 placeholder image
│
└── woocommerce/
    ├── archive-product.php      ← Shop / category listing page
    └── single-product.php       ← Individual product page
```

---

## 🚀 Installation

### Step 1: Copy theme to WordPress
```
wp-content/themes/stanray-theme/
```

### Step 2: Activate in WordPress
1. Go to **Appearance → Themes**
2. Activate **StanRay Custom**

### Step 3: Install WooCommerce
1. Go to **Plugins → Add New**
2. Install and activate **WooCommerce**
3. Run through the WooCommerce setup wizard

### Step 4: Set up menus
1. Go to **Appearance → Menus**
2. Create a **Primary Navigation** menu and assign to "Primary Navigation" location
3. Create a **Footer** menu and assign to "Footer Navigation" location

### Step 5: Set up the homepage
1. Go to **Pages → Add New** → Create a page called "Home"
2. Go to **Settings → Reading** → Set "Your homepage displays" to "A static page" → select "Home"
3. The `front-page.php` template will now load automatically

### Step 6: Add widgets to footer
1. Go to **Appearance → Widgets**
2. Add **Navigation Menu** or **Text** widgets to Footer Col 1, 2, 3

### Step 7: Configure theme options
1. Go to **Appearance → Customize**
2. Under **Theme Options** configure:
   - Header announcement bar
   - Footer copyright text
   - Social media links

---

## 🎨 Customization

### Homepage Content
If you have **ACF (Advanced Custom Fields)** installed, add these field groups to the Front Page:

| Field Name | Type | Description |
|---|---|---|
| `hero_video_url` | URL | MP4 video for hero background |
| `hero_image` | Image | Fallback hero image |
| `hero_headline` | Text | Large hero text |
| `hero_subtext` | Text | Sub-headline |
| `hero_cta_text` | Text | CTA button label |
| `hero_cta_link` | URL | CTA button link |
| `editorial_image` | Image | Editorial section image |
| `editorial_headline` | Text | Editorial headline |
| `editorial_body` | Textarea | Editorial body text |
| `editorial_cta_text` | Text | Editorial CTA label |
| `editorial_cta_link` | URL | Editorial CTA link |

### Colors
All colors are CSS variables in `assets/css/main.css`:
```css
:root {
    --color-black:  #0a0a0a;
    --color-white:  #fafafa;
    --color-accent: #c8a96e;  /* Change this to your brand color */
}
```

### Fonts
Fonts are loaded from Google Fonts in `functions.php`. Change the URL to load different fonts and update the CSS variables:
```css
--font-display: 'EB Garamond', Georgia, serif;
--font-body:    'Barlow', 'Helvetica Neue', sans-serif;
```

### Product grid columns
- Change `.product-grid--3col` to `.product-grid--4col` in templates
- Or add `grid-template-columns` override in your CSS

---

## 🔌 Recommended Plugins

| Plugin | Purpose | Required? |
|---|---|---|
| WooCommerce | Shop engine | ✅ Yes |
| ACF Free or Pro | Homepage content blocks | Recommended |
| WC Stripe | Credit card payments | For payments |
| Smush / ShortPixel | Image optimization | Recommended |
| Yoast SEO | SEO | Recommended |

---

## 🛒 WooCommerce Template Overrides

When WooCommerce looks for templates, it first checks `wp-content/themes/your-theme/woocommerce/`. Add more overrides by copying from:
```
wp-content/plugins/woocommerce/templates/
```
into:
```
wp-content/themes/stanray-theme/woocommerce/
```

Common templates to override:
- `cart/cart.php` — Cart page
- `checkout/form-checkout.php` — Checkout page
- `myaccount/my-account.php` — Account page
- `loop/loop-start.php` — Product loop wrapper
- `single-product/tabs/tabs.php` — Product tabs

---

## 📐 CSS Architecture

```
main.css        ← Variables + reset + global layout + components
woocommerce.css ← Everything WooCommerce-specific (loaded only on WC pages)
```

---

## 🔧 Local Development

Recommended local environments:
- **LocalWP** (https://localwp.com) — easiest for beginners
- **DDEV** — more advanced, Docker-based
- **Laragon** (Windows)
- **Valet** (Mac)

---

## 📝 Notes

- The theme **disables all default WooCommerce CSS** (`woocommerce_enqueue_styles` returns empty) — all WC styles are our own in `woocommerce.css`
- The mini cart drawer opens automatically when an item is added to cart
- Product hover images use the **first gallery image** as hover state
- All hover/reveal animations are CSS + lightweight vanilla JS — no GSAP or heavy libraries needed
