from PySide6.QtWidgets import (QMainWindow, QWidget, QVBoxLayout, QLabel, QLineEdit, QComboBox, QPushButton,
                               QFormLayout, QHBoxLayout, QMessageBox, QCheckBox, QSpinBox, QFrame, QScrollArea, QGroupBox)
from PySide6.QtGui import QRegularExpressionValidator, QDoubleValidator
from PySide6.QtCore import Qt, QRegularExpression

class ProductoForm(QWidget):
    def __init__(self, db,usuario_actual):
        super().__init__()
        self.db = db
        self.usuario_actual = usuario_actual
        self.setWindowTitle("Registro de Producto")
        self.setGeometry(200, 200, 850, 650)
        self.setStyleSheet(self._get_styles())
        #self.showMaximized()

        main_layout = QVBoxLayout()
        scroll_area = QScrollArea()
        scroll_area.setWidgetResizable(True)
        scroll_content = QWidget()
        layout = QVBoxLayout(scroll_content)
        layout.setSpacing(20)
        layout.setContentsMargins(20, 20, 20, 20)

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
        self.sku.setMaximumWidth(120)
        self.generar_sku()
        # self.sku.editingFinished.connect(lambda: self.mover_cursor(6))  # Deshabilitado navegación automática

        self.desc_larga = QLineEdit()
        self.desc_larga.textChanged.connect(self.convertir_a_mayusculas)
        # self.desc_larga.editingFinished.connect(lambda: self.mover_cursor(1))  # Deshabilitado navegación automática
        self.desc_corta = QLineEdit()
        self.desc_corta.textChanged.connect(self.convertir_a_mayusculas)
        self.desc_corta.setMaxLength(25)
        self.desc_corta.setMaximumWidth(250)
        # self.desc_corta.editingFinished.connect(lambda: self.mover_cursor(2))  # Deshabilitado navegación automática

        self.codigo_barra = QLineEdit()
        self.codigo_barra.setMaxLength(13)
        self.codigo_barra.setMaximumWidth(200)
        validator = QDoubleValidator()
        validator.setRange(0, 9999999999999, 0)
        self.codigo_barra.setValidator(validator)
        # self.codigo_barra.editingFinished.connect(lambda: self.mover_cursor(3))  # Deshabilitado navegación automática

        self.plu = QLineEdit()
        self.plu.setReadOnly(True)
        self.plu.setMaxLength(6)
        self.plu.setMaximumWidth(100)
        self.plu.setValidator(validator)
        # self.plu.editingFinished.connect(lambda: self.mover_cursor(4))  # Deshabilitado navegación automática

        self.unidad_medida = QComboBox()
        self.unidad_medida.addItems(["UND", "KG", "PACK", "CJA"])
        self.unidad_medida.currentTextChanged.connect(self.activar_plu)

        self.tipo_producto = QComboBox()
        self.tipo_producto.addItems(["Mercaderia", "Insumo", "Clase100"])
        self.tipo_producto.editTextChanged.connect(lambda: self.mover_cursor(5))

        self.stock = QSpinBox()
        self.stock.setRange(0, 99999)
        self.stock.setMaximumWidth(60)
        # self.stock.editingFinished.connect(lambda: self.mover_cursor(7))  # Deshabilitado navegación automática

        self.costo = QLineEdit()
        self.costo.setMaximumWidth(120)
        self.precio = QLineEdit()
        self.precio.setMaximumWidth(120)
        self.igv = QLineEdit()
        self.igv.setMaximumWidth(120)
        self.valor_venta = QLineEdit()
        self.valor_venta.setMaximumWidth(120)
        self.ganancia = QLineEdit()
        self.ganancia.setMaximumWidth(120)
        self.igv.setReadOnly(True)
        # self.igv.editingFinished.connect(lambda: self.mover_cursor(10))  # Deshabilitado navegación automática
        self.valor_venta.setReadOnly(True)
        # self.valor_venta.editingFinished.connect(lambda: self.mover_cursor(11))  # Deshabilitado navegación automática
        self.ganancia.setReadOnly(True)
        # self.ganancia.editingFinished.connect(lambda: self.mover_cursor(12))  # Deshabilitado navegación automática

        self.afecto_igv = QCheckBox("Afecto IGV")
        self.afecto_igv.setChecked(True)
        self.afecto_igv.stateChanged.connect(self.calcular_igv)
        # self.ganancia.editingFinished.connect(lambda: self.mover_cursor(13))  # Deshabilitado navegación automática
        self.costo.textChanged.connect(self.calcular_precio)
        # self.costo.editingFinished.connect(lambda: self.mover_cursor(8))  # Deshabilitado navegación automática
        self.precio.textChanged.connect(self.calcular_igv)
        # self.precio.editingFinished.connect(lambda: self.mover_cursor(9))  # Deshabilitado navegación automática

        self.activar_extra = QCheckBox("Mostrar Campos Avanzados")
        self.activar_extra.stateChanged.connect(self.activa_extra_frame)
        
        # === WIDGETS DE CAMPOS AVANZADOS ===
        self.marca = QLineEdit()
        self.marca.setPlaceholderText("Marca")
        self.marca.setMaximumWidth(160)
        self.marca.textChanged.connect(self.convertir_a_mayusculas)
        # self.marca.editingFinished.connect(lambda: self.mover_cursor(14))  # Deshabilitado navegación automática
        
        self.registro_sanitario = QLineEdit()
        self.registro_sanitario.setPlaceholderText("Registro")
        self.registro_sanitario.setMaximumWidth(160)
        # self.registro_sanitario.editingFinished.connect(lambda: self.mover_cursor(15))  # Deshabilitado navegación automática
        
        self.stock_minimo = QSpinBox()
        self.stock_minimo.setRange(0, 99999)
        self.stock_minimo.setValue(0)
        self.stock_minimo.setMaximumWidth(90)
        # self.stock_minimo.editingFinished.connect(lambda: self.mover_cursor(16))  # Deshabilitado navegación automática
        
        self.precio_regular = QLineEdit()
        self.precio_regular.setPlaceholderText("0.00")
        self.precio_regular.setMaximumWidth(120)
        # self.precio_regular.editingFinished.connect(lambda: self.mover_cursor(17))  # Deshabilitado navegación automática

        self.check_promocion = QCheckBox("Activar Promoción")
        self.check_promocion.stateChanged.connect(self.activa_promocion)
        
        self.precio_promocion = QLineEdit()
        self.precio_promocion.setPlaceholderText("0.00")
        self.precio_promocion.setMaximumWidth(120)
        self.precio_promocion.textChanged.connect(self.calcular_precio_promo)
        # self.precio_promocion.editingFinished.connect(lambda: self.mover_cursor(18))  # Deshabilitado navegación automática
        
        self.descuento = QLineEdit()
        self.descuento.setPlaceholderText("0.00%")
        self.descuento.setMaximumWidth(120)
        # self.descuento.editingFinished.connect(lambda: self.mover_cursor(19))  # Deshabilitado navegación automática
        self.descuento.setReadOnly(True)
        
        self.costo_reposicion = QLineEdit()
        self.costo_reposicion.setPlaceholderText("0.00")
        self.costo_reposicion.setMaximumWidth(120)
        # self.costo_reposicion.editingFinished.connect(lambda: self.mover_cursor(20))  # Deshabilitado navegación automática
        
        self.dias_retiro = QSpinBox()
        self.dias_retiro.setRange(1, 365)
        self.dias_retiro.setValue(1)
        self.dias_retiro.setMaximumWidth(100)
        # self.dias_retiro.editingFinished.connect(lambda: self.mover_cursor(21))  # Deshabilitado navegación automática
        
        self.case_pack = QLineEdit()
        self.case_pack.setPlaceholderText("12")
        self.case_pack.setMaximumWidth(100)
        # self.case_pack.editingFinished.connect(lambda: self.mover_cursor(22))  # Deshabilitado navegación automática

        # === FRAME DE CAMPOS AVANZADOS ===
        self.extra_frame = QGroupBox("🔧 Campos Avanzados")
        self.extra_frame.setVisible(False)
        
        # === LAYOUT PRINCIPAL HORIZONTAL ===
        main_horizontal_layout = QHBoxLayout()
        
        # === COLUMNA IZQUIERDA ===
        columna_izquierda = QVBoxLayout()
        columna_izquierda.setSpacing(10)
        
        # === GRUPO DE CLASIFICACIÓN ===
        clasificacion_group = QGroupBox("🏷️ Clasificación del Producto")
        clasificacion_layout = QFormLayout()
        clasificacion_layout.setSpacing(8)
        clasificacion_layout.addRow("Departamento:", self.departamento_combo)
        clasificacion_layout.addRow("Subdepartamento:", self.subdepto_combo)
        clasificacion_layout.addRow("Clase:", self.clase_combo)
        clasificacion_group.setLayout(clasificacion_layout)
        columna_izquierda.addWidget(clasificacion_group)

        # === GRUPO DE INFORMACIÓN BÁSICA ===
        basica_group = QGroupBox("📝 Información Básica")
        basica_layout = QVBoxLayout()
        basica_layout.setSpacing(12)
        basica_layout.setContentsMargins(15, 15, 15, 15)
        
        # Primera fila: SKU y Código de Barras
        fila1 = QHBoxLayout()
        fila1.setSpacing(15)
        sku_layout = QFormLayout()
        sku_layout.setLabelAlignment(Qt.AlignRight | Qt.AlignVCenter)
        sku_layout.setFormAlignment(Qt.AlignLeft | Qt.AlignTop)
        sku_layout.setSpacing(8)
        sku_layout.addRow("SKU:", self.sku)
        codigo_layout = QFormLayout()
        codigo_layout.setLabelAlignment(Qt.AlignRight | Qt.AlignVCenter)
        codigo_layout.setFormAlignment(Qt.AlignLeft | Qt.AlignTop)
        codigo_layout.setSpacing(20)
        self.codigo_barra.setMaxLength(13)  
        codigo_layout.addRow("Código de Barras:", self.codigo_barra)
        fila1.addLayout(sku_layout)
        fila1.addLayout(codigo_layout)
        fila1.addStretch()
        basica_layout.addLayout(fila1)
        
        # Segunda fila: Descripción Larga (toda la línea)
        desc_larga_layout = QFormLayout()
        desc_larga_layout.setLabelAlignment(Qt.AlignRight | Qt.AlignVCenter)
        desc_larga_layout.setFormAlignment(Qt.AlignLeft | Qt.AlignTop)
        desc_larga_layout.setSpacing(10)
        desc_larga_layout.addRow("Descripción Larga:", self.desc_larga)
        basica_layout.addLayout(desc_larga_layout)
        
        # Tercera fila: Descripción Corta y Tipo de Mercadería
        fila3 = QHBoxLayout()
        fila3.setSpacing(20)
        desc_corta_layout = QFormLayout()
        desc_corta_layout.setLabelAlignment(Qt.AlignRight | Qt.AlignVCenter)
        desc_corta_layout.setFormAlignment(Qt.AlignLeft | Qt.AlignTop)
        desc_corta_layout.setSpacing(10)
        desc_corta_layout.addRow("Descripción Corta:", self.desc_corta)
        tipo_layout = QFormLayout()
        tipo_layout.setLabelAlignment(Qt.AlignRight | Qt.AlignVCenter)
        tipo_layout.setFormAlignment(Qt.AlignLeft | Qt.AlignTop)
        tipo_layout.setSpacing(10)
        tipo_layout.addRow("Tipo de Mercadería:", self.tipo_producto)
        fila3.addLayout(desc_corta_layout, 2)  # Proporción 2 para desc_corta
        fila3.addLayout(tipo_layout, 1)        # Proporción 1 para tipo_producto
        fila3.addStretch()
        basica_layout.addLayout(fila3)
        
        # Cuarta fila: Unidad de Medida, PLU y Stock Inicial
        fila4 = QHBoxLayout()
        fila4.setSpacing(20)
        unidad_layout = QFormLayout()
        unidad_layout.setLabelAlignment(Qt.AlignRight | Qt.AlignVCenter)
        unidad_layout.setFormAlignment(Qt.AlignLeft | Qt.AlignTop)
        unidad_layout.setSpacing(10)
        unidad_layout.addRow("Unidad Medida:", self.unidad_medida)
        plu_layout = QFormLayout()
        plu_layout.setLabelAlignment(Qt.AlignRight | Qt.AlignVCenter)
        plu_layout.setFormAlignment(Qt.AlignLeft | Qt.AlignTop)
        plu_layout.setSpacing(10)
        plu_layout.addRow("PLU:", self.plu)
        stock_layout = QFormLayout()
        stock_layout.setLabelAlignment(Qt.AlignRight | Qt.AlignVCenter)
        stock_layout.setFormAlignment(Qt.AlignLeft | Qt.AlignTop)
        stock_layout.setSpacing(12)
        stock_layout.addRow("Stock Inicial:", self.stock)
        fila4.addLayout(unidad_layout)
        fila4.addLayout(plu_layout)
        fila4.addLayout(stock_layout)
        fila4.addStretch()
        basica_layout.addLayout(fila4)
        
        basica_group.setLayout(basica_layout)
        columna_izquierda.addWidget(basica_group)

        # === GRUPO DE PRECIOS ===
        precios_group = QGroupBox("💰 Información de Precios")
        precios_layout = QVBoxLayout()
        precios_layout.setSpacing(8)
        precios_layout.setContentsMargins(12, 12, 12, 12)
        precios_layout.setAlignment(Qt.AlignTop | Qt.AlignLeft)
        
        # Crear un layout de formulario único para mejor alineación
        precios_form_layout = QFormLayout()
        precios_form_layout.setLabelAlignment(Qt.AlignRight | Qt.AlignVCenter)
        precios_form_layout.setFormAlignment(Qt.AlignLeft | Qt.AlignTop)
        precios_form_layout.setSpacing(12)
        precios_form_layout.setFieldGrowthPolicy(QFormLayout.FieldGrowthPolicy.ExpandingFieldsGrow)
        
        # Agregar todos los campos al formulario
        precios_form_layout.addRow("Costo:", self.costo)
        precios_form_layout.addRow("Precio de Venta:", self.precio)
        precios_form_layout.addRow("IGV:", self.igv)
        precios_form_layout.addRow("Valor de Venta:", self.valor_venta)
        precios_form_layout.addRow("% Margen Ganancia:", self.ganancia)
        precios_form_layout.addRow("Afecto IGV:", self.afecto_igv)
        
        # Agregar el formulario al layout principal
        precios_layout.addLayout(precios_form_layout)
        precios_layout.addStretch()
        
        precios_group.setLayout(precios_layout)
        
        # Permitir que el grupo de precios mantenga su tamaño natural
        # precios_group.setMaximumWidth(240)  # Removido para mantener tamaño consistente
        # precios_group.setMinimumWidth(200)  # Removido para mantener tamaño consistente
        
        # === GRUPO DE CONFIGURACIÓN (CHECKBOX CAMPOS AVANZADOS) ===
        campos_extra_group = QGroupBox("⚙️ Configuración")
        campos_extra_layout = QVBoxLayout()
        campos_extra_layout.setSpacing(12)
        campos_extra_layout.setContentsMargins(15, 15, 15, 15)
        campos_extra_layout.setAlignment(Qt.AlignCenter | Qt.AlignVCenter)
        campos_extra_layout.addWidget(self.activar_extra)
        campos_extra_group.setLayout(campos_extra_layout)
        
        # Permitir que el grupo de configuración se ajuste al contenido
        # campos_extra_group.setMaximumWidth(240)  # Removido para mejor distribución
        # campos_extra_group.setMinimumWidth(180)  # Removido para mejor distribución
        
        # === LAYOUT VERTICAL PARA CONFIGURACIÓN Y CAMPOS AVANZADOS ===
        config_avanzados_layout = QVBoxLayout()
        config_avanzados_layout.setSpacing(10)
        
        # Agregar Configuración arriba
        config_avanzados_layout.addWidget(campos_extra_group)
        
        # Agregar Campos Avanzados debajo
        config_avanzados_layout.addWidget(self.extra_frame)
        config_avanzados_layout.addStretch()
        
        # === LAYOUT HORIZONTAL PARA PRECIOS Y CONFIGURACIÓN/AVANZADOS ===
        precios_config_avanzados_layout = QHBoxLayout()
        precios_config_avanzados_layout.setSpacing(15)
        
        # Agregar Precios a la izquierda con proporción ajustada
        precios_config_avanzados_layout.addWidget(precios_group, 1)  # Proporción 1 para precios
        
        # Agregar el layout vertical de configuración y avanzados a la derecha
        precios_config_avanzados_layout.addLayout(config_avanzados_layout, 2)  # Proporción 2 para avanzados
        
        # Agregar este layout horizontal a la columna izquierda
        columna_izquierda.addLayout(precios_config_avanzados_layout)
        
        # === COLUMNA DERECHA ===
        columna_derecha = QVBoxLayout()
        columna_derecha.setSpacing(10)
        
        # Agregar columna izquierda al layout horizontal
        main_horizontal_layout.addLayout(columna_izquierda)
        
        # Agregar columna derecha al layout horizontal
        main_horizontal_layout.addLayout(columna_derecha)



        # Subgrupo: Campos Avanzados (como en la imagen)
        campos_avanzados_grupo = QGroupBox("🔧 Campos Avanzados")
        campos_avanzados_layout = QVBoxLayout()
        campos_avanzados_layout.setSpacing(15)
        campos_avanzados_layout.setContentsMargins(15, 15, 15, 15)
        campos_avanzados_layout.setAlignment(Qt.AlignTop | Qt.AlignLeft)
        
        # Primera fila: Marca, Reg. Sanitario y Stock Mínimo
        fila_av1 = QHBoxLayout()
        fila_av1.setSpacing(15)
        marca_layout = QFormLayout()
        marca_layout.setLabelAlignment(Qt.AlignRight | Qt.AlignVCenter)
        marca_layout.setFormAlignment(Qt.AlignLeft | Qt.AlignTop)
        marca_layout.setSpacing(8)
        marca_layout.addRow("Marca:", self.marca)
        reg_sanitario_layout = QFormLayout()
        reg_sanitario_layout.setLabelAlignment(Qt.AlignRight | Qt.AlignVCenter)
        reg_sanitario_layout.setFormAlignment(Qt.AlignLeft | Qt.AlignTop)
        reg_sanitario_layout.setSpacing(8)
        reg_sanitario_layout.addRow("Reg. San.:", self.registro_sanitario)
        stock_min_layout = QFormLayout()
        stock_min_layout.setLabelAlignment(Qt.AlignRight | Qt.AlignVCenter)
        stock_min_layout.setFormAlignment(Qt.AlignLeft | Qt.AlignTop)
        stock_min_layout.setSpacing(12)
        stock_min_layout.addRow("Stock Min:", self.stock_minimo)
        fila_av1.addLayout(marca_layout, 3)        # Proporción 3 para marca
        fila_av1.addLayout(reg_sanitario_layout, 3) # Proporción 3 para registro sanitario
        fila_av1.addLayout(stock_min_layout, 1)     # Proporción 1 para stock mínimo
        fila_av1.addStretch()
        campos_avanzados_layout.addLayout(fila_av1)
        
        # Segunda fila: Activar Promoción
        promocion_layout = QFormLayout()
        promocion_layout.setLabelAlignment(Qt.AlignLeft | Qt.AlignVCenter)
        promocion_layout.setFormAlignment(Qt.AlignLeft | Qt.AlignTop)
        promocion_layout.setSpacing(8)
        promocion_layout.addRow("", self.check_promocion)
        campos_avanzados_layout.addLayout(promocion_layout)
        
        # Tercera fila: Precio Regular y Precio Promoción
        fila_av3 = QHBoxLayout()
        fila_av3.setSpacing(15)
        precio_reg_layout = QFormLayout()
        precio_reg_layout.setLabelAlignment(Qt.AlignRight | Qt.AlignVCenter)
        precio_reg_layout.setFormAlignment(Qt.AlignLeft | Qt.AlignTop)
        precio_reg_layout.setSpacing(8)
        precio_reg_layout.addRow("Precio Regular:", self.precio_regular)
        precio_promo_layout = QFormLayout()
        precio_promo_layout.setLabelAlignment(Qt.AlignRight | Qt.AlignVCenter)
        precio_promo_layout.setFormAlignment(Qt.AlignLeft | Qt.AlignTop)
        precio_promo_layout.setSpacing(8)
        precio_promo_layout.addRow("Precio Promoción:", self.precio_promocion)
        fila_av3.addLayout(precio_reg_layout)
        fila_av3.addLayout(precio_promo_layout)
        fila_av3.addStretch()
        campos_avanzados_layout.addLayout(fila_av3)
        
        # Cuarta fila: Costo de Reposición y % Descuento
        fila_av4 = QHBoxLayout()
        fila_av4.setSpacing(15)
        costo_rep_layout = QFormLayout()
        costo_rep_layout.setLabelAlignment(Qt.AlignRight | Qt.AlignVCenter)
        costo_rep_layout.setFormAlignment(Qt.AlignLeft | Qt.AlignTop)
        costo_rep_layout.setSpacing(8)
        costo_rep_layout.addRow("Costo de Reposición:", self.costo_reposicion)
        descuento_layout = QFormLayout()
        descuento_layout.setLabelAlignment(Qt.AlignRight | Qt.AlignVCenter)
        descuento_layout.setFormAlignment(Qt.AlignLeft | Qt.AlignTop)
        descuento_layout.setSpacing(8)
        descuento_layout.addRow("% Descuento:", self.descuento)
        fila_av4.addLayout(costo_rep_layout)
        fila_av4.addLayout(descuento_layout)
        fila_av4.addStretch()
        campos_avanzados_layout.addLayout(fila_av4)
        
        # Quinta fila: Días de Retiro y Case Pack
        fila_av5 = QHBoxLayout()
        fila_av5.setSpacing(15)
        dias_layout = QFormLayout()
        dias_layout.setLabelAlignment(Qt.AlignRight | Qt.AlignVCenter)
        dias_layout.setFormAlignment(Qt.AlignLeft | Qt.AlignTop)
        dias_layout.setSpacing(8)
        dias_layout.addRow("Días de Retiro:", self.dias_retiro)
        case_layout = QFormLayout()
        case_layout.setLabelAlignment(Qt.AlignRight | Qt.AlignVCenter)
        case_layout.setFormAlignment(Qt.AlignLeft | Qt.AlignTop)
        case_layout.setSpacing(8)
        case_layout.addRow("Case Pack:", self.case_pack)
        fila_av5.addLayout(dias_layout)
        fila_av5.addLayout(case_layout)
        fila_av5.addStretch()
        campos_avanzados_layout.addLayout(fila_av5)
        
        campos_avanzados_grupo.setLayout(campos_avanzados_layout)
        
        # Configurar el layout del frame
        self.extra_frame.setLayout(campos_avanzados_layout)
        

        
        # Agregar layout horizontal al layout principal
        layout.addLayout(main_horizontal_layout)
       
        # === BOTONES DE ACCIÓN ===
        botones_group = QGroupBox("💾 Acciones")
        botones_layout = QHBoxLayout()
        
        # Botón limpiar
        self.btn_limpiar = QPushButton("🧹 Limpiar")
        self.btn_limpiar.setObjectName("btn_limpiar")
        # self.btn_limpiar.setStyleSheet("QPushButton { background: #f39c12; color: white; }")  # Usar estilos del tema
        self.btn_limpiar.clicked.connect(self.limpiar_formulario)
        botones_layout.addWidget(self.btn_limpiar)
        
        # Espaciador
        botones_layout.addStretch()
        
        # Botón guardar
        self.btn_guardar = QPushButton("💾 Guardar Producto")
        self.btn_guardar.setObjectName("btn_guardar")
        # self.btn_guardar.setStyleSheet("QPushButton { background: #27ae60; color: white; }")  # Usar estilos del tema
        self.btn_guardar.clicked.connect(self.guardar_producto)
        botones_layout.addWidget(self.btn_guardar)
        
        botones_group.setLayout(botones_layout)
        layout.addWidget(botones_group)
       
        # Configurar scroll area
        scroll_area.setWidget(scroll_content)
        main_layout.addWidget(scroll_area)
        self.setLayout(main_layout)

    def _get_styles(self):
        """Retorna los estilos CSS mejorados para el formulario"""
        return """
        QWidget {
            font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
            font-size: 12px;
            background: qlineargradient(x1:0, y1:0, x2:0, y2:1, 
                                       stop:0 #f8f9fa, stop:1 #e9ecef);
        }
        
        QGroupBox {
            font-weight: 600;
            font-size: 13px;
            color: #2c3e50;
            border: 2px solid #dee2e6;
            border-radius: 12px;
            margin-top: 15px;
            padding-top: 15px;
            background: qlineargradient(x1:0, y1:0, x2:0, y2:1, 
                                       stop:0 #ffffff, stop:1 #f8f9fa);
        }
        
        QGroupBox::title {
            subcontrol-origin: margin;
            left: 15px;
            padding: 4px 12px;
            background: qlineargradient(x1:0, y1:0, x2:1, y2:0, 
                                       stop:0 #3498db, stop:1 #2980b9);
            color: #ffffff;
            font-weight: 600;
            border-radius: 6px;
        }
        
        QLineEdit {
            padding: 8px 12px;
            border: 2px solid #dee2e6;
            border-radius: 6px;
            font-size: 12px;
            background-color: #ffffff;
            color: #2c3e50;
            min-height: 20px;
            selection-background-color: #3498db;
            selection-color: #ffffff;
        }
        
        QLineEdit:focus {
            border-color: #3498db;
            background-color: #ffffff;
        }
        
        QLineEdit:hover {
            border-color: #adb5bd;
            background-color: #f8f9fa;
        }
        
        QLineEdit:read-only {
            background-color: #e9ecef;
            color: #6c757d;
            border-color: #ced4da;
        }
        
        QPushButton {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 12px;
            color: #ffffff;
            min-height: 24px;
            background: qlineargradient(x1:0, y1:0, x2:0, y2:1, 
                                       stop:0 #3498db, stop:1 #2980b9);
        }
        
        QPushButton:hover {
            background: qlineargradient(x1:0, y1:0, x2:0, y2:1, 
                                       stop:0 #5dade2, stop:1 #3498db);
        }
        
        QPushButton:pressed {
            background: qlineargradient(x1:0, y1:0, x2:0, y2:1, 
                                       stop:0 #2980b9, stop:1 #1f618d);
        }
        
        QPushButton#btn_guardar {
            background: qlineargradient(x1:0, y1:0, x2:0, y2:1, 
                                       stop:0 #27ae60, stop:1 #229954);
        }
        
        QPushButton#btn_guardar:hover {
            background: qlineargradient(x1:0, y1:0, x2:0, y2:1, 
                                       stop:0 #58d68d, stop:1 #27ae60);
        }
        
        QPushButton#btn_limpiar {
            background: qlineargradient(x1:0, y1:0, x2:0, y2:1, 
                                       stop:0 #e74c3c, stop:1 #c0392b);
        }
        
        QPushButton#btn_limpiar:hover {
            background: qlineargradient(x1:0, y1:0, x2:0, y2:1, 
                                       stop:0 #ec7063, stop:1 #e74c3c);
        }
        
        QComboBox {
            padding: 8px 12px;
            border: 2px solid #dee2e6;
            border-radius: 6px;
            font-size: 12px;
            background-color: #ffffff;
            color: #2c3e50;
            min-height: 20px;
            font-weight: 500;
        }
        
        QComboBox:focus {
            border-color: #3498db;
            background-color: #ffffff;
        }
        
        QComboBox:hover {
            border-color: #adb5bd;
            background-color: #f8f9fa;
        }
        
        QComboBox::drop-down {
            border: none;
            width: 24px;
            background-color: #f8f9fa;
            border-radius: 0 6px 6px 0;
        }
        
        QComboBox::down-arrow {
            image: none;
            border-left: 6px solid transparent;
            border-right: 6px solid transparent;
            border-top: 6px solid #6c757d;
            margin-right: 8px;
        }
        
        QComboBox QAbstractItemView {
            background-color: #ffffff;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            selection-background-color: #3498db;
            selection-color: #ffffff;
            outline: none;
        }
        
        QComboBox QAbstractItemView::item {
            padding: 10px 16px;
            color: #2c3e50;
            background-color: #ffffff;
            min-height: 20px;
        }
        
        QComboBox QAbstractItemView::item:hover {
            background-color: #f8f9fa;
            color: #2c3e50;
        }
        
        QComboBox QAbstractItemView::item:selected {
            background-color: #3498db;
            color: #ffffff;
        }
        
        QCheckBox {
            font-size: 12px;
            color: #2c3e50;
            font-weight: 500;
            spacing: 10px;
        }
        
        QCheckBox::indicator {
            width: 20px;
            height: 20px;
            border-radius: 4px;
        }
        
        QCheckBox::indicator:unchecked {
            border: 2px solid #dee2e6;
            background-color: #ffffff;
        }
        
        QCheckBox::indicator:checked {
            border: 2px solid #27ae60;
            background-color: #27ae60;
            image: url(data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIiIGhlaWdodD0iMTIiIHZpZXdCb3g9IjAgMCAxMiAxMiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTEwIDNMNC41IDguNUwyIDYiIHN0cm9rZT0id2hpdGUiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+Cjwvc3ZnPgo=);
        }
        
        QCheckBox::indicator:hover {
            border-color: #27ae60;
            background-color: #d5f4e6;
        }
        
        QSpinBox {
            padding: 8px 12px;
            border: 2px solid #dee2e6;
            border-radius: 6px;
            font-size: 12px;
            background-color: #ffffff;
            color: #2c3e50;
            min-height: 20px;
        }
        
        QSpinBox:focus {
            border-color: #3498db;
            background-color: #ffffff;
        }
        
        QSpinBox:hover {
            border-color: #adb5bd;
            background-color: #f8f9fa;
        }
        
        QLabel {
            color: #2c3e50;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        QScrollArea {
            background-color: transparent;
            border: none;
        }
        
        QScrollBar:vertical {
            background-color: #f8f9fa;
            width: 14px;
            border-radius: 7px;
            border: 1px solid #dee2e6;
        }
        
        QScrollBar::handle:vertical {
            background: qlineargradient(x1:0, y1:0, x2:1, y2:0, 
                                       stop:0 #3498db, stop:1 #2980b9);
            border-radius: 6px;
            min-height: 30px;
            margin: 2px;
        }
        
        QScrollBar::handle:vertical:hover {
            background: qlineargradient(x1:0, y1:0, x2:1, y2:0, 
                                       stop:0 #5dade2, stop:1 #3498db);
        }
        
        QScrollBar::add-line:vertical, QScrollBar::sub-line:vertical {
            height: 0px;
        }
        
        QScrollBar::add-page:vertical, QScrollBar::sub-page:vertical {
            background: none;
        }
        """

    def mover_cursor(self, valor):
        """Navegación manual entre campos - deshabilitada para mejor UX"""
        pass  # Función deshabilitada para evitar navegación automática molesta

    def keyPressEvent(self, event):
        """Maneja la navegación por teclado de manera más intuitiva"""
        if event.key() == Qt.Key_Return or event.key() == Qt.Key_Enter:
            # Navegación lógica con Enter
            current_widget = self.focusWidget()
            if current_widget == self.desc_larga:
                self.desc_corta.setFocus()
            elif current_widget == self.desc_corta:
                self.unidad_medida.setFocus()
            elif current_widget == self.codigo_barra:
                self.desc_larga.setFocus()
                #self.unidad_medida.setFocus()
            elif current_widget == self.costo:
                self.precio.setFocus()
            elif current_widget == self.precio:
                self.valor_venta.setFocus()
            elif current_widget == self.valor_venta:
                self.ganancia.setFocus()
            else:
                # Para otros campos, usar navegación por Tab
                self.focusNextChild()
        else:
            super().keyPressEvent(event)

    def mover_cursor_manual(self, valor):
        """Navegación manual entre campos - deshabilitada para mejor UX"""
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
                descripcion_larga, descripcion_corta, sku, nombre, codigo_barra,
                plu, costo_compra, costo_compra,  # costo_promedio = costo_compra inicialmente
                costo_reposicion, precio_regular, valor_venta,
                precio_venta, activo_promocion, precio_promocion,
                descuento, afecto_igv, valor_igv, stock_contable,
                stock_disponible, stock_minimo, 0.0,  # venta_promedio
                False,  # pesable False por defecto
                unidad_medida, marca, registro_sanitario, dias_retiro,
                case_pack, tipo_producto, categoria, clase, clase_id,
                None,  # referencia
                None,  # campo1
                None,  # campo2
                None,  # campo3
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
        # Obtener precio regular del campo precio, con validación mejorada
        try:
            precio_text = self.precio.text().strip()
            precio_reg = float(precio_text) if precio_text else 0.0
        except (ValueError, AttributeError):
            precio_reg = 0.0
            
        # Obtener costo de reposición del campo costo, con validación mejorada
        try:
            costo_text = self.costo.text().strip()
            costo_rep = float(costo_text) if costo_text else 0.0
        except (ValueError, AttributeError):
            costo_rep = 0.0
        if self.activar_extra.isChecked():
            #self.showMaximized()
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
            #self.extra_frame.setEnabled(state == Qt.Checked)
            self.showNormal()

    def activa_promocion(self, state):
        precio_promo = float(self.precio.text()) if self.precio.text() else 0.0
        if self.check_promocion.isChecked():
            #self.precio_promocion.setVisible(True)
            self.precio_promocion.setText(str(round((precio_promo*0.95),2)))
            self.precio_promocion.setEnabled(True)
            self.descuento.setText("5.00%")
            self.precio_promocion.setFocus()
        else: 
            self.precio_promocion.setText("0.00")               
            #self.precio_promocion.setVisible(False)
            self.precio_promocion.setEnabled(False)
            self.descuento.clear()
            #self.descuento.setText("0.00%")
            #self.precio_promocion.setVisible(state == Qt.Checked)
        
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

    def preseleccionar_clase(self, depto_id, subdepto_id, clase_id):
        """Pre-selecciona una clase específica en los combos"""
        # Seleccionar departamento
        for i in range(self.departamento_combo.count()):
            if self.departamento_combo.itemData(i) == depto_id:
                self.departamento_combo.setCurrentIndex(i)
                break
        
        # Actualizar subdepartamentos y seleccionar
        self.actualizar_subdepto()
        for i in range(self.subdepto_combo.count()):
            if self.subdepto_combo.itemData(i) == subdepto_id:
                self.subdepto_combo.setCurrentIndex(i)
                break
        
        # Actualizar clases y seleccionar
        self.actualizar_clase()
        for i in range(self.clase_combo.count()):
            if self.clase_combo.itemData(i) == clase_id:
                self.clase_combo.setCurrentIndex(i)
                break

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
            #descuento=()
            #self.descuento.setText(str(f"{valor_descuento}%"))
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
            #print(f"[DEBUG] Precio: {precio}, Costo: {costo}")  # Paso 2
            if precio == 0.0:
                self.valor_venta.setText("0.00")
                self.ganancia.setText("0%")
                self.igv.setText("0.00")
                #print(f"[DEBUG] entro x valor precio cero: ")  
                return
            else:
                valor_base=round(precio / 1.18, 2)
                valor_ganancia = round(((valor_base/costo)-1) * 100, 2)
                igv = round(valor_base * 0.18, 2)
                self.valor_venta.setText(str(valor_base))
                self.ganancia.setText(str(f"{valor_ganancia}%"))
                self.igv.setText(str(igv))        
                #print(f"[DEBUG] valor venta: {valor_base}, ganancia: {valor_ganancia}, igv: {igv}")  # Paso 2 
            if costo == 0.0:
                self.valor_venta.setText("0.00")
                self.ganancia.setText("0%")
                self.igv.setText("0.00")
                self.precio.setText("0.00")
                #print(f"[DEBUG] entro x valor costo cero: ")  
                return    
            valor_base=round(precio / 1.18, 2)
            valor_ganancia = round(((valor_base/costo)-1) * 100, 2)
            #print(f"[DEBUG] valor venta: {valor_base}, ganancia: {valor_ganancia}, igv: {igv}")
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
        #codigo_barras = self.codigo_barra.text().strip()
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
