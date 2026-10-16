/* IT Security Nepal — Alpine.js components (ported from the approved Phase 1 prototype).
   Progressive enhancement: all page content is server-rendered Blade; JS filters, reveals and submits forms. */

document.addEventListener('alpine:init', () => {
  const Alpine = window.Alpine;

  /* ---------- shared course index (prototype stand-in for the DB/API) ---------- */
  let indexPromise;
  const loadIndex = () => (indexPromise ??= fetch('/courses-index.json').then((r) => r.json()));

  /* ---------- Header: scroll shadow, mega menu, mobile drawer ---------- */
  Alpine.data('siteHeader', () => ({
    mega: false,
    mobile: false,
    scrolled: false,
    init() {
      const onScroll = () => (this.scrolled = window.scrollY > 8);
      window.addEventListener('scroll', onScroll, { passive: true });
      onScroll();
      window.matchMedia('(min-width: 64rem)').addEventListener('change', (e) => e.matches && (this.mobile = false));
    },
    closeAll() { this.mega = false; this.mobile = false; },
  }));

  /* ---------- Search overlay ---------- */
  Alpine.data('courseSearch', () => ({
    open: false, q: '', all: [], loading: false, active: -1,
    async openSearch() {
      this.open = true;
      this.$nextTick(() => this.$refs.input.focus());
      if (!this.all.length) { this.loading = true; this.all = await loadIndex(); this.loading = false; }
    },
    get results() {
      const q = this.q.trim().toLowerCase();
      if (q.length < 2) return [];
      const terms = q.split(/\s+/);
      return this.all
        .map((c) => {
          const hay = `${c.code} ${c.name} ${c.certification} ${c.categoryName}`.toLowerCase();
          if (!terms.every((t) => hay.includes(t))) return null;
          return { c, score: (c.code.toLowerCase().startsWith(q) ? 3 : 0) + (c.name.toLowerCase().includes(q) ? 2 : 0) + (c.featured ? 1 : 0) };
        })
        .filter(Boolean).sort((a, b) => b.score - a.score).slice(0, 8).map((r) => r.c);
    },
    move(d) { const n = this.results.length; if (n) this.active = (this.active + d + n) % n; },
    go() { const r = this.results[this.active >= 0 ? this.active : 0]; if (r) window.location.href = r.url; },
  }));

  /* ---------- Enquiry form: client-side validation + real submit (POST /enquiries, CSRF, server validation) ---------- */
  Alpine.data('enquiryForm', (defaultCourse = '', corporate = false, audience = 'Individual') => ({
    form: { name: '', phone: '', email: '', course: defaultCourse, mode: 'Classroom', audience, message: '', consent: false, org: '', size: corporate ? '1–5' : '2–5 people' },
    errors: {}, state: 'idle', submitted: false,
    rules: {
      name: (v) => (v.length < 2 ? 'Please enter your full name.' : ''),
      phone: (v) => (!/^(\+?977[-\s]?)?(9\d{9}|0\d{1,2}[-\s]?\d{6,7})$/.test(v.replace(/[\s-]/g, '')) ? 'Enter a valid Nepali mobile or landline number, e.g. 98XXXXXXXX.' : ''),
      email: (v) => (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v) ? 'Enter a valid email address, e.g. name@example.com.' : ''),
      course: (v) => (!v ? (corporate ? 'Choose a training area.' : 'Choose a course, or "Not sure yet".') : ''),
      consent: (v) => (!v ? 'Please agree so we can contact you.' : ''),
      org: (v) => (corporate && v.length < 2 ? 'Enter your organisation name.' : ''),
    },
    validate(field) {
      const msg = this.rules[field](this.form[field]);
      if (msg) this.errors[field] = msg; else delete this.errors[field];
      this.errors = { ...this.errors };
    },
    async submit() {
      this.submitted = true;
      Object.keys(this.rules).forEach((f) => this.validate(f));
      if (Object.keys(this.errors).length) {
        // wait for :aria-invalid bindings to render, then move focus to the first invalid field
        this.$nextTick(() => this.$el.querySelector('[aria-invalid="true"]')?.focus());
        return;
      }
      this.state = 'loading';
      const el = this.$el.closest('form') || this.$el;
      const body = new FormData(el);
      Object.entries(this.form).forEach(([k, v]) => body.set(k, typeof v === 'boolean' ? (v ? '1' : '0') : v));
      try {
        const res = await fetch(el.action, { method: 'POST', body, headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
        if (res.status === 422) {
          const data = await res.json();
          const map = { organisation: 'org', group_size: 'size', course_id: 'course', interest: 'course' };
          this.errors = Object.fromEntries(Object.entries(data.errors || {}).map(([k, v]) => [map[k] || k, v[0]]));
          this.state = 'idle';
          this.$nextTick(() => this.$el.querySelector('[aria-invalid="true"]')?.focus());
          return;
        }
        this.state = res.ok ? 'success' : 'error';
      } catch (e) {
        this.state = 'error';
      }
      if (this.state !== 'success') return;
      window.dispatchEvent(new CustomEvent('itsn:track', { detail: { event: 'enquiry_submit', course: this.form.course, audience: corporate ? 'Corporate' : this.form.audience } }));
    },
    reset() {
      this.form = { ...this.form, name: '', phone: '', email: '', message: '', consent: false };
      this.errors = {}; this.state = 'idle'; this.submitted = false;
    },
  }));

  /* ---------- Course directory filters (filters server-rendered cards) ---------- */
  Alpine.data('courseDirectory', (initial = {}) => ({
    q: '', cats: [], levels: [], modes: [], sort: 'featured', page: 1, perPage: 12, total: 0, visible: 0, filtersOpen: false, loading: false,
    names: initial.names || {}, isLg: false,
    init() {
      const mq = matchMedia('(min-width: 64rem)');
      this.isLg = mq.matches;
      mq.addEventListener('change', (e) => { this.isLg = e.matches; if (e.matches) this.filtersOpen = false; });
      const p = new URLSearchParams(location.search);
      this.q = p.get('q') || '';
      this.cats = p.getAll('category').length ? p.getAll('category') : initial.cats || [];
      this.levels = p.getAll('level');
      this.modes = p.getAll('mode');
      this.cards = [...this.$root.querySelectorAll('[data-course]')];
      this.cards.forEach((el, i) => (el.dataset.order = i));
      this.total = this.cards.length;
      this.$watch('q', () => this.refresh(true));
      ['cats', 'levels', 'modes', 'sort'].forEach((k) => this.$watch(k, () => this.refresh(true)));
      this.refresh(false);
    },
    match(el) {
      const q = this.q.trim().toLowerCase();
      return (!q || q.split(/\s+/).every((t) => el.dataset.search.includes(t)))
        && (!this.cats.length || this.cats.includes(el.dataset.category))
        && (!this.levels.length || this.levels.includes(el.dataset.level))
        && (!this.modes.length || this.modes.some((m) => el.dataset.modes.split('|').includes(m)));
    },
    refresh(resetPage) {
      if (resetPage) this.page = 1;
      const grid = this.$refs.grid;
      const matched = this.cards.filter((el) => this.match(el));
      const rank = { Beginner: 0, Intermediate: 1, Advanced: 2 };
      const sorters = {
        featured: (a, b) => (b.dataset.featured === 'true') - (a.dataset.featured === 'true') || a.dataset.order - b.dataset.order,
        az: (a, b) => a.querySelector('h2,h3').textContent.trim().localeCompare(b.querySelector('h2,h3').textContent.trim()),
        level: (a, b) => rank[a.dataset.level] - rank[b.dataset.level] || a.dataset.order - b.dataset.order,
      };
      matched.sort(sorters[this.sort]);
      this.cards.forEach((el) => (el.parentElement.hidden = true));
      matched.forEach((el, i) => {
        grid.appendChild(el.parentElement);
        el.parentElement.hidden = i >= this.page * this.perPage;
      });
      this.visible = matched.length;
      // keep URL shareable (Phase 2: real query params handled server-side, canonical stays /course/)
      const p = new URLSearchParams();
      if (this.q) p.set('q', this.q);
      this.cats.forEach((c) => p.append('category', c));
      this.levels.forEach((l) => p.append('level', l));
      this.modes.forEach((m) => p.append('mode', m));
      history.replaceState(null, '', p.toString() ? `?${p}` : location.pathname);
    },
    get pages() { return Math.max(1, Math.ceil(this.visible / this.perPage)); },
    get activeCount() { return this.cats.length + this.levels.length + this.modes.length + (this.q ? 1 : 0); },
    loadMore() { this.loading = true; setTimeout(() => { this.page++; this.refresh(false); this.loading = false; }, 350); },
    clear() { this.q = ''; this.cats = []; this.levels = []; this.modes = []; },
    toggle(list, v) { const i = this[list].indexOf(v); i >= 0 ? this[list].splice(i, 1) : this[list].push(v); },
  }));

  /* ---------- Generic list filter (batches, blog) ---------- */
  Alpine.data('listFilter', (selector, limit = 0) => ({
    active: 'all', q: '', count: 0, total: 0,
    init() { this.items = [...this.$root.querySelectorAll(selector)]; this.$watch('active', () => this.apply()); this.$watch('q', () => this.apply()); this.apply(); },
    // set(): toggle a filter value (clicking the active one again returns to "all")
    set(v) { this.active = this.active === v ? 'all' : v; },
    apply() {
      const q = this.q.trim().toLowerCase();
      let n = 0;
      this.items.forEach((el) => {
        const ok = (this.active === 'all' || el.dataset.category === this.active) && (!q || el.textContent.toLowerCase().includes(q));
        n += ok;
        el.hidden = !ok || (limit && n > limit); // optional cap: show at most `limit` matches
      });
      this.total = n;
      this.count = limit ? Math.min(n, limit) : n;
    },
  }));

  /* ---------- FAQ search ---------- */
  Alpine.data('faqSearch', () => ({
    q: '', group: 'all', count: 0,
    init() { this.$watch('q', () => this.apply()); this.$watch('group', () => this.apply()); this.apply(); },
    apply() {
      const q = this.q.trim().toLowerCase();
      let n = 0;
      this.$root.querySelectorAll('[data-faq]').forEach((el) => {
        const ok = (this.group === 'all' || el.dataset.group === this.group) && (!q || el.textContent.toLowerCase().includes(q));
        el.hidden = !ok; n += ok;
        if (q && ok) el.open = true;
      });
      this.$root.querySelectorAll('[data-faq-group]').forEach((g) => (g.hidden = !g.querySelector('[data-faq]:not([hidden])')));
      this.count = n;
    },
  }));

  /* ---------- Course Finder wizard (mocked recommendation rules) ---------- */
  Alpine.data('courseFinder', () => ({
    step: 0, steps: 4, answers: { interest: '', experience: '', goal: '', mode: '' }, error: '', results: [], loading: false,
    questions: ['interest', 'experience', 'goal', 'mode'],
    get progress() { return Math.round((Math.min(this.step, this.steps) / this.steps) * 100); },
    next() {
      const key = this.questions[this.step];
      if (!this.answers[key]) { this.error = 'Please choose an option to continue.'; this.$nextTick(() => this.$root.querySelector('[role=alert]')?.focus()); return; }
      this.error = '';
      this.step++;
      if (this.step === this.steps) this.recommend();
      this.$nextTick(() => this.$root.querySelector('[data-step-heading]:not([hidden])')?.focus());
    },
    back() { this.error = ''; if (this.step > 0) this.step--; this.$nextTick(() => this.$root.querySelector('[data-step-heading]:not([hidden])')?.focus()); },
    restart() { this.step = 0; this.answers = { interest: '', experience: '', goal: '', mode: '' }; this.results = []; },
    async recommend() {
      this.loading = true;
      const all = await loadIndex();
      // Rule table: interest → ordered path by experience level (only real catalogue slugs)
      const paths = {
        linux: { beginner: ['linux-bootcamp', 'red-hat-system-administration', 'red-hat-system-administration-ii'], some: ['red-hat-system-administration-ii', 'red-hat-certified-system-engineer', 'comptia-linux'], experienced: ['red-hat-certified-system-engineer', 'red-hat-openshift', 'red-hat-high-availability-clustering'] },
        security: { beginner: ['junior-security-analyst-track', 'comptia-security', 'digital-forensics-essentials'], some: ['ethical-hacking-expert', 'certified-network-defender-course', 'cysa-comptia-cybersecurity-analyst'], experienced: ['penetration-testing-course', 'cissp-certified-information-systems-security-professional', 'microsoft-cybersecurity-architect'] },
        network: { beginner: ['cisco-certified-network-associate', 'network-comptia-network', 'comptia-a-ceritification'], some: ['cisco-certified-network-associate', 'cisco-encor-350-401', 'certified-network-defender-course'], experienced: ['cisco-encor-350-401', 'cisco-enarsi-300-410', 'cisco-dccor-350-601'] },
        devops: { beginner: ['red-hat-system-administration', 'devops-mastery', 'linux-bootcamp'], some: ['devops-mastery', 'certified-kubernetes-administrator', 'red-hat-openshift'], experienced: ['certified-kubernetes-administrator', 'certified-kubernetes-security-specialist', 'az-400-microsoft-devops-course'] },
        cloud: { beginner: ['microsoft-azure-administrator', 'comptia-cloud', 'microsoft-365-administrator'], some: ['microsoft-azure-administrator', 'azure-infrastructure-architect-course', 'microsoft-identity-and-access-administrator'], experienced: ['azure-infrastructure-architect-course', 'az-400-microsoft-devops-course', 'ccsp-certified-cloud-security-professional'] },
        unsure: { beginner: ['linux-bootcamp', 'comptia-a-ceritification', 'job-ready'], some: ['red-hat-system-administration', 'cisco-certified-network-associate', 'junior-security-analyst-track'], experienced: ['red-hat-certified-system-engineer', 'devops-mastery', 'ethical-hacking-expert'] },
      };
      let slugs = [...paths[this.answers.interest][this.answers.experience]];
      if (this.answers.goal === 'abroad' && !slugs.includes('job-ready')) slugs = ['job-ready', ...slugs].slice(0, 3);
      const bySlug = Object.fromEntries(all.map((c) => [c.slug, c]));
      await new Promise((r) => setTimeout(r, 500));
      this.results = slugs.map((s, i) => ({ ...bySlug[s], why: i === 0 ? 'Best next step for you' : 'Good follow-on' })).filter((c) => c.slug);
      this.loading = false;
      window.dispatchEvent(new CustomEvent('itsn:track', { detail: { event: 'course_finder_complete', interest: this.answers.interest } }));
    },
  }));

  /* ---------- Prototype annotation toggle ---------- */
  Alpine.data('protoBar', () => ({
    clean: document.documentElement.classList.contains('proto-clean'),
    toggle() {
      this.clean = !this.clean;
      document.documentElement.classList.toggle('proto-clean', this.clean);
      try { localStorage.setItem('itsn-proto-clean', this.clean ? '1' : '0'); } catch (e) {}
    },
  }));
});

/* ---------- Scroll reveal (no library; respects reduced motion via CSS) ---------- */
document.addEventListener('DOMContentLoaded', () => {
  const els = document.querySelectorAll('.reveal');
  if (!('IntersectionObserver' in window)) { els.forEach((el) => el.classList.add('is-visible')); return; }
  const io = new IntersectionObserver((entries) => {
    entries.forEach((e) => { if (e.isIntersecting) { e.target.classList.add('is-visible'); io.unobserve(e.target); } });
  }, { rootMargin: '0px 0px -8% 0px' });
  els.forEach((el) => io.observe(el));
});

/* ---------- Analytics hook (prototype: logs to console; Phase 2: GA4/GTM dataLayer, no PII) ---------- */
window.addEventListener('itsn:track', (e) => console.info('[analytics]', e.detail));
document.addEventListener('click', (e) => {
  const a = e.target.closest('a[href^="tel:"], a[href*="wa.me"]');
  if (a) window.dispatchEvent(new CustomEvent('itsn:track', { detail: { event: a.href.startsWith('tel:') ? 'phone_click' : 'whatsapp_click' } }));
});
