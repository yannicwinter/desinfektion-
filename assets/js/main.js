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
        var ok = Object.keys(sel).every(function (k) {
          var v = sel[k], have = r.getAttribute('data-' + k);
          if (!v) return true;
          if (k === 'wtag' && (v === 'we' || v === 'wk')) return v === 'we' ? +have >= 6 : +have < 6;
          return have === v;
        });
        r.hidden = !ok;
        if (ok) n++;
      });
      // Monatsüberschriften ohne sichtbare Termine ausblenden, freie Termine zählen
      document.querySelectorAll('[data-date-list] .dates__month').forEach(function (m) {
        var el = m.nextElementSibling, any = false;
        while (el && !el.classList.contains('dates__month')) { if (!el.hidden) any = true; el = el.nextElementSibling; }
        m.hidden = !any;
      });
      var free = 0;
      rows.forEach(function (r) { if (!r.hidden && !r.classList.contains('date--full')) free++; });
      if (count) count.textContent = free + (free === 1 ? ' freier Termin' : ' freie Termine');
      if (empty) empty.hidden = n > 0;
    };
    filter.addEventListener('change', apply);
    apply(); // Vorauswahl aus der Startseiten-Suche (?ort=…&wann=…) sofort anwenden
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

  // Mobil: „Mehr“-Menü der Tab-Leiste
  var more = document.getElementById('mehr');
  var moreBtn = document.querySelector('[data-more-open]');
  if (more && moreBtn) {
    var openMore = function () {
      more.hidden = false;
      requestAnimationFrame(function () { more.classList.add('is-open'); });
      document.body.classList.add('more-open');
      moreBtn.setAttribute('aria-expanded', 'true');
      var first = more.querySelector('a');
      if (first) first.focus();
    };
    var closeMore = function () {
      more.classList.remove('is-open');
      document.body.classList.remove('more-open');
      moreBtn.setAttribute('aria-expanded', 'false');
      setTimeout(function () { more.hidden = true; }, reduce ? 0 : 250);
      moreBtn.focus();
    };
    moreBtn.addEventListener('click', openMore);
    more.querySelectorAll('[data-more-close]').forEach(function (b) { b.addEventListener('click', closeMore); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !more.hidden) closeMore(); });
  }
})();

/* Gestaltete Auswahlmenüs statt der Standard-Menüs des Browsers (select[data-nice]).
   Das echte <select> bleibt für Formular, Tastatur-Fallback und bestehende Skripte erhalten. */
(function () {
  'use strict';
  var chev = '<svg class="nsel__chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>';
  var tick = '<svg class="nsel__tick" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>';
  var all = [];
  var uid = 0;

  function split(text) {
    var m = /^(.*) \((\d+)\)$/.exec(text);
    return m ? { label: m[1], n: m[2] } : { label: text, n: '' };
  }

  function build(sel) {
    var wrap = document.createElement('div');
    wrap.className = 'nsel';
    var id = 'nsel-' + (++uid);
    wrap.innerHTML = '<button type="button" class="nsel__btn" aria-haspopup="listbox" aria-expanded="false" aria-controls="' + id + '"><span class="nsel__val"></span>' + chev + '</button><ul class="nsel__list" role="listbox" id="' + id + '" tabindex="-1" hidden></ul>';
    sel.parentNode.insertBefore(wrap, sel.nextSibling);
    sel.classList.add('nsel__native');
    sel.tabIndex = -1;
    sel.setAttribute('aria-hidden', 'true');
    var btn = wrap.querySelector('.nsel__btn');
    var list = wrap.querySelector('.nsel__list');
    if (sel.getAttribute('aria-label')) btn.setAttribute('aria-label', sel.getAttribute('aria-label') + ': ' + (sel.options[sel.selectedIndex] || {}).text);
    var active = -1;

    function sync() {
      var o = sel.options[sel.selectedIndex];
      wrap.querySelector('.nsel__val').textContent = o ? split(o.text).label : '';
      if (sel.getAttribute('aria-label') && o) btn.setAttribute('aria-label', sel.getAttribute('aria-label') + ': ' + o.text);
    }
    function render() {
      list.innerHTML = '';
      Array.prototype.forEach.call(sel.options, function (o, i) {
        var p = split(o.text);
        var li = document.createElement('li');
        li.className = 'nsel__opt' + (o.selected ? ' is-on' : '') + (o.disabled || p.n === '0' ? ' is-off' : '');
        li.setAttribute('role', 'option');
        li.setAttribute('aria-selected', o.selected ? 'true' : 'false');
        li.id = list.id + '-' + i;
        li.innerHTML = tick + '<span class="nsel__lbl"></span>' + (p.n !== '' ? '<span class="nsel__n">' + p.n + '</span>' : '');
        li.querySelector('.nsel__lbl').textContent = p.label;
        li.addEventListener('click', function () { choose(i); });
        li.addEventListener('mousemove', function () { setActive(i); });
        list.appendChild(li);
      });
    }
    function setActive(i) {
      var items = list.children;
      if (active >= 0 && items[active]) items[active].classList.remove('is-active');
      active = Math.max(0, Math.min(items.length - 1, i));
      if (items[active]) {
        items[active].classList.add('is-active');
        list.setAttribute('aria-activedescendant', items[active].id);
        items[active].scrollIntoView({ block: 'nearest' });
      }
    }
    function open() {
      all.forEach(function (x) { if (x !== api) x.close(); });
      render();
      list.hidden = false;
      wrap.classList.add('is-open');
      btn.setAttribute('aria-expanded', 'true');
      setActive(sel.selectedIndex);
      list.focus({ preventScroll: true });
    }
    function close(focusBtn) {
      if (list.hidden) return;
      list.hidden = true;
      wrap.classList.remove('is-open');
      btn.setAttribute('aria-expanded', 'false');
      if (focusBtn) btn.focus({ preventScroll: true });
    }
    function choose(i) {
      if (sel.selectedIndex !== i) {
        sel.selectedIndex = i;
        sel.dispatchEvent(new Event('change', { bubbles: true }));
      }
      sync();
      close(true);
    }
    btn.addEventListener('click', function (e) { e.stopPropagation(); list.hidden ? open() : close(true); });
    btn.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowDown' || e.key === 'ArrowUp') { e.preventDefault(); open(); }
    });
    list.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowDown') { e.preventDefault(); setActive(active + 1); }
      else if (e.key === 'ArrowUp') { e.preventDefault(); setActive(active - 1); }
      else if (e.key === 'Home') { e.preventDefault(); setActive(0); }
      else if (e.key === 'End') { e.preventDefault(); setActive(list.children.length - 1); }
      else if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); choose(active); }
      else if (e.key === 'Escape' || e.key === 'Tab') { close(e.key === 'Escape'); }
    });
    sel.addEventListener('change', sync);
    if (sel.form) sel.form.addEventListener('reset', function () { setTimeout(sync); });
    // Ganze Feldfläche (z. B. in der Suchleiste) öffnet das Menü
    var field = sel.closest('.sbar__f');
    if (field) field.addEventListener('click', function (e) { if (!wrap.contains(e.target)) { e.stopPropagation(); open(); } });
    var api = { close: close, sync: sync };
    all.push(api);
    sync();
  }

  document.querySelectorAll('select[data-nice]').forEach(build);
  document.addEventListener('click', function (e) {
    if (!e.target.closest('.nsel')) all.forEach(function (x) { x.close(); });
  });
  window.niceSelectSync = function () { all.forEach(function (x) { x.sync(); }); };
})();
