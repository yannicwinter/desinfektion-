(function () {
  'use strict';
  var root = document.querySelector('[data-search]');
  if (!root) return;
  var d = JSON.parse(root.getAttribute('data-search'));
  var form = root.querySelector('[data-search-form]');
  var state = { kurs: '', ort: '', wann: '' };

  function count(k, o, w) {
    return d.termine.filter(function (t) {
      return (!k || t.k === k) && (!o || t.o === o) && (!w || (w === 'we' ? t.w >= 6 : t.w < 6));
    }).length;
  }
  function label(n) {
    if (!n) return 'Zum Kurs';
    return n === 1 ? '1 Termin finden' : n + ' Termine finden';
  }

  function render() {
    var n = count(state.kurs, state.ort, state.wann);
    root.querySelectorAll('[data-count-label]').forEach(function (el) { el.textContent = label(n); });
    var kursSel = form.querySelector('[data-f=kurs]');
    Array.prototype.forEach.call(kursSel.options, function (opt) {
      if (!opt.dataset.label) opt.dataset.label = opt.textContent;
      opt.textContent = opt.dataset.label + ' (' + count(opt.value, state.ort, state.wann) + ')';
    });
    var ortSel = form.querySelector('[data-f=ort]');
    Array.prototype.forEach.call(ortSel.options, function (opt) {
      if (!opt.dataset.label) opt.dataset.label = opt.textContent;
      opt.textContent = opt.dataset.label + ' (' + count(state.kurs, opt.value, state.wann) + ')';
    });
    ['kurs', 'ort', 'wann'].forEach(function (k) {
      var sel = form.querySelector('[data-f=' + k + ']');
      if (sel && sel.value !== state[k]) sel.value = state[k];
    });
    if (window.niceSelectSync) window.niceSelectSync();
  }

  function go() {
    var q = [];
    if (state.ort) q.push('ort=' + encodeURIComponent(state.ort));
    if (state.wann) q.push('wann=' + state.wann);
    if (!state.kurs && d.cat) q.push('bereich=' + encodeURIComponent(d.cat));
    location.href = d.base + (state.kurs ? '/' + encodeURIComponent(state.kurs) : '') + (q.length ? '?' + q.join('&') : '');
  }

  form.addEventListener('change', function (e) {
    var k = e.target.getAttribute('data-f');
    if (!k) return;
    state[k] = e.target.value;
    if (k === 'kurs' && state.ort && !count(state.kurs, state.ort, state.wann)) state.ort = '';
    render();
  });
  form.addEventListener('submit', function (e) { e.preventDefault(); go(); });
  render();
})();
