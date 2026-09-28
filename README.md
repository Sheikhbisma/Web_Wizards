# MarketLink — eGreen Basket

A direct digital bridge between verified local growers and conscious customers.
Farmers publish weekly stock, customers pre-order before the market opens, and
both meet at a community market stall. No middlemen, no waste.

Built as a plain PHP application on XAMPP. There is no build step and no package
manager: Apache serves `public/` directly and the browser loads the CSS and JS as
authored.

---

## Requirements

| Tool | Version used | Notes |
|------|---------------|-------|
| PHP | 8.x | Runs on the XAMPP build; no Composer install step needed |
| MySQL / MariaDB | 8.x / 10.x | Schema ships in `public/techwiz7.sql` |
| Apache | 2.4 | Document root must point at `public/`, not the repo root |
| Web server | XAMPP 8.x | Development machine ran XAMPP on Windows |

PHP extensions required: `pdo_mysql`, `mail` (or the vendored PHPMailer fallback),
`session`, `json`.

---

## Setup

### 1. Place the project

Copy the folder into the Apache web root:

```
C:\xampp\htdocs\techwiz7
```

### 2. Create the database

Open the XAMPP shell and import the schema:

```bash
mysql -u root < public/techwiz7.sql
```

This creates the `techwiz7` database, all 17 tables, and seeds the base rows
(10 users, 21 products, 5 categories, 7 farmers, 6 markets).

### 3. Create the two git-ignored config files

Both are required. Both are ignored by `.gitignore`, so they will not be in the
repository and must be created locally.

**`private/config/dbconnect.php`** — copy this by hand, then edit the four
values at the top:

```php
<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$host = 'localhost';
$db   = 'techwiz7';
$user = 'root';
$pass = '';            // XAMPP default is an empty root password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
```

**`private/emailCredentials/credentials.php`** — a template ships as
`credentials.example.php`. Copy it and fill in the blanks:

```bash
copy private\emailCredentials\credentials.example.php \
     private\emailCredentials\credentials.php
```

It holds `EMAIL_ADDRESS` and `PASS_KEY` (a Gmail **app password**, not the
account password) for the password-reset and verification mail, plus the seeded
admin constants. Keep the constant names identical; the config files `include`
this path directly, so a missing file is a fatal error on any page.

Create an app password at <https://myaccount.google.com/apppasswords> (requires
2FA to be on).

### 4. Point Apache at `public/`

The document root must be the `public` folder. If the site is served from
`/techwiz7/`, every generated link already accounts for the extra segment.

Open `http://localhost/techwiz7/public/` and the home page should render.

---

## Roles

Three roles share one login form. Successful login redirects by role.

| Role | Lands on | Panel shell |
|------|----------|-------------|
| `customer` | `customer-dashboard` | `public/components/header.php` |
| `farmer` | `farmer-dashboard` | `public/components/farmer-sidebar.php` + `farmer-footer.php` |
| `admin` | `admin-dashboard` | `public/components/admin-sidebar.php` + `admin-footer.php` |

An account must have `is_verified = 1` to sign in, and `status = 'active'` or the
login is refused. Deactivated users are told to contact support.

---

## Layout

```
techwiz7/
|-- public/                    <- document root, everything web-facing
|   |-- index.php              <- front controller, single if/elseif route table
|   |-- components/            <- header, footer, admin/farmer sidebars
|   |-- css/                   <- customer.css, admin.css, farmer.css
|   |-- js/                    <- main.js, customer.js
|   |-- Uploads/               <- tracked site imagery (logo, slides, photos)
|   `-- techwiz7.sql           <- schema + seed data
|
`-- private/                   <- never web-reachable
    |-- config/                <- dbconnect, functions, customer helpers
    |-- views/                 <- Customer/, Farmer/, Admin/ + shared pages
    |-- backend-scripting/     <- POST handlers, one per action
    `-- emailCredentials/      <- credentials.php (git-ignored)
```

Routing is a flat `if/elseif` chain in `public/index.php` keyed on `?page=`.
There is no rewrite rule, so links work on any host without `.htaccess` tuning.
`ML_asset()` builds URLs that include the subfolder, so the app runs both at
`http://localhost/techwiz7/public/` and at a domain root.

---

## Pages

| Route | Purpose |
|-------|---------|
| `home` | Landing page, hero video, live counts |
| `about`, `contact` | Mission page and contact form |
| `products`, `product?id=` | Catalogue and product detail |
| `markets`, `market?id=` | Market list and a single market |
| `farmers`, `farmer?id=` | Grower directory and grower profile |
| `cart`, `checkout`, `orders`, `order?id=` | Basket and order lifecycle |
| `favorites`, `profile` | Saved items and account settings |
| `notifications` | Announcements plus per-user activity feed |
| `login`, `signup`, `verify-otp` | Auth, with optional `?role=farmer` |
| `forgot-password`, `reset-password?token=` | Token-based password recovery |
| `ai-chat`, `sitemap` | Assistant page and XML sitemap |
| `customer-dashboard` | Customer overview |
| `farmer-dashboard` and `farmer-*` | Weekly stock, products, markets, orders, reviews, reports |
| `admin-dashboard`, `manage-*`, `order-listing`, `admin-*` | Full admin panel |

---

## Database

17 tables. The ones that carry the interesting behaviour:

