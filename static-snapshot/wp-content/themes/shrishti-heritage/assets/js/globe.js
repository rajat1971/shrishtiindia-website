/* Shrishti hero globe: dotted wireframe earth in the emerald palette. */
(function () {
  var el = document.querySelector('.sh-hero .globe');
  if (!el || !window.d3 || !d3.geoOrthographic) return;
  if (getComputedStyle(el).display === 'none') return; // hidden on phones

  var C = { sphere: '#0B3129', rim: '#3F6B61', grid: 'rgba(159,179,171,0.18)', land: 'rgba(207,217,211,0.35)', dot: 'rgba(199,162,99,0.85)', mumbai: '#C7A263', label: '#F4EFE4' };
  var MUMBAI = [72.88, 19.07];
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  var canvas = document.createElement('canvas');
  canvas.setAttribute('aria-hidden', 'true');
  el.appendChild(canvas);
  el.classList.add('globe-live');
  var ctx = canvas.getContext('2d');
  var size, radius, dpr = window.devicePixelRatio || 1;
  var projection = d3.geoOrthographic().clipAngle(90);
  var path = d3.geoPath(projection, ctx);
  var graticule = d3.geoGraticule10();
  var land = null, dots = [];
  var rotation = [-MUMBAI[0] + 40, -15, 0];

  function resize() {
    size = Math.round(el.clientWidth);
    radius = size / 2 - 4;
    canvas.width = size * dpr; canvas.height = size * dpr;
    canvas.style.width = size + 'px'; canvas.style.height = size + 'px';
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    projection.scale(radius).translate([size / 2, size / 2]);
  }

  function inRing(pt, ring) {
    var x = pt[0], y = pt[1], inside = false;
    for (var i = 0, j = ring.length - 1; i < ring.length; j = i++) {
      var xi = ring[i][0], yi = ring[i][1], xj = ring[j][0], yj = ring[j][1];
      if ((yi > y) !== (yj > y) && x < ((xj - xi) * (y - yi)) / (yj - yi) + xi) inside = !inside;
    }
    return inside;
  }
  function inPoly(pt, poly) { if (!inRing(pt, poly[0])) return false; for (var i = 1; i < poly.length; i++) if (inRing(pt, poly[i])) return false; return true; }
  function inFeature(pt, f) {
    var g = f.geometry;
    if (g.type === 'Polygon') return inPoly(pt, g.coordinates);
    if (g.type === 'MultiPolygon') { for (var k = 0; k < g.coordinates.length; k++) if (inPoly(pt, g.coordinates[k])) return true; }
    return false;
  }
  function buildDots() {
    var step = 1.6;
    land.features.forEach(function (f) {
      var b = d3.geoBounds(f);
      for (var lng = b[0][0]; lng <= b[1][0]; lng += step)
        for (var lat = b[0][1]; lat <= b[1][1]; lat += step)
          if (inFeature([lng, lat], f)) dots.push([lng, lat]);
    });
  }

  function render() {
    projection.rotate(rotation);
    ctx.clearRect(0, 0, size, size);
    ctx.beginPath(); ctx.arc(size / 2, size / 2, radius, 0, 2 * Math.PI);
    ctx.fillStyle = C.sphere; ctx.fill();
    ctx.lineWidth = 1; ctx.strokeStyle = C.rim; ctx.stroke();
    ctx.beginPath(); path(graticule); ctx.strokeStyle = C.grid; ctx.lineWidth = 0.6; ctx.stroke();
    if (!land) return;
    ctx.beginPath(); path(land); ctx.strokeStyle = C.land; ctx.lineWidth = 0.7; ctx.stroke();
    var center = [-rotation[0], -rotation[1]];
    ctx.fillStyle = C.dot;
    for (var i = 0; i < dots.length; i++) {
      if (d3.geoDistance(dots[i], center) > 1.57) continue;
      var p = projection(dots[i]);
      ctx.beginPath(); ctx.arc(p[0], p[1], 1.05, 0, 6.2832); ctx.fill();
    }
    if (d3.geoDistance(MUMBAI, center) < 1.5) {
      var m = projection(MUMBAI);
      ctx.beginPath(); ctx.arc(m[0], m[1], 9, 0, 6.2832); ctx.strokeStyle = 'rgba(199,162,99,0.45)'; ctx.lineWidth = 1; ctx.stroke();
      ctx.beginPath(); ctx.arc(m[0], m[1], 4.5, 0, 6.2832); ctx.fillStyle = C.mumbai; ctx.fill();
      ctx.font = '600 11px Figtree, sans-serif'; ctx.fillStyle = C.label; ctx.letterSpacing = '2px';
      ctx.fillText('MUMBAI', m[0] + 14, m[1] + 4);
    }
  }

  // Rotation: slow auto-spin, drag to turn. No scroll-zoom, so page scrolling stays normal.
  var running = !reduce, visible = true, dragging = false, last = 0;
  function tick(t) {
    if (running && visible && !dragging && land) { rotation[0] += (t - (last || t)) * 0.006; render(); }
    last = t; requestAnimationFrame(tick);
  }
  canvas.addEventListener('pointerdown', function (e) {
    dragging = true; canvas.setPointerCapture(e.pointerId);
    var sx = e.clientX, sy = e.clientY, r0 = rotation.slice();
    function move(ev) { rotation[0] = r0[0] + (ev.clientX - sx) * 0.4; rotation[1] = Math.max(-60, Math.min(60, r0[1] - (ev.clientY - sy) * 0.4)); render(); }
    function up() { dragging = false; canvas.removeEventListener('pointermove', move); canvas.removeEventListener('pointerup', up); }
    canvas.addEventListener('pointermove', move); canvas.addEventListener('pointerup', up);
  });
  if ('IntersectionObserver' in window) new IntersectionObserver(function (en) { visible = en[0].isIntersecting; }).observe(el);
  window.addEventListener('resize', function () { resize(); render(); });

  resize(); render();
  fetch(el.getAttribute('data-land') || window.shrishtiGlobe.land).then(function (r) { return r.json(); }).then(function (json) {
    land = json; buildDots(); render(); requestAnimationFrame(tick);
  }).catch(function () { el.classList.remove('globe-live'); canvas.remove(); });
})();