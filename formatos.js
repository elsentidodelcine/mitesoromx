const FORMATOS = [
  // —— Comunes / base ——
  {
    id: '2d',
    icon: '🎬',
    nombre: '2D tradicional',
    badge: 'Mejor precio',
    cadenas: ['cinepolis', 'cinemex'],
    precio: 'Bajo',
    ideal: 'Drama, comedia, diálogo, cualquier salida sin “evento”',
    pros: ['El boleto más accesible', 'Más horarios y complejos', 'Suficiente si la película no es visual'],
    contras: ['Pantalla y sonido estándar', 'Butaca básica'],
    veredicto: 'Default inteligente. Si dudas del upgrade, quédate aquí y gasta en un mejor día u horario.'
  },
  {
    id: '3d',
    icon: '👓',
    nombre: '3D',
    badge: 'Solo a veces',
    cadenas: ['cinepolis', 'cinemex'],
    precio: 'Medio',
    ideal: 'Animación o cine pensado desde el origen en 3D',
    pros: ['Profundidad en pocas cintas bien hechas'],
    contras: ['Oscurece la imagen', 'Lentes molestos', 'La mayoría no lo aprovecha'],
    veredicto: 'Si no estás seguro, no. Prefiere 2D en pantalla grande (Macro / Xtreme / IMAX).'
  },

  // —— Cinépolis ——
  {
    id: 'macro-xe',
    icon: '📺',
    nombre: 'Macro XE',
    badge: 'Mejor upgrade Cinépolis',
    cadenas: ['cinepolis'],
    precio: 'Medio-alto',
    ideal: 'Acción, sci-fi, animación, terror, blockbusters',
    pros: ['Pantalla ~170 m² (mucho más grande que 2D)', 'Sonido potente (hasta ~13,000 W)', 'Buena relación calidad/precio vs IMAX o VIP'],
    contras: ['No está en todos los complejos', 'No es tan “certificado” como IMAX'],
    veredicto: 'El upgrade que más se nota en la película sin irte a precio IMAX. Priorízalo sobre VIP en cine visual.'
  },
  {
    id: 'imax-cp',
    icon: '⬛',
    nombre: 'IMAX (Cinépolis)',
    badge: 'Máxima imagen',
    cadenas: ['cinepolis'],
    precio: 'Alto',
    ideal: 'Estrenos rodados/masterizados para IMAX, Nolan, Marvel grande, sci-fi',
    pros: ['Pantalla y proyección de referencia', 'Imagen más nítida y envolvente', 'Audio IMAX calibrado'],
    contras: ['Más caro', 'Pocas salas en el país', 'No todas son láser de última gen'],
    veredicto: 'Cuando la cinta “pide” IMAX, sí vale. Si es un drama intimista, es dinero de más.'
  },
  {
    id: '4dx-cp',
    icon: '🌪️',
    nombre: '4DX (Cinépolis)',
    badge: 'Experiencia física',
    cadenas: ['cinepolis'],
    precio: 'Alto',
    ideal: 'Acción, persecuciones, terror “de feria”, plan distinto al cine normal',
    pros: ['Butacas con movimiento', 'Viento, agua, aromas, efectos de sala', 'Muy divertido en el género correcto'],
    contras: ['Cansa o distrae en dramas', 'Precio alto', 'No es para ver “en serio” una obra densa'],
    veredicto: 'No es mejor imagen: es atracción. Genial una vez; raro como formato default.'
  },
  {
    id: 'screenx',
    icon: '🖼️',
    nombre: 'ScreenX',
    badge: 'Solo Cinépolis',
    cadenas: ['cinepolis'],
    precio: 'Alto',
    ideal: 'Acción, carreras, épica visual con tomas laterales diseñadas para el formato',
    pros: ['Proyección a 270° (pantalla + muros laterales)', 'Inmersión visual única en México', 'A veces combinado con VIP o Macro XE'],
    contras: ['Pocas salas', 'Los laterales no duran toda la peli', 'La calidad de los lados no iguala el centro'],
    veredicto: 'Curioso y espectacular en el título adecuado. No sustituye a IMAX ni a Macro para “ver bien” la película.'
  },
  {
    id: 'pluus',
    icon: '🪑',
    nombre: 'PLUUS',
    badge: 'Confort intermedio',
    cadenas: ['cinepolis'],
    precio: 'Medio',
    ideal: 'Quienes quieren más comodidad que 2D sin pagar VIP completo',
    pros: ['Asientos más amplios', 'Descansa pies', 'Punto medio entre tradicional y VIP'],
    contras: ['No mejora imagen ni sonido como Macro/IMAX', 'No siempre hay en tu ciudad'],
    veredicto: 'Pagas butaca, no espectáculo. Útil si el 2D se siente apretado y el VIP se va de presupuesto.'
  },
  {
    id: 'vip-cp',
    icon: '🛋️',
    nombre: 'VIP (Cinépolis)',
    badge: 'Máxima comodidad',
    cadenas: ['cinepolis'],
    precio: 'Muy alto',
    ideal: 'Citas, cumpleaños, ver cine sin prisa',
    pros: ['Reclinables', 'Menos gente', 'Servicio / ambiente premium'],
    contras: ['No mejora la película en sí', 'El snack y el boleto suben mucho'],
    veredicto: 'El plan es la comodidad. En blockbuster visual, a veces conviene más Macro o IMAX.'
  },

  // —— Cinemex ——
  {
    id: 'sxtreme',
    icon: '📡',
    nombre: 'CinemeXtremo',
    badge: 'Mejor upgrade Cinemex',
    cadenas: ['cinemex'],
    precio: 'Medio-alto',
    ideal: 'Acción, sci-fi, animación, lo mismo que pedirías en Macro XE',
    pros: ['Pantalla más grande que 2D', 'Dolby Atmos potente (hasta ~24 canales / mucha potencia)', 'Equivalente práctico al Macro XE de Cinépolis'],
    contras: ['No es IMAX certificado', 'Disponibilidad según complejo'],
    veredicto: 'Si estás en Cinemex y la peli es visual, este es el upgrade que más tiene sentido antes de IMAX.'
  },
  {
    id: 'imax-cm',
    icon: '⬛',
    nombre: 'IMAX (Cinemex)',
    badge: 'Máxima imagen',
    cadenas: ['cinemex'],
    precio: 'Alto',
    ideal: 'Estrenos grandes; en algunas sedes hay IMAX Láser / Platino',
    pros: ['Formato IMAX de referencia', 'En Antara y otras: experiencia tope de gama', 'Mejor que Premium o Xtreme en imagen'],
    contras: ['Caro', 'Pocas ubicaciones'],
    veredicto: 'Misma lógica que IMAX en Cinépolis: solo cuando la película lo pide.'
  },
  {
    id: '4dx-cm',
    icon: '🌪️',
    nombre: '4DX (Cinemex)',
    badge: 'Experiencia física',
    cadenas: ['cinemex'],
    precio: 'Alto',
    ideal: 'Acción y “plan experiencia”, igual que 4DX de Cinépolis',
    pros: ['Movimiento + efectos de sala', 'Diversión alta en el género correcto'],
    contras: ['Distrae si vas por la historia', 'Precio premium'],
    veredicto: 'Atracción, no cine “puro”. Elige por mood, no por calidad de imagen.'
  },
  {
    id: 'premium',
    icon: '✨',
    nombre: 'Premium 2D / Premium 3D',
    badge: 'Confort Cinemex',
    cadenas: ['cinemex'],
    precio: 'Medio',
    ideal: 'Más espacio y butaca cómoda sin llegar a Platino',
    pros: ['Butacas más amplias y suaves', 'Más separación entre filas', 'Charola para snack'],
    contras: ['No es pantalla gigante', 'El 3D Premium sigue teniendo los contras del 3D'],
    veredicto: 'El “PLUUS” de Cinemex: comodidad intermedia. Para imagen grande, ve a Xtreme o IMAX.'
  },
  {
    id: 'platino',
    icon: '🛋️',
    nombre: 'Platino (Cinemex)',
    badge: 'VIP Cinemex',
    cadenas: ['cinemex'],
    precio: 'Muy alto',
    ideal: 'Cita, lujo, servicio a la butaca',
    pros: ['Reclinables', 'Servicio tipo restaurante en sala', 'Ambiente premium'],
    contras: ['Precio alto', 'No sustituye IMAX/Xtreme en impacto visual'],
    veredicto: 'Equivalente al VIP de Cinépolis. Pagas el plan, no el formato de proyección.'
  }
];

