from PySide6.QtWidgets import QWidget, QVBoxLayout, QHBoxLayout, QLabel, QPushButton, QListWidget, QMessageBox, QLineEdit
from ui.producto_form import ProductoForm

class DeptoAdmin(QWidget):
    def __init__(self, db, usuario_actual=None):
        super().__init__()
        self.db = db
        self.usuario_actual = usuario_actual
        self.setWindowTitle("Gestión de Categorías")
        self.setGeometry(200, 200, 750, 600)

        self.layout = QVBoxLayout()

        self.input_departamento = QLineEdit()
        self.input_departamento.setPlaceholderText("Nombre de departamento")
        self.lista_departamento = QListWidget()
        self.lista_departamento.itemSelectionChanged.connect(self.cargar_subdepartamentos)

        self.input_subdepto = QLineEdit()
        self.input_subdepto.setPlaceholderText("Nombre de subdepartamento")
        self.lista_subdepto = QListWidget()
        self.lista_subdepto.itemSelectionChanged.connect(self.cargar_clases)

        self.input_clase = QLineEdit()
        self.input_clase.setPlaceholderText("Nombre de clase")
        self.lista_clase = QListWidget()

        self.btn_add_depto = QPushButton("+ Depto")
        self.btn_edit_depto = QPushButton("✎ Depto")
        self.btn_del_depto = QPushButton("🗑 Depto")

        self.btn_add_sub = QPushButton("+ Subdepto")
        self.btn_edit_sub = QPushButton("✎ Subdepto")
        self.btn_del_sub = QPushButton("🗑 Subdepto")

        self.btn_add_clase = QPushButton("+ Clase")
        self.btn_edit_clase = QPushButton("✎ Clase")
        self.btn_del_clase = QPushButton("🗑 Clase")
        self.btn_crear_producto = QPushButton("🛍️ Crear Producto")

        self.btn_add_depto.clicked.connect(self.agregar_departamento)
        self.btn_edit_depto.clicked.connect(self.editar_departamento)
        self.btn_del_depto.clicked.connect(self.eliminar_departamento)

        self.btn_add_sub.clicked.connect(self.agregar_subdepto)
        self.btn_edit_sub.clicked.connect(self.editar_subdepto)
        self.btn_del_sub.clicked.connect(self.eliminar_subdepto)

        self.btn_add_clase.clicked.connect(self.agregar_clase)
        self.btn_edit_clase.clicked.connect(self.editar_clase)
        self.btn_del_clase.clicked.connect(self.eliminar_clase)
        self.btn_crear_producto.clicked.connect(self.crear_producto)

        layout_d = QVBoxLayout()
        layout_d.addWidget(QLabel("Departamentos"))
        layout_d.addWidget(self.lista_departamento)
        layout_d.addWidget(self.input_departamento)
        layout_d.addWidget(self.btn_add_depto)
        layout_d.addWidget(self.btn_edit_depto)
        layout_d.addWidget(self.btn_del_depto)

        layout_s = QVBoxLayout()
        layout_s.addWidget(QLabel("Subdepartamentos"))
        layout_s.addWidget(self.lista_subdepto)
        layout_s.addWidget(self.input_subdepto)
        layout_s.addWidget(self.btn_add_sub)
        layout_s.addWidget(self.btn_edit_sub)
        layout_s.addWidget(self.btn_del_sub)

        layout_c = QVBoxLayout()
        layout_c.addWidget(QLabel("Clases"))
        layout_c.addWidget(self.lista_clase)
        layout_c.addWidget(self.input_clase)
        layout_c.addWidget(self.btn_add_clase)
        layout_c.addWidget(self.btn_edit_clase)
        layout_c.addWidget(self.btn_del_clase)
        layout_c.addWidget(self.btn_crear_producto)

        h_layout = QHBoxLayout()
        h_layout.addLayout(layout_d)
        h_layout.addLayout(layout_s)
        h_layout.addLayout(layout_c)

        self.layout.addLayout(h_layout)
        self.setLayout(self.layout)
        self.cargar_departamentos()

    def cargar_departamentos(self):
        self.lista_departamento.clear()
        self.departamentos = self.db.get_departamentos()
        for d in self.departamentos:
            self.lista_departamento.addItem(f"{d[0]} - {d[1]}")

    def cargar_subdepartamentos(self):
        self.lista_subdepto.clear()
        self.lista_clase.clear()
        idx = self.lista_departamento.currentRow()
        if idx >= 0:
            departamento_id = self.departamentos[idx][0]
            self.subdepartamentos = self.db.get_subdepartamentos(departamento_id)
            for s in self.subdepartamentos:
                self.lista_subdepto.addItem(f"{s[0]} - {s[1]}")

    def cargar_clases(self):
        self.lista_clase.clear()
        idx = self.lista_subdepto.currentRow()
        if idx >= 0:
            sub_id = self.subdepartamentos[idx][0]
            self.clases = self.db.get_clases(sub_id)
            for c in self.clases:
                self.lista_clase.addItem(f"{c[0]} - {c[1]}")

    def agregar_departamento(self):
        nombre = self.input_departamento.text().strip()
        if not nombre:
            QMessageBox.warning(self, "Campo vacío", "El nombre del departamento no puede estar vacío.")
            return
        self.db.agregar_departamento(nombre)
        self.input_departamento.clear()
        self.cargar_departamentos()

    def editar_departamento(self):
        idx = self.lista_departamento.currentRow()
        if idx >= 0:
            id_ = self.departamentos[idx][0]
            nombre = self.input_departamento.text().strip()
            if not nombre:
                QMessageBox.warning(self, "Campo vacío", "El nombre del departamento no puede estar vacío.")
                return
            self.db.editar_departamento(id_, nombre)
            self.input_departamento.clear()
            self.cargar_departamentos()

    def eliminar_departamento(self):
        idx = self.lista_departamento.currentRow()
        if idx >= 0:
            id_ = self.departamentos[idx][0]
            confirm = QMessageBox.question(self, "Confirmar", "¿Eliminar departamento?")
            if confirm == QMessageBox.Yes:
                if self.db.eliminar_departamento(id_):
                    self.cargar_departamentos()
                else:
                    QMessageBox.warning(self, "Error", "No se puede eliminar: hay productos relacionados.")

    def agregar_subdepto(self):
        idx = self.lista_departamento.currentRow()
        if idx >= 0:
            nombre = self.input_subdepto.text().strip()
            if not nombre:
                QMessageBox.warning(self, "Campo vacío", "El nombre del subdepartamento no puede estar vacío.")
                return
            self.db.agregar_subdepartamento(self.departamentos[idx][0], nombre)
            self.input_subdepto.clear()
            self.cargar_subdepartamentos()

    def editar_subdepto(self):
        idx = self.lista_subdepto.currentRow()
        if idx >= 0:
            id_ = self.subdepartamentos[idx][0]
            nombre = self.input_subdepto.text().strip()
            if not nombre:
                QMessageBox.warning(self, "Campo vacío", "El nombre del subdepartamento no puede estar vacío.")
                return
            self.db.editar_subdepartamento(id_, nombre)
            self.input_subdepto.clear()
            self.cargar_subdepartamentos()

    def eliminar_subdepto(self):
        idx = self.lista_subdepto.currentRow()
        if idx >= 0:
            id_ = self.subdepartamentos[idx][0]
            self.db.eliminar_subdepartamento(id_)
            self.cargar_subdepartamentos()

    def agregar_clase(self):
        idx = self.lista_subdepto.currentRow()
        if idx >= 0:
            nombre = self.input_clase.text().strip()
            if not nombre:
                QMessageBox.warning(self, "Campo vacío", "El nombre de la clase no puede estar vacío.")
                return
            self.db.agregar_clase(self.subdepartamentos[idx][0], nombre)
            self.input_clase.clear()
            self.cargar_clases()

    def editar_clase(self):
        idx = self.lista_clase.currentRow()
        if idx >= 0:
            id_ = self.clases[idx][0]
            nombre = self.input_clase.text().strip()
            if not nombre:
                QMessageBox.warning(self, "Campo vacío", "El nombre de la clase no puede estar vacío.")
                return
            self.db.editar_clase(id_, nombre)
            self.input_clase.clear()
            self.cargar_clases()

    def eliminar_clase(self):
        idx = self.lista_clase.currentRow()
        if idx >= 0:
            id_ = self.clases[idx][0]
            self.db.eliminar_clase(id_)
            self.cargar_clases()

    def crear_producto(self):
        """Abre el formulario de producto con la clase seleccionada"""
        # Verificar que hay una clase seleccionada
        idx = self.lista_clase.currentRow()
        if idx < 0:
            QMessageBox.warning(self, "Selección requerida", "Debe seleccionar una clase para crear un producto.")
            return
        
        # Obtener los datos de la clase seleccionada
        clase_id = self.clases[idx][0]
        clase_nombre = self.clases[idx][1]
        
        # Obtener también el departamento y subdepartamento para contexto completo
        depto_idx = self.lista_departamento.currentRow()
        subdepto_idx = self.lista_subdepto.currentRow()
        
        if depto_idx >= 0 and subdepto_idx >= 0:
            depto_nombre = self.departamentos[depto_idx][1]
            subdepto_nombre = self.subdepartamentos[subdepto_idx][1]
            depto_id = self.departamentos[depto_idx][0]
            subdepto_id = self.subdepartamentos[subdepto_idx][0]
            
            # Crear el formulario de producto
            producto_form = ProductoForm(self.db, self.usuario_actual)
            
            # Pre-seleccionar la clase en el formulario
            producto_form.preseleccionar_clase(depto_id, subdepto_id, clase_id)
            
            # Mostrar el formulario
            producto_form.show()
            
            # Mostrar información de contexto
            QMessageBox.information(
                self, 
                "Crear Producto", 
                f"Formulario de producto abierto.\n"
                f"Categoría pre-seleccionada: {depto_nombre} > {subdepto_nombre} > {clase_nombre}\n"
                f"Ya puede proceder a llenar los datos del producto."
            )
        else:
            QMessageBox.warning(self, "Error", "No se pudo obtener el contexto completo de la categoría.")