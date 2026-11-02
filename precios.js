document.addEventListener('DOMContentLoaded', async () => {
  const container = document.getElementById('precios-container');
  const tabs = document.getElementById('cine-tabs');
  const selectComplejo = document.getElementById('filtro-complejo');
  const complejoWrap = document.getElementById('complejo-filter-wrap');
  const ciudadTabs = document.getElementById('ciudad-tabs');

  let data = [];
  let metaActualizado = null;
  let promos = [];
  let cadenaActiva = 'cinepolis';
  let complejoActivo = null;
  let ciudadActiva = 'sfr';

  let filtroDia = 'promedio';
  let filtroModo = 'combo';
  let filtroCat = 'todos';
  let usarClub = false;
  let filtroPreset = '1';

  const DIAS = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
  const DIAS_LABEL = {
    lunes: 'lunes', martes: 'martes', miercoles: 'miércoles', jueves: 'jueves',
    viernes: 'viernes', sabado: 'sábado', domingo: 'domingo'
  };

  const CIUDADES = {
    sfr: {
      nombre: 'San Francisco del Rincón',
      complejos: ['san-francisco', 'movie-center']
    },
    leon: {
      nombre: 'León',
      complejos: [
        'plaza-mayor-market', 'plaza-mayor-platino', 'plaza-stadium',
        'hilamas', 'leon', 'paso-morelos', 'galerias-las-torres',
        'altacia', 'altacia-vip', 'city-center-vip', 'centro-max', 'leon-centro'
      ]
    },
    'leon-cadenas': {
      nombre: 'León · por cadena',
      modo: 'cadenas',
      complejos: [
        'plaza-mayor-market', 'plaza-mayor-platino', 'plaza-stadium',
        'hilamas', 'leon', 'paso-morelos', 'galerias-las-torres',
        'altacia', 'altacia-vip', 'city-center-vip', 'centro-max', 'leon-centro'
      ]
    }
  };

  try {
    const response = await fetch('precios.json');
    if (!response.ok) throw new Error('No se pudo cargar precios.json');

    const raw = await response.json();
    data = normalizarData(raw);
    const sk = document.getElementById('precios-skeleton');
    if (sk) sk.remove();

    bindTabs();
    bindHerramientas();
    bindQtyButtons();
    bindPresets();

    marcarDiaHoyEnSelect();
    filtroDia = diaDeHoy();
    const selDia = document.getElementById('filtro-dia');
    if (selDia) selDia.value = filtroDia;

    const simDia = document.getElementById('sim-dia');
    if (simDia) {
      simDia.value = filtroDia;
      marcarDiaHoyEnSimSelect();
    }

    cargarComplejos();
    render();
    actualizarNotaFecha();
    llenarVersusSelects();
    refreshTools();
    renderPromos();
  } catch (error) {
    console.error(error);
    if (container) {
      container.innerHTML = `
        <div class="error-state">
          No se pudieron cargar los precios.<br>
          Revisa que exista el archivo <strong>precios.json</strong> junto a esta página.
        </div>`;
    }
  }

  function normalizarData(raw) {
    if (Array.isArray(raw)) {
      metaActualizado = null;
      promos = [];
      return raw;
    }
    metaActualizado = raw.actualizado || null;
    promos = Array.isArray(raw.promos) ? raw.promos : [];
    return raw.cadenas || [];
  }

  function bindTabs() {
    if (tabs) {
      tabs.addEventListener('click', (e) => {
        const btn = e.target.closest('.cine-tab');
        if (!btn) return;
        tabs.querySelectorAll('.cine-tab').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        cadenaActiva = btn.dataset.cadena;
        cargarComplejos();
        render();
      });
    }

    if (selectComplejo) {
      selectComplejo.addEventListener('change', () => {
        complejoActivo = selectComplejo.value;
        render();
      });
    }

    if (ciudadTabs) {
      ciudadTabs.addEventListener('click', (e) => {
        const btn = e.target.closest('.ciudad-tab');
        if (!btn) return;
        ciudadTabs.querySelectorAll('.ciudad-tab').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        ciudadActiva = btn.dataset.ciudad;
        renderComparativa(ciudadActiva);
        renderTipYTop3();
      });
    }
  }

  function getCadena() {
    return data.find(c => c.cadena === cadenaActiva);
  }

  function getComplejo() {
    const cadena = getCadena();
    if (!cadena) return null;
    return (cadena.complejos || []).find(c => c.id === complejoActivo) || (cadena.complejos || [])[0];
  }

  function cargarComplejos() {
    const cadena = getCadena();
    if (!cadena || !selectComplejo) return;

    const complejos = cadena.complejos || [];
    if (complejos.length <= 1) complejoWrap?.classList.add('hidden');
    else complejoWrap?.classList.remove('hidden');

    selectComplejo.innerHTML = complejos
      .map(c => `<option value="${escapeHTML(c.id)}">${escapeHTML(c.nombre)}</option>`)
      .join('');

    complejoActivo = complejos[0]?.id || null;
    selectComplejo.value = complejoActivo || '';
  }

  function render() {
    const cadena = getCadena();
    const complejo = getComplejo();
    if (!cadena || !complejo) {
      container.innerHTML = `<div class="error-state">No hay datos para este complejo.</div>`;
      return;
    }

    const cat = complejo.categoria || (/vip|platino/i.test(complejo.nombre) ? 'premium' : 'economico');
    const badgeCat = cat === 'premium'
      ? '<span class="badge badge-premium" data-tip="Sala VIP / Platino, precio más alto">VIP / Platino</span>'
      : '<span class="badge badge-economico" data-tip="Sala tradicional, mejor precio">Económico</span>';

    const badgeDulcero = tieneDulcero(complejo)
      ? ''
      : '<span class="badge badge-sin-dulcero" data-tip="Sin precios de dulcero en la base de datos">Sin dulcero cargado</span>';

    container.innerHTML = `
      <section class="cine-section active">
        <div class="cine-header">
          <h2>
            ${escapeHTML(cadena.nombreCadena)} — ${escapeHTML(complejo.nombre)}
            ${badgeCat}
            ${badgeDulcero}
          </h2>
          <span class="ubicacion">${escapeHTML(complejo.zona || 'Precios de referencia')}</span>
        </div>
        ${!tieneDulcero(complejo) ? `
          <div class="tip-box" style="margin-bottom:18px">
            Este complejo no tiene precios de dulcero en la base.
            El ranking en modo “Boleto + dulcero” lo omite o marca “—”.
          </div>` : ''}
        ${tablaBoletos(complejo)}
        ${tablaSnacks(complejo)}
      </section>
    `;
  }

  function tablaBoletos(complejo) {
    let boletos = complejo.boletos || [];
    if (!Array.isArray(boletos)) boletos = [boletos];
    const diasLabel = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
    const hoyKey = diaDeHoy();

    const filas = boletos.map(b => `
      <tr>
        <td>${escapeHTML(b.label || 'Boleto')}</td>
        ${DIAS.map(dia => {
          const p = b[dia];
          const vacio = p == null || p === 0;
          const hoyClass = dia === hoyKey ? ' hoy-col' : '';
          return `<td class="${vacio ? 'na' : 'precio'}${hoyClass}">${vacio ? '—' : fmt(p)}</td>`;
        }).join('')}
      </tr>
    `).join('');

    return `
      <div class="table-block">
        <h3><span>★</span> Boletos</h3>
        <div class="table-scroll">
          <table class="precios-table">
            <thead>
              <tr>
                <th>Tipo</th>
                ${DIAS.map((dia, i) => {
                  const hoyClass = dia === hoyKey ? ' hoy-col' : '';
                  return `<th class="${hoyClass}">${diasLabel[i]}${dia === hoyKey ? ' · hoy' : ''}</th>`;
                }).join('')}
              </tr>
            </thead>
            <tbody>${filas}</tbody>
          </table>
        </div>
      </div>
    `;
  }

  function tablaSnacks(complejo) {
    const snacks = complejo.snacks || [];
    const filas = snacks.map(s => {
      const paraLlevar = s.parallevar ?? s.paraLlevar ?? s['para llevar'] ?? null;
      return `
        <tr>
          <td>${escapeHTML(s.nombre)}</td>
          <td class="${s.chica == null ? 'na' : 'precio'}">${fmt(s.chica)}</td>
          <td class="${s.mediana == null ? 'na' : 'precio'}">${fmt(s.mediana)}</td>
          <td class="${s.grande == null ? 'na' : 'precio'}">${fmt(s.grande)}</td>
          <td class="${s.jumbo == null ? 'na' : 'precio'}">${fmt(s.jumbo)}</td>
          <td class="${paraLlevar == null ? 'na' : 'precio'}">${fmt(paraLlevar)}</td>
        </tr>
      `;
    }).join('');

    return `
      <div class="table-block">
        <h3><span>★</span> Dulcero / Snacks</h3>
        <div class="table-scroll">
          <table class="precios-table snacks-table">
            <thead>
              <tr>
                <th>Producto</th>
                <th>Chica</th>
                <th>Mediana</th>
                <th>Grande</th>
                <th>Jumbo</th>
                <th>Para llevar</th>
              </tr>
            </thead>
            <tbody>${filas}</tbody>
          </table>
        </div>
      </div>
    `;
  }

  function flatComplejos() {
    const list = [];
    data.forEach(cadena => {
      (cadena.complejos || []).forEach(c => {
        let boletos = c.boletos || [];
        if (!Array.isArray(boletos)) boletos = [boletos];
        list.push({
          ...c,
          boletos,
          cadena: cadena.cadena,
          nombreCadena: cadena.nombreCadena,
          categoria: c.categoria || (/vip|platino/i.test(c.nombre || '') ? 'premium' : 'economico')
        });
      });
    });
    return list;
  }

  function precioBoleto(complejo, dia, { nino = false, club = false } = {}) {
    const boletos = complejo.boletos || [];
    if (club) {
      const memb = boletos.find(b => /club|tarjeta/i.test(b.label || ''));
      if (memb && memb[dia] > 0) return Number(memb[dia]);
    }
    if (nino) {
      const n = boletos.find(b => /niñ|nino/i.test(b.label || ''));
      if (n && n[dia] > 0) return Number(n[dia]);
    }
    const b2d =
      boletos.find(b => /2d/i.test(b.label || '') && !/niñ|nino/i.test(b.label || '')) ||
      boletos.find(b => !/vip|platino|club|tarjeta|macro|pluus|junior|dolby|x4d/i.test(b.label || '')) ||
      boletos[0];
    const v = b2d?.[dia];
    return v == null || v === 0 ? null : Number(v);
  }

  function snackSize(complejo, nombreRx, size) {
    const s = (complejo.snacks || []).find(x => nombreRx.test(x.nombre || ''));
    if (!s) return null;
    const v = s[size];
    return v == null ? null : Number(v);
  }

  function getSnackGrande(complejo, nombreRegex) {
    return snackSize(complejo, nombreRegex, 'grande');
  }

  function costoPersona(complejo, dia, modo = filtroModo, club = usarClub) {
    const boleto = precioBoleto(complejo, dia, { club });
    if (boleto == null) return null;
    if (modo === 'boleto') return boleto;

    const palSize = modo === 'premium' ? 'jumbo' : 'grande';
    let pal = snackSize(complejo, /palomitas/i, palSize);
    if (pal == null && modo === 'premium') pal = snackSize(complejo, /palomitas/i, 'grande');
    const ref =
      snackSize(complejo, /refresco/i, modo === 'premium' ? 'jumbo' : 'grande') ??
      snackSize(complejo, /refresco/i, 'grande');

    if (pal == null || ref == null) return null;
    return boleto + pal + ref;
  }

  function scoreComplejo(complejo) {
    if (filtroDia === 'promedio') {
      const vals = DIAS.map(d => costoPersona(complejo, d)).filter(v => v != null);
      if (!vals.length) return null;
      return Math.round(vals.reduce((a, b) => a + b, 0) / vals.length);
    }
    return costoPersona(complejo, filtroDia);
  }

  function multiplicadorPreset() {
    if (filtroPreset === 'pareja') return { adultos: 2, ninos: 0 };
    if (filtroPreset === 'familia') return { adultos: 2, ninos: 2 };
    return { adultos: 1, ninos: 0 };
  }

  function scoreConPreset(complejo) {
    const base = scoreComplejo(complejo);
    if (base == null) return null;
    const { adultos, ninos } = multiplicadorPreset();
    return base * adultos + (ninos > 0 ? Math.round(base * 0.85) * ninos : 0);
  }

  function renderResumenRapido() {
    const el = document.getElementById('resumen-rapido');
    if (!el) return;

    const ciudadKey = ciudadActiva === 'leon-cadenas' ? 'leon' : (ciudadActiva || 'sfr');
    const ids = CIUDADES[ciudadKey]?.complejos || CIUDADES.sfr.complejos;
    const lista = filtrarLista(ids)
      .map(c => ({ c, score: scoreComplejo(c) }))
      .filter(x => x.score != null)
      .sort((a, b) => a.score - b.score);

    if (!lista.length) {
      el.hidden = true;
      return;
    }

    const g = lista[0];
    const diaTxt = filtroDia === 'promedio' ? 'promedio semanal' : (DIAS_LABEL[filtroDia] || filtroDia);
    el.innerHTML = `
      Hoy conviene <strong>${escapeHTML(g.c.nombreCadena)} · ${escapeHTML(g.c.nombre)}</strong>
      con <strong>${fmt(g.score)}</strong> (${escapeHTML(diaTxt)}, modo actual).
    `;
    el.hidden = false;
  }

  function mejorDiaDe(complejo) {
    let best = null;
    DIAS.forEach(d => {
      const v = costoPersona(complejo, d);
      if (v == null) return;
      if (!best || v < best.v) best = { d, v };
    });
    return best;
  }

  function filtrarLista(ids) {
    return flatComplejos()
      .filter(c => ids.includes(c.id))
      .filter(c => filtroCat === 'todos' || c.categoria === filtroCat);
  }

  function renderComparativa(ciudadKey) {
    const box = document.getElementById('comparativa-container');
    if (!box) return;

    const ciudad = CIUDADES[ciudadKey];
    if (!ciudad) {
      box.innerHTML = `<div class="error-state">Ciudad no configurada.</div>`;
      return;
    }

    let lista = flatComplejos().filter(c => ciudad.complejos.includes(c.id));
    if (filtroCat !== 'todos') lista = lista.filter(c => c.categoria === filtroCat);

    if (!lista.length) {
      box.innerHTML = `
        <div class="empty-state-box">
          <p>No hay datos suficientes con estos filtros.</p>
          <p style="font-size:.85rem">Prueba cambiar a <strong>Solo boleto</strong> o quitar el filtro de categoría.</p>
          <button type="button" class="filter-btn" id="btn-solo-boleto">Usar Solo boleto</button>
        </div>`;
      document.getElementById('btn-solo-boleto')?.addEventListener('click', () => {
        const sel = document.getElementById('filtro-modo');
        if (sel) { sel.value = 'boleto'; filtroModo = 'boleto'; }
        refreshTools();
      });
      return;
    }

    if (ciudad.modo === 'cadenas') {
      const porCadena = {};
      lista.forEach(c => {
        const prom = scoreConPreset(c);
        if (prom == null) return;
        if (!porCadena[c.cadena] || prom < porCadena[c.cadena].prom) {
          porCadena[c.cadena] = { complejo: c, prom };
        }
      });
      const filas = Object.values(porCadena).sort((a, b) => a.prom - b.prom);

      if (!filas.length) {
        box.innerHTML = `
          <div class="empty-state-box">
            <p>No hay datos suficientes con estos filtros.</p>
            <p style="font-size:.85rem">Prueba cambiar a <strong>Solo boleto</strong>.</p>
            <button type="button" class="filter-btn" id="btn-solo-boleto">Usar Solo boleto</button>
          </div>`;
        document.getElementById('btn-solo-boleto')?.addEventListener('click', () => {
          const sel = document.getElementById('filtro-modo');
          if (sel) { sel.value = 'boleto'; filtroModo = 'boleto'; }
          refreshTools();
        });
        return;
      }

      const mejor = filas[0];
      box.innerHTML = `
        <div class="comp-winner">
          En León, la cadena más barata es
          <strong>${escapeHTML(mejor.complejo.nombreCadena)}</strong>
          con <strong>$${mejor.prom}</strong> en ${escapeHTML(mejor.complejo.nombre)}.
        </div>
        <div class="table-block">
          <h3><span>★</span> Mejor complejo por cadena</h3>
          <div class="table-scroll">
            <table class="precios-table">
              <thead><tr><th>Cadena</th><th>Mejor complejo</th><th>Total</th></tr></thead>
              <tbody>
                ${filas.map((f, i) => `
                  <tr class="${i === 0 ? 'mejor' : ''}">
                    <td>${escapeHTML(f.complejo.nombreCadena)}</td>
                    <td>${escapeHTML(f.complejo.nombre)}</td>
                    <td class="precio">$${f.prom}</td>
                  </tr>`).join('')}
              </tbody>
            </table>
          </div>
        </div>
        <div style="text-align:center;padding:12px">
          <button type="button" class="btn-export" id="btn-export-comp">Exportar imagen</button>
        </div>`;
      document.getElementById('btn-export-comp')?.addEventListener('click', exportarComparativa);
      return;
    }

    const diasLabel = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
    const ranked = lista
      .map(c => ({ c, prom: scoreConPreset(c) }))
      .filter(x => x.prom != null)
      .sort((a, b) => a.prom - b.prom);
    const ganador = ranked[0];

    const filas = lista.map(c => {
      const pal = getSnackGrande(c, /palomitas/i);
      const ref = getSnackGrande(c, /refresco/i);
      const prom = scoreConPreset(c);
      const esMejor = ganador && c.id === ganador.c.id;
      return `
        <tr class="${esMejor ? 'mejor' : ''}">
          <td>
            <strong>${escapeHTML(c.nombre)}</strong><br>
            <span style="color:var(--muted-2);font-size:.72rem;">
              ${escapeHTML(c.nombreCadena)}${c.categoria === 'premium' ? ' · VIP/Platino' : ''}
            </span>
          </td>
          <td class="precio">${fmt(precioBoleto(c, 'viernes', { club: usarClub }))}</td>
          <td class="precio">${fmt(pal)}</td>
          <td class="precio">${fmt(ref)}</td>
          ${DIAS.map(d => {
            const t = costoPersona(c, d);
            return `<td class="${t == null ? 'na' : 'precio'}">${t == null ? '—' : '$' + t}</td>`;
          }).join('')}
          <td class="precio"><strong>${prom == null ? '—' : '$' + prom}</strong></td>
        </tr>`;
    }).join('');

    box.innerHTML = `
      ${ganador ? `
        <div class="comp-winner">
          En <strong>${escapeHTML(ciudad.nombre)}</strong> conviene más
          <strong>${escapeHTML(ganador.c.nombreCadena)} · ${escapeHTML(ganador.c.nombre)}</strong>
          con <strong>$${ganador.prom}</strong>.
        </div>` : ''}
      <div class="table-block">
        <h3><span>★</span> Comparativa de gasto adulto</h3>
        <div class="table-scroll">
          <table class="precios-table">
            <thead>
              <tr>
                <th>Complejo</th>
                <th>Boleto*</th>
                <th>Pal G</th>
                <th>Ref G</th>
                ${diasLabel.map(d => `<th>${d}</th>`).join('')}
                <th>Score</th>
              </tr>
            </thead>
            <tbody>${filas}</tbody>
          </table>
        </div>
        <p style="padding:12px 18px;color:var(--muted);font-size:.78rem;margin:0;">
          * Viernes (o membresía). Si ves muchos “—”, cambia el modo a “Solo boleto”.
        </p>
      </div>
      <div style="text-align:center;padding:12px">
        <button type="button" class="btn-export" id="btn-export-comp">Exportar imagen</button>
      </div>`;

    document.getElementById('btn-export-comp')?.addEventListener('click', exportarComparativa);
  }

  function renderTipYTop3() {
    const tip = document.getElementById('tip-mejor-dia');
    const top3 = document.getElementById('top3-box');
    if (!tip || !top3) return;

    const ciudadKey = document.getElementById('sim-ciudad')?.value || ciudadActiva || 'sfr';
    const baseKey = ciudadKey === 'leon-cadenas' ? 'leon' : ciudadKey;
    const ids = CIUDADES[baseKey]?.complejos || CIUDADES.sfr.complejos;

    const lista = filtrarLista(ids)
      .map(c => ({ c, score: scoreConPreset(c), best: mejorDiaDe(c) }))
      .filter(x => x.score != null)
      .sort((a, b) => a.score - b.score);

    if (!lista.length) {
      tip.innerHTML = '';
      top3.innerHTML = `
        <div class="empty-state-box">
          <p>No hay datos suficientes con estos filtros.</p>
          <p style="font-size:.85rem">Prueba cambiar a <strong>Solo boleto</strong> o quitar el filtro de categoría.</p>
          <button type="button" class="filter-btn" id="btn-solo-boleto">Usar Solo boleto</button>
        </div>`;
      document.getElementById('btn-solo-boleto')?.addEventListener('click', () => {
        const sel = document.getElementById('filtro-modo');
        if (sel) {
          sel.value = 'boleto';
          filtroModo = 'boleto';
        }
        refreshTools();
      });
      return;
    }

    const scores = lista.map(x => x.score);
    const promedio = Math.round(scores.reduce((a, b) => a + b, 0) / scores.length);

    const g = lista[0];
    tip.innerHTML = g.best
      ? `Conviene <strong>${escapeHTML(g.c.nombreCadena)} · ${escapeHTML(g.c.nombre)}</strong>
         (${fmt(g.score)}). Mejor día: <strong>${DIAS_LABEL[g.best.d]}</strong> (${fmt(g.best.v)}).`
      : '';

    top3.innerHTML = `
      <h3><span>★</span> Top 3 más baratos · ${escapeHTML(CIUDADES[baseKey].nombre)}</h3>
      <div class="table-scroll">
        <table class="precios-table">
          <thead><tr><th>#</th><th>Complejo</th><th>Total</th><th>Mejor día</th></tr></thead>
          <tbody>
            ${lista.slice(0, 3).map((x, i) => {
              const diff = x.score - promedio;
              const diffHtml = diff < 0
                ? `<span class="diff-barato">${diff} vs prom.</span>`
                : diff > 0
                  ? `<span class="diff-caro">+${diff} vs prom.</span>`
                  : '';

              return `
                <tr class="${i === 0 ? 'rank-1' : ''}">
                  <td>${i + 1}</td>
                  <td>
                    <strong>${escapeHTML(x.c.nombre)}</strong><br>
                    <span style="color:var(--muted-2);font-size:.72rem">
                      ${escapeHTML(x.c.nombreCadena)}${x.c.zona ? ' · ' + escapeHTML(x.c.zona) : ''}
                    </span>
                  </td>
                  <td class="precio">${fmt(x.score)} ${diffHtml}</td>
                  <td>${x.best ? DIAS_LABEL[x.best.d] + ' · ' + fmt(x.best.v) : '—'}</td>
                </tr>`;
            }).join('')}
          </tbody>
        </table>
      </div>`;
  }

  // ========== SIMULADOR MEJORADO ==========
  function renderSimulador() {
    const box = document.getElementById('sim-resultados');
    if (!box) return;

    const ciudadKey = document.getElementById('sim-ciudad')?.value || 'sfr';
    const adultos = Number(document.getElementById('sim-adultos')?.value || 0);
    const ninos = Number(document.getElementById('sim-ninos')?.value || 0);
    const palQty = Number(document.getElementById('sim-pal-qty')?.value || 0);
    const palOpt = document.getElementById('sim-pal')?.value || '0';
    const nRef = Number(document.getElementById('sim-ref')?.value || 0);
    const nAgua = Number(document.getElementById('sim-agua')?.value || 0);
    const simDiaVal = document.getElementById('sim-dia')?.value || 'viernes';
    const dia = simDiaVal === 'promedio' ? 'viernes' : simDiaVal;
    const club = document.getElementById('sim-usar-club')?.checked || false;

    const lista = filtrarLista(CIUDADES[ciudadKey]?.complejos || []);

    const resultados = lista.map(c => {
      let total = 0;
      let ok = true;
      let desglose = { boletos: 0, palomitas: 0, refrescos: 0, aguas: 0 };

      for (let i = 0; i < adultos; i++) {
        const b = precioBoleto(c, dia, { club });
        if (b == null) { ok = false; break; }
        total += b;
        desglose.boletos += b;
      }
      for (let i = 0; i < ninos; i++) {
        const b = precioBoleto(c, dia, { nino: true }) ?? precioBoleto(c, dia, {});
        if (b == null) { ok = false; break; }
        total += b;
        desglose.boletos += b;
      }

      if (palOpt !== '0' && palQty > 0) {
        const p = snackSize(c, /palomitas/i, palOpt) ?? snackSize(c, /palomitas/i, 'grande');
        if (p == null) ok = false;
        else {
          total += p * palQty;
          desglose.palomitas = p * palQty;
        }
      }

      for (let i = 0; i < nRef; i++) {
        const r = snackSize(c, /refresco/i, 'grande');
        if (r == null) ok = false;
        else {
          total += r;
          desglose.refrescos += r;
        }
      }
      for (let i = 0; i < nAgua; i++) {
        const a = snackSize(c, /agua/i, 'grande') ?? snackSize(c, /agua/i, 'chica');
        if (a == null) ok = false;
        else {
          total += a;
          desglose.aguas += a;
        }
      }

      return { c, total: ok ? total : null, desglose };
    }).filter(x => x.total != null).sort((a, b) => a.total - b.total);

    if (!resultados.length) {
      box.innerHTML = `
        <div class="empty-state-box">
          <p>No hay datos suficientes con estos filtros.</p>
          <p style="font-size:.85rem">Prueba otra ciudad o quita el filtro de categoría en “Filtros avanzados”.</p>
        </div>`;
      return;
    }

    const winner = resultados[0];
    const second = resultados[1];
    const ahorro = second ? second.total - winner.total : 0;
    const diaTxt = DIAS_LABEL[dia] || dia;

    const personasTxt = [
      adultos > 0 ? `${adultos} adulto${adultos > 1 ? 's' : ''}` : '',
      ninos > 0 ? `${ninos} niño${ninos > 1 ? 's' : ''}` : ''
    ].filter(Boolean).join(' + ');

    let snacksTxt = [];
    if (palOpt !== '0' && palQty > 0) snacksTxt.push(`${palQty}× palomitas ${palOpt}`);
    if (nRef > 0) snacksTxt.push(`${nRef}× refresco G`);
    if (nAgua > 0) snacksTxt.push(`${nAgua}× agua`);

    box.innerHTML = `
      <div class="sim-winner-card">
        <div class="label">🏆 Mejor opción · ${escapeHTML(diaTxt)}</div>
        <div class="nombre">${escapeHTML(winner.c.nombreCadena)} · ${escapeHTML(winner.c.nombre)}</div>
        <div class="total">${fmt(winner.total)}</div>
        ${ahorro > 0 ? `<div class="ahorro">Ahorras ${fmt(ahorro)} vs el segundo más barato</div>` : ''}
        <div class="desglose">
          <div><span>Boletos (${personasTxt || '0'})</span><span>${fmt(winner.desglose.boletos)}</span></div>
          ${winner.desglose.palomitas ? `<div><span>Palomitas</span><span>${fmt(winner.desglose.palomitas)}</span></div>` : ''}
          ${winner.desglose.refrescos ? `<div><span>Refrescos</span><span>${fmt(winner.desglose.refrescos)}</span></div>` : ''}
          ${winner.desglose.aguas ? `<div><span>Aguas</span><span>${fmt(winner.desglose.aguas)}</span></div>` : ''}
        </div>
        <div class="sim-actions">
          <button type="button" class="btn-copy" id="btn-copy-sim">Copiar resumen</button>
          <button type="button" class="btn-ver-detalle" id="btn-ver-detalle" data-id="${escapeHTML(winner.c.id)}" data-cadena="${escapeHTML(winner.c.cadena)}">Ver precios detallados</button>
        </div>
        <div class="copy-feedback" id="copy-feedback"></div>
      </div>

      <div class="table-scroll">
        <table class="precios-table">
          <thead>
            <tr><th>#</th><th>Complejo</th><th>Total (${escapeHTML(diaTxt)})</th></tr>
          </thead>
          <tbody>
            ${resultados.slice(0, 8).map((x, i) => `
              <tr class="${i === 0 ? 'mejor' : ''}">
                <td>${i + 1}</td>
                <td>${escapeHTML(x.c.nombreCadena)} · ${escapeHTML(x.c.nombre)}</td>
                <td class="precio"><strong>${fmt(x.total)}</strong></td>
              </tr>`).join('')}
          </tbody>
        </table>
      </div>
    `;

    document.getElementById('btn-copy-sim')?.addEventListener('click', () => {
      const txt = `Salida cine (${personasTxt}${snacksTxt.length ? ' + ' + snacksTxt.join(', ') : ''})\n` +
        `${winner.c.nombreCadena} · ${winner.c.nombre}: ${fmt(winner.total)} (${diaTxt})` +
        (ahorro > 0 ? `\nAhorro vs 2º: ${fmt(ahorro)}` : '');
      navigator.clipboard?.writeText(txt).then(() => {
        const fb = document.getElementById('copy-feedback');
        if (fb) {
          fb.textContent = '¡Copiado!';
          setTimeout(() => fb.textContent = '', 2000);
        }
      }).catch(() => {
        const fb = document.getElementById('copy-feedback');
        if (fb) fb.textContent = 'No se pudo copiar';
      });
    });

    document.getElementById('btn-ver-detalle')?.addEventListener('click', (e) => {
      const id = e.currentTarget.dataset.id;
      const cadena = e.currentTarget.dataset.cadena;
      const tab = document.querySelector(`.cine-tab[data-cadena="${cadena}"]`);
      if (tab) {
        document.querySelectorAll('.cine-tab').forEach(b => b.classList.remove('active'));
        tab.classList.add('active');
        cadenaActiva = cadena;
        cargarComplejos();
        if (selectComplejo) {
          selectComplejo.value = id;
          complejoActivo = id;
        }
        render();
        document.getElementById('precios-container')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  }

  function llenarVersusSelects() {
    const a = document.getElementById('vs-a');
    const b = document.getElementById('vs-b');
    if (!a || !b) return;
    const opts = flatComplejos().map(c =>
      `<option value="${escapeHTML(c.id)}">${escapeHTML(c.nombreCadena)} · ${escapeHTML(c.nombre)}</option>`
    ).join('');
    a.innerHTML = opts;
    b.innerHTML = opts;
    a.value = 'san-francisco';
    b.value = 'movie-center';
  }

  function renderVersus() {
    const box = document.getElementById('vs-result');
    const idA = document.getElementById('vs-a')?.value;
    const idB = document.getElementById('vs-b')?.value;
    if (!box || !idA || !idB) return;

    const all = flatComplejos();
    const A = all.find(c => c.id === idA);
    const B = all.find(c => c.id === idB);
    if (!A || !B) return;

    const sA = scoreComplejo(A);
    const sB = scoreComplejo(B);
    const diaRef = filtroDia === 'promedio' ? 'viernes' : filtroDia;
    const bA = precioBoleto(A, diaRef, { club: usarClub });
    const bB = precioBoleto(B, diaRef, { club: usarClub });
    const pA = snackSize(A, /palomitas/i, 'grande');
    const pB = snackSize(B, /palomitas/i, 'grande');
    const rA = snackSize(A, /refresco/i, 'grande');
    const rB = snackSize(B, /refresco/i, 'grande');
    const diff = sA != null && sB != null ? Math.abs(sA - sB) : null;
    const gana = sA != null && sB != null ? (sA <= sB ? A : B) : null;

    box.innerHTML = `
      <div class="table-scroll">
        <table class="precios-table">
          <thead>
            <tr>
              <th>Concepto</th>
              <th>${escapeHTML(A.nombre)}</th>
              <th>${escapeHTML(B.nombre)}</th>
            </tr>
          </thead>
          <tbody>
            <tr><td>Boleto</td><td class="precio">${fmt(bA)}</td><td class="precio">${fmt(bB)}</td></tr>
            <tr><td>Palomitas G</td><td class="precio">${fmt(pA)}</td><td class="precio">${fmt(pB)}</td></tr>
            <tr><td>Refresco G</td><td class="precio">${fmt(rA)}</td><td class="precio">${fmt(rB)}</td></tr>
            <tr class="mejor">
              <td><strong>Total</strong></td>
              <td class="precio"><strong>${fmt(sA)}</strong></td>
              <td class="precio"><strong>${fmt(sB)}</strong></td>
            </tr>
          </tbody>
        </table>
      </div>
      ${gana ? `<p class="tip-box" style="margin:12px 16px">
        Gana <strong>${escapeHTML(gana.nombreCadena)} · ${escapeHTML(gana.nombre)}</strong>
        ${diff != null ? `(ahorro ${fmt(diff)})` : ''}.
      </p>` : ''}`;
  }

  function simSet(adultos, ninos, palQty, palSize, ref, agua) {
    const set = (id, val) => {
      const el = document.getElementById(id);
      if (el) el.value = val;
    };
    set('sim-adultos', adultos);
    set('sim-ninos', ninos);
    set('sim-pal-qty', palQty);
    set('sim-pal', palSize);
    set('sim-ref', ref);
    set('sim-agua', agua);
  }

  function bindQtyButtons() {
    document.querySelectorAll('.qty-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.dataset.target;
        const delta = Number(btn.dataset.delta);
        const input = document.getElementById(id);
        if (!input) return;
        let val = Number(input.value || 0) + delta;
        const min = Number(input.min || 0);
        const max = Number(input.max || 99);
        val = Math.max(min, Math.min(max, val));
        input.value = val;

        if (id === 'sim-pal-qty' && val === 0) {
          const sel = document.getElementById('sim-pal');
          if (sel) sel.value = '0';
        }
        if (id === 'sim-pal-qty' && val > 0) {
          const sel = document.getElementById('sim-pal');
          if (sel && sel.value === '0') sel.value = 'grande';
        }
        renderSimulador();
      });
    });
  }

  function bindPresets() {
    const box = document.getElementById('preset-btns');
    if (!box) return;
    box.addEventListener('click', (e) => {
      const btn = e.target.closest('.preset-btn');
      if (!btn) return;
      box.querySelectorAll('.preset-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const p = btn.dataset.preset;
      if (p === 'solo') simSet(1, 0, 1, 'grande', 1, 0);
      else if (p === 'pareja') simSet(2, 0, 1, 'jumbo', 2, 0);
      else if (p === 'familia') simSet(2, 2, 2, 'jumbo', 3, 1);
      else if (p === 'grupo') simSet(4, 0, 2, 'jumbo', 4, 0);
      renderSimulador();
    });
  }

  function bindHerramientas() {
    const on = (id, fn) => {
      const el = document.getElementById(id);
      if (el) el.addEventListener('change', fn);
    };

    on('filtro-dia', e => { filtroDia = e.target.value; refreshTools(); });
    on('filtro-modo', e => { filtroModo = e.target.value; refreshTools(); });
    on('filtro-cat', e => { filtroCat = e.target.value; refreshTools(); });
    on('usar-club', e => { usarClub = e.target.checked; refreshTools(); });

    on('sim-ciudad', () => renderSimulador());
    on('sim-dia', () => renderSimulador());
    on('sim-adultos', () => renderSimulador());
    on('sim-ninos', () => renderSimulador());
    on('sim-pal', () => {
      const qty = document.getElementById('sim-pal-qty');
      const sel = document.getElementById('sim-pal');
      if (sel?.value === '0' && qty) qty.value = 0;
      if (sel?.value !== '0' && qty && Number(qty.value) === 0) qty.value = 1;
      renderSimulador();
    });
    on('sim-pal-qty', () => renderSimulador());
    on('sim-ref', () => renderSimulador());
    on('sim-agua', () => renderSimulador());
    on('sim-usar-club', () => renderSimulador());

    on('vs-a', () => renderVersus());
    on('vs-b', () => renderVersus());
  }

  function refreshTools() {
    renderTipYTop3();
    renderSimulador();
    renderVersus();
    renderComparativa(ciudadActiva);
    renderResumenRapido();
  }

  function actualizarNotaFecha() {
    const el = document.getElementById('precios-actualizado');
    if (!el) return;

    if (!metaActualizado) {
      el.textContent = 'Precios sujetos a cambio según sucursal y promociones.';
      el.classList.remove('viejo');
      return;
    }

    el.textContent = `Precios actualizados: ${metaActualizado}. Sujetos a cambio por sucursal y promociones.`;

    const m = String(metaActualizado).match(/(\d{4})-(\d{2})-(\d{2})/);
    if (m) {
      const fecha = new Date(`${m[1]}-${m[2]}-${m[3]}T12:00:00`);
      const dias = Math.floor((Date.now() - fecha.getTime()) / 86400000);
      if (dias > 14) {
        el.classList.add('viejo');
        el.textContent += ` ⚠️ Hace ${dias} días — pueden estar desactualizados.`;
      } else {
        el.classList.remove('viejo');
      }
    }
  }

  function fmt(valor) {
    if (valor == null || valor === '') return '—';
    return `$${Number(valor)}`;
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

  function tieneDulcero(complejo) {
    const snacks = complejo.snacks || [];
    const pal = snacks.find(s => /palomitas/i.test(s.nombre || ''));
    const ref = snacks.find(s => /refresco/i.test(s.nombre || ''));
    return (pal?.grande != null) && (ref?.grande != null);
  }

  function renderPromos() {
    const box = document.getElementById('promos-list');
    if (!box) return;

    if (!promos.length) {
      box.innerHTML = `<p style="text-align:center;color:var(--muted);font-size:.85rem">No hay promos cargadas.</p>`;
      return;
    }

    box.innerHTML = promos.map(p => `
      <div class="promo-card">
        <strong>${escapeHTML(p.titulo || 'Promo')}</strong>
        <div>${escapeHTML(p.detalle || '')}</div>
        ${p.vigencia ? `<small>${escapeHTML(p.vigencia)}</small>` : ''}
      </div>
    `).join('');
  }

  function diaDeHoy() {
    const map = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
    return map[new Date().getDay()];
  }

  function marcarDiaHoyEnSelect() {
    const sel = document.getElementById('filtro-dia');
    if (!sel) return;
    const hoy = diaDeHoy();
    [...sel.options].forEach(opt => {
      if (opt.value === 'promedio') {
        opt.textContent = 'Promedio semanal';
        return;
      }
      const labels = {
        lunes: 'Lunes', martes: 'Martes', miercoles: 'Miércoles',
        jueves: 'Jueves', viernes: 'Viernes', sabado: 'Sábado', domingo: 'Domingo'
      };
      opt.textContent = (labels[opt.value] || opt.value) + (opt.value === hoy ? ' · hoy' : '');
    });
  }

  function marcarDiaHoyEnSimSelect() {
    const sel = document.getElementById('sim-dia');
    if (!sel) return;
    const hoy = diaDeHoy();
    [...sel.options].forEach(opt => {
      if (opt.value === 'promedio') {
        opt.textContent = 'Promedio semanal';
        return;
      }
      const labels = {
        lunes: 'Lunes', martes: 'Martes', miercoles: 'Miércoles',
        jueves: 'Jueves', viernes: 'Viernes', sabado: 'Sábado', domingo: 'Domingo'
      };
      opt.textContent = (labels[opt.value] || opt.value) + (opt.value === hoy ? ' · hoy' : '');
    });
  }
});

// ===== Menú móvil =====
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

// ===== Toggle tema =====
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
document.addEventListener('DOMContentLoaded', () => {
  const btnTop = document.getElementById('btn-top');
  if (!btnTop) return;

  function toggleBtnTop() {
    if (window.scrollY > 400) btnTop.classList.add('is-visible');
    else btnTop.classList.remove('is-visible');
  }

  window.addEventListener('scroll', toggleBtnTop, { passive: true });
  toggleBtnTop();

  btnTop.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
});

async function exportarComparativa() {
  const target = document.getElementById('comparativa-container');
  if (!target || typeof html2canvas === 'undefined') {
    alert('No se pudo exportar. Recarga la página.');
    return;
  }
  const canvas = await html2canvas(target, {
    backgroundColor: document.documentElement.getAttribute('data-theme') === 'light' ? '#f4f4f6' : '#121218',
    scale: 2
  });
  const link = document.createElement('a');
  link.download = `comparativa-precios-${Date.now()}.png`;
  link.href = canvas.toDataURL('image/png');
  link.click();
}
