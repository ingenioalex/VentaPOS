from PySide6.QtWidgets import (
    QMainWindow, QWidget, QVBoxLayout, QHBoxLayout, QTableWidget,
    QTableWidgetItem, QPushButton, QLineEdit, QComboBox, QLabel,
    QMessageBox, QHeaderView, QGroupBox, QGridLayout
)
from PySide6.QtCore import Qt
from PySide6.QtGui import QFont, QColor
from ui.proveedor_form import ProveedorForm

class ProveedoresAdmin(QMainWindow):
    """Ventana principal de gestión de proveedores"""
    
    def __init__(self, db_client, usuario_actual):
        super().__init__()
        self.db = db_client
        self.usuario = usuario_actual
        
        self.setWindowTitle("🏢 Gestión de Proveedores - RMS")
        self.setGeometry(100, 100, 1400, 800)
        
        self.init_ui()
        self.apply_styles()
        self.cargar_estadisticas()
        self.cargar_proveedores()
    
    def init_ui(self):
        """Inicializar interfaz de usuario"""
        central_widget = QWidget()
        self.setCentralWidget(central_widget)
        
        main_layout = QVBoxLayout(central_widget)
        main_layout.setSpacing(10)
        main_layout.setContentsMargins(10, 10, 10, 10)
        
        # Título
        title_label = QLabel("🏢 Gestión de Proveedores")
        title_label.setAlignment(Qt.AlignCenter)
        title_font = QFont()
        title_font.setPointSize(16)
        title_font.setBold(True)
        title_label.setFont(title_font)
        main_layout.addWidget(title_label)
        
        # Estadísticas
        stats_group = QGroupBox("📊 Estadísticas")
        stats_layout = QGridLayout(stats_group)
        
        self.lbl_total_proveedores = QLabel("0")
        self.lbl_proveedores_activos = QLabel("0")
        self.lbl_proveedores_inactivos = QLabel("0")
        self.lbl_total_compras = QLabel("S/ 0.00")
        self.lbl_total_deuda = QLabel("S/ 0.00")
        self.lbl_calificacion_promedio = QLabel("0.0")
        
        # Configurar estilo de métricas
        metric_font = QFont()
        metric_font.setPointSize(12)
        metric_font.setBold(True)
        
        for lbl in [self.lbl_total_proveedores, self.lbl_proveedores_activos,
                   self.lbl_proveedores_inactivos, self.lbl_total_compras,
                   self.lbl_total_deuda, self.lbl_calificacion_promedio]:
            lbl.setFont(metric_font)
            lbl.setAlignment(Qt.AlignCenter)
            lbl.setStyleSheet("background-color: #ecf0f1; padding: 8px; border-radius: 4px;")
        
        stats_layout.addWidget(QLabel("Total Proveedores:"), 0, 0)
        stats_layout.addWidget(self.lbl_total_proveedores, 0, 1)
        stats_layout.addWidget(QLabel("Activos:"), 0, 2)
        stats_layout.addWidget(self.lbl_proveedores_activos, 0, 3)
        stats_layout.addWidget(QLabel("Inactivos:"), 0, 4)
        stats_layout.addWidget(self.lbl_proveedores_inactivos, 0, 5)
        
        stats_layout.addWidget(QLabel("Total Compras:"), 1, 0)
        stats_layout.addWidget(self.lbl_total_compras, 1, 1)
        stats_layout.addWidget(QLabel("Total Deuda:"), 1, 2)
        stats_layout.addWidget(self.lbl_total_deuda, 1, 3)
        stats_layout.addWidget(QLabel("Calificación Promedio:"), 1, 4)
        stats_layout.addWidget(self.lbl_calificacion_promedio, 1, 5)
        
        main_layout.addWidget(stats_group)
        
        # Controles de filtro y búsqueda
        filtros_layout = QHBoxLayout()
        
        filtros_layout.addWidget(QLabel("Buscar:"))
        
        self.input_busqueda = QLineEdit()
        self.input_busqueda.setPlaceholderText("Buscar por razón social, nombre comercial o RUC...")
        self.input_busqueda.textChanged.connect(self.cargar_proveedores)
        filtros_layout.addWidget(self.input_busqueda)
        
        filtros_layout.addWidget(QLabel("Estado:"))
        
        self.combo_estado = QComboBox()
        self.combo_estado.addItems(['todos', 'activo', 'inactivo', 'bloqueado'])
        self.combo_estado.currentTextChanged.connect(self.cargar_proveedores)
        filtros_layout.addWidget(self.combo_estado)
        
        self.btn_limpiar = QPushButton("🔄 Limpiar")
        self.btn_limpiar.clicked.connect(self.limpiar_filtros)
        filtros_layout.addWidget(self.btn_limpiar)
        
        main_layout.addLayout(filtros_layout)
        
        # Botones de acción
        botones_layout = QHBoxLayout()
        
        self.btn_nuevo = QPushButton("➕ Nuevo Proveedor")
        self.btn_nuevo.clicked.connect(self.nuevo_proveedor)
        self.btn_nuevo.setStyleSheet("""
            QPushButton {
                background-color: #27ae60;
                color: white;
                font-weight: bold;
                padding: 10px;
                border-radius: 5px;
                font-size: 12px;
            }
            QPushButton:hover {
                background-color: #229954;
            }
        """)
        botones_layout.addWidget(self.btn_nuevo)
        
        self.btn_editar = QPushButton("✏️ Editar")
        self.btn_editar.clicked.connect(self.editar_proveedor)
        self.btn_editar.setStyleSheet("""
            QPushButton {
                background-color: #3498db;
                color: white;
                font-weight: bold;
                padding: 10px;
                border-radius: 5px;
                font-size: 12px;
            }
            QPushButton:hover {
                background-color: #2980b9;
            }
        """)
        botones_layout.addWidget(self.btn_editar)
        
        self.btn_eliminar = QPushButton("🗑️ Eliminar")
        self.btn_eliminar.clicked.connect(self.eliminar_proveedor)
        self.btn_eliminar.setStyleSheet("""
            QPushButton {
                background-color: #e74c3c;
                color: white;
                font-weight: bold;
                padding: 10px;
                border-radius: 5px;
                font-size: 12px;
            }
            QPushButton:hover {
                background-color: #c0392b;
            }
        """)
        botones_layout.addWidget(self.btn_eliminar)
        
        self.btn_ver_detalles = QPushButton("👁️ Ver Detalles")
        self.btn_ver_detalles.clicked.connect(self.ver_detalles)
        self.btn_ver_detalles.setStyleSheet("""
            QPushButton {
                background-color: #95a5a6;
                color: white;
                font-weight: bold;
                padding: 10px;
                border-radius: 5px;
                font-size: 12px;
            }
            QPushButton:hover {
                background-color: #7f8c8d;
            }
        """)
        botones_layout.addWidget(self.btn_ver_detalles)
        
        botones_layout.addStretch()
        main_layout.addLayout(botones_layout)
        
        # Tabla de proveedores
        self.tbl_proveedores = QTableWidget()
        self.tbl_proveedores.setColumnCount(12)
        self.tbl_proveedores.setHorizontalHeaderLabels([
            "ID", "Razón Social", "Nombre Comercial", "RUC", "Teléfono",
            "Email", "Tipo", "Categoría", "Condición Pago", "Estado",
            "Calificación", "Total Compras"
        ])
        
        # Configurar tabla
        header = self.tbl_proveedores.horizontalHeader()
        header.setSectionResizeMode(0, QHeaderView.ResizeToContents)  # ID
        header.setSectionResizeMode(1, QHeaderView.Stretch)  # Razón Social
        header.setSectionResizeMode(2, QHeaderView.Stretch)  # Nombre Comercial
        header.setSectionResizeMode(3, QHeaderView.ResizeToContents)  # RUC
        header.setSectionResizeMode(4, QHeaderView.ResizeToContents)  # Teléfono
        header.setSectionResizeMode(5, QHeaderView.Stretch)  # Email
        header.setSectionResizeMode(6, QHeaderView.ResizeToContents)  # Tipo
        header.setSectionResizeMode(7, QHeaderView.ResizeToContents)  # Categoría
        header.setSectionResizeMode(8, QHeaderView.ResizeToContents)  # Condición Pago
        header.setSectionResizeMode(9, QHeaderView.ResizeToContents)  # Estado
        header.setSectionResizeMode(10, QHeaderView.ResizeToContents)  # Calificación
        header.setSectionResizeMode(11, QHeaderView.ResizeToContents)  # Total Compras
        
        self.tbl_proveedores.setAlternatingRowColors(True)
        self.tbl_proveedores.setSelectionBehavior(QTableWidget.SelectRows)
        self.tbl_proveedores.doubleClicked.connect(self.editar_proveedor)
        
        main_layout.addWidget(self.tbl_proveedores)
    
    def apply_styles(self):
        """Aplicar estilos CSS"""
        self.setStyleSheet("""
            QMainWindow {
                background-color: #f5f5f5;
            }
            QTableWidget {
                gridline-color: #bdc3c7;
                background-color: white;
                alternate-background-color: #f8f9fa;
                font-size: 10px;
            }
            QTableWidget::item {
                padding: 5px;
                border-bottom: 1px solid #ecf0f1;
                color: #2c3e50;
            }
            QTableWidget::item:alternate {
                background-color: #f8f9fa;
                color: #2c3e50;
            }
            QHeaderView::section {
                background-color: #34495e;
                color: white;
                padding: 8px;
                border-right: 1px solid #2c3e50;
                font-size: 11px;
                font-weight: bold;
            }
            QGroupBox {
                font-size: 12px;
                font-weight: bold;
                color: #2c3e50;
                border: 2px solid #bdc3c7;
                border-radius: 5px;
                margin-top: 10px;
                padding-top: 10px;
            }
            QGroupBox::title {
                subcontrol-origin: margin;
                left: 10px;
                padding: 0 5px;
            }
            QLabel {
                color: #2c3e50;
                font-size: 10px;
            }
            QLineEdit, QComboBox {
                padding: 5px;
                border: 1px solid #bdc3c7;
                border-radius: 3px;
                font-size: 10px;
                background-color: white;
            }
        """)
    
    def cargar_estadisticas(self):
        """Cargar y mostrar estadísticas de proveedores"""
        try:
            stats = self.db.get_estadisticas_proveedores()
            
            if stats:
                self.lbl_total_proveedores.setText(str(stats.get('total_proveedores', 0)))
                self.lbl_proveedores_activos.setText(str(stats.get('proveedores_activos', 0)))
                self.lbl_proveedores_inactivos.setText(str(stats.get('proveedores_inactivos', 0)))
                self.lbl_total_compras.setText(f"S/ {stats.get('total_compras_general', 0):,.2f}")
                self.lbl_total_deuda.setText(f"S/ {stats.get('total_deuda_general', 0):,.2f}")
                self.lbl_calificacion_promedio.setText(f"{stats.get('calificacion_promedio', 0):.1f}")
        except Exception as e:
            print(f"Error cargando estadísticas: {e}")
    
    def cargar_proveedores(self):
        """Cargar proveedores en la tabla"""
        try:
            filtro = self.input_busqueda.text().strip()
            estado = self.combo_estado.currentText()
            
            proveedores = self.db.get_proveedores(filtro, estado)
            
            self.tbl_proveedores.setRowCount(len(proveedores))
            
            for row, proveedor in enumerate(proveedores):
                for col, value in enumerate(proveedor):
                    if col == 9:  # Estado
                        item = QTableWidgetItem(str(value).upper())
                        if value == 'activo':
                            item.setBackground(QColor(200, 255, 200))
                        elif value == 'inactivo':
                            item.setBackground(QColor(255, 200, 200))
                        else:  # bloqueado
                            item.setBackground(QColor(255, 255, 200))
                    elif col == 10:  # Calificación
                        item = QTableWidgetItem("⭐" * int(value))
                        item.setTextAlignment(Qt.AlignCenter)
                    elif col == 11:  # Total Compras
                        item = QTableWidgetItem(f"S/ {float(value):,.2f}")
                        item.setTextAlignment(Qt.AlignRight | Qt.AlignVCenter)
                    else:
                        item = QTableWidgetItem(str(value) if value is not None else "")
                    
                    self.tbl_proveedores.setItem(row, col, item)
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error cargando proveedores: {e}")
    
    def limpiar_filtros(self):
        """Limpiar filtros de búsqueda"""
        self.input_busqueda.clear()
        self.combo_estado.setCurrentIndex(0)
    
    def nuevo_proveedor(self):
        """Abrir formulario para nuevo proveedor"""
        dialog = ProveedorForm(self.db, self.usuario)
        if dialog.exec():
            self.cargar_proveedores()
            self.cargar_estadisticas()
    
    def editar_proveedor(self):
        """Editar proveedor seleccionado"""
        row = self.tbl_proveedores.currentRow()
        if row < 0:
            QMessageBox.warning(self, "Advertencia", "Seleccione un proveedor para editar")
            return
        
        proveedor_id = int(self.tbl_proveedores.item(row, 0).text())
        proveedor_data = self.db.get_proveedor_por_id(proveedor_id)
        
        if proveedor_data:
            dialog = ProveedorForm(self.db, self.usuario, proveedor_data)
            if dialog.exec():
                self.cargar_proveedores()
                self.cargar_estadisticas()
    
    def eliminar_proveedor(self):
        """Eliminar (desactivar) proveedor seleccionado"""
        row = self.tbl_proveedores.currentRow()
        if row < 0:
            QMessageBox.warning(self, "Advertencia", "Seleccione un proveedor para eliminar")
            return
        
        proveedor_id = int(self.tbl_proveedores.item(row, 0).text())
        razon_social = self.tbl_proveedores.item(row, 1).text()
        
        respuesta = QMessageBox.question(
            self,
            "Confirmar Eliminación",
            f"¿Está seguro de que desea desactivar al proveedor '{razon_social}'?\n\n"
            "Nota: El proveedor se marcará como inactivo, no se eliminará de la base de datos.",
            QMessageBox.Yes | QMessageBox.No
        )
        
        if respuesta == QMessageBox.Yes:
            if self.db.eliminar_proveedor(proveedor_id):
                QMessageBox.information(self, "Éxito", "Proveedor desactivado correctamente")
                self.cargar_proveedores()
                self.cargar_estadisticas()
            else:
                QMessageBox.critical(self, "Error", "No se pudo desactivar el proveedor")
    
    def ver_detalles(self):
        """Ver detalles del proveedor seleccionado"""
        row = self.tbl_proveedores.currentRow()
        if row < 0:
            QMessageBox.warning(self, "Advertencia", "Seleccione un proveedor para ver detalles")
            return
        
        proveedor_id = int(self.tbl_proveedores.item(row, 0).text())
        proveedor_data = self.db.get_proveedor_por_id(proveedor_id)
        
        if proveedor_data:
            detalles = f"""
            <h2>{proveedor_data[1]}</h2>
            <p><b>Nombre Comercial:</b> {proveedor_data[2] or 'N/A'}</p>
            <p><b>RUC:</b> {proveedor_data[3]}</p>
            <p><b>Dirección:</b> {proveedor_data[4] or 'N/A'}</p>
            <p><b>Teléfono:</b> {proveedor_data[5] or 'N/A'}</p>
            <p><b>Celular:</b> {proveedor_data[6] or 'N/A'}</p>
            <p><b>Email:</b> {proveedor_data[7] or 'N/A'}</p>
            <p><b>Web:</b> {proveedor_data[8] or 'N/A'}</p>
            <hr>
            <p><b>Persona de Contacto:</b> {proveedor_data[9] or 'N/A'}</p>
            <p><b>Cargo:</b> {proveedor_data[10] or 'N/A'}</p>
            <p><b>Teléfono Contacto:</b> {proveedor_data[11] or 'N/A'}</p>
            <p><b>Email Contacto:</b> {proveedor_data[12] or 'N/A'}</p>
            <hr>
            <p><b>Tipo:</b> {proveedor_data[13]}</p>
            <p><b>Categoría:</b> {proveedor_data[14] or 'N/A'}</p>
            <p><b>Condición de Pago:</b> {proveedor_data[15]}</p>
            <p><b>Días de Crédito:</b> {proveedor_data[16]}</p>
            <p><b>Descuento General:</b> {proveedor_data[17]}%</p>
            <p><b>Estado:</b> {proveedor_data[27]}</p>
            <p><b>Calificación:</b> {"⭐" * int(proveedor_data[28])}</p>
            <hr>
            <p><b>Total Compras:</b> S/ {float(proveedor_data[30] or 0):,.2f}</p>
            <p><b>Total Deuda:</b> S/ {float(proveedor_data[31] or 0):,.2f}</p>
            <p><b>Última Compra:</b> {proveedor_data[32] or 'N/A'}</p>
            """
            
            msg = QMessageBox(self)
            msg.setWindowTitle("Detalles del Proveedor")
            msg.setTextFormat(Qt.RichText)
            msg.setText(detalles)
            msg.exec()

