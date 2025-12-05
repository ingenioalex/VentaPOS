from PySide6.QtWidgets import (QWidget, QVBoxLayout, QLabel, QLineEdit, QComboBox, QPushButton,
                               QFormLayout, QHBoxLayout, QMessageBox, QCheckBox, QSpinBox, QFrame)
from PySide6.QtGui import QRegularExpressionValidator, QDoubleValidator
from PySide6.QtCore import Qt, QRegularExpression

class ProductoForm(QWidget):
    def __init__(self, db):
        super().__init__()
        self.db = db
        self.setWindowTitle("Registro de Producto")
        self.setGeometry(200, 200, 750, 750)
		#producto_form_inicial.py
        layout = QVBoxLayout()
        form = QFormLayout()

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

        self.desc_larga = QLineEdit()
        self.desc_larga.textChanged.connect(self.convertir_a_mayusculas)
        self.desc_corta = QLineEdit()
        self.desc_corta.textChanged.connect(self.convertir_a_mayusculas)
        
        self.codigo_barra = QLineEdit()
        self.codigo_barra.setMaxLength(13)        
        validator = QDoubleValidator()
        validator.setRange(0, 9999999999999, 0)  # 0 decimales
        self.codigo_barra.setValidator(validator)
    

        self.plu = QLineEdit()
        self.plu.setReadOnly(True)
        self.plu.setMaxLength(5)  
        self.plu.setValidator(validator) 

        self.unidad_medida = QComboBox()
        self.unidad_medida.addItems(["UND", "KG", "PACK", "CJA"])
        self.unidad_medida.currentTextChanged.connect(self.activar_plu)

        self.tipo_producto = QComboBox()
        self.tipo_producto.addItems(["Mercaderia", "Insumo", "Clase100"])

        self.stock = QSpinBox()
        self.stock.setRange(0, 99999)

        self.costo = QLineEdit()
        self.precio = QLineEdit()
        self.igv = QLineEdit()
        self.valor_venta = QLineEdit()
        self.ganancia = QLineEdit()
        self.igv.setReadOnly(True)
        self.valor_venta.setReadOnly(True)
        self.ganancia.setReadOnly(True)

        self.afecto_igv = QCheckBox("Afecto IGV")
        self.afecto_igv.setChecked(True)
        self.afecto_igv.stateChanged.connect(self.calcular_igv)
        self.costo.textChanged.connect(self.calcular_precio)
        self.precio.textChanged.connect(self.calcular_igv)

        #self.activar_precio = QCheckBox("Activar Precio Personalizado")
        self.activar_extra = QCheckBox("Mostrar Campos Avanzados")
       
        form.addRow("Departamento:", self.departamento_combo)
        form.addRow("Subdepartamento:", self.subdepto_combo)
        form.addRow("Clase:", self.clase_combo)
        form.addRow("SKU:", self.sku)
        form.addRow("Descripción Larga:", self.desc_larga)
        form.addRow("Descripción Corta:", self.desc_corta)
        form.addRow("Código de Barras:", self.codigo_barra)
        form.addRow("Unidad de Medida:", self.unidad_medida)
        form.addRow("PLU (opcional):", self.plu)
        form.addRow("Tipo de Producto:", self.tipo_producto)
        form.addRow("Stock Inicial:", self.stock)
        form.addRow("Costo Compra:", self.costo)
        form.addRow("Precio Venta:", self.precio)
        form.addRow("IGV:", self.igv)
        form.addRow("Valor Venta:", self.valor_venta)
        form.addRow("% Margen de Ganancia:", self.ganancia)
        form.addRow("", self.afecto_igv)
        #form.addRow("", self.activar_precio)
        form.addRow("", self.activar_extra)

        self.btn_guardar = QPushButton("Guardar Producto")
        self.btn_guardar.clicked.connect(self.validar_producto)

        layout.addLayout(form)
        layout.addWidget(self.btn_guardar)
        self.setLayout(layout)

        
    def convertir_a_mayusculas(self):
        cursor_pos = self.desc_larga.cursorPosition()  # Guardar posición del cursor
        texto = self.desc_larga.text()
        texto2= self.desc_corta.text()
        self.desc_larga.setText(texto.upper())
        self.desc_corta.setText(texto2.upper())
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
        else:
            self.plu.clear()

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
           
    def calcular_igv(self):
        try:
            precio = float(self.precio.text()) if self.precio.text() else 0.0
            costo = float(self.costo.text()) if self.costo.text() else 0.0
            if costo == 0.0:
                self.valor_venta.setText("0.00")
                self.ganancia.setText("0%")
                self.igv.setText("0.00")
                self.precio.setText("0.00")
                return 
            if self.afecto_igv.isChecked():
                igv_valor = round(precio * 0.18, 2)
                base_valor = round(precio / 1.18, 2)
                valor_ganancia = round(((base_valor/costo)-1) * 100, 2)
            else:
                igv_valor = 0.0
                base_valor = precio
                valor_ganancia = round(((base_valor/costo)-1) * 100, 2)
            self.igv.setText(str(igv_valor))
            self.valor_venta.setText(str(base_valor))
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
        codigo_barras = self.codigo_barra.text().strip()
        if len(codigo_barras) > 13 or not codigo_barras.isdigit():
            QMessageBox.warning(self, "Validación", "El código de barras no puede exceder los 13 dígitos.")
            return False

        self.desc_larga.setText(self.desc_larga.text().strip().upper())
        self.desc_corta.setText(self.desc_corta.text().strip().upper())
        return True

        QMessageBox.information(self, "Validado", "Producto listo para guardar (aún sin insertar en DB).")
