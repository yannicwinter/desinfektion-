/* Terminsuche Startseite: zählt freie Termine live (Kurs · Ort · Wann) und steuert das Such-Menü am Handy. */
(function () {
  'use strict';
  var root = document.querySelector('[data-search]');
  if (!root) return;
  var d = JSON.parse(root.getAttribute('data-search'));
  var form = root.querySelector('[data-search-form]');
  var sheet = root.querySelector('.ssheet');
  var state = { kurs: d.kurse.length ? d.kurse[0].slug : '', ort: '', wann: '' };
  var touched = false;

  function count(k, o, w) {
    return d.termine.filter(function (t) {
      return t.k === k && (!o || t.o === o) && (!w || (w === 'we' ? t.w >= 6 : t.w < 6));
    }).length;
  }
  function label(n) {
    if (!n) return 'Zum Kurs – aktuell keine freien Termine';
    return n === 1 ? '1 freien Termin anzeigen' : n + ' freie Termine anzeigen';
  }
  function title(slug) {
    for (var i = 0; i < d.kurse.length; i++) if (d.kurse[i].slug === slug) return d.kurse[i].title;
    return '';
  }

  function render() {
    var n = count(state.kurs, state.ort, state.wann);
    root.querySelectorAll('[data-count-label]').forEach(function (el) { el.textContent = label(n); });
    // Desktop: Ort-Auswahl zeigt Anzahl je Ort
    var ortSel = form.querySelector('[data-f=ort]');
    Array.prototype.forEach.call(ortSel.options, function (opt) {
      if (!opt.value) { opt.textContent = 'Alle Orte'; return; }
      opt.textContent = opt.value + ' (' + count(state.kurs, opt.value, state.wann) + ')';
    });
    // Handy: Kurs-Kacheln und Chips
    root.querySelectorAll('[data-chips=kurs] .kchip').forEach(function (b) {
      var c = count(b.getAttribute('data-v'), state.ort, state.wann);
      var el = b.querySelector('[data-n]');
      el.textContent = c ? (c === 1 ? '1 freier Termin' : c + ' freie Termine') : 'zurzeit keine Termine';
      el.classList.toggle('is-zero', !c);
    });
    root.querySelectorAll('[data-chips=ort] .schip').forEach(function (b) {
      var v = b.getAttribute('data-v');
      b.disabled = !!v && !count(state.kurs, v, state.wann);
    });
    ['kurs', 'ort', 'wann'].forEach(function (k) {
      root.querySelectorAll('[data-chips=' + k + '] button').forEach(function (b) {
        var on = b.getAttribute('data-v') === state[k];
        b.classList.toggle('is-on', on);
        b.setAttribute('aria-pressed', on ? 'true' : 'false');
      });
      var sel = form.querySelector('[data-f=' + k + ']');
      if (sel && sel.value !== state[k]) sel.value = state[k];
    });
    var sub = root.querySelector('[data-spill-sub]');
    if (sub && touched) {
      sub.textContent = title(state.kurs) + ' · ' + (state.ort || 'alle Orte') + (state.wann ? ' · ' + (state.wann === 'we' ? 'Wochenende' : 'unter der Woche') : '');
    }
  }

  function go() {
    var q = [];
    if (state.ort) q.push('ort=' + encodeURIComponent(state.ort));
    if (state.wann) q.push('wann=' + state.wann);
    location.href = d.base + '/' + encodeURIComponent(state.kurs) + (q.length ? '?' + q.join('&') : '');
  }

  form.addEventListener('change', function (e) {
    var k = e.target.getAttribute('data-f');
    if (!k) return;
    state[k] = e.target.value;
    // Ort ohne Termine beim neuen Kurs zurücksetzen
    if (k === 'kurs' && state.ort && !count(state.kurs, state.ort, state.wann)) state.ort = '';
    touched = true;
    render();
  });
  form.addEventListener('submit', function (e) { e.preventDefault(); go(); });

  root.querySelectorAll('[data-chips]').forEach(function (group) {
    var k = group.getAttribute('data-chips');
    group.addEventListener('click', function (e) {
      var b = e.target.closest('button');
      if (!b || b.disabled) return;
      state[k] = b.getAttribute('data-v');
      if (k === 'kurs' && state.ort && !count(state.kurs, state.ort, state.wann)) state.ort = '';
      touched = true;
      render();
    });
  });

  // Handy: Such-Menü öffnen/schließen
  var opener = root.querySelector('[data-sheet-open]');
  function openSheet() {
    sheet.hidden = false;
    requestAnimationFrame(function () { sheet.classList.add('is-open'); });
    document.body.classList.add('sheet-open');
    var first = sheet.querySelector('.kchip.is-on') || sheet.querySelector('.kchip');
    if (first) first.focus({ preventScroll: true });
  }
  function closeSheet() {
    sheet.classList.remove('is-open');
    document.body.classList.remove('sheet-open');
    setTimeout(function () { sheet.hidden = true; }, 260);
    opener.focus({ preventScroll: true });
  }
  opener.addEventListener('click', openSheet);
  sheet.querySelectorAll('[data-sheet-close]').forEach(function (b) { b.addEventListener('click', closeSheet); });
  sheet.querySelector('[data-sheet-go]').addEventListener('click', go);
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !sheet.hidden) closeSheet(); });

  render();
})();
