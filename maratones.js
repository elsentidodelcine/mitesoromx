let DATA = { maratones: [] };
let maratonActivo = null;
let filtroSub = 'todos';

function escapeHTML(s) {
  return String(s == null ? '' : s)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

function getMaraton(id) {
  return DATA.maratones.find(function (m) { return m.id === id; });
}

function renderTabs() {
  const tabs = document.getElementById('maraton-tabs');
  if (!tabs) return;

  tabs.innerHTML = DATA.maratones.map(function (m) {
    const active = maratonActivo && maratonActivo.id === m.id ? ' active' : '';
    const disabled = !m.activo && (!m.peliculas || !m.peliculas.length);
    const soon = disabled ? ' <span class="soon">próx.</span>' : '';
    return (
      '<button type="button" class="maraton-tab' + active + '" data-id="' +
      escapeHTML(m.id) + '"' +
      (disabled ? ' disabled' : '') +
      ' role="tab">' +
      (m.emoji ? m.emoji + ' ' : '') +
      escapeHTML(m.nombre) +
      soon +
      '</button>'
    );
  }).join('');

  tabs.querySelectorAll('.maraton-tab:not([disabled])').forEach(function (btn) {
    btn.addEventListener('click', function () {
      seleccionarMaraton(btn.getAttribute('data-id'));
    });
  });
}

function llenarSubgeneros(peliculas) {
  const sel = document.getElementById('filtro-subgenero');
  if (!sel) return;
  const set = new Set();
  (peliculas || []).forEach(function (p) {
    if (p.subgenero) set.add(p.subgenero);
  });
  const opts = ['<option value="todos">Todos</option>'];
  Array.from(set).sort().forEach(function (s) {
    opts.push('<option value="' + escapeHTML(s) + '">' + escapeHTML(s) + '</option>');
  });
  sel.innerHTML = opts.join('');
  sel.value = 'todos';
  filtroSub = 'todos';
}

function renderHero(m) {
  const eyebrow = document.getElementById('maraton-eyebrow');
  const titulo = document.getElementById('maraton-titulo');
  const desc = document.getElementById('maraton-desc');
  if (eyebrow) eyebrow.textContent = (m.emoji || '') + ' ' + (m.nombre || '');
  if (titulo) titulo.textContent = m.titulo || m.nombre || 'Maratón';
  if (desc) desc.textContent = m.descripcion || '';

  document.body.className = document.body.className
    .replace(/tema-\w+/g, '')
    .trim();
  document.body.classList.add('page-maratones');
  if (m.tema) document.body.classList.add('tema-' + m.tema);
}

function renderGrid() {
  const grid = document.getElementById('maraton-grid');
  const empty = document.getElementById('maraton-empty');
  const count = document.getElementById('maraton-count');
  if (!grid || !maratonActivo) return;

  let lista = maratonActivo.peliculas || [];
  if (filtroSub !== 'todos') {
    lista = lista.filter(function (p) { return p.subgenero === filtroSub; });
  }

  if (count) {
    count.textContent = lista.length
      ? lista.length + ' película' + (lista.length !== 1 ? 's' : '')
      : 'Sin películas aún';
  }

  if (!lista.length) {
    grid.innerHTML = '';
    if (empty) empty.hidden = false;
    return;
  }
  if (empty) empty.hidden = true;

  grid.innerHTML = lista.map(function (p, i) {
    const tiene = p.resena && String(p.resena).trim();
    const tag = tiene ? 'a' : 'article';
    const href = tiene ? ' href="' + escapeHTML(p.resena) + '"' : '';
    const disabled = tiene ? '' : ' is-disabled';
    const cta = tiene ? 'Ver reseña →' : 'Sin reseña aún';
    const poster = p.poster
      ? '<img src="' + escapeHTML(p.poster) + '" alt="" loading="lazy" onerror="this.style.opacity=\'0.3\'">'
      : '';

    return (
      '<' + tag + ' class="maraton-card' + disabled + '"' + href + '>' +
      '<div class="maraton-poster-wrap">' +
      '<span class="maraton-num">' + (i + 1) + '</span>' +
      poster +
      '</div>' +
      '<div class="maraton-body">' +
      '<h2>' + escapeHTML(p.titulo) + '</h2>' +
      '<div class="maraton-meta">' + escapeHTML(String(p.anio || '')) + '</div>' +
      (p.subgenero ? '<span class="maraton-tag">' + escapeHTML(p.subgenero) + '</span>' : '') +
      (p.nota ? '<p class="maraton-nota">' + escapeHTML(p.nota) + '</p>' : '') +
      '<span class="maraton-cta">' + cta + '</span>' +
      '</div>' +
      '</' + tag + '>'
    );
  }).join('');
}

function seleccionarMaraton(id) {
  const m = getMaraton(id);
  if (!m) return;
  maratonActivo = m;
  filtroSub = 'todos';
  renderHero(m);
  renderTabs();
  llenarSubgeneros(m.peliculas);
  renderGrid();
  try {
    history.replaceState(null, '', '#' + m.slug);
  } catch (e) {}
}

document.addEventListener('DOMContentLoaded', async function () {
  document.getElementById('filtro-subgenero')?.addEventListener('change', function (e) {
    filtroSub = e.target.value;
    renderGrid();
  });

  document.getElementById('theme-toggle')?.addEventListener('click', function () {
    const html = document.documentElement;
    const actual = html.getAttribute('data-theme') || 'dark';
    const nuevo = actual === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', nuevo);
    try { localStorage.setItem('lbdc-theme', nuevo); } catch (e) {}
  });

  const btnTop = document.getElementById('btn-top');
  if (btnTop) {
    window.addEventListener('scroll', function () {
      btnTop.classList.toggle('is-visible', window.scrollY > 400);
    }, { passive: true });
    btnTop.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  try {
    const res = await fetch('maratones.json');
    if (!res.ok) throw new Error('No se pudo cargar maratones.json');
    DATA = await res.json();

    const hash = (location.hash || '').replace('#', '');
    let inicial = DATA.maratones.find(function (m) { return m.slug === hash && m.activo; });
    if (!inicial) {
      inicial = DATA.maratones.find(function (m) { return m.destacado && m.activo; })
        || DATA.maratones.find(function (m) { return m.activo; })
        || DATA.maratones[0];
    }
    seleccionarMaraton(inicial.id);
  } catch (err) {
    console.error(err);
    const grid = document.getElementById('maraton-grid');
    if (grid) {
      grid.innerHTML = '<p class="maraton-empty">No se pudieron cargar los maratones.</p>';
    }
  }
});