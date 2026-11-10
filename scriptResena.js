document.addEventListener('DOMContentLoaded', async () => {
  const grid = document.getElementById('reviews-grid');
  const subtitle = document.getElementById('reviews-subtitle');
  const rankingBox = document.getElementById('ranking-list');
  const rankingSubtitle = document.getElementById('ranking-subtitle');
  const paginationBox = document.getElementById('reviews-pagination');
  const countEl = document.getElementById('reviews-count');
  const featuredContainer = document.getElementById('featured-review-container');
  const activeFiltersBox = document.getElementById('active-filters');
  const btnLimpiar = document.getElementById('btn-limpiar');

  let paginaActual = 1;
  const RESEÑAS_POR_PAGINA = 6;
  let todasResenas = [];

  // ===== Carga con cache simple =====
  async function cargarResenas() {
    const CACHE_KEY = 'lbdc-resenas-v1';
    const CACHE_TTL = 1000 * 60 * 30; // 30 min

    try {
      const cached = sessionStorage.getItem(CACHE_KEY);
      if (cached) {
        const { data, ts } = JSON.parse(cached);
        if (Date.now() - ts < CACHE_TTL && Array.isArray(data)) {
          return data;
        }
      }
    } catch (_) {}

    const response = await fetch('resenas.json');
    if (!response.ok) throw new Error('No se pudo cargar resenas.json');
    const data = await response.json();
    const lista = Array.isArray(data) ? data : [];

    try {
      sessionStorage.setItem(CACHE_KEY, JSON.stringify({ data: lista, ts: Date.now() }));
    } catch (_) {}

    return lista;
  }

  try {
    todasResenas = await cargarResenas();

    llenarGeneros(todasResenas);
    actualizarHero(todasResenas.length);
    renderRanking(todasResenas);

    // Listeners
    ['review-search', 'filter-genero', 'filter-puntaje', 'filter-orden'].forEach(id => {
      const el = document.getElementById(id);
      if (!el) return;
      el.addEventListener('input', () => { paginaActual = 1; pintar(); });
      el.addEventListener('change', () => { paginaActual = 1; pintar(); });
    });

    btnLimpiar?.addEventListener('click', limpiarFiltros);

    // Botón explorar del hero
    document.getElementById('btn-explorar')?.addEventListener('click', (e) => {
      e.preventDefault();
      scrollToResenas();
    });

    pintar();
  } catch (error) {
    console.error('Error cargando reseñas:', error);
    if (subtitle) subtitle.textContent = 'No se pudieron cargar las reseñas.';
    if (grid) {
      grid.innerHTML = `
        <div class="empty-state">
          <p>No se pudieron cargar las reseñas.</p>
          <p class="empty-hint">Revisa tu conexión o intenta más tarde.</p>
        </div>`;
      grid.removeAttribute('aria-busy');
    }
  }

  // ===== Pintar todo =====
  function pintar() {
    const filtradas = aplicarFiltros(todasResenas);
    actualizarSubtitulo(filtradas.length, todasResenas.length);
    actualizarChips();
    actualizarBotonLimpiar();

  if (!filtradas.length) {
      if (grid) {
        grid.innerHTML = `
          <div class="empty-state">
            <p>Ninguna película coincide con esos filtros.</p>
            <p class="empty-hint">Prueba con otros géneros o limpia los filtros.</p>
            <button type="button" class="button button-secondary" id="empty-limpiar">Limpiar filtros</button>
          </div>`;
        document.getElementById('empty-limpiar')?.addEventListener('click', limpiarFiltros);
      }
      if (paginationBox) paginationBox.innerHTML = '';
      if (countEl) countEl.textContent = '';
      grid?.removeAttribute('aria-busy');
      return;
    }

    const totalPaginas = Math.ceil(filtradas.length / RESEÑAS_POR_PAGINA) || 1;
    if (paginaActual > totalPaginas) paginaActual = 1;

    const inicio = (paginaActual - 1) * RESEÑAS_POR_PAGINA;
    const pagina = filtradas.slice(inicio, inicio + RESEÑAS_POR_PAGINA);

   const items = pagina;

    if (grid) {
      grid.innerHTML = items.map(cardSecundaria).join('');
      grid.removeAttribute('aria-busy');
    }

    if (countEl) {
      countEl.textContent = filtradas.length === 1
        ? '1 reseña'
        : `${filtradas.length} reseñas`;
    }

    crearPaginacionResenas(filtradas.length);
  }

  function cardDestacada(r) {
    const enlace = `review.html?id=${encodeURIComponent(r.id)}`;
    const grimorio = obtenerGrimorio(r.puntaje);
    const estrellas = generarEstrellasHTML(r.puntaje);
    const lectura = tiempoLectura(r.contenido);
    const esNueva = esResenaNueva(r.fecha);

    return `
      <article class="featured-card ${Number(r.puntaje) >= 4.5 ? 'top-rated' : ''}">
        <a href="${enlace}" class="featured-image" aria-label="Leer reseña de ${escapeHTML(r.titulo)}">
          <img src="${escapeHTML(r.poster)}" alt="Póster de ${escapeHTML(r.titulo)}"
               width="300" height="450" loading="eager" decoding="async"
               onerror="this.onerror=null; this.style.display='none'; this.closest('.featured-image')?.classList.add('poster-placeholder');">
          <div class="poster-fallback">
            <span>${escapeHTML(r.posterFallback?.linea1 || r.titulo)}</span>
            <strong>${escapeHTML(r.posterFallback?.linea2 || '')}</strong>
          </div>
          ${esNueva ? '<span class="badge-nueva">Nueva</span>' : ''}
          <span class="card-score">★ ${escapeHTML(String(r.puntaje))}</span>
        </a>
        <div class="featured-content">
          <div class="movie-meta">
            <span>${escapeHTML(String(r.anio))}</span>
            ${(r.generos || []).map(g => `<span class="meta-dot" aria-hidden="true"></span><span>${escapeHTML(g)}</span>`).join('')}
          </div>
          <h3><a href="${enlace}">${escapeHTML(r.titulo)}</a></h3>
          <div class="card-grimorio">
            <span class="card-grimorio-title">${escapeHTML(grimorio.titulo)}</span>
          </div>
          <p>${escapeHTML(r.resumen)}</p>
          <div class="featured-footer">
            <div class="card-rating">
              <span class="stars" aria-hidden="true">${estrellas}</span>
              <span>${escapeHTML(String(r.puntaje))}</span>
            </div>
            <span class="reading-time">${escapeHTML(lectura)}</span>
            <a href="${enlace}" class="button button-primary">Leer reseña →</a>
          </div>
        </div>
      </article>`;
  }

  function cardSecundaria(r) {
    const enlace = `review.html?id=${encodeURIComponent(r.id)}`;
    const grimorio = obtenerGrimorio(r.puntaje);
    const estrellas = generarEstrellasHTML(r.puntaje);
    const lectura = tiempoLectura(r.contenido);
    const esTop = Number(r.puntaje) >= 4.5;
    const esNueva = esResenaNueva(r.fecha);
    const generosHTML = (r.generos || [])
      .map(g => `<span class="meta-dot" aria-hidden="true"></span><span>${escapeHTML(g)}</span>`)
      .join('');

    return `
      <article class="review-card ${esTop ? 'top-rated' : ''}">
        <a href="${enlace}" class="card-image" aria-label="Leer reseña de ${escapeHTML(r.titulo)}">
          <img src="${escapeHTML(r.poster)}" alt="Póster de ${escapeHTML(r.titulo)}"
               width="200" height="300" loading="lazy" decoding="async"
               onerror="this.onerror=null; this.style.display='none'; this.closest('.card-image')?.classList.add('poster-placeholder');">
          <div class="poster-fallback">
            <span>${escapeHTML(r.posterFallback?.linea1 || r.titulo)}</span>
            <strong>${escapeHTML(r.posterFallback?.linea2 || '')}</strong>
            <small>IMAGEN DEL PÓSTER</small>
          </div>
          ${esNueva ? '<span class="badge-nueva">Nueva</span>' : ''}
          <span class="card-label">${escapeHTML(r.etiqueta || 'RESEÑA')}</span>
          <span class="card-score">★ ${escapeHTML(String(r.puntaje))}</span>
        </a>
        <div class="card-content">
          <div class="movie-meta">
            <span>${escapeHTML(String(r.anio))}</span>
            ${generosHTML}
          </div>
          <h3><a href="${enlace}">${escapeHTML(r.titulo)}</a></h3>
          <div class="card-grimorio">
            <span class="card-grimorio-title">${escapeHTML(grimorio.titulo)}</span>
          </div>
          <p>${escapeHTML(r.resumen)}</p>
          <div class="reading-time">${escapeHTML(lectura)}</div>
          <div class="card-footer">
            <div class="card-rating">
              <span class="stars" aria-hidden="true">${estrellas}</span>
              <span>${escapeHTML(String(r.puntaje))}</span>
            </div>
            <a href="${enlace}">Leer reseña →</a>
          </div>
        </div>
      </article>`;
  }

  function crearPaginacionResenas(totalItems) {
    if (!paginationBox) return;
    paginationBox.innerHTML = '';

    const totalPaginas = Math.ceil(totalItems / RESEÑAS_POR_PAGINA) || 1;
    if (totalPaginas <= 1) return;

    const prev = document.createElement('button');
    prev.type = 'button';
    prev.textContent = '←';
    prev.disabled = paginaActual === 1;
    prev.setAttribute('aria-label', 'Página anterior');
    prev.onclick = () => { paginaActual--; pintar(); scrollToResenas(); };
    paginationBox.appendChild(prev);

    let start = Math.max(1, paginaActual - 2);
    let end = Math.min(totalPaginas, start + 4);
    if (end - start < 4) start = Math.max(1, end - 4);

    for (let i = start; i <= end; i++) {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.textContent = i;
      if (i === paginaActual) {
        btn.classList.add('active');
        btn.setAttribute('aria-current', 'page');
      }
      btn.setAttribute('aria-label', `Página ${i} de ${totalPaginas}`);
      btn.onclick = () => { paginaActual = i; pintar(); scrollToResenas(); };
      paginationBox.appendChild(btn);
    }

    const next = document.createElement('button');
    next.type = 'button';
    next.textContent = '→';
    next.disabled = paginaActual === totalPaginas;
    next.setAttribute('aria-label', 'Página siguiente');
    next.onclick = () => { paginaActual++; pintar(); scrollToResenas(); };
    paginationBox.appendChild(next);
  }

  function renderRanking(resenas) {
    if (!rankingBox) return;

    const anioActual = new Date().getFullYear();
    const delAnio = resenas.filter(r => Number(r.anio) === anioActual);

    const top = delAnio
      .slice()
      .sort((a, b) =>
        Number(b.puntaje) - Number(a.puntaje) ||
        (a.titulo || '').localeCompare(b.titulo || '', 'es')
      )
      .slice(0, 12);

    if (rankingSubtitle) {
      rankingSubtitle.textContent = top.length
        ? `Top ${top.length} de ${anioActual} según el grimorio.`
        : `Aún no hay reseñas de ${anioActual}.`;
    }

    if (!top.length) {
      rankingBox.innerHTML = `<li class="empty-state">Aún no hay reseñas de ${anioActual}.</li>`;
      return;
    }

    rankingBox.innerHTML = top.map((r, i) => {
      const grim = obtenerGrimorio(r.puntaje).titulo;
      return `
        <li>
          <a class="ranking-row" href="review.html?id=${encodeURIComponent(r.id)}">
            <span class="ranking-pos">${i + 1}</span>
            <img src="${escapeHTML(r.poster || '')}" alt="" class="ranking-poster" width="40" height="60" loading="lazy"
                 onerror="this.style.visibility='hidden'">
            <div class="ranking-info">
              <strong>${escapeHTML(r.titulo)}</strong>
              <span class="ranking-meta">${escapeHTML(String(r.anio || ''))} · ${escapeHTML(grim)}</span>
            </div>
            <span class="ranking-score">${escapeHTML(String(r.puntaje))}<small>/5</small></span>
          </a>
        </li>`;
    }).join('');
  }

  // ===== Filtros y chips =====
  function hayFiltrosActivos() {
    const q = (document.getElementById('review-search')?.value || '').trim();
    const gen = document.getElementById('filter-genero')?.value || 'todos';
    const minP = document.getElementById('filter-puntaje')?.value || 'todos';
    return !!(q || gen !== 'todos' || minP !== 'todos');
  }

  function actualizarChips() {
    if (!activeFiltersBox) return;
    const chips = [];

    const q = (document.getElementById('review-search')?.value || '').trim();
    if (q) chips.push({ key: 'search', label: `Buscar: “${q}”` });

    const gen = document.getElementById('filter-genero')?.value;
    if (gen && gen !== 'todos') chips.push({ key: 'genero', label: `Género: ${gen}` });

    const minP = document.getElementById('filter-puntaje')?.value;
    if (minP && minP !== 'todos') chips.push({ key: 'puntaje', label: `★ ${minP}+` });

    if (!chips.length) {
      activeFiltersBox.hidden = true;
      activeFiltersBox.innerHTML = '';
      return;
    }

    activeFiltersBox.hidden = false;
    activeFiltersBox.innerHTML = chips.map(c => `
      <button type="button" class="filter-chip" data-key="${c.key}">
        ${escapeHTML(c.label)} <span aria-hidden="true">×</span>
      </button>`).join('');

    activeFiltersBox.querySelectorAll('.filter-chip').forEach(btn => {
      btn.addEventListener('click', () => {
        const key = btn.dataset.key;
        if (key === 'search') document.getElementById('review-search').value = '';
        if (key === 'genero') document.getElementById('filter-genero').value = 'todos';
        if (key === 'puntaje') document.getElementById('filter-puntaje').value = 'todos';
        paginaActual = 1;
        pintar();
      });
    });
  }

  function actualizarBotonLimpiar() {
    if (!btnLimpiar) return;
    btnLimpiar.hidden = !hayFiltrosActivos();
  }

  function limpiarFiltros() {
    const search = document.getElementById('review-search');
    const gen = document.getElementById('filter-genero');
    const puntaje = document.getElementById('filter-puntaje');
    const orden = document.getElementById('filter-orden');

    if (search) search.value = '';
    if (gen) gen.value = 'todos';
    if (puntaje) puntaje.value = 'todos';
    if (orden) orden.value = 'fecha';

    paginaActual = 1;
    pintar();
  }

  function actualizarHero(total) {
    const heroCount = document.getElementById('hero-count');
    const heroLabel = document.getElementById('hero-label');
    if (heroCount) heroCount.textContent = total.toString().padStart(2, '0');
    if (heroLabel) {
      heroLabel.textContent = total === 1 ? 'Reseña publicada' : 'Reseñas publicadas';
    }
  }

  function actualizarSubtitulo(nFiltro, nTotal) {
    if (!subtitle) return;
    const filtrando = hayFiltrosActivos();

    if (nTotal === 0) {
      subtitle.textContent = 'Aún no hay reseñas publicadas.';
      return;
    }
    if (filtrando) {
      subtitle.textContent = nFiltro === 0
        ? 'Ningún resultado.'
        : nFiltro === 1
          ? '1 reseña encontrada.'
          : `${nFiltro} reseñas encontradas.`;
      return;
    }

    if (nTotal === 1) subtitle.innerHTML = `Una película.<br>Una opinión sin vueltas.`;
    else if (nTotal === 2) subtitle.innerHTML = `Dos películas. Dos mundos.<br>Una opinión sin vueltas.`;
    else subtitle.innerHTML = `${numeroATexto(nTotal)} películas. ${numeroATexto(nTotal)} mundos.<br>Una opinión sin vueltas.`;
  }

  function scrollToResenas() {
    const section = document.getElementById('resenas');
    if (!section) return;
    const offset = 80;
    const top = section.getBoundingClientRect().top + window.pageYOffset - offset;
    window.scrollTo({ top, behavior: 'smooth' });
  }

  function esResenaNueva(fecha) {
    if (!fecha) return false;
    const d = new Date(fecha);
    if (isNaN(d)) return false;
    const dias = (Date.now() - d.getTime()) / (1000 * 60 * 60 * 24);
    return dias <= 14;
  }
});

