from PySide6.QtWidgets import QDialog, QVBoxLayout, QLabel, QLineEdit, QPushButton, QComboBox, QFormLayout, QMessageBox, QGroupBox
from PySide6.QtCore import Qt

class UserForm(QDialog):
    def __init__(self, db, modo='agregar', datos=None):
        super().__init__()
        self.db = db
        self.setWindowTitle("Agregar Usuario" if modo == 'agregar' else "Editar Usuario")
        self.setGeometry(300, 300, 400, 300)
        self.modo = modo
        self.datos = datos
        
        # Aplicar estilos
        self.setStyleSheet(self._get_styles())

        layout = QVBoxLayout()
        layout.setSpacing(15)
        layout.setContentsMargins(20, 20, 20, 20)
        
        # Grupo principal
        main_group = QGroupBox("Datos del Usuario")
        main_group.setStyleSheet("""
            QGroupBox {
                font-weight: bold;
                font-size: 12px;
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
        """)
        
        form = QFormLayout()
        form.setLabelAlignment(Qt.AlignRight | Qt.AlignVCenter)
        form.setFormAlignment(Qt.AlignLeft | Qt.AlignTop)
        form.setSpacing(12)

        self.nombre = QLineEdit()
        self.nombre.setPlaceholderText("Ingrese el nombre completo")
        self.usuario = QLineEdit()
        self.usuario.setPlaceholderText("Ingrese el nombre de usuario")
        self.clave = QLineEdit()
        self.clave.setEchoMode(QLineEdit.Password)
        self.clave.setPlaceholderText("Ingrese la contraseña")
        self.rol = QComboBox()
        self.rol.addItems(['admin', 'cajero', 'jefe', 'supervisor', 'tesorero', 'barras'])

        form.addRow("Nombre:", self.nombre)
        form.addRow("Usuario:", self.usuario)
        if modo == 'agregar':
            form.addRow("Contraseña:", self.clave)
        form.addRow("Rol:", self.rol)

        main_group.setLayout(form)
        
        # Botones
        btn_layout = QVBoxLayout()
        self.guardar_btn = QPushButton("💾 Guardar Usuario")
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

        if datos:
            self.nombre.setText(datos["nombre"])
            self.usuario.setText(datos["usuario"])
            self.rol.setCurrentText(datos["rol"])

    def _get_styles(self):
        """Retorna los estilos CSS consistentes con la aplicación"""
        return """
        QWidget {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11px;
            background-color: #f5f7fa;
        }
        
        QLineEdit {
            padding: 8px 12px;
            border: 2px solid #5d6d7e;
            border-radius: 6px;
            font-size: 11px;
            background-color: white;
            color: #2c3e50;
        }
        
        QLineEdit:focus {
            border-color: #3498db;
            background-color: #ffffff;
        }
        
        QComboBox {
            padding: 8px 12px;
            border: 2px solid #5d6d7e;
            border-radius: 6px;
            font-size: 11px;
            background-color: white;
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
            color: #2c3e50;
            font-size: 11px;
            font-weight: bold;
        }
        """

    def guardar(self):
        """Valida y guarda los datos del usuario"""
        nombre = self.nombre.text().strip()
        usuario = self.usuario.text().strip()
        rol = self.rol.currentText()
        
        # Validaciones
        if not nombre or not usuario:
            QMessageBox.warning(self, "⚠️ Validación", "Nombre y usuario son obligatorios")
            return
            
        if len(usuario) < 3:
            QMessageBox.warning(self, "⚠️ Validación", "El nombre de usuario debe tener al menos 3 caracteres")
            return
            
        if self.modo == 'agregar':
            clave = self.clave.text().strip()
            if not clave:
                QMessageBox.warning(self, "⚠️ Validación", "Contraseña obligatoria")
                return
            if len(clave) < 4:
                QMessageBox.warning(self, "⚠️ Validación", "La contraseña debe tener al menos 4 caracteres")
                return
            try:
                self.db.agregar_usuario(nombre, usuario, clave, rol)
                QMessageBox.information(self, "✅ Éxito", f"Usuario '{usuario}' agregado correctamente")
            except Exception as e:
                QMessageBox.critical(self, "❌ Error", f"Error al agregar usuario: {str(e)}")
                return
        else:
            try:
                self.db.editar_usuario(self.datos['id'], nombre, usuario, rol)
                QMessageBox.information(self, "✅ Éxito", f"Usuario '{usuario}' actualizado correctamente")
            except Exception as e:
                QMessageBox.critical(self, "❌ Error", f"Error al actualizar usuario: {str(e)}")
                return
                
        self.accept()

