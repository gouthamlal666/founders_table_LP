/**
 * Founders Table — landing page server
 * ------------------------------------
 * - Serves the static site (index.html + /assets)
 * - POST /api/guest-suggestions     → validates, stores, and emails the team
 * - POST /api/podcast-applications  → "Be a guest" lead form + pop-up
 *
 * Storage: data/guest-suggestions.jsonl (one JSON per line) + data/guest-suggestions.csv
 *          data/podcast-applications.jsonl + data/podcast-applications.csv
 * Email:   SMTP via nodemailer (configure in .env — see .env.example)
 *
 * Run:  npm install && npm start   →  http://localhost:3000
 */
'use strict';

const http = require('http');
const fs = require('fs');
const path = require('path');
const crypto = require('crypto');

loadEnv(path.join(__dirname, '.env'));

const PORT = Number(process.env.PORT) || 3000;
const ROOT = __dirname;
const DATA_DIR = path.join(ROOT, 'data');
const JSONL_FILE = path.join(DATA_DIR, 'guest-suggestions.jsonl');
const CSV_FILE = path.join(DATA_DIR, 'guest-suggestions.csv');

const MIME = {
  '.html': 'text/html; charset=utf-8',
  '.css': 'text/css; charset=utf-8',
  '.js': 'application/javascript; charset=utf-8',
  '.json': 'application/json; charset=utf-8',
  '.svg': 'image/svg+xml',
  '.png': 'image/png',
  '.jpg': 'image/jpeg',
  '.jpeg': 'image/jpeg',
  '.webp': 'image/webp',
  '.avif': 'image/avif',
  '.ico': 'image/x-icon',
  '.mp4': 'video/mp4',
  '.webm': 'video/webm',
  '.woff2': 'font/woff2',
};

