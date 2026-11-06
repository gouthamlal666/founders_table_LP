# Founders Table by RAG Holdings — Landing Page

A marketing landing page for **The Founders Table** podcast.
Banner with video montage → About → RAG Holdings & venue → Episodes with reels → Suggest-a-guest form → Footer.

## Run it

The site runs on a small Laravel app in `backend/` (PHP 8.3+ and Composer). It serves the landing page, saves both forms to a database and has an admin dashboard.

First time only:

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

Create an empty MySQL database called `founderstable_db` (for example in phpMyAdmin), check the `DB_HOST`, `DB_PORT`, `DB_USERNAME` and `DB_PASSWORD` lines in `.env` match your MySQL, then create the tables:

```bash
php artisan migrate
```

On this Mac the database lives in XAMPP's MariaDB, which runs on port **3308** (Homebrew's separate MySQL uses 3306), so `.env` has `DB_PORT=3308`.

Create your admin login (asks for name, email and password):

```bash
php artisan admin:create
```

Then start it:

```bash
php artisan serve
```

- Website: http://localhost:8000
- Admin: http://localhost:8000/admin

## Admin dashboard

Built with Laravel + Livewire. After signing in you get:

- **Dashboard**: new applications and suggestions, last-7-days trend, a submissions chart (7 / 30 / 90 days), the application pipeline, and the latest entries.
- **Guest applications** ("Be a Guest" form and pop-up): search, filter by status or form, change status right in the list, export to CSV.
- **Guest suggestions** ("Suggest a Guest" form): the same tools.
- **Detail pages**: every field, one-tap Email / WhatsApp / Call / LinkedIn, status, private notes, delete.
- **Account**: change your name, email and password.

Statuses: applications go New → Contacted → Shortlisted → Scheduled → Recorded (or Declined). Suggestions go New → Reviewing → Contacted → Invited (or Declined).

Sign-in is limited to 5 wrong attempts a minute. There is no public sign-up: add admins with `php artisan admin:create`.

## Add a new episode

Edit **`assets/js/episodes.js`** only. Copy an episode block, paste it at the top of the list (newest first), and change:

| Field | Example |
|---|---|
| `youtubeId` | the part after `watch?v=` → `9wfFUsfYh6E` |
| `title`, `guest`, `company`, `duration`, `description` | text shown on the page |
| `reels` | 1–3 Shorts: `{ id: 'Kvn0XsjTozw', title: '…' }` (the part after `/shorts/`) |

Thumbnails come from YouTube automatically. The episode count in the banner and the "At the table so far" list update by themselves.

After editing, bump `?v=1` → `?v=2` on the script/style links in `index.html` so returning visitors get the new version.

## The two forms

Every "Be a Guest" and "Suggest a Guest" submission is:

1. **Saved** in the MySQL database `founderstable_db` (set in `backend/.env` under `DB_*`) and shown in the admin. You can also browse it in phpMyAdmin.
2. **Emailed** to your team, if you set it up: in `backend/.env` set `FOUNDERS_MAIL_TO` (one or more addresses, comma-separated) and the `MAIL_*` SMTP settings. Each email has an "Open in admin" button.

If email isn't set up, submissions are still saved. The log (`backend/storage/logs/laravel.log`) notes that the email was skipped.

The forms have front-end and server-side validation, a hidden spam trap (honeypot) and a rate limit (8 submissions per 10 min per IP).

### Option B: static hosting (WordPress, Netlify, cPanel…)

If you'd rather not run the Laravel app, use the Google Sheet + email script in `integrations/google-apps-script.gs` (setup steps are at the top of the file), then set the endpoint in `index.html`:

```html
window.FT_CONFIG = { formEndpoint: 'https://script.google.com/macros/s/XXXX/exec' };
```

Upload `index.html` and the `assets/` folder. Nothing else is needed, but there is no admin dashboard in this setup.

## Putting it online

Use any PHP host (Laravel Forge, Ploi, a VPS, or cPanel with PHP 8.3+):

- Upload the whole folder. Point the domain's web root at **`backend/public`**.
- `backend/public/assets` is a link to the landing page's `assets/` folder. If your host doesn't keep links, copy `assets/` into `backend/public/` instead.
- In `backend/.env` set `APP_ENV=production`, `APP_DEBUG=false` and `APP_URL=https://your-domain`, then run `php artisan migrate --force` and `php artisan admin:create`.
- If the landing page lives somewhere else, set `LANDING_PATH` to the folder that holds `index.html`.

## Files

```
index.html                 page markup (all copy lives here)
assets/css/styles.css      design: navy #1c2448, gold #daad55, Montserrat
assets/js/episodes.js      ← episode data (edit this to add episodes)
assets/js/main.js          video controls, episode rendering, animations, form
assets/video/              hero montage (MP4, fast-start) + poster frame
assets/img/                logos, venue photos, favicon, social share image
integrations/              Google Apps Script alternative for the form
backend/                   Laravel + Livewire app: serves the site, form API, admin
  routes/web.php           landing page + admin pages
  routes/api.php           POST /api/podcast-applications, /api/guest-suggestions
  app/Http/Controllers/    form handling (Api/) and CSV export (Admin/)
  app/Livewire/            admin screens: login, dashboard, lists, detail, account
  resources/views/         admin templates (layouts/, livewire/) and the email
  public/admin-assets/     admin stylesheet
```

## Notes

- **Hero video:** autoplays muted and loops, with sound and pause buttons. It pauses when scrolled out of view or when the tab is hidden, to save data and battery. Visitors who prefer reduced motion see the poster frame and can press play themselves.
- **Video size:** the montage is 4.3 MB (720×1280) and streams progressively. To make it lighter, re-encode it with ffmpeg:
  `ffmpeg -i in.mp4 -vf scale=540:-2 -c:v libx264 -crf 26 -preset slow -c:a aac -b:a 96k -movflags +faststart founders-table-montage.mp4`
- **Episode 2 guest name:** this is still a placeholder in `episodes.js`. Search for `TODO`.
- **Social share image:** `og:image` is a relative path. Once the page has its final URL, change it to an absolute URL (some platforms require one). Update `<link rel="canonical">` to match.
