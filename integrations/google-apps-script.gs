/**
 * Founders Table — guest suggestions → Google Sheet + email
 * ---------------------------------------------------------
 * Use this if you host the landing page as static files (e.g. on WordPress,
 * Netlify, cPanel) and don't want to run server.js.
 *
 * SETUP (5 minutes)
 * 1. Create a Google Sheet, e.g. "Founders Table — Guest Suggestions".
 * 2. Extensions → Apps Script. Delete the sample code and paste this file.
 * 3. Set NOTIFY_EMAIL below to your team's address.
 * 4. Deploy → New deployment → type "Web app"
 *      Execute as: Me      Who has access: Anyone
 *    Authorise when asked, then copy the Web app URL (ends in /exec).
 * 5. In index.html set:
 *      window.FT_CONFIG = { formEndpoint: 'PASTE_THE_/exec_URL_HERE' };
 */

const NOTIFY_EMAIL = 'marketing@ragroup.ae'; // comma-separate several addresses
const SHEET_NAME = 'Suggestions';
const HEADERS = ['Submitted at', 'Founder', 'LinkedIn', 'Company', 'Role', 'Industry', 'Based in', 'Website / Instagram', 'Why', 'Topics', 'Questions', 'Connection', 'Suggested by', 'Email', 'Page'];

function doPost(e) {
  try {
    const d = JSON.parse((e && e.postData && e.postData.contents) || '{}');
    if (d.website) return json_({ ok: true }); // honeypot

    const v = (k, max) => String(d[k] || '').trim().slice(0, max);
    if (d.formType === 'application') return handleApplication_(d, v);
    const row = {
      founderName: v('founderName', 120), linkedin: v('linkedin', 300), companyName: v('companyName', 160),
      founderRole: v('founderRole', 120), industry: v('industry', 80), location: v('location', 120),
      companyWebsite: v('companyWebsite', 300), why: v('why', 2000), topics: v('topics', 400),
      questions: v('questions', 2000), connection: v('connection', 80), yourName: v('yourName', 120),
      yourEmail: v('yourEmail', 200), pageUrl: v('pageUrl', 300),
    };
    if (!row.founderName || !row.linkedin || !row.companyName || !row.why) {
      return json_({ ok: false, error: 'Please fill in the required fields.' });
    }

    const ss = SpreadsheetApp.getActiveSpreadsheet();
    const sh = ss.getSheetByName(SHEET_NAME) || ss.insertSheet(SHEET_NAME);
    if (sh.getLastRow() === 0) { sh.appendRow(HEADERS); sh.setFrozenRows(1); sh.getRange(1, 1, 1, HEADERS.length).setFontWeight('bold'); }
    const safe = (s) => (/^[=+\-@]/.test(s) ? "'" + s : s); // stop formula injection
    sh.appendRow([new Date(), row.founderName, row.linkedin, row.companyName, row.founderRole, row.industry, row.location,
      row.companyWebsite, row.why, row.topics, row.questions, row.connection, row.yourName, row.yourEmail, row.pageUrl]
      .map((x) => typeof x === 'string' ? safe(x) : x));

    const body = [
      'Founder: ' + row.founderName, 'LinkedIn: ' + row.linkedin, 'Company: ' + row.companyName,
      'Role: ' + (row.founderRole || '—'), 'Industry: ' + (row.industry || '—'), 'Based in: ' + (row.location || '—'),
      'Website / Instagram: ' + (row.companyWebsite || '—'),
      '', 'Why: ' + row.why, 'Topics: ' + (row.topics || '—'), '', 'Questions: ' + (row.questions || '—'),
      'Connection: ' + (row.connection || '—'), '',
      'Suggested by: ' + (row.yourName || 'Anonymous') + (row.yourEmail ? ' <' + row.yourEmail + '>' : ''),
      '', 'All suggestions: ' + ss.getUrl(),
    ].join('\n');
    const opts = { name: 'Founders Table' };
    if (row.yourEmail) opts.replyTo = row.yourEmail;
    MailApp.sendEmail(NOTIFY_EMAIL, 'Founders Table — guest suggestion: ' + row.founderName + ' (' + row.companyName + ')', body, opts);

    return json_({ ok: true });
  } catch (err) {
    return json_({ ok: false, error: 'Server error. Please try again.' });
  }
}

// "Be a guest" applications (page section + pop-up) → separate "Applications" tab
const APPLY_SHEET = 'Applications';
const APPLY_HEADERS = ['Submitted at', 'Form', 'Name', 'Company', 'Role', 'Email', 'Phone', 'Industry', 'LinkedIn / website', 'Story', 'Page'];

function handleApplication_(d, v) {
  const r = {
    source: v('source', 20) === 'popup' ? 'Pop-up' : 'Page section',
    fullName: v('fullName', 120), company: v('company', 160), role: v('role', 120), email: v('email', 200),
    phone: v('phone', 30), industry: v('industry', 80), linkedin: v('linkedin', 300), story: v('story', 2000), pageUrl: v('pageUrl', 300),
  };
  if (!r.fullName || !r.company || !r.email || !r.phone) {
    return json_({ ok: false, error: 'Please fill in the required fields.' });
  }

  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const sh = ss.getSheetByName(APPLY_SHEET) || ss.insertSheet(APPLY_SHEET);
  if (sh.getLastRow() === 0) { sh.appendRow(APPLY_HEADERS); sh.setFrozenRows(1); sh.getRange(1, 1, 1, APPLY_HEADERS.length).setFontWeight('bold'); }
  const safe = (s) => (/^[=+\-@]/.test(s) ? "'" + s : s);
  sh.appendRow([new Date(), r.source, r.fullName, r.company, r.role, r.email, r.phone, r.industry, r.linkedin, r.story, r.pageUrl].map((x) => typeof x === 'string' ? safe(x) : x));

  const body = [
    'Name: ' + r.fullName, 'Company: ' + r.company, 'Role: ' + (r.role || '—'), 'Email: ' + r.email, 'Phone: ' + r.phone,
    'Industry: ' + (r.industry || '—'), 'LinkedIn / website: ' + (r.linkedin || '—'), '', 'Story: ' + (r.story || '—'), '',
    'Form: ' + r.source, '', 'All applications: ' + ss.getUrl(),
  ].join('\n');
  MailApp.sendEmail(NOTIFY_EMAIL, 'Founders Table — guest application: ' + r.fullName + ' (' + r.company + ')', body, { name: 'Founders Table', replyTo: r.email });
  return json_({ ok: true });
}

function json_(obj) {
  return ContentService.createTextOutput(JSON.stringify(obj)).setMimeType(ContentService.MimeType.JSON);
}
