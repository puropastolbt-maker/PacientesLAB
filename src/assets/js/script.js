/* ============================================================
   script.js – Gestión de Pacientes (AJAX + CRUD)
   ============================================================ */

const API_URL = '/api/pacientes.php';

/* ═══════════════════════════════════════════════════
   AJAX HELPER  – wrapper sobre XMLHttpRequest
   Devuelve una Promise con { ok, status, data }
   ═══════════════════════════════════════════════════ */
function ajaxRequest(method, url, body = null) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open(method, url, true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.onload = () => {
            let data;
            try { data = JSON.parse(xhr.responseText); }
            catch { data = { error: 'Respuesta inválida del servidor.' }; }
            resolve({ ok: xhr.status >= 200 && xhr.status < 300, status: xhr.status, data });
        };

        xhr.onerror = () => reject(new Error('Error de red. Verifique su conexión.'));
        xhr.ontimeout = () => reject(new Error('Tiempo de espera agotado.'));
        xhr.timeout = 15000;

        xhr.send(body ? JSON.stringify(body) : null);
    });
}

/* ═══════════════════════════════════════════════════
   UTILIDADES GENERALES
   ═══════════════════════════════════════════════════ */

/* Escapado HTML para evitar XSS al insertar datos en el DOM */
function escHtml(str = '') {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

/* Toast de notificación */
function showToast(msg, type = 'success') {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = `toast toast-${type} show`;
    clearTimeout(t._timer);
    t._timer = setTimeout(() => { t.className = 'toast'; }, 3500);
}

/* ── Modales ── */
function abrirModal(id) {
    const el = document.getElementById(id);
    el.classList.add('open');
    /* Foco en el primer input del modal para accesibilidad */
    const first = el.querySelector('input:not([readonly]), select, textarea');
    if (first) setTimeout(() => first.focus(), 80);
}

function cerrarModal(id) {
    document.getElementById(id).classList.remove('open');
}

/* Cerrar al clic en el overlay (fuera del box) */
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
        if (e.target === overlay) cerrarModal(overlay.id);
    });
});

/* Cerrar con tecla Escape */
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.open').forEach(m => {
            cerrarModal(m.id);
        });
    }
});

/* ── Búsqueda con Enter ── */
document.getElementById('inputBuscar').addEventListener('keydown', e => {
    if (e.key === 'Enter') cargarPacientes();
});

/* ── Búsqueda en tiempo real (mientras escribes) ── */
let timeoutBusqueda;
document.getElementById('inputBuscar').addEventListener('input', () => {
    clearTimeout(timeoutBusqueda);
    timeoutBusqueda = setTimeout(cargarPacientes, 300);
});

/* ── Helpers de formulario ── */
function setBtnLoading(btn, loading) {
    const txt = btn.querySelector('.btn-text');
    const loader = btn.querySelector('.btn-loader');
    if (txt) txt.style.display = loading ? 'none' : '';
    if (loader) loader.style.display = loading ? '' : 'none';
    btn.disabled = loading;
}

function mostrarError(el, msg) {
    el.textContent = '⚠️ ' + msg;
    el.style.display = 'block';
    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function ocultarError(el) {
    el.style.display = 'none';
    el.textContent = '';
}

/* ── Validación cliente ── */
function validarCampos({ cedula, nombre, apellido, correo }) {
    if (!cedula.trim()) return 'La cédula es obligatoria.';
    if (!nombre.trim()) return 'El nombre es obligatorio.';
    if (!apellido.trim()) return 'El apellido es obligatorio.';
    if (!correo.trim()) return 'El correo es obligatorio.';
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo))
        return 'Ingrese un correo electrónico válido.';
    return null;  // sin errores
}

/* ═══════════════════════════════════════════════════
   LISTAR / BUSCAR PACIENTES
   ═══════════════════════════════════════════════════ */