let filtroCadena = 'todas';

function escapeHTML(s) {
  return String(s || '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

function etiquetaCadena(cadenas) {
  if (cadenas.includes('cinepolis') && cadenas.includes('cinemex')) {
    return '<span class="fmt-chain fmt-chain-both">Ambas cadenas</span>';
  }
  if (cadenas.includes('cinepolis')) {
    return '<span class="fmt-chain fmt-chain-cp">Cinépolis</span>';
  }
  return '<span class="fmt-chain fmt-chain-cm">Cinemex</span>';
}

function renderFormatos() {
  const grid = document.getElementById('formatos-grid');
  if (!grid) return;

  const lista = FORMATOS.filter(f => {
    if (filtroCadena === 'todas') return true;
    return f.cadenas.includes(filtroCadena);
  });

  grid.innerHTML = lista.map(f => `
    <article class="formato-card" data-id="${f.id}">
      <div class="formato-top">
        <div class="formato-icon" aria-hidden="true">${f.icon}</div>
        <div class="formato-top-right">
          ${etiquetaCadena(f.cadenas)}
          <span class="formato-badge">${escapeHTML(f.badge)}</span>
        </div>
      </div>
      <h2>${escapeHTML(f.nombre)}</h2>
      <p class="formato-meta">Precio relativo: <strong>${escapeHTML(f.precio)}</strong></p>
      <p class="formato-ideal"><strong>Ideal para:</strong> ${escapeHTML(f.ideal)}</p>
      <div class="formato-cols">
        <div>
          <h3>Pros</h3>
          <ul>${f.pros.map(p => `<li>${escapeHTML(p)}</li>`).join('')}</ul>
        </div>
        <div>
          <h3>Contras</h3>
          <ul>${f.contras.map(c => `<li>${escapeHTML(c)}</li>`).join('')}</ul>
        </div>
      </div>
      <p class="formato-veredicto"><strong>Veredicto</strong>${escapeHTML(f.veredicto)}</p>
    </article>
  `).join('');
}

document.addEventListener('DOMContentLoaded', () => {
  const bar = document.getElementById('formatos-filtros');
  if (bar) {
    bar.addEventListener('click', (e) => {
      const btn = e.target.closest('[data-cadena]');
      if (!btn) return;
      bar.querySelectorAll('[data-cadena]').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      filtroCadena = btn.dataset.cadena;
      renderFormatos();
    });
  }
  renderFormatos();
});