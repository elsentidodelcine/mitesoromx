/**
 * resena: URL a tu reseña. null o "" = sin reseña aún
 */
const SAGAS = {
  ucm: {
    id: 'ucm',
    nombre: 'Universo Cinematográfico de Marvel',
    subtitulo: 'Infinity Saga · orden cronológico hasta Endgame',
    descripcion:
      'Películas del UCM en el orden en que ocurren en la historia (no el de estreno), hasta el cierre de la Saga del Infinito.',
    items: [
      /* ... deja aquí tus mismos items del UCM ... */
      {
        id: 'cap-primer-vengador',
        titulo: 'Capitán América: El primer vengador',
        anio: 2011,
        cuando: '1943–1945',
        fase: 'Fase 1',
        nota: 'Segunda Guerra Mundial. Origen de Steve Rogers.',
        poster: 'imgs/sagas/ucm/capitan.jpeg',
        resena: 'review.html?id=capitan-america-el-primer-vengador'
      },
      {
        id: 'capitana-marvel',
        titulo: 'Capitana Marvel',
        anio: 2019,
        cuando: '1995',
        fase: 'Fase 3',
        nota: 'Ambientada décadas antes del resto del UCM moderno.',
        poster: 'imgs/sagas/ucm/marvel.jpeg',
        resena: 'review.html?id=capitana-marvel'
      },
      {
        id: 'iron-man',
        titulo: 'Iron Man',
        anio: 2008,
        cuando: '2008',
        fase: 'Fase 1',
        nota: 'El punto de partida del UCM moderno. “Yo soy Iron Man”.',
        poster: 'imgs/sagas/ucm/ironman.jpeg',
        resena: 'review.html?id=iron-man'
      },
      {
        id: 'increible-hulk',
        titulo: 'El increíble Hulk',
        anio: 2008,
        cuando: '2008 / Semana de Furia',
        fase: 'Fase 1',
        nota: 'Paralela al arranque de la era Stark; encaja cerca de Iron Man 2 / Thor.',
        poster: 'imgs/sagas/ucm/hulk.jpeg',
        resena: ''
      },
      {
        id: 'iron-man-2',
        titulo: 'Iron Man 2',
        anio: 2010,
        cuando: '2010–2011',
        fase: 'Fase 1',
        nota: 'Semana de Furia: se cruza con Hulk y Thor.',
        poster: 'imgs/sagas/ucm/ironman2.jpeg',
        resena: 'review.html?id=iron-man-2'
      },
      {
        id: 'thor',
        titulo: 'Thor',
        anio: 2011,
        cuando: '2011',
        fase: 'Fase 1',
        nota: 'Cierra la Semana de Furia antes de Los Vengadores.',
        poster: 'imgs/sagas/ucm/thor.jpeg',
        resena: ''
      },
      {
        id: 'vengadores',
        titulo: 'Los Vengadores',
        anio: 2012,
        cuando: '2012',
        fase: 'Fase 1',
        nota: 'Batalla de Nueva York. Cierre de la Fase 1.',
        poster: 'imgs/sagas/ucm/avengers.jpeg',
        resena: 'review.html?id=los-vengadores'
      },
      {
        id: 'iron-man-3',
        titulo: 'Iron Man 3',
        anio: 2013,
        cuando: '2012–2013',
        fase: 'Fase 2',
        nota: 'Poco después de Nueva York.',
        poster: 'imgs/sagas/ucm/ironman3.jpeg',
        resena: 'review.html?id=iron-man-3'
      },
      {
        id: 'thor-mundo-oscuro',
        titulo: 'Thor: El mundo oscuro',
        anio: 2013,
        cuando: '2013',
        fase: 'Fase 2',
        nota: 'Éter / Gema de la Realidad.',
        poster: 'imgs/sagas/ucm/thor2.jpeg',
        resena: 'review.html?id=thor-un-mundo-oscuro'
      },
      {
        id: 'cap-soldado-invierno',
        titulo: 'Capitán América: El soldado de invierno',
        anio: 2014,
        cuando: '2014',
        fase: 'Fase 2',
        nota: 'Caída de S.H.I.E.L.D. y Hydra.',
        poster: 'imgs/sagas/ucm/capitan2.jpeg',
        resena: 'review.html?id=capitan-america-el-soldado-de-invierno'
      },
      {
        id: 'guardianes',
        titulo: 'Guardianes de la Galaxia',
        anio: 2014,
        cuando: '2014',
        fase: 'Fase 2',
        nota: 'Gema del Poder. Historia paralela en el espacio.',
        poster: 'imgs/sagas/ucm/guardianes.jpeg',
        resena: 'review.html?id=guardianes-de-la-galaxia'
      },
      {
        id: 'guardianes-2',
        titulo: 'Guardianes de la Galaxia Vol. 2',
        anio: 2017,
        cuando: '2014 (+meses)',
        fase: 'Fase 3',
        nota: 'Pocos meses después del Vol. 1 (aunque se estrenó en 2017).',
        poster: 'imgs/sagas/ucm/guardianes2.jpeg',
        resena: 'review.html?id=guardianes-de-la-galaxia-vol-2'
      },
      {
        id: 'era-ultron',
        titulo: 'Vengadores: La era de Ultrón',
        anio: 2015,
        cuando: '2015',
        fase: 'Fase 2',
        nota: 'Sokovia. Visión y gemas.',
        poster: 'imgs/sagas/ucm/ultron.jpeg',
        resena: 'review.html?id=avengers-era-de-ultron'
      },
      {
        id: 'ant-man',
        titulo: 'Ant-Man',
        anio: 2015,
        cuando: '2015',
        fase: 'Fase 2',
        nota: 'Después de Ultrón; reino cuántico.',
        poster: 'imgs/sagas/ucm/antman.jpeg',
        resena: 'review.html?id=ant-man'
      },
      {
        id: 'civil-war',
        titulo: 'Capitán América: Civil War',
        anio: 2016,
        cuando: '2016',
        fase: 'Fase 3',
        nota: 'Acuerdos de Sokovia. Debut de Spider-Man y Black Panther.',
        poster: 'imgs/sagas/ucm/capitan3.jpeg',
        resena: 'review.html?id=capitan-america-civil-war'
      },
      {
        id: 'doctor-strange',
        titulo: 'Doctor Strange',
        anio: 2016,
        cuando: '2016–2017',
        fase: 'Fase 3',
        nota: 'Kamar-Taj y la Gema del Tiempo.',
        poster: 'imgs/sagas/ucm/strange.jpeg',
        resena: 'review.html?id=doctor-strange'
      },
      {
        id: 'homecoming',
        titulo: 'Spider-Man: Homecoming',
        anio: 2017,
        cuando: '2016 (post Civil War)',
        fase: 'Fase 3',
        nota: 'Poco después de Civil War.',
        poster: 'imgs/sagas/ucm/spiderman.jpeg',
        resena: ''
      },
      {
        id: 'black-panther',
        titulo: 'Black Panther',
        anio: 2018,
        cuando: '2016–2018',
        fase: 'Fase 3',
        nota: 'Tras la muerte de T’Chaka en Civil War.',
        poster: 'imgs/sagas/ucm/pantera.jpeg',
        resena: 'review.html?id=pantera-negra'
      },
      {
        id: 'ragnarok',
        titulo: 'Thor: Ragnarok',
        anio: 2017,
        cuando: '2017',
        fase: 'Fase 3',
        nota: 'Camino directo a Infinity War.',
        poster: 'imgs/sagas/ucm/thor3.jpeg',
        resena: 'review.html?id=thor-ragnarok'
      },
      {
        id: 'ant-man-avispa',
        titulo: 'Ant-Man y la Avispa',
        anio: 2018,
        cuando: '2018',
        fase: 'Fase 3',
        nota: 'Paralela / justo antes del chasquido.',
        poster: 'imgs/sagas/ucm/antman2.jpeg',
        resena: 'review.html?id=ant-man-y-la-avispa'
      },
      {
        id: 'infinity-war',
        titulo: 'Vengadores: Infinity War',
        anio: 2018,
        cuando: '2018',
        fase: 'Fase 3',
        nota: 'Thanos y el chasquido.',
        poster: 'imgs/sagas/ucm/infinity.jpeg',
        resena: 'review.html?id=avengers-infinity-war'
      },
      {
        id: 'endgame',
        titulo: 'Vengadores: Endgame',
        anio: 2019,
        cuando: '2018–2023',
        fase: 'Fase 3',
        nota: 'Cierre de la Infinity Saga.',
        poster: 'imgs/sagas/ucm/endgame.jpeg',
        resena: 'review.html?id=avengers-endgame'
      }
    ]
  },

  rocky: {
    id: 'rocky',
    nombre: 'Rocky y Creed',
    subtitulo: 'Orden de estreno · de Filadelfia a Adonis',
    descripcion:
      'La historia de Rocky Balboa y el relevo generacional con Adonis Creed. Orden de estreno (coincide con el de la trama).',
    items: [
      {
        id: 'rocky',
        titulo: 'Rocky',
        anio: 1976,
        cuando: '1975–1976',
        fase: 'Rocky',
        nota: 'El underdog de Filadelfia frente a Apollo Creed.',
        poster: 'imgs/sagas/rocky/rocky.jpg',
        resena: ''
      },
      {
        id: 'rocky-2',
        titulo: 'Rocky II',
        anio: 1979,
        cuando: '1976–1979',
        fase: 'Rocky',
        nota: 'La revancha. Familia, presión y el título.',
        poster: 'imgs/sagas/rocky/rocky-2.jpg',
        resena: ''
      },
      {
        id: 'rocky-3',
        titulo: 'Rocky III',
        anio: 1982,
        cuando: '1981–1982',
        fase: 'Rocky',
        nota: 'Clubber Lang y el regreso de la “mirada del tigre”.',
        poster: 'imgs/sagas/rocky/rocky-3.jpg',
        resena: ''
      },
      {
        id: 'rocky-4',
        titulo: 'Rocky IV',
        anio: 1985,
        cuando: '1985',
        fase: 'Rocky',
        nota: 'Ivan Drago, Guerra Fría y el round en Rusia.',
        poster: 'imgs/sagas/rocky/rocky-4.jpg',
        resena: ''
      },
      {
        id: 'rocky-5',
        titulo: 'Rocky V',
        anio: 1990,
        cuando: '1990',
        fase: 'Rocky',
        nota: 'Regreso a los barrios; mentor de un nuevo boxeador.',
        poster: 'imgs/sagas/rocky/rocky-5.jpg',
        resena: ''
      },
      {
        id: 'rocky-balboa',
        titulo: 'Rocky Balboa',
        anio: 2006,
        cuando: '2006',
        fase: 'Rocky',
        nota: 'El regreso del campeón años después. Cierre emocional de la saga clásica.',
        poster: 'imgs/sagas/rocky/rocky-balboa.jpg',
        resena: ''
      },
      {
        id: 'creed',
        titulo: 'Creed',
        anio: 2015,
        cuando: '2015',
        fase: 'Creed',
        nota: 'Adonis, hijo de Apollo, y Rocky como entrenador.',
        poster: 'imgs/sagas/rocky/creed.jpg',
        resena: ''
      },
      {
        id: 'creed-2',
        titulo: 'Creed II',
        anio: 2018,
        cuando: '2018',
        fase: 'Creed',
        nota: 'El fantasma de Drago vuelve a la familia Creed.',
        poster: 'imgs/sagas/rocky/creed-2.jpg',
        resena: ''
      },
      {
        id: 'creed-3',
        titulo: 'Creed III',
        anio: 2023,
        cuando: '2023',
        fase: 'Creed',
        nota: 'Adonis frente a su pasado; Michael B. Jordan dirige.',
        poster: 'imgs/sagas/rocky/creed-3.jpg',
        resena: ''
      },
      {
        id: 'yo-soy-rocky',
        titulo: 'Yo Soy Rocky',
        anio: 2026,
        cuando: '2026',
        fase: 'Biográfica',
        nota: 'Biografía de Rocky.',
        poster: 'imgs/sagas/rocky/play-rocky.jpg',
        resena: ''
      }
    ]
  },

  'juegos-del-hambre': {
    id: 'juegos-del-hambre',
    nombre: 'Los Juegos del Hambre',
    subtitulo: 'Orden cronológico · precuela + trilogía',
    descripcion:
      'Primero la precuela (origen de Snow) y luego la historia de Katniss en el orden en que se cuenta en pantalla.',
    items: [
      {
        id: 'balada-pajaros-serpientes',
        titulo: 'Los Juegos del Hambre: Balada de pájaros cantores y serpientes',
        anio: 2023,
        cuando: '64 años antes de Katniss',
        fase: 'Precuela',
        nota: 'Origen de Coriolanus Snow. Ver antes si quieres cronología total.',
        poster: 'imgs/sagas/juegos-del-hambre/balada.jpg',
        resena: ''
      },
      {
        id: 'amanecer-en-la-cosecha',
        titulo: 'Los Juegos del Hambre: Amanecer en la Cosecha',
        anio: 2026,
        cuando: '40 años después de Balada y 24 años antes de Katniss',
        fase: 'Precuela',
        nota: 'Segundo Vasallaje de los 25. Ver antes si quieres cronología total.',
        poster: 'imgs/sagas/juegos-del-hambre/cosecha.jpg',
        resena: ''
      },
      {
        id: 'juegos-del-hambre',
        titulo: 'Los Juegos del Hambre',
        anio: 2012,
        cuando: '74.os Juegos',
        fase: 'Trilogía',
        nota: 'Katniss se ofrece como tributo. El inicio del arco principal.',
        poster: 'imgs/sagas/juegos-del-hambre/hg1.jpg',
        resena: ''
      },
      {
        id: 'en-llamas',
        titulo: 'Los Juegos del Hambre: En llamas',
        anio: 2013,
        cuando: '75.os Juegos (Vasallaje)',
        fase: 'Trilogía',
        nota: 'El Vasallaje de los Veinticinco y la chispa de la rebelión.',
        poster: 'imgs/sagas/juegos-del-hambre/en-llamas.jpg',
        resena: ''
      },
      {
        id: 'sinsajo-1',
        titulo: 'Los Juegos del Hambre: Sinsajo – Parte 1',
        anio: 2014,
        cuando: 'Post-Juegos / Distrito 13',
        fase: 'Trilogía',
        nota: 'Propaganda, Distrito 13 y la figura del Sinsajo.',
        poster: 'imgs/sagas/juegos-del-hambre/sinsajo-1.jpg',
        resena: ''
      },
      {
        id: 'sinsajo-2',
        titulo: 'Los Juegos del Hambre: Sinsajo – Parte 2',
        anio: 2015,
        cuando: 'Asalto al Capitolio',
        fase: 'Trilogía',
        nota: 'Cierre de la guerra y el destino de Panem.',
        poster: 'imgs/sagas/juegos-del-hambre/sinsajo-2.jpg',
        resena: ''
      }
    ]
  }
};