| Table | Holds |
|-------|-------|
| `users` | All three roles. `role`, `status`, `is_verified`, hashed `password`, optional `remember_token` |
| `farmers`, `markets` | Grower and stall records, linked by `market_farmer` |
| `products` | Linked to a farmer and category. `image_url` stores a **filename only** |
| `weekly_stock_template` | Per-farmer weekly rows driving the farmer stock grid |
| `orders`, `order_items` | Order header and line items |
| `notifications` | Per-user activity feed, read/unread flag |
| `announcements` | Platform-wide notices with an `is_active` toggle |
| `reviews`, `favorites`, `pickup_slots`, `reports`, `audit_log`, `categories`, `customers` | Supporting data |

### Product images

`image_url` stores a bare filename, never a path. `prodImgSrc()` checks both
`Uploads/img/<file>` and `Uploads/<file>` before returning a URL, and returns an
empty string when `image_url` is blank. Uploads written by `save-product.php`
land in `public/Uploads/img/`.

Known gap: when `image_url` is set but the file is absent from disk,
`prodImgSrc()` still returns a URL for the expected path, so the browser renders
a broken image rather than the category icon tile that `prodImg()` is capable of
drawing. Change the final `return ML_asset('Uploads/img/' . $file);` to
`return '';` to make the fallback reliable.

### Announcements

Announcements live only in `announcements` and are read by the customer's
notification page via `WHERE is_active = 1`. They are deliberately **not** copied
into `notifications` at publish time: those copies were separate rows, so
deactivating or deleting an announcement left the old copy sitting in every
customer's feed and the same text appeared twice. One table means the admin's
toggle and delete actually withdraw the notice.

---

## Security

- **Passwords** hashed with `password_hash()`; login uses `password_verify()`.
- **Session fixation** guarded by `session_regenerate_id(true)` on login.
- **CSRF** via `csrf_field()` in the form and `verify_csrf()` in the handler.
  Enforced on signup, login, and every state-changing POST.
- **SQL injection** prevented with prepared statements throughout. `selectData()`
  and `insertData()` are thin wrappers, not string interpolation.
- **Output escaping** through `sanitize_output()` on all user-supplied text.
- **Password reset** uses a single-use random token with an expiry, never a
  user-supplied identifier.
- **Access control** enforced server-side by `customerGuard()`, `validateFarmer()`
  and `validateAdmin()`. Hiding a nav link is never the only check.
- **Ownership** is re-checked in the handler, not trusted from the form, so a
  farmer cannot write to another farmer's rows.
- **Remember-me** issues a 24-byte random token via `random_bytes()`, stored in
  `users.remember_token` and handed back as an `httpOnly` cookie. The token is
  compared verbatim, not hashed, so a database read would yield a live
  remember-me token; set `secure` on the cookie when serving over HTTPS.

### Secrets

Never commit these two files; both are in `.gitignore`:

- `private/config/dbconnect.php` — database DSN and password
- `private/emailCredentials/credentials.php` — Gmail app password, seeded admin login

A `credentials.example.php` template is tracked so a new clone knows the required
constant names.

---

## Conventions

- Prepared statements for anything user-supplied. No string-built SQL.
- `sanitize_output()` on every echoed value, including inside attributes.
- One view file per page, one handler file per POST action in
  `private/backend-scripting/`.
- Role-scoped view folders: `Customer/`, `Farmer/`, `Admin/`.
- Icons are Font Awesome and Bootstrap Icons. **No emoji** — they render
  differently per OS, ignore the palette, and read as placeholder art.
- Product grids stay simple and responsive. No carousel or 3D tilt on listings.
- `prefers-reduced-motion` is honoured across the storefront, both panels, and
  the GSAP hero scroll.
- `will-change` is reserved for small transform-animated elements (orbit rings,
  drag card, cart fly ghost, market cards). It is deliberately kept off the
  full-width navbar reveal layer, where promoting a layer that wide smears the
  header on scroll.

---

## Documentation

- `docs/TEST_DATA.md` — demo / test data reference (accounts, markets,
  farmers, products, orders, reviews, favorites) used for demos and QA.
- `docs/API.md` — JSON (AJAX) endpoint reference: cart, favorites, reviews,
  orders, notifications, profile, live search and the assistant chat.

---

## Troubleshooting

**Blank page or "Connection failed"** — `dbconnect.php` is missing or the
credentials are wrong. Confirm the `techwiz7` database was imported.

**Fatal error on any page, mention of `EMAIL_ADDRESS`** — `credentials.php` is
missing. Copy it from `credentials.example.php`.

**Password-reset mail never arrives** — `PASS_KEY` must be a Gmail *app*
password, not the account password, and 2FA has to be enabled. Also check
`php.ini` has `mail()` available, otherwise the vendored PHPMailer is used.

**Images show a broken-image icon** — the filename in `products.image_url` has no
matching file in `public/Uploads/`. `prodImgSrc()` will still hand the browser a
URL for the expected path, so the request 404s and the icon appears; the category
tile only substitutes when `image_url` is blank. Check that the file is in
`Uploads/img/` and not at the repo root.

**Navbar or panel styles missing** — the document root is pointing at the repo
root instead of `public/`, so the relative CSS paths do not resolve.

**A route returns "404 Page Not Found"** — that exact string is the `else`
branch of the route table in `public/index.php`. The `?page=` value does not
match any key.

---

## License

Academic project — Web Wizards. Coursework submission.
