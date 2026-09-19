document.addEventListener('DOMContentLoaded', async () => {
  const container = document.getElementById('estrenos-container');
  const filters = document.getElementById('estrenos-filters');
  const selectMes = document.getElementById('filtro-mes');
  const countEl = document.getElementById('resultados-count');
  let calVista = new Date();
  calVista.setDate(1); // primer día del mes actual


  let estrenos = [];
  let filtroTipo = 'todos';
  let filtroMes = 'todos';
  const POR_PAGINA = 12;
  let paginaActual = 1;
  let listaFiltrada = [];

  // Fecha de hoy (sin horas)
  const hoy = new Date();
  hoy.setHours(0, 0, 0, 0);



const MESES_ES = [
  'Enero','Febrero','Marzo','Abril','Mayo','Junio',
  'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'
];

// Semana de cine en México: jueves → miércoles
function inicioSemanaJueves(ref) {
  const x = new Date(ref);
  x.setHours(0, 0, 0, 0);
  const day = x.getDay(); // 0=dom ... 4=jue ... 6=sáb
  // Días desde el jueves más reciente (o hoy si es jueves)
  const diff = (day - 4 + 7) % 7; // 4 = jueves
  x.setDate(x.getDate() - diff);
  return x;
}

function enEstaSemana(fechaStr) {
  if (!fechaStr) return false;
  const f = new Date(fechaStr + 'T12:00:00');
  const hoyLocal = new Date();
  hoyLocal.setHours(0, 0, 0, 0);
  const ini = inicioSemanaJueves(hoyLocal); // jueves actual
  const fin = new Date(ini);
  fin.setDate(fin.getDate() + 6); // miércoles
  fin.setHours(23, 59, 59, 999);
  return f >= ini && f <= fin;
}

function resaltarYScroll(el) {
  el.scrollIntoView({ behavior: 'smooth', block: 'center' });
  el.classList.add('estreno-flash');
  setTimeout(function () {
    el.classList.remove('estreno-flash');
  }, 1600);
}

function renderCalendario(listaCompleta) {
  const grid = document.getElementById('cal-grid');
  const label = document.getElementById('cal-mes-label');
  if (!grid || !label) return;

  const y = calVista.getFullYear();
  const m = calVista.getMonth();
  label.textContent = MESES_ES[m] + ' ' + y;

  const data = Array.isArray(listaCompleta) ? listaCompleta : [];

  const delMes = data
    .filter(function (item) {
      if (!item.fecha) return false;
      const d = new Date(item.fecha + 'T12:00:00');
      return d.getFullYear() === y && d.getMonth() === m;
    })
    .sort(function (a, b) {
      return String(a.fecha).localeCompare(String(b.fecha));
    });

  const porDia = {};
  delMes.forEach(function (item) {
    if (!porDia[item.fecha]) porDia[item.fecha] = [];
    porDia[item.fecha].push(item);
  });

  const dias = Object.keys(porDia).sort();

  if (!dias.length) {
    grid.innerHTML = '<p class="cal-empty">No hay estrenos/preventas cargados en este mes.</p>';
    return;
  }

  var html = '';
  dias.forEach(function (fecha) {
    const d = new Date(fecha + 'T12:00:00');
    const semanaClass = enEstaSemana(fecha) ? ' cal-dia--semana' : '';
    const items = porDia[fecha];
    const dow = d.toLocaleDateString('es-MX', { weekday: 'short' });

    html += '<article class="cal-dia' + semanaClass + '">';
    html += '<header>';
    html += '<span class="cal-num">' + d.getDate() + '</span>';
    html += '<span class="cal-dow">' + dow + '</span>';
    html += '</header><ul>';

    items.forEach(function (it) {
      const esPreventa = String(it.tipo || '').toLowerCase() === 'preventa';
      const liClass = esPreventa ? 'is-preventa' : 'is-estreno';
      const labelTipo = esPreventa
        ? 'Preventa'
        : (it.estadoCartelera === 'estreno' ? 'Estreno' : 'Cartelera');
      const id = it.id || '';
      const poster = it.poster
        ? '<img src="' + escapeHTML(it.poster) + '" alt="" loading="lazy">'
        : '';

      html += '<li class="' + liClass + '" data-estreno-id="' + escapeHTML(id) + '" role="button" tabindex="0" title="Ver en la lista">';
      html += poster;
      html += '<div><strong>' + escapeHTML(it.titulo || 'Sin título') + '</strong>';
      html += '<span>' + labelTipo + '</span></div></li>';
    });

    html += '</ul></article>';
  });

  grid.innerHTML = html;

  grid.querySelectorAll('[data-estreno-id]').forEach(function (el) {
    function ir() {
      const id = el.getAttribute('data-estreno-id');
      if (!id) return;

      var target = document.getElementById('estreno-' + id);
      if (target) {
        resaltarYScroll(target);
        return;
      }

      filtroTipo = 'todos';
      filtroMes = 'todos';
      if (selectMes) selectMes.value = 'todos';
      if (filters) {
        filters.querySelectorAll('.filter-btn').forEach(function (b) {
          b.classList.remove('active');
        });
        var btnTodos = filters.querySelector('[data-filter="todos"]');
        if (btnTodos) btnTodos.classList.add('active');
      }
      paginaActual = 1;
      renderEstrenos();

      requestAnimationFrame(function () {
        var t = document.getElementById('estreno-' + id);
        if (t) resaltarYScroll(t);
      });
    }

    el.addEventListener('click', ir);
    el.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        ir();
      }
    });
  });
}

  // Mostrar skeleton mientras carga
  mostrarSkeleton(8);

  try {

    const response = await fetch('estrenos.json');
    if (!response.ok) throw new Error('No se pudo cargar estrenos.json');
    estrenos = await response.json();

    // Ordenar por fecha (más próximos primero)
    estrenos.sort((a, b) => new Date(a.fecha) - new Date(b.fecha));

    // Llenar el select de meses
    llenarSelectMeses();

    // Ocultar skeleton
   const sk = document.getElementById('skeleton-loading');
   if (sk) sk.style.display = 'none';

      renderCalendario(estrenos); // lista completa
      renderEstrenos();           // lista filtrable de abajo

  } catch (error) {
    console.error(error);
    container.innerHTML = `
      <div class="estrenos-empty">
        No se pudieron cargar los estrenos.<br>
        Intenta de nuevo más tarde.
      </div>`;
  }

  // ===== Filtros de tipo =====
  if (filters) {
    filters.addEventListener('click', (e) => {
      const btn = e.target.closest('.filter-btn');
      if (!btn) return;

      filters.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      filtroTipo = btn.dataset.filter;
      paginaActual = 1;   // ← agregar
      renderEstrenos();
    });
  }

  // ===== Filtro de mes =====
  if (selectMes) {
    selectMes.addEventListener('change', () => {
      filtroMes = selectMes.value;
      paginaActual = 1;   // ← agregar
      renderEstrenos();
    });
  }

  document.getElementById('cal-prev')?.addEventListener('click', () => {
    calVista.setMonth(calVista.getMonth() - 1);
    renderCalendario(estrenos);
  });

  document.getElementById('cal-next')?.addEventListener('click', () => {
    calVista.setMonth(calVista.getMonth() + 1);
    renderCalendario(estrenos);
  });

  // ===== Skeleton =====
    function mostrarSkeleton(cantidad = 8) {
      const skeleton = document.getElementById('skeleton-loading');
      if (!skeleton) return;

      let html = '';
      for (let i = 0; i < cantidad; i++) {
        html += `
          <div class="skeleton-card">
            <div class="skeleton-poster"></div>
            <div class="skeleton-body">
              <div class="skeleton-line short"></div>
              <div class="skeleton-line medium"></div>
              <div class="skeleton-line" style="width:40%"></div>
            </div>
          </div>`;
      }
      skeleton.innerHTML = html;
      skeleton.style.display = 'grid';
    }

  // ===== Tipo dinámico =====
 function getTipoEfectivo(item) {
   if (item.tipo !== 'preventa') return item.tipo;
   return diasRestantes(item.fecha) <= 0 ? 'estreno' : 'preventa';
 }

  // ===== Llenar opciones de mes =====
  function llenarSelectMeses() {
    if (!selectMes) return;

    const mesesUnicos = new Set();

    estrenos.forEach(item => {
      const d = new Date(item.fecha);
      const key = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
      mesesUnicos.add(key);
    });

    const mesesOrdenados = Array.from(mesesUnicos).sort();

    const nombresMes = [
      'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
      'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];

    selectMes.innerHTML = `<option value="todos">Todos los meses</option>`;

    mesesOrdenados.forEach(key => {
      const [year, month] = key.split('-');
      const nombre = `${nombresMes[parseInt(month) - 1]} ${year}`;
      selectMes.innerHTML += `<option value="${key}">${nombre}</option>`;
    });
  }

function renderEstrenos() {
  let lista = [...estrenos];

  if (filtroTipo === 'fuera') {
    // Solo las que YA NO están en cartelera
    lista = lista.filter(e =>
      e.estadoCartelera === 'fuera' || e.enCartelera === false
    );
  } else {
    // En todos los demás filtros: ocultar las que ya salieron
    lista = lista.filter(e =>
      e.estadoCartelera !== 'fuera' && e.enCartelera !== false
    );

    if (filtroTipo === 'preventa') {
      lista = lista.filter(e => getTipoEfectivo(e) === 'preventa');
    } else if (filtroTipo === 'reestreno') {
      lista = lista.filter(e => getTipoEfectivo(e) === 'reestreno');
    } else if (filtroTipo === 'estreno') {
      // Solo estrenos NUEVOS (no los que ya pasaron a "en cartelera")
      lista = lista.filter(e => {
        const tipo = getTipoEfectivo(e);
        return tipo === 'estreno' && e.estadoCartelera !== 'cartelera';
      });
    } else if (filtroTipo === 'cartelera') {
      lista = lista.filter(e => e.estadoCartelera === 'cartelera');
    }
    // filtroTipo === 'todos' → ya filtramos los "fuera", mostramos el resto
  }

  if (filtroMes !== 'todos') {
    lista = lista.filter(item => {
      const d = new Date(item.fecha);
      const key = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
      return key === filtroMes;
    });
  }

  listaFiltrada = lista;
  renderPagina();
}

 function renderPagina() {
   const pagNav = document.getElementById('estrenos-pagination');
   const total = listaFiltrada.length;
   const totalPaginas = Math.max(1, Math.ceil(total / POR_PAGINA));

   if (paginaActual > totalPaginas) paginaActual = totalPaginas;
   if (paginaActual < 1) paginaActual = 1;

   // Contador de resultados
   /*if (countEl) {
     countEl.textContent = total === 0
       ? 'No hay resultados'
       : `${total} elemento{total !== 1 ? 's' : ''} encontrado${total !== 1 ? 's' : ''}`;
   }*/

   if (total === 0) {
     container.innerHTML = `
       <div class="estrenos-empty">
         <p style="font-size:1.1rem; margin-bottom:8px;">No hay estrenos con estos filtros</p>
         <p style="font-size:0.9rem; color:var(--muted); margin-bottom:20px;">Prueba cambiando el tipo o el mes</p>
         <button type="button" class="filter-btn" id="limpiar-filtros">
           Ver todos los estrenos
         </button>
       </div>`;

     document.getElementById('limpiar-filtros')?.addEventListener('click', () => {
       filtroTipo = 'todos';
       filtroMes = 'todos';
       if (selectMes) selectMes.value = 'todos';
       filters?.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
       filters?.querySelector('[data-filter="todos"]')?.classList.add('active');
       paginaActual = 1;
       renderEstrenos();
     });

     if (pagNav) {
       pagNav.hidden = true;
       pagNav.innerHTML = '';
     }
     return;
   }

   const inicio = (paginaActual - 1) * POR_PAGINA;
   const slice = listaFiltrada.slice(inicio, inicio + POR_PAGINA);

   container.innerHTML = `
     <div class="estrenos-grid">
       ${slice.map(crearCard).join('')}
     </div>
   `;

   renderControlesPaginacion(total, totalPaginas);
 }

  function renderControlesPaginacion(total, totalPaginas) {
    const pagNav = document.getElementById('estrenos-pagination');
    if (!pagNav) return;

    if (totalPaginas <= 1) {
      pagNav.hidden = true;
      pagNav.innerHTML = '';
      return;
    }

    pagNav.hidden = false;

    let html = '';
    html += `<button type="button" class="page-btn" data-page="prev" ${paginaActual === 1 ? 'disabled' : ''}>←</button>`;

    const windowSize = 5;
    let from = Math.max(1, paginaActual - Math.floor(windowSize / 2));
    let to = Math.min(totalPaginas, from + windowSize - 1);
    from = Math.max(1, to - windowSize + 1);

    for (let i = from; i <= to; i++) {
      html += `<button type="button" class="page-btn ${i === paginaActual ? 'active' : ''}" data-page="${i}">${i}</button>`;
    }

    html += `<button type="button" class="page-btn" data-page="next" ${paginaActual === totalPaginas ? 'disabled' : ''}>→</button>`;
    html += `<div class="page-info">Página ${paginaActual} de ${totalPaginas} · ${total} estrenos</div>`;

    pagNav.innerHTML = html;

    pagNav.querySelectorAll('.page-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const v = btn.dataset.page;
        if (v === 'prev') paginaActual--;
        else if (v === 'next') paginaActual++;
        else paginaActual = Number(v);
        renderPagina();
        container.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
    });
  }

  // ===== Crear tarjeta =====
