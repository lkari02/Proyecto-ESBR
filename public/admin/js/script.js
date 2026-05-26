tailwind.config = {
  darkMode: 'class',
  theme: {
    extend: {
      fontFamily: {
        display: ['Barlow Condensed', 'sans-serif'],
        body: ['Barlow', 'sans-serif'],
        mono: ['IBM Plex Mono', 'monospace'],
      },
      colors: {
        steel: {
          50:  '#f4f6f9', 100: '#e8ecf2', 200: '#cdd5e0', 300: '#a8b7ca', 
          400: '#7e94ad', 500: '#5f7894', 600: '#4a6080', 700: '#3c4f69', 
          800: '#344358', 900: '#1e2a38', 950: '#111824',
        },
        ember: { 400: '#f97316', 500: '#ea6a0a', 600: '#c2540a' },
        cobalt: { 400: '#3b82f6', 500: '#2563eb', 600: '#1d4ed8' }
      }
    }
  }
}

/* ---- DATA ---- */
const PIEZAS = [
  { sku:'BP-GRU-001', nombre:'Sello mecánico carbono/cerámica', marca:'Grundfos', modelos:['cm-3-5','sp-5a-18'], precio:1250.00, stock:34 },
  { sku:'BP-GRU-002', nombre:'Impulsor 5 etapas SS304', marca:'Grundfos', modelos:['cm-3-5'], precio:4800.00, stock:8 },
  { sku:'BP-GRU-003', nombre:'Motor eléctrico 1.5 HP 220V', marca:'Grundfos', modelos:['sp-5a-18'], precio:12400.00, stock:3 },
  { sku:'BP-XYL-001', nombre:'Rodamiento de bolas 6205-2RS', marca:'Xylem', modelos:['lcc-100-250'], precio:320.00, stock:0 },
  { sku:'BP-XYL-002', nombre:'Eje de acero inoxidable 316', marca:'Xylem', modelos:['lcc-100-250'], precio:2750.00, stock:15 },
  { sku:'BP-XYL-003', nombre:'Junta tórica NBR 90A', marca:'Xylem', modelos:['lcc-100-250','f-32-160a'], precio:85.00, stock:120 },
  { sku:'BP-PED-001', nombre:'Difusor PP reforzado fibra de vidrio', marca:'Pedrollo', modelos:['f-32-160a'], precio:1900.00, stock:7 },
  { sku:'BP-PED-002', nombre:'Carcasa bomba fundición gris', marca:'Pedrollo', modelos:['f-32-160a'], precio:5600.00, stock:4 },
  { sku:'BP-PED-003', nombre:'Conjunto eje-impulsor DN50', marca:'Pedrollo', modelos:['f-32-160a'], precio:3200.00, stock:0 },
  { sku:'BP-PNT-001', nombre:'Capacitor arranque 25µF/450V', marca:'Pentax', modelos:['cam-80-00'], precio:145.00, stock:42 },
  { sku:'BP-PNT-002', nombre:'Flotador de nivel acero inox', marca:'Pentax', modelos:['cam-80-00'], precio:680.00, stock:18 },
  { sku:'BP-GRU-004', nombre:'Rotor magnético NdFeB N42', marca:'Grundfos', modelos:['cm-3-5','sp-5a-18'], precio:890.00, stock:6 },
  { sku:'BP-XYL-004', nombre:'Válvula de retención 2"', marca:'Xylem', modelos:['lcc-100-250'], precio:1100.00, stock:22 },
  { sku:'BP-PED-004', nombre:'Kit empaque completo EPDM', marca:'Pedrollo', modelos:['f-32-160a'], precio:420.00, stock:55 },
];

const MODEL_LABELS = {
  'cm-3-5':'CM 3-5','sp-5a-18':'SP 5A-18','lcc-100-250':'LCC 100-250',
  'f-32-160a':'F 32/160A','cam-80-00':'CAM 80/00'
};

/* ---- THEME ---- */
function toggleTheme() {
  const html = document.documentElement;
  const isDark = html.classList.toggle('dark');
  document.getElementById('iconSun').style.display  = isDark ? 'block' : 'none';
  document.getElementById('iconMoon').style.display = isDark ? 'none'  : 'block';
  localStorage.setItem('bp-theme', isDark ? 'dark' : 'light');
}
(function initTheme() {
  const saved = localStorage.getItem('bp-theme');
  if (saved === 'dark') {
    document.documentElement.classList.add('dark');
    document.getElementById('iconSun').style.display  = 'block';
    document.getElementById('iconMoon').style.display = 'none';
  }
})();

/* ---- TABLE ---- */
let currentPage = 1;
const PER_PAGE = 8;

