const FORMATOS = [
  {
    id: '2d',
    icon: '🎬',
    nombre: '2D tradicional',
    badge: 'Mejor precio',
    ideal: 'Casi cualquier película, sobre todo drama y comedia',
    pros: ['Precio accesible', 'Más horarios', 'Pantalla suficiente'],
    contras: ['Menos inmersión', 'Butaca básica'],
    veredicto: 'La opción default. Ideal si vas por la historia, no por el espectáculo.'
  },
  {
    id: 'macro',
    icon: '🔊',
    nombre: 'Macro XE / pantalla grande',
    badge: 'Mejor upgrade',
    ideal: 'Blockbusters, animación, sci-fi, terror intenso',
    pros: ['Imagen y sonido potentes', 'Más inmersión', 'Mejor relación calidad/precio que VIP'],
    contras: ['No en todos los complejos', 'Puede ser excesivo en dramas quietos'],
    veredicto: 'El upgrade que sí se nota en la película. Priorízalo sobre VIP en acción.'
  },
  {
    id: 'vip',
    icon: '🛋️',
    nombre: 'VIP / Platino',
    badge: 'Máxima comodidad',
    ideal: 'Citas, cumpleaños, rewatch sin prisa',
    pros: ['Butaca reclinable', 'Menos gente', 'Ambiente premium'],
    contras: ['Cuesta notablemente más', 'No mejora imagen ni historia'],
    veredicto: 'Pagas comodidad, no calidad de imagen. Vale si el plan es disfrutar el plan.'
  },
  {
    id: '3d',
    icon: '👓',
    nombre: '3D',
    badge: 'Solo a veces',
    ideal: 'Animación o cine diseñado desde el origen en 3D',
    pros: ['Efecto wow en pocas cintas'],
    contras: ['Oscurece la imagen', 'Lentes molestos', 'Pocas lo aprovechan de verdad'],
    veredicto: 'Si dudas, no. Quédate en 2D grande o Macro.'
  }
];

const grid = document.getElementById('formatos-grid');
if (grid) {
  grid.innerHTML = FORMATOS.map(f => `
    <article class="formato-card" data-id="${f.id}">
      <div class="formato-top">
        <div class="formato-icon" aria-hidden="true">${f.icon}</div>
        <span class="formato-badge">${f.badge}</span>
      </div>
      <h2>${f.nombre}</h2>
      <p class="formato-ideal"><strong>Ideal para:</strong> ${f.ideal}</p>
      <div class="formato-cols">
        <div>
          <h3>Pros</h3>
          <ul>${f.pros.map(p => `<li>${p}</li>`).join('')}</ul>
        </div>
        <div>
          <h3>Contras</h3>
          <ul>${f.contras.map(c => `<li>${c}</li>`).join('')}</ul>
        </div>
      </div>
      <p class="formato-veredicto"><strong>Veredicto</strong>${f.veredicto}</p>
    </article>
  `).join('');
}