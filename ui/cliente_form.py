from PySide6.QtWidgets import (QDialog, QVBoxLayout, QLabel, QLineEdit, QPushButton, 
                               QComboBox, QFormLayout, QMessageBox, QGroupBox, QTextEdit)
from PySide6.QtCore import Qt
import re

class ClienteForm(QDialog):
    def __init__(self, db, modo='agregar', datos=None):
        super().__init__()
        self.db = db
        self.setWindowTitle("Agregar Cliente" if modo == 'agregar' else "Editar Cliente")
        self.setGeometry(300, 300, 500, 400)
        self.modo = modo
        self.datos = datos
        
        # Aplicar estilos
        self.setStyleSheet(self._get_styles())

        layout = QVBoxLayout()
        layout.setSpacing(15)
        layout.setContentsMargins(20, 20, 20, 20)
        
        # Grupo principal
        main_group = QGroupBox("Datos del Cliente")
        main_group.setStyleSheet("""
            QGroupBox {
                font-weight: bold;
                font-size: 12px;
                color: white;
                border: 2px solid #34495e;
                border-radius: 8px;
                margin-top: 8px;
                padding-top: 8px;
                background-color: #34495e;
            }
            QGroupBox::title {
                subcontrol-origin: margin;
                left: 10px;
                padding: 0 8px 0 8px;
                background-color: #34495e;
                color: white;
                font-weight: bold;
            }
        """)
        
        form = QFormLayout()
        form.setLabelAlignment(Qt.AlignRight | Qt.AlignVCenter)
        form.setFormAlignment(Qt.AlignLeft | Qt.AlignTop)
        form.setSpacing(12)

        # Documento
        self.tipo_documento = QComboBox()
        self.tipo_documento.addItems(['DNI', 'RUC'])
        self.tipo_documento.currentTextChanged.connect(self.on_tipo_documento_changed)
        
        self.numero_documento = QLineEdit()
        self.numero_documento.setPlaceholderText("Ingrese el número de documento")
        self.numero_documento.textChanged.connect(self.validar_documento)
        
        # Nombre
        self.nombre = QLineEdit()
        self.nombre.setPlaceholderText("Ingrese el nombre completo")
        
        # Dirección
        self.direccion = QTextEdit()
        self.direccion.setPlaceholderText("Ingrese la dirección completa")
        self.direccion.setMaximumHeight(60)
        
        # Teléfono
        self.telefono = QLineEdit()
        self.telefono.setPlaceholderText("Ingrese el número de teléfono")
        self.telefono.setMaxLength(15)
        
        # Email
        self.email = QLineEdit()
        self.email.setPlaceholderText("Ingrese el correo electrónico")
        
        # Agregar campos al formulario
        form.addRow("Tipo Documento:", self.tipo_documento)
        form.addRow("Número Documento:", self.numero_documento)
        form.addRow("Nombre Completo:", self.nombre)
        form.addRow("Dirección:", self.direccion)
        form.addRow("Teléfono:", self.telefono)
        form.addRow("Email:", self.email)

        main_group.setLayout(form)
        
        # Botones
        btn_layout = QVBoxLayout()
        self.guardar_btn = QPushButton("💾 Guardar Cliente")
        self.guardar_btn.clicked.connect(self.guardar)
        self.guardar_btn.setStyleSheet("""
            QPushButton {
                background-color: #27ae60;
                color: white;
                font-weight: bold;
                font-size: 12px;
                padding: 10px;
                border: none;
                border-radius: 6px;
            }
            QPushButton:hover {
                background-color: #229954;
            }
            QPushButton:pressed {
                background-color: #1e8449;
            }
        """)

        layout.addWidget(main_group)
        layout.addWidget(self.guardar_btn)
        self.setLayout(layout)

        # Cargar datos si es modo edición
        if datos:
            self.cargar_datos()

    def _get_styles(self):
        """Retorna los estilos CSS consistentes con la aplicación"""
        return """
        QWidget {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11px;
            background-color: #2c3e50;
        }
        
        QLineEdit {
            padding: 8px 12px;
            border: 2px solid #34495e;
            border-radius: 6px;
            font-size: 11px;
            background-color: #ecf0f1;
            color: #2c3e50;
        }
        
        QLineEdit:focus {
            border-color: #3498db;
            background-color: #ffffff;
        }
        
        QTextEdit {
            padding: 8px 12px;
            border: 2px solid #34495e;
            border-radius: 6px;
            font-size: 11px;
            background-color: #ecf0f1;
            color: #2c3e50;
        }
        
        QTextEdit:focus {
            border-color: #3498db;
            background-color: #ffffff;
        }
        
        QComboBox {
            padding: 8px 12px;
            border: 2px solid #34495e;
            border-radius: 6px;
            font-size: 11px;
            background-color: #ecf0f1;
            color: #2c3e50;
            min-height: 20px;
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
        
        QLabel {
            color: white;
            font-size: 11px;
            font-weight: bold;
        }
        """

    def on_tipo_documento_changed(self):
        """Actualiza el placeholder según el tipo de documento"""
        if self.tipo_documento.currentText() == 'DNI':
            self.numero_documento.setPlaceholderText("Ingrese el DNI (8 dígitos)")
            self.numero_documento.setMaxLength(8)
        else:  # RUC
            self.numero_documento.setPlaceholderText("Ingrese el RUC (11 dígitos)")
            self.numero_documento.setMaxLength(11)

    def validar_documento(self):
        """Valida el formato del documento"""
        texto = self.numero_documento.text()
        if not texto:
            return
            
        # Solo permitir números
        if not texto.isdigit():
            self.numero_documento.setText(re.sub(r'[^0-9]', '', texto))
            return
            
        # Validar longitud según tipo
        if self.tipo_documento.currentText() == 'DNI' and len(texto) > 8:
            self.numero_documento.setText(texto[:8])
        elif self.tipo_documento.currentText() == 'RUC' and len(texto) > 11:
            self.numero_documento.setText(texto[:11])

    def cargar_datos(self):
        """Carga los datos del cliente para edición"""
        if self.datos:
            self.tipo_documento.setCurrentText(self.datos[1])  # documento_tipo
            self.numero_documento.setText(self.datos[2])       # documento_numero
            self.nombre.setText(self.datos[3])                 # nombre
            self.direccion.setPlainText(self.datos[4] or "")   # direccion
            self.telefono.setText(self.datos[5] or "")         # telefono
            self.email.setText(self.datos[6] or "")            # email

    def validar_formulario(self):
        """Valida los datos del formulario"""
        errores = []
        
        # Validar documento
        if not self.numero_documento.text().strip():
            errores.append("El número de documento es obligatorio")
        else:
            doc = self.numero_documento.text().strip()
            if self.tipo_documento.currentText() == 'DNI' and len(doc) != 8:
                errores.append("El DNI debe tener 8 dígitos")
            elif self.tipo_documento.currentText() == 'RUC' and len(doc) != 11:
                errores.append("El RUC debe tener 11 dígitos")
        
        # Validar nombre
        if not self.nombre.text().strip():
            errores.append("El nombre es obligatorio")
        elif len(self.nombre.text().strip()) < 3:
            errores.append("El nombre debe tener al menos 3 caracteres")
        
        # Validar email si se proporciona
        email = self.email.text().strip()
        if email and not self.validar_email(email):
            errores.append("El formato del email no es válido")
        
        # Validar teléfono si se proporciona
        telefono = self.telefono.text().strip()
        if telefono and not self.validar_telefono(telefono):
            errores.append("El formato del teléfono no es válido")
        
        return errores

    def validar_email(self, email):
        """Valida el formato del email"""
        patron = r'^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$'
        return re.match(patron, email) is not None

    def validar_telefono(self, telefono):
        """Valida el formato del teléfono"""
        # Permitir números con o sin espacios, guiones, paréntesis
        patron = r'^[\d\s\-\(\)\+]+$'
        return re.match(patron, telefono) is not None and len(telefono.replace(' ', '').replace('-', '').replace('(', '').replace(')', '').replace('+', '')) >= 7

    def guardar(self):
        """Valida y guarda los datos del cliente"""
        # Validar formulario
        errores = self.validar_formulario()
        if errores:
            QMessageBox.warning(self, "⚠️ Validación", "\n".join(errores))
            return
        
        # Obtener datos
        documento_tipo = self.tipo_documento.currentText()
        documento_numero = self.numero_documento.text().strip()
        nombre = self.nombre.text().strip()
        direccion = self.direccion.toPlainText().strip()
        telefono = self.telefono.text().strip()
        email = self.email.text().strip()
        
        try:
            if self.modo == 'agregar':
                # Verificar si el documento ya existe
                cliente_existente = self.db.get_cliente_por_documento(documento_numero)
                if cliente_existente:
                    QMessageBox.warning(self, "⚠️ Cliente Existente", 
                                      f"Ya existe un cliente con el documento {documento_numero}")
                    return
                
                self.db.agregar_cliente(documento_tipo, documento_numero, nombre, 
                                      direccion, telefono, email)
                QMessageBox.information(self, "✅ Éxito", f"Cliente '{nombre}' agregado correctamente")
            else:
                self.db.editar_cliente(self.datos[0], documento_tipo, documento_numero, 
                                     nombre, direccion, telefono, email)
                QMessageBox.information(self, "✅ Éxito", f"Cliente '{nombre}' actualizado correctamente")
                
            self.accept()
            
        except Exception as e:
            QMessageBox.critical(self, "❌ Error", f"Error al guardar cliente: {str(e)}")
