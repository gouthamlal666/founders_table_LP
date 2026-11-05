# Founders Table by RAG Holdings — Landing Page

A marketing landing page for **The Founders Table** podcast.
Banner with video montage → About → RAG Holdings & venue → Episodes with reels → Suggest-a-guest form → Footer.

## Run it

```bash
npm install
npm start
```

Open http://localhost:3000

## Add a new episode

Edit **`assets/js/episodes.js`** only. Copy an episode block, paste it at the top of the list (newest first), and change:

| Field | Example |
|---|---|
| `youtubeId` | the part after `watch?v=` → `9wfFUsfYh6E` |
| `title`, `guest`, `company`, `duration`, `description` | text shown on the page |
| `reels` | 1–3 Shorts: `{ id: 'Kvn0XsjTozw', title: '…' }` (the part after `/shorts/`) |

Thumbnails come from YouTube automatically. The episode count in the banner and the "At the table so far" list update by themselves.

After editing, bump `?v=1` → `?v=2` on the script/style links in `index.html` so returning visitors get the new version.

## Guest-suggestion form

Every submission is:

1. **Stored**: `data/guest-suggestions.csv` (opens in Excel/Sheets) and `data/guest-suggestions.jsonl`
2. **Emailed** to your team: copy `.env.example` → `.env` and fill in your SMTP details and `MAIL_TO`. Restart the server.

If SMTP isn't set up yet, submissions are still saved. The server log will say the email was skipped.

The form has front-end and server-side validation, a hidden spam trap (honeypot) and a rate limit (8 submissions per 10 min per IP).

### Option B: static hosting (WordPress, Netlify, cPanel…)

If you'd rather not run Node, use the Google Sheet + email script in `integrations/google-apps-script.gs` (setup steps are at the top of the file), then set the endpoint in `index.html`:

```html
window.FT_CONFIG = { formEndpoint: 'https://script.google.com/macros/s/XXXX/exec' };
```

Upload `index.html` and the `assets/` folder. Nothing else is needed.

## Files

```
index.html                 page markup (all copy lives here)
assets/css/styles.css      design: navy #1c2448, gold #daad55, Montserrat
assets/js/episodes.js      ← episode data (edit this to add episodes)
assets/js/main.js          video controls, episode rendering, animations, form
assets/video/              hero montage (MP4, fast-start) + poster frame
assets/img/                logos, venue photos, favicon, social share image
server.js                  static server + /api/guest-suggestions
integrations/              Google Apps Script alternative for the form
```

## Notes

- **Hero video:** autoplays muted and loops, with sound and pause buttons. It pauses when scrolled out of view or when the tab is hidden, to save data and battery. Visitors who prefer reduced motion see the poster frame and can press play themselves.
- **Video size:** the montage is 4.3 MB (720×1280) and streams progressively. To make it lighter, re-encode it with ffmpeg:
  `ffmpeg -i in.mp4 -vf scale=540:-2 -c:v libx264 -crf 26 -preset slow -c:a aac -b:a 96k -movflags +faststart founders-table-montage.mp4`
- **Episode 2 guest name:** this is still a placeholder in `episodes.js`. Search for `TODO`.
- **Social share image:** `og:image` is a relative path. Once the page has its final URL, change it to an absolute URL (some platforms require one). Update `<link rel="canonical">` to match.
