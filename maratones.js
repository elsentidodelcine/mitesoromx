let DATA = { maratones: [] };
let maratonActivo = null;
let filtroSub = 'todos';
let filtroInt = 'todos';
let filtroDec = 'todos';
let soloResena = false;

function escapeHTML(s) {
  return String(s == null ? '' : s)
    .replace(/&/g, '&amp;').replace(/</g, '&lt;')
    .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function getMaraton(id) {
  return DATA.maratones.find(m => m.id === id);
}

function mapPelisById(m) {
  const map = {};
  (m.peliculas || []).forEach(p => { map[p.id] = p; });
  return map;
}

function decadeOf(anio) {
  const y = Number(anio);
  if (!y) return null;
  return Math.floor(y / 10) * 10;
}

function mostrarSkeleton(n) {
  const grid = document.getElementById('maraton-grid');
  if (!grid) return;
  grid.innerHTML = Array.from({ length: n }, () =>
    '<div class="maraton-skel" aria-hidden="true"></div>'
  ).join('');
}

function renderCountdown(m) {
  const el = document.getElementById('maraton-countdown');
  if (!el || !m.fechaObjetivo) { if (el) el.hidden = true; return; }
  const target = new Date(m.fechaObjetivo + 'T23:59:59');
  const now = new Date();
  const diff = target - now;
  if (diff < 0) {
    el.textContent = '🎃 ¡Ya es temporada de Halloween!';
    el.hidden = false;
    return;
  }
  const dias = Math.ceil(diff / 86400000);
  el.textContent = dias === 0
    ? '🎃 ¡Es Halloween!'
    : `🎃 Faltan ${dias} día${dias === 1 ? '' : 's'} para Halloween`;
  el.hidden = false;
}

function cardHTML(p, num, compact) {
  const tiene = p.resena && String(p.resena).trim();
  const tag = tiene ? 'a' : 'article';
  const href = tiene ? ` href="${escapeHTML(p.resena)}"` : '';
  const disabled = tiene ? '' : ' is-disabled';
  const cta = tiene ? 'Ver reseña →' : 'Sin reseña aún';
  const poster = p.poster
    ? `<img src="${escapeHTML(p.poster)}" alt="" loading="lazy" onerror="this.style.opacity='0.25'">`
    : '';
  const int = p.intensidad
    ? `<span class="badge-int ${escapeHTML(p.intensidad)}">${escapeHTML(p.intensidad)}</span>`
    : '';
  const cine = p.enCartelera ? '<span class="badge-cine">En cines</span>' : '';
  const dur = p.duracion ? `<span class="maraton-duracion">${p.duracion} min</span>` : '';

  return (
    `<${tag} class="maraton-card${disabled}"${href} data-id="${escapeHTML(p.id)}">` +
    `<div class="maraton-poster-wrap">` +
    (num != null ? `<span class="maraton-num">${num}</span>` : '') +
    int + cine + poster +
    `</div>` +
    `<div class="maraton-body">` +
    `<h2>${escapeHTML(p.titulo)}</h2>` +
    `<div class="maraton-meta">${escapeHTML(String(p.anio || ''))} ${dur}</div>` +
    (p.subgenero ? `<span class="maraton-tag">${escapeHTML(p.subgenero)}</span>` : '') +
    (!compact && p.nota ? `<p class="maraton-nota">${escapeHTML(p.nota)}</p>` : '') +
    `<span class="maraton-cta">${cta}</span>` +
    `</div></${tag}>`
  );
}

function listaFiltrada() {
  if (!maratonActivo) return [];
  return (maratonActivo.peliculas || []).filter(p => {
    if (filtroSub !== 'todos' && p.subgenero !== filtroSub) return false;
    if (filtroInt !== 'todos' && p.intensidad !== filtroInt) return false;
    if (filtroDec !== 'todos' && String(decadeOf(p.anio)) !== filtroDec) return false;
    if (soloResena && !(p.resena && String(p.resena).trim())) return false;
    return true;
  });
}

function llenarFiltros(peliculas) {
  const sub = document.getElementById('filtro-subgenero');
  const dec = document.getElementById('filtro-decada');
  if (sub) {
    const set = new Set();
    peliculas.forEach(p => { if (p.subgenero) set.add(p.subgenero); });
    sub.innerHTML = '<option value="todos">Todos</option>' +
      Array.from(set).sort().map(s =>
        `<option value="${escapeHTML(s)}">${escapeHTML(s)}</option>`
      ).join('');
  }
  if (dec) {
    const set = new Set();
    peliculas.forEach(p => {
      const d = decadeOf(p.anio);
      if (d) set.add(d);
    });
    dec.innerHTML = '<option value="todos">Todas</option>' +
      Array.from(set).sort((a, b) => a - b).map(d =>
        `<option value="${d}">${d}s</option>`
      ).join('');
  }
  filtroSub = 'todos';
  filtroInt = 'todos';
  filtroDec = 'todos';
  soloResena = false;
  const cb = document.getElementById('filtro-resena');
  if (cb) cb.checked = false;
  if (sub) sub.value = 'todos';
  if (dec) dec.value = 'todos';
  const fi = document.getElementById('filtro-intensidad');
  if (fi) fi.value = 'todos';
}

function renderEmpieza() {
  const wrap = document.getElementById('maraton-empieza');
  const grid = document.getElementById('maraton-empieza-grid');
  if (!wrap || !grid || !maratonActivo) return;
  const ids = maratonActivo.empiezaAqui || [];
  const map = mapPelisById(maratonActivo);
  const items = ids.map(id => map[id]).filter(Boolean);
  if (!items.length) { wrap.hidden = true; return; }
  wrap.hidden = false;
  grid.innerHTML = items.map((p, i) => cardHTML(p, i + 1, true)).join('');
}

function renderNoches() {
  const sec = document.getElementById('maraton-noches');
  if (!sec || !maratonActivo) return;
  const noches = maratonActivo.noches || [];
  const map = mapPelisById(maratonActivo);
  if (!noches.length) { sec.hidden = true; sec.innerHTML = ''; return; }
  sec.hidden = false;
  sec.innerHTML = '<h2 class="maraton-grid-title">Por noches</h2>' + noches.map(n => {
    const pelis = (n.ids || []).map(id => map[id]).filter(Boolean);
    if (!pelis.length) return '';
    return (
      `<div class="noche-block">` +
      `<h3>${escapeHTML(n.nombre)}</h3>` +
      `<div class="noche-grid">${pelis.map((p, i) => cardHTML(p, i + 1, true)).join('')}</div>` +
      `</div>`
    );
  }).join('');
}

function renderParejas() {
  const sec = document.getElementById('maraton-parejas');
  const list = document.getElementById('maraton-parejas-list');
  if (!sec || !list || !maratonActivo) return;
  const pares = maratonActivo.parejas || [];
  const map = mapPelisById(maratonActivo);
  if (!pares.length) { sec.hidden = true; return; }
  sec.hidden = false;
  list.innerHTML = pares.map(par => {
    const a = map[par.de];
    const b = map[par.a];
    if (!a || !b) return '';
    return (
      `<div class="pareja-item">` +
      `<strong>${escapeHTML(a.titulo)}</strong>` +
      `<span class="pareja-arrow">→</span>` +
      `<strong>${escapeHTML(b.titulo)}</strong>` +
      (par.texto ? `<span style="opacity:.7">· ${escapeHTML(par.texto)}</span>` : '') +
      `</div>`
    );
  }).join('');
}

function renderGrid() {
  const grid = document.getElementById('maraton-grid');
  const empty = document.getElementById('maraton-empty');
  const count = document.getElementById('maraton-count');
  if (!grid) return;
  const lista = listaFiltrada();
  if (count) {
    count.textContent = lista.length
      ? `${lista.length} película${lista.length !== 1 ? 's' : ''}`
      : 'Sin resultados';
  }
  if (!lista.length) {
    grid.innerHTML = '';
    if (empty) empty.hidden = false;
    return;
  }
  if (empty) empty.hidden = true;
  grid.innerHTML = lista.map((p, i) => cardHTML(p, i + 1, false)).join('');
}

function renderTabs() {
  const tabs = document.getElementById('maraton-tabs');
  if (!tabs) return;
  tabs.innerHTML = DATA.maratones.map(m => {
    const active = maratonActivo && maratonActivo.id === m.id ? ' active' : '';
    const disabled = !m.activo && !(m.peliculas && m.peliculas.length);
    return (
      `<button type="button" class="maraton-tab${active}" data-id="${escapeHTML(m.id)}"` +
      (disabled ? ' disabled' : '') + `>` +
      (m.emoji ? m.emoji + ' ' : '') + escapeHTML(m.nombre) +
      (disabled ? ' <span class="soon">próx.</span>' : '') +
      `</button>`
    );
  }).join('');
  tabs.querySelectorAll('.maraton-tab:not([disabled])').forEach(btn => {
    btn.addEventListener('click', () => seleccionarMaraton(btn.getAttribute('data-id')));
  });
}

function renderHero(m) {
  const eyebrow = document.getElementById('maraton-eyebrow');
  const titulo = document.getElementById('maraton-titulo');
  const desc = document.getElementById('maraton-desc');
  if (eyebrow) eyebrow.textContent = `${m.emoji || ''} ${m.nombre || ''}`;
  if (titulo) titulo.textContent = m.titulo || m.nombre || 'Maratón';
  if (desc) desc.textContent = m.descripcion || '';
  document.body.className = document.body.className.replace(/tema-\w+/g, '').trim();
  document.body.classList.add('page-maratones');
  if (m.tema) document.body.classList.add('tema-' + m.tema);
  renderCountdown(m);
}

function seleccionarMaraton(id) {
  const m = getMaraton(id);
  if (!m) return;
  maratonActivo = m;
  renderHero(m);
  renderTabs();
  llenarFiltros(m.peliculas || []);
  renderEmpieza();
  renderNoches();
  renderParejas();
  renderGrid();
  try { history.replaceState(null, '', '#' + (m.slug || m.id)); } catch (e) {}
}

function copiarLista() {
  const lista = listaFiltrada();
  if (!lista.length) return;
  const texto = lista.map((p, i) =>
    `${i + 1}. ${p.titulo} (${p.anio || '?'})${p.intensidad ? ' · ' + p.intensidad : ''}`
  ).join('\n');
  const full = `${maratonActivo.titulo || 'Maratón'}\n\n${texto}\n\n— Los Brujos del Cine`;
  navigator.clipboard.writeText(full).then(() => {
    const btn = document.getElementById('btn-copiar-lista');
    if (btn) {
      const t = btn.textContent;
      btn.textContent = '✓ Copiado';
      setTimeout(() => { btn.textContent = t; }, 1600);
    }
  }).catch(() => alert('No se pudo copiar'));
}

function sorpresa() {
  const lista = listaFiltrada();
  if (!lista.length) return;
  const p = lista[Math.floor(Math.random() * lista.length)];
  const el = document.querySelector(`.maraton-card[data-id="${p.id}"]`);
  if (el) {
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    el.style.outline = '2px solid var(--maraton-accent)';
    setTimeout(() => { el.style.outline = ''; }, 1800);
  }
}

document.addEventListener('DOMContentLoaded', async () => {
  mostrarSkeleton(8);

  document.getElementById('filtro-subgenero')?.addEventListener('change', e => {
    filtroSub = e.target.value; renderGrid();
  });
  document.getElementById('filtro-intensidad')?.addEventListener('change', e => {
    filtroInt = e.target.value; renderGrid();
  });
  document.getElementById('filtro-decada')?.addEventListener('change', e => {
    filtroDec = e.target.value; renderGrid();
  });
  document.getElementById('filtro-resena')?.addEventListener('change', e => {
    soloResena = e.target.checked; renderGrid();
  });
  document.getElementById('btn-random')?.addEventListener('click', sorpresa);
  document.getElementById('btn-copiar-lista')?.addEventListener('click', copiarLista);
  document.getElementById('btn-limpiar-filtros')?.addEventListener('click', () => {
    llenarFiltros(maratonActivo?.peliculas || []);
    renderGrid();
  });

  document.getElementById('theme-toggle')?.addEventListener('click', () => {
    const html = document.documentElement;
    const actual = html.getAttribute('data-theme') || 'dark';
    const nuevo = actual === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', nuevo);
    try { localStorage.setItem('lbdc-theme', nuevo); } catch (e) {}
  });

  const btnTop = document.getElementById('btn-top');
  if (btnTop) {
    window.addEventListener('scroll', () => {
      btnTop.classList.toggle('is-visible', window.scrollY > 400);
    }, { passive: true });
    btnTop.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  try {
    const res = await fetch('maratones.json');
    if (!res.ok) throw new Error('Error cargando JSON');
    DATA = await res.json();
    const hash = (location.hash || '').replace('#', '');
    let inicial = DATA.maratones.find(m => m.slug === hash && m.activo)
      || DATA.maratones.find(m => m.destacado && m.activo)
      || DATA.maratones.find(m => m.activo)
      || DATA.maratones[0];
    seleccionarMaraton(inicial.id);
  } catch (err) {
    console.error(err);
    const grid = document.getElementById('maraton-grid');
    if (grid) grid.innerHTML = '<p class="maraton-empty">No se pudieron cargar los maratones.</p>';
  }
});