function crearCard(item) {
  const tipo = getTipoEfectivo(item);
  const d = diasRestantes(item.fecha);

  const badgeClass = tipo === 'reestreno' ? 'reestreno' :
                     tipo === 'preventa'  ? 'preventa'  : '';

  const badgeTexto = tipo === 'reestreno' ? 'Reestreno' :
                     tipo === 'preventa'  ? 'Preventa'  : 'Estreno';

  const estado = etiquetaCartelera(item);
  const estadoHTML = estado
    ? `<span class="estreno-estado estreno-estado--${estado.key}">${estado.label}</span>`
    : '';

  const cinesHTML = (item.cines || [])
    .map(c => `<span class="cine-tag">${escapeHTML(c)}</span>`)
    .join('');

  // Clases para resaltar Hoy / Esta semana
  let extraClass = '';
  let countdownClass = '';
  if (d === 0) {
    extraClass = 'hoy';
    countdownClass = 'hoy';
  } else if (d > 0 && d <= 7) {
    extraClass = 'esta-semana';
    countdownClass = 'esta-semana';
  }

   const idAttr = item.id ? 'id="estreno-' + escapeHTML(item.id) + '"' : '';

   return `
     <article class="estreno-card ${extraClass}" ${idAttr} data-estreno-id="${escapeHTML(item.id || '')}">
      <div class="estreno-poster">
        <img
          src="${escapeHTML(item.poster)}"
          alt="Póster de ${escapeHTML(item.titulo)}"
          loading="lazy"
          decoding="async"
          onerror="this.src='imgs/posters/placeholder.jpg'; this.onerror=null;"
        >
        <span class="estreno-badge ${badgeClass}">${badgeTexto}</span>
        ${estadoHTML}
      </div>

      <div class="estreno-body">
        <div class="estreno-fecha">${escapeHTML(item.fechaTexto || item.fecha)}</div>
        <h2 class="estreno-titulo">${escapeHTML(item.titulo)}</h2>
        ${item.nota ? `<p class="estreno-nota">${escapeHTML(item.nota)}</p>` : ''}
        <div class="estreno-cines">${cinesHTML}</div>
        <span class="estreno-countdown ${countdownClass}">${textoContador(item.fecha)}</span>
      </div>
    </article>
  `;
}


  function escapeHTML(str) {
    if (str == null) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }
});

