from PySide6.QtWidgets import (QWidget, QVBoxLayout, QTableWidget, QTableWidgetItem, QLabel,
                               QPushButton, QHBoxLayout, QLineEdit, QMessageBox, QGroupBox, QHeaderView,
                               QDialog, QFormLayout, QComboBox)
from PySide6.QtCore import Qt
from PySide6.QtGui import QFont
from ui.user_form import UserForm

class UserAdmin(QWidget):
    def __init__(self, db, usuario_actual=None):
        super().__init__()
        self.db = db
        self.usuario_actual = usuario_actual
        self.es_admin = False
        
        # Verificar si el usuario actual es admin
        if usuario_actual:
            rol = self.db.get_rol_usuario(usuario_actual)
            self.es_admin = (rol == 'admin')
        self.setWindowTitle("👥 Gestión de Usuarios")
        self.setGeometry(200, 200, 800, 500)
        
        # Aplicar estilos
        self.setStyleSheet(self._get_styles())

        layout = QVBoxLayout()
        layout.setSpacing(15)
        layout.setContentsMargins(20, 20, 20, 20)
        
        # Título
        title_label = QLabel("👥 Gestión de Usuarios del Sistema")
        title_label.setStyleSheet("""
            QLabel {
                font-size: 16px;
                font-weight: bold;
                color: #2c3e50;
                padding: 10px;
                background-color: #e8edf4;
                border: 2px solid #5d6d7e;
                border-radius: 8px;
            }
        """)
        
        # Grupo de búsqueda
        search_group = QGroupBox("🔍 Búsqueda")
        search_group.setStyleSheet("""
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
        
        search_layout = QVBoxLayout()
        self.search_box = QLineEdit()
        self.search_box.setPlaceholderText("🔍 Buscar por nombre o usuario...")
        self.search_box.textChanged.connect(self.load_users)
        search_layout.addWidget(self.search_box)
        search_group.setLayout(search_layout)

        # Tabla de usuarios
        self.table = QTableWidget()
        self.table.setAlternatingRowColors(True)
        self.table.setSelectionBehavior(QTableWidget.SelectRows)
        self.table.setSelectionMode(QTableWidget.SingleSelection)
        self.table.verticalHeader().setVisible(False)
        self.table.setShowGrid(True)
        self.table.setSortingEnabled(True)
        
        # Configurar encabezados con distribución específica
        header = self.table.horizontalHeader()
        header.setStretchLastSection(False)
        header.setSectionResizeMode(QHeaderView.Interactive)
        
        # Configurar altura de filas
        self.table.verticalHeader().setDefaultSectionSize(35)
        self.table.verticalHeader().setMinimumSectionSize(30)

        # Botones de acción
        btn_layout = QHBoxLayout()
        btn_layout.setSpacing(10)
        
        self.add_btn = QPushButton("➕ Agregar Usuario")
        self.edit_btn = QPushButton("✏️ Editar Usuario")
        self.del_btn = QPushButton("🗑️ Eliminar Usuario")
        self.cambiar_pass_btn = QPushButton("🔐 Cambiar Contraseña")
        self.cambiar_conexion_btn = QPushButton("🔌 Cambiar Conexión")
        self.refresh_btn = QPushButton("🔄 Actualizar")
        
        # Botón cambiar contraseña solo visible para admin
        self.cambiar_pass_btn.setVisible(self.es_admin)
        # Botón cambiar conexión solo visible para admin
        self.cambiar_conexion_btn.setVisible(self.es_admin)

        # Estilos de botones
        self.add_btn.setStyleSheet("""
            QPushButton {
                background-color: #27ae60;
                color: white;
                font-weight: bold;
                font-size: 11px;
                padding: 8px 16px;
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
        
        self.edit_btn.setStyleSheet("""
            QPushButton {
                background-color: #f39c12;
                color: white;
                font-weight: bold;
                font-size: 11px;
                padding: 8px 16px;
                border: none;
                border-radius: 6px;
            }
            QPushButton:hover {
                background-color: #e67e22;
            }
            QPushButton:pressed {
                background-color: #d68910;
            }
        """)
        
        self.del_btn.setStyleSheet("""
            QPushButton {
                background-color: #e74c3c;
                color: white;
                font-weight: bold;
                font-size: 11px;
                padding: 8px 16px;
                border: none;
                border-radius: 6px;
            }
            QPushButton:hover {
                background-color: #c0392b;
            }
            QPushButton:pressed {
                background-color: #a93226;
            }
        """)
        
        self.refresh_btn.setStyleSheet("""
            QPushButton {
                background-color: #3498db;
                color: white;
                font-weight: bold;
                font-size: 11px;
                padding: 8px 16px;
                border: none;
                border-radius: 6px;
            }
            QPushButton:hover {
                background-color: #2980b9;
            }
            QPushButton:pressed {
                background-color: #21618c;
            }
        """)
        
        self.cambiar_pass_btn.setStyleSheet("""
            QPushButton {
                background-color: #8e44ad;
                color: white;
                font-weight: bold;
                font-size: 11px;
                padding: 8px 16px;
                border: none;
                border-radius: 6px;
            }
            QPushButton:hover {
                background-color: #7d3c98;
            }
            QPushButton:pressed {
                background-color: #6c3483;
            }
        """)
        
        self.cambiar_conexion_btn.setStyleSheet("""
            QPushButton {
                background-color: #16a085;
                color: white;
                font-weight: bold;
                font-size: 11px;
                padding: 8px 16px;
                border: none;
                border-radius: 6px;
            }
            QPushButton:hover {
                background-color: #138d75;
            }
            QPushButton:pressed {
                background-color: #117a65;
            }
        """)

        self.add_btn.clicked.connect(self.agregar_usuario)
        self.edit_btn.clicked.connect(self.editar_usuario)
        self.del_btn.clicked.connect(self.eliminar_usuario)
        self.cambiar_pass_btn.clicked.connect(self.cambiar_contraseña)
        self.cambiar_conexion_btn.clicked.connect(self.cambiar_estado_conexion)
        self.refresh_btn.clicked.connect(self.load_users)

        btn_layout.addWidget(self.add_btn)
        btn_layout.addWidget(self.edit_btn)
        btn_layout.addWidget(self.del_btn)
        btn_layout.addWidget(self.cambiar_pass_btn)
        btn_layout.addWidget(self.cambiar_conexion_btn)
        btn_layout.addStretch()
        btn_layout.addWidget(self.refresh_btn)

        layout.addWidget(title_label)
        layout.addWidget(search_group)
        layout.addWidget(self.table)
        layout.addLayout(btn_layout)
        self.setLayout(layout)

        self.load_users()

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
        
        QTableWidget {
            background-color: white;
            border: 2px solid #5d6d7e;
            border-radius: 8px;
            gridline-color: #bdc3c7;
            selection-background-color: #d6eaf8;
            selection-color: #2c3e50;
            alternate-background-color: #f8f9fa;
            font-size: 11px;
        }
        
        QTableWidget::item {
            padding: 8px 12px;
            border-bottom: 1px solid #e8edf4;
            color: #2c3e50;
            font-size: 11px;
            font-weight: 500;
        }
        
        QTableWidget::item:selected {
            background-color: #d6eaf8;
            color: #2c3e50;
            font-weight: bold;
        }
        
        QTableWidget::item:hover {
            background-color: #e8f4fd;
        }
        
        QHeaderView::section {
            background-color: #34495e;
            color: white;
            padding: 12px 8px;
            border: none;
            font-weight: bold;
            font-size: 11px;
            text-align: center;
        }
        
        QHeaderView::section:first {
            border-top-left-radius: 6px;
        }
        
        QHeaderView::section:last {
            border-top-right-radius: 6px;
        }
        
        QScrollBar:vertical {
            background-color: #f1f2f6;
            width: 12px;
            border-radius: 6px;
        }
        
        QScrollBar::handle:vertical {
            background-color: #5d6d7e;
            border-radius: 6px;
            min-height: 20px;
        }
        
        QScrollBar::handle:vertical:hover {
            background-color: #34495e;
        }
        
        QScrollBar:horizontal {
            background-color: #f1f2f6;
            height: 12px;
            border-radius: 6px;
        }
        
        QScrollBar::handle:horizontal {
            background-color: #5d6d7e;
            border-radius: 6px;
            min-width: 20px;
        }
        
        QScrollBar::handle:horizontal:hover {
            background-color: #34495e;
        }
        """

    def load_users(self):
        """Carga la lista de usuarios en la tabla"""
        try:
            filtro = self.search_box.text()
            usuarios = self.db.get_usuarios(filtro)
            self.table.setRowCount(len(usuarios))
            self.table.setColumnCount(6)
            self.table.setHorizontalHeaderLabels(["ID", "Nombre", "Usuario", "Rol", "Conectado", "Estado"])

            # Configurar anchos específicos de columnas
            self.table.setColumnWidth(0, 60)   # ID - más estrecho
            self.table.setColumnWidth(1, 200)  # Nombre - más ancho
            self.table.setColumnWidth(2, 150)  # Usuario - medio
            self.table.setColumnWidth(3, 100)  # Rol - medio
            self.table.setColumnWidth(4, 80)   # Conectado - estrecho
            self.table.setColumnWidth(5, 100)  # Estado - medio

            for row_idx, usuario in enumerate(usuarios):
                for col_idx, value in enumerate(usuario):
                    item = QTableWidgetItem(str(value))
                    
                    # Alineación específica por columna
                    if col_idx == 0:  # ID - centrado
                        item.setTextAlignment(Qt.AlignCenter)
                    elif col_idx == 1:  # Nombre - izquierda
                        item.setTextAlignment(Qt.AlignLeft | Qt.AlignVCenter)
                    elif col_idx == 2:  # Usuario - izquierda
                        item.setTextAlignment(Qt.AlignLeft | Qt.AlignVCenter)
                    elif col_idx == 3:  # Rol - centrado
                        item.setTextAlignment(Qt.AlignCenter)
                    elif col_idx == 4:  # Conectado - centrado
                        item.setTextAlignment(Qt.AlignCenter)
                    elif col_idx == 5:  # Estado - centrado
                        item.setTextAlignment(Qt.AlignCenter)
                    
                    # Colorear según el estado
                    if col_idx == 5:  # Columna Estado
                        if "activo" in str(value).lower():
                            item.setBackground(Qt.green)
                            item.setForeground(Qt.white)
                        elif "inactivo" in str(value).lower():
                            item.setBackground(Qt.red)
                            item.setForeground(Qt.white)
                        else:
                            item.setBackground(Qt.lightGray)
                            item.setForeground(Qt.black)
                    
                    # Colorear según conexión
                    elif col_idx == 4:  # Columna Conectado
                        if "sí" in str(value).lower() or "si" in str(value).lower() or "true" in str(value).lower():
                            item.setBackground(Qt.green)
                            item.setForeground(Qt.white)
                        else:
                            item.setBackground(Qt.red)
                            item.setForeground(Qt.white)
                    
                    # Colorear según rol
                    elif col_idx == 3:  # Columna Rol
                        rol = str(value).lower()
                        if "admin" in rol:
                            item.setBackground(Qt.darkBlue)
                            item.setForeground(Qt.white)
                        elif "supervisor" in rol:
                            item.setBackground(Qt.darkMagenta)
                            item.setForeground(Qt.white)
                        elif "cajero" in rol:
                            item.setBackground(Qt.darkGreen)
                            item.setForeground(Qt.white)
                        else:
                            item.setBackground(Qt.darkGray)
                            item.setForeground(Qt.white)
                    
                    self.table.setItem(row_idx, col_idx, item)
            
            # Ajustar altura de filas automáticamente
            self.table.resizeRowsToContents()
                    
        except Exception as e:
            QMessageBox.critical(self, "❌ Error", f"Error al cargar usuarios: {str(e)}")

    def agregar_usuario(self):
        """Abre el formulario para agregar un nuevo usuario"""
        try:
            form = UserForm(self.db, modo='agregar')
            if form.exec():
                self.load_users()
        except Exception as e:
            QMessageBox.critical(self, "❌ Error", f"Error al abrir formulario: {str(e)}")

    def editar_usuario(self):
        """Abre el formulario para editar el usuario seleccionado"""
        fila = self.table.currentRow()
        if fila < 0:
            QMessageBox.warning(self, "⚠️ Advertencia", "Seleccione un usuario para editar")
            return
            
        try:
            datos = {
                "id": int(self.table.item(fila, 0).text()),
                "nombre": self.table.item(fila, 1).text(),
                "usuario": self.table.item(fila, 2).text(),
                "rol": self.table.item(fila, 3).text()
            }
            form = UserForm(self.db, modo='editar', datos=datos)
            if form.exec():
                self.load_users()
        except Exception as e:
            QMessageBox.critical(self, "❌ Error", f"Error al editar usuario: {str(e)}")

    def eliminar_usuario(self):
        """Elimina el usuario seleccionado con confirmación"""
        fila = self.table.currentRow()
        if fila < 0:
            QMessageBox.warning(self, "⚠️ Advertencia", "Seleccione un usuario para eliminar")
            return
            
        try:
            id_usuario = int(self.table.item(fila, 0).text())
            nombre_usuario = self.table.item(fila, 2).text()
            
            confirm = QMessageBox.question(
                self, 
                "🗑️ Confirmar Eliminación", 
                f"¿Está seguro de eliminar el usuario '{nombre_usuario}'?\n\nEsta acción no se puede deshacer.",
                QMessageBox.Yes | QMessageBox.No,
                QMessageBox.No
            )
            
            if confirm == QMessageBox.Yes:
                self.db.eliminar_usuario(id_usuario)
                self.load_users()
                QMessageBox.information(self, "✅ Éxito", f"Usuario '{nombre_usuario}' eliminado correctamente")
                
        except Exception as e:
            QMessageBox.critical(self, "❌ Error", f"Error al eliminar usuario: {str(e)}")

    def cambiar_contraseña(self):
        """Cambiar la contraseña del usuario seleccionado (solo admin)"""
        # Verificar que sea admin
        if not self.es_admin:
            QMessageBox.warning(self, "⚠️ Acceso Denegado", "Solo los administradores pueden cambiar contraseñas.")
            return
        
        fila = self.table.currentRow()
        if fila < 0:
            QMessageBox.warning(self, "⚠️ Advertencia", "Seleccione un usuario para cambiar su contraseña")
            return
        
        try:
            usuario_id = int(self.table.item(fila, 0).text())
            nombre_usuario = self.table.item(fila, 1).text()
            login_usuario = self.table.item(fila, 2).text()
            
            # Crear diálogo para cambiar contraseña
            dialog = QDialog(self)
            dialog.setWindowTitle("🔐 Cambiar Contraseña")
            dialog.setModal(True)
            dialog.setFixedSize(450, 250)
            
            layout = QVBoxLayout(dialog)
            layout.setSpacing(15)
            layout.setContentsMargins(20, 20, 20, 20)
            
            # Información del usuario
            info_label = QLabel(f"Usuario: <b>{login_usuario}</b><br>Nombre: {nombre_usuario}")
            info_label.setStyleSheet("font-size: 12px; color: #2c3e50; padding: 10px; background-color: #e8edf4; border-radius: 6px;")
            layout.addWidget(info_label)
            
            # Formulario
            form_layout = QFormLayout()
            
            self.nueva_pass_input = QLineEdit()
            self.nueva_pass_input.setEchoMode(QLineEdit.Password)
            self.nueva_pass_input.setPlaceholderText("Ingrese la nueva contraseña")
            self.nueva_pass_input.setMinimumHeight(35)
            form_layout.addRow("Nueva Contraseña:", self.nueva_pass_input)
            
            self.confirmar_pass_input = QLineEdit()
            self.confirmar_pass_input.setEchoMode(QLineEdit.Password)
            self.confirmar_pass_input.setPlaceholderText("Confirme la nueva contraseña")
            self.confirmar_pass_input.setMinimumHeight(35)
            form_layout.addRow("Confirmar Contraseña:", self.confirmar_pass_input)
            
            layout.addLayout(form_layout)
            
            # Botones
            btn_layout = QHBoxLayout()
            btn_layout.addStretch()
            
            btn_cancelar = QPushButton("❌ Cancelar")
            btn_cancelar.setStyleSheet("""
                QPushButton {
                    background-color: #95a5a6;
                    color: white;
                    font-weight: bold;
                    padding: 10px 20px;
                    border-radius: 6px;
                }
                QPushButton:hover {
                    background-color: #7f8c8d;
                }
            """)
            btn_cancelar.clicked.connect(dialog.reject)
            btn_layout.addWidget(btn_cancelar)
            
            btn_guardar = QPushButton("💾 Guardar")
            btn_guardar.setStyleSheet("""
                QPushButton {
                    background-color: #27ae60;
                    color: white;
                    font-weight: bold;
                    padding: 10px 20px;
                    border-radius: 6px;
                }
                QPushButton:hover {
                    background-color: #229954;
                }
            """)
            btn_guardar.clicked.connect(lambda: self._guardar_contraseña(dialog, usuario_id, login_usuario))
            btn_layout.addWidget(btn_guardar)
            
            layout.addLayout(btn_layout)
            
            # Estilos del diálogo
            dialog.setStyleSheet("""
                QDialog {
                    background-color: #f5f7fa;
                }
                QLabel {
                    color: #2c3e50;
                }
                QLineEdit {
                    padding: 8px;
                    border: 2px solid #5d6d7e;
                    border-radius: 6px;
                    font-size: 11px;
                    background-color: white;
                }
                QLineEdit:focus {
                    border-color: #3498db;
                }
            """)
            
            dialog.exec()
            
        except Exception as e:
            QMessageBox.critical(self, "❌ Error", f"Error al cambiar contraseña: {str(e)}")

    def _guardar_contraseña(self, dialog, usuario_id, login_usuario):
        """Guarda la nueva contraseña después de validar"""
        try:
            nueva_pass = self.nueva_pass_input.text().strip()
            confirmar_pass = self.confirmar_pass_input.text().strip()
            
            # Validaciones
            if not nueva_pass:
                QMessageBox.warning(self, "Validación", "La contraseña no puede estar vacía.")
                return
            
            if len(nueva_pass) < 4:
                QMessageBox.warning(self, "Validación", "La contraseña debe tener al menos 4 caracteres.")
                return
            
            if nueva_pass != confirmar_pass:
                QMessageBox.warning(self, "Validación", "Las contraseñas no coinciden.")
                return
            
            # Confirmar cambio
            respuesta = QMessageBox.question(
                self,
                "🔐 Confirmar Cambio",
                f"¿Está seguro de cambiar la contraseña del usuario '{login_usuario}'?",
                QMessageBox.Yes | QMessageBox.No,
                QMessageBox.No
            )
            
            if respuesta == QMessageBox.Yes:
                if self.db.cambiar_contraseña_usuario(usuario_id, nueva_pass):
                    QMessageBox.information(
                        self,
                        "✅ Éxito",
                        f"Contraseña del usuario '{login_usuario}' cambiada correctamente."
                    )
                    dialog.accept()
                else:
                    QMessageBox.critical(
                        self,
                        "❌ Error",
                        "No se pudo cambiar la contraseña. Intente nuevamente."
                    )
                    
        except Exception as e:
            QMessageBox.critical(self, "❌ Error", f"Error al guardar contraseña: {str(e)}")

    def cambiar_estado_conexion(self):
        """Cambiar el estado de conexión del usuario seleccionado (solo admin)"""
        # Verificar que sea admin
        if not self.es_admin:
            QMessageBox.warning(self, "⚠️ Acceso Denegado", "Solo los administradores pueden cambiar el estado de conexión.")
            return
        
        fila = self.table.currentRow()
        if fila < 0:
            QMessageBox.warning(self, "⚠️ Advertencia", "Seleccione un usuario para cambiar su estado de conexión")
            return
        
        try:
            usuario_id = int(self.table.item(fila, 0).text())
            nombre_usuario = self.table.item(fila, 1).text()
            login_usuario = self.table.item(fila, 2).text()
            estado_actual = self.table.item(fila, 4).text()
            
            # Determinar estado actual (puede ser 'si', 'no', 'Sí', 'No', 'true', 'false', etc.)
            estado_actual_lower = str(estado_actual).lower().strip()
            esta_conectado = estado_actual_lower in ['si', 'sí', 'yes', 'true', '1', 'conectado']
            
            # Crear diálogo para cambiar estado de conexión
            dialog = QDialog(self)
            dialog.setWindowTitle("🔌 Cambiar Estado de Conexión")
            dialog.setModal(True)
            dialog.setFixedSize(450, 280)
            
            layout = QVBoxLayout(dialog)
            layout.setSpacing(15)
            layout.setContentsMargins(20, 20, 20, 20)
            
            # Información del usuario
            info_label = QLabel(f"Usuario: <b>{login_usuario}</b><br>Nombre: {nombre_usuario}<br><br>Estado Actual: <b>{'Conectado' if esta_conectado else 'Desconectado'}</b>")
            info_label.setStyleSheet("font-size: 12px; color: #2c3e50; padding: 10px; background-color: #e8edf4; border-radius: 6px;")
            layout.addWidget(info_label)
            
            # Formulario
            form_layout = QFormLayout()
            
            # Combo para seleccionar nuevo estado
            self.combo_nuevo_estado = QComboBox()
            self.combo_nuevo_estado.addItems(["Desconectado (no)", "Conectado (si)"])
            self.combo_nuevo_estado.setCurrentIndex(0 if not esta_conectado else 1)
            self.combo_nuevo_estado.setMinimumHeight(35)
            form_layout.addRow("Nuevo Estado:", self.combo_nuevo_estado)
            
            layout.addLayout(form_layout)
            
            # Botones
            btn_layout = QHBoxLayout()
            btn_layout.addStretch()
            
            btn_cancelar = QPushButton("❌ Cancelar")
            btn_cancelar.setStyleSheet("""
                QPushButton {
                    background-color: #95a5a6;
                    color: white;
                    font-weight: bold;
                    padding: 10px 20px;
                    border-radius: 6px;
                }
                QPushButton:hover {
                    background-color: #7f8c8d;
                }
            """)
            btn_cancelar.clicked.connect(dialog.reject)
            btn_layout.addWidget(btn_cancelar)
            
            btn_guardar = QPushButton("💾 Guardar")
            btn_guardar.setStyleSheet("""
                QPushButton {
                    background-color: #16a085;
                    color: white;
                    font-weight: bold;
                    padding: 10px 20px;
                    border-radius: 6px;
                }
                QPushButton:hover {
                    background-color: #138d75;
                }
            """)
            btn_guardar.clicked.connect(lambda: self._guardar_estado_conexion(dialog, usuario_id, login_usuario))
            btn_layout.addWidget(btn_guardar)
            
            layout.addLayout(btn_layout)
            
            # Estilos del diálogo
            dialog.setStyleSheet("""
                QDialog {
                    background-color: #f5f7fa;
                }
                QLabel {
                    color: #2c3e50;
                }
                QComboBox {
                    padding: 8px;
                    border: 2px solid #5d6d7e;
                    border-radius: 6px;
                    font-size: 11px;
                    background-color: white;
                    color: #000000;
                }
                QComboBox:focus {
                    border-color: #3498db;
                }
                QComboBox QAbstractItemView {
                    background-color: white;
                    color: #000000;
                    selection-background-color: #3498db;
                    selection-color: white;
                }
            """)
            
            dialog.exec()
            
        except Exception as e:
            QMessageBox.critical(self, "❌ Error", f"Error al cambiar estado de conexión: {str(e)}")

    def _guardar_estado_conexion(self, dialog, usuario_id, login_usuario):
        """Guarda el nuevo estado de conexión"""
        try:
            nuevo_estado_index = self.combo_nuevo_estado.currentIndex()
            nuevo_estado = "si" if nuevo_estado_index == 1 else "no"
            estado_texto = "Conectado" if nuevo_estado == "si" else "Desconectado"
            
            # Confirmar cambio
            respuesta = QMessageBox.question(
                self,
                "🔌 Confirmar Cambio",
                f"¿Está seguro de cambiar el estado de conexión del usuario '{login_usuario}' a '{estado_texto}'?",
                QMessageBox.Yes | QMessageBox.No,
                QMessageBox.No
            )
            
            if respuesta == QMessageBox.Yes:
                if self.db.actualizar_conectado_usuario_por_id(usuario_id, nuevo_estado):
                    QMessageBox.information(
                        self,
                        "✅ Éxito",
                        f"Estado de conexión del usuario '{login_usuario}' cambiado correctamente a '{estado_texto}'."
                    )
                    dialog.accept()
                    # Refrescar la tabla
                    self.load_users()
                else:
                    QMessageBox.critical(
                        self,
                        "❌ Error",
                        "No se pudo cambiar el estado de conexión. Intente nuevamente."
                    )
                    
        except Exception as e:
            QMessageBox.critical(self, "❌ Error", f"Error al guardar estado de conexión: {str(e)}")