/* ===== Helpers ===== */
function escapeHTML(str) {
  if (str == null) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function numeroATexto(n) {
  const numeros = {
    1: 'Una', 2: 'Dos', 3: 'Tres', 4: 'Cuatro', 5: 'Cinco',
    6: 'Seis', 7: 'Siete', 8: 'Ocho', 9: 'Nueve', 10: 'Diez',
    11: 'Once', 12: 'Doce', 13: 'Trece', 14: 'Catorce', 15: 'Quince'
  };
  return numeros[n] || n.toString();
}

function tiempoLectura(contenido) {
  const texto = Array.isArray(contenido) ? contenido.join(' ') : String(contenido || '');
  const palabras = texto.trim().split(/\s+/).filter(Boolean).length;
  const min = Math.max(1, Math.round(palabras / 200));
  return `${min} min de lectura`;
}

function llenarGeneros(resenas) {
  const sel = document.getElementById('filter-genero');
  if (!sel) return;
  const set = new Set();
  resenas.forEach(r => (r.generos || []).forEach(g => set.add(g)));
  [...set].sort((a, b) => a.localeCompare(b, 'es')).forEach(g => {
    const opt = document.createElement('option');
    opt.value = g;
    opt.textContent = g;
    sel.appendChild(opt);
  });
}

function aplicarFiltros(resenas) {
  const q = (document.getElementById('review-search')?.value || '').trim().toLowerCase();
  const gen = document.getElementById('filter-genero')?.value || 'todos';
  const minP = document.getElementById('filter-puntaje')?.value || 'todos';
  const orden = document.getElementById('filter-orden')?.value || 'fecha';

  let list = resenas.filter(r => {
    const okQ = !q || (r.titulo || '').toLowerCase().includes(q);
    const okG = gen === 'todos' || (r.generos || []).includes(gen);
    const okP = minP === 'todos' || Number(r.puntaje) >= Number(minP);
    return okQ && okG && okP;
  });

  return list.slice().sort((a, b) => {
    if (orden === 'puntaje') return Number(b.puntaje) - Number(a.puntaje);
    if (orden === 'titulo') return (a.titulo || '').localeCompare(b.titulo || '', 'es');
    const fa = a.fecha ? new Date(a.fecha) : new Date(0);
    const fb = b.fecha ? new Date(b.fecha) : new Date(0);
    return fb - fa;
  });
}

function obtenerGrimorio(puntaje) {
  const valor = Number(puntaje) || 0;
  const key = Math.round(valor * 2) / 2;

  const grimorio = {
    0.5: { titulo: 'HECHIZO FALLIDO', descripcion: 'Se ve por morbo, no por gusto. Al final queda la culpa… y el coraje.' },
    1.0: { titulo: 'MAL AUGURIO', descripcion: 'Nada funciona. Cada minuto duele y terminarla es más mérito del espectador que de la película.' },
    1.5: { titulo: 'BRUJERÍA OSCURA', descripcion: 'Un desastre anunciado. No entretiene, no sorprende y solo deja arrepentimiento.' },
    2.0: { titulo: 'CONJURO MAL EJECUTADO', descripcion: 'Tiene ideas, pero todo sale mal. Aburre, se siente torpe o se desinfla rápido.' },
    2.5: { titulo: 'NEUTRAL, PERO OLVIDABLE', descripcion: 'Cumple lo básico y ya. No molesta, pero tampoco se queda contigo.' },
    3.0: { titulo: 'PALOMERA RITUAL', descripcion: 'Funciona mientras dura. Ideal para apagar el cerebro y dejarla pasar.' },
    3.5: { titulo: 'BUEN EMBRUJO', descripcion: 'Entretenida y con momentos sólidos. Le falta fuerza para trascender, pero se disfruta.' },
    4.0: { titulo: 'HECHIZO BIEN LOGRADO', descripcion: 'Bien hecha, efectiva y cumplidora. Sales satisfecho y el boleto lo vale.' },
    4.5: { titulo: 'MAGIA MAYOR', descripcion: 'Destaca, conecta y se queda en la memoria. Muy fácil de recomendar.' },
    5.0: { titulo: 'CINE LEGENDARIO', descripcion: 'Pura magia. De esas que justifican amar el cine y querer volver a verla.' }
  };

  return grimorio[key] || {
    titulo: 'SIN CLASIFICAR',
    descripcion: 'Esta película aún no tiene un hechizo asignado en el grimorio.'
  };
}

function generarEstrellasHTML(puntaje) {
  const valor = Number(puntaje) || 0;
  const llenas = Math.floor(valor);
  const decimal = valor - llenas;
  let html = '';

  for (let i = 1; i <= 5; i++) {
    if (i <= llenas) html += '<span class="star-full">★</span>';
    else if (i === llenas + 1 && decimal >= 0.25 && decimal < 0.75) html += '<span class="star-half">★</span>';
    else if (i === llenas + 1 && decimal >= 0.75) html += '<span class="star-full">★</span>';
    else html += '<span class="star-empty">★</span>';
  }
  return html;
}

/* ===== Nav móvil ===== */
document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.getElementById('nav-toggle');
  const nav = document.getElementById('main-nav');
  if (!toggle || !nav) return;

  toggle.addEventListener('click', () => {
    const open = nav.classList.toggle('is-open');
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    toggle.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
  });

  nav.querySelectorAll('a').forEach(a => {
    a.addEventListener('click', () => {
      nav.classList.remove('is-open');
      toggle.setAttribute('aria-expanded', 'false');
      toggle.setAttribute('aria-label', 'Abrir menú');
    });
  });

  document.addEventListener('click', (e) => {
    if (!nav.classList.contains('is-open')) return;
    if (nav.contains(e.target) || toggle.contains(e.target)) return;
    nav.classList.remove('is-open');
    toggle.setAttribute('aria-expanded', 'false');
    toggle.setAttribute('aria-label', 'Abrir menú');
  });
});