function escapeHTML(s) {
  return String(s == null ? '' : s)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

function renderSaga(sagaId) {
  const saga = SAGAS[sagaId];
  const meta = document.getElementById('saga-meta');
  const timeline = document.getElementById('saga-timeline');
  if (!saga || !meta || !timeline) return;

  meta.innerHTML =
    '<strong>' +
    escapeHTML(saga.nombre) +
    '</strong>' +
    escapeHTML(saga.descripcion) +
    '<br><span style="opacity:.85">' +
    saga.items.length +
    ' películas · ' +
    escapeHTML(saga.subtitulo) +
    '</span>';

  timeline.innerHTML = saga.items
    .map(function (item, i) {
      var n = i + 1;
      var tieneResena = item.resena && String(item.resena).trim();
      var tag = tieneResena ? 'a' : 'div';
      var href = tieneResena ? ' href="' + escapeHTML(item.resena) + '"' : '';
      var disabled = tieneResena ? '' : ' is-disabled';
      var cta = tieneResena
        ? '<span class="saga-cta">Ver reseña →</span>'
        : '<span class="saga-cta">Reseña pendiente</span>';

      var poster = item.poster
        ? '<img class="saga-poster" src="' +
          escapeHTML(item.poster) +
          '" alt="" loading="lazy" width="80" height="120" onerror="this.style.visibility=\'hidden\'">'
        : '<div class="saga-poster saga-poster--empty" aria-hidden="true"></div>';

      return (
        '<' +
        tag +
        ' class="saga-item' +
        disabled +
        '"' +
        href +
        (tieneResena ? '' : ' aria-disabled="true"') +
        '>' +
        '<div class="saga-num">' +
        n +
        '</div>' +
        poster +
        '<div class="saga-body">' +
        '<h2>' +
        escapeHTML(item.titulo) +
        '</h2>' +
        '<div class="saga-meta-row">' +
        '<span class="saga-fase">' +
        escapeHTML(item.fase) +
        '</span>' +
        '<span>' +
        escapeHTML(item.cuando) +
        '</span>' +
        '<span>Estreno ' +
        escapeHTML(String(item.anio)) +
        '</span>' +
        '</div>' +
        (item.nota ? '<p class="saga-nota">' + escapeHTML(item.nota) + '</p>' : '') +
        cta +
        '</div>' +
        '</' +
        tag +
        '>'
      );
    })
    .join('');
}

document.addEventListener('DOMContentLoaded', function () {
  var tabs = document.getElementById('sagas-tabs');
  var actual = 'ucm';
  renderSaga(actual);

  if (tabs) {
    tabs.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-saga]');
      if (!btn) return;
      tabs.querySelectorAll('.saga-tab').forEach(function (b) {
        b.classList.remove('active');
        b.setAttribute('aria-selected', 'false');
      });
      btn.classList.add('active');
      btn.setAttribute('aria-selected', 'true');
      actual = btn.dataset.saga;
      renderSaga(actual);
      var meta = document.getElementById('saga-meta');
      if (meta) meta.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  }
});

