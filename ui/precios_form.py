from PySide6.QtWidgets import (
    QWidget, QVBoxLayout, QHBoxLayout, QLineEdit, QPushButton, QLabel, 
    QFormLayout, QMessageBox, QTableWidget, QTableWidgetItem, QHeaderView, 
    QRadioButton, QButtonGroup, QGroupBox, QGridLayout, QFrame, QSpacerItem, 
    QSizePolicy, QScrollArea, QComboBox, QCheckBox, QProgressBar, QDialog
)
from PySide6.QtCore import Qt, QTimer, Signal
from PySide6.QtGui import QIntValidator, QDoubleValidator, QFont, QIcon, QPixmap

class PreciosForm(QWidget):
    def __init__(self, db, usuario_actual):
        super().__init__()
        self.db = db
        self.usuario = usuario_actual
        self.producto_id = None
        self.producto = None
        self.productos_resultado = []

        self.setWindowTitle("🎯 Gestión de Precios - Sistema RMS")
        self.setGeometry(200, 200, 1000, 800)
        self.setMinimumSize(800, 600)  # Tamaño mínimo responsive
        self.setStyleSheet(self._get_styles())
        
        self.setup_ui()
        self.setup_connections()
        self.setup_validators()
        
        # Aplicar estilos a QMessageBox
        self.setStyleSheet(self.styleSheet() + self.aplicar_estilos_mensajes())

    def _get_styles(self):
        """Retorna los estilos CSS con colores azul acero más serios"""
        return """
        QWidget {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11px;
            background-color: #f5f7fa;
        }
        
        QGroupBox {
            font-weight: bold;
            font-size: 11px;
            color: #2c3e50;
            border: 2px solid #5d6d7e;
            border-radius: 8px;
            margin-top: 8px;
            padding-top: 8px;
            background-color: #e8edf4;
        }
        
        QGroupBox::title {
            subcontrol-origin: margin;
            left: 10px;
            padding: 0 8px 0 8px;
            background-color: #e8edf4;
            color: #2c3e50;
            font-weight: bold;
        }
        
        QLineEdit {
            padding: 6px 10px;
            border: 2px solid #5d6d7e;
            border-radius: 6px;
            font-size: 11px;
            background-color: white;
            color: #2c3e50;
        }
        
        QLineEdit:focus {
            border-color: #3498db;
            background-color: #ffffff;
            color: #2c3e50;
        }
        
        QLineEdit:read-only {
            background-color: #e8edf4;
            color: #6c757d;
            border-color: #5d6d7e;
        }
        
        QPushButton {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 11px;
            color: white;
        }
        
        QPushButton:hover {
            opacity: 0.9;
        }
        
        QPushButton:pressed {
            opacity: 0.8;
        }
        
        .btn-primary {
            background-color: #2c3e50;
        }
        
        .btn-success {
            background-color: #27ae60;
        }
        
        .btn-warning {
            background-color: #f39c12;
        }
        
        .btn-danger {
            background-color: #e74c3c;
        }
        
        .btn-info {
            background-color: #5d6d7e;
        }
        
        QRadioButton {
            font-size: 11px;
            color: #2c3e50;
            spacing: 6px;
            font-weight: bold;
        }
        
        QRadioButton::indicator {
            width: 16px;
            height: 16px;
        }
        
        QRadioButton::indicator:unchecked {
            border: 2px solid #5d6d7e;
            border-radius: 8px;
            background-color: white;
        }
        
        QRadioButton::indicator:checked {
            border: 2px solid #2c3e50;
            border-radius: 8px;
            background-color: #2c3e50;
        }
        
        QTableWidget {
            background-color: white;
            border: 2px solid #5d6d7e;
            border-radius: 8px;
            gridline-color: #bdc3c7;
            selection-background-color: #d6eaf8;
            selection-color: #2c3e50;
            alternate-background-color: #f8f9fa;
        }
        
        QTableWidget::item {
            padding: 6px;
            border-bottom: 1px solid #e8edf4;
            color: #2c3e50;
            font-size: 11px;
        }
        
        QTableWidget::item:selected {
            background-color: #d6eaf8;
            color: #2c3e50;
        }
        
        QHeaderView::section {
            background-color: #34495e;
            color: white;
            padding: 8px 6px;
            border: none;
            font-weight: bold;
            font-size: 10px;
        }
        
        QLabel {
            color: #2c3e50;
            font-size: 11px;
            font-weight: bold;
        }
        
        .label-title {
            font-size: 14px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .label-info {
            font-size: 10px;
            color: #5d6d7e;
            font-style: italic;
        }
        
        .label-success {
            color: #27ae60;
            font-weight: bold;
        }
        
        .label-warning {
            color: #f39c12;
            font-weight: bold;
        }
        
        .label-danger {
            color: #e74c3c;
            font-weight: bold;
        }
        
        QComboBox {
            padding: 6px 10px;
            border: 2px solid #5d6d7e;
            border-radius: 6px;
            font-size: 11px;
            background-color: white;
            color: #2c3e50;
            font-weight: bold;
        }
        
        QComboBox:focus {
            border-color: #3498db;
        }
        
        QComboBox::drop-down {
            border: none;
            width: 18px;
        }
        
        QComboBox::down-arrow {
            image: none;
            border-left: 4px solid transparent;
            border-right: 4px solid transparent;
            border-top: 4px solid #2c3e50;
            margin-right: 4px;
        }
        
        QCheckBox {
            font-size: 11px;
            color: #2c3e50;
            font-weight: bold;
            spacing: 6px;
        }
        
        QCheckBox::indicator {
            width: 16px;
            height: 16px;
        }
        
        QCheckBox::indicator:unchecked {
            border: 2px solid #5d6d7e;
            border-radius: 4px;
            background-color: white;
        }
        
        QCheckBox::indicator:checked {
            border: 2px solid #27ae60;
            border-radius: 4px;
            background-color: #27ae60;
        }
        
        QProgressBar {
            border: 2px solid #5d6d7e;
            border-radius: 6px;
            text-align: center;
            font-weight: bold;
            color: #2c3e50;
            background-color: #e8edf4;
        }
        
        QProgressBar::chunk {
            background-color: #3498db;
            border-radius: 4px;
        }
        """

    def setup_ui(self):
        """Configura la interfaz de usuario principal"""
        # Crear scroll area
        scroll_area = QScrollArea()
        scroll_area.setWidgetResizable(True)
        scroll_area.setHorizontalScrollBarPolicy(Qt.ScrollBarPolicy.ScrollBarAsNeeded)
        scroll_area.setVerticalScrollBarPolicy(Qt.ScrollBarPolicy.ScrollBarAsNeeded)
        scroll_area.setFrameShape(QScrollArea.Shape.NoFrame)  # Sin marco para mejor apariencia
        
        # Widget contenedor para el scroll
        scroll_widget = QWidget()
        scroll_widget.setSizePolicy(QSizePolicy.Policy.Expanding, QSizePolicy.Policy.Expanding)
        main_layout = QVBoxLayout(scroll_widget)
        main_layout.setSpacing(10)  # Menos espaciado para aprovechar mejor el espacio
        main_layout.setContentsMargins(15, 15, 15, 15)  # Márgenes más pequeños

        # === HEADER ===
        header_layout = QHBoxLayout()
        
        # Título principal
        title_label = QLabel("🎯 GESTIÓN DE PRECIOS")
        title_label.setStyleSheet("font-size: 18px; font-weight: bold; color: #2c3e50; margin: 8px; background: #e8edf4; padding: 8px; border-radius: 6px; border: 2px solid #5d6d7e;")
        header_layout.addWidget(title_label)
        
        # Información del usuario
        user_info = QLabel(f"👤 Usuario: {self.usuario}")
        user_info.setStyleSheet("font-size: 10px; color: #5d6d7e; margin: 8px; background: #e8edf4; padding: 6px; border-radius: 4px; font-weight: bold; border: 2px solid #5d6d7e;")
        header_layout.addWidget(user_info)
        
        main_layout.addLayout(header_layout)

        # === SECCIÓN DE BÚSQUEDA ===
        search_group = QGroupBox("🔍 Búsqueda de Productos")
        search_layout = QVBoxLayout()

        # Opciones de búsqueda
        search_options_layout = QHBoxLayout()
        search_options_layout.setSpacing(15)
        
        self.opciones_busqueda = QButtonGroup(self)

        self.radio_descripcion = QRadioButton("📝 Descripción")
        self.radio_sku = QRadioButton("🆔 SKU")
        self.radio_barra = QRadioButton("🏷️ Código de Barra")
        self.radio_marca = QRadioButton("🏭 Marca")

        self.opciones_busqueda.addButton(self.radio_descripcion)
        self.opciones_busqueda.addButton(self.radio_sku)
        self.opciones_busqueda.addButton(self.radio_barra)
        self.opciones_busqueda.addButton(self.radio_marca)

        for radio in [self.radio_descripcion, self.radio_sku, self.radio_barra, self.radio_marca]:
            search_options_layout.addWidget(radio)
            radio.setStyleSheet("margin: 5px;")
        
        self.radio_descripcion.setChecked(True)
        search_layout.addLayout(search_options_layout)
        
        # Campo de búsqueda
        search_input_layout = QHBoxLayout()
        self.input_busqueda = QLineEdit()
        self.input_busqueda.setPlaceholderText("🔍 Escriba para buscar productos...")
        self.input_busqueda.setMinimumHeight(40)
        self.input_busqueda.setStyleSheet("font-size: 14px;")
        
        # Botón de búsqueda
        self.btn_buscar = QPushButton("🔍 Buscar")
        self.btn_buscar.setMinimumHeight(35)
        self.btn_buscar.setStyleSheet("QPushButton { background: #3498db; color: white; padding: 8px; border-radius: 6px; font-weight: bold; font-size: 11px; }")
        
        search_input_layout.addWidget(self.input_busqueda, 4)
        search_input_layout.addWidget(self.btn_buscar, 1)
        search_layout.addLayout(search_input_layout)
        
        search_group.setLayout(search_layout)
        main_layout.addWidget(search_group)

        # === TABLA DE RESULTADOS ===
        results_group = QGroupBox("📊 Resultados de Búsqueda")
        results_layout = QVBoxLayout()
        
        # Información de resultados
        self.label_resultados = QLabel("0 productos encontrados")
        self.label_resultados.setStyleSheet("font-size: 10px; color: #5d6d7e; font-style: italic; background: #e8edf4; padding: 6px; border-radius: 4px; font-weight: bold; border: 2px solid #5d6d7e;")
        results_layout.addWidget(self.label_resultados)
        
        # Tabla de resultados
        self.tabla_resultados = QTableWidget(0, 8)
        self.tabla_resultados.setHorizontalHeaderLabels([
            "SKU", "Descripción", "Código Barra", "Costo", 
            "Valor Venta", "Precio Venta", "Afecto IGV", "Stock"
        ])
        
        # Configurar tabla
        header = self.tabla_resultados.horizontalHeader()
        header.setSectionResizeMode(0, QHeaderView.ResizeMode.Fixed)  # SKU
        header.setSectionResizeMode(1, QHeaderView.ResizeMode.Stretch)  # Descripción
        header.setSectionResizeMode(2, QHeaderView.ResizeMode.Fixed)  # Código Barra
        header.setSectionResizeMode(3, QHeaderView.ResizeMode.Fixed)  # Costo
        header.setSectionResizeMode(4, QHeaderView.ResizeMode.Fixed)  # Valor Venta
        header.setSectionResizeMode(5, QHeaderView.ResizeMode.Fixed)  # Precio Venta
        header.setSectionResizeMode(6, QHeaderView.ResizeMode.Fixed)  # Afecto IGV
        header.setSectionResizeMode(7, QHeaderView.ResizeMode.Fixed)  # Stock
        
        # Establecer anchos específicos
        self.tabla_resultados.setColumnWidth(0, 100)  # SKU
        self.tabla_resultados.setColumnWidth(2, 120)  # Código Barra
        self.tabla_resultados.setColumnWidth(3, 80)   # Costo
        self.tabla_resultados.setColumnWidth(4, 100)  # Valor Venta
        self.tabla_resultados.setColumnWidth(5, 100)  # Precio Venta
        self.tabla_resultados.setColumnWidth(6, 80)   # Afecto IGV
        self.tabla_resultados.setColumnWidth(7, 60)   # Stock
        
        self.tabla_resultados.verticalHeader().setVisible(False)
        self.tabla_resultados.setSelectionBehavior(QTableWidget.SelectRows)
        self.tabla_resultados.setAlternatingRowColors(True)
        self.tabla_resultados.setMinimumHeight(150)  # Altura mínima más pequeña para mejor responsividad
        self.tabla_resultados.setSizePolicy(QSizePolicy.Policy.Expanding, QSizePolicy.Policy.Expanding)
        
        results_layout.addWidget(self.tabla_resultados)
        results_group.setLayout(results_layout)
        main_layout.addWidget(results_group)

        # === FORMULARIO DE ACTUALIZACIÓN ===
        update_group = QGroupBox("✏️ Actualización de Precios")
        update_layout = QGridLayout()
        update_layout.setSpacing(8)  # Reducir espaciado
        
        # Información del producto seleccionado
        self.label_producto = QLabel("Producto: Ninguno seleccionado")
        self.label_producto.setStyleSheet("font-size: 14px; font-weight: bold; color: #2c3e50; background: #e8edf4; padding: 8px; border-radius: 6px; border: 2px solid #5d6d7e;")
        update_layout.addWidget(self.label_producto, 0, 0, 1, 4)
        
        # Primera fila - Costo y Margen (más comprimida)
        update_layout.addWidget(QLabel("Costo de Compra:"), 1, 0)
        self.input_costo_nuevo = QLineEdit()
        self.input_costo_nuevo.setPlaceholderText("0.00")
        update_layout.addWidget(self.input_costo_nuevo, 1, 1)
        
        update_layout.addWidget(QLabel("Margen de Ganancia:"), 1, 2)
        self.combo_margen = QComboBox()
        self.combo_margen.addItems(["20%", "25%", "30%", "35%", "40%", "50%", "Personalizado"])
        self.combo_margen.setCurrentText("20%")
        update_layout.addWidget(self.combo_margen, 1, 3)
        
        # Segunda fila - Valores calculados
        update_layout.addWidget(QLabel("Valor de Venta:"), 2, 0)
        self.input_valor_nuevo = QLineEdit()
        self.input_valor_nuevo.setPlaceholderText("0.00")
        self.input_valor_nuevo.setReadOnly(True)
        self.input_valor_nuevo.setStyleSheet("background-color: #e8f5e8;")
        update_layout.addWidget(self.input_valor_nuevo, 2, 1)
        
        update_layout.addWidget(QLabel("% Ganancia:"), 2, 2)
        self.input_ganancia = QLineEdit()
        self.input_ganancia.setPlaceholderText("0.00%")
        self.input_ganancia.setReadOnly(True)
        self.input_ganancia.setStyleSheet("background-color: #e8f5e8;")
        update_layout.addWidget(self.input_ganancia, 2, 3)
        
        # Tercera fila - Precio final e IGV
        update_layout.addWidget(QLabel("Precio de Venta:"), 3, 0)
        self.input_precio_nuevo = QLineEdit()
        self.input_precio_nuevo.setPlaceholderText("0.00")
        update_layout.addWidget(self.input_precio_nuevo, 3, 1)
        
        update_layout.addWidget(QLabel("IGV (18%):"), 3, 2)
        self.input_igv = QLineEdit()
        self.input_igv.setPlaceholderText("0.00")
        self.input_igv.setReadOnly(True)
        self.input_igv.setStyleSheet("background-color: #e8f5e8;")
        update_layout.addWidget(self.input_igv, 3, 3)
        
        # Cuarta fila - Afecto IGV y Checkbox
        update_layout.addWidget(QLabel("Afecto IGV:"), 4, 0)
        self.label_igv_aplica = QLabel("No")
        self.label_igv_aplica.setStyleSheet("font-size: 11px; color: #f39c12; font-weight: bold; background: #e8edf4; padding: 6px; border-radius: 4px; border: 2px solid #f39c12;")
        update_layout.addWidget(self.label_igv_aplica, 4, 1)
        
        update_layout.addWidget(QLabel("Aplicar Cambios:"), 4, 2)
        self.check_actualizar_stock = QCheckBox("Actualizar Stock")
        self.check_actualizar_stock.setChecked(True)
        update_layout.addWidget(self.check_actualizar_stock, 4, 3)
        
        update_group.setLayout(update_layout)
        main_layout.addWidget(update_group)

        # === BOTONES DE ACCIÓN ===
        buttons_layout = QHBoxLayout()
        
        # Botón limpiar
        self.btn_limpiar = QPushButton("🧹 Limpiar")
        self.btn_limpiar.setStyleSheet("QPushButton { background: #f39c12; color: white; padding: 8px; border-radius: 6px; font-weight: bold; font-size: 11px; }")
        self.btn_limpiar.setMinimumHeight(40)
        buttons_layout.addWidget(self.btn_limpiar)
        
        # Espaciador
        buttons_layout.addStretch()
        
        # Botón guardar
        self.btn_guardar = QPushButton("💾 Guardar Precios")
        self.btn_guardar.setStyleSheet("QPushButton { background: #27ae60; color: white; padding: 8px; border-radius: 6px; font-weight: bold; font-size: 11px; }")
        self.btn_guardar.setMinimumHeight(40)
        self.btn_guardar.setMinimumWidth(180)
        buttons_layout.addWidget(self.btn_guardar)
        
        # Botón historial
        self.btn_historial = QPushButton("📊 Ver Historial")
        self.btn_historial.setStyleSheet("QPushButton { background: #16a085; color: white; padding: 8px; border-radius: 6px; font-weight: bold; font-size: 11px; }")
        self.btn_historial.setMinimumHeight(40)
        buttons_layout.addWidget(self.btn_historial)
        
        main_layout.addLayout(buttons_layout)

        # === BARRA DE ESTADO ===
        self.progress_bar = QProgressBar()
        self.progress_bar.setVisible(False)
        self.progress_bar.setStyleSheet("""
            QProgressBar {
                border: 2px solid #e9ecef;
                border-radius: 6px;
                text-align: center;
                font-weight: bold;
            }
            QProgressBar::chunk {
                background-color: #3498db;
                border-radius: 4px;
            }
        """)
        main_layout.addWidget(self.progress_bar)

        # Configurar scroll area
        scroll_area.setWidget(scroll_widget)
        
        # Layout principal responsive
        main_window_layout = QVBoxLayout(self)
        main_window_layout.setContentsMargins(0, 0, 0, 0)  # Sin márgenes en el contenedor principal
        main_window_layout.addWidget(scroll_area)

    def setup_connections(self):
        """Configura las conexiones de señales"""
        # Búsqueda
        self.input_busqueda.textChanged.connect(self.buscar_productos_dinamicamente)
        self.btn_buscar.clicked.connect(self.buscar_productos_dinamicamente)

        # Radio buttons
        self.radio_descripcion.toggled.connect(self.actualizar_validacion_busqueda)
        self.radio_sku.toggled.connect(self.actualizar_validacion_busqueda)
        self.radio_barra.toggled.connect(self.actualizar_validacion_busqueda)
        self.radio_marca.toggled.connect(self.actualizar_validacion_busqueda)

        # Tabla
        self.tabla_resultados.cellClicked.connect(self.seleccionar_producto_tabla)
        
        # Campos de cálculo
        self.input_costo_nuevo.textChanged.connect(self.calcular_precio_desde_costo)
        self.input_ganancia.textChanged.connect(self.calcular_precio_desde_ganancia)
        self.input_precio_nuevo.textChanged.connect(self.calcular_ganancia_desde_precio)
        self.combo_margen.currentTextChanged.connect(self.aplicar_margen_automatico)
        
        # Botones
        self.btn_limpiar.clicked.connect(self.limpiar_formulario)
        self.btn_guardar.clicked.connect(self.actualizar_precios)
        self.btn_historial.clicked.connect(self.mostrar_historial)

    def setup_validators(self):
        """Configura los validadores de entrada"""
        # Validador para números decimales
        decimal_validator = QDoubleValidator(0.00, 999999.99, 2)
        decimal_validator.setNotation(QDoubleValidator.StandardNotation)
        
        self.input_costo_nuevo.setValidator(decimal_validator)
        self.input_precio_nuevo.setValidator(decimal_validator)
        
        # Validador para enteros
        int_validator = QIntValidator(0, 999999)
        self.input_busqueda.setValidator(int_validator)

    def actualizar_validacion_busqueda(self):
        """Actualiza la validación del campo de búsqueda según la opción seleccionada"""
        self.input_busqueda.clear()
        if self.radio_sku.isChecked() or self.radio_barra.isChecked():
            self.input_busqueda.setValidator(QIntValidator())
            self.input_busqueda.setPlaceholderText("🔍 Ingrese solo números...")
        else:
            self.input_busqueda.setValidator(None)
            self.input_busqueda.setPlaceholderText("🔍 Escriba para buscar productos...")

    def buscar_productos_dinamicamente(self):
        """Busca productos dinámicamente según el texto ingresado"""
        texto = self.input_busqueda.text().strip()
        if len(texto) < 2:
            self.tabla_resultados.setRowCount(0)
            self.label_resultados.setText("0 productos encontrados")
            return

        try:
            # Mostrar progreso
            self.progress_bar.setVisible(True)
            self.progress_bar.setRange(0, 0)  # Indeterminado
            
            # Realizar búsqueda según la opción seleccionada
            if self.radio_sku.isChecked():
                resultados = self.db.buscar_producto_por_sku(texto)
            elif self.radio_barra.isChecked():
                resultados = self.db.buscar_producto_por_barra(texto)
            elif self.radio_marca.isChecked():
                resultados = self.db.buscar_producto_por_marca(texto)
            else:
                resultados = self.db.buscar_producto_por_descripcion(texto)

            # Actualizar tabla
            self.actualizar_tabla_resultados(resultados)
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error en la búsqueda: {str(e)}")
        finally:
            self.progress_bar.setVisible(False)

    def actualizar_tabla_resultados(self, resultados):
        """Actualiza la tabla de resultados con los productos encontrados"""
        self.productos_resultado = resultados
        self.tabla_resultados.setRowCount(len(resultados))
        
        for row, prod in enumerate(resultados):
            # SKU
            sku_item = QTableWidgetItem(prod["sku"])
            sku_item.setTextAlignment(Qt.AlignCenter)
            self.tabla_resultados.setItem(row, 0, sku_item)
            
            # Descripción
            desc_item = QTableWidgetItem(prod["descripcion_larga"])
            if len(prod["descripcion_larga"]) > 50:
                desc_item.setToolTip(prod["descripcion_larga"])
            self.tabla_resultados.setItem(row, 1, desc_item)
            
            # Código de barras
            barra_item = QTableWidgetItem(prod["codigo_barra"])
            barra_item.setTextAlignment(Qt.AlignCenter)
            self.tabla_resultados.setItem(row, 2, barra_item)
            
            # Costo
            costo_item = QTableWidgetItem(f"S/. {float(prod['costo_compra'] or 0):.2f}")
            costo_item.setTextAlignment(Qt.AlignRight | Qt.AlignVCenter)
            self.tabla_resultados.setItem(row, 3, costo_item)
            
            # Valor venta
            valor_item = QTableWidgetItem(f"S/. {float(prod['valor_venta'] or 0):.2f}")
            valor_item.setTextAlignment(Qt.AlignRight | Qt.AlignVCenter)
            self.tabla_resultados.setItem(row, 4, valor_item)
            
            # Precio venta
            precio_item = QTableWidgetItem(f"S/. {float(prod['precio_venta'] or 0):.2f}")
            precio_item.setTextAlignment(Qt.AlignRight | Qt.AlignVCenter)
            self.tabla_resultados.setItem(row, 5, precio_item)
            
            # Afecto IGV
            afecto_item = QTableWidgetItem("Sí" if prod.get("afecto_igv", True) else "No")
            afecto_item.setTextAlignment(Qt.AlignCenter)
            if prod.get("afecto_igv", True):
                afecto_item.setBackground(Qt.green)
                afecto_item.setForeground(Qt.white)
            else:
                afecto_item.setBackground(Qt.red)
                afecto_item.setForeground(Qt.white)
            self.tabla_resultados.setItem(row, 6, afecto_item)
            
            # Stock
            stock_disponible = float(prod.get('stock_disponible', 0) or 0)
            stock_item = QTableWidgetItem(f"{stock_disponible:.0f}")
            stock_item.setTextAlignment(Qt.AlignCenter)
            
            # Colorear según el nivel de stock
            if stock_disponible <= 0:
                stock_item.setBackground(Qt.red)
                stock_item.setForeground(Qt.white)
            elif stock_disponible <= 10:
                stock_item.setBackground(Qt.yellow)
                stock_item.setForeground(Qt.black)
            else:
                stock_item.setBackground(Qt.green)
                stock_item.setForeground(Qt.white)
                
            self.tabla_resultados.setItem(row, 7, stock_item)
            
            # Altura de fila
            self.tabla_resultados.setRowHeight(row, 35)
        
        # Actualizar contador
        self.label_resultados.setText(f"{len(resultados)} productos encontrados")

    def seleccionar_producto_tabla(self, row, column):
        """Maneja la selección de un producto en la tabla"""
        if row >= len(self.productos_resultado):
            return
            
        producto = self.productos_resultado[row]
        self.producto_id = producto["id"]
        self.producto = producto
        
        # Actualizar información del producto
        afecto = "Sí" if producto.get("afecto_igv", True) else "No"
        self.label_igv_aplica.setText(afecto)
        if afecto == "Sí":
            self.label_igv_aplica.setStyleSheet("font-size: 11px; color: #27ae60; font-weight: bold; background: #e8edf4; padding: 6px; border-radius: 4px; border: 2px solid #27ae60;")
        else:
            self.label_igv_aplica.setStyleSheet("font-size: 11px; color: #e74c3c; font-weight: bold; background: #e8edf4; padding: 6px; border-radius: 4px; border: 2px solid #e74c3c;")
        
        # Mostrar información del producto
        descripcion_corta = producto['descripcion_larga'][:50] + "..." if len(producto['descripcion_larga']) > 50 else producto['descripcion_larga']
        self.label_producto.setText(f"🆔 {producto['sku']} - {descripcion_corta}")
        
        # Cargar valores actuales
        self.input_costo_nuevo.setText(f"{float(producto['costo_compra'] or 0):.2f}")
        
        # Calcular automáticamente
        self.calcular_precio_desde_costo()
        
        # Enfocar campo de costo
        self.input_costo_nuevo.setFocus()
        self.input_costo_nuevo.selectAll()

    def aplicar_margen_automatico(self, margen_texto):
        """Aplica automáticamente el margen seleccionado"""
        if not self.producto_id or margen_texto == "Personalizado":
            return
            
        try:
            margen_porcentaje = float(margen_texto.replace("%", "")) / 100
            costo = float(self.input_costo_nuevo.text() or 0)
            
            if costo > 0:
                valor_venta = round(costo * (1 + margen_porcentaje), 2)
                self.input_valor_nuevo.setText(f"{valor_venta:.2f}")
                self.calcular_precio_desde_costo()
                
        except ValueError:
            pass

    def calcular_precio_desde_costo(self):
        """Calcula el precio desde el costo ingresado"""
        try:
            costo = float(self.input_costo_nuevo.text() or 0)
            if costo <= 0:
                self.limpiar_campos_calculo()
                return 
            
            # Obtener margen del combo
            margen_texto = self.combo_margen.currentText()
            if margen_texto != "Personalizado":
                margen_porcentaje = float(margen_texto.replace("%", "")) / 100
                valor_venta = round(costo * (1 + margen_porcentaje), 2)
            else:
                # Usar margen del 20% por defecto
                valor_venta = round(costo * 1.20, 2)
            
            # Calcular ganancia
            ganancia = round(((valor_venta / costo) - 1) * 100, 2)
            
            # Verificar si aplica IGV
            afecto_igv = self.label_igv_aplica.text().strip() == "Sí"
            
            if afecto_igv:
                igv = round(valor_venta * 0.18, 2)
                precio_final = round(valor_venta + igv, 2)
            else:
                igv = 0.0
                precio_final = valor_venta
            
            # Actualizar campos
            self.input_valor_nuevo.setText(f"{valor_venta:.2f}")
            self.input_ganancia.setText(f"{ganancia:.2f}%")
            self.input_igv.setText(f"{igv:.2f}")
            self.input_precio_nuevo.setText(f"{precio_final:.2f}")
            
        except ValueError:
            self.limpiar_campos_calculo()

    def calcular_precio_desde_ganancia(self):
        """Calcula el precio desde el porcentaje de ganancia"""
        self.calcular_ganancia_desde_precio()

    def calcular_ganancia_desde_precio(self):
        """Calcula la ganancia desde el precio de venta"""
        try:
            precio = float(self.input_precio_nuevo.text() or 0)
            costo = float(self.input_costo_nuevo.text() or 0)
            
            if precio <= 0 or costo <= 0:
                self.limpiar_campos_calculo()
                return
            
            # Calcular valor de venta (sin IGV)
            afecto_igv = self.label_igv_aplica.text().strip() == "Sí"
            
            if afecto_igv:
                valor_venta = round(precio / 1.18, 2)
                igv = round(valor_venta * 0.18, 2)
            else:
                valor_venta = precio
                igv = 0.0
            
            # Calcular ganancia
            ganancia = round(((valor_venta / costo) - 1) * 100, 2)
            
            # Actualizar campos
            self.input_valor_nuevo.setText(f"{valor_venta:.2f}")
            self.input_ganancia.setText(f"{ganancia:.2f}%")
            self.input_igv.setText(f"{igv:.2f}")
            
        except ValueError:
            self.limpiar_campos_calculo()

    def limpiar_campos_calculo(self):
        """Limpia los campos de cálculo"""
        self.input_valor_nuevo.setText("0.00")
        self.input_ganancia.setText("0.00%")
        self.input_igv.setText("0.00")
        self.input_precio_nuevo.setText("0.00")

    def limpiar_formulario(self):
        """Limpia completamente el formulario"""
        # Limpiar búsqueda
        self.input_busqueda.clear()
        self.tabla_resultados.setRowCount(0)
        self.label_resultados.setText("0 productos encontrados")
        
        # Limpiar formulario
        self.input_costo_nuevo.clear()
        self.input_valor_nuevo.clear()
        self.input_precio_nuevo.clear()
        self.input_igv.clear()
        self.input_ganancia.clear()
        
        # Resetear selección
        self.label_producto.setText("Producto: Ninguno seleccionado")
        self.label_igv_aplica.setText("No")
        self.label_igv_aplica.setStyleSheet("font-size: 11px; color: #f39c12; font-weight: bold; background: #e8edf4; padding: 6px; border-radius: 4px; border: 2px solid #f39c12;")
        self.producto_id = None
        self.producto = None
        
        # Resetear combo
        self.combo_margen.setCurrentText("20%")
        
        # Enfocar búsqueda
        self.input_busqueda.setFocus()

    def mostrar_historial(self):
        """Muestra el historial de cambios de precios"""
        if not self.producto_id:
            QMessageBox.information(self, "Información", "Primero seleccione un producto para ver su historial.")
            return
        
        try:
            # Crear diálogo de historial
            historial_dialog = QDialog(self)
            historial_dialog.setWindowTitle(f"📊 Historial de Precios - {self.producto['sku']}")
            historial_dialog.setModal(True)
            historial_dialog.setMinimumSize(800, 600)
            historial_dialog.setStyleSheet(self._get_styles())
            
            # Layout principal
            layout = QVBoxLayout(historial_dialog)
            
            # Header con información del producto
            header_layout = QHBoxLayout()
            
            # Información del producto
            producto_info = QLabel(f"🆔 {self.producto['sku']} - {self.producto['descripcion_larga'][:60]}...")
            producto_info.setStyleSheet("font-size: 14px; font-weight: bold; color: #2c3e50; background: #ecf0f1; padding: 10px; border-radius: 6px; border: 2px solid #bdc3c7;")
            header_layout.addWidget(producto_info)
            
            # Botón cerrar
            btn_cerrar = QPushButton("❌ Cerrar")
            btn_cerrar.setStyleSheet("QPushButton { background: #e74c3c; color: white; padding: 8px; border-radius: 6px; font-weight: bold; font-size: 11px; }")
            btn_cerrar.clicked.connect(historial_dialog.accept)
            header_layout.addWidget(btn_cerrar)
            
            layout.addLayout(header_layout)
            
            # Tabla de historial
            historial_group = QGroupBox("📈 Historial de Cambios de Precios")
            historial_layout = QVBoxLayout()
            
            # Crear tabla de historial
            tabla_historial = QTableWidget()
            tabla_historial.setColumnCount(8)
            tabla_historial.setHorizontalHeaderLabels([
                "Fecha", "Usuario", "Costo Anterior", "Costo Nuevo", 
                "Valor Venta Anterior", "Valor Venta Nuevo",
                "Precio Venta Anterior", "Precio Venta Nuevo"
            ])
            
            # Configurar tabla
            header = tabla_historial.horizontalHeader()
            header.setSectionResizeMode(0, QHeaderView.ResizeMode.Fixed)  # Fecha
            header.setSectionResizeMode(1, QHeaderView.ResizeMode.Fixed)  # Usuario
            header.setSectionResizeMode(2, QHeaderView.ResizeMode.Fixed)  # Costo Anterior
            header.setSectionResizeMode(3, QHeaderView.ResizeMode.Fixed)  # Costo Nuevo
            header.setSectionResizeMode(4, QHeaderView.ResizeMode.Fixed)  # Valor Venta Anterior
            header.setSectionResizeMode(5, QHeaderView.ResizeMode.Fixed)  # Valor Venta Nuevo
            header.setSectionResizeMode(6, QHeaderView.ResizeMode.Fixed)  # Precio Venta Anterior
            header.setSectionResizeMode(7, QHeaderView.ResizeMode.Fixed)  # Precio Venta Nuevo
            
            # Establecer anchos específicos
            tabla_historial.setColumnWidth(0, 120)  # Fecha
            tabla_historial.setColumnWidth(1, 100)  # Usuario
            tabla_historial.setColumnWidth(2, 100)  # Costo Anterior
            tabla_historial.setColumnWidth(3, 100)  # Costo Nuevo
            tabla_historial.setColumnWidth(4, 120)  # Valor Venta Anterior
            tabla_historial.setColumnWidth(5, 120)  # Valor Venta Nuevo
            tabla_historial.setColumnWidth(6, 120)  # Precio Venta Anterior
            tabla_historial.setColumnWidth(7, 120)  # Precio Venta Nuevo
            
            tabla_historial.verticalHeader().setVisible(False)
            tabla_historial.setSelectionBehavior(QTableWidget.SelectRows)
            tabla_historial.setAlternatingRowColors(True)
            tabla_historial.setMinimumHeight(300)
            
            historial_layout.addWidget(tabla_historial)
            historial_group.setLayout(historial_layout)
            layout.addWidget(historial_group)
            
            # Cargar datos del historial
            self._cargar_historial_en_tabla(tabla_historial)
            
            # Mostrar diálogo
            historial_dialog.exec()
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error al mostrar historial: {str(e)}")
    
    def _cargar_historial_en_tabla(self, tabla):
        """Carga el historial de precios en la tabla especificada"""
        try:
            # Obtener historial desde la tabla producto_precio
            historial = self._obtener_historial_desde_bd()
            
            if not historial:
                # Si no hay historial, mostrar mensaje
                tabla.setRowCount(1)
                tabla.setItem(0, 0, QTableWidgetItem("Sin historial disponible"))
                # Combinar todas las columnas para el mensaje
                tabla.setSpan(0, 0, 1, 8)
                return
            
            # Configurar tabla con datos
            tabla.setRowCount(len(historial))
            
            for row, registro in enumerate(historial):
                # Fecha
                fecha_item = QTableWidgetItem(registro.get("fecha", "N/A"))
                fecha_item.setTextAlignment(Qt.AlignCenter)
                tabla.setItem(row, 0, fecha_item)
                
                # Usuario
                usuario_item = QTableWidgetItem(registro.get("usuario_actualizacion", "N/A"))
                usuario_item.setTextAlignment(Qt.AlignCenter)
                tabla.setItem(row, 1, usuario_item)
                
                # Costo Anterior
                costo_ant_item = QTableWidgetItem(f"S/. {float(registro.get('costo_anterior', 0)):.2f}")
                costo_ant_item.setTextAlignment(Qt.AlignRight | Qt.AlignVCenter)
                tabla.setItem(row, 2, costo_ant_item)
                
                # Costo Nuevo
                costo_nuevo_item = QTableWidgetItem(f"S/. {float(registro.get('costo_nuevo', 0)):.2f}")
                costo_nuevo_item.setTextAlignment(Qt.AlignRight | Qt.AlignVCenter)
                tabla.setItem(row, 3, costo_nuevo_item)
                
                # Valor Venta Anterior
                valor_ant_item = QTableWidgetItem(f"S/. {float(registro.get('valor_venta_anterior', 0)):.2f}")
                valor_ant_item.setTextAlignment(Qt.AlignRight | Qt.AlignVCenter)
                tabla.setItem(row, 4, valor_ant_item)
                
                # Valor Venta Nuevo
                valor_nuevo_item = QTableWidgetItem(f"S/. {float(registro.get('valor_venta_nuevo', 0)):.2f}")
                valor_nuevo_item.setTextAlignment(Qt.AlignRight | Qt.AlignVCenter)
                tabla.setItem(row, 5, valor_nuevo_item)
                
                # Precio Venta Anterior
                precio_ant_item = QTableWidgetItem(f"S/. {float(registro.get('precio_venta_anterior', 0)):.2f}")
                precio_ant_item.setTextAlignment(Qt.AlignRight | Qt.AlignVCenter)
                tabla.setItem(row, 6, precio_ant_item)
                
                # Precio Venta Nuevo
                precio_nuevo_item = QTableWidgetItem(f"S/. {float(registro.get('precio_venta_nuevo', 0)):.2f}")
                precio_nuevo_item.setTextAlignment(Qt.AlignRight | Qt.AlignVCenter)
                tabla.setItem(row, 7, precio_nuevo_item)
                
                # Altura de fila
                tabla.setRowHeight(row, 30)
                
                # Colorear cambios significativos
                if float(registro.get('precio_venta_nuevo', 0)) > float(registro.get('precio_venta_anterior', 0)):
                    # Precio aumentó - color verde claro
                    for col in range(8):
                        tabla.item(row, col).setBackground(Qt.green)
                        tabla.item(row, col).setForeground(Qt.black)
                elif float(registro.get('precio_venta_nuevo', 0)) < float(registro.get('precio_venta_anterior', 0)):
                    # Precio disminuyó - color rojo claro
                    for col in range(8):
                        tabla.item(row, col).setBackground(Qt.red)
                        tabla.item(row, col).setForeground(Qt.white)
            
        except Exception as e:
            # En caso de error, mostrar mensaje
            tabla.setRowCount(1)
            tabla.setItem(0, 0, QTableWidgetItem(f"Error al cargar historial: {str(e)}"))
            # Combinar todas las columnas para el mensaje de error
            tabla.setSpan(0, 0, 1, 8)
    
    def _obtener_historial_desde_bd(self):
        """Obtiene el historial real desde la tabla producto_precio"""
        try:
            # Usar el método de DBClient para obtener historial
            resultados = self.db.obtener_historial_precios(self.producto_id)
            
            # Convertir resultados a formato de diccionario
            historial = []
            if resultados:
                for row in resultados:
                    historial.append({
                        "fecha": row[0].strftime("%d/%m/%Y %H:%M") if row[0] else "N/A",
                        "usuario_actualizacion": row[1] or "N/A",
                        "costo_anterior": float(row[2] or 0),
                        "costo_nuevo": float(row[3] or 0),
                        "valor_venta_anterior": float(row[4] or 0),
                        "valor_venta_nuevo": float(row[5] or 0),
                        "precio_venta_anterior": float(row[6] or 0),
                        "precio_venta_nuevo": float(row[7] or 0)
                    })
            
            return historial
            
        except Exception as e:
            print(f"Error al obtener historial desde BD: {e}")
            # En caso de error, retornar lista vacía
            return []

    def actualizar_precios(self):
        """Actualiza los precios del producto seleccionado"""
        if not self.producto_id:
            QMessageBox.warning(self, "Advertencia", "Primero seleccione un producto.")
            return
        
        try:
            # Validar campos
            costo_nuevo = float(self.input_costo_nuevo.text() or 0)
            valor_venta_nuevo = float(self.input_valor_nuevo.text() or 0)
            precio_venta_nuevo = float(self.input_precio_nuevo.text() or 0)
            
            if costo_nuevo <= 0 or valor_venta_nuevo <= 0 or precio_venta_nuevo <= 0:
                QMessageBox.warning(self, "Validación", "Todos los campos deben tener valores mayores a 0.")
                return

            # Calcular IGV
            afecto_igv = self.label_igv_aplica.text().strip() == "Sí"
            if afecto_igv:
                valor_igv = round(valor_venta_nuevo * 0.18, 2)
            else:
                valor_igv = 0.0

            # Confirmar actualización con diálogo personalizado
            respuesta = self.mostrar_dialogo_confirmacion(
                f"¿Está seguro de actualizar los precios del producto?\n\n"
                f"SKU: {self.producto['sku']}\n"
                f"Nuevo Costo: S/. {costo_nuevo:.2f}\n"
                f"Nuevo Precio: S/. {precio_venta_nuevo:.2f}"
            )
            
            if not respuesta:
                return

            # Mostrar progreso
            self.progress_bar.setVisible(True)
            self.progress_bar.setRange(0, 100)
            self.progress_bar.setValue(25)

            # Actualizar precios en la base de datos
            nuevos_precios = {
                'costo_promedio': costo_nuevo,
                'costo_nuevo': costo_nuevo,
                'valor_venta': valor_venta_nuevo,
                'precio_venta': precio_venta_nuevo,
                'valor_igv': valor_igv,
                'afecto_igv': afecto_igv,
                'usuario_actualizacion': self.usuario
            }
            
            self.db.actualizar_precios_producto(self.producto_id, nuevos_precios)
            
            self.progress_bar.setValue(50)

            # Guardar historial de precios
            self.db.guardar_historial_precios(
                producto_id=self.producto_id,
                descripcion_larga=self.producto["descripcion_larga"],
                costos={
                    "costo_anterior": self.producto.get("costo_compra", 0),
                    "costo_nuevo": costo_nuevo,
                    "costo_promedio": costo_nuevo,
                    "valor_venta_anterior": self.producto.get("valor_venta", 0),
                    "precio_venta_anterior": self.producto.get("precio_venta", 0),
                    "valor_venta_nuevo": valor_venta_nuevo,
                    "precio_venta_nuevo": precio_venta_nuevo,
                    "usuario_actualizacion": self.usuario
                }
            )

            self.progress_bar.setValue(100)
            
            # Mensaje de éxito
            QMessageBox.information(
                self, 
                "✅ Éxito", 
                f"Precios actualizados correctamente para:\n\n"
                f"🆔 SKU: {self.producto['sku']}\n"
                f"📝 Producto: {self.producto['descripcion_larga'][:50]}...\n"
                f"💰 Nuevo Precio: S/. {precio_venta_nuevo:.2f}\n"
                f"📊 Margen: {self.input_ganancia.text()}"
            )
            
            # Limpiar formulario
            self.limpiar_formulario()
            
            # Actualizar tabla
            self.buscar_productos_dinamicamente()
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error al actualizar precios: {str(e)}")
        finally:
            self.progress_bar.setVisible(False)

    def mostrar_dialogo_confirmacion(self, mensaje):
        """Muestra un diálogo de confirmación personalizado con mejor contraste"""
        from PySide6.QtWidgets import QDialog, QVBoxLayout, QHBoxLayout, QLabel, QPushButton
        
        dialog = QDialog(self)
        dialog.setWindowTitle("Confirmar Actualización")
        dialog.setModal(True)
        dialog.setFixedSize(450, 280)
        
        # Aplicar estilos al diálogo
        dialog.setStyleSheet("""
            QDialog {
                background-color: #ffffff;
                color: #2c3e50;
            }
            QLabel {
                color: #2c3e50;
                font-size: 12px;
                padding: 5px;
            }
            QPushButton {
                background-color: #3498db;
                color: white;
                font-weight: bold;
                font-size: 12px;
                padding: 12px 24px;
                border: 2px solid #2980b9;
                border-radius: 8px;
                min-width: 100px;
            }
            QPushButton:hover {
                background-color: #2980b9;
                border-color: #21618c;
            }
            QPushButton:pressed {
                background-color: #21618c;
                border-color: #1b4f72;
            }
            QPushButton#btn_no {
                background-color: #e74c3c;
                border-color: #c0392b;
            }
            QPushButton#btn_no:hover {
                background-color: #c0392b;
                border-color: #a93226;
            }
            QPushButton#btn_no:pressed {
                background-color: #a93226;
                border-color: #922b21;
            }
        """)
        
        layout = QVBoxLayout(dialog)
        layout.setSpacing(20)
        layout.setContentsMargins(30, 30, 30, 30)
        
        # Icono y mensaje
        icon_label = QLabel("⚠️")
        icon_label.setStyleSheet("font-size: 48px; color: #f39c12; background: transparent;")
        icon_label.setAlignment(Qt.AlignmentFlag.AlignCenter)
        layout.addWidget(icon_label)
        
        # Mensaje principal
        mensaje_label = QLabel(mensaje)
        mensaje_label.setWordWrap(True)
        mensaje_label.setAlignment(Qt.AlignmentFlag.AlignCenter)
        mensaje_label.setStyleSheet("""
            font-size: 14px; 
            font-weight: bold; 
            color: #2c3e50; 
            background: #ecf0f1; 
            padding: 15px; 
            border-radius: 8px;
            border: 2px solid #bdc3c7;
        """)
        layout.addWidget(mensaje_label)
        
        # Botones
        botones_layout = QHBoxLayout()
        botones_layout.addStretch()
        
        btn_no = QPushButton("❌ No")
        btn_no.setObjectName("btn_no")
        btn_no.clicked.connect(dialog.reject)
        botones_layout.addWidget(btn_no)
        
        btn_si = QPushButton("✅ Sí")
        btn_si.clicked.connect(dialog.accept)
        botones_layout.addWidget(btn_si)
        
        botones_layout.addStretch()
        layout.addLayout(botones_layout)
        
        return dialog.exec() == QDialog.DialogCode.Accepted

    def aplicar_estilos_mensajes(self):
        """Aplica estilos consistentes a todos los QMessageBox"""
        # Estilos para QMessageBox
        style = """
            QMessageBox {
                background-color: #ffffff;
                color: #2c3e50;
            }
            QMessageBox QLabel {
                color: #2c3e50;
                font-size: 12px;
                font-weight: bold;
                background-color: #ecf0f1;
                padding: 10px;
                border-radius: 6px;
                border: 1px solid #bdc3c7;
            }
            QMessageBox QPushButton {
                background-color: #3498db;
                color: white;
                font-weight: bold;
                font-size: 11px;
                padding: 10px 20px;
                border: 2px solid #2980b9;
                border-radius: 6px;
                min-width: 80px;
            }
            QMessageBox QPushButton:hover {
                background-color: #2980b9;
                border-color: #21618c;
            }
            QMessageBox QPushButton:pressed {
                background-color: #21618c;
                border-color: #1b4f72;
            }
        """
        return style