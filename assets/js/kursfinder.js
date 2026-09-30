/* Kursfinder: geführter Assistent – fragt nach dem Zweck, filtert nach Ort/Tag
   und zeigt die nächsten freien Termine mit direktem Link zur Anmeldung. */
(function () {
  'use strict';
  var box = document.getElementById('kursfinder');
  if (!box) return;
  var cfg = JSON.parse(box.getAttribute('data-kf-config'));
  var log = box.querySelector('[data-kf-log]');
  var form = box.querySelector('[data-kf-form]');
  var input = form.querySelector('input');
  var launch = document.querySelector('[data-kf-open]');
  var state = {};
  var cache = {};

  // ---------- Hilfsfunktionen ----------
  function el(tag, cls, text) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text != null) n.textContent = text;
    return n;
  }
  function scroll() { log.scrollTop = log.scrollHeight; }
  function bot(text) {
    var b = el('div', 'kf__msg kf__msg--bot');
    if (typeof text === 'string') b.textContent = text; else b.appendChild(text);
    log.appendChild(b);
    scroll();
    return b;
  }
  function me(text) { log.appendChild(el('div', 'kf__msg kf__msg--me', text)); scroll(); }
  function clearChoices() { log.querySelectorAll('.kf__choices').forEach(function (c) { c.remove(); }); }
  function choices(list) {
    clearChoices();
    var wrap = el('div', 'kf__choices');
    list.forEach(function (c) {
      var btn = el(c.href ? 'a' : 'button', 'kf__chip' + (c.primary ? ' kf__chip--primary' : ''), c.label);
      if (c.href) { btn.href = c.href; } else { btn.type = 'button'; }
      btn.addEventListener('click', function (e) {
        if (c.href) return;
        e.preventDefault();
        me(c.label);
        clearChoices();
        setTimeout(c.go, 250);
      });
      wrap.appendChild(btn);
    });
    log.appendChild(wrap);
    scroll();
  }
  function title(slug) { return cfg.titles[slug] || slug; }
  function load(slug) {
    if (cache[slug]) return Promise.resolve(cache[slug]);
    return fetch(cfg.api + '?kurs=' + encodeURIComponent(slug)).then(function (r) {
      if (!r.ok) throw new Error(r.status);
      return r.json();
    }).then(function (d) { cache[slug] = d; return d; });
  }
  function typing() {
    var t = el('div', 'kf__msg kf__msg--bot kf__typing');
    t.innerHTML = '<span></span><span></span><span></span>';
    log.appendChild(t);
    scroll();
    return t;
  }

  // ---------- Gesprächsablauf ----------
  function start() {
    log.innerHTML = '';
    state = {};
    bot('Hallo! Ich helfe dir, den passenden Kurs und einen freien Termin zu finden.');
    ask();
  }

  function ask() {
    bot('Wofür brauchst du den Kurs?');
    var list = [];
    if (cfg.titles['erste-hilfe-ausbildung']) list.push({ label: 'Führerschein', go: function () { pick('erste-hilfe-ausbildung'); } });
    list.push({ label: 'Für den Job (Ersthelfer)', go: askBetrieb });
    if (cfg.titles['erste-hilfe-ausbildung']) list.push({ label: 'Trainer, Verein, Studium', go: function () { pick('erste-hilfe-ausbildung'); } });
    if (cfg.titles['erste-hilfe-am-kind']) list.push({ label: 'Für Kinder', go: function () { pick('erste-hilfe-am-kind'); } });
    if (cfg.titles['erste-hilfe-am-hund']) list.push({ label: 'Für meinen Hund', go: askHund });
    if (cfg.titles['brandschutzhelfer']) list.push({ label: 'Brandschutzhelfer', go: function () { pick('brandschutzhelfer', 'Die Kosten trägt der Arbeitgeber.'); } });
    list.push({ label: 'Etwas anderes', go: other });
    choices(list);
  }

  function askBetrieb() {
    bot('Warst du in den letzten 2 Jahren schon in einer Erste-Hilfe-Ausbildung?');
    choices([
      { label: 'Ja – ich muss auffrischen', go: function () { pick('erste-hilfe-fortbildung', 'Die Kosten übernimmt meist die Berufsgenossenschaft – bitte vorher klären.'); } },
      { label: 'Nein / länger her', go: function () { pick('erste-hilfe-ausbildung', 'Die Kosten übernimmt meist die Berufsgenossenschaft – bitte vorher klären.'); } }
    ]);
  }

  function askHund() {
    if (!cfg.titles['erste-hilfe-am-welpen']) { pick('erste-hilfe-am-hund'); return; }
    bot('Erwachsener Hund oder Welpe?');
    choices([
      { label: 'Erwachsener Hund', go: function () { pick('erste-hilfe-am-hund'); } },
      { label: 'Welpe', go: function () { pick('erste-hilfe-am-welpen', 'Dein Welpe darf mitkommen.'); } }
    ]);
  }

  function other() {
    var frag = el('div');
    frag.appendChild(el('p', null, 'Diese Angebote planen wir individuell mit dir:'));
    var ul = el('ul', 'kf__links');
    cfg.requests.forEach(function (r) {
      var li = el('li');
      var a = el('a', null, r.title);
      a.href = r.url;
      li.appendChild(a);
      ul.appendChild(li);
    });
    frag.appendChild(ul);
    bot(frag);
    choices([
      { label: 'Anfrage stellen', href: cfg.contact, primary: true },
      { label: 'Anrufen', href: cfg.phoneLink },
      { label: 'Neu starten', go: start }
    ]);
  }

  function pick(slug, note) {
    state.slug = slug;
    bot('Dann passt: ' + title(slug) + '.' + (note ? ' ' + note : ''));
    var t = typing();
    load(slug).then(function (d) {
      t.remove();
      state.data = d;
      if (!d.dates.length) { noDates(d); return; }
      if (state.ort !== undefined && state.day !== undefined) { results(); return; }
      askOrt();
    }).catch(function () {
      t.remove();
      bot('Die Termine konnten gerade nicht geladen werden.');
      choices([{ label: 'Zur Terminseite', href: cfg.api.replace(/api\/kursfinder$/, 'termine'), primary: true }, { label: 'Neu starten', go: start }]);
    });
  }

  function askOrt() {
    if (state.ort !== undefined) { askDay(); return; }
    var orte = [];
    state.data.dates.forEach(function (x) { if (x.ort && orte.indexOf(x.ort) === -1) orte.push(x.ort); });
    if (orte.length < 2) { state.ort = ''; askDay(); return; }
    bot('Wo passt es dir am besten?');
    var list = orte.sort().map(function (o) { return { label: o, go: function () { state.ort = o; askDay(); } }; });
    list.push({ label: 'Egal', go: function () { state.ort = ''; askDay(); } });
    choices(list);
  }

  function askDay() {
    if (state.day !== undefined) { results(); return; }
    var pool = filtered(state.ort, '');
    var we = pool.some(function (x) { return x.wd >= 6; });
    var wk = pool.some(function (x) { return x.wd < 6; });
    if (!(we && wk)) { state.day = ''; results(); return; }
    bot('Lieber unter der Woche oder am Wochenende?');
    choices([
      { label: 'Unter der Woche', go: function () { state.day = 'wk'; results(); } },
      { label: 'Wochenende', go: function () { state.day = 'we'; results(); } },
      { label: 'Egal', go: function () { state.day = ''; results(); } }
    ]);
  }

  function filtered(ort, day) {
    return state.data.dates.filter(function (x) {
      if (ort && x.ort !== ort) return false;
      if (day === 'we' && x.wd < 6) return false;
      if (day === 'wk' && x.wd >= 6) return false;
      if (typeof day === 'number' && x.wd !== day) return false;
      return true;
    });
  }

  function results() {
    var d = state.data;
    var hits = filtered(state.ort, state.day);
    var intro = 'Hier sind die nächsten freien Termine:';
    if (!hits.length) {
      hits = d.dates;
      intro = 'Dafür habe ich leider nichts gefunden – das sind die nächsten freien Termine insgesamt:';
    }
    var frag = el('div');
    frag.appendChild(el('p', null, intro));
    hits.slice(0, 3).forEach(function (x) {
      var card = el('a', 'kf__date');
      card.href = x.book;
      var cal = el('span', 'kf__cal');
      cal.appendChild(el('small', null, x.month));
      cal.appendChild(el('strong', null, x.day));
      var info = el('span', 'kf__info');
      info.appendChild(el('strong', null, x.label + (x.time ? ' · ' + x.time + ' Uhr' : '')));
      info.appendChild(el('span', null, (x.ort || x.place) + (x.price ? ' · ' + x.price : '')));
      if (x.free) info.appendChild(el('span', 'kf__free' + (x.few ? ' kf__free--few' : ''), x.free));
      card.appendChild(cal);
      card.appendChild(info);
      card.appendChild(el('span', 'kf__book', 'Buchen'));
      frag.appendChild(card);
    });
    bot(frag);
    choices([
      { label: 'Alle Termine (' + d.dates.length + ')', href: d.all, primary: true },
      { label: 'Mehr zum Kurs', href: d.info },
      { label: 'Neu starten', go: start }
    ]);
  }

  function noDates(d) {
    bot('Für ' + d.title + ' gibt es gerade keine freien Termine. Neue kommen laufend dazu – für Gruppen und Betriebe finden wir auch einen eigenen Termin.');
    choices([
      { label: 'Anfrage stellen', href: d.inhouse, primary: true },
      { label: 'Anrufen', href: cfg.phoneLink },
      { label: 'Neu starten', go: start }
    ]);
  }

  // ---------- Freitext ----------
  var days = { montag: 1, dienstag: 2, mittwoch: 3, donnerstag: 4, freitag: 5, samstag: 6, sonntag: 7 };
  function understand(text) {
    var t = ' ' + text.toLowerCase() + ' ';
    var slug = null;
    var betrieb = cfg.keywords._betrieb.some(function (k) { return t.indexOf(k) !== -1; });
    Object.keys(cfg.keywords).some(function (s) {
      if (s === '_betrieb') return false;
      var hit = cfg.keywords[s].some(function (k) { return t.indexOf(k) !== -1; });
      if (hit) slug = s;
      return hit;
    });
    var day;
    if (/wochenend/.test(t)) day = 'we';
    else if (/unter der woche|werktag|wochentag/.test(t)) day = 'wk';
    Object.keys(days).forEach(function (n) { if (t.indexOf(n) !== -1) day = days[n]; });
    return { slug: slug, betrieb: betrieb, day: day, text: t };
  }

  function freeText(text) {
    me(text);
    clearChoices();
    var u = understand(text);
    // Ort erst nach dem Laden bekannt → nach Kurswahl prüfen
    var applyOrt = function () {
      if (!state.data) return;
      state.data.dates.forEach(function (x) { if (x.ort && u.text.indexOf(x.ort.toLowerCase()) !== -1) state.ort = x.ort; });
    };
    if (u.day !== undefined) state.day = u.day;
    var slug = u.slug;
    if (u.betrieb && (!slug || slug === 'erste-hilfe-ausbildung') && !/führerschein|fuehrerschein/.test(u.text)) {
      if (slug === 'erste-hilfe-ausbildung' && /ausbildung/.test(u.text)) slug = 'erste-hilfe-ausbildung';
      else { setTimeout(askBetrieb, 250); return; }
    }
    if (!slug && state.slug) slug = state.slug;
    if (!slug) {
      setTimeout(function () { bot('Das habe ich noch nicht ganz verstanden.'); ask(); }, 250);
      return;
    }
    state.slug = slug;
    var t = typing();
    load(slug).then(function (d) {
      t.remove();
      state.data = d;
      applyOrt();
      bot('Dann passt: ' + d.title + '.');
      if (!d.dates.length) { noDates(d); return; }
      if (state.ort === undefined) askOrt(); else askDay();
    }).catch(function () { t.remove(); bot('Die Termine konnten gerade nicht geladen werden.'); });
  }

  // ---------- Öffnen / Schließen ----------
  function open() {
    box.hidden = false;
    requestAnimationFrame(function () { box.classList.add('is-open'); });
    document.body.classList.add('kf-open');
    if (launch) launch.setAttribute('aria-expanded', 'true');
    if (!log.childElementCount) start();
    setTimeout(function () {
      var first = log.querySelector('.kf__chip');
      (first || input).focus({ preventScroll: true });
    }, 50);
  }
  function close() {
    box.classList.remove('is-open');
    document.body.classList.remove('kf-open');
    if (launch) { launch.setAttribute('aria-expanded', 'false'); launch.focus(); }
    setTimeout(function () { box.hidden = true; }, 220);
  }
  document.querySelectorAll('[data-kf-open]').forEach(function (b) {
    b.addEventListener('click', function (e) { e.preventDefault(); open(); });
  });
  box.querySelector('[data-kf-close]').addEventListener('click', close);
  box.querySelector('[data-kf-restart]').addEventListener('click', start);
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !box.hidden) close(); });
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var v = input.value.trim();
    if (!v) return;
    input.value = '';
    freeText(v);
  });
  // Direktlink: …#kursfinder öffnet den Assistenten
  if (location.hash === '#kursfinder') open();
})();