function getFiltered() {
  const q = (document.getElementById('searchInput')?.value || '').toLowerCase().trim();
  const modelo = document.getElementById('modeloFilter')?.value || '';
  const stock  = document.getElementById('stockFilter')?.value || '';
  return PIEZAS.filter(p => {
    if (q && !p.nombre.toLowerCase().includes(q) && !p.sku.toLowerCase().includes(q)) return false;
    if (modelo && !p.modelos.includes(modelo)) return false;
    if (stock === 'ok'  && p.stock === 0) return false;
    if (stock === 'low' && (p.stock === 0 || p.stock >= 10)) return false;
    if (stock === 'out' && p.stock > 0) return false;
    return true;
  });
}

function stockClass(s) {
  if (s === 0)   return 'stock-out';
  if (s < 10)    return 'stock-low';
  return 'stock-ok';
}
function stockLabel(s) {
  if (s === 0) return '✕ AGOTADO';
  return s;
}

function renderTable() { currentPage = 1; renderPage(); }

function renderPage() {
  const filtered = getFiltered();
  const total    = filtered.length;
  const start    = (currentPage - 1) * PER_PAGE;
  const page     = filtered.slice(start, start + PER_PAGE);
  const tbody    = document.getElementById('tableBody');

  if (tbody) {
    if (!page.length) {
      tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:36px;color:var(--text-muted);font-size:14px;font-family:\'Barlow Condensed\',sans-serif;letter-spacing:0.5px;">No se encontraron piezas con los filtros aplicados.</td></tr>';
    } else {
      tbody.innerHTML = page.map(p => `
        <tr>
          <td><span class="sku-cell">${p.sku}</span></td>
          <td style="font-weight:500;">${p.nombre}</td>
          <td><span class="badge badge-brand">${p.marca}</span></td>
          <td><div style="display:flex;flex-wrap:wrap;gap:3px;">${p.modelos.map(m=>`<span class="badge badge-model">${MODEL_LABELS[m]||m}</span>`).join('')}</div></td>
          <td><span class="price-cell">$${p.precio.toLocaleString('es-MX',{minimumFractionDigits:2})}</span></td>
          <td><span class="${stockClass(p.stock)}">${stockLabel(p.stock)}</span></td>
          <td style="text-align:center;">
            <div style="display:flex;gap:5px;justify-content:center;">
          <button class="act-btn edit" title="Editar" onclick="abrirModalEditar(${p.id})">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          </button>
          <button class="act-btn del" title="Eliminar" onclick="eliminarPieza(${p.id})">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg>
          </button>   
            </div>
          </td>
        </tr>
      `).join('');
    }
  }

  // 🛡️ ESCUDOS PROTECTORES PARA PAGINACIÓN Y ESTADÍSTICAS
  const pagInfo = document.getElementById('pagInfo');
  if (pagInfo) {
    const from = total === 0 ? 0 : start + 1;
    const to   = Math.min(start + PER_PAGE, total);
    pagInfo.textContent = `Mostrando ${from}–${to} de ${total} resultado${total !== 1 ? 's' : ''}`;
  }

  const pagBtns = document.getElementById('pagBtns');
  if (pagBtns) {
    const totalPages = Math.max(1, Math.ceil(total / PER_PAGE));
    pagBtns.innerHTML = '';
    const mkBtn = (label, pg, active) => {
      const b = document.createElement('button');
      b.className = 'pag-btn' + (active ? ' active' : '');
      b.textContent = label;
      if (!active) b.onclick = () => { currentPage = pg; renderPage(); };
      return b;
    };
    if (currentPage > 1) pagBtns.appendChild(mkBtn('‹', currentPage - 1, false));
    for (let i = 1; i <= totalPages; i++) pagBtns.appendChild(mkBtn(String(i), i, i === currentPage));
    if (currentPage < totalPages) pagBtns.appendChild(mkBtn('›', currentPage + 1, false));
  }

  const stTotal = document.getElementById('stTotal');
  if (stTotal) {
    stTotal.textContent = PIEZAS.length;
    document.getElementById('stStock').textContent = PIEZAS.filter(p => p.stock > 0).length;
    document.getElementById('stLow').textContent   = PIEZAS.filter(p => p.stock > 0 && p.stock < 10).length;
    document.getElementById('stOut').textContent   = PIEZAS.filter(p => p.stock === 0).length;
  }
}

function clearFilters() {
  if (document.getElementById('searchInput')) document.getElementById('searchInput').value  = '';
  if (document.getElementById('modeloFilter')) document.getElementById('modeloFilter').value = '';
  if (document.getElementById('stockFilter')) document.getElementById('stockFilter').value  = '';
  renderTable();
}

/* ---- MODAL ---- */
function openModal()  { 
  const modal = document.getElementById('mainModal');
  if (modal) modal.classList.add('open'); 
}
function closeModal() {
  const modal = document.getElementById('mainModal');
  if (modal) modal.classList.remove('open');
  resetImageState();
}

