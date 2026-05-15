from PySide6.QtWidgets import (QMainWindow, QWidget, QVBoxLayout, QLabel, QLineEdit, QComboBox, QPushButton,
                               QFormLayout, QHBoxLayout, QMessageBox, QCheckBox, QSpinBox, QFrame, QScrollArea, QGroupBox, QGridLayout)
from PySide6.QtGui import QRegularExpressionValidator, QDoubleValidator
from PySide6.QtCore import Qt, QRegularExpression

class ProductoForm(QWidget):
    def __init__(self, db, usuario_actual):
        super().__init__()
        self.db = db
        self.usuario_actual = usuario_actual
        self.setWindowTitle("Registro de Producto")
        self.setGeometry(200, 200, 900, 700)
        self.setStyleSheet(self._get_styles())

        main_layout = QVBoxLayout()
        scroll_area = QScrollArea()
        scroll_area.setWidgetResizable(True)
        scroll_content = QWidget()
        layout = QVBoxLayout(scroll_content)
        layout.setSpacing(15)
        layout.setContentsMargins(15, 15, 15, 15)

        # Inicializar widgets
        self.departamento_combo = QComboBox()
        self.subdepto_combo = QComboBox()
        self.clase_combo = QComboBox()

        self.departamentos = self.db.get_departamentos()
        self.departamento_combo.addItem("-- Seleccione --")
        for d in self.departamentos:
            self.departamento_combo.addItem(d[1], userData=d[0])

        self.departamento_combo.currentIndexChanged.connect(self.actualizar_subdepto)
        self.subdepto_combo.currentIndexChanged.connect(self.actualizar_clase)

        self.sku = QLineEdit()
        self.sku.setReadOnly(True)
        self.generar_sku()
        self.sku.editingFinished.connect(lambda: self.mover_cursor(6))

        self.desc_larga = QLineEdit()
        self.desc_larga.textChanged.connect(self.convertir_a_mayusculas)
        self.desc_larga.editingFinished.connect(lambda: self.mover_cursor(1))
        self.desc_corta = QLineEdit()
        self.desc_corta.textChanged.connect(self.convertir_a_mayusculas)
        self.desc_corta.setMaxLength(20)
        self.desc_corta.editingFinished.connect(lambda: self.mover_cursor(2))

        self.codigo_barra = QLineEdit()
        self.codigo_barra.setMaxLength(15)
        validator = QDoubleValidator()
        validator.setRange(0, 9999999999999, 0)
        self.codigo_barra.setValidator(validator)
        self.codigo_barra.editingFinished.connect(lambda: self.mover_cursor(3))

        self.plu = QLineEdit()
        self.plu.setReadOnly(True)
        self.plu.setMaxLength(6)
        self.plu.setValidator(validator)
        self.plu.editingFinished.connect(lambda: self.mover_cursor(4))

        self.unidad_medida = QComboBox()
        self.unidad_medida.addItems(["UND", "KG", "PACK", "CJA"])
        self.unidad_medida.currentTextChanged.connect(self.activar_plu)

        self.tipo_producto = QComboBox()
        self.tipo_producto.addItems(["Mercadería", "Insumo", "Clase100"])
        self.tipo_producto.editTextChanged.connect(lambda: self.mover_cursor(5))

        self.stock = QSpinBox()
        self.stock.setRange(1, 99999)
        self.stock.editingFinished.connect(lambda: self.mover_cursor(7))

        self.costo = QLineEdit()
        self.precio = QLineEdit()
        self.igv = QLineEdit()
        self.valor_venta = QLineEdit()
        self.ganancia = QLineEdit()
        self.igv.setReadOnly(True)
        self.igv.editingFinished.connect(lambda: self.mover_cursor(10))
        self.valor_venta.setReadOnly(True)
        self.valor_venta.editingFinished.connect(lambda: self.mover_cursor(11))
        self.ganancia.setReadOnly(True)
        self.ganancia.editingFinished.connect(lambda: self.mover_cursor(12))

        self.afecto_igv = QCheckBox("Afecto IGV")
        self.afecto_igv.setChecked(True)
        self.afecto_igv.stateChanged.connect(self.calcular_igv)
        self.ganancia.editingFinished.connect(lambda: self.mover_cursor(13))
        self.costo.textChanged.connect(self.calcular_precio)
        self.costo.editingFinished.connect(lambda: self.mover_cursor(8))
        self.precio.textChanged.connect(self.calcular_igv)
        self.precio.editingFinished.connect(lambda: self.mover_cursor(9))

        self.activar_extra = QCheckBox("Mostrar Campos Avanzados")
        self.activar_extra.stateChanged.connect(self.activa_extra_frame)
        
        # Campos avanzados
        self.marca = QLineEdit()
        self.marca.setPlaceholderText("Marca")
        self.marca.textChanged.connect(self.convertir_a_mayusculas)
        self.marca.editingFinished.connect(lambda: self.mover_cursor(14))
        
        self.registro_sanitario = QLineEdit()
        self.registro_sanitario.setPlaceholderText("Registro")
        self.registro_sanitario.editingFinished.connect(lambda: self.mover_cursor(15))
        
        self.stock_minimo = QSpinBox()
        self.stock_minimo.setRange(0, 99999)
        self.stock_minimo.setValue(0)
        self.stock_minimo.editingFinished.connect(lambda: self.mover_cursor(16))
        
        self.precio_regular = QLineEdit()
        self.precio_regular.setPlaceholderText("0.00")
        self.precio_regular.editingFinished.connect(lambda: self.mover_cursor(17))

        self.check_promocion = QCheckBox("Activar Promoción")
        self.check_promocion.stateChanged.connect(self.activa_promocion)
        
        self.precio_promocion = QLineEdit()
        self.precio_promocion.setPlaceholderText("0.00")
        self.precio_promocion.textChanged.connect(self.calcular_precio_promo)
        self.precio_promocion.editingFinished.connect(lambda: self.mover_cursor(18))
        
        self.descuento = QLineEdit()
        self.descuento.setPlaceholderText("0.00%")
        self.descuento.editingFinished.connect(lambda: self.mover_cursor(19))
        self.descuento.setReadOnly(True)
        
        self.costo_reposicion = QLineEdit()
        self.costo_reposicion.setPlaceholderText("0.00")
        self.costo_reposicion.editingFinished.connect(lambda: self.mover_cursor(20))
        
        self.dias_retiro = QSpinBox()
        self.dias_retiro.setRange(1, 365)
        self.dias_retiro.setValue(1)
        self.dias_retiro.editingFinished.connect(lambda: self.mover_cursor(21))
        
        self.case_pack = QLineEdit()
        self.case_pack.setPlaceholderText("12")
        self.case_pack.editingFinished.connect(lambda: self.mover_cursor(22))
        
        # Frame de campos avanzados
        self.extra_frame = QGroupBox("🔧 Campos Avanzados")
        self.extra_frame.setVisible(False)
        
        # === ESTRUCTURA PRINCIPAL (2 COLUMNAS) ===
        main_grid = QGridLayout()
        main_grid.setColumnStretch(0, 2)  # Columna izquierda más ancha
        main_grid.setColumnStretch(1, 1)  # Columna derecha más estrecha
        
        # === COLUMNA IZQUIERDA ===
        
        # Grupo de Clasificación
        clasificacion_group = QGroupBox("🏷️ Clasificación del Producto")
        clasificacion_layout = QFormLayout()
        clasificacion_layout.addRow("Departamento:", self.departamento_combo)
        clasificacion_layout.addRow("Subdepartamento:", self.subdepto_combo)
        clasificacion_layout.addRow("Clase:", self.clase_combo)
        clasificacion_group.setLayout(clasificacion_layout)
        main_grid.addWidget(clasificacion_group, 0, 0)
        
        # Grupo de Información Básica
        basica_group = QGroupBox("📝 Información Básica")
        basica_layout = QGridLayout()
        basica_layout.addWidget(QLabel("SKU:"), 0, 0)
        basica_layout.addWidget(self.sku, 0, 1)
        basica_layout.addWidget(QLabel("Código de Barras:"), 0, 2)
        basica_layout.addWidget(self.codigo_barra, 0, 3)
        
        basica_layout.addWidget(QLabel("Descripción Larga:"), 1, 0, 1, 4)
        basica_layout.addWidget(self.desc_larga, 2, 0, 1, 4)
        
        basica_layout.addWidget(QLabel("Descripción Corta:"), 3, 0)
        basica_layout.addWidget(self.desc_corta, 3, 1)
        basica_layout.addWidget(QLabel("Tipo de Mercadería:"), 3, 2)
        basica_layout.addWidget(self.tipo_producto, 3, 3)
        
        basica_layout.addWidget(QLabel("Unidad Medida:"), 4, 0)
        basica_layout.addWidget(self.unidad_medida, 4, 1)
        basica_layout.addWidget(QLabel("PLU:"), 4, 2)
        basica_layout.addWidget(self.plu, 4, 3)
        basica_layout.addWidget(QLabel("Inventario inicial:"), 4, 4)
        basica_layout.addWidget(self.stock, 4, 5)
        
        basica_group.setLayout(basica_layout)
        main_grid.addWidget(basica_group, 1, 0)
        
        # === COLUMNA DERECHA ===
        
        # Grupo de Información de Precios
        precios_group = QGroupBox("💰 Información de Precios")
        precios_layout = QFormLayout()
        precios_layout.addRow("Costo:", self.costo)
        precios_layout.addRow("Precio de Venta:", self.precio)
        precios_layout.addRow("IGV:", self.igv)
        precios_layout.addRow("Valor de Venta:", self.valor_venta)
        precios_layout.addRow("% Margen Ganancia:", self.ganancia)
        precios_layout.addRow("Afecto IGV:", self.afecto_igv)
        precios_group.setLayout(precios_layout)
        main_grid.addWidget(precios_group, 0, 1)
        
        # Grupo de Configuración (Checkbox Campos Avanzados)
        config_group = QGroupBox("⚙️ Configuración")
        config_layout = QVBoxLayout()
        config_layout.addWidget(self.activar_extra)
        config_group.setLayout(config_layout)
        main_grid.addWidget(config_group, 1, 1)
        
        # === CAMPOS AVANZADOS (OCUPAN AMBAS COLUMNAS) ===
        campos_avanzados_layout = QGridLayout()
        
        # Fila 1
        campos_avanzados_layout.addWidget(QLabel("Marca:"), 0, 0)
        campos_avanzados_layout.addWidget(self.marca, 0, 1)
        campos_avanzados_layout.addWidget(QLabel("Reg. Sanitario:"), 0, 2)
        campos_avanzados_layout.addWidget(self.registro_sanitario, 0, 3)
        campos_avanzados_layout.addWidget(QLabel("Inventario mínimo:"), 0, 4)
        campos_avanzados_layout.addWidget(self.stock_minimo, 0, 5)
        
        # Fila 2
        campos_avanzados_layout.addWidget(self.check_promocion, 1, 0, 1, 2)
        
        # Fila 3
        campos_avanzados_layout.addWidget(QLabel("Precio Regular:"), 2, 0)
        campos_avanzados_layout.addWidget(self.precio_regular, 2, 1)
        campos_avanzados_layout.addWidget(QLabel("Precio Promoción:"), 2, 2)
        campos_avanzados_layout.addWidget(self.precio_promocion, 2, 3)
        
        # Fila 4
        campos_avanzados_layout.addWidget(QLabel("Costo de Reposición:"), 3, 0)
        campos_avanzados_layout.addWidget(self.costo_reposicion, 3, 1)
        campos_avanzados_layout.addWidget(QLabel("% Descuento:"), 3, 2)
        campos_avanzados_layout.addWidget(self.descuento, 3, 3)
        
        # Fila 5
        campos_avanzados_layout.addWidget(QLabel("Días de Retiro:"), 4, 0)
        campos_avanzados_layout.addWidget(self.dias_retiro, 4, 1)
        campos_avanzados_layout.addWidget(QLabel("Unid. por empaque:"), 4, 2)
        campos_avanzados_layout.addWidget(self.case_pack, 4, 3)
        
        self.extra_frame.setLayout(campos_avanzados_layout)
        main_grid.addWidget(self.extra_frame, 2, 0, 1, 2)  # Ocupa ambas columnas
        
        # === BOTONES DE ACCIÓN ===
        botones_group = QGroupBox("💾 Acciones")
        botones_layout = QHBoxLayout()
        
        # Botón limpiar
        self.btn_limpiar = QPushButton("🧹 Limpiar")
        self.btn_limpiar.setStyleSheet("QPushButton { background: #f39c12; color: white; }")
        self.btn_limpiar.clicked.connect(self.limpiar_formulario)
        botones_layout.addWidget(self.btn_limpiar)
        
        # Espaciador
        botones_layout.addStretch()
        
        # Botón guardar
        self.btn_guardar = QPushButton("💾 Guardar Producto")
        self.btn_guardar.setStyleSheet("QPushButton { background: #27ae60; color: white; }")
        self.btn_guardar.clicked.connect(self.guardar_producto)
        botones_layout.addWidget(self.btn_guardar)
        
        botones_group.setLayout(botones_layout)
        main_grid.addWidget(botones_group, 3, 0, 1, 2)  # Ocupa ambas columnas
        
        # Agregar layout principal
        layout.addLayout(main_grid)
       
        # Configurar scroll area
        scroll_area.setWidget(scroll_content)
        main_layout.addWidget(scroll_area)
        self.setLayout(main_layout)

    def _get_styles(self):
        """Retorna los estilos CSS para el formulario"""
        return """
        QWidget {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11px;
            background-color: #f5f7fa;
        }
        
        QGroupBox {
            font-weight: bold;
            font-size: 12px;
            color: #2c3e50;
            border: 2px solid #5d6d7e;
            border-radius: 8px;
            margin-top: 10px;
            padding-top: 10px;
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
            padding: 8px 12px;
            border: 2px solid #5d6d7e;
            border-radius: 6px;
            font-size: 11px;
            background-color: white;
            color: #2c3e50;
            min-height: 20px;
        }
        
        QLineEdit:focus {
            border-color: #3498db;
            background-color: #ffffff;
        }
        
        QLineEdit:read-only {
            background-color: #e8edf4;
            color: #6c757d;
        }
        
        QPushButton {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 12px;
            color: white;
            min-height: 25px;
        }
        
        QPushButton:hover {
            opacity: 0.9;
        }
        
        QComboBox {
            padding: 8px 12px;
            border: 2px solid #5d6d7e;
            border-radius: 6px;
            font-size: 11px;
            background-color: white;
            color: #2c3e50;
            min-height: 20px;
            font-weight: bold;
        }
        
        QComboBox:focus {
            border-color: #3498db;
        }
        
        QComboBox::drop-down {
            border: none;
            width: 20px;
        }
        
        QComboBox::down-arrow {
            image: none;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-top: 5px solid #2c3e50;
            margin-right: 5px;
        }
        
        QCheckBox {
            font-size: 11px;
            color: #2c3e50;
            font-weight: bold;
            spacing: 8px;
        }
        
        QCheckBox::indicator {
            width: 18px;
            height: 18px;
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
        
        QSpinBox {
            padding: 8px 12px;
            border: 2px solid #5d6d7e;
            border-radius: 6px;
            font-size: 11px;
            background-color: white;
            color: #2c3e50;
            min-height: 20px;
        }
        
        QLabel {
            color: #2c3e50;
            font-size: 11px;
            font-weight: bold;
        }
        """

    def mover_cursor(self, valor):
        if valor==1:
            self.desc_corta.setFocus()
        elif valor == 2:
            self.codigo_barra.setFocus()
        elif valor == 3:
            self.unidad_medida.setFocus()
        elif valor == 4:
            self.tipo_producto.setFocus()
        elif valor == 5:
            self.stock.setFocus()
        elif valor == 6:
            self.desc_larga.setFocus()
        elif valor == 7:
            self.costo.setFocus()
        elif valor == 8:
            self.precio.setFocus()
        elif valor == 9:
            self.igv.setFocus()
        elif valor == 10:
            self.valor_venta.setFocus()
        elif valor == 11:
            self.ganancia.setFocus()
        elif valor == 12:
            self.afecto_igv.setFocus()
        elif valor == 13:
            self.activar_extra.setFocus()
        elif valor == 14:
            self.registro_sanitario.setFocus()
        elif valor == 15:
            self.stock_minimo.setFocus()
        elif valor == 16:
            self.precio_regular.setFocus()
        elif valor == 17:
            self.check_promocion.setFocus()
        elif valor == 18:
            self.descuento.setFocus()
        elif valor == 19:
            self.costo_reposicion.setFocus()
        elif valor == 20:
            self.dias_retiro.setFocus()
        elif valor == 21:
            self.case_pack.setFocus()
        elif valor == 22:
            self.btn_guardar.setFocus()
        else:
            return "Número fuera de rango"

    def guardar_producto(self):
        if not self.validar_producto():
            return

        try:
            clase_id = self.clase_combo.currentData()
            descripcion_larga = self.desc_larga.text().strip().upper()
            descripcion_corta = self.desc_corta.text().strip().upper()
            sku = self.sku.text().strip()
            nombre = f"{sku} - {descripcion_larga}"
            codigo_barra = self.codigo_barra.text().strip()
            plu = self.plu.text().strip() if self.unidad_medida.currentText() == "KG" else None
            estado = True
            tipo_estado = 'activo'
            unidad_medida = self.unidad_medida.currentText()
            tipo_producto = self.tipo_producto.currentText()
            stock_contable = self.stock.value()
            stock_disponible = stock_contable
            costo_compra = float(self.costo.text()) if self.costo.text() else 0.0
            precio_venta = float(self.precio.text()) if self.precio.text() else 0.0
            valor_venta = float(self.valor_venta.text()) if self.valor_venta.text() else 0.0
            valor_igv = float(self.igv.text()) if self.igv.text() else 0.0
            afecto_igv = self.afecto_igv.isChecked()

            marca = self.marca.text().strip() if self.activar_extra.isChecked() else None
            registro_sanitario = self.registro_sanitario.text().strip() if self.activar_extra.isChecked() else None
            stock_minimo = self.stock_minimo.value() if self.activar_extra.isChecked() else None
            precio_regular = float(self.precio_regular.text()) if self.precio_regular.text() else None
            activo_promocion = self.check_promocion.isChecked() if self.activar_extra.isChecked() else False
            precio_promocion = float(self.precio_promocion.text()) if self.precio_promocion.text() and activo_promocion else None
            descuento = float(self.descuento.text().replace('%','')) if self.descuento.text() else None
            costo_reposicion = float(self.costo_reposicion.text()) if self.costo_reposicion.text() else None
            dias_retiro = self.dias_retiro.value() if self.activar_extra.isChecked() else None
            case_pack = int(self.case_pack.text()) if self.case_pack.text().isdigit() else None

            # Obtener nombres para categoría y clase
            departamento = self.departamento_combo.currentText()
            subdepto = self.subdepto_combo.currentText()
            clase = self.clase_combo.currentText()
            categoria = f"{departamento} > {subdepto} > {clase}"

            datos = (
                clase_id, descripcion_larga, descripcion_corta, sku,
                nombre, codigo_barra, plu, costo_compra, costo_compra,
                costo_reposicion, precio_regular, valor_venta,
                precio_venta, activo_promocion, precio_promocion,
                descuento, afecto_igv, valor_igv, stock_contable,
                stock_disponible, stock_minimo, False,  # pesable False por defecto
                unidad_medida, marca, registro_sanitario, dias_retiro,
                case_pack, tipo_producto, categoria, clase,
                estado, tipo_estado, self.usuario_actual
            )

            producto_id = self.db.insertar_producto(datos)

            self.db.insertar_producto_precio(
                producto_id=producto_id,
                descripcion_larga=descripcion_larga,
                costo_nuevo=costo_compra,
                costo_promedio=costo_compra,
                valor_venta_nuevo=valor_venta,
                precio_venta_nuevo=precio_venta,
                usuario=self.usuario_actual
            )

            QMessageBox.information(self, "Éxito", "Producto registrado correctamente.")
            self.limpiar_formulario()

        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error al guardar: {str(e)}")

    def activa_extra_frame(self, state):
        stock_min = self.stock.value()
        precio_reg = float(self.precio.text()) if self.precio.text() else 0.0
        costo_rep = float(self.costo.text()) if self.costo.text() else 0.0
        if self.activar_extra.isChecked():
            self.showMaximized()
            self.extra_frame.setVisible(True)
            self.stock_minimo.setValue(stock_min)
            self.precio_regular.setText(str(precio_reg))
            self.costo_reposicion.setText(str(costo_rep))
            self.marca.setFocus()
            self.case_pack.setText("12")
        else:  
            self.extra_frame.setVisible(False)
            self.stock_minimo.setValue(0) 
            self.precio_regular.setText("0.00")  
            self.costo_reposicion.setText("0.00")  
            self.case_pack.clear() 
            self.showNormal()

    def activa_promocion(self, state):
        precio_promo = float(self.precio.text()) if self.precio.text() else 0.0
        if self.check_promocion.isChecked():
            self.precio_promocion.setText(str(round((precio_promo*0.95),2)))
            self.precio_promocion.setEnabled(True)
            self.descuento.setText("5.00%")
            self.precio_promocion.setFocus()
        else: 
            self.precio_promocion.setText("0.00")               
            self.precio_promocion.setEnabled(False)
            self.descuento.clear()

    def convertir_a_mayusculas(self):
        cursor_pos = self.desc_larga.cursorPosition()  # Guardar posición del cursor
        texto = self.desc_larga.text()
        texto2= self.desc_corta.text()
        texto3= self.marca.text()
        self.desc_larga.setText(texto.upper())
        self.desc_corta.setText(texto2.upper())
        self.marca.setText(texto3.upper())
        self.desc_larga.setCursorPosition(cursor_pos)  # Restaurar posición

    def actualizar_subdepto(self):
        self.subdepto_combo.clear()
        self.clase_combo.clear()
        idx = self.departamento_combo.currentIndex()
        if idx > 0:
            depto_id = self.departamento_combo.currentData()
            subdepto_list = self.db.get_subdepartamentos(depto_id)
            for s in subdepto_list:
                self.subdepto_combo.addItem(s[1], userData=s[0])

    def actualizar_clase(self):
        self.clase_combo.clear()
        idx = self.subdepto_combo.currentIndex()
        if idx >= 0:
            sub_id = self.subdepto_combo.currentData()
            clase_list = self.db.get_clases(sub_id)
            for c in clase_list:
                self.clase_combo.addItem(c[1], userData=c[0])

    def activar_plu(self, text):
        if text == "KG":
            correlativo = 100 + self.db.get_correlativo_sku()
            self.plu.setText(str(correlativo))
            self.plu.setReadOnly(False)
            self.plu.setFocus()
        else:
            self.plu.clear()
            self.tipo_producto.setFocus()

    def calcular_precio(self):
        try:
            costo = float(self.costo.text()) if self.costo.text() else 0.0
            if costo == 0.0:
                self.valor_venta.setText("0.00")
                self.ganancia.setText("0%")
                self.igv.setText("0.00")
                self.precio.setText("0.00")
                return 

            valor_base = round(costo * 1.20, 2)
            valor_ganancia = round(((valor_base/costo)-1) * 100, 2)
            self.valor_venta.setText(str(valor_base))
            self.ganancia.setText(str(f"{valor_ganancia}%"))
            if self.afecto_igv.isChecked():
                igv = round(valor_base * 0.18, 2)
                precio_base= round(valor_base + igv, 2)
                self.igv.setText(str(igv)) 
                self.precio.setText(str(precio_base))
            else:
                self.igv.setText("0.00")
                self.precio.setText(str(valor_base))
        except ValueError:
            self.precio.setText("0.00")
            self.igv.setText("0.00")
            self.valor_venta.setText("0.00")

    def calcular_precio_promo(self):
        try:
            precio_base = float(self.precio.text()) if self.precio.text() else 0.0
            precio_promo=float(self.precio_promocion.text()) if self.precio_promocion.text() else 0.0
            if precio_base == 0.0:
                self.precio_promocion.setText("0.00")
                return 
            if precio_promo == 0.0:
                self.descuento.setText("0.00%")
                return
            if self.check_promocion.isChecked():                
                valor_descuento= round(((precio_base/precio_promo) -1)*100, 2)
                self.descuento.setText(str(f"{valor_descuento}%"))
            else:
                self.descuento.clear()
        except ValueError:
            self.descuento.setText("0.00%")
           
    def calcular_igv(self):
        try:
            precio = float(self.precio.text()) if self.precio.text() else 0.0
            costo = float(self.costo.text()) if self.costo.text() else 0.0
            if precio == 0.0:
                self.valor_venta.setText("0.00")
                self.ganancia.setText("0%")
                self.igv.setText("0.00")
                return
            else:
                valor_base=round(precio / 1.18, 2)
                valor_ganancia = round(((valor_base/costo)-1) * 100, 2)
                igv = round(valor_base * 0.18, 2)
                self.valor_venta.setText(str(valor_base))
                self.ganancia.setText(str(f"{valor_ganancia}%"))
                self.igv.setText(str(igv))        
            if costo == 0.0:
                self.valor_venta.setText("0.00")
                self.ganancia.setText("0%")
                self.igv.setText("0.00")
                self.precio.setText("0.00")
                return    
            valor_base=round(precio / 1.18, 2)
            valor_ganancia = round(((valor_base/costo)-1) * 100, 2)
            if self.afecto_igv.isChecked():
                igv= round(valor_base * 0.18, 2)
                self.igv.setText(str(igv)) 
                self.valor_venta.setText(str(valor_base))
            else:
                valor_base = precio
                valor_ganancia = round(((valor_base/costo)-1) * 100, 2)
                self.igv.setText("0.00")
                self.valor_venta.setText(str(valor_base))
                self.ganancia.setText(str(f"{valor_ganancia}%"))
        except ValueError:
            self.igv.setText("0.00")
            self.valor_venta.setText("0.00")
            self.ganancia.setText("0.00%")

    def generar_sku(self):
        correlativo = self.db.get_correlativo_sku()
        self.sku.setText(f"2020{correlativo:04d}")

    def validar_producto(self):
        if not self.desc_larga.text().strip():
            QMessageBox.warning(self, "Validación", "La descripción larga es obligatoria.")
            return False
        if not self.desc_corta.text().strip():
            QMessageBox.warning(self, "Validación", "La descripción corta es obligatoria.")
            return False
        if not self.clase_combo.currentData():
            QMessageBox.warning(self, "Validación", "Debe seleccionar una clase.")
            return False
        if not self.codigo_barra.text().isdigit() or len(self.codigo_barra.text()) > 13:
            QMessageBox.warning(self, "Validación", "El código de barras debe tener hasta 13 dígitos.")
            return False

        self.desc_larga.setText(self.desc_larga.text().strip().upper())
        self.desc_corta.setText(self.desc_corta.text().strip().upper())
        return True

    def limpiar_formulario(self):
        """Limpia todos los campos del formulario"""
        # Resetear combos
        self.departamento_combo.setCurrentIndex(0)
        self.subdepto_combo.clear()
        self.clase_combo.clear()
        
        # Limpiar campos básicos
        self.desc_larga.clear()
        self.desc_corta.clear()
        self.codigo_barra.clear()
        self.plu.clear()
        self.unidad_medida.setCurrentIndex(0)
        self.tipo_producto.setCurrentIndex(0)
        self.stock.setValue(1)
        self.costo.clear()
        self.precio.clear()
        self.igv.clear()
        self.valor_venta.clear()
        self.ganancia.clear()
        self.afecto_igv.setChecked(True)
        
        # Limpiar campos avanzados
        self.marca.clear()
        self.registro_sanitario.clear()
        self.stock_minimo.setValue(0)
        self.precio_regular.clear()
        self.check_promocion.setChecked(False)
        self.precio_promocion.clear()
        self.descuento.clear()
        self.costo_reposicion.clear()
        self.dias_retiro.setValue(1)
        self.case_pack.clear()
        
        # Ocultar campos avanzados
        self.activar_extra.setChecked(False)
        self.extra_frame.setVisible(False)
        
        # Generar nuevo SKU
        self.generar_sku()
        
        # Enfocar primer campo
        self.desc_larga.setFocus()