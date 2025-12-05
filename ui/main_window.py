from PySide6.QtWidgets import QMainWindow, QLabel, QPushButton, QVBoxLayout, QHBoxLayout, QGroupBox, QWidget, QMessageBox, QFrame, QGridLayout, QSizePolicy, QScrollArea
from PySide6.QtGui import QFont
from PySide6.QtCore import Qt
from ui.user_admin import UserAdmin
from ui.depto_admin import DeptoAdmin
from ui.producto_form import ProductoForm
from ui.precios_form import PreciosForm
from ui.venta_form import VentaForm
from ui.clientes_admin import ClientesAdmin
from ui.cierres_admin import CierresAdmin
from ui.dashboard_form import DashboardForm
from ui.stock_avanzado_form import StockAvanzadoForm
from ui.reportes_ventas_form import ReportesVentasForm
from ui.reportes_productos_form import ReportesProductosForm
from ui.reportes_usuarios_form import ReportesUsuariosForm
from ui.config_sistema_form import ConfigSistemaForm
from ui.flejes_form import FlejesForm
from ui.flejes_vb import FlejesVBForm
from ui.proveedores_admin import ProveedoresAdmin

class MainWindow(QMainWindow):
    def __init__(self,  usuario: str, db: 'DBClient', sesion_id: int):
        super().__init__()
        #print(f"Sesión ID al iniciar: {sesion_id} - Tipo: {type(sesion_id)}")
        self.sesion_id = sesion_id
        self.usuario = usuario
        self.db = db
        self.setWindowTitle("RMS - Principal")
        self.setGeometry(150, 150, 400, 400)

        self.label = QLabel(f"Bienvenido {usuario}")
        self.logout_btn = QPushButton("Cerrar sesión")
        self.logout_btn.clicked.connect(self.logout)

        self.admin_btn = QPushButton("Gestión de Usuarios")
        self.admin_btn.clicked.connect(self.abrir_gestion_usuarios)
        self.admin_btn.setVisible(False)

        self.categoria_btn = QPushButton("Gestión de Categorías")
        self.categoria_btn.clicked.connect(self.abrir_gestion_categorias)
        self.categoria_btn.setVisible(False)

        self.producto_btn = QPushButton("Registrar Producto")
        self.producto_btn.clicked.connect(self.abrir_formulario_producto)
        self.producto_btn.setVisible(False)

        self.precio_btn=QPushButton("Actualizar precios")
        self.precio_btn.clicked.connect(self.abrir_formulario_precio)
        self.precio_btn.setVisible(False)

        self.venta_btn=QPushButton("POS Venta")
        self.venta_btn.clicked.connect(self.abrir_pos)
        self.venta_btn.setVisible(False)

        self.clientes_btn = QPushButton("👥 Gestión de Clientes")
        self.clientes_btn.clicked.connect(self.abrir_gestion_clientes)
        self.clientes_btn.setVisible(False)
        self.clientes_btn.setStyleSheet("""
            QPushButton {
                background-color: #5d6d7e;
                color: white;
                font-weight: bold;
                font-size: 12px;
                padding: 10px;
                border: none;
                border-radius: 6px;
            }
            QPushButton:hover {
                background-color: #34495e;
            }
            QPushButton:pressed {
                background-color: #2c3e50;
            }
        """)
        
        self.cierres_btn = QPushButton("💰 Cierres de Caja")
        self.cierres_btn.clicked.connect(self.abrir_gestion_cierres)
        self.cierres_btn.setVisible(False)
        self.cierres_btn.setStyleSheet("""
            QPushButton {
                background-color: #e74c3c;
                color: white;
                font-weight: bold;
                font-size: 12px;
                padding: 10px;
                border: none;
                border-radius: 6px;
            }
            QPushButton:hover {
                background-color: #c0392b;
            }
            QPushButton:pressed {
                background-color: #a93226;
            }
        """)
        
        self.dashboard_btn = QPushButton("📊 Dashboard")
        self.dashboard_btn.clicked.connect(self.abrir_dashboard)
        self.dashboard_btn.setVisible(False)
        self.dashboard_btn.setStyleSheet("""
            QPushButton {
                background-color: #9b59b6;
                color: white;
                font-weight: bold;
                font-size: 12px;
                padding: 10px;
                border: none;
                border-radius: 6px;
            }
            QPushButton:hover {
                background-color: #8e44ad;
            }
            QPushButton:pressed {
                background-color: #7d3c98;
            }
        """)
        
        self.reportes_btn = QPushButton("📈 Reportes de Ventas")
        self.reportes_btn.clicked.connect(self.abrir_reportes_ventas)
        self.reportes_btn.setVisible(False)
        self.reportes_btn.setStyleSheet("""
            QPushButton {
                background-color: #e67e22;
                color: white;
                font-weight: bold;
                font-size: 12px;
                padding: 10px;
                border: none;
                border-radius: 6px;
            }
            QPushButton:hover {
                background-color: #d35400;
            }
            QPushButton:pressed {
                background-color: #ba4a00;
            }
        """)
        
        self.reportes_productos_btn = QPushButton("📊 Reportes de Productos")
        self.reportes_productos_btn.clicked.connect(self.abrir_reportes_productos)
        self.reportes_productos_btn.setVisible(False)
        self.reportes_productos_btn.setStyleSheet("""
            QPushButton {
                background-color: #16a085;
                color: white;
                font-weight: bold;
                font-size: 12px;
                padding: 10px;
                border: none;
                border-radius: 6px;
            }
            QPushButton:hover {
                background-color: #138d75;
            }
            QPushButton:pressed {
                background-color: #117a65;
            }
        """)
        
        # Reportes de Usuarios
        self.reportes_usuarios_btn = QPushButton("👥 Reportes de Usuarios")
        self.reportes_usuarios_btn.clicked.connect(self.abrir_reportes_usuarios)
        self.reportes_usuarios_btn.setVisible(False)
        self.reportes_usuarios_btn.setStyleSheet("""
            QPushButton {
                background-color: #8e44ad;
                color: white;
                font-weight: bold;
                font-size: 12px;
                padding: 10px;
                border: none;
                border-radius: 6px;
            }
            QPushButton:hover {
                background-color: #7d3c98;
            }
            QPushButton:pressed {
                background-color: #6c3483;
            }
        """)
        
        # Proveedores
        self.proveedores_btn = QPushButton("🏢 Proveedores")
        self.proveedores_btn.clicked.connect(self.abrir_proveedores)
        self.proveedores_btn.setVisible(False)
        self.proveedores_btn.setStyleSheet("""
            QPushButton {
                background-color: #d35400;
                color: white;
                font-weight: bold;
                font-size: 12px;
                padding: 10px;
                border: none;
                border-radius: 6px;
            }
            QPushButton:hover {
                background-color: #ba4a00;
            }
            QPushButton:pressed {
                background-color: #a04000;
            }
        """)
        
        self.stock_btn = QPushButton("📦 Stock Avanzado")
        self.stock_btn.clicked.connect(self.abrir_stock_avanzado)
        self.stock_btn.setVisible(False)

        # Configuración del Sistema
        self.config_btn = QPushButton("⚙️ Configuración")
        self.config_btn.clicked.connect(self.abrir_configuracion_sistema)
        self.config_btn.setVisible(False)
        self.config_btn.setStyleSheet("""
            QPushButton {
                background-color: #7f8c8d;
                color: white;
                font-weight: bold;
                font-size: 12px;
                padding: 10px;
                border: none;
                border-radius: 6px;
            }
            QPushButton:hover { background-color: #707b7c; }
            QPushButton:pressed { background-color: #5f6a6a; }
        """)
        self.stock_btn.setStyleSheet("""
            QPushButton {
                background-color: #f39c12;
                color: white;
                font-weight: bold;
                font-size: 12px;
                padding: 10px;
                border: none;
                border-radius: 6px;
            }
            QPushButton:hover {
                background-color: #e67e22;
            }
            QPushButton:pressed {
                background-color: #d35400;
            }
        """)

        # Flejes / Códigos de Barras
        self.flejes_btn = QPushButton("🏷️ Flejes / Códigos de Barras")
        self.flejes_btn.clicked.connect(self.abrir_flejes)
        self.flejes_btn.setVisible(False)
        self.flejes_btn.setStyleSheet("""
            QPushButton {
                background-color: #8e44ad;
                color: white;
                font-weight: bold;
                font-size: 12px;
                padding: 10px;
                border: none;
                border-radius: 6px;
            }
            QPushButton:hover { background-color: #7d3c98; }
            QPushButton:pressed { background-color: #6c3483; }
        """)

        # Flejes V2 (Basado en VB6)
        self.flejes_v2_btn = QPushButton("🏷️ Flejes V2 (VB6)")
        self.flejes_v2_btn.clicked.connect(self.abrir_flejes_v2)
        self.flejes_v2_btn.setVisible(False)
        self.flejes_v2_btn.setStyleSheet("""
            QPushButton {
                background-color: #16a085;
                color: white;
                font-weight: bold;
                font-size: 12px;
                padding: 10px;
                border: none;
                border-radius: 6px;
            }
            QPushButton:hover { background-color: #138d75; }
            QPushButton:pressed { background-color: #117a65; }
        """)

        rol = self.db.get_rol_usuario(usuario)
        if rol == 'admin':
            self.admin_btn.setVisible(True)
        # Reiniciar visibilidad: todo oculto inicialmente
        for btn in [
            self.categoria_btn, self.producto_btn, self.precio_btn, self.venta_btn,
            self.clientes_btn, self.cierres_btn, self.stock_btn, self.reportes_btn,
            self.reportes_productos_btn, self.reportes_usuarios_btn, self.proveedores_btn,
            self.dashboard_btn, self.config_btn, self.flejes_btn, self.flejes_v2_btn
        ]:
            btn.setVisible(False)

        # Ocultar tarjetas por defecto
        for card in [
            getattr(self, 'card_pos', None), getattr(self, 'card_cierre', None), getattr(self, 'card_flejes', None), getattr(self, 'card_clientes', None),
            getattr(self, 'card_categorias', None), getattr(self, 'card_productos', None), getattr(self, 'card_precios', None), getattr(self, 'card_stock', None),
            getattr(self, 'card_dashboard', None), getattr(self, 'card_rep_ventas', None), getattr(self, 'card_rep_prod', None), getattr(self, 'card_rep_users', None),
            getattr(self, 'card_users', None), getattr(self, 'card_prov', None), getattr(self, 'card_config', None)
        ]:
            if card is not None:
                card.setVisible(False)

        # Permisos por rol
        if rol == 'admin':
            # Acceso total (botones clásicos)
            self.categoria_btn.setVisible(True)
            self.producto_btn.setVisible(True)
            self.precio_btn.setVisible(True)
            self.venta_btn.setVisible(True)
            self.clientes_btn.setVisible(True)
            self.cierres_btn.setVisible(True)
            self.stock_btn.setVisible(True)
            self.reportes_btn.setVisible(True)
            self.reportes_productos_btn.setVisible(True)
            self.reportes_usuarios_btn.setVisible(True)
            self.proveedores_btn.setVisible(True)
            self.dashboard_btn.setVisible(True)
            self.config_btn.setVisible(True)
            self.flejes_btn.setVisible(True)
            self.flejes_v2_btn.setVisible(True)
        elif rol == 'cajero':
            # Solo POS Venta
            self.venta_btn.setVisible(True)
        elif rol == 'supervisor':
            # Solo módulos de cajas y cierre de cajas
            self.cierres_btn.setVisible(True)
        elif rol == 'jefe':
            # Reportes, Dashboard, POS ventas y (si existiera) flejes
            self.venta_btn.setVisible(True)
            self.dashboard_btn.setVisible(True)
            self.reportes_btn.setVisible(True)
            self.reportes_productos_btn.setVisible(True)
            self.reportes_usuarios_btn.setVisible(True)
            self.flejes_btn.setVisible(True)
            self.flejes_v2_btn.setVisible(True)
        elif rol == 'barras':
            # Solo impresión de flejes/código de barras
            self.flejes_btn.setVisible(True)
            self.flejes_v2_btn.setVisible(True)
        elif rol == 'digitador':
            # Solo ingreso de productos y familias o proveedores
            self.categoria_btn.setVisible(True)   # familias (departamentos/subdepartamentos/clases)
            self.producto_btn.setVisible(True)    # productos
            self.proveedores_btn.setVisible(True) # proveedores
        elif rol == 'generico':
            # Por configurar: sin accesos por defecto
            pass
        else:
            # Roles no reconocidos: sin accesos
            pass


        # Estilos generales sutiles
        self.setStyleSheet("""
            QGroupBox { font-weight: bold; border: 2px solid #bdc3c7; border-radius: 6px; margin-top: 10px; }
            QGroupBox::title { subcontrol-origin: margin; left: 10px; padding: 0 6px; }
            QWidget#MainContent { background: #f5f5f5; }
        """)

        root = QVBoxLayout()
        title_row = QHBoxLayout()
        self.label.setStyleSheet("font-weight: 600; font-size: 14px;")
        title_row.addWidget(self.label)
        title_row.addStretch()
        title_row.addWidget(self.logout_btn)
        root.addLayout(title_row)

        # Estilo homogéneo de botones (tamaño y tipografía)
        def tune(btn: QPushButton):
            btn.setMinimumHeight(80)
            btn.setSizePolicy(QSizePolicy.Expanding, QSizePolicy.Preferred)
            btn.setStyleSheet(btn.styleSheet() + "\nfont-size: 13px; font-weight: 600; padding: 12px;")

        for b in [
            self.venta_btn, self.cierres_btn, self.flejes_btn, self.flejes_v2_btn, self.clientes_btn,
            self.categoria_btn, self.producto_btn, self.precio_btn, self.stock_btn,
            self.dashboard_btn, self.reportes_btn, self.reportes_productos_btn, self.reportes_usuarios_btn,
            self.admin_btn, self.proveedores_btn, self.config_btn
        ]:
            tune(b)

        # Sección Operaciones (grid responsive)
        ops_box = QGroupBox("Operaciones")
        ops = QGridLayout(ops_box)
        self._ops_buttons = [self.venta_btn, self.cierres_btn, self.flejes_btn, self.flejes_v2_btn, self.clientes_btn]

        # Sección Inventario (grid responsive)
        inv_box = QGroupBox("Inventario")
        inv = QGridLayout(inv_box)
        self._inv_buttons = [self.categoria_btn, self.producto_btn, self.precio_btn, self.stock_btn]

        # Sección Reportes (grid responsive)
        rep_box = QGroupBox("Reportes")
        rep = QGridLayout(rep_box)
        self._rep_buttons = [self.dashboard_btn, self.reportes_btn, self.reportes_productos_btn, self.reportes_usuarios_btn]

        # Sección Administración (grid responsive)
        adm_box = QGroupBox("Administración y Configuración")
        adm = QGridLayout(adm_box)
        self._adm_buttons = [self.admin_btn, self.proveedores_btn, self.config_btn]

        # Contenedor scrollable
        content_widget = QWidget()
        content_layout = QVBoxLayout(content_widget)
        content_layout.setContentsMargins(8, 8, 8, 8)
        content_layout.setSpacing(14)
        content_layout.addWidget(ops_box)
        content_layout.addWidget(inv_box)
        content_layout.addWidget(rep_box)
        content_layout.addWidget(adm_box)

        scroll = QScrollArea()
        scroll.setWidgetResizable(True)
        scroll.setFrameShape(QFrame.NoFrame)
        scroll.setWidget(content_widget)

        root.addWidget(scroll)

        central = QWidget()
        central.setObjectName("MainContent")
        central.setLayout(root)
        self.setCentralWidget(central)

        # Guardar layouts para reflow y hacer primera distribución
        self._grids = [(ops, self._ops_buttons), (inv, self._inv_buttons), (rep, self._rep_buttons), (adm, self._adm_buttons)]
        self._reflow_buttons()

    def _reflow_buttons(self):
        try:
            available = max(300, self.width() - 80)
            base = 260  # ancho objetivo por botón
            cols = max(1, min(4, available // base))
            for grid, buttons in self._grids:
                # Limpiar grid
                while grid.count():
                    item = grid.takeAt(0)
                    w = item.widget()
                    if w:
                        w.setParent(None)
                # Recolocar
                r = c = 0
                for btn in buttons:
                    grid.addWidget(btn, r, c)
                    c += 1
                    if c >= cols:
                        c = 0
                        r += 1
        except Exception:
            pass

    def resizeEvent(self, event):
        super().resizeEvent(event)
        self._reflow_buttons()

        # No hay tarjetas; visibilidad ya está controlada por rol arriba

    def closeEvent(self, event):
        try:
            if hasattr(self, 'sesion_id') and self.sesion_id:
                success = self.db.cerrar_sesion(self.sesion_id)
                if not success:
                    QMessageBox.warning(self, "Advertencia", "La sesión ya estaba cerrada")
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error al cerrar sesión: {e}")
        finally:
            event.accept()

    def logout(self):
        self.db.set_estado_conectado(self.usuario, False)
        self.close()

    def abrir_gestion_usuarios(self):
        self.user_admin = UserAdmin(self.db, self.usuario)
        self.user_admin.show()

    def abrir_gestion_categorias(self):
        self.cat_admin = DeptoAdmin(self.db, self.usuario)
        self.cat_admin.show()

    def abrir_formulario_producto(self):
        #self.producto_form = ProductoForm(self.db)
        self.producto_form = ProductoForm(db=self.db, usuario_actual=self.usuario)
        self.producto_form.show()

    def abrir_formulario_precio(self):
        self.precios_form = PreciosForm(db=self.db, usuario_actual=self.usuario)
        self.precios_form.show()

    #def abrir_formulario_venta(self):
    #    self.venta_form = VentaForm(db=self.db, usuario_actual=self.usuario)
    #    self.venta_form.show()

    def abrir_pos(self):
        self.pos = VentaForm(db=self.db, usuario_actual=self.usuario)  # config se lee sola
        self.pos.show()

    def abrir_gestion_clientes(self):
        self.clientes_admin = ClientesAdmin(self.db)
        self.clientes_admin.show()

    def abrir_gestion_cierres(self):
        """Abre el módulo de gestión de cierres de caja"""
        try:
            # Verificar permisos (solo jefe, supervisor, admin)
            rol = self.db.get_rol_usuario(self.usuario)
            if rol not in ['admin', 'jefe', 'supervisor']:
                QMessageBox.warning(self, "⚠️ Acceso Denegado", 
                                  "Solo administradores, jefes y supervisores pueden acceder a los cierres de caja.")
                return
            
            self.cierres_admin = CierresAdmin(self.db, self.usuario)
            self.cierres_admin.show()
        except Exception as e:
            QMessageBox.critical(self, "❌ Error", f"Error al abrir gestión de cierres: {str(e)}")
    
    def abrir_dashboard(self):
        """Abre el Dashboard principal"""
        try:
            self.dashboard = DashboardForm(self.db, self.usuario)
            self.dashboard.show()
        except Exception as e:
            QMessageBox.critical(self, "❌ Error", f"Error al abrir Dashboard: {str(e)}")
    
    def abrir_reportes_ventas(self):
        """Abre el módulo de Reportes de Ventas"""
        try:
            # Verificar permisos (solo admin, jefe, supervisor)
            rol = self.db.get_rol_usuario(self.usuario)
            if rol not in ['admin', 'jefe', 'supervisor']:
                QMessageBox.warning(self, "⚠️ Acceso Denegado", 
                                  "Solo administradores, jefes y supervisores pueden acceder a los reportes de ventas.")
                return
            
            self.reportes_ventas = ReportesVentasForm(self.db, self.usuario)
            self.reportes_ventas.show()
        except Exception as e:
            QMessageBox.critical(self, "❌ Error", f"Error al abrir Reportes de Ventas: {str(e)}")
    
    def abrir_reportes_productos(self):
        """Abre el módulo de Reportes de Productos"""
        try:
            # Verificar permisos (solo admin, jefe, supervisor)
            rol = self.db.get_rol_usuario(self.usuario)
            if rol not in ['admin', 'jefe', 'supervisor']:
                QMessageBox.warning(self, "⚠️ Acceso Denegado", 
                                  "Solo administradores, jefes y supervisores pueden acceder a los reportes de productos.")
                return
            
            self.reportes_productos = ReportesProductosForm(self.db, self.usuario)
            self.reportes_productos.show()
        except Exception as e:
            QMessageBox.critical(self, "❌ Error", f"Error al abrir Reportes de Productos: {str(e)}")
    
    def abrir_stock_avanzado(self):
        """Abre el módulo de Gestión de Stock Avanzada"""
        try:
            # Verificar permisos (solo admin y jefe)
            rol = self.db.get_rol_usuario(self.usuario)
            if rol not in ['admin', 'jefe']:
                QMessageBox.warning(self, "⚠️ Acceso Denegado", 
                                  "Solo administradores y jefes pueden acceder a la gestión de stock avanzada.")
                return
            
            self.stock_avanzado_form = StockAvanzadoForm(self.db, self.usuario)
            self.stock_avanzado_form.show()
        except Exception as e:
            QMessageBox.critical(self, "❌ Error", f"Error al abrir gestión de stock: {str(e)}")
    
    def abrir_reportes_usuarios(self):
        """Abre el módulo de Reportes de Usuarios"""
        try:
            # Verificar permisos (solo admin, jefe, supervisor)
            rol = self.db.get_rol_usuario(self.usuario)
            if rol not in ['admin', 'jefe', 'supervisor']:
                QMessageBox.warning(self, "⚠️ Acceso Denegado", 
                                  "Solo administradores, jefes y supervisores pueden acceder a los reportes de usuarios.")
                return
            
            self.reportes_usuarios = ReportesUsuariosForm(self.db, self.usuario)
            self.reportes_usuarios.show()
        except Exception as e:
            QMessageBox.critical(self, "❌ Error", f"Error al abrir Reportes de Usuarios: {str(e)}")
    
    def abrir_proveedores(self):
        """Abre el módulo de Gestión de Proveedores"""
        try:
            # Verificar permisos (solo admin y jefe)
            rol = self.db.get_rol_usuario(self.usuario)
            if rol not in ['admin', 'jefe']:
                QMessageBox.warning(self, "⚠️ Acceso Denegado", 
                                  "Solo administradores y jefes pueden acceder a la gestión de proveedores.")
                return
            
            self.proveedores_admin = ProveedoresAdmin(self.db, self.usuario)
            self.proveedores_admin.show()
        except Exception as e:
            QMessageBox.critical(self, "❌ Error", f"Error al abrir Gestión de Proveedores: {str(e)}")

    def abrir_configuracion_sistema(self):
        """Abre la configuración del sistema (solo admin/jefe)."""
        try:
            rol = self.db.get_rol_usuario(self.usuario)
            if rol not in ['admin', 'jefe']:
                QMessageBox.warning(self, "⚠️ Acceso Denegado", "Solo administradores y jefes pueden acceder a Configuración del Sistema.")
                return
            self.config_form = ConfigSistemaForm(self.db, self.usuario)
            self.config_form.show()
        except Exception as e:
            QMessageBox.critical(self, "❌ Error", f"Error al abrir Configuración: {str(e)}")

    def abrir_flejes(self):
        """Abre el módulo de Flejes / Códigos de Barras (admin, jefe, barras)."""
        try:
            rol = self.db.get_rol_usuario(self.usuario)
            if rol not in ['admin', 'jefe', 'barras']:
                QMessageBox.warning(self, "⚠️ Acceso Denegado", "No tiene permisos para Flejes/Códigos de Barras.")
                return
            self.flejes_form = FlejesForm(self.db, self.usuario, main_window=self)
            self.flejes_form.show()
        except Exception as e:
            QMessageBox.critical(self, "❌ Error", f"Error al abrir Flejes: {str(e)}")

    def abrir_flejes_v2(self):
        """Abre el módulo de Flejes V2 basado en VB6 (admin, jefe, barras)."""
        try:
            rol = self.db.get_rol_usuario(self.usuario)
            if rol not in ['admin', 'jefe', 'barras']:
                QMessageBox.warning(self, "⚠️ Acceso Denegado", "No tiene permisos para Flejes V2.")
                return
            self.flejes_v2_form = FlejesVBForm(self.db, self.usuario, main_window=self)
            self.flejes_v2_form.show()
        except Exception as e:
            QMessageBox.critical(self, "❌ Error", f"Error al abrir Flejes V2: {str(e)}")