document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.getElementById('nav-toggle');
  const nav = document.getElementById('main-nav');
  if (!toggle || !nav) return;

  toggle.addEventListener('click', () => {
    const open = nav.classList.toggle('is-open');
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    toggle.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
  });

  // Cerrar al tocar un link
  nav.querySelectorAll('a').forEach(a => {
    a.addEventListener('click', () => {
      nav.classList.remove('is-open');
      toggle.setAttribute('aria-expanded', 'false');
      toggle.setAttribute('aria-label', 'Abrir menú');
    });
  });

  // Cerrar al hacer click fuera
  document.addEventListener('click', (e) => {
    if (!nav.classList.contains('is-open')) return;
    if (nav.contains(e.target) || toggle.contains(e.target)) return;
    nav.classList.remove('is-open');
    toggle.setAttribute('aria-expanded', 'false');
    toggle.setAttribute('aria-label', 'Abrir menú');
  });
});

function diasRestantes(fechaStr) {
  // fechaStr: "2026-08-29"
  const hoy = new Date();
  hoy.setHours(0, 0, 0, 0);
  const f = new Date(fechaStr + 'T00:00:00');
  const diff = Math.round((f - hoy) / 86400000);
  return diff;
}

function textoContador(fechaStr) {
  const d = diasRestantes(fechaStr);
  if (d > 1)  return `Faltan ${d} días`;
  if (d === 1) return 'Falta 1 día';
  if (d === 0) return 'Hoy se estrena';
  if (d > -7)  return 'Estrenada hace poco';
  return 'Ya estrenada';
}

