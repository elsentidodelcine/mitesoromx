const FORMATOS = [
  {
    id: '2d',
    nombre: '2D tradicional',
    precio: 'Más barato',
    ideal: 'Casi cualquier película',
    pros: ['Precio accesible', 'Pantalla suficiente', 'Más horarios'],
    contras: ['Menos inmersión', 'Butacas básicas'],
    veredicto: 'La opción default. Perfecta si vas por la historia, no por el espectáculo.'
  },
  {
    id: 'vip',
    nombre: 'VIP / Platino',
    precio: 'Alto',
    ideal: 'Citas, rewatch cómodo, estrenos “de evento”',
    pros: ['Butaca reclinable', 'Menos gente', 'Mejor snack en sala'],
    contras: ['Cuesta notablemente más', 'No mejora la película en sí'],
    veredicto: 'Paga por comodidad, no por imagen. Vale si el plan es disfrutar sin prisa.'
  },
  {
    id: 'macro',
    nombre: 'Macro XE / pantalla grande',
    precio: 'Medio-alto',
    ideal: 'Blockbusters, animación, terror intenso',
    pros: ['Imagen y sonido potentes', 'Más inmersión', 'Buena relación calidad/precio vs VIP'],
    contras: ['No en todos los complejos', 'Puede ser “demasiado” en dramas quietos'],
    veredicto: 'El mejor upgrade si la película es visual. Priorízalo sobre VIP en acción.'
  },
  {
    id: '3d',
    nombre: '3D',
    precio: 'Medio',
    ideal: 'Animación o cine diseñado en 3D',
    pros: ['Efecto wow en algunas cintas'],
    contras: ['Oscurece la imagen', 'Lentes molestos', 'Pocas películas lo aprovechan'],
    veredicto: 'Solo si la cinta está pensada en 3D. Si dudas, quédate en 2D grande.'
  }
];

const grid = document.getElementById('formatos-grid');
if (grid) {
  grid.innerHTML = FORMATOS.map(f => `
    <article class="formato-card">
      <header>
        <h2>${f.nombre}</h2>
        <span class="formato-precio">${f.precio}</span>
      </header>
      <p class="formato-ideal"><strong>Ideal para:</strong> ${f.ideal}</p>
      <div class="formato-cols">
        <div><h3>Pros</h3><ul>${f.pros.map(p => `<li>${p}</li>`).join('')}</ul></div>
        <div><h3>Contras</h3><ul>${f.contras.map(c => `<li>${c}</li>`).join('')}</ul></div>
      </div>
      <p class="formato-veredicto">${f.veredicto}</p>
    </article>
  `).join('');
}