/* ---- LANG TABS ---- */
function switchLangTab(lang) {
  ['es','en'].forEach(l => {
    const tab = document.getElementById('tab-' + l);
    const panel = document.getElementById('panel-' + l);
    if (tab && panel) {
        tab.classList.toggle('active', l === lang);
        panel.classList.toggle('active', l === lang);
    }
  });
}

function updateLangStatus(lang) {
  const nombreEl = document.getElementById('fNombre_' + lang);
  const descEl   = document.getElementById('fDesc_'   + lang);
  if (!nombreEl || !descEl) return;

  const nombre = (nombreEl.value || '').trim();
  const desc   = (descEl.value || '').trim();
  const filled = (nombre ? 1 : 0) + (desc ? 1 : 0);

  setCharCount('cc-nombre-' + lang, nombre.length,  200);
  setCharCount('cc-desc-'   + lang, desc.length, 1000);

  const dot = document.getElementById('status-' + lang);
  if (dot) {
      dot.className = 'lang-status';
      if (filled === 2)      dot.classList.add('filled');
      else if (filled === 1) dot.classList.add('partial');
  }

  const badge = document.getElementById('badge-' + lang);
  if (badge) {
      badge.textContent = filled + '/2';
      badge.classList.toggle('done', filled === 2);
  }

  if (lang === 'es') updatePreview();
}

function setCharCount(id, val, max) {
  const el = document.getElementById(id);
  if (!el) return;
  el.textContent = val + ' / ' + max;
  el.className = 'char-count';
  if (val > max * 0.9)  el.classList.add('warn');
  if (val >= max)       el.classList.add('over');
}

/* ---- QUICK PREVIEW ---- */
function updatePreview() {
  const nameInput = document.getElementById('fNombre_es');
  const skuInput = document.getElementById('fSku');
  const precioInput = document.getElementById('fPrecio');
  const stockInput = document.getElementById('fStock');
  
  if (!nameInput) return; // Si no estamos en el catálogo, ignoramos esto

  const nombre = (nameInput.value || '').trim();
  const sku    = (skuInput?.value || '').trim();
  const precio = parseFloat(precioInput?.value) || 0;
  const stock  = parseInt(stockInput?.value)    || 0;

  const nameEl = document.getElementById('prevName');
  if (nameEl) {
      if (nombre) {
        nameEl.textContent = nombre;
        nameEl.classList.remove('empty');
      } else {
        nameEl.textContent = 'Nombre de la pieza';
        nameEl.classList.add('empty');
      }
  }

  const prevSkuEl = document.getElementById('prevSku');
  if (prevSkuEl) prevSkuEl.querySelector('span').textContent = sku ? `SKU: ${sku}` : 'SKU: —';

  const prevStockEl = document.getElementById('prevStock');
  if (prevStockEl) prevStockEl.querySelector('span').textContent = `Stock: ${stock} · $${precio.toLocaleString('es-MX',{minimumFractionDigits:2})}`;
}

/* ---- IMAGE HANDLING ---- */
const MIN_IMGS = 6, MAX_IMGS = 10;
let uploadedImages = [];
let selectedThumb  = 0;

function resetImageState() {
  uploadedImages = [];
  selectedThumb  = 0;
  
  const fileInput = document.getElementById('fileInput');
  if (fileInput) fileInput.value = '';

  ['es','en'].forEach(lang => {
    const nombreEl = document.getElementById('fNombre_' + lang);
    const descEl = document.getElementById('fDesc_' + lang);
    if (nombreEl) nombreEl.value = '';
    if (descEl) descEl.value = '';
    updateLangStatus(lang);
  });
  
  switchLangTab('es');
  renderThumbs();
  updateValidation();
  updatePreviewThumb();
}

function handleFiles(files) {
  const remaining = MAX_IMGS - uploadedImages.length;
  const toAdd     = Array.from(files).slice(0, remaining);
  toAdd.forEach(file => {
    const url = URL.createObjectURL(file);
    uploadedImages.push({ url, name: file.name });
  });
  if (uploadedImages.length > 0 && selectedThumb >= uploadedImages.length) {
    selectedThumb = uploadedImages.length - 1;
  }
  renderThumbs();
  updateValidation();
  updatePreviewThumb();
  
  const fileInput = document.getElementById('fileInput');
  if (fileInput) fileInput.value = '';
}

