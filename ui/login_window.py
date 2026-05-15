import json, os.path
from PySide6.QtWidgets import QApplication, QWidget, QVBoxLayout, QLabel, QLineEdit, QPushButton, QMessageBox
from services.db_client import DBClient
from ui.main_window import MainWindow

class LoginWindow(QWidget):
    def __init__(self):
        super().__init__()
        self.sesion_id = None  # Guardará el ID de la sesión activa
        self.db = DBClient()
        self.setWindowTitle("Inicio de sesión RMS")
        self.setGeometry(100, 100, 300, 150)

        # Cargamos la configuración al iniciar
        self.config_data = self.leer_config_json("config.json")

        layout = QVBoxLayout()

        self.user_input = QLineEdit()
        self.user_input.setPlaceholderText("Usuario")
        layout.addWidget(self.user_input)
        self.user_input.editingFinished.connect(self.mover_cursor1)

        self.pass_input = QLineEdit()
        self.pass_input.setPlaceholderText("Contraseña")
        self.pass_input.setEchoMode(QLineEdit.Password)
        layout.addWidget(self.pass_input)
        self.pass_input.editingFinished.connect(self.mover_cursor2)

        self.login_btn = QPushButton("Ingresar")
        self.login_btn.clicked.connect(self.verificar_login)
        layout.addWidget(self.login_btn)

        self.setLayout(layout)
           
    def verificar_login(self):
        usuario = self.user_input.text()
        clave = self.pass_input.text()
        
        # Verificar credenciales
        resultado = self.db.verificar_usuario(usuario, clave)
        
        if resultado == True:
            try:
                # Registrar sesión y obtener ID
                self.sesion_id = self.db.registra_sesion(
                    usuario=usuario,
                    terminal=self.config_data["terminal"],
                    local=self.config_data["local"]
                )
                
                # Crear y mostrar ventana principal
                self.main_window = MainWindow(
                    usuario=usuario,
                    db=self.db,
                    sesion_id=self.sesion_id
                )
                
                self.hide()  # Ocultar ventana de login
                self.main_window.show()
                
            except Exception as e:
                QMessageBox.critical(self, "Error", f"Error al iniciar sesión: {e}")
                # Opcional: Intentar cerrar sesión si falló
                if hasattr(self, 'sesion_id') and self.sesion_id:
                    self.db.cerrar_sesion(self.sesion_id)
        
        elif resultado == 'ya_conectado':
            QMessageBox.critical(self, "Sesión activa", "Este usuario ya está conectado en otra terminal.")
        else:
            QMessageBox.warning(self, "Error", "Usuario o clave incorrectos")

        
    def closeEvent(self, event):
        """Maneja el cierre de la ventana"""
        if self.sesion_id:
            try:
                self.db.cerrar_sesion(self.sesion_id)
            except Exception as e:
                QMessageBox.warning(self, "Advertencia", f"No se pudo registrar el cierre: {e}")
        event.accept()

    def mover_cursor1(self):
        self.pass_input.setFocus()

    def mover_cursor2(self):
        self.login_btn.setFocus()

    def leer_config_json(self, ruta_archivo):
        """Lee el archivo JSON y devuelve un diccionario con los datos."""
        try:
            # Verifica si la ruta existe
            if not os.path.exists(ruta_archivo):
                raise FileNotFoundError(f"Archivo no encontrado: {os.path.abspath(ruta_archivo)}")
                
            with open(ruta_archivo, 'r', encoding='utf-8') as file:
                datos = json.load(file)
            return datos
        except json.JSONDecodeError as e:
            QMessageBox.critical(self, "Error de JSON", f"El archivo no tiene formato JSON válido: {e}")
        except Exception as e:
            QMessageBox.critical(self, "Error", f"No se pudo leer el archivo JSON:\n{str(e)}")
        return None

    def registrar_sesion(self, usuario, clave):
        """Registra la sesión con los datos del JSON"""
        try:
            if self.config_data:
                terminal = self.config_data.get("terminal", "")
                local = self.config_data.get("local", "")
                serie_impresora=self.config_data.get("serie_impresora",""),
                tipo_conexion=self.config_data.get("tipo_conexion","")
                # Asumo que tu DBClient tiene un método registra_sesion que acepta estos parámetros
                self.db.registra_sesion(usuario, terminal, local)
        except Exception as e:
            QMessageBox.warning(self, "Advertencia", f"No se pudo registrar la sesión: {e}")