/* ===== Theme toggle ===== */
(function () {
  const KEY = 'lbdc-theme';
  const root = document.documentElement;
  const btn = () => document.getElementById('theme-toggle');

  function apply(theme) {
    root.setAttribute('data-theme', theme);
    try { localStorage.setItem(KEY, theme); } catch (_) {}
    const b = btn();
    if (b) {
      b.setAttribute('aria-label', theme === 'light' ? 'Cambiar a modo oscuro' : 'Cambiar a modo claro');
    }
  }

  let saved = null;
  try { saved = localStorage.getItem(KEY); } catch (_) {}
  if (saved === 'light' || saved === 'dark') {
    apply(saved);
  } else if (window.matchMedia('(prefers-color-scheme: light)').matches) {
    apply('light');
  } else {
    apply('dark');
  }

  document.addEventListener('DOMContentLoaded', () => {
    btn()?.addEventListener('click', () => {
      const next = root.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
      apply(next);
    });
  });
})();

/* ===== Back to top ===== */
document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('back-to-top');
  if (!btn) return;

  const onScroll = () => {
    if (window.scrollY > 380) {
      btn.hidden = false;
      btn.classList.add('is-visible');
    } else {
      btn.classList.remove('is-visible');
      // delay hide for fade
      setTimeout(() => {
        if (window.scrollY <= 380) btn.hidden = true;
      }, 200);
    }
  };

  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  btn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
});