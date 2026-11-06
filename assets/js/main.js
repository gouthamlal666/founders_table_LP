/* =====================================================================
   Founders Table by RAG Holdings — page behaviour
   ===================================================================== */
(function () {
  'use strict';

  const $ = (s, el = document) => el.querySelector(s);
  const $$ = (s, el = document) => [...el.querySelectorAll(s)];
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const YT = 'https://www.youtube.com';

  /* ---------------------------------------------------------- header */
  const header = $('.site-header');
  const onScroll = () => header.classList.toggle('scrolled', window.scrollY > 40);
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });

  const toggle = $('.nav-toggle');
  const setNav = (open) => {
    document.body.classList.toggle('nav-open', open);
    toggle.setAttribute('aria-expanded', String(open));
    toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
  };
  toggle.addEventListener('click', () => setNav(!document.body.classList.contains('nav-open')));
  $$('#main-nav a').forEach((a) => a.addEventListener('click', () => setNav(false)));
  document.addEventListener('keydown', (e) => e.key === 'Escape' && setNav(false));

  // Highlight the nav link of the section in view
  const navLinks = $$('#main-nav a[href^="#"]');
  if ('IntersectionObserver' in window) {
    const spy = new IntersectionObserver((entries) => {
      entries.forEach((en) => {
        if (!en.isIntersecting) return;
        navLinks.forEach((a) => a.classList.toggle('active', a.getAttribute('href') === '#' + en.target.id));
      });
    }, { rootMargin: '-45% 0px -50% 0px' });
    ['about', 'host', 'venue', 'episodes', 'apply', 'suggest'].forEach((id) => { const s = document.getElementById(id); s && spy.observe(s); });
  }

  /* ---------------------------------------------------------- hero video */
  const video = $('#hero-video');
  const soundBtn = $('.sound-toggle');
  const playBtn = $('.play-toggle');
  const bar = $('.video-progress span');
  let userPaused = false;

  if (video) {
    video.muted = true; // required for autoplay on every browser

    const tryPlay = () => { const p = video.play(); p && p.catch(() => setPaused(true)); };
    const setPaused = (paused) => {
      playBtn.classList.toggle('paused', paused);
      playBtn.setAttribute('aria-label', paused ? 'Play video' : 'Pause video');
    };

    if (reducedMotion) { video.removeAttribute('autoplay'); video.pause(); setPaused(true); }

    video.addEventListener('play', () => setPaused(false));
    video.addEventListener('pause', () => setPaused(true));

    soundBtn.addEventListener('click', () => {
      const turnOn = video.muted;
      video.muted = !turnOn;
      if (turnOn) { video.volume = 1; userPaused = false; if (video.paused) tryPlay(); }
      soundBtn.setAttribute('aria-pressed', String(turnOn));
      soundBtn.setAttribute('aria-label', turnOn ? 'Mute sound' : 'Turn sound on');
      $('.sound-label', soundBtn).textContent = turnOn ? 'Sound on' : 'Sound off';
    });

    playBtn.addEventListener('click', () => {
      if (video.paused) { userPaused = false; tryPlay(); } else { userPaused = true; video.pause(); }
    });

    // progress bar (rAF only while playing)
    let raf;
    const tick = () => {
      if (video.duration) bar.style.transform = `scaleX(${video.currentTime / video.duration})`;
      raf = requestAnimationFrame(tick);
    };
    video.addEventListener('playing', () => { cancelAnimationFrame(raf); raf = requestAnimationFrame(tick); });
    video.addEventListener('pause', () => cancelAnimationFrame(raf));

    // Save battery/data: pause when the banner scrolls out of view, resume when back.
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(([en]) => {
        if (en.isIntersecting) { if (!userPaused && !reducedMotion) tryPlay(); }
        else if (!video.paused) video.pause();
      }, { threshold: 0.2 }).observe(video);
    }
    document.addEventListener('visibilitychange', () => {
      if (document.hidden) video.pause();
      else if (!userPaused && !reducedMotion) tryPlay();
    });
  }

  /* ---------------------------------------------------------- episodes */
  const list = $('#episode-list');
  const episodes = Array.isArray(window.FT_EPISODES) ? window.FT_EPISODES : [];

  const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const initials = (name) => name.split(/[\s&]+/).filter((w) => /^[A-Za-z]/.test(w)).slice(0, 2).map((w) => w[0]).join('').toUpperCase();
  const pad = (n) => String(n).padStart(2, '0');
  const ext = 'target="_blank" rel="noopener"';

  function episodeHTML(ep, i) {
    const url = `${YT}/watch?v=${encodeURIComponent(ep.youtubeId)}`;
    const thumb = ep.thumbnail || `https://i.ytimg.com/vi/${encodeURIComponent(ep.youtubeId)}/maxresdefault.jpg`;
    const fallback = `https://i.ytimg.com/vi/${encodeURIComponent(ep.youtubeId)}/hqdefault.jpg`;
    const label = ep.number != null ? `Episode ${pad(ep.number)}` : 'Episode';
    const reels = (ep.reels || []).slice(0, 3);
    const lazy = i === 0 ? 'eager' : 'lazy';

    return `
    <article class="episode reveal" aria-labelledby="ep-${esc(ep.youtubeId)}-title">
      <a class="ep-thumb" href="${url}" ${ext} aria-label="Watch ${esc(label)}: ${esc(ep.title)} on YouTube">
        <img src="${esc(thumb)}" data-fallback="${esc(fallback)}" alt="" width="1280" height="720" loading="${lazy}" decoding="async" />
        <span class="ep-badge${i === 0 ? ' new' : ''}">${i === 0 ? 'Latest · ' : ''}EP ${pad(ep.number ?? episodes.length - i)}</span>
        <span class="play-btn" aria-hidden="true"><svg class="i"><use href="#i-play"/></svg></span>
        ${ep.duration ? `<span class="ep-duration"><svg class="i" aria-hidden="true"><use href="#i-clock"/></svg>${esc(ep.duration)}</span>` : ''}
      </a>

      <div class="ep-info">
        <p class="ep-num">${esc(label)}</p>
        <h3 class="ep-title" id="ep-${esc(ep.youtubeId)}-title"><a href="${url}" ${ext}>${esc(ep.title)}</a></h3>
        <div class="ep-guest">
          <span class="ep-guest-avatar" aria-hidden="true">${esc(initials(ep.guest || 'FT'))}</span>
          <div><strong>${esc(ep.guest)}</strong><span>${esc(ep.company)}</span></div>
        </div>
        <p class="ep-desc">${esc(ep.description)}</p>
        <a class="btn btn-gold ep-cta" href="${url}" ${ext}>
          <svg class="i" aria-hidden="true"><use href="#i-youtube"/></svg> Watch Full Episode
        </a>

        ${reels.length ? `
        <p class="reels-label">Highlight reels</p>
        <div class="reels">
          ${reels.map((r) => `
          <a class="reel" href="${YT}/shorts/${encodeURIComponent(r.id)}" ${ext} aria-label="Watch reel: ${esc(r.title)}">
            <img src="https://i.ytimg.com/vi/${encodeURIComponent(r.id)}/oar2.jpg" data-fallback="https://i.ytimg.com/vi/${encodeURIComponent(r.id)}/hqdefault.jpg" alt="" width="405" height="720" loading="lazy" decoding="async" />
            <span class="reel-play" aria-hidden="true"><svg class="i"><use href="#i-play"/></svg></span>
            <span class="reel-tag" aria-hidden="true">Reel</span>
            <span class="reel-title">${esc(r.title)}</span>
          </a>`).join('')}
        </div>` : ''}
      </div>
    </article>`;
  }

  if (list && episodes.length) {
    list.innerHTML = episodes.map(episodeHTML).join('');
    // Fallback thumbnail if a size isn't available on YouTube
    $$('img[data-fallback]', list).forEach((img) => {
      img.addEventListener('error', function onErr() {
        img.removeEventListener('error', onErr);
        img.src = img.dataset.fallback;
      });
    });
    $$('[data-episode-count]').forEach((el) => (el.textContent = episodes.length));
  }

  const past = $('#past-guests');
  if (past) {
    past.innerHTML = episodes.map((ep) =>
      `<li><strong>${esc(ep.guest)}</strong><span>${esc(String(ep.company || '').replace(/^.*?,\s*/, ''))}</span></li>`).join('');
  }

  /* ---------------------------------------------------------- instagram reels */
  const track = $('#insta-track');
  const insta = Array.isArray(window.FT_INSTAGRAM) ? window.FT_INSTAGRAM.filter(Boolean) : [];
  if (track && insta.length) {
    track.innerHTML = insta.map((id, i) => {
      const code = encodeURIComponent(String(id).trim());
      return `<div class="insta-card" data-src="https://www.instagram.com/p/${code}/embed/">
        <iframe title="Instagram reel ${i + 1} from The Founders Table" scrolling="no" allowtransparency="true" allow="encrypted-media; picture-in-picture; clipboard-write" referrerpolicy="strict-origin-when-cross-origin"></iframe>
      </div>`;
    }).join('') + `<a class="insta-more" href="https://www.instagram.com/ragholdings/" ${ext}>
        <svg class="i" aria-hidden="true"><use href="#i-instagram"/></svg>More on Instagram</a>`;

    // Load embeds only when the strip comes near the screen (keeps first load fast).
    const cards = $$('.insta-card', track);
    // When the section nears the screen, load the embeds in order, 3 at a time:
    // the first ones appear quickly and the rest are ready before anyone swipes to them.
    let nextCard = 0;
    const loadNext = () => {
      const card = cards[nextCard++];
      if (!card) return;
      const f = $('iframe', card);
      let done = false;
      const finish = () => { if (done) return; done = true; card.classList.add('loaded'); loadNext(); };
      f.addEventListener('load', finish, { once: true });
      f.addEventListener('error', finish, { once: true });
      f.src = card.dataset.src;
    };
    const loadAll = () => { for (let i = 0; i < 3; i++) loadNext(); };
    if ('IntersectionObserver' in window) {
      const io2 = new IntersectionObserver(([en]) => {
        if (en.isIntersecting) { io2.disconnect(); loadAll(); }
      }, { rootMargin: '600px 0px' });
      io2.observe(track);
    } else loadAll();

    /* "River": the strip drifts slowly and loops forever.
       Cards that leave on one side are re-ordered (CSS `order`) to the other side —
       nothing is moved in the DOM, so the Instagram embeds never reload. */
    track.classList.add('is-river');
    const SPEED = 38;            // px per second
    const RESUME_AFTER = 2500;   // ms after the last touch/scroll before drifting again
    const items = [...track.children];
    let order = items.slice();
    let pos = 0;                 // our own float scroll position
    let boost = 0;               // remaining distance from an arrow click
    let hovering = false, inView = false, lastTouch = 0, last = 0;
    const gap = () => parseFloat(getComputedStyle(track).columnGap) || 18;

    const applyOrder = () => order.forEach((el, i) => (el.style.order = i));
    applyOrder();

    // Keep exactly one card hidden off the left edge, so it can loop (and be swiped) both ways.
    const normalize = () => {
      const g = gap();
      const w = (el) => el.offsetWidth + g;
      let changed = false, guard = items.length * 2;
      while (guard-- > 0 && pos >= w(order[0]) + w(order[1])) { pos -= w(order[0]); order.push(order.shift()); changed = true; }
      while (guard-- > 0 && pos < w(order[0])) { const l = order.pop(); order.unshift(l); pos += w(l); changed = true; }
      if (changed) applyOrder();
      track.scrollLeft = pos;
    };
    normalize();

    const frame = (t) => {
      const dt = Math.min(0.05, last ? (t - last) / 1000 : 0);
      last = t;
      const idle = !hovering && Date.now() - lastTouch > RESUME_AFTER && !track.contains(document.activeElement);
      let move = idle && !reducedMotion ? SPEED * dt : 0;
      if (boost) {
        const b = Math.abs(boost) < 1 ? boost : boost * Math.min(1, dt * 7);
        boost -= b; move += b;
      }
      if (move) { pos += move; normalize(); }
      raf2 = inView && !document.hidden ? requestAnimationFrame(frame) : 0;
    };
    let raf2 = 0;
    const start = () => { if (!raf2 && inView && !document.hidden) { last = 0; raf2 = requestAnimationFrame(frame); } };

    // Someone swiping / scrolling the strip themselves
    track.addEventListener('scroll', () => {
      if (Math.abs(track.scrollLeft - pos) > 2) { pos = track.scrollLeft; boost = 0; lastTouch = Date.now(); normalize(); }
    }, { passive: true });
    ['pointerdown', 'wheel', 'touchstart'].forEach((ev) => track.addEventListener(ev, () => { lastTouch = Date.now(); }, { passive: true }));
    track.addEventListener('mouseenter', () => (hovering = true));
    track.addEventListener('mouseleave', () => (hovering = false));

    // Arrows glide one "page" either way
    const step = () => { const g = gap(); return (order[0].offsetWidth + g) * Math.max(1, Math.floor(track.clientWidth / (order[0].offsetWidth + g))); };
    $$('.insta-nav').forEach((b) => b.addEventListener('click', () => {
      boost += Number(b.dataset.dir) * step();
      lastTouch = Date.now();
      start();
    }));

    // Only animate while the section is on screen
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(([en]) => { inView = en.isIntersecting; start(); }).observe(track);
    } else { inView = true; start(); }
    document.addEventListener('visibilitychange', start);
  }

  /* ---------------------------------------------------------- reveal on scroll */
  const revealEls = $$('.reveal');
  if ('IntersectionObserver' in window && !reducedMotion) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach((en) => { if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); } });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    revealEls.forEach((el) => io.observe(el));
  } else {
    revealEls.forEach((el) => el.classList.add('in'));
  }

  /* ---------------------------------------------------------- premium scroll motion */
  if ('IntersectionObserver' in window && !reducedMotion) {
    const root = document.documentElement;
    root.classList.add('motion');

    // 1. Section titles rise in word by word from behind a mask
    const splitWords = (node, state) => {
      [...node.childNodes].forEach((child) => {
        if (child.nodeType === 3) {
          const frag = document.createDocumentFragment();
          child.textContent.split(/(\s+)/).forEach((part) => {
            if (!part) return;
            if (/^\s+$/.test(part)) { frag.appendChild(document.createTextNode(' ')); return; }
            const mask = document.createElement('span');
            const word = document.createElement('span');
            mask.className = 'w';
            word.textContent = part;
            word.style.setProperty('--wi', state.i++);
            // gradient text has to live on the word itself once it is inline-block
            if (node.tagName === 'EM') word.className = 'em-grad';
            else if (node.classList && node.classList.contains('text-gold')) word.className = 'text-gold';
            else if (node.classList && node.classList.contains('text-gold-dark')) word.className = 'text-gold-dark';
            mask.appendChild(word);
            frag.appendChild(mask);
          });
          child.replaceWith(frag);
        } else if (child.nodeType === 1 && child.tagName !== 'BR') {
          splitWords(child, state);
        }
      });
    };
    const titles = $$('.section-title');
    titles.forEach((t) => {
      t.setAttribute('aria-label', t.textContent.replace(/\s+/g, ' ').trim());
      splitWords(t, { i: 0 });
      t.classList.add('split');
    });

    // 2. Counters in the host stats
    const countUp = (el) => {
      const m = el.textContent.match(/^(\d+)(.*)$/);
      if (!m) return;
      const end = +m[1], suffix = m[2], t0 = performance.now(), dur = 1400;
      const step = (now) => {
        const p = Math.min(1, (now - t0) / dur), eased = 1 - Math.pow(1 - p, 4);
        el.textContent = Math.round(end * eased) + suffix;
        if (p < 1) requestAnimationFrame(step);
      };
      el.textContent = '0' + suffix;
      requestAnimationFrame(step);
    };

    // 3. Staggered children (pillars, logo wall, FAQ list)
    $$('.pillars > li, .brand-logos > li, .faq-list > .faq-item, .vg').forEach((el) => {
      el.style.setProperty('--i', [...el.parentElement.children].indexOf(el));
    });

    const motionIO = new IntersectionObserver((entries) => {
      entries.forEach((en) => {
        if (!en.isIntersecting) return;
        en.target.classList.add('in');
        if (en.target.matches('.host-facts')) $$('dt', en.target).forEach(countUp);
        motionIO.unobserve(en.target);
      });
    }, { threshold: 0.2, rootMargin: '0px 0px -60px 0px' });
    [...titles, ...$$('.host-facts')].forEach((el) => motionIO.observe(el));

    // 4. Scroll progress bar + parallax + hero fade, all in one rAF tick
    const bar = document.createElement('div');
    bar.className = 'scroll-progress';
    bar.setAttribute('aria-hidden', 'true');
    document.body.appendChild(bar);

    const heroCopy = $('.hero-copy');
    const heroMedia = $('.hero-media');
    // limit = share of the frame height the image may travel; matches the zoom headroom set in CSS
    const parallax = $$('.host-photo-frame img, .vg img').map((el) => ({
      el, speed: el.closest('.vg-main') ? 0.06 : 0.1, limit: el.closest('.host-photo-frame') ? 0.065 : 0.035,
    }));
    let ticking = false;
    const update = () => {
      ticking = false;
      const vh = window.innerHeight, sy = window.scrollY;
      const max = root.scrollHeight - vh;
      bar.style.transform = `scaleX(${max > 0 ? sy / max : 0})`;

      if (heroCopy && sy < vh * 1.2) {
        const p = Math.min(1, sy / vh);
        heroCopy.style.translate = `0 ${sy * 0.18}px`;
        heroCopy.style.opacity = String(1 - p * 0.9);
        if (heroMedia) heroMedia.style.translate = `0 ${sy * 0.08}px`;
      }
      parallax.forEach(({ el, speed, limit }) => {
        const r = el.parentElement.getBoundingClientRect();
        if (r.bottom < -100 || r.top > vh + 100) return;
        const cap = r.height * limit;
        const offset = Math.max(-cap, Math.min(cap, (r.top + r.height / 2 - vh / 2) * -speed));
        el.style.translate = `0 ${offset.toFixed(1)}px`;
      });
    };
    const requestTick = () => { if (!ticking) { ticking = true; requestAnimationFrame(update); } };
    window.addEventListener('scroll', requestTick, { passive: true });
    window.addEventListener('resize', requestTick);
    update();
  }

  /* ---------------------------------------------------------- guest form */
  const form = $('#guest-form');
  const success = $('#form-success');
  const status = $('#form-status');
  const submitBtn = $('.submit-btn', form);
  const endpoint = (window.FT_CONFIG && window.FT_CONFIG.formEndpoint) || '/api/guest-suggestions';

  const rules = {
    // order = the order fields appear, so the first error gets focus
    founderName: (v) => (v ? '' : 'Please enter the founder’s name.'),
    linkedin: (v) => {
      if (!v) return 'Please add the founder’s LinkedIn profile link.';
      try {
        const u = new URL(/^https?:\/\//i.test(v) ? v : 'https://' + v);
        return /(^|\.)linkedin\.com$/i.test(u.hostname) && u.pathname.length > 1 ? '' : 'Please paste a LinkedIn profile link (linkedin.com/in/…).';
      } catch { return 'Please paste a LinkedIn profile link (linkedin.com/in/…).'; }
    },
    companyName: (v) => (v ? '' : 'Please enter the company name.'),
    why: (v) => (v ? '' : 'Please tell us why you’d like to see this founder.'),
    yourEmail: (v) => (!v || /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v) ? '' : 'Please enter a valid email address.'),
  };

  const setError = (name, msg) => {
    const input = form.elements[name];
    const field = input.closest('.field');
    const err = $(`#${name}-error`);
    field.classList.toggle('invalid', !!msg);
    input.setAttribute('aria-invalid', msg ? 'true' : 'false');
    if (err) { err.textContent = msg || ''; msg ? input.setAttribute('aria-describedby', err.id) : input.removeAttribute('aria-describedby'); }
  };

  const validate = (name) => setError(name, rules[name](form.elements[name].value.trim())) || !rules[name](form.elements[name].value.trim());

  Object.keys(rules).forEach((name) => {
    const el = form.elements[name];
    el.addEventListener('blur', () => { if (el.value.trim() || el.closest('.field').classList.contains('invalid')) validate(name); });
    el.addEventListener('input', () => { if (el.closest('.field').classList.contains('invalid')) validate(name); });
  });

  const gIndustry = form.elements.industry;
  if (gIndustry) { const tone = () => gIndustry.classList.toggle('empty', !gIndustry.value); tone(); gIndustry.addEventListener('change', tone); }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    status.textContent = '';

    const results = Object.keys(rules).map((n) => [n, validate(n)]);
    const firstBad = results.find(([, ok]) => !ok);
    if (firstBad) { form.elements[firstBad[0]].focus(); return; }

    const fd = new FormData(form);
    const data = Object.fromEntries(fd.entries());
    Object.keys(data).forEach((k) => (data[k] = String(data[k]).trim()));
    data.topics = fd.getAll('topics').join(', '); // several checkboxes → one line
    if (data.linkedin && !/^https?:\/\//i.test(data.linkedin)) data.linkedin = 'https://' + data.linkedin;
    data.pageUrl = location.href;

    submitBtn.classList.add('loading');
    $('.btn-label', submitBtn).textContent = 'Sending…';

    try {
      const isAppsScript = /script\.google(usercontent)?\.com/.test(endpoint);
      const res = await fetch(endpoint, {
        method: 'POST',
        // Apps Script can't answer CORS preflights, so send as text/plain there.
        headers: { 'Content-Type': isAppsScript ? 'text/plain;charset=utf-8' : 'application/json' },
        body: JSON.stringify(data),
      });
      let body = {};
      try { body = await res.json(); } catch { /* non-JSON */ }

      if (res.ok && body.ok !== false) {
        form.reset();
        Object.keys(rules).forEach((n) => setError(n, ''));
        form.elements.industry && form.elements.industry.classList.add('empty');
        form.hidden = true;
        success.hidden = false;
        success.focus({ preventScroll: true });
        success.scrollIntoView({ behavior: reducedMotion ? 'auto' : 'smooth', block: 'center' });
      } else if (body.errors) {
        Object.entries(body.errors).forEach(([n, m]) => form.elements[n] && setError(n, m));
        status.textContent = 'Please check the highlighted fields.';
      } else {
        status.textContent = body.error || 'Something went wrong. Please try again in a moment.';
      }
    } catch (err) {
      status.textContent = 'We couldn’t reach the server. Please check your connection and try again.';
    } finally {
      submitBtn.classList.remove('loading');
      $('.btn-label', submitBtn).textContent = 'Suggest a Guest';
    }
  });

  $('#suggest-another').addEventListener('click', () => {
    success.hidden = true;
    form.hidden = false;
    form.elements.founderName.focus();
  });

  /* ---------------------------------------------------------- "be a guest" lead forms (section + pop-up) */
  const CFG = window.FT_CONFIG || {};
  const applyEndpoint = CFG.applyEndpoint || '/api/podcast-applications';
  const store = {
    get(k) { try { return localStorage.getItem(k); } catch { return null; } },
    set(k, v) { try { localStorage.setItem(k, v); } catch { /* storage blocked */ } },
  };
  const KEY_APPLIED = 'ft-applied';
  const KEY_SNOOZE = 'ft-apply-popup-closed-at';

  const leadRules = {
    fullName: (v) => (v ? '' : 'Please enter your name.'),
    company: (v) => (v ? '' : 'Please enter your company name.'),
    email: (v) => (!v ? 'Please enter your email.' : /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v) ? '' : 'Please enter a valid email address.'),
    phone: (v) => (!v ? 'Please enter your phone number.' : /^\+?[\d\s().-]{7,20}$/.test(v) && v.replace(/\D/g, '').length >= 7 ? '' : 'Please enter a valid phone number.'),
    linkedin: (v) => {
      if (!v) return '';
      try { const u = new URL(/^https?:\/\//i.test(v) ? v : 'https://' + v); return /\./.test(u.hostname) ? '' : 'Please enter a valid link.'; }
      catch { return 'Please enter a valid link.'; }
    },
    story: (v, el) => (el.hasAttribute('data-required') && !v ? 'Please share a few lines about your story.' : ''),
  };

  function initLeadForm(f, onDone) {
    const wrap = f.parentElement;
    const ok = $('.form-success', wrap);
    const st = $('.form-status', f);
    const btn = $('.submit-btn', f);
    const btnText = $('.btn-label', btn).textContent;
    const names = Object.keys(leadRules).filter((n) => f.elements[n]);

    const setErr = (name, msg) => {
      const input = f.elements[name];
      const field = input.closest('.field');
      const err = $('.field-error', field);
      field.classList.toggle('invalid', !!msg);
      input.setAttribute('aria-invalid', msg ? 'true' : 'false');
      if (err) {
        if (!err.id) err.id = input.id + '-error';
        err.textContent = msg || '';
        msg ? input.setAttribute('aria-describedby', err.id) : input.removeAttribute('aria-describedby');
      }
    };
    const check = (name) => {
      const el = f.elements[name];
      const msg = leadRules[name](el.value.trim(), el);
      setErr(name, msg);
      return !msg;
    };

    names.forEach((name) => {
      const el = f.elements[name];
      el.addEventListener('blur', () => { if (el.value.trim() || el.closest('.field').classList.contains('invalid')) check(name); });
      el.addEventListener('input', () => { if (el.closest('.field').classList.contains('invalid')) check(name); });
    });

    const sel = f.elements.industry;
    if (sel) { const tone = () => sel.classList.toggle('empty', !sel.value); tone(); sel.addEventListener('change', tone); }

    f.addEventListener('submit', async (e) => {
      e.preventDefault();
      st.textContent = '';
      const bad = names.filter((n) => !check(n));
      if (bad.length) { f.elements[bad[0]].focus(); return; }

      const data = Object.fromEntries(new FormData(f).entries());
      Object.keys(data).forEach((k) => (data[k] = String(data[k]).trim()));
      if (data.linkedin && !/^https?:\/\//i.test(data.linkedin)) data.linkedin = 'https://' + data.linkedin;
      data.formType = 'application';
      data.source = f.dataset.source || 'section';
      data.pageUrl = location.href;

      btn.classList.add('loading');
      $('.btn-label', btn).textContent = 'Sending…';
      try {
        const isAppsScript = /script\.google(usercontent)?\.com/.test(applyEndpoint);
        const res = await fetch(applyEndpoint, {
          method: 'POST',
          headers: { 'Content-Type': isAppsScript ? 'text/plain;charset=utf-8' : 'application/json' },
          body: JSON.stringify(data),
        });
        let body = {};
        try { body = await res.json(); } catch { /* non-JSON */ }

        if (res.ok && body.ok !== false) {
          f.reset();
          names.forEach((n) => setErr(n, ''));
          if (sel) sel.classList.add('empty');
          f.hidden = true;
          ok.hidden = false;
          ok.focus({ preventScroll: true });
          store.set(KEY_APPLIED, '1');
          onDone && onDone();
        } else if (body.errors) {
          Object.entries(body.errors).forEach(([n, m]) => f.elements[n] && setErr(n, m));
          st.textContent = 'Please check the highlighted fields.';
        } else {
          st.textContent = body.error || 'Something went wrong. Please try again in a moment.';
        }
      } catch {
        st.textContent = 'We couldn’t reach the server. Please check your connection and try again.';
      } finally {
        btn.classList.remove('loading');
        $('.btn-label', btn).textContent = btnText;
      }
    });
  }

  const applyForm = $('#apply-form');
  if (applyForm) initLeadForm(applyForm, () => $('.form-success', applyForm.parentElement).scrollIntoView({ behavior: reducedMotion ? 'auto' : 'smooth', block: 'center' }));

  /* ---------------------------------------------------------- "be a guest" pop-up */
  const modal = $('#apply-modal');
  const popupForm = $('#apply-popup-form');
  if (modal && popupForm && typeof modal.showModal === 'function') {
    initLeadForm(popupForm);

    let shown = false;
    const snoozeMs = (Number(CFG.popupSnoozeDays) || 3) * 864e5;
    const snoozed = () => {
      const t = Number(store.get(KEY_SNOOZE));
      return t && Date.now() - t < snoozeMs;
    };
    const blocked = () => shown || modal.open || store.get(KEY_APPLIED) || snoozed();
    // Don't interrupt someone typing in a form or using the mobile menu.
    const busy = () => document.body.classList.contains('nav-open') ||
      (document.activeElement && document.activeElement.closest && document.activeElement.closest('form'));

    const open = (force) => {
      if (!force && (blocked() || busy())) return;
      if (modal.open) return;
      shown = true;
      modal.showModal();
      document.documentElement.classList.add('modal-lock');
    };
    const close = () => { if (modal.open) modal.close(); };

    modal.addEventListener('close', () => {
      document.documentElement.classList.remove('modal-lock');
      if (!store.get(KEY_APPLIED)) store.set(KEY_SNOOZE, String(Date.now()));
    });
    $$('[data-close-modal]', modal).forEach((b) => b.addEventListener('click', close));
    // click on the dimmed backdrop closes it
    modal.addEventListener('click', (e) => { if (e.target === modal) close(); });

    // Triggers: time on page, scroll depth, or exit intent (desktop)
    const delay = (Number(CFG.popupDelaySeconds) || 20) * 1000;
    setTimeout(() => open(), delay);

    const depth = (Number(CFG.popupScrollPercent) || 45) / 100;
    const onDepth = () => {
      const max = document.documentElement.scrollHeight - window.innerHeight;
      if (max > 0 && window.scrollY / max >= depth) { window.removeEventListener('scroll', onDepth); open(); }
    };
    window.addEventListener('scroll', onDepth, { passive: true });

    document.addEventListener('mouseout', (e) => {
      if (!e.relatedTarget && e.clientY <= 0 && window.matchMedia('(pointer: fine)').matches) open();
    });

    // Allow any element with data-open-apply to open it on demand, and ?apply=1 for testing/campaign links
    $$('[data-open-apply]').forEach((el) => el.addEventListener('click', (e) => { e.preventDefault(); open(true); }));
    if (/[?&]apply=1\b/.test(location.search)) open(true);
  }

  /* ---------------------------------------------------------- misc */
  const y = $('#year');
  if (y) y.textContent = new Date().getFullYear();
})();