async function cargarPacientes() {
    const q = document.getElementById('inputBuscar').value.trim();
    const url = q ? `${API_URL}?buscar=${encodeURIComponent(q)}` : API_URL;
    const container = document.getElementById('sidebarPacientes');

    container.innerHTML = `
        <div class="empty-state">
            <span class="empty-icon loading-spin">⏳</span>
            <p>Cargando…</p>
        </div>`;

    try {
        const { ok, data } = await ajaxRequest('GET', url);

        if (!ok || !Array.isArray(data)) {
            container.innerHTML = `
                <div class="empty-state">
                    <span class="empty-icon">⚠️</span>
                    <p>${escHtml(data?.error || 'Error al obtener datos.')}</p>
                </div>`;
            return;
        }

        if (data.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <span class="empty-icon">🔍</span>
                    <p>No se encontraron pacientes.</p>
                </div>`;
            return;
        }

        container.innerHTML = data.map(p => `
            <div class="paciente-item" onclick="seleccionarPaciente('${escHtml(p.cedula)}','${escHtml(p.nombre)}','${escHtml(p.apellido)}','${escHtml(p.correo)}')">
                <div class="paciente-item-name">${escHtml(p.nombre)} ${escHtml(p.apellido)}</div>
                <div class="paciente-item-cedula">🆔 ${escHtml(p.cedula)}</div>
                <div class="paciente-item-correo">✉️ ${escHtml(p.correo)}</div>
                <div class="paciente-item-actions" onclick="event.stopPropagation()">
                    <button class="btn btn-primary btn-icon"
                        onclick="abrirModalEditar('${escHtml(p.cedula)}','${escHtml(p.nombre)}','${escHtml(p.apellido)}','${escHtml(p.correo)}')">
                        ✏️
                    </button>
                    <button class="btn btn-danger btn-icon"
                        onclick="abrirModalEliminar('${escHtml(p.cedula)}','${escHtml(p.nombre)} ${escHtml(p.apellido)}')">
                        🗑️
                    </button>
                </div>
            </div>`).join('');

        // Actualizar estadísticas en la topbar
        actualizarEstadisticas();

    } catch (err) {
        container.innerHTML = `
            <div class="empty-state">
                <span class="empty-icon">⚠️</span>
                <p>${escHtml(err.message || 'Error de conexión.')}</p>
            </div>`;
        console.error('[cargarPacientes]', err);
    }
}

/* ═══════════════════════════════════════════════════
   SELECCIONAR PACIENTE
   ═══════════════════════════════════════════════════ */
function seleccionarPaciente(cedula, nombre, apellido, correo) {
    // Actualizar main content
    document.getElementById('welcomeSection').style.display = 'none';
    document.getElementById('pacienteInfo').style.display = 'block';
    document.getElementById('historiaClinicaSection').style.display = 'flex';
    document.getElementById('pacienteNombre').textContent = `${nombre} ${apellido}`;
    document.getElementById('pacienteCedula').textContent = cedula;
    document.getElementById('pacienteCorreo').textContent = correo;

    // Resaltar item en sidebar
    document.querySelectorAll('.paciente-item').forEach(el => el.classList.remove('active'));
    event.currentTarget.classList.add('active');

    // Cargar historia clínica
    cargarHistoriaClinica(cedula);
}

function limpiarSeleccion() {
    document.getElementById('welcomeSection').style.display = 'block';
    document.getElementById('pacienteInfo').style.display = 'none';
    document.getElementById('historiaClinicaSection').style.display = 'none';
    document.querySelectorAll('.paciente-item').forEach(el => el.classList.remove('active'));
    
    // Limpiar sección de historia
    cedulaPacienteSeleccionado = null;
    document.getElementById('sidebarHistoria').innerHTML = `
        <div class="empty-state">
            <span class="empty-icon">📋</span>
            <p>No hay registros de historia clínica</p>
        </div>`;
    document.getElementById('btnAgregarHistoria').disabled = true;
}

/* ═══════════════════════════════════════════════════
   AGREGAR PACIENTE
   ═══════════════════════════════════════════════════ */
function abrirModalAgregar() {
    document.getElementById('formAgregar').reset();
    ocultarError(document.getElementById('agError'));
    abrirModal('modalAgregar');
}

async function submitAgregar(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitAgregar');
    const err = document.getElementById('agError');
    const form = document.getElementById('formAgregar');
    const data = Object.fromEntries(new FormData(form).entries());

    /* Validación cliente */
    const clientErr = validarCampos(data);
    if (clientErr) { mostrarError(err, clientErr); return; }

    ocultarError(err);
    setBtnLoading(btn, true);

    try {
        const { ok, data: result } = await ajaxRequest('POST', API_URL, data);

        if (ok && result.success) {
            cerrarModal('modalAgregar');
            showToast('✅ ' + (result.message || 'Paciente agregado correctamente.'));
            cargarPacientes();
            actualizarEstadisticas();
        } else {
            mostrarError(err, result.error || 'Error al agregar el paciente.');
        }
    } catch (ex) {
        mostrarError(err, ex.message || 'Error de conexión con el servidor.');
        console.error('[submitAgregar]', ex);
    } finally {
        setBtnLoading(btn, false);
    }
}

/* ═══════════════════════════════════════════════════
   EDITAR PACIENTE
   ═══════════════════════════════════════════════════ */
function abrirModalEditar(cedula, nombre, apellido, correo) {
    document.getElementById('edCedula').value = cedula;
    document.getElementById('edNombre').value = nombre;
    document.getElementById('edApellido').value = apellido;
    document.getElementById('edCorreo').value = correo;
    ocultarError(document.getElementById('edError'));
    abrirModal('modalEditar');
}

async function submitEditar(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitEditar');
    const err = document.getElementById('edError');
    const form = document.getElementById('formEditar');
    const data = Object.fromEntries(new FormData(form).entries());

    /* Validación cliente */
    const clientErr = validarCampos(data);
    if (clientErr) { mostrarError(err, clientErr); return; }

    ocultarError(err);
    setBtnLoading(btn, true);

    try {
        const { ok, data: result } = await ajaxRequest('PUT', API_URL, data);

        if (ok && result.success) {
            cerrarModal('modalEditar');
            showToast('✅ ' + (result.message || 'Paciente actualizado correctamente.'));
            cargarPacientes();
            actualizarEstadisticas();
        } else {
            mostrarError(err, result.error || 'Error al actualizar el paciente.');
        }
    } catch (ex) {
        mostrarError(err, ex.message || 'Error de conexión con el servidor.');
        console.error('[submitEditar]', ex);
    } finally {
        setBtnLoading(btn, false);
    }
}

/* ═══════════════════════════════════════════════════
   ELIMINAR PACIENTE
   ═══════════════════════════════════════════════════ */
function abrirModalEliminar(cedula, nombreCompleto) {
    document.getElementById('cedulaEliminar').value = cedula;
    document.getElementById('nombreEliminar').textContent = nombreCompleto;
    abrirModal('modalEliminar');
}

async function confirmarEliminar() {
    const cedula = document.getElementById('cedulaEliminar').value;
    const btn = document.querySelector('#modalEliminar .btn-danger');

    setBtnLoading(btn, true);

    try {
        const { ok, data: result } = await ajaxRequest('DELETE', API_URL, { cedula });

        if (ok && result.success) {
            cerrarModal('modalEliminar');
            showToast('🗑️ ' + (result.message || 'Paciente eliminado correctamente.'));
            cargarPacientes();
            actualizarEstadisticas();
            limpiarSeleccion();
        } else {
            showToast(result.error || 'Error al eliminar el paciente.', 'error');
            cerrarModal('modalEliminar');
        }
    } catch (ex) {
        showToast(ex.message || 'Error de conexión con el servidor.', 'error');
        console.error('[confirmarEliminar]', ex);
    } finally {
        setBtnLoading(btn, false);
    }
}

/* ═══════════════════════════════════════════════════
   INICIALIZACIÓN
   ═══════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    cargarPacientes();
    actualizarReloj();
    setInterval(actualizarReloj, 1000);
});

/* ═══════════════════════════════════════════════════
   TOPBAR - Reloj y Fecha
   ═══════════════════════════════════════════════════ */
function actualizarReloj() {
    const now = new Date();
    
    // Formato de fecha
    const opcionesFecha = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
    const fechaFormato = now.toLocaleDateString('es-ES', opcionesFecha)
        .split(',')[0].charAt(0).toUpperCase() + now.toLocaleDateString('es-ES', opcionesFecha).split(',')[0].slice(1);
    
    document.getElementById('currentDate').textContent = fechaFormato;
    
    // Formato de hora
    const horas = String(now.getHours()).padStart(2, '0');
    const minutos = String(now.getMinutes()).padStart(2, '0');
    const segundos = String(now.getSeconds()).padStart(2, '0');
    document.getElementById('currentTime').textContent = `${horas}:${minutos}:${segundos}`;
}

/* ─── Menú de usuario ─── */
function toggleUserMenu() {
    const menu = document.getElementById('userMenu');
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

/* Cerrar menú al hacer clic fuera */
document.addEventListener('click', (e) => {
    const menu = document.getElementById('userMenu');
    const btn = document.querySelector('.user-menu-btn');
    if (!e.target.closest('.user-widget') && menu.style.display === 'block') {
        menu.style.display = 'none';
    }
});

/* ─── Actualizar estadísticas en la topbar ─── */
async function actualizarEstadisticas() {
    try {
        const { ok, data } = await ajaxRequest('GET', API_URL);
        if (ok && Array.isArray(data)) {
            document.getElementById('totalStats').textContent = data.length;
        }
    } catch (err) {
        console.error('[actualizarEstadisticas]', err);
    }
}

/* ═══════════════════════════════════════════════════
   HISTORIA CLÍNICA - SIDEBAR
   ═══════════════════════════════════════════════════ */

const API_HISTORIA = '/api/historia_clinica.php';
let cedulaPacienteSeleccionado = null;

/* Cargar historia clínica del paciente */
async function cargarHistoriaClinica(cedula) {
    cedulaPacienteSeleccionado = cedula;
    const container = document.getElementById('sidebarHistoria');
    const btnAgregar = document.getElementById('btnAgregarHistoria');

    container.innerHTML = `
        <div class="empty-state">
            <span class="empty-icon loading-spin">⏳</span>
            <p>Cargando historia clínica…</p>
        </div>`;
    btnAgregar.disabled = true;

    try {
        const { ok, data } = await ajaxRequest('GET', `${API_HISTORIA}?cedula=${encodeURIComponent(cedula)}`);

        if (!ok || !Array.isArray(data)) {
            container.innerHTML = `
                <div class="empty-state">
                    <span class="empty-icon">⚠️</span>
                    <p>${escHtml(data?.error || 'Error al cargar historia clínica.')}</p>
                </div>`;
            btnAgregar.disabled = true;
            return;
        }

        if (data.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <span class="empty-icon">📋</span>
                    <p>No hay registros de historia clínica</p>
                </div>`;
            btnAgregar.disabled = false;
            return;
        }

        // Obtener info del paciente para mostrar en el header
        const paciente = await ajaxRequest('GET', `api/pacientes.php?buscar=${encodeURIComponent(cedula)}`);
        const pacienteData = paciente.data?.[0];

        let html = '';

        html += `
            <table class="historia-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    ${data.map(h => `
                        <tr>
                            <td class="fecha-cell"><strong>${escHtml(h.fecha)}</strong></td>
                            <td class="actions-cell">
                                <button class="btn btn-primary btn-icon" 
                                    onclick="abrirModalEditarHistoria('${h.id_historia}','${escHtml(h.fecha)}','${escHtml(h.diagnostico || '')}','${escHtml(h.tratamiento || '')}','${escHtml(h.observaciones || '')}')"
                                    title="Editar">✏️</button>
                                <button class="btn btn-danger btn-icon" 
                                    onclick="abrirModalEliminarHistoria('${h.id_historia}','${escHtml(h.fecha)}')"
                                    title="Eliminar">🗑️</button>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <div style="font-size: .8rem; color: var(--text-muted); padding: .5rem 0;">
                                    ${h.diagnostico ? `<div><strong>Diagnóstico:</strong> ${escHtml(h.diagnostico)}</div>` : ''}
                                    ${h.tratamiento ? `<div><strong>Tratamiento:</strong> ${escHtml(h.tratamiento)}</div>` : ''}
                                    ${h.observaciones ? `<div><strong>Observaciones:</strong> ${escHtml(h.observaciones)}</div>` : ''}
                                </div>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>`;

        container.innerHTML = html;
        btnAgregar.disabled = false;

    } catch (err) {
        container.innerHTML = `
            <div class="empty-state">
                <span class="empty-icon">⚠️</span>
                <p>${escHtml(err.message || 'Error de conexión.')}</p>
            </div>`;
        console.error('[cargarHistoriaClinica]', err);
        btnAgregar.disabled = true;
    }
}
function cerrarSidebar() {
    cedulaPacienteSeleccionado = null;
    document.getElementById('sidebarHistoria').innerHTML = `
        <div class="empty-state">
            <span class="empty-icon">👤</span>
            <p>Selecciona un paciente para ver su historia clínica</p>
        </div>`;
    document.getElementById('btnAgregarHistoria').disabled = true;
}

/* Abrir modal para agregar historia */
function abrirModalAgregarHistoria() {
    if (!cedulaPacienteSeleccionado) {
        showToast('Selecciona un paciente primero.', 'error');
        return;
    }
    document.getElementById('formAgregarHistoria').reset();
    
    // Establecer la fecha de hoy
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('hcFecha').value = today;
    
    ocultarError(document.getElementById('hcAgError'));
    abrirModal('modalAgregarHistoria');
}

async function submitAgregarHistoria(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitHistoria');
    const err = document.getElementById('hcAgError');
    const form = document.getElementById('formAgregarHistoria');
    const formData = new FormData(form);

    if (!formData.get('fecha')) {
        mostrarError(err, 'La fecha es obligatoria.');
        return;
    }

    const data = {
        cedula: cedulaPacienteSeleccionado,
        fecha: formData.get('fecha'),
        diagnostico: formData.get('diagnostico') || '',
        tratamiento: formData.get('tratamiento') || '',
        observaciones: formData.get('observaciones') || ''
    };

    ocultarError(err);
    setBtnLoading(btn, true);

    try {
        const { ok, data: result } = await ajaxRequest('POST', API_HISTORIA, data);

        if (ok && result.success) {
            cerrarModal('modalAgregarHistoria');
            showToast('✅ Registro de historia clínica agregado correctamente.');
            cargarHistoriaClinica(cedulaPacienteSeleccionado);
        } else {
            mostrarError(err, result.error || 'Error al agregar el registro.');
        }
    } catch (ex) {
        mostrarError(err, ex.message || 'Error de conexión con el servidor.');
        console.error('[submitAgregarHistoria]', ex);
    } finally {
        setBtnLoading(btn, false);
    }
}

/* Abrir modal para editar historia */
function abrirModalEditarHistoria(id, fecha, diagnostico, tratamiento, observaciones) {
    document.getElementById('ehId').value = id;
    document.getElementById('ehFecha').value = fecha;
    document.getElementById('ehDiagnostico').value = diagnostico;
    document.getElementById('ehTratamiento').value = tratamiento;
    document.getElementById('ehObservaciones').value = observaciones;
    ocultarError(document.getElementById('hcEdError'));
    abrirModal('modalEditarHistoria');
}

async function submitEditarHistoria(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitEditarHistoria');
    const err = document.getElementById('hcEdError');
    const form = document.getElementById('formEditarHistoria');
    const formData = new FormData(form);

    if (!formData.get('fecha')) {
        mostrarError(err, 'La fecha es obligatoria.');
        return;
    }

    const data = {
        id_historia: document.getElementById('ehId').value,
        fecha: formData.get('fecha'),
        diagnostico: formData.get('diagnostico') || '',
        tratamiento: formData.get('tratamiento') || '',
        observaciones: formData.get('observaciones') || ''
    };

    ocultarError(err);
    setBtnLoading(btn, true);

    try {
        const { ok, data: result } = await ajaxRequest('PUT', API_HISTORIA, data);

        if (ok && result.success) {
            cerrarModal('modalEditarHistoria');
            showToast('✅ Registro de historia clínica actualizado correctamente.');
            cargarHistoriaClinica(cedulaPacienteSeleccionado);
        } else {
            mostrarError(err, result.error || 'Error al actualizar el registro.');
        }
    } catch (ex) {
        mostrarError(err, ex.message || 'Error de conexión con el servidor.');
        console.error('[submitEditarHistoria]', ex);
    } finally {
        setBtnLoading(btn, false);
    }
}

/* Abrir modal para eliminar historia */
function abrirModalEliminarHistoria(id, fecha) {
    document.getElementById('idHistoriaEliminar').value = id;
    document.querySelector('#modalEliminarHistoria .confirm-text').textContent = `¿Está seguro que desea eliminar el registro del ${fecha}?`;
    abrirModal('modalEliminarHistoria');
}

async function confirmarEliminarHistoria() {
    const id = document.getElementById('idHistoriaEliminar').value;
    const btn = document.querySelector('#modalEliminarHistoria .btn-danger');

    setBtnLoading(btn, true);

    try {
        const { ok, data: result } = await ajaxRequest('DELETE', API_HISTORIA, { id_historia: id });

        if (ok && result.success) {
            cerrarModal('modalEliminarHistoria');
            showToast('🗑️ Registro de historia clínica eliminado correctamente.');
            cargarHistoriaClinica(cedulaPacienteSeleccionado);
        } else {
            showToast(result.error || 'Error al eliminar el registro.', 'error');
            cerrarModal('modalEliminarHistoria');
        }
    } catch (ex) {
        showToast(ex.message || 'Error de conexión con el servidor.', 'error');
        console.error('[confirmarEliminarHistoria]', ex);
    } finally {
        setBtnLoading(btn, false);
    }
}