from PySide6.QtWidgets import (
    QMainWindow, QWidget, QVBoxLayout, QHBoxLayout, QTabWidget,
    QGroupBox, QLabel, QLineEdit, QComboBox, QPushButton,
    QTableWidget, QTableWidgetItem, QHeaderView, QMessageBox, QCheckBox
)
from PySide6.QtCore import Qt
from PySide6.QtGui import QFont, QColor


class ConfigSistemaForm(QMainWindow):
    """Configuración del sistema enfocada en gestión de usuarios/roles/sesión (sin alterar esquema)."""

    ROLES_PERMITIDOS = ['admin', 'cajero', 'jefe', 'supervisor', 'tesorero', 'test', 'barras', 'generico', 'digitador']
    CONECTADO_VALORES = ['si', 'no', 'test']

    def __init__(self, db_client, usuario_actual):
        super().__init__()
        self.db = db_client
        self.usuario_actual = usuario_actual
        self.init_ui()
        self.apply_styles()
        self.cargar_usuarios()

    def init_ui(self):
        self.setWindowTitle("⚙️ Configuración del Sistema")
        self.setGeometry(100, 100, 1200, 700)

        central = QWidget()
        self.setCentralWidget(central)

        main_layout = QVBoxLayout(central)

        title = QLabel("⚙️ Configuración del Sistema")
        f = QFont()
        f.setPointSize(16)
        f.setBold(True)
        title.setFont(f)
        title.setAlignment(Qt.AlignCenter)
        main_layout.addWidget(title)

        self.tabs = QTabWidget()
        main_layout.addWidget(self.tabs)

        # Tab: Usuarios y Roles
        tab_usuarios = QWidget()
        ly = QVBoxLayout(tab_usuarios)

        filtros = QHBoxLayout()
        filtros.addWidget(QLabel("Buscar:"))
        self.input_busqueda = QLineEdit()
        self.input_busqueda.setPlaceholderText("Nombre o usuario...")
        self.input_busqueda.textChanged.connect(self.cargar_usuarios)
        filtros.addWidget(self.input_busqueda)

        self.btn_refrescar = QPushButton("🔄 Refrescar")
        self.btn_refrescar.clicked.connect(self.cargar_usuarios)
        filtros.addWidget(self.btn_refrescar)
        filtros.addStretch()
        ly.addLayout(filtros)

        self.tbl_usuarios = QTableWidget()
        self.tbl_usuarios.setColumnCount(7)
        self.tbl_usuarios.setHorizontalHeaderLabels([
            "ID", "Nombre", "Usuario", "Rol", "Supervisor", "Conectado", "Acciones"
        ])
        h = self.tbl_usuarios.horizontalHeader()
        h.setSectionResizeMode(0, QHeaderView.ResizeToContents)
        h.setSectionResizeMode(1, QHeaderView.Stretch)
        h.setSectionResizeMode(2, QHeaderView.ResizeToContents)
        h.setSectionResizeMode(3, QHeaderView.ResizeToContents)
        h.setSectionResizeMode(4, QHeaderView.ResizeToContents)
        h.setSectionResizeMode(5, QHeaderView.ResizeToContents)
        h.setSectionResizeMode(6, QHeaderView.ResizeToContents)
        self.tbl_usuarios.setAlternatingRowColors(True)
        ly.addWidget(self.tbl_usuarios)

        self.tabs.addTab(tab_usuarios, "👥 Usuarios y Roles")

    def apply_styles(self):
        self.setStyleSheet(
            """
            QMainWindow { background-color: #f5f5f5; }
            QHeaderView::section {
                background-color: #34495e; color: white; padding: 8px; border: none; font-weight: bold; font-size: 11px;
            }
            QTableWidget { background-color: white; alternate-background-color: #f8f9fa; font-size: 10px; }
            QTableWidget::item { padding: 5px; border-bottom: 1px solid #ecf0f1; color: #2c3e50; }
            QPushButton { background-color: #3498db; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 11px; font-weight: bold; }
            QPushButton:hover { background-color: #2980b9; }
            QLabel { color: #2c3e50; }
            QLineEdit { padding: 5px; border: 1px solid #bdc3c7; border-radius: 3px; background: white; }
            QComboBox { padding: 4px; border: 1px solid #bdc3c7; border-radius: 3px; background: white; }
            QCheckBox { padding: 2px; }
            """
        )

    def cargar_usuarios(self):
        try:
            filtro = self.input_busqueda.text().strip()
            usuarios = self.db.get_usuarios(filtro)
            # get_usuarios: id, nombre, usuario, rol, conectado, estado
            # Añadimos supervisor con otra consulta para no romper compatibilidad
            # Intentar obtener supervisor via SELECT adicional (evitar modificar método existente)
            self.tbl_usuarios.setRowCount(len(usuarios))
            for row, u in enumerate(usuarios):
                usuario_id = u[0]
                nombre = u[1]
                usuario_login = u[2]
                rol = u[3]
                conectado = u[4]

                self.tbl_usuarios.setItem(row, 0, QTableWidgetItem(str(usuario_id)))
                self.tbl_usuarios.setItem(row, 1, QTableWidgetItem(str(nombre)))
                self.tbl_usuarios.setItem(row, 2, QTableWidgetItem(str(usuario_login)))

                combo_rol = QComboBox()
                combo_rol.addItems(self.ROLES_PERMITIDOS)
                idx = combo_rol.findText(str(rol))
                if idx >= 0:
                    combo_rol.setCurrentIndex(idx)
                self.tbl_usuarios.setCellWidget(row, 3, combo_rol)

                chk_sup = QCheckBox("Es supervisor")
                # Buscar supervisor con consulta directa
                try:
                    import psycopg2
                    with self.db.conn.cursor() as cur:
                        cur.execute("SELECT supervisor FROM usuarios WHERE id=%s", (usuario_id,))
                        sup_row = cur.fetchone()
                        es_sup = 1 if (sup_row and sup_row[0]) else 0
                except Exception:
                    es_sup = 0
                chk_sup.setChecked(bool(es_sup))
                self.tbl_usuarios.setCellWidget(row, 4, chk_sup)

                combo_con = QComboBox()
                combo_con.addItems(self.CONECTADO_VALORES)
                idxc = combo_con.findText(str(conectado))
                if idxc >= 0:
                    combo_con.setCurrentIndex(idxc)
                self.tbl_usuarios.setCellWidget(row, 5, combo_con)

                btn_guardar = QPushButton("Guardar")
                btn_guardar.clicked.connect(lambda _, r=row: self._guardar_cambios_fila(r))
                self.tbl_usuarios.setCellWidget(row, 6, btn_guardar)
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error cargando usuarios: {e}")

    def _guardar_cambios_fila(self, row: int):
        try:
            id_item = self.tbl_usuarios.item(row, 0)
            user_item = self.tbl_usuarios.item(row, 2)
            if not id_item or not user_item:
                return
            usuario_id = int(id_item.text())
            usuario_login = user_item.text()

            combo_rol = self.tbl_usuarios.cellWidget(row, 3)
            chk_sup = self.tbl_usuarios.cellWidget(row, 4)
            combo_con = self.tbl_usuarios.cellWidget(row, 5)

            nuevo_rol = combo_rol.currentText() if combo_rol else None
            es_supervisor = 1 if (chk_sup and chk_sup.isChecked()) else 0
            conectado_val = combo_con.currentText() if combo_con else 'no'

            ok1 = self.db.actualizar_rol_usuario(usuario_id, nuevo_rol)
            ok2 = self.db.actualizar_supervisor_usuario(usuario_id, es_supervisor)
            ok3 = self.db.actualizar_conectado_usuario_por_id(usuario_id, conectado_val)

            if ok1 and ok2 and ok3:
                QMessageBox.information(self, "Éxito", "Cambios guardados correctamente")
            else:
                QMessageBox.warning(self, "Advertencia", "Algunos cambios no pudieron guardarse")
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error guardando cambios: {e}")


