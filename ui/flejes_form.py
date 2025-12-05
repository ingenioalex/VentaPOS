from PySide6.QtWidgets import (
    QMainWindow, QWidget, QVBoxLayout, QHBoxLayout, QGroupBox, QLabel,
    QLineEdit, QPushButton, QTableWidget, QTableWidgetItem, QHeaderView,
    QSpinBox, QComboBox, QMessageBox, QFormLayout, QCheckBox, QScrollArea
)
from PySide6.QtGui import QFont, QPainter, QPixmap, QColor, QPen
from PySide6.QtCore import Qt, QSize
from PySide6.QtPrintSupport import QPrinter, QPrintPreviewDialog


class FlejesForm(QMainWindow):
    """Módulo de impresión de flejes/etiquetas de códigos de barras.
    Rediseñado para parecerse al sistema SISMARKET.
    """

    def __init__(self, db_client, usuario_actual, main_window=None):
        super().__init__()
        self.db = db_client
        self.usuario = usuario_actual
        self.main_window = main_window
        self.producto_seleccionado = None
        self.setWindowTitle("IMPRESION DE CODIGO DE BARRAS")
        self.setGeometry(100, 100, 1200, 800)
        self._init_ui()
        self._apply_styles()

    def _init_ui(self):
        central = QWidget()
        self.setCentralWidget(central)
        layout = QVBoxLayout(central)
        layout.setSpacing(10)
        layout.setContentsMargins(10, 10, 10, 10)

        # ============= BARRA DE HERRAMIENTAS / CABECERA =============
        toolbar_group = QGroupBox()
        toolbar_group.setFixedHeight(60)
        toolbar_layout = QHBoxLayout(toolbar_group)
        toolbar_layout.setContentsMargins(10, 5, 10, 5)
        toolbar_layout.setSpacing(10)

        # Botones de acción en la cabecera (estilo toolbar)
        self.btn_buscar = QPushButton("🔍")
        self.btn_buscar.setToolTip("Buscar Producto")
        self.btn_buscar.setFixedSize(40, 40)
        self.btn_buscar.clicked.connect(self._buscar)

        self.btn_refrescar = QPushButton("🔄")
        self.btn_refrescar.setToolTip("Refrescar")
        self.btn_refrescar.setFixedSize(40, 40)
        self.btn_refrescar.clicked.connect(self._refrescar_lista)

        # Campo de búsqueda
        self.input_busqueda = QLineEdit()
        self.input_busqueda.setPlaceholderText("Buscar por código, descripción o código de barras...")
        self.input_busqueda.setMinimumHeight(35)
        self.input_busqueda.returnPressed.connect(self._buscar)

        toolbar_layout.addWidget(self.btn_buscar)
        toolbar_layout.addWidget(self.btn_refrescar)
        toolbar_layout.addWidget(self.input_busqueda)
        toolbar_layout.addStretch()

        layout.addWidget(toolbar_group)

        # ============= TABLA DE PRODUCTOS =============
        tabla_group = QGroupBox("Lista de Productos")
        tabla_layout = QVBoxLayout(tabla_group)

        self.tbl_productos = QTableWidget()
        self.tbl_productos.setColumnCount(8)
        self.tbl_productos.setHorizontalHeaderLabels([
            "Codigo", "Cód. de Barras", "Descripción Larga", "Descripcion Corta",
            "UMed", "PVenta", "POferta", "Cos"
        ])
        self.tbl_productos.setSelectionBehavior(QTableWidget.SelectRows)
        self.tbl_productos.setSelectionMode(QTableWidget.SingleSelection)
        self.tbl_productos.itemSelectionChanged.connect(self._on_producto_seleccionado)
        self.tbl_productos.setAlternatingRowColors(True)

        h = self.tbl_productos.horizontalHeader()
        h.setSectionResizeMode(0, QHeaderView.ResizeToContents)  # Codigo
        h.setSectionResizeMode(1, QHeaderView.ResizeToContents)  # Cód. de Barras
        h.setSectionResizeMode(2, QHeaderView.Stretch)  # Descripción Larga
        h.setSectionResizeMode(3, QHeaderView.ResizeToContents)  # Descripcion Corta
        h.setSectionResizeMode(4, QHeaderView.ResizeToContents)  # UMed
        h.setSectionResizeMode(5, QHeaderView.ResizeToContents)  # PVenta
        h.setSectionResizeMode(6, QHeaderView.ResizeToContents)  # POferta
        h.setSectionResizeMode(7, QHeaderView.ResizeToContents)  # Cos

        tabla_layout.addWidget(self.tbl_productos)
        layout.addWidget(tabla_group)

        # ============= SECCIÓN DE DETALLES Y CONFIGURACIÓN =============
        detalles_config_layout = QHBoxLayout()

        # Panel izquierdo: Detalles del producto seleccionado
        detalles_group = QGroupBox("Detalles del Producto")
        detalles_group.setMinimumWidth(500)
        detalles_layout = QFormLayout(detalles_group)
        detalles_layout.setSpacing(10)

        self.label_codigo_interno = QLabel("")
        self.label_codigo_interno.setStyleSheet("color: #000000; font-weight: bold;")
        detalles_layout.addRow("Codigo Interno:", self.label_codigo_interno)

        # Código de barras con checkbox
        cod_barras_layout = QHBoxLayout()
        self.check_codigo_barras = QCheckBox()
        self.check_codigo_barras.setChecked(True)
        self.label_codigo_barras = QLabel("")
        self.label_codigo_barras.setStyleSheet("color: #000000; font-weight: bold;")
        cod_barras_layout.addWidget(self.check_codigo_barras)
        cod_barras_layout.addWidget(self.label_codigo_barras)
        cod_barras_layout.addStretch()
        detalles_layout.addRow("Cod. Barras:", cod_barras_layout)

        self.label_desc_larga = QLabel("")
        self.label_desc_larga.setStyleSheet("color: #000000;")
        detalles_layout.addRow("Descripcion Larga:", self.label_desc_larga)

        self.label_desc_corta = QLabel("")
        self.label_desc_corta.setStyleSheet("color: #000000;")
        detalles_layout.addRow("Descripcion Corta:", self.label_desc_corta)

        self.label_u_med = QLabel("")
        self.label_u_med.setStyleSheet("color: #000000;")
        detalles_layout.addRow("U_Med:", self.label_u_med)

        self.label_precio = QLabel("")
        self.label_precio.setStyleSheet("color: #000000; font-weight: bold; font-size: 12px;")
        detalles_layout.addRow("Precio:", self.label_precio)

        # Cantidad a imprimir
        self.spin_cantidad = QSpinBox()
        self.spin_cantidad.setMinimum(1)
        self.spin_cantidad.setMaximum(9999)
        self.spin_cantidad.setValue(1)
        self.spin_cantidad.setMinimumWidth(100)
        detalles_layout.addRow("Cant.:", self.spin_cantidad)

        # Vista previa del código de barras
        self.label_vista_previa = QLabel()
        self.label_vista_previa.setMinimumSize(300, 100)
        self.label_vista_previa.setMaximumSize(350, 120)
        self.label_vista_previa.setAlignment(Qt.AlignCenter)
        self.label_vista_previa.setScaledContents(False)  # Importante para mostrar pixmaps
        self.label_vista_previa.setStyleSheet("""
            QLabel {
                border: 2px solid #34495e;
                border-radius: 5px;
                background-color: white;
                padding: 10px;
                color: #000000;
            }
        """)
        self.label_vista_previa.setText("Seleccione un producto para ver el código de barras")
        detalles_layout.addRow("Vista Previa:", self.label_vista_previa)

        detalles_config_layout.addWidget(detalles_group)

        # Panel derecho: Configuración de impresión
        config_group = QGroupBox("Configuración de Impresión")
        config_group.setMinimumWidth(400)
        config_layout = QFormLayout(config_group)
        config_layout.setSpacing(10)

        # Tamaño del fleje
        self.combo_tamano_fleje = QComboBox()
        self.combo_tamano_fleje.addItems([
            "Pequeño (50x25 mm)",
            "Mediano (62x30 mm)",
            "Grande (90x50 mm)"
        ])
        self.combo_tamano_fleje.currentIndexChanged.connect(self._actualizar_vista_previa)
        config_layout.addRow("Tamaño del Fleje:", self.combo_tamano_fleje)

        # Filas por columna
        self.spin_filas_columna = QSpinBox()
        self.spin_filas_columna.setMinimum(1)
        self.spin_filas_columna.setMaximum(10)
        self.spin_filas_columna.setValue(1)
        config_layout.addRow("Filas por Columna:", self.spin_filas_columna)

        # Columnas por fila
        self.spin_columnas = QSpinBox()
        self.spin_columnas.setMinimum(1)
        self.spin_columnas.setMaximum(5)
        self.spin_columnas.setValue(3)
        config_layout.addRow("Columnas por Fila:", self.spin_columnas)

        # Formato de código de barras
        self.combo_formato = QComboBox()
        self.combo_formato.addItems([
            "Texto",
            "Code128 (gráfico)",
            "EAN-13 (gráfico)"
        ])
        self.combo_formato.currentIndexChanged.connect(self._actualizar_vista_previa)
        config_layout.addRow("Formato:", self.combo_formato)

        # Mostrar descripción en etiqueta
        self.check_mostrar_desc = QCheckBox()
        self.check_mostrar_desc.setChecked(True)
        config_layout.addRow("Mostrar Descripción:", self.check_mostrar_desc)

        # Mostrar precio en etiqueta
        self.check_mostrar_precio = QCheckBox()
        self.check_mostrar_precio.setChecked(True)
        config_layout.addRow("Mostrar Precio:", self.check_mostrar_precio)

        # Modo de impresión
        self.check_impresion_continua = QCheckBox()
        self.check_impresion_continua.setChecked(True)
        self.check_impresion_continua.setToolTip("Imprime todas las etiquetas seguidas en dos columnas. Desmarcar para impresión por hoja.")
        config_layout.addRow("Impresión Continua:", self.check_impresion_continua)

        detalles_config_layout.addWidget(config_group)
        layout.addLayout(detalles_config_layout)

        # ============= BOTONES DE ACCIÓN =============
        botones_layout = QHBoxLayout()
        botones_layout.addStretch()

        self.btn_aceptar = QPushButton("Aceptar")
        self.btn_aceptar.clicked.connect(self._aceptar)

        self.btn_limpiar = QPushButton("Limpiar")
        self.btn_limpiar.clicked.connect(self._limpiar)

        self.btn_imprimir = QPushButton("Imprimir")
        self.btn_imprimir.clicked.connect(self._imprimir)
        self.btn_imprimir.setStyleSheet("""
            QPushButton {
                background-color: #27ae60;
                color: white;
                font-weight: bold;
                padding: 10px 30px;
            }
        """)

        self.btn_salir = QPushButton("Salir")
        self.btn_salir.clicked.connect(self.close)

        botones_layout.addWidget(self.btn_aceptar)
        botones_layout.addWidget(self.btn_limpiar)
        botones_layout.addWidget(self.btn_imprimir)
        botones_layout.addWidget(self.btn_salir)

        layout.addLayout(botones_layout)

        # Cargar lista inicial
        self._refrescar_lista()

    def _apply_styles(self):
        self.setStyleSheet("""
            QMainWindow {
                background-color: #ffffff;
            }
            QGroupBox {
                font-weight: bold;
                font-size: 12px;
                border: 2px solid #34495e;
                border-radius: 5px;
                margin-top: 10px;
                padding-top: 15px;
                background-color: #ffffff;
                color: #000000;
            }
            QGroupBox::title {
                subcontrol-origin: margin;
                left: 10px;
                padding: 0 5px;
                background-color: #ffffff;
                color: #000000;
                font-weight: bold;
            }
            QTableWidget {
                background-color: #ffffff;
                alternate-background-color: #f0f0f0;
                font-size: 11px;
                border: 2px solid #34495e;
                gridline-color: #bdc3c7;
                selection-background-color: #3498db;
                selection-color: #ffffff;
                color: #000000;
            }
            QTableWidget::item {
                padding: 5px;
                color: #000000;
            }
            QTableWidget::item:selected {
                background-color: #3498db;
                color: #ffffff;
            }
            QTableWidget::item:alternate {
                background-color: #f0f0f0;
                color: #000000;
            }
            QHeaderView::section {
                background-color: #34495e;
                color: #ffffff;
                font-weight: bold;
                padding: 8px;
                border: none;
                font-size: 11px;
            }
            QPushButton {
                background-color: #3498db;
                color: #ffffff;
                font-weight: bold;
                border: none;
                padding: 8px 16px;
                border-radius: 4px;
                font-size: 11px;
            }
            QPushButton:hover {
                background-color: #2980b9;
                color: #ffffff;
            }
            QPushButton:pressed {
                background-color: #21618c;
                color: #ffffff;
            }
            QLineEdit {
                padding: 6px;
                border: 2px solid #34495e;
                border-radius: 3px;
                background: #ffffff;
                font-size: 11px;
                color: #000000;
            }
            QLineEdit:focus {
                border-color: #3498db;
                background-color: #ffffff;
                color: #000000;
            }
            QComboBox, QSpinBox {
                padding: 6px;
                border: 2px solid #34495e;
                border-radius: 3px;
                background: #ffffff;
                font-size: 11px;
                color: #000000;
            }
            QComboBox:focus, QSpinBox:focus {
                border-color: #3498db;
                background-color: #ffffff;
                color: #000000;
            }
            QComboBox QAbstractItemView {
                background-color: #ffffff;
                color: #000000;
                selection-background-color: #3498db;
                selection-color: #ffffff;
                border: 1px solid #34495e;
            }
            QLabel {
                font-size: 11px;
                color: #000000;
                background-color: transparent;
            }
            QCheckBox {
                font-size: 11px;
                color: #000000;
            }
            QCheckBox::indicator {
                border: 2px solid #34495e;
                background-color: #ffffff;
                width: 18px;
                height: 18px;
            }
            QCheckBox::indicator:checked {
                background-color: #3498db;
                border-color: #3498db;
            }
        """)

    def _buscar(self):
        """Realiza la búsqueda de productos"""
        try:
            q = self.input_busqueda.text().strip()
            productos = []
            
            if q:
                # Buscar por código_barra exacto primero
                try:
                    with self.db.conn.cursor() as cur:
                        cur.execute(
                            """
                            SELECT id, codigo_barra, descripcion_larga, descripcion_corta,
                                   unidad_medida, precio_venta, precio_promocion, costo_compra,
                                   sku
                            FROM productos
                            WHERE codigo_barra = %s AND tipo_estado='activo'
                            ORDER BY descripcion_larga
                            """,
                            (q,)
                        )
                        productos = cur.fetchall()
                except Exception:
                    productos = []

                # Si no hubo exacto, buscar por like en descripción o código
                if not productos:
                    with self.db.conn.cursor() as cur:
                        cur.execute(
                            """
                            SELECT id, codigo_barra, descripcion_larga, descripcion_corta,
                                   unidad_medida, precio_venta, precio_promocion, costo_compra,
                                   sku
                            FROM productos
                            WHERE tipo_estado='activo' AND (
                                descripcion_larga ILIKE %s OR 
                                descripcion_corta ILIKE %s OR
                                codigo_barra ILIKE %s OR
                                sku ILIKE %s
                            )
                            ORDER BY descripcion_larga
                            LIMIT 100
                            """,
                            (f"%{q}%", f"%{q}%", f"%{q}%", f"%{q}%")
                        )
                        productos = cur.fetchall()
            else:
                # Sin filtro, cargar todos
                with self.db.conn.cursor() as cur:
                    cur.execute(
                        """
                        SELECT id, codigo_barra, descripcion_larga, descripcion_corta,
                               unidad_medida, precio_venta, precio_promocion, costo_compra,
                               sku
                        FROM productos
                        WHERE tipo_estado='activo'
                        ORDER BY descripcion_larga
                        LIMIT 200
                        """
                    )
                    productos = cur.fetchall()

            self._poblar_tabla(productos)
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error en búsqueda: {e}")

    def _refrescar_lista(self):
        """Refresca la lista de productos"""
        self.input_busqueda.clear()
        self._buscar()

    def _poblar_tabla(self, productos):
        """Pobla la tabla con los productos encontrados"""
        self.tbl_productos.setRowCount(len(productos))
        
        for row, p in enumerate(productos):
            producto_id, cod_barra, desc_larga, desc_corta, u_med, precio_venta, precio_promo, costo, sku = p
            
            self.tbl_productos.setItem(row, 0, QTableWidgetItem(str(sku or "")))
            self.tbl_productos.setItem(row, 1, QTableWidgetItem(str(cod_barra or "")))
            self.tbl_productos.setItem(row, 2, QTableWidgetItem(str(desc_larga or "")))
            self.tbl_productos.setItem(row, 3, QTableWidgetItem(str(desc_corta or "")))
            self.tbl_productos.setItem(row, 4, QTableWidgetItem(str(u_med or "")))
            
            precio_v = float(precio_venta or 0)
            self.tbl_productos.setItem(row, 5, QTableWidgetItem(f"{precio_v:.2f}"))
            
            precio_p = float(precio_promo or precio_venta or 0)
            self.tbl_productos.setItem(row, 6, QTableWidgetItem(f"{precio_p:.2f}"))
            
            costo_val = float(costo or 0)
            self.tbl_productos.setItem(row, 7, QTableWidgetItem(f"{costo_val:.2f}"))

    def _on_producto_seleccionado(self):
        """Cuando se selecciona un producto de la tabla"""
        fila = self.tbl_productos.currentRow()
        if fila < 0:
            return
        
        try:
            # Obtener datos de la fila seleccionada
            sku = self.tbl_productos.item(fila, 0).text()
            cod_barra = self.tbl_productos.item(fila, 1).text()
            desc_larga = self.tbl_productos.item(fila, 2).text()
            desc_corta = self.tbl_productos.item(fila, 3).text()
            u_med = self.tbl_productos.item(fila, 4).text()
            precio_str = self.tbl_productos.item(fila, 5).text()
            
            # Guardar producto seleccionado
            self.producto_seleccionado = {
                'sku': sku,
                'codigo_barra': cod_barra,
                'descripcion_larga': desc_larga,
                'descripcion_corta': desc_corta,
                'unidad_medida': u_med,
                'precio': precio_str
            }
            
            # Actualizar labels
            self.label_codigo_interno.setText(sku)
            self.label_codigo_barras.setText(cod_barra)
            self.label_desc_larga.setText(desc_larga)
            self.label_desc_corta.setText(desc_corta)
            self.label_u_med.setText(u_med)
            self.label_precio.setText(f"S/ {precio_str}")
            
            # Actualizar vista previa del código de barras
            self._actualizar_vista_previa()
            
        except Exception as e:
            QMessageBox.warning(self, "Advertencia", f"Error al seleccionar producto: {e}")

    def _actualizar_vista_previa(self):
        """Actualiza la vista previa del código de barras"""
        if not self.producto_seleccionado:
            self.label_vista_previa.clear()
            self.label_vista_previa.setText("Seleccione un producto para ver el código de barras")
            return
        
        cod_barra = self.producto_seleccionado.get('codigo_barra', '')
        desc_corta = self.producto_seleccionado.get('descripcion_corta', '')
        precio = self.producto_seleccionado.get('precio', '0.00')
        
        if not cod_barra:
            self.label_vista_previa.clear()
            self.label_vista_previa.setText("Sin código de barras")
            return
        
        try:
            # Crear vista previa gráfica con mayor tamaño
            pixmap = QPixmap(330, 120)
            pixmap.fill(Qt.white)
            
            if not pixmap.isNull():
                painter = QPainter(pixmap)
                if painter.isActive():
                    painter.setRenderHint(QPainter.Antialiasing)
                    painter.setPen(Qt.black)
                    
                    # Dibujar descripción corta (usar fuente genérica)
                    font_desc = QFont()
                    font_desc.setBold(True)
                    font_desc.setPointSize(9)
                    painter.setFont(font_desc)
                    painter.drawText(10, 18, desc_corta[:28])
                    
                    # Dibujar código de barras según formato
                    formato = self.combo_formato.currentText()
                    if formato == "Code128 (gráfico)":
                        self._draw_code128(painter, 10, 32, 310, 40, cod_barra, draw_text=False)
                    elif formato == "EAN-13 (gráfico)":
                        self._draw_ean13(painter, 10, 32, 310, 40, cod_barra, draw_text=False)
                    else:  # Texto
                        font_mono = QFont("Courier", 11)
                        painter.setFont(font_mono)
                        painter.setPen(Qt.black)
                        painter.drawText(10, 58, cod_barra)
                    
                    # Dibujar precio
                    font_precio = QFont()
                    font_precio.setBold(True)
                    font_precio.setPointSize(9)
                    painter.setFont(font_precio)
                    painter.setPen(Qt.black)
                    precio_texto = f"S/ {precio}"
                    painter.drawText(10, 100, precio_texto)
                    
                    painter.end()
                    
                    # Limpiar texto y establecer pixmap
                    self.label_vista_previa.clear()
                    self.label_vista_previa.setPixmap(pixmap)
                else:
                    self.label_vista_previa.setText("Error al crear vista previa")
            else:
                self.label_vista_previa.setText("Error al crear imagen")
        except Exception as e:
            print(f"Error en vista previa: {e}")
            self.label_vista_previa.setText(f"Error: {str(e)}")

    def _aceptar(self):
        """Acepta el producto seleccionado y cantidad"""
        if not self.producto_seleccionado:
            QMessageBox.warning(self, "Advertencia", "Seleccione un producto primero")
            return
        
        cantidad = self.spin_cantidad.value()
        QMessageBox.information(
            self,
            "Confirmación",
            f"Producto aceptado:\n{self.producto_seleccionado['descripcion_larga']}\nCantidad: {cantidad}"
        )

    def _limpiar(self):
        """Limpia la selección y los campos"""
        self.tbl_productos.clearSelection()
        self.producto_seleccionado = None
        self.label_codigo_interno.clear()
        self.label_codigo_barras.clear()
        self.label_desc_larga.clear()
        self.label_desc_corta.clear()
        self.label_u_med.clear()
        self.label_precio.clear()
        self.spin_cantidad.setValue(1)
        self.label_vista_previa.setText("Seleccione un producto para ver el código de barras")
        self.input_busqueda.clear()

    def _imprimir(self):
        """Imprime las etiquetas"""
        if not self.producto_seleccionado:
            QMessageBox.warning(self, "Advertencia", "Seleccione un producto para imprimir")
            return
        
        cantidad = self.spin_cantidad.value()
        if cantidad <= 0:
            QMessageBox.warning(self, "Advertencia", "La cantidad debe ser mayor a 0")
            return
        
        # Configurar impresora y vista previa
        printer = QPrinter(QPrinter.HighResolution)
        printer.setFullPage(True)
        
        # Tamaños aproximados en puntos (1 punto ~ 1/72 pulgadas)
        size_map = {
            0: (142, 71),   # 50x25 mm aprox
            1: (175, 85),   # 62x30 mm aprox
            2: (255, 142),  # 90x50 mm aprox
        }
        label_w, label_h = size_map.get(self.combo_tamano_fleje.currentIndex(), (175, 85))
        cols = max(1, self.spin_columnas.value())
        filas = max(1, self.spin_filas_columna.value())
        
        preview = QPrintPreviewDialog(printer, self)
        preview.paintRequested.connect(
            lambda p: self._render_labels(
                p, label_w, label_h, cols, filas, cantidad
            )
        )
        preview.exec()

    def _render_labels(self, painter_device, label_w, label_h, cols, filas, cantidad):
        """Renderiza las etiquetas para impresión - Modo continuo o por hoja"""
        if not self.producto_seleccionado:
            return
        
        painter = QPainter(painter_device)
        if not painter.isActive():
            return
        
        painter.setRenderHint(QPainter.Antialiasing, False)
        painter.setPen(QPen(Qt.black, 2))
        
        # Obtener dimensiones de página
        page_rect = painter_device.pageRect(QPrinter.Point)
        page_width = page_rect.width()
        page_height = page_rect.height()
        
        margin = 30.0  # Mismo margen que flejes_vb.py
        desc_corta = self.producto_seleccionado.get('descripcion_corta', 'Sin descripción')
        cod_barra = self.producto_seleccionado.get('codigo_barra', '0000000000000')
        precio = self.producto_seleccionado.get('precio', '0.00')
        formato = self.combo_formato.currentText()
        modo_continuo = self.check_impresion_continua.isChecked()
        
        # Altura de etiqueta basada en el contenido (similar a flejes_vb.py)
        # Asegurar altura mínima suficiente para mostrar todo el contenido
        altura_etiqueta = max(float(label_h), 100.0)  # Mínimo 100 puntos para que quepa todo
        margen_vertical = 10.0
        
        if modo_continuo:
            # MODO CONTINUO: Dos columnas (basado en flejes_vb.py)
            x_col1 = margin
            x_col2 = (page_width / 2.0) + 10.0
            y_actual = margin
            columna_actual = 0
            etiquetas_impresas = 0
            
            while etiquetas_impresas < cantidad:
                # Verificar si necesitamos nueva página ANTES de dibujar
                if y_actual + altura_etiqueta + margen_vertical > page_height:
                    if etiquetas_impresas > 0:
                        if not painter.isActive():
                            break
                        try:
                            painter_device.newPage()
                            if not painter.isActive():
                                break
                        except Exception as e:
                            print(f"Error al crear nueva página: {e}")
                            break
                    y_actual = margin
                    columna_actual = 0
                
                if not painter.isActive():
                    break
                
                # Determinar coordenada X según columna
                x_actual = x_col1 if columna_actual == 0 else x_col2
                
                # Verificar coordenadas válidas
                if x_actual < 0 or y_actual < 0:
                    break
                
                # Dibujar etiqueta
                try:
                    self._dibujar_etiqueta(
                        painter, x_actual, y_actual, 
                        label_w, label_h, 
                        desc_corta, cod_barra, precio, formato
                    )
                except Exception as e:
                    print(f"Error al dibujar etiqueta: {e}")
                    break
                
                etiquetas_impresas += 1
                
                # Cambiar de columna después de cada etiqueta
                if columna_actual == 0:
                    columna_actual = 1
                else:
                    columna_actual = 0
                    y_actual += altura_etiqueta + margen_vertical
        else:
            # MODO POR HOJA: Usar cols y filas
            x = margin
            y = margin
            col = 0
            row = 0
            
            for i in range(cantidad):
                # Nueva página si es necesario
                if y + label_h + 10 > page_height - margin:
                    if not painter.isActive():
                        break
                    try:
                        painter_device.newPage()
                        if not painter.isActive():
                            break
                    except Exception as e:
                        print(f"Error al crear nueva página: {e}")
                        break
                    y = margin
                    x = margin
                    col = 0
                    row = 0
                
                if x + label_w + 10 > page_width - margin:
                    x = margin
                    y += label_h + 10
                    col = 0
                    row += 1
                
                # Dibujar etiqueta
                try:
                    self._dibujar_etiqueta(painter, x, y, label_w, label_h, desc_corta, cod_barra, precio, formato)
                except Exception as e:
                    print(f"Error al dibujar etiqueta: {e}")
                    break
                
                # Avanzar
                col += 1
                if col >= cols:
                    col = 0
                    row += 1
                    x = margin
                    y += label_h + 10
                else:
                    x += label_w + 10
        
        painter.end()
    
    def _dibujar_etiqueta(self, painter, x, y, w, h, desc, codigo, precio, formato):
        """Dibuja una etiqueta completa - Basado en flejes_vb.py con coordenadas correctas"""
        if not painter or not painter.isActive():
            return
        
        x_base = float(x)
        y_base = float(y)
        scale = 1.5  # Factor de escala igual que flejes_vb.py
        
        # Descripción corta (línea 1) - Similar a flejes_vb.py
        if self.check_mostrar_desc.isChecked():
            try:
                font_desc = QFont("Arial", int(8 * scale))  # 8 * 1.5 = 12 puntos
                painter.setFont(font_desc)
                painter.setPen(QPen(Qt.black, 1))
                desc_texto = desc[:28] if len(desc) > 28 else desc
                painter.drawText(int(x_base), int(y_base), desc_texto)
            except Exception as e:
                print(f"Error al dibujar descripción: {e}")
        
        # Código de barras (gráfico o texto) - Similar a flejes_vb.py
        if self.check_codigo_barras.isChecked():
            try:
                # Posición del código de barras (similar a flejes_vb: y_base + 15.0 * scale)
                y_barcode = y_base + (15.0 * scale)
                
                # Dibujar código de barras gráfico si corresponde
                if formato == "Code128 (gráfico)":
                    self._draw_code128(
                        painter, 
                        int(x_base + (4.5 * scale)), 
                        int(y_barcode), 
                        int(w - (9 * scale)), 
                        int(30 * scale), 
                        codigo, 
                        draw_text=False
                    )
                elif formato == "EAN-13 (gráfico)":
                    self._draw_ean13(
                        painter, 
                        int(x_base + (4.5 * scale)), 
                        int(y_barcode), 
                        int(w - (9 * scale)), 
                        int(30 * scale), 
                        codigo, 
                        draw_text=False
                    )
                
                # SIEMPRE dibujar el texto formateado del código de barras debajo del gráfico
                # (igual que flejes_vb.py línea 1022-1028)
                font_codigo = QFont("Courier New", int(10 * scale))  # 10 * 1.5 = 15 puntos
                painter.setFont(font_codigo)
                painter.setPen(QPen(Qt.black, 1))
                # Formato del código: primer dígito, espacio, 6 dígitos, espacio, 6 dígitos
                if len(codigo) == 13:
                    cod_formateado = f"{codigo[0]}   {codigo[1:7]}  {codigo[7:13]}"
                else:
                    cod_formateado = codigo
                # Posición del texto del código (similar a flejes_vb: y_base + 50.0 * scale)
                cod_y = y_base + (50.0 * scale)
                painter.drawText(int(x_base + (4.0 * scale)), int(cod_y), cod_formateado)
            except Exception as e:
                print(f"Error al dibujar código de barras: {e}")
        
        # Precio - Similar a flejes_vb.py
        if self.check_mostrar_precio.isChecked():
            try:
                font_precio = QFont("Arial", int(9 * scale))  # 9 * 1.5 = 13.5 puntos
                font_precio.setBold(True)
                painter.setFont(font_precio)
                painter.setPen(QPen(Qt.black, 1))
                precio_texto = f"S/. {precio}  "
                # Posición del precio (similar a flejes_vb: y_base + 65.0 * scale)
                precio_y = y_base + (65.0 * scale)
                painter.drawText(int(x_base + (5.0 * scale)), int(precio_y), precio_texto)
            except Exception as e:
                print(f"Error al dibujar precio: {e}")

    # ============== Renderizadores de código de barras ==============
    _CODE128_PATTERNS = [
        (2,1,2,2,2,2),(2,2,2,1,2,2),(2,2,2,2,2,1),(1,2,1,2,2,3),(1,2,1,3,2,2),
        (1,3,1,2,2,2),(1,2,2,2,1,3),(1,2,2,3,1,2),(1,3,2,2,1,2),(2,2,1,2,1,3),
        (2,2,1,3,1,2),(2,3,1,2,1,2),(1,1,2,2,3,2),(1,2,2,1,3,2),(1,2,2,2,3,1),
        (1,1,3,2,2,2),(1,2,3,1,2,2),(1,2,3,2,2,1),(2,2,3,2,1,1),(2,2,1,1,3,2),
        (2,2,1,2,3,1),(2,1,3,2,1,2),(2,2,3,1,1,2),(3,1,2,1,3,1),(3,1,1,2,2,2),
        (3,2,1,1,2,2),(3,2,1,2,2,1),(3,1,2,2,1,2),(3,2,2,1,1,2),(3,2,2,2,1,1),
        (2,1,2,1,2,3),(2,1,2,3,2,1),(2,3,2,1,2,1),(1,1,1,3,2,3),(1,3,1,1,2,3),
        (1,3,1,3,2,1),(1,1,2,3,1,3),(1,3,2,1,1,3),(1,3,2,3,1,1),(2,1,1,3,1,3),
        (2,3,1,1,1,3),(2,3,1,3,1,1),(1,1,2,1,3,3),(1,1,2,3,3,1),(1,3,2,1,3,1),
        (1,1,3,1,2,3),(1,1,3,3,2,1),(1,3,3,1,2,1),(3,1,3,1,2,1),(2,1,1,3,3,1),
        (2,3,1,1,3,1),(2,1,3,1,1,3),(2,1,3,3,1,1),(2,1,3,1,3,1),(3,1,1,1,2,3),
        (3,1,1,3,2,1),(3,3,1,1,2,1),(3,1,2,1,1,3),(3,1,2,3,1,1),(3,3,2,1,1,1),
        (3,1,4,1,1,1),(2,2,1,4,1,1),(4,3,1,1,1,1),(2,4,1,2,1,1),(1,3,4,1,1,1),
        (1,1,1,2,2,4),(1,2,1,1,2,4),(1,2,1,2,2,3),(1,1,2,2,1,4),(1,2,2,1,1,4),
        (1,2,2,2,1,3),(1,1,1,4,2,1),(1,1,2,4,1,1),(1,2,1,4,1,1),(1,1,4,1,2,1),
        (1,1,4,2,1,1),(4,1,1,1,2,1),(4,1,2,1,1,1),(4,2,1,1,1,1),(2,1,2,1,4,1),
        (2,1,4,1,2,1),(4,1,2,1,2,1),(1,1,1,1,4,2),(1,1,1,2,4,1),(1,2,1,1,4,1),
        (1,1,4,1,1,2),(1,1,4,2,1,1),(4,1,1,2,1,1),(2,1,1,1,4,1),(2,1,1,4,1,1),
        (1,1,4,1,2,1),(1,1,4,2,1,1),(1,2,4,1,1,1),(1,2,1,1,1,4),(1,2,1,4,1,1),
        (1,4,1,1,1,2),(1,4,1,2,1,1),(1,1,1,2,2,3),(1,1,2,2,1,3),(1,2,2,1,1,3),
        (1,1,1,2,3,2),(1,1,2,2,3,1),(1,2,2,1,3,1)
    ]

    def _code128_b_values(self, data: str):
        values = [104]  # Start B
        for ch in data:
            o = ord(ch)
            if 32 <= o <= 126:
                values.append(o - 32)
            else:
                values.append(0)
        checksum = values[0]
        for i, v in enumerate(values[1:], start=1):
            checksum += v * i
        checksum %= 103
        values.append(checksum)
        values.append(106)  # Stop
        return values

    def _draw_code128(self, painter: QPainter, x: int, y: int, width: int, height: int, data: str, draw_text=True):
        """Dibuja código de barras Code128 con validaciones de seguridad"""
        if not painter or not painter.isActive():
            return
        if not data or width <= 0 or height <= 0:
            return
        
        try:
            values = self._code128_b_values(data)
            modules = []
            for v in values:
                if v < len(self._CODE128_PATTERNS):
                    modules.extend(self._CODE128_PATTERNS[v])
            total_modules = sum(modules)
            if total_modules == 0:
                return
            module_w = max(1, int(width / total_modules))
            cx = int(x)
            black = True
            for w_units in modules:
                wpx = max(1, int(w_units * module_w))
                if black:
                    # Validar coordenadas antes de dibujar
                    if cx >= 0 and y >= 0 and wpx > 0 and height > 0:
                        painter.fillRect(cx, y, wpx, height, Qt.black)
                cx += wpx
                black = not black
            # Solo dibujar texto si se solicita
            if draw_text:
                try:
                    font_texto = QFont()
                    font_texto.setPointSize(7)
                    painter.setFont(font_texto)
                    painter.setPen(QPen(Qt.black, 1))
                    painter.drawText(int(x), int(y + height + 10), data)
                except Exception as e:
                    print(f"Error al dibujar texto Code128: {e}")
        except Exception as e:
            print(f"Error en _draw_code128: {e}")

    _EAN_L = {
        '0':'0001101','1':'0011001','2':'0010011','3':'0111101','4':'0100011','5':'0110001','6':'0101111','7':'0111011','8':'0110111','9':'0001011'
    }
    _EAN_G = {
        '0':'0100111','1':'0110011','2':'0011011','3':'0100001','4':'0011101','5':'0111001','6':'0000101','7':'0010001','8':'0001001','9':'0010111'
    }
    _EAN_R = _EAN_L
    _EAN_PARITY = {
        '0':'LLLLLL','1':'LLGLGG','2':'LLGGLG','3':'LLGGGL','4':'LGLLGG','5':'LGGLLG','6':'LGGGLL','7':'LGLGLG','8':'LGLGGL','9':'LGGLGL'
    }

    def _ean13_checksum(self, data12: str) -> int:
        s = 0
        for i, ch in enumerate(reversed(data12)):
            v = int(ch)
            s += v * (3 if (i % 2 == 0) else 1)
        return (10 - (s % 10)) % 10

    def _draw_ean13(self, painter: QPainter, x: int, y: int, width: int, height: int, data: str, draw_text=True):
        """Dibuja código de barras EAN-13 con validaciones de seguridad"""
        if not painter or not painter.isActive():
            return
        if not data or width <= 0 or height <= 0:
            return
        
        try:
            digits = ''.join(ch for ch in data if ch.isdigit())
            if len(digits) < 12:
                try:
                    font_mono = QFont("Courier", 10)
                    painter.setFont(font_mono)
                    painter.setPen(QPen(Qt.black, 1))
                    painter.drawText(int(x), int(y + height//2), data)
                except Exception as e:
                    print(f"Error al dibujar texto EAN13 (corto): {e}")
                return
            if len(digits) == 12:
                digits += str(self._ean13_checksum(digits))
            if len(digits) != 13:
                try:
                    font_mono = QFont("Courier", 10)
                    painter.setFont(font_mono)
                    painter.setPen(QPen(Qt.black, 1))
                    painter.drawText(int(x), int(y + height//2), data)
                except Exception as e:
                    print(f"Error al dibujar texto EAN13 (inválido): {e}")
                return

            left_guard = '101'
            center_guard = '01010'
            right_guard = '101'
            first = digits[0]
            left = digits[1:7]
            right = digits[7:]
            pattern = left_guard
            parity = self._EAN_PARITY.get(first, 'LLLLLL')  # Usar get() para evitar KeyError
            for i, d in enumerate(left):
                if d not in self._EAN_L or d not in self._EAN_G:
                    continue
                code = self._EAN_L[d] if parity[i] == 'L' else self._EAN_G[d]
                pattern += code
            pattern += center_guard
            for d in right:
                if d not in self._EAN_R:
                    continue
                code = ''.join('1' if c == '0' else '0' for c in self._EAN_R[d])
                pattern += code
            pattern += right_guard

            total_modules = len(pattern)
            if total_modules == 0:
                return
            module_w = max(1, int(width / total_modules))
            cx = int(x)
            for bit in pattern:
                wpx = max(1, int(module_w))
                if bit == '1':
                    # Validar coordenadas antes de dibujar
                    if cx >= 0 and y >= 0 and wpx > 0 and height > 0:
                        painter.fillRect(cx, y, wpx, height, Qt.black)
                cx += wpx
            # Solo dibujar texto si se solicita
            if draw_text:
                try:
                    font_texto = QFont()
                    font_texto.setPointSize(7)
                    painter.setFont(font_texto)
                    painter.setPen(QPen(Qt.black, 1))
                    painter.drawText(int(x), int(y + height + 10), digits)
                except Exception as e:
                    print(f"Error al dibujar texto EAN13: {e}")
        except Exception as e:
            print(f"Error en _draw_ean13: {e}")

    def closeEvent(self, event):
        """Maneja el cierre de la ventana y actualiza el estado de conexión"""
        try:
            # Actualizar estado de conexión a 'no'
            if self.usuario:
                self.db.set_estado_conectado(self.usuario, False)
                
            # Si el usuario es 'barras' y existe main_window, cerrarlo también
            if self.main_window and self.main_window.isVisible():
                try:
                    rol = self.db.get_rol_usuario(self.usuario)
                    if rol == 'barras':
                        # Cerrar main_window también para el usuario barras
                        self.main_window.close()
                except:
                    pass  # Si hay error al obtener el rol, continuar con el cierre
        except Exception as e:
            print(f"Error al cerrar sesión en flejes: {e}")
            # Continuar con el cierre aunque haya error
        finally:
            event.accept()