/* ===== Nav móvil ===== */
document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.getElementById('nav-toggle');
  const nav = document.getElementById('main-nav');
  if (!toggle || !nav) return;

  toggle.addEventListener('click', (e) => {
    e.stopPropagation();
    const open = nav.classList.toggle('is-open');
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    toggle.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
  });

  nav.querySelectorAll('a').forEach((a) => {
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

/* ===== Theme (único) ===== */
(function () {
  const KEY = 'lbdc-theme';
  const root = document.documentElement;

  function apply(theme) {
    root.setAttribute('data-theme', theme);
    try {
      localStorage.setItem(KEY, theme);
    } catch (_) {}
    const btn = document.getElementById('theme-toggle');
    if (btn) {
      btn.setAttribute(
        'aria-label',
        theme === 'light' ? 'Cambiar a modo oscuro' : 'Cambiar a modo claro'
      );
    }
  }

  try {
    const saved = localStorage.getItem(KEY);
    if (saved === 'light' || saved === 'dark') apply(saved);
    else if (!root.getAttribute('data-theme')) {
      apply(window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark');
    }
  } catch (_) {}

  function bind() {
    const btn = document.getElementById('theme-toggle');
    if (!btn || btn.dataset.themeBound === '1') return;
    btn.dataset.themeBound = '1';
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      const actual = root.getAttribute('data-theme') || 'dark';
      apply(actual === 'light' ? 'dark' : 'light');
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bind);
  } else {
    bind();
  }
})();

/* ===== Back to top ===== */
document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('back-to-top') || document.getElementById('btn-top');
  if (!btn) return;
  const sync = () => btn.classList.toggle('is-visible', window.scrollY > 400);
  window.addEventListener('scroll', sync, { passive: true });
  sync();
  btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
});