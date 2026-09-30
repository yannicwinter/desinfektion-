/* DRK Arbeitssicherheit – kleine Helfer, kein Framework */
(function () {
  'use strict';
  var doc = document.documentElement;
  doc.classList.add('js');
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Header-Schatten beim Scrollen
  var onScroll = function () { doc.classList.toggle('is-scrolled', window.scrollY > 8); };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  // Aktiven Menüpunkt auf kleinen Bildschirmen sichtbar scrollen
  var cur = document.querySelector('.nav a[aria-current="page"]');
  if (cur && cur.scrollIntoView && window.innerWidth < 1000) {
    var list = cur.closest('.nav__list');
    if (list && list.scrollWidth > list.clientWidth) list.scrollLeft = cur.offsetLeft - 16;
  }

  // Einblenden beim Scrollen
  var items = document.querySelectorAll('.reveal');
  if ('IntersectionObserver' in window && !reduce) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { en.target.classList.add('is-in'); io.unobserve(en.target); }
      });
    }, { rootMargin: '0px 0px -8% 0px' });
    items.forEach(function (el, i) { el.style.transitionDelay = Math.min(i % 4, 3) * 70 + 'ms'; io.observe(el); });
  } else {
    items.forEach(function (el) { el.classList.add('is-in'); });
  }

  // Akkordeon: weich auf- und zuklappen, Termine beim Öffnen nachladen
  var loadDates = function (item) {
    var box = item.querySelector('[data-dates]');
    if (!box || box.dataset.loaded) return;
    box.dataset.loaded = '1';
    fetch(box.getAttribute('data-dates'), { headers: { 'X-Requested-With': 'fetch' } })
      .then(function (r) { if (!r.ok) throw new Error(r.status); return r.text(); })
      .then(function (html) { box.querySelector('.acc__dates-list').innerHTML = html; })
      .catch(function () {
        box.querySelector('.acc__dates-list').innerHTML = '<p class="muted small">Termine konnten gerade nicht geladen werden.</p>';
      });
  };
  document.querySelectorAll('details.acc__item').forEach(function (d) {
    var sum = d.querySelector('summary');
    var body = d.querySelector('.acc__body');
    if (d.open) loadDates(d);
    sum.addEventListener('click', function (e) {
      if (reduce || !body.animate) { setTimeout(function () { if (d.open) loadDates(d); }); return; }
      e.preventDefault();
      if (d.dataset.anim) return;
      d.dataset.anim = '1';
      if (!d.open) {
        d.open = true;
        loadDates(d);
        var h = body.scrollHeight;
        body.animate([{ height: '0px', opacity: 0 }, { height: h + 'px', opacity: 1 }], { duration: 380, easing: 'cubic-bezier(.2,.7,.2,1)' })
          .onfinish = function () { delete d.dataset.anim; };
        if (d.id && history.replaceState) history.replaceState(null, '', '#' + d.id);
      } else {
        var h2 = body.scrollHeight;
        d.classList.add('is-closing');
        body.animate([{ height: h2 + 'px', opacity: 1 }, { height: '0px', opacity: 0 }], { duration: 280, easing: 'cubic-bezier(.4,0,.2,1)' })
          .onfinish = function () { d.open = false; delete d.dataset.anim; d.classList.remove('is-closing'); };
      }
    });
  });

  // Direktlink auf einen Kurs (#slug) öffnet das passende Akkordeon
  var openHash = function () {
    if (!location.hash) return;
    var el = document.getElementById(decodeURIComponent(location.hash.slice(1)));
    if (el && el.tagName === 'DETAILS') {
      el.open = true;
      loadDates(el);
      setTimeout(function () { el.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' }); }, 60);
    }
  };
  openHash();
  window.addEventListener('hashchange', openHash);

  // Kursauswahl Startseite → /termine/{kurs}
  var finder = document.querySelector('[data-finder]');
  if (finder) {
    finder.addEventListener('submit', function (e) {
      e.preventDefault();
      location.href = finder.getAttribute('action').replace(/\/$/, '') + '/' + encodeURIComponent(finder.querySelector('[name=kurs]').value);
    });
  }

  // Terminfilter (Ort, Monat, Wochentag)
  var filter = document.querySelector('[data-date-filter]');
  if (filter) {
    var rows = document.querySelectorAll('[data-date-list] .date');
    var count = document.querySelector('[data-date-count]');
    var empty = document.querySelector('[data-date-empty]');
    var apply = function () {
      var sel = {};
      filter.querySelectorAll('select').forEach(function (s) { sel[s.name] = s.value; });
      var n = 0;
      rows.forEach(function (r) {
        var ok = Object.keys(sel).every(function (k) { return !sel[k] || r.getAttribute('data-' + k) === sel[k]; });
        r.hidden = !ok;
        if (ok) n++;
      });
      if (count) count.textContent = n + (n === 1 ? ' Termin' : ' Termine');
      if (empty) empty.hidden = n > 0;
    };
    filter.addEventListener('change', apply);
    var reset = document.querySelector('[data-date-reset]');
    if (reset) reset.addEventListener('click', function (e) { e.preventDefault(); filter.reset(); apply(); });
  }

  // Admin: Zeichenzähler für Google-Titel/-Beschreibung, Löschbestätigung
  document.querySelectorAll('[data-count]').forEach(function (el) {
    var max = +el.getAttribute('data-count');
    var out = document.createElement('small');
    out.className = 'count';
    el.insertAdjacentElement('afterend', out);
    var upd = function () {
      out.textContent = el.value.length + ' / ' + max + ' Zeichen';
      out.classList.toggle('count--over', el.value.length > max);
    };
    el.addEventListener('input', upd);
    upd();
  });
  document.querySelectorAll('[data-confirm]').forEach(function (b) {
    b.addEventListener('click', function (e) { if (!confirm(b.getAttribute('data-confirm'))) e.preventDefault(); });
  });
})();

