"""
Módulo de impresión de códigos de barras basado en el proyecto VB6
Replica la funcionalidad del proyecto original de VB6
"""

from PySide6.QtWidgets import (
    QMainWindow, QWidget, QVBoxLayout, QHBoxLayout, QGroupBox, QLabel,
    QLineEdit, QPushButton, QTableWidget, QTableWidgetItem, QHeaderView,
    QSpinBox, QComboBox, QMessageBox, QFormLayout, QCheckBox, QTextEdit
)
from PySide6.QtGui import QFont, QPainter, QPixmap, QColor, QPen
from PySide6.QtCore import Qt, QSize
from PySide6.QtPrintSupport import QPrinter, QPrintPreviewDialog
from datetime import datetime


class FlejesVBForm(QMainWindow):
    """Módulo de impresión de flejes basado en el proyecto VB6 original"""

    def __init__(self, db_client, usuario_actual, main_window=None):
        super().__init__()
        self.db = db_client
        self.usuario = usuario_actual
        self.main_window = main_window
        self.producto_seleccionado = None
        
        self.setWindowTitle("IMPRESIÓN DE CÓDIGO DE BARRAS")
        self.setGeometry(100, 100, 1200, 580)
        self._init_ui()
        self._apply_styles()

    def _init_ui(self):
        central = QWidget()
        self.setCentralWidget(central)
        layout = QVBoxLayout(central)
        layout.setSpacing(3)
        layout.setContentsMargins(5, 5, 5, 5)

        # ============= BOTONES DE BÚSQUEDA EN CABECERA =============
        toolbar_layout = QHBoxLayout()
        toolbar_layout.setSpacing(3)
        toolbar_layout.setContentsMargins(0, 0, 0, 0)
        
        self.btn_codigo = QPushButton("Código")
        self.btn_codigo.clicked.connect(lambda: self._buscar_producto("codigo"))
        
        self.btn_barras = QPushButton("Cód. de &barras")
        self.btn_barras.clicked.connect(lambda: self._buscar_producto("barras"))
        
        self.btn_descripcion = QPushButton("&Descripción larga")
        self.btn_descripcion.clicked.connect(lambda: self._buscar_producto("descripcion"))
        
        self.btn_descripcion_corta = QPushButton("Descripción corta")
        self.btn_descripcion_corta.clicked.connect(lambda: self._buscar_producto("descripcion_corta"))
        
        self.btn_umed = QPushButton("U. med.")
        self.btn_umed.clicked.connect(lambda: self._buscar_producto("umed"))
        
        self.btn_pventa = QPushButton("P. venta")
        self.btn_pventa.clicked.connect(lambda: self._buscar_producto("pventa"))
        
        self.btn_poferta = QPushButton("P. Oferta")
        self.btn_poferta.clicked.connect(lambda: self._buscar_producto("poferta"))
        
        toolbar_layout.addWidget(self.btn_codigo)
        toolbar_layout.addWidget(self.btn_barras)
        toolbar_layout.addWidget(self.btn_descripcion)
        toolbar_layout.addWidget(self.btn_descripcion_corta)
        toolbar_layout.addWidget(self.btn_umed)
        toolbar_layout.addWidget(self.btn_pventa)
        toolbar_layout.addWidget(self.btn_poferta)
        toolbar_layout.addStretch()
        
        layout.addLayout(toolbar_layout)

        # ============= TABLA DE PRODUCTOS (MSFlexGrid equivalente) =============
        self.tbl_productos = QTableWidget()
        self.tbl_productos.setColumnCount(11)
        self.tbl_productos.setHorizontalHeaderLabels([
            "", "COD.INTERNO", "COD.BARRA", "DESCRIPCIÓN DE PRODUCTO",
            "DESCRIPCIÓN CORTA", "U. MED.", "P. VENTA", "P. OFERTA",
            "COSTO", "AF", "MSTOC"
        ])
        self.tbl_productos.setColumnWidth(0, 0)  # Columna oculta
        self.tbl_productos.setColumnWidth(1, 110)
        self.tbl_productos.setColumnWidth(2, 130)
        self.tbl_productos.setColumnWidth(3, 380)
        self.tbl_productos.setColumnWidth(4, 170)
        self.tbl_productos.setColumnWidth(5, 60)
        self.tbl_productos.setColumnWidth(6, 100)
        self.tbl_productos.setColumnWidth(7, 100)
        self.tbl_productos.setColumnWidth(8, 80)
        self.tbl_productos.setColumnWidth(9, 40)
        self.tbl_productos.setColumnWidth(10, 40)
        
        # Altura de filas más compacta
        self.tbl_productos.verticalHeader().setDefaultSectionSize(22)
        self.tbl_productos.verticalHeader().setVisible(False)
        
        self.tbl_productos.setSelectionBehavior(QTableWidget.SelectRows)
        self.tbl_productos.setSelectionMode(QTableWidget.SingleSelection)
        self.tbl_productos.itemDoubleClicked.connect(self._on_producto_doble_click)
        self.tbl_productos.setAlternatingRowColors(True)
        self.tbl_productos.setMaximumHeight(280)
        
        layout.addWidget(self.tbl_productos)

        # ============= INFORMACIÓN Y CONFIGURACIÓN =============
        info_config_layout = QHBoxLayout()

        # Frame 2: Detalles del producto seleccionado
        frame2 = QGroupBox()
        frame2.setMaximumWidth(550)
        frame2_layout = QFormLayout(frame2)
        frame2_layout.setSpacing(3)
        frame2_layout.setContentsMargins(6, 6, 6, 6)
        
        self.txt_codigo = QLineEdit()
        self.txt_codigo.setEnabled(False)
        self.txt_codigo.setAlignment(Qt.AlignCenter)
        frame2_layout.addRow("Código interno", self.txt_codigo)
        
        cod_barras_layout = QHBoxLayout()
        self.txt_barras = QLineEdit()
        self.txt_barras.setEnabled(False)
        self.txt_barras.setAlignment(Qt.AlignCenter)
        cod_barras_layout.addWidget(self.txt_barras)
        frame2_layout.addRow("Cod. Barras", cod_barras_layout)
        
        self.txt_descripcion_larga = QLineEdit()
        self.txt_descripcion_larga.setEnabled(False)
        frame2_layout.addRow("Descripción larga", self.txt_descripcion_larga)
        
        desc_envase_layout = QHBoxLayout()
        self.txt_descripcion_corta = QLineEdit()
        self.txt_descripcion_corta.setEnabled(False)
        desc_envase_layout.addWidget(self.txt_descripcion_corta)
        
        self.txt_envase = QLineEdit()
        self.txt_envase.setEnabled(False)
        self.txt_envase.setAlignment(Qt.AlignCenter)
        self.txt_envase.setMaximumWidth(80)
        desc_envase_layout.addWidget(QLabel("U. med."))
        desc_envase_layout.addWidget(self.txt_envase)
        frame2_layout.addRow("Descripción corta", desc_envase_layout)
        
        peso_cant_layout = QHBoxLayout()
        self.txt_peso = QLineEdit()
        self.txt_peso.setEnabled(False)
        self.txt_peso.setAlignment(Qt.AlignCenter)
        self.txt_peso.setMaximumWidth(90)
        self.lbl_und = QLabel("GR.")
        peso_cant_layout.addWidget(QLabel("Peso"))
        peso_cant_layout.addWidget(self.txt_peso)
        peso_cant_layout.addWidget(self.lbl_und)
        frame2_layout.addRow("", peso_cant_layout)
        
        self.txt_cantidad = QLineEdit()
        self.txt_cantidad.setAlignment(Qt.AlignCenter)
        self.txt_cantidad.setMaximumWidth(120)
        self.txt_cantidad.setMaxLength(3)
        frame2_layout.addRow("Cant.", self.txt_cantidad)
        
        self.txt_precio = QLineEdit()
        self.txt_precio.setEnabled(False)
        self.txt_precio.setAlignment(Qt.AlignCenter)
        font_precio = QFont()
        font_precio.setPointSize(12)
        font_precio.setBold(True)
        self.txt_precio.setFont(font_precio)
        frame2_layout.addRow("Precio", self.txt_precio)
        
        info_config_layout.addWidget(frame2)

        # Frame 3: Vista previa del código de barras
        frame3 = QGroupBox()
        frame3.setMaximumWidth(180)
        frame3_layout = QVBoxLayout(frame3)
        frame3_layout.setSpacing(1)
        frame3_layout.setContentsMargins(4, 6, 4, 4)
        
        # Label para descripción (arriba)
        self.label_descripcion = QLabel("")
        self.label_descripcion.setAlignment(Qt.AlignCenter)
        self.label_descripcion.setMaximumHeight(18)
        self.label_descripcion.setStyleSheet("font-size: 8px; font-weight: bold;")
        frame3_layout.addWidget(self.label_descripcion)
        
        # Vista previa del código de barras (centrada)
        self.picture_preview = QLabel()
        self.picture_preview.setMinimumSize(170, 48)
        self.picture_preview.setMaximumSize(170, 48)
        self.picture_preview.setStyleSheet("""
            QLabel {
                border: 1px solid #000000;
                background-color: white;
            }
        """)
        self.picture_preview.setAlignment(Qt.AlignCenter)
        frame3_layout.addWidget(self.picture_preview, alignment=Qt.AlignHCenter)
        
        # Labels para el código (equivalente a Label5, Label6, Label7) - más compactos
        codigo_layout = QHBoxLayout()
        codigo_layout.setSpacing(1)
        codigo_layout.setContentsMargins(0, 0, 0, 0)
        self.label_codigo_part1 = QLabel("0")
        self.label_codigo_part1.setAlignment(Qt.AlignCenter)
        self.label_codigo_part1.setMaximumHeight(14)
        self.label_codigo_part1.setStyleSheet("font-size: 8px;")
        self.label_codigo_part2 = QLabel("L6")
        self.label_codigo_part2.setAlignment(Qt.AlignCenter)
        self.label_codigo_part2.setMaximumHeight(14)
        self.label_codigo_part2.setStyleSheet("font-size: 8px;")
        self.label_codigo_part3 = QLabel("L7")
        self.label_codigo_part3.setAlignment(Qt.AlignCenter)
        self.label_codigo_part3.setMaximumHeight(14)
        self.label_codigo_part3.setStyleSheet("font-size: 8px;")
        codigo_layout.addWidget(self.label_codigo_part1)
        codigo_layout.addWidget(self.label_codigo_part2)
        codigo_layout.addWidget(self.label_codigo_part3)
        frame3_layout.addLayout(codigo_layout)
        
        # Precio y peso en una línea
        precio_peso_layout = QHBoxLayout()
        precio_peso_layout.setSpacing(3)
        precio_peso_layout.setContentsMargins(0, 0, 0, 0)
        self.label_precio = QLabel("S/.")
        self.label_precio.setAlignment(Qt.AlignLeft)
        self.label_precio.setMaximumHeight(14)
        self.label_precio.setStyleSheet("font-size: 8px;")
        self.label_peso = QLabel("")
        self.label_peso.setAlignment(Qt.AlignLeft)
        self.label_peso.setMaximumHeight(14)
        self.label_peso.setStyleSheet("font-size: 8px;")
        precio_peso_layout.addWidget(self.label_precio)
        precio_peso_layout.addWidget(self.label_peso)
        frame3_layout.addLayout(precio_peso_layout)
        
        info_config_layout.addWidget(frame3)

        layout.addLayout(info_config_layout)

        # ============= BOTONES DE ACCIÓN =============
        botones_layout = QHBoxLayout()
        botones_layout.setSpacing(5)
        botones_layout.setContentsMargins(0, 0, 0, 0)
        
        self.check_ceros = QCheckBox("00")
        self.check_ceros.setChecked(True)
        botones_layout.addWidget(self.check_ceros)
        
        # Opción de modo de impresión
        self.check_impresion_continua = QCheckBox("Impresión Continua")
        self.check_impresion_continua.setChecked(True)
        self.check_impresion_continua.setToolTip("Imprime todas las etiquetas seguidas. Desmarcar para imprimir por hoja.")
        botones_layout.addWidget(self.check_impresion_continua)
        
        botones_layout.addStretch()
        
        self.btn_aceptar = QPushButton("&Acepta")
        self.btn_aceptar.clicked.connect(self._generar_codigo)
        self.btn_aceptar.setMinimumHeight(35)
        
        self.btn_limpiar = QPushButton("&Limpiar")
        self.btn_limpiar.clicked.connect(self._limpiar)
        self.btn_limpiar.setMinimumHeight(35)
        
        self.btn_imprimir = QPushButton("&Imprimir")
        self.btn_imprimir.clicked.connect(self._imprimir)
        self.btn_imprimir.setEnabled(False)
        self.btn_imprimir.setMinimumHeight(35)
        
        self.btn_salir = QPushButton("&Salir")
        self.btn_salir.clicked.connect(self.close)
        self.btn_salir.setMinimumHeight(35)
        
        botones_layout.addWidget(self.btn_aceptar)
        botones_layout.addWidget(self.btn_limpiar)
        botones_layout.addWidget(self.btn_imprimir)
        botones_layout.addWidget(self.btn_salir)
        
        layout.addLayout(botones_layout)

        # Conectar eventos
        self.txt_cantidad.returnPressed.connect(self._on_cantidad_enter)
        self.txt_peso.returnPressed.connect(self._on_peso_enter)

    def _apply_styles(self):
        self.setStyleSheet("""
            QMainWindow {
                background-color: #f0f0f0;
            }
            QGroupBox {
                font-weight: bold;
                font-size: 10px;
                border: 2px solid #5d6d7e;
                border-radius: 3px;
                margin-top: 3px;
                padding-top: 8px;
            }
            QGroupBox::title {
                subcontrol-origin: margin;
                left: 8px;
                padding: 0 3px;
            }
            QPushButton {
                background-color: #d0d0d0;
                color: #000000;
                font-weight: bold;
                font-size: 9px;
                padding: 3px 10px;
                border: 1px solid #808080;
                min-height: 25px;
            }
            QPushButton:hover {
                background-color: #c0c0c0;
            }
            QPushButton:pressed {
                background-color: #a0a0a0;
            }
            QLineEdit {
                padding: 3px;
                border: 1px solid #808080;
                background: white;
                font-size: 10px;
                color: #000000;
                min-height: 22px;
            }
            QLineEdit:disabled {
                background-color: #f0f0f0;
            }
            QTableWidget {
                background-color: white;
                alternate-background-color: #f8f8f8;
                font-size: 9px;
                border: 1px solid #808080;
                color: #000000;
                gridline-color: #d0d0d0;
            }
            QTableWidget::item {
                padding: 2px;
            }
            QHeaderView::section {
                background-color: #d0d0d0;
                color: #000000;
                font-weight: bold;
                padding: 3px;
                border: 1px solid #808080;
                font-size: 9px;
            }
            QLabel {
                font-size: 9px;
                color: #000000;
            }
            QFormLayout {
                spacing: 2px;
            }
        """)

    def _buscar_producto(self, tipo):
        """Busca productos según el tipo especificado"""
        from PySide6.QtWidgets import QInputDialog
        
        busqueda, ok = QInputDialog.getText(self, "EL SUPER SAC", "Ingrese la descripcion del producto:")
        if not ok or not busqueda:
            return
        
        try:
            with self.db.conn.cursor() as cur:
                if tipo == "codigo" or tipo == "barras":
                    # Buscar por código de barras
                    codigo = self._completa_codigo(busqueda) if tipo == "codigo" else busqueda
                    cur.execute("""
                        SELECT id, sku, codigo_barra, descripcion_larga, descripcion_corta,
                               unidad_medida, precio_venta, precio_promocion, costo_compra,
                               afecto_igv, stock_disponible
                        FROM productos
                        WHERE codigo_barra = %s AND tipo_estado = 'activo'
                        ORDER BY descripcion_larga
                    """, (codigo,))
                else:
                    # Buscar por descripción
                    cur.execute("""
                        SELECT id, sku, codigo_barra, descripcion_larga, descripcion_corta,
                               unidad_medida, precio_venta, precio_promocion, costo_compra,
                               afecto_igv, stock_disponible
                        FROM productos
                        WHERE tipo_estado = 'activo' AND (
                            descripcion_larga ILIKE %s OR
                            descripcion_corta ILIKE %s OR
                            codigo_barra ILIKE %s
                        )
                        ORDER BY descripcion_larga
                        LIMIT 100
                    """, (f"%{busqueda}%", f"%{busqueda}%", f"%{busqueda}%"))
                
                productos = cur.fetchall()
                self._llenar_tabla(productos)
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error al buscar productos: {e}")

    def _completa_codigo(self, codigo):
        """Completa el código con ceros a la izquierda hasta 12 dígitos"""
        while len(codigo) < 12:
            codigo = "0" + codigo
        return codigo[:12]

    def _llenar_tabla(self, productos):
        """Llena la tabla con los productos encontrados"""
        self.tbl_productos.setRowCount(len(productos))
        
        for row, p in enumerate(productos):
            producto_id, sku, cod_barra, desc_larga, desc_corta, u_med, precio_venta, precio_promo, costo, afecto_igv, stock = p
            
            # Columna 0 (oculta): ID
            self.tbl_productos.setItem(row, 0, QTableWidgetItem(str(producto_id)))
            # Columna 1: COD.INTERNO
            self.tbl_productos.setItem(row, 1, QTableWidgetItem(str(sku or "")))
            # Columna 2: COD.BARRA
            self.tbl_productos.setItem(row, 2, QTableWidgetItem(str(cod_barra or "")))
            # Columna 3: DESCRIPCION DE PRODUCTO
            self.tbl_productos.setItem(row, 3, QTableWidgetItem(str(desc_larga or "")))
            # Columna 4: DESCRIPCION CORTA
            self.tbl_productos.setItem(row, 4, QTableWidgetItem(str(desc_corta or "")))
            # Columna 5: U.MED
            item_umed = QTableWidgetItem(str(u_med or ""))
            if str(u_med or "").upper() == "KGR":
                item_umed.setForeground(QColor(255, 0, 0))  # Rojo para KGR
            self.tbl_productos.setItem(row, 5, item_umed)
            # Columna 6: P. VENTA
            precio_v = float(precio_venta or 0)
            self.tbl_productos.setItem(row, 6, QTableWidgetItem(f"{precio_v:,.2f}"))
            # Columna 7: P. OFERTA
            precio_p = float(precio_promo or precio_venta or 0)
            self.tbl_productos.setItem(row, 7, QTableWidgetItem(f"{precio_p:,.2f}"))
            # Columna 8: COSTO
            costo_val = float(costo or 0)
            self.tbl_productos.setItem(row, 8, QTableWidgetItem(f"{costo_val:,.2f}"))
            # Columna 9: AF
            self.tbl_productos.setItem(row, 9, QTableWidgetItem("SI" if afecto_igv else "NO"))
            # Columna 10: MSTOC
            self.tbl_productos.setItem(row, 10, QTableWidgetItem(str(stock or 0)))

    def _on_producto_doble_click(self):
        """Cuando se hace doble clic en un producto"""
        self._llenar_campos()
        self.txt_cantidad.setFocus()
        
        fila = self.tbl_productos.currentRow()
        if fila >= 0:
            u_med = self.tbl_productos.item(fila, 5).text()
            cod_barra = self.tbl_productos.item(fila, 2).text()
            
            # Verificar si es producto pesable (KGR y código empieza con "20")
            if u_med.upper() == "KGR" and cod_barra and cod_barra[:2] == "20":
                self.txt_peso.setEnabled(True)
                self.label_peso.setVisible(True)
                self.txt_peso.setFocus()
            else:
                self.txt_peso.setEnabled(False)
                self.label_peso.setVisible(False)
            
            self.btn_aceptar.setEnabled(True)
            self._generar_codigo()  # Auto-generar código

    def _llenar_campos(self):
        """Llena los campos con el producto seleccionado"""
        fila = self.tbl_productos.currentRow()
        if fila < 0:
            return
        
        self.txt_codigo.setText(self.tbl_productos.item(fila, 1).text())
        self.txt_barras.setText(self.tbl_productos.item(fila, 2).text())
        self.txt_descripcion_larga.setText(self.tbl_productos.item(fila, 3).text())
        self.txt_descripcion_corta.setText(self.tbl_productos.item(fila, 4).text())
        self.txt_envase.setText(self.tbl_productos.item(fila, 5).text())
        self.txt_cantidad.setText("")
        self.txt_precio.setText(self.tbl_productos.item(fila, 7).text())
        self.txt_peso.setText("")

    def _generar_codigo(self):
        """Genera el código de barras y actualiza la vista previa"""
        u_med = self.txt_envase.text().upper()
        cod_barra = self.txt_barras.text().strip()
        
        if u_med == "KGR" and cod_barra and len(cod_barra) >= 7:
            # Producto pesable: calcular código basado en peso
            if not self.txt_peso.text():
                QMessageBox.warning(self, "Advertencia", "Ingrese el peso para productos pesables")
                return
            
            peso = float(self.txt_peso.text())
            cod_fijo = cod_barra[:7]
            num = str(int(peso * 1000)).zfill(5)
            primera = cod_fijo + num
            
            # Calcular dígito de control EAN-13
            suma_pares = (int(primera[1]) + int(primera[3]) + int(primera[5]) + 
                         int(primera[7]) + int(primera[9]) + int(primera[11])) * 3
            suma_impares = (int(primera[0]) + int(primera[2]) + int(primera[4]) + 
                           int(primera[6]) + int(primera[8]) + int(primera[10]))
            suma_total = suma_pares + suma_impares
            multiplo = int((suma_total / 10) + 0.95) * 10
            digito_control = multiplo - suma_total
            nuevo_codigo = primera + str(digito_control)
            
            self.txt_barras.setText(nuevo_codigo)
            
            # Actualizar precio según peso
            precio_base = float(self.txt_precio.text() or 0)
            if 20 < peso < 1000:
                dividendo = 1000 / peso
                nuevo_precio = precio_base / dividendo
            else:
                num_peso = peso if peso >= 1 else peso * 1000
                dividendo = num_peso / 1000 if num_peso > 1000 else num_peso
                nuevo_precio = precio_base * dividendo
            self.txt_precio.setText(f"{nuevo_precio:,.2f}")
            
            # Mostrar peso
            if 20 < peso < 1000:
                self.label_peso.setText(f"{peso}GR.")
                self.lbl_und.setText("GR.")
            else:
                peso_kg = peso if peso < 1000 else peso / 1000
                self.label_peso.setText(f"{peso_kg}KG.")
                self.lbl_und.setText("KGR.")
            
            self.label_precio.setText(f"S/. {precio_base * peso:,.2f}")
            self.label_peso.setVisible(True)
        else:
            # Producto normal
            if len(cod_barra) != 13:
                QMessageBox.critical(self, "Error", "Error: La longitud del código es incorrecta")
                return
            
            precio = float(self.txt_precio.text() or 0)
            self.label_precio.setText(f"S/. {precio:,.2f}")
            self.label_peso.setVisible(False)
            self.label_peso.setText("")
        
        # Actualizar labels y dibujar código de barras
        self.label_descripcion.setText(self.txt_descripcion_corta.text()[:20])
        codigo_final = self.txt_barras.text()
        if len(codigo_final) == 13:
            self.label_codigo_part1.setText(codigo_final[0])
            self.label_codigo_part2.setText(codigo_final[1:7])
            self.label_codigo_part3.setText(codigo_final[7:13])
        
        # Dibujar código de barras EAN-13
        self._paint_code_ean13(self.picture_preview, codigo_final)
        self.btn_imprimir.setEnabled(True)

    def _paint_code_ean13(self, label_widget, codigo_completo):
        """Dibuja código de barras EAN-13 en el label (equivalente a PaintCode de VB6)"""
        if len(codigo_completo) != 13:
            return
        
        # Patrones de código EAN-13
        code_a = {
            '0': '0001101', '1': '0011001', '2': '0010011', '3': '0111101',
            '4': '0100011', '5': '0110001', '6': '0101111', '7': '0111011',
            '8': '0110111', '9': '0001011'
        }
        
        code_b = {
            '0': '0100111', '1': '0110011', '2': '0011011', '3': '0100001',
            '4': '0011101', '5': '0111001', '6': '0000101', '7': '0010001',
            '8': '0001001', '9': '0010111'
        }
        
        code_c = {
            '0': '1110010', '1': '1100110', '2': '1101100', '3': '1000010',
            '4': '1011100', '5': '1001110', '6': '1010000', '7': '1000100',
            '8': '1001000', '9': '1110100'
        }
        
        # Patrones de paridad según primer dígito
        parity_pattern = {
            '0': 'AAAAAA', '1': 'AABBAB', '2': 'AABBBA', '3': 'AABBBA',
            '4': 'ABAABB', '5': 'ABBAAB', '6': 'ABBBBA', '7': 'ABABAB',
            '8': 'ABABBA', '9': 'ABBABA'
        }
        
        # Pixmap más compacto
        pixmap = QPixmap(170, 50)
        pixmap.fill(Qt.white)
        painter = QPainter(pixmap)
        painter.setPen(QPen(Qt.black, 1))
        
        # Calcular ancho total del código para centrarlo
        # Guardas: 2 + 2 + 2 + 2 = 8
        # Primeros 6 dígitos: 6 * 7 = 42
        # Últimos 6 dígitos: 6 * 7 = 42
        # Total: 8 + 42 + 42 = 92 píxeles
        ancho_total = 92
        x_inicio = (170 - ancho_total) // 2  # Centrar
        
        x = x_inicio + 3
        y_start = 2
        y_bar_height = 38
        y_text_height = 35
        
        # Primera guarda (inicio)
        painter.drawLine(x, y_start, x, y_bar_height)
        x += 2
        painter.drawLine(x, y_start, x, y_bar_height)
        x += 2
        
        # Primeros 6 dígitos (con paridad)
        primer_digito = codigo_completo[0]
        seis_digitos = codigo_completo[1:7]
        patron_paridad = parity_pattern.get(primer_digito, 'AAAAAA')
        
        for i, digito in enumerate(seis_digitos):
            usar_code_a = patron_paridad[i] == 'A'
            patron = code_a[digito] if usar_code_a else code_b[digito]
            
            for bit in patron:
                if bit == '1':
                    painter.drawLine(x, y_start, x, y_text_height)
                x += 1
        
        # Guarda central
        x += 1
        painter.drawLine(x, y_start, x, y_bar_height)
        x += 1
        painter.drawLine(x, y_start, x, y_bar_height)
        x += 1
        
        # Últimos 6 dígitos (siempre código C invertido)
        seis_ultimos = codigo_completo[7:13]
        for digito in seis_ultimos:
            patron = code_c[digito]
            for bit in patron:
                if bit == '1':
                    painter.drawLine(x, y_start, x, y_text_height)
                x += 1
        
        # Guarda final
        x += 1
        painter.drawLine(x, y_start, x, y_bar_height)
        x += 1
        painter.drawLine(x, y_start, x, y_bar_height)
        
        painter.end()
        label_widget.setPixmap(pixmap)

    def _on_cantidad_enter(self):
        """Cuando se presiona Enter en cantidad"""
        self.btn_imprimir.setFocus()

    def _on_peso_enter(self):
        """Cuando se presiona Enter en peso"""
        try:
            peso = float(self.txt_peso.text())
            cod_fijo = self.txt_barras.text()[:7]
            
            if 20 < peso < 1000:
                num = str(int(peso)).zfill(5)
                dividendo = 1000 / peso
                precio_base = float(self.tbl_productos.item(self.tbl_productos.currentRow(), 7).text().replace(',', ''))
                nuevo_precio = precio_base / dividendo
            else:
                num = str(int(peso * 1000)).zfill(5) if peso < 1 else str(int(peso)).zfill(5)
                num_val = int(num)
                dividendo = num_val / 1000 if num_val > 1000 else num_val
                precio_base = float(self.tbl_productos.item(self.tbl_productos.currentRow(), 7).text().replace(',', ''))
                nuevo_precio = precio_base * dividendo
            
            self.txt_precio.setText(f"{nuevo_precio:,.2f}")
            
            # Calcular nuevo código
            cod_peso = num
            primera = cod_fijo + cod_peso
            suma_pares = (int(primera[1]) + int(primera[3]) + int(primera[5]) + 
                         int(primera[7]) + int(primera[9]) + int(primera[11])) * 3
            suma_impares = (int(primera[0]) + int(primera[2]) + int(primera[4]) + 
                           int(primera[6]) + int(primera[8]) + int(primera[10]))
            suma_total = suma_pares + suma_impares
            multiplo = int((suma_total / 10) + 0.95) * 10
            digito_control = multiplo - suma_total
            nuevo_codigo = primera + str(digito_control)
            
            self.txt_barras.setText(nuevo_codigo)
            
            # Actualizar labels
            self.label_codigo_part1.setText(nuevo_codigo[0])
            self.label_codigo_part2.setText(nuevo_codigo[1:7])
            self.label_codigo_part3.setText(nuevo_codigo[7:13])
            
            self.label_precio.setText(f"S/. {float(self.txt_precio.text().replace(',', '')) * peso:,.2f}")
            self.label_descripcion.setText(self.txt_descripcion_corta.text())
            
            if 20 < peso < 1000:
                self.label_peso.setText(f"{peso}GR.")
                self.lbl_und.setText("GR.")
            else:
                peso_kg = peso if peso < 1000 else peso / 1000
                self.label_peso.setText(f"{peso_kg}KG.")
                self.lbl_und.setText("KGR.")
            
            # Dibujar código
            self._paint_code_ean13(self.picture_preview, nuevo_codigo)
            self.txt_cantidad.setFocus()
        except Exception as e:
            QMessageBox.warning(self, "Error", f"Error al procesar peso: {e}")

    def _limpiar(self):
        """Limpia todos los campos"""
        self.txt_codigo.clear()
        self.txt_barras.clear()
        self.txt_descripcion_larga.clear()
        self.txt_descripcion_corta.clear()
        self.txt_envase.clear()
        self.txt_peso.clear()
        self.txt_cantidad.clear()
        self.txt_precio.clear()
        self.picture_preview.clear()
        self.label_precio.setText("S/.")
        self.label_descripcion.setText("Label2")
        self.label_peso.setText("Label4")
        self.label_codigo_part1.setText("0")
        self.label_codigo_part2.setText("L6")
        self.label_codigo_part3.setText("L7")
        self.btn_imprimir.setEnabled(False)

    def _imprimir(self):
        """Imprime las etiquetas (2 por página como en VB6)"""
        cantidad = int(self.txt_cantidad.text() or 0)
        if cantidad <= 1 or cantidad > 100:
            QMessageBox.warning(self, "Advertencia", "La cantidad debe ser mayor que 1 y menor de 100")
            return
        
        codigo_barras = self.txt_barras.text()
        
        respuesta = QMessageBox.question(
            self, "Confirmar Impresión",
            f"¿Está seguro que desea imprimir : {cantidad} etiquetas de: {codigo_barras}\nPuerto:",
            QMessageBox.Yes | QMessageBox.No
        )
        
        if respuesta == QMessageBox.Yes:
            printer = QPrinter(QPrinter.HighResolution)
            printer.setFullPage(True)
            
            preview = QPrintPreviewDialog(printer, self)
            preview.paintRequested.connect(
                lambda p: self._render_etiquetas(
                    p, codigo_barras, cantidad
                )
            )
            preview.exec()

    def _render_etiquetas(self, printer, codigo_barras, cantidad):
        """Renderiza las etiquetas para impresión - Modo continuo o por hoja"""
        try:
            painter = QPainter(printer)
            if not painter.isActive():
                print("Error: Painter no está activo")
                return
            
            painter.setRenderHint(QPainter.Antialiasing, False)  # Sin antialiasing como VB6
        except Exception as e:
            print(f"Error al inicializar painter: {e}")
            return
        
        desc_larga = self.txt_descripcion_larga.text()
        desc_corta = self.txt_descripcion_corta.text()
        precio = self.txt_precio.text().replace(',', '')
        peso = self.txt_peso.text()
        fecha = datetime.now().strftime("%d/%m/%y")
        
        # Generar pixmap del código de barras (igual que Picture1 en VB6)
        # VB6 Picture1: Width=1695, Height=615 (aproximadamente 170x62 píxeles)
        pixmap_barcode = QPixmap(170, 62)
        pixmap_barcode.fill(Qt.white)
        temp_painter = QPainter(pixmap_barcode)
        if temp_painter.isActive():
            temp_painter.setPen(QPen(Qt.black, 1))
            # Dibujar código de barras usando la misma lógica que PaintCode en VB6
            primer_digito = codigo_barras[0]
            seis_digitos = codigo_barras[1:7]
            seis_ultimos = codigo_barras[7:13]
            self._paint_code_vb6_style(temp_painter, pixmap_barcode, primer_digito, seis_digitos, seis_ultimos)
        temp_painter.end()
        
        # Obtener dimensiones de la página
        page_rect = printer.pageRect(QPrinter.Point)
        page_height = page_rect.height()
        page_width = page_rect.width()
        
        # Coordenadas de las dos columnas
        # Ancho aproximado de etiqueta: ~150 puntos
        ancho_etiqueta = 150.0
        margen_lateral = 30.0
        x_columna_izq = margen_lateral
        x_columna_der = (page_width / 2.0) + 10.0  # Mitad de la página para segunda columna
        
        # Altura aproximada de cada etiqueta (en puntos)
        # Con el escalado, la etiqueta completa mide aproximadamente 80-90 puntos
        altura_etiqueta = 90.0
        margen_vertical = 10.0
        margen_superior = 30.0
        
        # Determinar modo de impresión
        modo_continuo = self.check_impresion_continua.isChecked()
        
        if modo_continuo:
            # MODO CONTINUO: Imprimir todas las etiquetas seguidas en dos columnas
            y_actual = margen_superior
            columna_actual = 0  # 0 = izquierda, 1 = derecha
            etiquetas_impresas = 0
            
            while etiquetas_impresas < cantidad:
                # Verificar si necesitamos nueva página ANTES de dibujar
                if y_actual + altura_etiqueta + margen_vertical > page_height:
                    if etiquetas_impresas > 0:  # Solo nueva página si ya imprimimos algo
                        if not painter.isActive():
                            break
                        try:
                            printer.newPage()
                            # Verificar que el painter siga activo después de newPage
                            if not painter.isActive():
                                print("Warning: Painter se desactivó después de newPage")
                                break
                        except Exception as e:
                            print(f"Error al crear nueva página: {e}")
                            break
                    y_actual = margen_superior
                    columna_actual = 0
                
                # Verificar que el painter esté activo antes de dibujar
                if not painter.isActive():
                    break
                
                # Determinar coordenada X según columna
                x_offset = x_columna_izq if columna_actual == 0 else x_columna_der
                
                # Verificar que las coordenadas sean válidas
                if x_offset < 0 or y_actual < 0:
                    break
                
                # Imprimir etiqueta
                try:
                    self._imprimir_etiqueta_vb6_style(
                        painter, x_offset, y_actual,
                        desc_larga, desc_corta, precio, peso, fecha,
                        pixmap_barcode, codigo_barras
                    )
                except Exception as e:
                    print(f"Error al imprimir etiqueta: {e}")
                    break
                
                etiquetas_impresas += 1
                
                # Cambiar de columna después de cada etiqueta
                if columna_actual == 0:
                    columna_actual = 1
                else:
                    columna_actual = 0
                    y_actual += altura_etiqueta + margen_vertical
        else:
            # MODO POR HOJA: Mantener lógica original (2 etiquetas por página)
            etiquetas_por_pagina = 2
            total_paginas = (cantidad + 1) // etiquetas_por_pagina
            
            etiquetas_impresas = 0
            
            for pagina in range(total_paginas):
                # Primera etiqueta de la página (izquierda)
                if etiquetas_impresas < cantidad:
                    self._imprimir_etiqueta_vb6_style(
                        painter, x_columna_izq, margen_superior,
                        desc_larga, desc_corta, precio, peso, fecha,
                        pixmap_barcode, codigo_barras
                    )
                    etiquetas_impresas += 1
                
                # Segunda etiqueta de la página (derecha)
                if etiquetas_impresas < cantidad:
                    self._imprimir_etiqueta_vb6_style(
                        painter, x_columna_der, margen_superior,
                        desc_larga, desc_corta, precio, peso, fecha,
                        pixmap_barcode, codigo_barras
                    )
                    etiquetas_impresas += 1
                
                # Nueva página después de cada par (excepto la última)
                if etiquetas_impresas < cantidad:
                    try:
                        if painter.isActive():
                            printer.newPage()
                            if not painter.isActive():
                                print("Error: Painter se desactivó después de newPage")
                                break
                    except Exception as e:
                        print(f"Error al crear nueva página: {e}")
                        break
        
        try:
            if painter.isActive():
                painter.end()
        except Exception as e:
            print(f"Error al cerrar painter: {e}")

    def _paint_code_vb6_style(self, painter, pixmap, primer_digito, seis_digitos, seis_ultimos):
        """Dibuja código de barras EAN-13 usando la lógica exacta de PaintCode de VB6"""
        if not painter.isActive():
            return
        
        # Patrones de código EAN-13 (igual que VB6)
        code_a = {
            '0': '0001101', '1': '0011001', '2': '0010011', '3': '0111101',
            '4': '0100011', '5': '0110001', '6': '0101111', '7': '0111011',
            '8': '0110111', '9': '0001011'
        }
        code_b = {
            '0': '0100111', '1': '0110011', '2': '0011011', '3': '0100001',
            '4': '0011101', '5': '0111001', '6': '0000101', '7': '0010001',
            '8': '0001001', '9': '0010111'
        }
        code_c = {
            '0': '1110010', '1': '1100110', '2': '1101100', '3': '1000010',
            '4': '1011100', '5': '1001110', '6': '1010000', '7': '1000100',
            '8': '1001000', '9': '1110100'
        }
        parity_pattern = {
            '0': 'AAAAAA', '1': 'AABBAB', '2': 'AABBAB', '3': 'AABBBA',
            '4': 'ABAABB', '5': 'ABBAAB', '6': 'ABBBBA', '7': 'ABABAB',
            '8': 'ABABBA', '9': 'ABBABA'
        }
        
        painter.setPen(QPen(Qt.black, 1))
        
        # Primera guarda (líneas 1 y 3, desplazadas +10 como en VB6)
        x = 1 + 10
        painter.drawLine(x, 0, x, 35)
        x = 3 + 10
        painter.drawLine(x, 0, x, 35)
        
        # Primeros 6 dígitos (con paridad A o B)
        reihe = parity_pattern.get(primer_digito, 'AAAAAA')
        for z in range(6):
            digito = seis_digitos[z]
            if reihe[z] == 'A':
                patron = code_a[digito]
            else:
                patron = code_b[digito]
            
            # Dibujar los 7 bits del patrón
            for d in range(7):
                x_pos = (z * 7) + d + 3 + 10
                if patron[d] == '1':
                    painter.drawLine(x_pos, 0, x_pos, 30)  # Línea negra
                # else: línea blanca (no se dibuja, fondo blanco)
        
        # Guarda central
        x = (6 * 7) + 5 + 10
        painter.drawLine(x, 0, x, 35)
        x = (6 * 7) + 7 + 10
        painter.drawLine(x, 0, x, 35)
        
        # Últimos 6 dígitos (siempre código C)
        for z in range(6):
            digito = seis_ultimos[z]
            patron = code_c[digito]
            
            # Dibujar los 7 bits del patrón (desplazados +50 como en VB6)
            for d in range(7):
                x_pos = (z * 7) + d + 50 + 10
                if patron[d] == '1':
                    painter.drawLine(x_pos, 0, x_pos, 30)  # Línea negra
        
        # Guarda final
        x = 94 + 9
        painter.drawLine(x, 0, x, 35)
        x = 96 + 9
        painter.drawLine(x, 0, x, 35)
    
    def _imprimir_etiqueta_vb6_style(self, painter, x_offset, y_offset, desc_larga, desc_corta, precio, peso, fecha, pixmap_barcode, codigo_barras):
        """Imprime una etiqueta usando la lógica exacta de VB6 (imprimirCodigoEAN)
        x_offset y y_offset están en puntos (ya convertidos)
        """
        # x_offset y y_offset ya están en puntos
        x_base = x_offset
        y_base = y_offset
        
        # Escalar coordenadas y fuentes para que sean visibles
        # Factor de escala: multiplicar por 1.5 para hacer más grande
        scale = 1.5
        
        # Descripción larga línea 1 (VB6: CurrentX = x_offset, CurrentY = 0, FontSize = 4.8)
        font_arial = QFont("Arial", int(8 * scale))  # Escalado
        painter.setFont(font_arial)
        painter.setPen(QPen(Qt.black, 1))
        
        # VB6: Printer.Print Mid$(Me.txtdescripcionlarga.Text, 1, 28)
        desc_line1 = desc_larga[:28] if len(desc_larga) > 28 else desc_larga
        painter.drawText(int(x_base), int(y_base), desc_line1)
        
        # Descripción larga línea 2 (VB6: CurrentX = x_offset + 100, CurrentY = 100)
        x_line2 = x_base + (5.0 * scale)  # Escalado
        y_line2 = y_base + (8.0 * scale)  # Escalado
        desc_line2 = desc_larga[28:48] if len(desc_larga) > 28 else ""
        if desc_line2:
            painter.drawText(int(x_line2), int(y_line2), desc_line2)
        
        # Código de barras (VB6: Printer.PaintPicture Me.Picture1.Image, 90, 220)
        barcode_x = x_base + (4.5 * scale)  # Escalado
        barcode_y = y_base + (15.0 * scale)  # Escalado
        
        if pixmap_barcode and not pixmap_barcode.isNull():
            # Escalar el pixmap para que sea más grande
            scaled_pixmap = pixmap_barcode.scaled(
                int(pixmap_barcode.width() * scale),
                int(pixmap_barcode.height() * scale),
                Qt.KeepAspectRatio,
                Qt.SmoothTransformation
            )
            painter.drawPixmap(int(barcode_x), int(barcode_y), scaled_pixmap)
        
        # Código de barras como texto (VB6: CurrentY = 750, CurrentX = 80, FontSize = 6.5)
        font_courier = QFont("Courier New", int(10 * scale))  # Escalado
        painter.setFont(font_courier)
        cod_b = f"{codigo_barras[0]}   {codigo_barras[1:7]}  {codigo_barras[7:13]}"
        cod_x = x_base + (4.0 * scale)
        cod_y = y_base + (50.0 * scale)  # Escalado
        painter.drawText(int(cod_x), int(cod_y), cod_b)
        
        # Fecha (VB6: CurrentY = 870, CurrentX = 100, FontSize = 5.3)
        font_arial_small = QFont("Arial", int(8 * scale))  # Escalado
        painter.setFont(font_arial_small)
        fecha_texto = f"Empacado el:{fecha}"
        fecha_x = x_base + (5.0 * scale)
        fecha_y = y_base + (58.0 * scale)  # Escalado
        painter.drawText(int(fecha_x), int(fecha_y), fecha_texto)
        
        # Precio (VB6: CurrentY = 980, CurrentX = 100, FontSize = 5.5, Bold = True)
        font_precio = QFont("Arial", int(9 * scale))  # Escalado
        font_precio.setBold(True)
        painter.setFont(font_precio)
        precio_texto = f"S/. {precio}  "
        precio_x = x_base + (5.0 * scale)
        precio_y = y_base + (65.0 * scale)  # Escalado
        painter.drawText(int(precio_x), int(precio_y), precio_texto)
        
        # Peso (VB6: CurrentY = 870, CurrentX = 1250 para primera, 3480 para segunda)
        if peso:
            font_peso = QFont("Arial", int(9 * scale))  # Escalado
            painter.setFont(font_peso)
            peso_texto = f"Peso: {peso}"
            peso_x = x_base + (80.0 * scale)  # Escalado
            peso_y = y_base + (58.0 * scale)  # Escalado
            painter.drawText(int(peso_x), int(peso_y), peso_texto)
        
        # Nombre empresa (VB6: CurrentY = 1100, CurrentX = 100, FontSize = 6.5, Bold = True)
        font_empresa = QFont("Courier New", int(10 * scale))  # Escalado
        font_empresa.setBold(True)
        painter.setFont(font_empresa)
        empresa_texto = "EL SUPER SAC"
        empresa_x = x_base + (5.0 * scale)
        empresa_y = y_base + (72.0 * scale)  # Escalado
        painter.drawText(int(empresa_x), int(empresa_y), empresa_texto)
        
        # Texto promocional (VB6: CurrentY = 1000, CurrentX = 1070/3300, FontSize = 4.5, Italic = True)
        font_italic = QFont("Arial", int(7 * scale))  # Escalado
        font_italic.setItalic(True)
        painter.setFont(font_italic)
        promo_x1 = x_base + (70.0 * scale)  # Escalado
        promo_x2 = x_base + (75.0 * scale)  # Escalado
        promo_y1 = y_base + (63.0 * scale)  # Escalado
        promo_y2 = y_base + (66.0 * scale)  # Escalado
        painter.drawText(int(promo_x1), int(promo_y1), "Siempre de todo a")
        painter.drawText(int(promo_x2), int(promo_y2), "Menor precio")

    def _paint_code_ean13_detailed(self, painter, pixmap, codigo_completo):
        """Versión detallada para impresión del código EAN-13"""
        if len(codigo_completo) != 13:
            return
        
        if not painter.isActive():
            return
        
        code_a = {
            '0': '0001101', '1': '0011001', '2': '0010011', '3': '0111101',
            '4': '0100011', '5': '0110001', '6': '0101111', '7': '0111011',
            '8': '0110111', '9': '0001011'
        }
        
        code_b = {
            '0': '0100111', '1': '0110011', '2': '0011011', '3': '0100001',
            '4': '0011101', '5': '0111001', '6': '0000101', '7': '0010001',
            '8': '0001001', '9': '0010111'
        }
        
        code_c = {
            '0': '1110010', '1': '1100110', '2': '1101100', '3': '1000010',
            '4': '1011100', '5': '1001110', '6': '1010000', '7': '1000100',
            '8': '1001000', '9': '1110100'
        }
        
        parity_pattern = {
            '0': 'AAAAAA', '1': 'AABBAB', '2': 'AABBBA', '3': 'AABBBA',
            '4': 'ABAABB', '5': 'ABBAAB', '6': 'ABBBBA', '7': 'ABABAB',
            '8': 'ABABBA', '9': 'ABBABA'
        }
        
        # Obtener dimensiones del pixmap
        pixmap_width = pixmap.width()
        pixmap_height = pixmap.height()
        
        # Calcular ancho total del código de barras (escalado)
        # Ancho base: ~92 píxeles para 170px de ancho
        # Escalar proporcionalmente al pixmap
        scale_factor = pixmap_width / 170.0
        ancho_total = int(92 * scale_factor)
        
        # Centrar el código
        x_inicio = (pixmap_width - ancho_total) // 2
        
        # Alturas escaladas
        y_start = 5
        y_bar_height = int(pixmap_height * 0.75)  # 75% de la altura
        y_text_height = int(pixmap_height * 0.65)  # 65% de la altura
        
        # Ancho de línea escalado
        line_width = max(1, int(2 * scale_factor))
        painter.setPen(QPen(Qt.black, line_width))
        
        x = x_inicio + int(3 * scale_factor)
        
        # Primera guarda
        painter.drawLine(x, y_start, x, y_bar_height)
        x += int(2 * scale_factor)
        painter.drawLine(x, y_start, x, y_bar_height)
        x += int(2 * scale_factor)
        
        # Primeros 6 dígitos
        primer_digito = codigo_completo[0]
        seis_digitos = codigo_completo[1:7]
        patron_paridad = parity_pattern.get(primer_digito, 'AAAAAA')
        
        for i, digito in enumerate(seis_digitos):
            usar_code_a = patron_paridad[i] == 'A'
            patron = code_a[digito] if usar_code_a else code_b[digito]
            
            for bit in patron:
                if bit == '1':
                    painter.drawLine(x, y_start, x, y_text_height)
                x += int(scale_factor)
        
        # Guarda central
        x += int(scale_factor)
        painter.drawLine(x, y_start, x, y_bar_height)
        x += int(scale_factor)
        painter.drawLine(x, y_start, x, y_bar_height)
        x += int(scale_factor)
        
        # Últimos 6 dígitos
        seis_ultimos = codigo_completo[7:13]
        for digito in seis_ultimos:
            patron = code_c[digito]
            for bit in patron:
                if bit == '1':
                    painter.drawLine(x, y_start, x, y_text_height)
                x += int(scale_factor)
        
        # Guarda final
        x += int(scale_factor)
        painter.drawLine(x, y_start, x, y_bar_height)
        x += int(scale_factor)
        painter.drawLine(x, y_start, x, y_bar_height)

    def closeEvent(self, event):
        """Maneja el cierre de la ventana"""
        try:
            if self.usuario:
                self.db.set_estado_conectado(self.usuario, False)
            if self.main_window and self.main_window.isVisible():
                try:
                    rol = self.db.get_rol_usuario(self.usuario)
                    if rol == 'barras':
                        self.main_window.close()
                except:
                    pass
        except Exception as e:
            print(f"Error al cerrar sesión: {e}")
        finally:
            event.accept()

