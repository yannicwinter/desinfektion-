/* Kursfinder: geführter Assistent – fragt nach dem Zweck (und wann der letzte Kurs war),
   filtert nach Ort/Tag und zeigt die nächsten freien Termine mit direktem Link zur Anmeldung.
   Antworten erscheinen nacheinander mit „tippt …“ und Wort für Wort wie in einem Chat. */
(function () {
  'use strict';
  var box = document.getElementById('kursfinder');
  if (!box) return;
  var cfg = JSON.parse(box.getAttribute('data-kf-config'));
  var log = box.querySelector('[data-kf-log]');
  var form = box.querySelector('[data-kf-form]');
  var input = form.querySelector('input');
  var launch = document.querySelector('[data-kf-open]');
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var state = {};
  var cache = {};
  var queue = Promise.resolve();
  var gen = 0; // erhöht sich bei „Neu starten“ → alte Warteschlange verwerfen

  // ---------- Bausteine ----------
  function el(tag, cls, text) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text != null) n.textContent = text;
    return n;
  }
  function wait(ms) { return new Promise(function (r) { setTimeout(r, reduce ? 0 : ms); }); }
  function scroll() { log.scrollTop = log.scrollHeight; }
  function then(fn) {
    var g = gen;
    queue = queue.then(function () { if (g === gen) return fn(); });
    return queue;
  }

  // Bot-Nachricht: erst „tippt …“, dann Text Wort für Wort (bzw. Karten sanft eingeblendet)
  function say(content) {
    return then(function () {
      var dots = el('div', 'kf__msg kf__msg--bot kf__typing');
      dots.innerHTML = '<span></span><span></span><span></span>';
      log.appendChild(dots);
      scroll();
      var len = typeof content === 'string' ? content.length : 60;
      return wait(Math.min(1200, 450 + len * 6)).then(function () {
        dots.remove();
        var b = el('div', 'kf__msg kf__msg--bot');
        log.appendChild(b);
        if (typeof content !== 'string') {
          b.appendChild(content);
          b.classList.add('kf__msg--fade');
          scroll();
          return wait(250);
        }
        if (reduce) { b.textContent = content; scroll(); return; }
        var words = content.split(' ');
        var i = 0;
        return new Promise(function (done) {
          (function next() {
            if (i >= words.length) { done(); return; }
            b.textContent += (i ? ' ' : '') + words[i++];
            scroll();
            setTimeout(next, 28 + Math.random() * 40);
          })();
        });
      });
    });
  }
  function me(text) { log.appendChild(el('div', 'kf__msg kf__msg--me', text)); scroll(); }
  function clearChoices() { log.querySelectorAll('.kf__choices').forEach(function (c) { c.remove(); }); }
  function choices(list) {
    return then(function () {
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
          c.go();
        });
        wrap.appendChild(btn);
      });
      log.appendChild(wrap);
      scroll();
    });
  }
  function title(slug) { return cfg.titles[slug] || slug; }
  function load(slug) {
    if (cache[slug]) return Promise.resolve(cache[slug]);
    return fetch(cfg.api + '?kurs=' + encodeURIComponent(slug)).then(function (r) {
      if (!r.ok) throw new Error(r.status);
      return r.json();
    }).then(function (d) { cache[slug] = d; return d; });
  }

  // ---------- Gesprächsablauf ----------
  function start() {
    closeFrame();
    gen++;
    queue = Promise.resolve();
    log.innerHTML = '';
    state = {};
    say('Hallo! Ich helfe dir, den passenden Kurs und einen freien Termin zu finden.');
    ask();
  }

  function ask() {
    say('Wofür brauchst du den Kurs?');
    var list = [];
    if (cfg.titles['erste-hilfe-ausbildung']) list.push({ label: 'Führerschein', go: function () { pick('erste-hilfe-ausbildung', 'Für den Führerschein brauchst du die komplette Ausbildung.'); } });
    list.push({ label: 'Für den Job (Ersthelfer)', go: function () { askLast('job'); } });
    list.push({ label: 'Trainer, Verein, Studium', go: function () { askLast('verein'); } });
    if (cfg.titles['erste-hilfe-am-kind']) list.push({ label: 'Für Kinder', go: function () { pick('erste-hilfe-am-kind'); } });
    if (cfg.titles['erste-hilfe-am-hund']) list.push({ label: 'Für meinen Hund', go: askHund });
    if (cfg.titles['brandschutzhelfer']) list.push({ label: 'Brandschutzhelfer', go: function () { pick('brandschutzhelfer', 'Die Kosten trägt der Arbeitgeber.'); } });
    if (cfg.firmTopics.length) list.push({ label: 'Für unser Team / Betrieb', go: firm });
    list.push({ label: 'Etwas anderes', go: other });
    list.push({ label: 'Ich habe eine Frage', go: quick });
    choices(list);
  }

  // Ausbildung oder Fortbildung? Entscheidet, wie lange der letzte Kurs her ist.
  function askLast(ctx) {
    var bg = ctx === 'job' ? ' Bei der Anmeldung „Arbeitgeber / BG“ wählen – die Kosten übernimmt meist die Berufsgenossenschaft.' : '';
    say('Wann war dein letzter Erste-Hilfe-Kurs?');
    choices([
      { label: 'Noch nie / länger als 2 Jahre', go: function () { pick('erste-hilfe-ausbildung', 'Du brauchst die komplette Ausbildung.' + bg); } },
      { label: 'In den letzten 2 Jahren', go: function () { pick('erste-hilfe-fortbildung', 'Die Fortbildung reicht.' + bg); } }
    ]);
  }

  function askHund() {
    if (!cfg.titles['erste-hilfe-am-welpen']) { pick('erste-hilfe-am-hund'); return; }
    say('Erwachsener Hund oder Welpe?');
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
    say(frag);
    choices([
      { label: 'Anfrage stellen', href: cfg.contact, primary: true },
      { label: 'Anrufen', href: cfg.phoneLink },
      { label: 'Neu starten', go: start }
    ]);
  }

  function pick(slug, note, variant) {
    state.slug = slug;
    var loading = load(slug); // Termine schon laden, während „getippt“ wird
    say('Dann passt: ' + title(slug) + (variant ? ' – ' + variant : '') + '.' + (note ? ' ' + note : ''));
    then(function () {
      return loading.then(function (d) {
        // Kurs mit mehreren HiOrg-Listen (z. B. Ausbildung/Fortbildung): nur die passende zeigen
        if (variant) d = Object.assign({}, d, { dates: d.dates.filter(function (x) { return !x.variant || x.variant === variant; }) });
        state.data = d;
        if (state.text) applyOrt(state.text);
        if (!d.dates.length) { noDates(d); return; }
        askOrt();
      }).catch(function () {
        say('Die Termine konnten gerade nicht geladen werden.');
        choices([{ label: 'Zur Terminseite', href: cfg.api.replace(/api\/kursfinder$/, 'termine'), primary: true }, { label: 'Neu starten', go: start }]);
      });
    });
  }

  function askOrt() {
    if (state.ort !== undefined) { askDay(); return; }
    var orte = [];
    state.data.dates.forEach(function (x) { if (x.ort && orte.indexOf(x.ort) === -1) orte.push(x.ort); });
    if (orte.length < 2) { state.ort = ''; askDay(); return; }
    say('Wo passt es dir am besten?');
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
    say('Lieber unter der Woche oder am Wochenende?');
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
    var intro = hits.length === 1 ? 'Ich habe einen passenden Termin gefunden:' : 'Hier sind die nächsten freien Termine:';
    if (!hits.length) {
      hits = d.dates;
      intro = 'Dafür habe ich leider nichts gefunden. Das sind die nächsten freien Termine insgesamt:';
    }
    say(intro);
    var frag = el('div', 'kf__dates');
    hits.slice(0, 3).forEach(function (x) {
      var card = el('a', 'kf__date');
      card.href = x.book;
      if (cfg.inline && x.frame) card.addEventListener('click', function (e) { e.preventDefault(); openFrame(x, d.title); });
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
    say(frag);
    choices([
      { label: 'Alle Termine (' + d.dates.length + ')', href: d.all, primary: true },
      { label: 'Mehr zum Kurs', href: d.info },
      { label: 'Neu starten', go: start }
    ]);
  }

  // Anmeldung direkt im Kursfinder (HiOrg-Formular eingebettet); „Zurück“ führt zu den Terminen
  function openFrame(x, kurs) {
    closeFrame();
    var wrap = el('div', 'kf__frame');
    var bar = el('div', 'kf__framebar');
    var back = el('button', 'kf__back', '← Termine');
    back.type = 'button';
    back.addEventListener('click', closeFrame);
    var head = el('div', 'kf__frametitle');
    head.appendChild(el('strong', null, 'Anmeldung'));
    head.appendChild(el('span', null, kurs + (x.variant ? ' (' + x.variant + ')' : '') + ' · ' + x.label));
    var big = el('a', 'kf__big', 'Groß ↗');
    big.href = x.book;
    big.title = 'Anmeldung auf eigener Seite öffnen';
    bar.appendChild(back); bar.appendChild(head); bar.appendChild(big);
    var f = document.createElement('iframe');
    f.src = x.frame;
    f.title = 'Anmeldeformular ' + kurs;
    f.referrerPolicy = 'strict-origin-when-cross-origin';
    wrap.appendChild(bar);
    wrap.appendChild(el('p', 'kf__framehint', 'Die Anmeldung läuft über HiOrg, unser Kursverwaltungssystem.'));
    wrap.appendChild(f);
    box.appendChild(wrap);
    box.classList.add('kf--frame');
  }
  function closeFrame() {
    var f = box.querySelector('.kf__frame');
    if (f) f.remove();
    box.classList.remove('kf--frame');
  }

  // 2) Für Betriebe: Thema, Teilnehmerzahl, Ort → vorausgefüllte Anfrage
  function firm() {
    state.firm = {};
    say('Gern! Wir schulen Teams bei uns oder direkt im Betrieb. Worum geht es?');
    var list = cfg.firmTopics.map(function (t) { return { label: t.title, go: function () { state.firm.slug = t.slug; state.firm.title = t.title; firmCount(); } }; });
    list.push({ label: 'Fachkraft für Arbeitssicherheit', go: function () { state.firm.slug = 'arbeitssicherheit'; state.firm.title = 'Fachkraft für Arbeitssicherheit'; firmSend(); } });
    list.push({ label: 'Etwas anderes', go: function () { state.firm.slug = 'sonstiges'; state.firm.title = ''; firmCount(); } });
    choices(list);
  }
  function firmCount() {
    say('Wie viele Personen sollen ungefähr teilnehmen?');
    choices(['bis 10', '11–20', 'mehr als 20', 'Weiß ich noch nicht'].map(function (n) {
      return { label: n, go: function () { state.firm.n = n === 'Weiß ich noch nicht' ? '' : n; firmPlace(); } };
    }));
  }
  function firmPlace() {
    say('Wo soll die Schulung stattfinden?');
    choices([
      { label: 'Bei uns im Betrieb (Inhouse)', go: function () { state.firm.ort = 'bei uns im Betrieb (Inhouse)'; firmSend(); } },
      { label: 'Beim DRK', go: function () { state.firm.ort = 'beim DRK'; firmSend(); } },
      { label: 'Egal / Beratung gewünscht', go: function () { state.firm.ort = ''; firmSend(); } }
    ]);
  }
  function firmSend() {
    var f = state.firm;
    var msg = 'Anfrage über den Kursfinder' + (f.title ? ': ' + f.title : '') + (f.n ? ', ca. ' + f.n + ' Teilnehmende' : '') + (f.ort ? ', Schulung ' + f.ort : '') + '.';
    var url = cfg.firma + '?thema=' + encodeURIComponent(f.slug) + (f.n ? '&teilnehmer=' + encodeURIComponent(f.n) : '') + '&nachricht=' + encodeURIComponent(msg) + '#formular';
    say('Alles klar. Ich habe die Anfrage schon vorbereitet – bitte nur noch Name und E-Mail ergänzen. Wir melden uns schnellstmöglich mit Termin und Angebot.' + (f.slug === 'erste-hilfe-im-betrieb' ? ' Tipp: Die Kosten für Ersthelfer übernimmt meist die Berufsgenossenschaft.' : ''));
    choices([
      { label: 'Anfrage abschicken', href: url, primary: true },
      { label: 'Lieber anrufen', href: cfg.phoneLink },
      { label: 'Neu starten', go: start }
    ]);
  }

  // 4) Häufige Fragen als Schnellauswahl
  function quick() {
    say('Was möchtest du wissen? Du kannst deine Frage auch unten eintippen.');
    var list = cfg.quick.map(function (q) { return { label: q, go: function () { freeText(q, true); } }; });
    list.push({ label: 'Zurück zur Kurssuche', go: ask });
    choices(list);
  }

  function noDates(d) {
    say('Für ' + d.title + ' gibt es gerade keine freien Termine. Neue kommen laufend dazu – für Gruppen und Betriebe finden wir auch einen eigenen Termin.');
    choices([
      { label: 'Anfrage stellen', href: d.inhouse, primary: true },
      { label: 'Anrufen', href: cfg.phoneLink },
      { label: 'Neu starten', go: start }
    ]);
  }

  // ---------- Freitext ----------
  var days = { montag: 1, dienstag: 2, mittwoch: 3, donnerstag: 4, freitag: 5, samstag: 6, sonntag: 7 };
  function has(t, list) { return list.some(function (k) { return t.indexOf(k) !== -1; }); }
  function applyOrt(t) {
    if (!state.data) return;
    state.data.dates.forEach(function (x) { if (x.ort && t.indexOf(x.ort.toLowerCase()) !== -1) state.ort = x.ort; });
  }

  // Ort und Wochentag/Wochenende aus dem Text merken (z. B. „nächster Kurs in Verden am Samstag“)
  function detect(t) {
    if (/wochenend/.test(t)) state.day = 'we';
    else if (/unter der woche|werktag|wochentag/.test(t)) state.day = 'wk';
    Object.keys(days).forEach(function (n) { if (t.indexOf(n) !== -1) state.day = days[n]; });
    if (state.data) applyOrt(t);
    (cfg.orte || []).forEach(function (o) { if (t.indexOf(o.toLowerCase()) !== -1) state.ort = o; });
  }

  function freeText(text, shown) {
    if (!shown) me(text);
    clearChoices();
    var t = ' ' + text.toLowerCase() + ' ';
    state.text = t;
    detect(t);

    // Wissenssuche auf dem Server: durchsucht Kurse und FAQ der Website
    var asked = fetch(cfg.api + '?frage=' + encodeURIComponent(text)).then(function (r) { return r.json(); });
    then(function () {
      return asked.then(answer).catch(function () {
        say('Da ist gerade etwas schiefgelaufen. Wähl bitte einfach aus:');
        ask();
      });
    });
  }

  function answer(a) {
    // Mit dem korrigierten Text (Tippfehler bereinigt) Ort und Tag erneut erkennen
    if (a.fixed) {
      state.text = ' ' + a.fixed + ' ';
      detect(state.text);
    }
    var t = state.text;
    if (a.faq) say(a.faq.a);
    if (a.type === 'none') {
      // Terminfrage ohne Kursart („Wann ist der nächste Kurs in Verden?“) → nachfragen, wofür
      if (/n(ä|ae)chst|termin|kurs|wann|frei|platz|anmeld|buch/.test(t) || state.ort || state.day !== undefined) {
        var wo = state.ort ? ' in ' + state.ort : '';
        var tag = state.day === 'we' ? ' am Wochenende' : state.day === 'wk' ? ' unter der Woche' : (typeof state.day === 'number' ? ' am ' + ['', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag'][state.day] : '');
        say('Gern, ich suche dir den nächsten freien Termin' + wo + tag + '. Dafür muss ich kurz wissen, welcher Kurs der richtige ist.');
        ask();
        return;
      }
      say('Das habe ich leider nicht verstanden. Wähl einfach aus, wofür du den Kurs brauchst:');
      ask();
      return;
    }
    if (a.type === 'faq') {
      choices([
        { label: 'Passenden Kurs finden', go: ask, primary: true },
        { label: 'Weitere Frage', go: quick },
        { label: 'Alle Fragen', href: cfg.faq },
        { label: 'Neu starten', go: start }
      ]);
      return;
    }
    if (a.fact) say(a.title + ' – ' + a.fact + '.');
    if (!a.bookable) {
      say(a.title + ': ' + a.teaser + ' Den Termin stimmen wir individuell mit dir ab.');
      choices([
        { label: 'Anfrage stellen', href: a.inquiry, primary: true },
        { label: 'Mehr zum Kurs', href: a.url },
        { label: 'Neu starten', go: start }
      ]);
      return;
    }
    var fuehrerschein = /führerschein|fuehrerschein|fahrschule|fahrerlaubnis/.test(t);
    var zweck = has(t, cfg.keywords._betrieb);
    // Ausbildung für Job/Trainer/Verein: erst klären, wie lange der letzte Kurs her ist
    if (a.slug === 'erste-hilfe-ausbildung' && zweck && !fuehrerschein && !/fortbild|auffrisch/.test(t)) {
      askLast(/betrieb|firma|arbeit|job|chef|ersthelfer|bg|berufsgenossen|unternehmen/.test(t) ? 'job' : 'verein');
      return;
    }
    pick(a.slug, fuehrerschein && a.slug === 'erste-hilfe-ausbildung' ? 'Für den Führerschein brauchst du die komplette Ausbildung.' : '');
  }

  // ---------- Öffnen / Schließen ----------
  function open() {
    box.hidden = false;
    requestAnimationFrame(function () { box.classList.add('is-open'); });
    document.body.classList.add('kf-open');
    if (launch) launch.setAttribute('aria-expanded', 'true');
    if (!log.childElementCount) start();
    setTimeout(function () { input.focus({ preventScroll: true }); }, 60);
  }
  function close() {
    closeFrame();
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
  if (location.hash === '#kursfinder') open();
})();
