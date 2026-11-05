/* =====================================================================
   FOUNDERS TABLE — EPISODES
   ---------------------------------------------------------------------
   To add a new episode: copy one block below, paste it at the TOP of the
   list (newest first) and change the values. That's it — the page builds
   the row, thumbnails and buttons automatically.

   youtubeId : the part after "watch?v=" in the YouTube link
               e.g. https://www.youtube.com/watch?v=9wfFUsfYh6E → "9wfFUsfYh6E"
   reels     : 1–3 Shorts. id = the part after "/shorts/"
               e.g. https://www.youtube.com/shorts/Kvn0XsjTozw → "Kvn0XsjTozw"
   thumbnail : optional — custom image path. Leave out to use YouTube's.
   ===================================================================== */

window.FT_EPISODES = [
  {
    number: 4,
    youtubeId: '9wfFUsfYh6E',
    title: 'The Man Behind ABC',
    guest: 'Muhammed Madani',
    company: 'Chairman, ABC Group',
    duration: '50:27',
    description:
      'From a single building-materials shop in Taliparamba, Kannur, in 1998 to a group trading across India, the Middle East and Africa. Muhammed Madani on where the name ABC came from, expanding from India to the UAE and Africa, putting culture first, and why a founder’s own name and face matter as much as the brand.',
    reels: [
      { id: 'Uhz5HZ96xeU', title: 'Half Gujarati, Half Marwadi… and Somehow ADANI?' },
    ],
  },
  {
    number: 3,
    youtubeId: 'JSBcrwMG1as',
    title: 'The Founder Who Built Kerala’s Biggest Real Estate Brand',
    guest: 'Sunil Kumar',
    company: 'Founder, Asset Homes',
    duration: '35:22',
    description:
      'An engineer with a Dubai dream who went independent in 1994 and went on to found Asset Homes, now Kerala’s highest-rated builder with 95+ projects across 10 districts. On the early days, leadership lessons from Gandhi, why the team matters more than the buildings, and what Gen Z expects from employers.',
    reels: [
      { id: 'sG_M2VvWSd4', title: 'He Had a Vision in 2013. What Happened Next?' },
      { id: '0NSvL8MU-6c', title: 'Founder of Asset Homes Joins The Founders Table' },
    ],
  },
  {
    number: 2,
    youtubeId: 'Uh7COeqqClc',
    title: 'Growing Up as an Emirati: Tradition, Mindset & Modern UAE',
    guest: 'An Emirati Perspective', // TODO: replace with the guest’s name
    company: 'Special guest',         // TODO: replace with the guest’s title / company
    duration: '41:53',
    description:
      'A candid conversation on Emirati culture, business licences, changing regulations and the rise of content creators — and what it really means to build and grow a business in the UAE.',
    reels: [
      { id: 'PH2YLI3vKFE', title: 'Why Are Kandoras Different Colours?' },
      { id: 'nC4Kws7PV5Q', title: 'Why Does the UAE Keep Changing Its Rules?' },
    ],
  },
  {
    number: 1,
    youtubeId: 'v-xePWYwSyk',
    title: 'From a Job Loss in 2008 to Building RAG Holdings',
    guest: 'Rassal Ahmed & Mohammed Aslam',
    company: 'CEO & MD, RAG Holdings',
    duration: '51:21',
    description:
      'The first episode goes back to where it all began. RAG Holdings’ founders share how the company started after the 2008 financial crisis, the challenges and decisions along the way, and the mindset behind building a multinational business with a long-term vision.',
    reels: [
      { id: 'C96g-BWByok', title: 'From Job Loss During a Financial Crisis to a Multinational Company' },
      { id: 'qJu0azhUQ4w', title: 'From 10,000 Steps a Day to Building a Multinational Company' },
    ],
  },
];

/* =====================================================================
   INSTAGRAM REELS — "On Instagram" strip
   ---------------------------------------------------------------------
   Paste the code from the post link, newest first.
   e.g. https://www.instagram.com/p/De1qEExO-B8/  → 'De1qEExO-B8'
   (links with /reel/ work the same way)
   ===================================================================== */
window.FT_INSTAGRAM = [
  'De1qEExO-B8',
  'DemNtY6ojNV',
  'DeZUux2IVeZ',
  'DeQso3PoiXh',
  'DeMY1yRoAFX',
  'DeECfjLBxcE',
  'Dd34jtMu3wB',
  'DdrCbzDAhQi',
  'DdtI6VZK7jN',
  'DdmAiDig_0p',
  'DdWa0IOAyU-',
  'DdRh8ZsQjEu',
  'DdMMiNXgqqW',
];
