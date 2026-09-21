/**
 * resena: URL a tu reseña en el blog.
 * - Si ya tienes anclas: "blog.html#iron-man"
 * - O página suelta: "resenas/iron-man.html"
 * - null = aún sin reseña (card no clickeable a reseña)
 */
const SAGAS = {
  ucm: {
    id: 'ucm',
    nombre: 'Universo Cinematográfico de Marvel',
    subtitulo: 'Infinity Saga · orden cronológico hasta Endgame',
    descripcion:
      'Películas del UCM en el orden en que ocurren en la historia (no el de estreno), hasta el cierre de la Saga del Infinito.',
    items: [
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
        resena: 'review.html?id='
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
        resena: 'review.html?id='
      },
      {
        id: 'vengadores',
        titulo: 'Los Vengadores',
        anio: 2012,
        cuando: '2012',
        fase: 'Fase 1',
        nota: 'Batalla de Nueva York. Cierre de la Fase 1.',
        poster: 'imgs/sagas/ucm/avengers.jpeg',
        resena: 'review.html?id='
      },
      {
        id: 'iron-man-3',
        titulo: 'Iron Man 3',
        anio: 2013,
        cuando: '2012–2013',
        fase: 'Fase 2',
        nota: 'Poco después de Nueva York.',
        poster: 'imgs/sagas/ucm/ironman3.jpeg',
        resena: 'review.html?id='
      },
      {
        id: 'thor-mundo-oscuro',
        titulo: 'Thor: El mundo oscuro',
        anio: 2013,
        cuando: '2013',
        fase: 'Fase 2',
        nota: 'Éter / Gema de la Realidad.',
        poster: 'imgs/sagas/ucm/thor2.jpeg',
        resena: 'review.html?id='
      },
      {
        id: 'cap-soldado-invierno',
        titulo: 'Capitán América: El soldado de invierno',
        anio: 2014,
        cuando: '2014',
        fase: 'Fase 2',
        nota: 'Caída de S.H.I.E.L.D. y Hydra.',
        poster: 'imgs/sagas/ucm/capitan2.jpeg',
        resena: 'review.html?id='
      },
      {
        id: 'guardianes',
        titulo: 'Guardianes de la Galaxia',
        anio: 2014,
        cuando: '2014',
        fase: 'Fase 2',
        nota: 'Gema del Poder. Historia paralela en el espacio.',
        poster: 'imgs/sagas/ucm/guardianes.jpeg',
        resena: 'review.html?id='
      },
      {
        id: 'guardianes-2',
        titulo: 'Guardianes de la Galaxia Vol. 2',
        anio: 2017,
        cuando: '2014 (+meses)',
        fase: 'Fase 3',
        nota: 'Pocos meses después del Vol. 1 (aunque se estrenó en 2017).',
        poster: 'imgs/sagas/ucm/guardianes2.jpeg',
        resena: 'review.html?id='
      },
      {
        id: 'era-ultron',
        titulo: 'Vengadores: La era de Ultrón',
        anio: 2015,
        cuando: '2015',
        fase: 'Fase 2',
        nota: 'Sokovia. Visión y gemas.',
        poster: 'imgs/sagas/ucm/ultron.jpeg',
        resena: 'review.html?id='
      },
      {
        id: 'ant-man',
        titulo: 'Ant-Man',
        anio: 2015,
        cuando: '2015',
        fase: 'Fase 2',
        nota: 'Después de Ultrón; reino cuántico.',
        poster: 'imgs/sagas/ucm/antman.jpeg',
        resena: 'review.html?id='
      },
      {
        id: 'civil-war',
        titulo: 'Capitán América: Civil War',
        anio: 2016,
        cuando: '2016',
        fase: 'Fase 3',
        nota: 'Acuerdos de Sokovia. Debut de Spider-Man y Black Panther.',
        poster: 'imgs/sagas/ucm/capitan3.jpeg',
        resena: 'review.html?id='
      },
      {
        id: 'doctor-strange',
        titulo: 'Doctor Strange',
        anio: 2016,
        cuando: '2016–2017',
        fase: 'Fase 3',
        nota: 'Kamar-Taj y la Gema del Tiempo.',
        poster: 'imgs/sagas/ucm/strange.jpeg',
        resena: 'review.html?id='
      },
      {
        id: 'homecoming',
        titulo: 'Spider-Man: Homecoming',
        anio: 2017,
        cuando: '2016 (post Civil War)',
        fase: 'Fase 3',
        nota: 'Poco después de Civil War.',
        poster: 'imgs/sagas/ucm/spiderman.jpeg',
        resena: 'review.html?id='
      },
      {
        id: 'black-panther',
        titulo: 'Black Panther',
        anio: 2018,
        cuando: '2016–2018',
        fase: 'Fase 3',
        nota: 'Tras la muerte de T’Chaka en Civil War.',
        poster: 'imgs/sagas/ucm/pantera.jpeg',
        resena: 'review.html?id='
      },
      {
        id: 'ragnarok',
        titulo: 'Thor: Ragnarok',
        anio: 2017,
        cuando: '2017',
        fase: 'Fase 3',
        nota: 'Camino directo a Infinity War.',
        poster: 'imgs/sagas/ucm/thor3.jpeg',
        resena: 'review.html?id='
      },
      {
        id: 'ant-man-avispa',
        titulo: 'Ant-Man y la Avispa',
        anio: 2018,
        cuando: '2018',
        fase: 'Fase 3',
        nota: 'Paralela / justo antes del chasquido.',
        poster: 'imgs/sagas/ucm/antman2.jpeg',
        resena: 'review.html?id='
      },
      {
        id: 'infinity-war',
        titulo: 'Vengadores: Infinity War',
        anio: 2018,
        cuando: '2018',
        fase: 'Fase 3',
        nota: 'Thanos y el chasquido.',
        poster: 'imgs/sagas/ucm/infinity.jpeg',
        resena: 'review.html?id='
      },
      {
        id: 'endgame',
        titulo: 'Vengadores: Endgame',
        anio: 2019,
        cuando: '2018–2023',
        fase: 'Fase 3',
        nota: 'Cierre de la Infinity Saga.',
        poster: 'imgs/sagas/ucm/endgame.jpeg',
        resena: 'review.html?id='
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
    '</strong><br>' +
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
           ? '<img class="saga-poster" src="' + escapeHTML(item.poster) + '" alt="" loading="lazy" onerror="this.style.visibility=\'hidden\'">'
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
           '<div class="saga-num">' + n + '</div>' +
           poster +
           '<div class="saga-body">' +
           '<h2>' + escapeHTML(item.titulo) + '</h2>' +
           '<div class="saga-meta-row">' +
           '<span class="saga-fase">' + escapeHTML(item.fase) + '</span>' +
           '<span>' + escapeHTML(item.cuando) + '</span>' +
           '<span>Estreno ' + escapeHTML(String(item.anio)) + '</span>' +
           '</div>' +
           (item.nota ? '<p class="saga-nota">' + escapeHTML(item.nota) + '</p>' : '') +
           cta +
           '</div>' +
           '</' + tag + '>'
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
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }
});