function renderThumbs() {
  const strip   = document.getElementById('thumbsStrip');
  const addBtn  = document.getElementById('thumbAddBtn');
  if (!strip || !addBtn) return; // 🛡️ Evita error en Cotizaciones

  strip.innerHTML = '';

  uploadedImages.forEach((img, i) => {
    const wrap = document.createElement('div');
    wrap.className = 'thumb-item' + (i === selectedThumb ? ' selected' : '');
    wrap.onclick = () => { selectedThumb = i; renderThumbs(); updatePreviewThumb(); };
    const imgEl = document.createElement('img');
    imgEl.src = img.url;
    const rmBtn = document.createElement('button');
    rmBtn.className = 'thumb-remove';
    rmBtn.innerHTML = '✕';
    rmBtn.title = 'Eliminar';
    rmBtn.onclick = (e) => { e.stopPropagation(); removeImage(i); };
    wrap.appendChild(imgEl);
    wrap.appendChild(rmBtn);
    strip.appendChild(wrap);
  });

  if (uploadedImages.length < MAX_IMGS) {
    const add = addBtn.cloneNode(true);
    add.id = 'thumbAddBtn';
    add.onclick = () => document.getElementById('fileInput').click();
    strip.appendChild(add);
  }

  const counter = document.getElementById('imgCounter');
  if (counter) {
      const n = uploadedImages.length;
      counter.textContent = `${n} / ${MAX_IMGS}`;
      counter.className = 'img-counter';
      if (n === 0)          counter.classList.add('empty');
      else if (n < MIN_IMGS) counter.classList.add('warn');
      else if (n <= MAX_IMGS) counter.classList.add('ok');
  }
}

function removeImage(i) {
  URL.revokeObjectURL(uploadedImages[i].url);
  uploadedImages.splice(i, 1);
  if (selectedThumb >= uploadedImages.length) selectedThumb = Math.max(0, uploadedImages.length - 1);
  renderThumbs();
  updateValidation();
  updatePreviewThumb();
}

function updatePreviewThumb() {
  const thumb = document.getElementById('prevThumb');
  if (!thumb) return;

  if (uploadedImages.length > 0) {
    thumb.innerHTML = `<img src="${uploadedImages[selectedThumb].url}" alt="preview">`;
  } else {
    thumb.innerHTML = `<div class="no-img"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" opacity=".4"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>`;
  }
}

function updateValidation() {
  const bar = document.getElementById('validationBar');
  const msg = document.getElementById('validationMsg');
  const btn = document.getElementById('saveBtn');
  
  if (!bar || !msg || !btn) return;

  const n = uploadedImages.length;
  bar.className = 'validation-bar';
  
  if (n === 0) {
    bar.classList.add('empty');
    msg.textContent = 'Añade al menos 6 imágenes para continuar';
    btn.style.opacity = '.5'; btn.style.cursor = 'not-allowed';
  } else if (n < MIN_IMGS) {
    bar.classList.add('pending');
    msg.textContent = `Faltan ${MIN_IMGS - n} imagen${MIN_IMGS - n > 1 ? 'es' : ''} (mínimo ${MIN_IMGS})`;
    btn.style.opacity = '.5'; btn.style.cursor = 'not-allowed';
  } else if (n > MAX_IMGS) {
    bar.classList.add('over');
    msg.textContent = `Máximo ${MAX_IMGS} imágenes — elimina ${n - MAX_IMGS}`;
    btn.style.opacity = '.5'; btn.style.cursor = 'not-allowed';
  } else {
    bar.classList.add('ready');
    msg.textContent = `✓ ${n} imágenes listas — formulario completo`;
    btn.style.opacity = '1'; btn.style.cursor = 'pointer';
  }
}

/* ---- DRAG & DROP ---- */
function handleDragOver(e) {
  e.preventDefault();
  const dz = document.getElementById('dropZone');
  if(dz) dz.classList.add('drag-over');
}
function handleDragLeave(e) {
  const dz = document.getElementById('dropZone');
  if(dz) dz.classList.remove('drag-over');
}
function handleDrop(e) {
  e.preventDefault();
  const dz = document.getElementById('dropZone');
  if(dz) dz.classList.remove('drag-over');
  handleFiles(e.dataTransfer.files);
}

/* ---- SAVE ---- */
function handleSave() {
  if (uploadedImages.length < MIN_IMGS) return;
  alert('Pieza guardada exitosamente (demo).');
  closeModal();
}

/* ---- CLOSE ON BACKDROP ---- */
// 🛡️ CORRECCIÓN: El clic para cerrar va en el overlay (mainModal), no en un botón con clase
const mainModalOverlay = document.getElementById('mainModal');
if (mainModalOverlay) {
  mainModalOverlay.addEventListener('click', function(e) {
    if (e.target === this) closeModal();
  });
}

/* ---- INIT ---- */
// 🛡️ Solo arrancamos estas funciones si detectamos que estamos en el Catálogo
if (document.getElementById('tableBody')) {
  renderTable();
}
if (document.getElementById('thumbsStrip')) {
  renderThumbs();
}