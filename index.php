<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pacientes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <!-- ───────── TOPBAR ───────── -->
    <header class="topbar">
        <div class="topbar-container">
            <!-- Logo y Nombre -->
            <div class="topbar-brand">
                <span class="brand-icon">🏥</span>
                <div class="brand-text">
                    <h1>Hospital Manager</h1>
                    <p>Gestión de Pacientes</p>
                </div>
            </div>

            <!-- Centro: Fecha y Hora -->
            <div class="topbar-center">
                <div class="datetime-widget">
                    <span class="date-icon">📅</span>
                    <div class="datetime-info">
                        <div id="currentDate" class="date-display">Mar 3, 2026</div>
                        <div id="currentTime" class="time-display">00:00:00</div>
                    </div>
                </div>
            </div>

            <!-- Derecha: Estadísticas y Usuario -->
            <div class="topbar-right">
                <div class="stats-widget">
                    <div class="stat-item">
                        <span class="stat-icon">👥</span>
                        <div class="stat-content">
                            <span class="stat-label">Pacientes</span>
                            <span id="totalStats" class="stat-value">0</span>
                        </div>
                    </div>
                </div>

                <div class="topbar-divider"></div>

                <div class="user-widget">
                    <span class="user-icon">👨‍⚕️</span>
                    <div class="user-info">
                        <span class="user-name">Administrador</span>
                        <span class="user-role">Sistema</span>
                    </div>
                    <button class="user-menu-btn" onclick="toggleUserMenu()" title="Menú">⋮</button>
                </div>

                <div id="userMenu" class="user-menu" style="display:none;">
                    <a href="#" onclick="event.preventDefault(); showToast('Perfil en desarrollo', 'success')">👤 Perfil</a>
                    <a href="#" onclick="event.preventDefault(); showToast('Configuración en desarrollo', 'success')">⚙️ Configuración</a>
                    <div class="menu-divider"></div>
                    <a href="#" onclick="event.preventDefault(); showToast('Sesión cerrada', 'success')">🚪 Salir</a>
                </div>
            </div>
        </div>
    </header>

    <!-- ───────── MAIN ───────── -->
    <main class="container-with-sidebars">
        
        <!-- SIDEBAR IZQUIERDA: PACIENTES -->
        <aside class="sidebar sidebar-left card">
            <div class="sidebar-header">
                <h3 class="sidebar-title">👥 Pacientes</h3>
            </div>

            <!-- Búsqueda en sidebar -->
            <div class="sidebar-search">
                <div class="search-input-wrap">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                    <input type="text" id="inputBuscar" placeholder="Buscar paciente…" autocomplete="off">
                </div>
            </div>

            <!-- Lista de pacientes -->
            <div id="sidebarPacientes" class="sidebar-content">
                <div class="empty-state">
                    <span class="empty-icon">🔍</span>
                    <p>Cargando pacientes…</p>
                </div>
            </div>

            <div class="sidebar-footer">
                <button id="btnAgregar" class="btn btn-success btn-full" onclick="abrirModalAgregar()">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    Agregar Paciente
                </button>
            </div>
        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <div class="main-content">
            <section id="pacienteInfo" class="card paciente-detail" style="display:none;">
                <div class="paciente-detail-header">
                    <h2 id="pacienteNombre"></h2>
                    <button class="btn btn-outline btn-sm" onclick="limpiarSeleccion()">Cerrar</button>
                </div>
                <div class="paciente-detail-body">
                    <div class="detail-row">
                        <span class="detail-label">Cédula:</span>
                        <span id="pacienteCedula" class="detail-value"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Correo:</span>
                        <span id="pacienteCorreo" class="detail-value"></span>
                    </div>
                </div>
            </section>

            <section class="card welcome-section" id="welcomeSection">
                <div class="welcome-content">
                    <div class="welcome-icon">🏥</div>
                    <h2>Bienvenido</h2>
                    <p>Selecciona un paciente de la lista para ver su historia clínica</p>
                </div>
            </section>
        </div>

        <!-- SIDEBAR DERECHA: HISTORIA CLÍNICA -->
        <aside class="sidebar sidebar-right card">
            <div class="sidebar-header">
                <h3 class="sidebar-title">📋 Historia Clínica</h3>
            </div>
            
            <div id="sidebarHistoria" class="sidebar-content">
                <div class="empty-state">
                    <span class="empty-icon">👤</span>
                    <p>Selecciona un paciente para ver su historia clínica</p>
                </div>
            </div>

            <div class="sidebar-footer">
                <button id="btnAgregarHistoria" class="btn btn-success btn-full" onclick="abrirModalAgregarHistoria()" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    Agregar Registro
                </button>
            </div>
        </aside>

    </main>

    <!-- ═══════════ MODAL: AGREGAR ═══════════ -->
    <div id="modalAgregar" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="tituloModalAgregar">
        <div class="modal-box">
            <div class="modal-header">
                <h3 id="tituloModalAgregar">Agregar Paciente</h3>
                <button class="modal-close" onclick="cerrarModal('modalAgregar')" aria-label="Cerrar">&times;</button>
            </div>
            <form id="formAgregar" onsubmit="submitAgregar(event)">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="agCedula">Cédula <span class="req">*</span></label>
                        <input type="text" id="agCedula" name="cedula" required placeholder="Ej. 1234567890">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="agNombre">Nombre <span class="req">*</span></label>
                            <input type="text" id="agNombre" name="nombre" required placeholder="Nombre">
                        </div>
                        <div class="form-group">
                            <label for="agApellido">Apellido <span class="req">*</span></label>
                            <input type="text" id="agApellido" name="apellido" required placeholder="Apellido">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="agCorreo">Correo electrónico <span class="req">*</span></label>
                        <input type="email" id="agCorreo" name="correo" required placeholder="ejemplo@correo.com">
                    </div>
                    <div id="agError" class="form-error" style="display:none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline"
                        onclick="cerrarModal('modalAgregar')">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="btnSubmitAgregar">
                        <span class="btn-text">Guardar Paciente</span>
                        <span class="btn-loader" style="display:none;">Guardando…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ═══════════ MODAL: EDITAR ═══════════ -->
    <div id="modalEditar" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="tituloModalEditar">
        <div class="modal-box">
            <div class="modal-header">
                <h3 id="tituloModalEditar">Editar Paciente</h3>
                <button class="modal-close" onclick="cerrarModal('modalEditar')" aria-label="Cerrar">&times;</button>
            </div>
            <form id="formEditar" onsubmit="submitEditar(event)">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edCedula">Cédula</label>
                        <input type="text" id="edCedula" name="cedula" readonly class="readonly-input">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edNombre">Nombre <span class="req">*</span></label>
                            <input type="text" id="edNombre" name="nombre" required placeholder="Nombre">
                        </div>
                        <div class="form-group">
                            <label for="edApellido">Apellido <span class="req">*</span></label>
                            <input type="text" id="edApellido" name="apellido" required placeholder="Apellido">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edCorreo">Correo electrónico <span class="req">*</span></label>
                        <input type="email" id="edCorreo" name="correo" required placeholder="ejemplo@correo.com">
                    </div>
                    <div id="edError" class="form-error" style="display:none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="cerrarModal('modalEditar')">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitEditar">
                        <span class="btn-text">Actualizar</span>
                        <span class="btn-loader" style="display:none;">Actualizando…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ═══════════ MODAL: CONFIRMAR ELIMINAR ═══════════ -->
    <div id="modalEliminar" class="modal-overlay" role="dialog" aria-modal="true">
        <div class="modal-box modal-sm">
            <div class="modal-header modal-header-danger">
                <h3>Eliminar Paciente</h3>
                <button class="modal-close" onclick="cerrarModal('modalEliminar')" aria-label="Cerrar">&times;</button>
            </div>
            <div class="modal-body">
                <div class="confirm-icon">🗑️</div>
                <p class="confirm-text">¿Está seguro que desea eliminar al paciente <strong
                        id="nombreEliminar"></strong>?</p>
                <p class="confirm-sub">Esta acción no se puede deshacer.</p>
                <input type="hidden" id="cedulaEliminar">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="cerrarModal('modalEliminar')">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmarEliminar()">
                    <span class="btn-text">Sí, eliminar</span>
                    <span class="btn-loader" style="display:none;">Eliminando…</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ═══════════ MODAL: AGREGAR HISTORIA CLÍNICA ═══════════ -->
    <div id="modalAgregarHistoria" class="modal-overlay" role="dialog" aria-modal="true">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Agregar Registro de Historia Clínica</h3>
                <button class="modal-close" onclick="cerrarModal('modalAgregarHistoria')" aria-label="Cerrar">&times;</button>
            </div>
            <form id="formAgregarHistoria" onsubmit="submitAgregarHistoria(event)">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="hcFecha">Fecha <span class="req">*</span></label>
                        <input type="date" id="hcFecha" name="fecha" required>
                    </div>
                    <div class="form-group">
                        <label for="hcDiagnostico">Diagnóstico</label>
                        <textarea id="hcDiagnostico" name="diagnostico" placeholder="Ingrese el diagnóstico…" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="hcTratamiento">Tratamiento</label>
                        <textarea id="hcTratamiento" name="tratamiento" placeholder="Ingrese el tratamiento…" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="hcObservaciones">Observaciones</label>
                        <textarea id="hcObservaciones" name="observaciones" placeholder="Ingrese las observaciones…" rows="3"></textarea>
                    </div>
                    <div id="hcAgError" class="form-error" style="display:none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="cerrarModal('modalAgregarHistoria')">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="btnSubmitHistoria">
                        <span class="btn-text">Guardar Registro</span>
                        <span class="btn-loader" style="display:none;">Guardando…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ═══════════ MODAL: EDITAR HISTORIA CLÍNICA ═══════════ -->
    <div id="modalEditarHistoria" class="modal-overlay" role="dialog" aria-modal="true">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Editar Registro de Historia Clínica</h3>
                <button class="modal-close" onclick="cerrarModal('modalEditarHistoria')" aria-label="Cerrar">&times;</button>
            </div>
            <form id="formEditarHistoria" onsubmit="submitEditarHistoria(event)">
                <div class="modal-body">
                    <input type="hidden" id="ehId" name="id_historia">
                    <div class="form-group">
                        <label for="ehFecha">Fecha <span class="req">*</span></label>
                        <input type="date" id="ehFecha" name="fecha" required>
                    </div>
                    <div class="form-group">
                        <label for="ehDiagnostico">Diagnóstico</label>
                        <textarea id="ehDiagnostico" name="diagnostico" placeholder="Ingrese el diagnóstico…" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="ehTratamiento">Tratamiento</label>
                        <textarea id="ehTratamiento" name="tratamiento" placeholder="Ingrese el tratamiento…" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="ehObservaciones">Observaciones</label>
                        <textarea id="ehObservaciones" name="observaciones" placeholder="Ingrese las observaciones…" rows="3"></textarea>
                    </div>
                    <div id="hcEdError" class="form-error" style="display:none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="cerrarModal('modalEditarHistoria')">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitEditarHistoria">
                        <span class="btn-text">Actualizar</span>
                        <span class="btn-loader" style="display:none;">Actualizando…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ═══════════ MODAL: CONFIRMAR ELIMINAR HISTORIA ═══════════ -->
    <div id="modalEliminarHistoria" class="modal-overlay" role="dialog" aria-modal="true">
        <div class="modal-box modal-sm">
            <div class="modal-header modal-header-danger">
                <h3>Eliminar Registro</h3>
                <button class="modal-close" onclick="cerrarModal('modalEliminarHistoria')" aria-label="Cerrar">&times;</button>
            </div>
            <div class="modal-body">
                <div class="confirm-icon">📋</div>
                <p class="confirm-text">¿Está seguro que desea eliminar este registro de historia clínica?</p>
                <p class="confirm-sub">Esta acción no se puede deshacer.</p>
                <input type="hidden" id="idHistoriaEliminar">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="cerrarModal('modalEliminarHistoria')">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmarEliminarHistoria()">
                    <span class="btn-text">Sí, eliminar</span>
                    <span class="btn-loader" style="display:none;">Eliminando…</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Toast de notificaciones -->
    <div id="toast" class="toast" role="alert"></div>

    <script src="assets/js/script.js"></script>
</body>

</html>