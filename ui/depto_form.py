from PySide6.QtWidgets import QDialog, QVBoxLayout, QLineEdit, QPushButton, QLabel, QMessageBox

class DeptoForm(QDialog):
    def __init__(self, titulo, nombre_inicial=""):
        super().__init__()
        self.setWindowTitle(titulo)
        self.setGeometry(300, 300, 300, 120)

        layout = QVBoxLayout()

        self.input = QLineEdit()
        self.input.setPlaceholderText("Ingrese nombre")
        self.input.setText(nombre_inicial)

        btn_guardar = QPushButton("Guardar")
        btn_guardar.clicked.connect(self.validar)

        layout.addWidget(QLabel("Nombre:"))
        layout.addWidget(self.input)
        layout.addWidget(btn_guardar)

        self.setLayout(layout)
        self.nombre_resultado = None

    def validar(self):
        nombre = self.input.text().strip()
        if not nombre:
            QMessageBox.warning(self, "Campo vacío", "Debe ingresar un nombre válido.")
        else:
            self.nombre_resultado = nombre
            self.accept()
