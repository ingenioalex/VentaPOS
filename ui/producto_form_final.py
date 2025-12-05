from PySide6.QtWidgets import (QMainWindow, QWidget, QVBoxLayout, QLabel, QLineEdit, QComboBox, QPushButton,
                               QFormLayout, QHBoxLayout, QMessageBox, QCheckBox, QSpinBox, QFrame, QScrollArea)
from PySide6.QtGui import QRegularExpressionValidator, QDoubleValidator
from PySide6.QtCore import Qt, QRegularExpression

class ProductoForm(QWidget):
    def __init__(self, db,usuario_actual):
        super().__init__()
        self.db = db
        self.usuario_actual = usuario_actual
        self.setWindowTitle("Registro de Producto")
        self.setGeometry(200, 200, 850, 850)
        #self.showMaximized()

        #layout = QVBoxLayout()
        #self.form = QFormLayout()
        main_layout = QVBoxLayout()
        scroll_area = QScrollArea()
        scroll_area.setWidgetResizable(True)
        scroll_content = QWidget()
        layout = QVBoxLayout(scroll_content)
        self.form = QFormLayout()

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
        self.codigo_barra.setMaxLength(13)
        validator = QDoubleValidator()
        validator.setRange(0, 9999999999999, 0)
        self.codigo_barra.setValidator(validator)
        self.codigo_barra.editingFinished.connect(lambda: self.mover_cursor(3))

        self.plu = QLineEdit()
        self.plu.setReadOnly(True)
        self.plu.setMaxLength(5)
        self.plu.setValidator(validator)
        self.plu.editingFinished.connect(lambda: self.mover_cursor(4))

        self.unidad_medida = QComboBox()
        self.unidad_medida.addItems(["UND", "KG", "PACK", "CJA"])
        self.unidad_medida.currentTextChanged.connect(self.activar_plu)

        self.tipo_producto = QComboBox()
        self.tipo_producto.addItems(["Mercaderia", "Insumo", "Clase100"])
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
        

        self.form.addRow("Departamento:", self.departamento_combo)
        self.form.addRow("Subdepartamento:", self.subdepto_combo)
        self.form.addRow("Clase:", self.clase_combo)
        self.form.addRow("SKU:", self.sku)
        self.form.addRow("Descripción Larga:", self.desc_larga)
        self.form.addRow("Descripción Corta:", self.desc_corta)
        self.form.addRow("Código de Barras:", self.codigo_barra)
        self.form.addRow("Unidad de Medida:", self.unidad_medida)
        self.form.addRow("PLU (opcional):", self.plu)
        self.form.addRow("Tipo de Producto:", self.tipo_producto)
        self.form.addRow("Stock Inicial:", self.stock)
        self.form.addRow("Costo Compra:", self.costo)
        self.form.addRow("Precio Venta:", self.precio)
        self.form.addRow("IGV:", self.igv)
        self.form.addRow("Valor Venta:", self.valor_venta)
        self.form.addRow("% Margen de Ganancia:", self.ganancia)
        self.form.addRow("", self.afecto_igv)
        self.form.addRow("Mostrar Campos Avanzados:", self.activar_extra)

        self.extra_frame = QFrame()
        self.extra_frame.setFrameShape(QFrame.StyledPanel)
        self.extra_layout = QFormLayout(self.extra_frame)
        self.extra_frame.setVisible(False)

        self.marca = QLineEdit()
        self.marca.textChanged.connect(self.convertir_a_mayusculas)
        self.marca.editingFinished.connect(lambda: self.mover_cursor(14))
        self.registro_sanitario = QLineEdit()
        self.registro_sanitario.editingFinished.connect(lambda: self.mover_cursor(15))
        self.stock_minimo = QSpinBox()
        self.stock_minimo.setRange(0, 99999)
        self.stock_minimo.editingFinished.connect(lambda: self.mover_cursor(16))
        self.precio_regular = QLineEdit()
        self.precio_regular.editingFinished.connect(lambda: self.mover_cursor(17))

        self.check_promocion = QCheckBox("Activar Promoción")
        self.check_promocion.stateChanged.connect(self.activa_promocion)
        self.precio_promocion = QLineEdit()
        self.precio_promocion.textChanged.connect(self.calcular_precio_promo)
        self.precio_promocion.editingFinished.connect(lambda: self.mover_cursor(18))
        #self.precio_promocion.setEnabled(False)
        self.descuento = QLineEdit()
        self.descuento.editingFinished.connect(lambda: self.mover_cursor(19))
        self.descuento.setReadOnly(True)
        
        self.costo_reposicion = QLineEdit()
        self.costo_reposicion.editingFinished.connect(lambda: self.mover_cursor(20))
        self.dias_retiro = QSpinBox()
        self.dias_retiro.setRange(1, 365)
        self.dias_retiro.editingFinished.connect(lambda: self.mover_cursor(21))
        self.case_pack = QLineEdit()
        self.case_pack.editingFinished.connect(lambda: self.mover_cursor(22))

        self.extra_layout.addRow("Marca:", self.marca)
        self.extra_layout.addRow("Registro Sanitario:", self.registro_sanitario)
        self.extra_layout.addRow("Stock Mínimo:", self.stock_minimo)
        self.extra_layout.addRow("Precio Regular:", self.precio_regular)
        self.extra_layout.addRow("", self.check_promocion)
        self.extra_layout.addRow("Precio Promoción:", self.precio_promocion)

        self.extra_layout.addRow("Descuento:", self.descuento)
        self.extra_layout.addRow("Costo Reposición:", self.costo_reposicion)
        self.extra_layout.addRow("Días de Retiro:", self.dias_retiro)
        self.extra_layout.addRow("Case Pack:", self.case_pack)

        layout.addLayout(self.form)
        layout.addWidget(self.extra_frame)
       
        scroll_area.setWidget(scroll_content)
        main_layout.addWidget(scroll_area)
        self.setLayout(main_layout)

        self.btn_guardar = QPushButton("Guardar Producto")
        self.btn_guardar.clicked.connect(self.guardar_producto)
        layout.addWidget(self.btn_guardar)
        self.setLayout(layout)

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
            print(f"[DEBUG] valor venta: {valor_base}, ganancia: {valor_ganancia}, igv: {igv}")
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

    def limpiar_formulario(self):
        self.clase_combo.setCurrentIndex(-1)
        self.desc_larga.clear()
        self.desc_corta.clear()
        self.codigo_barra.clear()
        self.plu.clear()
        self.unidad_medida.setCurrentIndex(0)
        self.tipo_producto.setCurrentIndex(0)
        self.stock.setValue(0)
        self.costo.clear()
        self.precio.clear()
        self.igv.clear()
        self.valor_venta.clear()
        self.ganancia.clear()
        self.afecto_igv.setChecked(True)
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
        self.generar_sku()

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
