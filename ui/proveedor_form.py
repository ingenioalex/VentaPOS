from PySide6.QtWidgets import (
    QDialog, QVBoxLayout, QHBoxLayout, QFormLayout, QLineEdit,
    QPushButton, QComboBox, QTextEdit, QSpinBox, QCheckBox,
    QDateEdit, QGroupBox, QMessageBox, QDoubleSpinBox, QLabel
)
from PySide6.QtCore import Qt, QDate
from datetime import datetime

class ProveedorForm(QDialog):
    """Formulario para agregar/editar proveedores"""
    
    def __init__(self, db_client, usuario_actual, proveedor_data=None):
        super().__init__()
        self.db = db_client
        self.usuario = usuario_actual
        self.proveedor_data = proveedor_data
        self.proveedor_id = proveedor_data[0] if proveedor_data else None
        
        self.setWindowTitle("Nuevo Proveedor" if not proveedor_data else "Editar Proveedor")
        self.setMinimumWidth(900)
        self.setMinimumHeight(700)
        
        self.init_ui()
        
        if proveedor_data:
            self.cargar_datos()
    
    def init_ui(self):
        """Inicializar interfaz de usuario"""
        layout = QVBoxLayout(self)
        layout.setSpacing(10)
        
        # Información Básica
        basica_group = QGroupBox("📋 Información Básica")
        basica_layout = QFormLayout()
        
        self.razon_social = QLineEdit()
        self.razon_social.setMaxLength(200)
        basica_layout.addRow("Razón Social:*", self.razon_social)
        
        self.nombre_comercial = QLineEdit()
        self.nombre_comercial.setMaxLength(200)
        basica_layout.addRow("Nombre Comercial:", self.nombre_comercial)
        
        self.ruc = QLineEdit()
        self.ruc.setMaxLength(11)
        basica_layout.addRow("RUC:*", self.ruc)
        
        self.direccion = QTextEdit()
        self.direccion.setMaximumHeight(60)
        basica_layout.addRow("Dirección:", self.direccion)
        
        basica_group.setLayout(basica_layout)
        layout.addWidget(basica_group)
        
        # Información de Contacto
        contacto_group = QGroupBox("📞 Información de Contacto")
        contacto_layout = QFormLayout()
        
        self.telefono = QLineEdit()
        self.telefono.setMaxLength(20)
        contacto_layout.addRow("Teléfono:", self.telefono)
        
        self.celular = QLineEdit()
        self.celular.setMaxLength(20)
        contacto_layout.addRow("Celular:", self.celular)
        
        self.email = QLineEdit()
        self.email.setMaxLength(100)
        contacto_layout.addRow("Email:", self.email)
        
        self.web = QLineEdit()
        self.web.setMaxLength(150)
        contacto_layout.addRow("Sitio Web:", self.web)
        
        contacto_group.setLayout(contacto_layout)
        layout.addWidget(contacto_group)
        
        # Persona de Contacto
        persona_group = QGroupBox("👤 Persona de Contacto")
        persona_layout = QFormLayout()
        
        self.contacto_nombre = QLineEdit()
        self.contacto_nombre.setMaxLength(100)
        persona_layout.addRow("Nombre:", self.contacto_nombre)
        
        self.contacto_cargo = QLineEdit()
        self.contacto_cargo.setMaxLength(50)
        persona_layout.addRow("Cargo:", self.contacto_cargo)
        
        self.contacto_telefono = QLineEdit()
        self.contacto_telefono.setMaxLength(20)
        persona_layout.addRow("Teléfono:", self.contacto_telefono)
        
        self.contacto_email = QLineEdit()
        self.contacto_email.setMaxLength(100)
        persona_layout.addRow("Email:", self.contacto_email)
        
        persona_group.setLayout(persona_layout)
        layout.addWidget(persona_group)
        
        # Layout horizontal para columnas
        columnas_layout = QHBoxLayout()
        
        # Columna Izquierda: Información Comercial
        comercial_group = QGroupBox("💼 Información Comercial")
        comercial_layout = QFormLayout()
        
        self.tipo_proveedor = QComboBox()
        self.tipo_proveedor.addItems(['general', 'nacional', 'internacional', 'servicio', 'producto'])
        comercial_layout.addRow("Tipo:", self.tipo_proveedor)
        
        self.categoria = QComboBox()
        self.categoria.setEditable(True)
        self.categoria.addItems(['alimentos', 'bebidas', 'suministros', 'servicios', 'limpieza', 'oficina'])
        comercial_layout.addRow("Categoría:", self.categoria)
        
        self.condicion_pago = QComboBox()
        self.condicion_pago.addItems(['contado', 'crédito', 'mixto'])
        comercial_layout.addRow("Condición de Pago:", self.condicion_pago)
        
        self.dias_credito = QSpinBox()
        self.dias_credito.setRange(0, 365)
        comercial_layout.addRow("Días de Crédito:", self.dias_credito)
        
        self.descuento_general = QDoubleSpinBox()
        self.descuento_general.setRange(0, 100)
        self.descuento_general.setDecimals(2)
        self.descuento_general.setSuffix(" %")
        comercial_layout.addRow("Descuento General:", self.descuento_general)
        
        self.calificacion = QSpinBox()
        self.calificacion.setRange(1, 5)
        self.calificacion.setValue(5)
        comercial_layout.addRow("Calificación (1-5):", self.calificacion)
        
        comercial_group.setLayout(comercial_layout)
        columnas_layout.addWidget(comercial_group)
        
        # Columna Derecha: Información Bancaria y Certificaciones
        derecha_layout = QVBoxLayout()
        
        # Información Bancaria
        bancaria_group = QGroupBox("🏦 Información Bancaria")
        bancaria_layout = QFormLayout()
        
        self.banco = QLineEdit()
        self.banco.setMaxLength(100)
        bancaria_layout.addRow("Banco:", self.banco)
        
        self.cuenta_bancaria = QLineEdit()
        self.cuenta_bancaria.setMaxLength(50)
        bancaria_layout.addRow("Cuenta:", self.cuenta_bancaria)
        
        self.tipo_cuenta = QComboBox()
        self.tipo_cuenta.addItems(['', 'ahorros', 'corriente'])
        bancaria_layout.addRow("Tipo Cuenta:", self.tipo_cuenta)
        
        self.cci = QLineEdit()
        self.cci.setMaxLength(50)
        bancaria_layout.addRow("CCI:", self.cci)
        
        bancaria_group.setLayout(bancaria_layout)
        derecha_layout.addWidget(bancaria_group)
        
        # Certificaciones
        cert_group = QGroupBox("📄 Certificaciones")
        cert_layout = QFormLayout()
        
        self.tiene_licencia = QCheckBox()
        cert_layout.addRow("Tiene Licencia:", self.tiene_licencia)
        
        self.numero_licencia = QLineEdit()
        self.numero_licencia.setMaxLength(50)
        cert_layout.addRow("Nº Licencia:", self.numero_licencia)
        
        self.fecha_venc_licencia = QDateEdit()
        self.fecha_venc_licencia.setCalendarPopup(True)
        self.fecha_venc_licencia.setDate(QDate.currentDate())
        cert_layout.addRow("Venc. Licencia:", self.fecha_venc_licencia)
        
        self.tiene_certificado_calidad = QCheckBox()
        cert_layout.addRow("Cert. Calidad:", self.tiene_certificado_calidad)
        
        self.tiene_certificado_sanitario = QCheckBox()
        cert_layout.addRow("Cert. Sanitario:", self.tiene_certificado_sanitario)
        
        cert_group.setLayout(cert_layout)
        derecha_layout.addWidget(cert_group)
        
        columnas_layout.addLayout(derecha_layout)
        layout.addLayout(columnas_layout)
        
        # Estado y Observaciones
        estado_group = QGroupBox("⚙️ Estado y Observaciones")
        estado_layout = QFormLayout()
        
        self.estado = QComboBox()
        self.estado.addItems(['activo', 'inactivo', 'bloqueado'])
        estado_layout.addRow("Estado:", self.estado)
        
        self.observaciones = QTextEdit()
        self.observaciones.setMaximumHeight(60)
        estado_layout.addRow("Observaciones:", self.observaciones)
        
        estado_group.setLayout(estado_layout)
        layout.addWidget(estado_group)
        
        # Botones
        botones_layout = QHBoxLayout()
        
        self.btn_guardar = QPushButton("💾 Guardar")
        self.btn_guardar.clicked.connect(self.guardar_proveedor)
        self.btn_guardar.setStyleSheet("""
            QPushButton {
                background-color: #27ae60;
                color: white;
                font-weight: bold;
                padding: 10px;
                border-radius: 5px;
            }
            QPushButton:hover {
                background-color: #229954;
            }
        """)
        
        self.btn_cancelar = QPushButton("❌ Cancelar")
        self.btn_cancelar.clicked.connect(self.reject)
        self.btn_cancelar.setStyleSheet("""
            QPushButton {
                background-color: #e74c3c;
                color: white;
                font-weight: bold;
                padding: 10px;
                border-radius: 5px;
            }
            QPushButton:hover {
                background-color: #c0392b;
            }
        """)
        
        botones_layout.addWidget(self.btn_guardar)
        botones_layout.addWidget(self.btn_cancelar)
        
        layout.addLayout(botones_layout)
        
        # Aplicar estilos
        self.setStyleSheet("""
            QGroupBox {
                font-weight: bold;
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
            QLineEdit, QTextEdit, QComboBox, QSpinBox, QDoubleSpinBox, QDateEdit {
                padding: 5px;
                border: 1px solid #bdc3c7;
                border-radius: 3px;
            }
        """)
    
    def cargar_datos(self):
        """Cargar datos del proveedor para edición"""
        if not self.proveedor_data:
            return
        
        self.razon_social.setText(str(self.proveedor_data[1] or ""))
        self.nombre_comercial.setText(str(self.proveedor_data[2] or ""))
        self.ruc.setText(str(self.proveedor_data[3] or ""))
        self.direccion.setPlainText(str(self.proveedor_data[4] or ""))
        self.telefono.setText(str(self.proveedor_data[5] or ""))
        self.celular.setText(str(self.proveedor_data[6] or ""))
        self.email.setText(str(self.proveedor_data[7] or ""))
        self.web.setText(str(self.proveedor_data[8] or ""))
        self.contacto_nombre.setText(str(self.proveedor_data[9] or ""))
        self.contacto_cargo.setText(str(self.proveedor_data[10] or ""))
        self.contacto_telefono.setText(str(self.proveedor_data[11] or ""))
        self.contacto_email.setText(str(self.proveedor_data[12] or ""))
        
        tipo = str(self.proveedor_data[13] or "general")
        index = self.tipo_proveedor.findText(tipo)
        if index >= 0:
            self.tipo_proveedor.setCurrentIndex(index)
        
        self.categoria.setCurrentText(str(self.proveedor_data[14] or ""))
        
        condicion = str(self.proveedor_data[15] or "contado")
        index = self.condicion_pago.findText(condicion)
        if index >= 0:
            self.condicion_pago.setCurrentIndex(index)
        
        self.dias_credito.setValue(int(self.proveedor_data[16] or 0))
        self.descuento_general.setValue(float(self.proveedor_data[17] or 0))
        self.banco.setText(str(self.proveedor_data[18] or ""))
        self.cuenta_bancaria.setText(str(self.proveedor_data[19] or ""))
        
        tipo_cta = str(self.proveedor_data[20] or "")
        index = self.tipo_cuenta.findText(tipo_cta)
        if index >= 0:
            self.tipo_cuenta.setCurrentIndex(index)
        
        self.cci.setText(str(self.proveedor_data[21] or ""))
        self.tiene_licencia.setChecked(bool(self.proveedor_data[22]))
        self.numero_licencia.setText(str(self.proveedor_data[23] or ""))
        
        if self.proveedor_data[24]:
            fecha_venc = self.proveedor_data[24]
            if isinstance(fecha_venc, str):
                fecha_venc = datetime.strptime(fecha_venc[:10], '%Y-%m-%d').date()
            self.fecha_venc_licencia.setDate(QDate(fecha_venc.year, fecha_venc.month, fecha_venc.day))
        
        self.tiene_certificado_calidad.setChecked(bool(self.proveedor_data[25]))
        self.tiene_certificado_sanitario.setChecked(bool(self.proveedor_data[26]))
        
        estado = str(self.proveedor_data[27] or "activo")
        index = self.estado.findText(estado)
        if index >= 0:
            self.estado.setCurrentIndex(index)
        
        self.calificacion.setValue(int(self.proveedor_data[28] or 5))
        self.observaciones.setPlainText(str(self.proveedor_data[29] or ""))
    
    def validar_datos(self):
        """Validar datos del formulario"""
        if not self.razon_social.text().strip():
            QMessageBox.warning(self, "Validación", "La razón social es obligatoria")
            self.razon_social.setFocus()
            return False
        
        ruc = self.ruc.text().strip()
        if not ruc:
            QMessageBox.warning(self, "Validación", "El RUC es obligatorio")
            self.ruc.setFocus()
            return False
        
        if len(ruc) != 11:
            QMessageBox.warning(self, "Validación", "El RUC debe tener 11 dígitos")
            self.ruc.setFocus()
            return False
        
        if not ruc.isdigit():
            QMessageBox.warning(self, "Validación", "El RUC debe contener solo números")
            self.ruc.setFocus()
            return False
        
        # Verificar si el RUC ya existe (solo al crear o si cambió)
        if not self.proveedor_id or (self.proveedor_data and ruc != str(self.proveedor_data[3])):
            proveedor_existe = self.db.buscar_proveedor_por_ruc(ruc)
            if proveedor_existe:
                QMessageBox.warning(self, "Validación", "Ya existe un proveedor con este RUC")
                self.ruc.setFocus()
                return False
        
        return True
    
    def guardar_proveedor(self):
        """Guardar o actualizar proveedor"""
        if not self.validar_datos():
            return
        
        try:
            # Preparar fecha de vencimiento de licencia
            fecha_venc_licencia = None
            if self.tiene_licencia.isChecked() and self.numero_licencia.text().strip():
                fecha_venc_licencia = self.fecha_venc_licencia.date().toPython()
            
            datos = (
                self.razon_social.text().strip(),
                self.nombre_comercial.text().strip() or None,
                self.ruc.text().strip(),
                self.direccion.toPlainText().strip() or None,
                self.telefono.text().strip() or None,
                self.celular.text().strip() or None,
                self.email.text().strip() or None,
                self.web.text().strip() or None,
                self.contacto_nombre.text().strip() or None,
                self.contacto_cargo.text().strip() or None,
                self.contacto_telefono.text().strip() or None,
                self.contacto_email.text().strip() or None,
                self.tipo_proveedor.currentText(),
                self.categoria.currentText() or None,
                self.condicion_pago.currentText(),
                self.dias_credito.value(),
                self.descuento_general.value(),
                self.banco.text().strip() or None,
                self.cuenta_bancaria.text().strip() or None,
                self.tipo_cuenta.currentText() or None,
                self.cci.text().strip() or None,
                self.tiene_licencia.isChecked(),
                self.numero_licencia.text().strip() or None,
                fecha_venc_licencia,
                self.tiene_certificado_calidad.isChecked(),
                self.tiene_certificado_sanitario.isChecked(),
                self.estado.currentText(),
                self.calificacion.value(),
                self.observaciones.toPlainText().strip() or None,
                self.usuario
            )
            
            if self.proveedor_id:
                # Actualizar
                self.db.actualizar_proveedor(self.proveedor_id, datos)
                QMessageBox.information(self, "Éxito", "Proveedor actualizado correctamente")
            else:
                # Insertar
                proveedor_id = self.db.insertar_proveedor(datos)
                QMessageBox.information(self, "Éxito", f"Proveedor registrado correctamente con ID: {proveedor_id}")
            
            self.accept()
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error al guardar proveedor: {str(e)}")