// Only these folders/files are publicly served.
const PUBLIC = [/^\/$/, /^\/index\.html$/, /^\/assets\//, /^\/favicon\.ico$/];

/* ---------------------------------------------------------------- utils */

function loadEnv(file) {
  if (!fs.existsSync(file)) return;
  for (const line of fs.readFileSync(file, 'utf8').split(/\r?\n/)) {
    const m = line.match(/^\s*([A-Z0-9_]+)\s*=\s*(.*)\s*$/i);
    if (!m || line.trim().startsWith('#')) continue;
    let v = m[2];
    if (/^(['"]).*\1$/.test(v)) v = v.slice(1, -1);
    if (!(m[1] in process.env)) process.env[m[1]] = v;
  }
}

function send(res, status, body, headers = {}) {
  res.writeHead(status, { 'X-Content-Type-Options': 'nosniff', ...headers });
  res.end(body);
}

function json(res, status, obj) {
  send(res, status, JSON.stringify(obj), { 'Content-Type': MIME['.json'], 'Cache-Control': 'no-store' });
}

const clean = (v, max) => String(v ?? '').replace(/\u0000/g, '').trim().slice(0, max);
const isEmail = (v) => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v);
const isUrl = (v) => { try { const u = new URL(v); return /^https?:$/.test(u.protocol); } catch { return false; } };
const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const csvCell = (s) => {
  let v = String(s ?? '');
  if (/^[=+\-@\t\r]/.test(v)) v = "'" + v; // guard against spreadsheet formula injection
  return '"' + v.replace(/"/g, '""') + '"';
};

/* ------------------------------------------------------------ rate limit */

const hits = new Map(); // ip -> [timestamps]
function rateLimited(ip) {
  const now = Date.now();
  const recent = (hits.get(ip) || []).filter((t) => now - t < 10 * 60 * 1000);
  recent.push(now);
  hits.set(ip, recent);
  return recent.length > 8; // max 8 submissions / 10 min / IP
}

/* --------------------------------------------------------------- storage */

const CSV_HEADERS = ['id', 'submittedAt', 'founderName', 'linkedin', 'companyName', 'founderRole', 'industry', 'location', 'companyWebsite', 'why', 'topics', 'questions', 'connection', 'yourName', 'yourEmail', 'pageUrl'];

function store(entry, jsonl = JSONL_FILE, csv = CSV_FILE, headers = CSV_HEADERS) {
  fs.mkdirSync(DATA_DIR, { recursive: true });
  fs.appendFileSync(jsonl, JSON.stringify(entry) + '\n');
  if (!fs.existsSync(csv)) fs.writeFileSync(csv, '﻿' + headers.join(',') + '\n');
  fs.appendFileSync(csv, headers.map((h) => csvCell(entry[h])).join(',') + '\n');
}

// "Be a guest" applications (section lead form + pop-up)
const APPLY_JSONL = path.join(DATA_DIR, 'podcast-applications.jsonl');
const APPLY_CSV = path.join(DATA_DIR, 'podcast-applications.csv');
const APPLY_HEADERS = ['id', 'submittedAt', 'source', 'fullName', 'company', 'role', 'email', 'phone', 'industry', 'linkedin', 'story', 'pageUrl'];

/* ----------------------------------------------------------------- email */

let transporter = null;
function getTransporter() {
  if (transporter !== null) return transporter;
  if (!process.env.SMTP_HOST) { transporter = false; return false; }
  try {
    const nodemailer = require('nodemailer');
    transporter = nodemailer.createTransport({
      host: process.env.SMTP_HOST,
      port: Number(process.env.SMTP_PORT) || 587,
      secure: String(process.env.SMTP_SECURE) === 'true',
      auth: process.env.SMTP_USER ? { user: process.env.SMTP_USER, pass: process.env.SMTP_PASS } : undefined,
    });
  } catch (e) {
    console.warn('[email] nodemailer not installed — run `npm install`. Email disabled.');
    transporter = false;
  }
  return transporter;
}

async function notifyTeam(e) {
  const t = getTransporter();
  if (!t) { console.log('[email] SMTP not configured — skipped notification for', e.id); return false; }
  const rows = [
    ['Founder', e.founderName], ['LinkedIn', e.linkedin || '—'], ['Company', e.companyName], ['Role', e.founderRole || '—'],
    ['Industry', e.industry || '—'], ['Based in', e.location || '—'], ['Website / Instagram', e.companyWebsite || '—'],
    ['Why this founder', e.why], ['Topics', e.topics || '—'], ['Questions to ask', e.questions || '—'],
    ['Connection', e.connection || '—'], ['Suggested by', e.yourName || 'Anonymous'], ['Email', e.yourEmail || '—'], ['Received', e.submittedAt],
  ];
  const html = `
  <div style="font-family:Montserrat,Arial,sans-serif;max-width:620px;margin:auto;border:1px solid #e5e1d6;border-radius:12px;overflow:hidden">
    <div style="background:#1c2448;color:#fff;padding:22px 26px">
      <div style="color:#daad55;font-size:11px;letter-spacing:3px;text-transform:uppercase">Founders Table · RAG Holdings</div>
      <div style="font-size:20px;font-weight:700;margin-top:6px">New guest suggestion: ${esc(e.founderName)}</div>
    </div>
    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#1c2448">
      ${rows.map(([k, v]) => `<tr><td style="padding:12px 26px;width:150px;color:#6b7086;vertical-align:top;border-bottom:1px solid #f0ede5">${k}</td><td style="padding:12px 26px;border-bottom:1px solid #f0ede5;white-space:pre-wrap">${k === 'LinkedIn' && e.linkedin ? `<a href="${esc(e.linkedin)}">${esc(e.linkedin)}</a>` : esc(v)}</td></tr>`).join('')}
    </table>
    <div style="padding:14px 26px;font-size:12px;color:#8a8ea0">ID ${e.id}</div>
  </div>`;
  const text = rows.map(([k, v]) => `${k}: ${v}`).join('\n');
  await t.sendMail({
    from: process.env.MAIL_FROM || process.env.SMTP_USER,
    to: process.env.MAIL_TO,
    replyTo: e.yourEmail || undefined,
    subject: `Founders Table — guest suggestion: ${e.founderName} (${e.companyName})`,
    text,
    html,
  });
  return true;
}

async function notifyApplication(e) {
  const t = getTransporter();
  if (!t) { console.log('[email] SMTP not configured — skipped notification for', e.id); return false; }
  const rows = [
    ['Name', e.fullName], ['Company', e.company], ['Role', e.role || '—'], ['Email', e.email], ['Phone', e.phone],
    ['Industry', e.industry || '—'], ['LinkedIn / website', e.linkedin || '—'], ['Their story', e.story || '—'],
    ['Form', e.source === 'popup' ? 'Pop-up' : 'Page section'], ['Received', e.submittedAt],
  ];
  const html = `
  <div style="font-family:Montserrat,Arial,sans-serif;max-width:620px;margin:auto;border:1px solid #e5e1d6;border-radius:12px;overflow:hidden">
    <div style="background:#1c2448;color:#fff;padding:22px 26px">
      <div style="color:#daad55;font-size:11px;letter-spacing:3px;text-transform:uppercase">Founders Table · Guest application</div>
      <div style="font-size:20px;font-weight:700;margin-top:6px">${esc(e.fullName)} — ${esc(e.company)}</div>
    </div>
    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#1c2448">
      ${rows.map(([k, v]) => `<tr><td style="padding:12px 26px;width:150px;color:#6b7086;vertical-align:top;border-bottom:1px solid #f0ede5">${k}</td><td style="padding:12px 26px;border-bottom:1px solid #f0ede5;white-space:pre-wrap">${esc(v)}</td></tr>`).join('')}
    </table>
    <div style="padding:14px 26px;font-size:12px;color:#8a8ea0">ID ${e.id}</div>
  </div>`;
  await t.sendMail({
    from: process.env.MAIL_FROM || process.env.SMTP_USER,
    to: process.env.MAIL_TO,
    replyTo: e.email,
    subject: `Founders Table — guest application: ${e.fullName} (${e.company})`,
    text: rows.map(([k, v]) => `${k}: ${v}`).join('\n'),
    html,
  });
  return true;
}

/* -------------------------------------------------------------- handlers */

function readBody(req, limit = 20 * 1024) {
  return new Promise((resolve, reject) => {
    let size = 0; const chunks = [];
    req.on('data', (c) => { size += c.length; if (size > limit) { reject(new Error('too_large')); req.destroy(); } else chunks.push(c); });
    req.on('end', () => resolve(Buffer.concat(chunks).toString('utf8')));
    req.on('error', reject);
  });
}

async function handleSuggestion(req, res) {
  const ip = (req.headers['x-forwarded-for'] || req.socket.remoteAddress || '').split(',')[0].trim();
  if (rateLimited(ip)) return json(res, 429, { ok: false, error: 'Too many submissions. Please try again later.' });

  let data;
  try {
    const raw = await readBody(req);
    data = (req.headers['content-type'] || '').includes('application/json')
      ? JSON.parse(raw)
      : Object.fromEntries(new URLSearchParams(raw));
  } catch {
    return json(res, 400, { ok: false, error: 'Invalid request.' });
  }

  // Honeypot — bots fill hidden fields. Pretend success.
  if (clean(data.website, 200)) return json(res, 200, { ok: true });

  const entry = {
    id: crypto.randomUUID(),
    submittedAt: new Date().toISOString(),
    founderName: clean(data.founderName, 120),
    linkedin: clean(data.linkedin, 300),
    companyName: clean(data.companyName, 160),
    founderRole: clean(data.founderRole, 120),
    industry: clean(data.industry, 80),
    location: clean(data.location, 120),
    companyWebsite: clean(data.companyWebsite, 300),
    why: clean(data.why, 2000),
    topics: clean(Array.isArray(data.topics) ? data.topics.join(', ') : data.topics, 400),
    questions: clean(data.questions, 2000),
    connection: clean(data.connection, 80),
    yourName: clean(data.yourName, 120),
    yourEmail: clean(data.yourEmail, 200),
    pageUrl: clean(data.pageUrl, 300),
  };

  const errors = {};
  if (!entry.founderName) errors.founderName = 'Please enter the founder’s name.';
  if (!entry.companyName) errors.companyName = 'Please enter the company name.';
  if (!entry.why) errors.why = 'Please tell us why.';
  if (!isUrl(entry.linkedin) || !/(^|\.)linkedin\.com$/i.test(new URL(entry.linkedin || 'http://x').hostname)) {
    errors.linkedin = 'Please paste a LinkedIn profile link (linkedin.com/in/…).';
  }
  if (entry.yourEmail && !isEmail(entry.yourEmail)) errors.yourEmail = 'Please enter a valid email.';
  if (Object.keys(errors).length) return json(res, 422, { ok: false, errors });

  try {
    store(entry);
  } catch (e) {
    console.error('[store] failed', e);
    return json(res, 500, { ok: false, error: 'We couldn’t save your suggestion. Please try again.' });
  }

  // Email is best-effort: the submission is already safely stored.
  notifyTeam(entry).catch((e) => console.error('[email] failed for', entry.id, e.message));

  console.log('[guest] new suggestion', entry.id, '-', entry.founderName, '/', entry.companyName);
  return json(res, 200, { ok: true, id: entry.id });
}

async function handleApplication(req, res) {
  const ip = (req.headers['x-forwarded-for'] || req.socket.remoteAddress || '').split(',')[0].trim();
  if (rateLimited(ip)) return json(res, 429, { ok: false, error: 'Too many submissions. Please try again later.' });

  let data;
  try {
    const raw = await readBody(req);
    data = (req.headers['content-type'] || '').includes('application/json')
      ? JSON.parse(raw)
      : Object.fromEntries(new URLSearchParams(raw));
  } catch {
    return json(res, 400, { ok: false, error: 'Invalid request.' });
  }

  if (clean(data.website, 200)) return json(res, 200, { ok: true }); // honeypot

  const entry = {
    id: crypto.randomUUID(),
    submittedAt: new Date().toISOString(),
    source: clean(data.source, 20) === 'popup' ? 'popup' : 'section',
    fullName: clean(data.fullName, 120),
    company: clean(data.company, 160),
    role: clean(data.role, 120),
    email: clean(data.email, 200),
    phone: clean(data.phone, 30),
    industry: clean(data.industry, 80),
    linkedin: clean(data.linkedin, 300),
    story: clean(data.story, 2000),
    pageUrl: clean(data.pageUrl, 300),
  };

  const errors = {};
  if (!entry.fullName) errors.fullName = 'Please enter your name.';
  if (!entry.company) errors.company = 'Please enter your company name.';
  if (!isEmail(entry.email)) errors.email = 'Please enter a valid email.';
  if (entry.phone.replace(/\D/g, '').length < 7) errors.phone = 'Please enter a valid phone number.';
  if (entry.linkedin && !isUrl(entry.linkedin)) errors.linkedin = 'Please enter a valid link (https://…).';
  if (Object.keys(errors).length) return json(res, 422, { ok: false, errors });

  try {
    store(entry, APPLY_JSONL, APPLY_CSV, APPLY_HEADERS);
  } catch (e) {
    console.error('[store] failed', e);
    return json(res, 500, { ok: false, error: 'We couldn’t save your application. Please try again.' });
  }

  notifyApplication(entry).catch((e) => console.error('[email] failed for', entry.id, e.message));
  console.log('[apply] new application', entry.id, '-', entry.fullName, '/', entry.company, `(${entry.source})`);
  return json(res, 200, { ok: true, id: entry.id });
}

function serveStatic(req, res) {
  let urlPath;
  try { urlPath = decodeURIComponent(new URL(req.url, 'http://x').pathname); } catch { return send(res, 400, 'Bad request'); }
  if (!PUBLIC.some((r) => r.test(urlPath))) return send(res, 404, 'Not found');
  if (urlPath === '/') urlPath = '/index.html';

  const file = path.normalize(path.join(ROOT, urlPath));
  if (!file.startsWith(ROOT + path.sep)) return send(res, 403, 'Forbidden');

  fs.stat(file, (err, stat) => {
    if (err || !stat.isFile()) return send(res, 404, 'Not found');
    const ext = path.extname(file).toLowerCase();
    const type = MIME[ext] || 'application/octet-stream';
    // HTML/CSS/JS revalidate on every load (so edits show up immediately); media is cached for a week.
    const cache = ['.html', '.css', '.js', '.json'].includes(ext) ? 'no-cache' : 'public, max-age=604800';
    const lastMod = stat.mtime.toUTCString();
    const base = { 'Content-Type': type, 'Cache-Control': cache, 'Accept-Ranges': 'bytes', 'Last-Modified': lastMod };
    const ims = req.headers['if-modified-since'];
    if (ims && !req.headers.range && Math.floor(stat.mtimeMs / 1000) <= Math.floor(Date.parse(ims) / 1000)) {
      res.writeHead(304, base); return res.end();
    }

    // Range support so <video> can stream/seek efficiently.
    const range = req.headers.range;
    if (range && /^bytes=\d*-\d*$/.test(range)) {
      let [s, e] = range.replace('bytes=', '').split('-');
      let start = s === '' ? stat.size - Number(e) : Number(s);
      let end = s === '' || e === '' ? stat.size - 1 : Number(e);
      if (start < 0) start = 0;
      if (end >= stat.size) end = stat.size - 1;
      if (start > end) return send(res, 416, '', { 'Content-Range': `bytes */${stat.size}` });
      res.writeHead(206, { ...base, 'Content-Range': `bytes ${start}-${end}/${stat.size}`, 'Content-Length': end - start + 1 });
      return fs.createReadStream(file, { start, end }).pipe(res);
    }
    res.writeHead(200, { ...base, 'Content-Length': stat.size });
    if (req.method === 'HEAD') return res.end();
    fs.createReadStream(file).pipe(res);
  });
}

http.createServer((req, res) => {
  if (req.url.startsWith('/api/guest-suggestions')) {
    if (req.method === 'POST') return handleSuggestion(req, res).catch((e) => { console.error(e); json(res, 500, { ok: false, error: 'Server error.' }); });
    return json(res, 405, { ok: false, error: 'Method not allowed' });
  }
  if (req.url.startsWith('/api/podcast-applications')) {
    if (req.method === 'POST') return handleApplication(req, res).catch((e) => { console.error(e); json(res, 500, { ok: false, error: 'Server error.' }); });
    return json(res, 405, { ok: false, error: 'Method not allowed' });
  }
  if (req.method !== 'GET' && req.method !== 'HEAD') return send(res, 405, 'Method not allowed');
  serveStatic(req, res);
}).listen(PORT, () => {
  console.log(`Founders Table landing page → http://localhost:${PORT}`);
  console.log(getTransporter() ? `[email] notifications → ${process.env.MAIL_TO}` : '[email] SMTP not configured (submissions are still stored in /data)');
});