function badgeEstreno(item) {
  const d = diasRestantes(item.fecha);
  if (d === 0) return '<span class="badge badge-hoy">Hoy se estrena</span>';
  if (d > 0) return '<span class="badge badge-preventa">Preventa</span>';
  return '<span class="badge badge-estreno">Estreno</span>';
}

function filtrarPorCadena(lista, cadena) {
  if (cadena === 'todas') return lista;
  return lista.filter(item => {
    const cines = item.cines || item.cadenas || [];
    // si es array de strings:
    if (cines.some(c => String(c).toLowerCase().includes(cadena))) return true;
    // si es array de objetos { nombre: "Cinépolis" }:
    if (cines.some(c => (c.nombre || c.cadena || '').toLowerCase().includes(cadena))) return true;
    return false;
  });
}

document.getElementById('filter-cadena')?.addEventListener('change', () => {
  renderEstrenos(); // tu función de pintado
});


function etiquetaCartelera(item) {
  if (item.tipo === 'preventa') return null;

  if (item.estadoCartelera === 'estreno') {
    return { key: 'hoy', label: 'En estreno' };
  }
  if (item.estadoCartelera === 'cartelera') {
    return { key: 'encartelera', label: 'En cartelera' };
  }
  if (item.estadoCartelera === 'fuera') {
    return { key: 'salio', label: 'Fuera de cartelera' };
  }

  // por si aún tienes enCartelera true/false en algunas
  if (item.enCartelera === true) {
    return { key: 'encartelera', label: 'En cartelera' };
  }
  if (item.enCartelera === false) {
    return { key: 'salio', label: 'Fuera de cartelera' };
  }
  return null;
}

// ===== Toggle tema claro / oscuro =====
document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('theme-toggle');
  if (!btn) return;

  btn.addEventListener('click', () => {
    const html = document.documentElement;
    const actual = html.getAttribute('data-theme') || 'dark';
    const nuevo = actual === 'dark' ? 'light' : 'dark';

    html.setAttribute('data-theme', nuevo);
    try {
      localStorage.setItem('lbdc-theme', nuevo);
    } catch (e) {}
  });
});

// ===== Botón volver arriba =====
const btnTop = document.getElementById('btn-top');

function toggleBtnTop() {
  if (!btnTop) return;
  if (window.scrollY > 400) {
    btnTop.classList.add('is-visible');
  } else {
    btnTop.classList.remove('is-visible');
  }
}

window.addEventListener('scroll', toggleBtnTop, { passive: true });
toggleBtnTop(); // por si la página ya está scrolleada

if (btnTop) {
  btnTop.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
}
