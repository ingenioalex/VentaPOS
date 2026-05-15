from PySide6.QtWidgets import (QWidget, QVBoxLayout, QTableWidget, QTableWidgetItem, QLabel,
                               QPushButton, QHBoxLayout, QLineEdit, QMessageBox, QGroupBox, 
                               QHeaderView, QDialog, QTabWidget, QFormLayout)
from PySide6.QtCore import Qt
from ui.cliente_form import ClienteForm

class ClientesAdmin(QWidget):
    def __init__(self, db):
        super().__init__()
        self.db = db
        self.setWindowTitle("👥 Gestión de Clientes")
        self.setGeometry(200, 200, 1000, 600)
        
        # Aplicar estilos
        self.setStyleSheet(self._get_styles())

        layout = QVBoxLayout()
        layout.setSpacing(15)
        layout.setContentsMargins(20, 20, 20, 20)
        
        # Título
        title_label = QLabel("👥 Gestión de Clientes del Sistema")
        title_label.setStyleSheet("""
            QLabel {
                font-size: 16px;
                font-weight: bold;
                color: white;
                padding: 15px;
                background-color: #1b2631;
                border: 2px solid #34495e;
                border-radius: 8px;
            }
        """)
        
        # Grupo de búsqueda
        search_group = QGroupBox("🔍 Búsqueda")
        search_group.setStyleSheet("""
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
        
        search_layout = QVBoxLayout()
        self.search_box = QLineEdit()
        self.search_box.setPlaceholderText("🔍 Buscar por nombre, documento o email...")
        self.search_box.textChanged.connect(self.load_clientes)
        search_layout.addWidget(self.search_box)
        search_group.setLayout(search_layout)

        # Tabla de clientes
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
        
        self.add_btn = QPushButton("➕ Agregar Cliente")
        self.edit_btn = QPushButton("✏️ Editar Cliente")
        self.del_btn = QPushButton("🗑️ Eliminar Cliente")
        self.view_btn = QPushButton("👁️ Ver Detalles")
        self.refresh_btn = QPushButton("🔄 Actualizar")

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
        
        self.view_btn.setStyleSheet("""
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
        
        self.refresh_btn.setStyleSheet("""
            QPushButton {
                background-color: #9b59b6;
                color: white;
                font-weight: bold;
                font-size: 11px;
                padding: 8px 16px;
                border: none;
                border-radius: 6px;
            }
            QPushButton:hover {
                background-color: #8e44ad;
            }
            QPushButton:pressed {
                background-color: #7d3c98;
            }
        """)

        self.add_btn.clicked.connect(self.agregar_cliente)
        self.edit_btn.clicked.connect(self.editar_cliente)
        self.del_btn.clicked.connect(self.eliminar_cliente)
        self.view_btn.clicked.connect(self.ver_detalles_cliente)
        self.refresh_btn.clicked.connect(self.load_clientes)

        btn_layout.addWidget(self.add_btn)
        btn_layout.addWidget(self.edit_btn)
        btn_layout.addWidget(self.view_btn)
        btn_layout.addWidget(self.del_btn)
        btn_layout.addStretch()
        btn_layout.addWidget(self.refresh_btn)

        layout.addWidget(title_label)
        layout.addWidget(search_group)
        layout.addWidget(self.table)
        layout.addLayout(btn_layout)
        self.setLayout(layout)

        self.load_clientes()

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
        
        QTableWidget {
            background-color: #ecf0f1;
            border: 2px solid #34495e;
            border-radius: 8px;
            gridline-color: #bdc3c7;
            selection-background-color: #3498db;
            selection-color: white;
            alternate-background-color: #d5dbdb;
            font-size: 11px;
        }
        
        QTableWidget::item {
            padding: 8px 12px;
            border-bottom: 1px solid #bdc3c7;
            color: #2c3e50;
            font-size: 11px;
            font-weight: 500;
        }
        
        QTableWidget::item:selected {
            background-color: #3498db;
            color: white;
            font-weight: bold;
        }
        
        QTableWidget::item:hover {
            background-color: #5dade2;
            color: white;
        }
        
        QHeaderView::section {
            background-color: #1b2631;
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

    def load_clientes(self):
        """Carga la lista de clientes en la tabla"""
        try:
            filtro = self.search_box.text()
            clientes = self.db.get_clientes(filtro)
            self.table.setRowCount(len(clientes))
            self.table.setColumnCount(8)
            self.table.setHorizontalHeaderLabels(["ID", "Tipo Doc", "Número Doc", "Nombre", "Dirección", "Teléfono", "Correo electrónico", "Registro"])

            # Configurar anchos específicos de columnas
            self.table.setColumnWidth(0, 50)   # ID - estrecho
            self.table.setColumnWidth(1, 80)   # Tipo Doc - medio
            self.table.setColumnWidth(2, 120)  # Número Doc - medio
            self.table.setColumnWidth(3, 200)  # Nombre - ancho
            self.table.setColumnWidth(4, 200)  # Dirección - ancho
            self.table.setColumnWidth(5, 120)  # Teléfono - medio
            self.table.setColumnWidth(6, 150)  # Email - medio
            self.table.setColumnWidth(7, 120)  # Registro - medio

            for row_idx, cliente in enumerate(clientes):
                for col_idx, value in enumerate(cliente):
                    item = QTableWidgetItem(str(value) if value else "")
                    
                    # Alineación específica por columna
                    if col_idx in [0, 1, 2, 5, 7]:  # ID, Tipo, Número, Teléfono, Registro - centrados
                        item.setTextAlignment(Qt.AlignCenter)
                    else:  # Nombre, Dirección, Email - izquierda
                        item.setTextAlignment(Qt.AlignLeft | Qt.AlignVCenter)
                    
                    # Colorear según el tipo de documento
                    if col_idx == 1:  # Columna Tipo Doc
                        if str(value).upper() == 'DNI':
                            item.setBackground(Qt.blue)
                            item.setForeground(Qt.white)
                        elif str(value).upper() == 'RUC':
                            item.setBackground(Qt.green)
                            item.setForeground(Qt.white)
                    
                    self.table.setItem(row_idx, col_idx, item)
            
            # Ajustar altura de filas automáticamente
            self.table.resizeRowsToContents()
                    
        except Exception as e:
            QMessageBox.critical(self, "❌ Error", f"Error al cargar clientes: {str(e)}")

    def agregar_cliente(self):
        """Abre el formulario para agregar un nuevo cliente"""
        try:
            form = ClienteForm(self.db, modo='agregar')
            if form.exec():
                self.load_clientes()
        except Exception as e:
            QMessageBox.critical(self, "❌ Error", f"Error al abrir formulario: {str(e)}")

    def editar_cliente(self):
        """Abre el formulario para editar el cliente seleccionado"""
        fila = self.table.currentRow()
        if fila < 0:
            QMessageBox.warning(self, "⚠️ Advertencia", "Seleccione un cliente para editar")
            return
            
        try:
            # Crear tupla con los datos en el orden esperado por ClienteForm
            datos = (
                int(self.table.item(fila, 0).text()),      # id
                self.table.item(fila, 1).text(),           # documento_tipo
                self.table.item(fila, 2).text(),           # documento_numero
                self.table.item(fila, 3).text(),           # nombre
                self.table.item(fila, 4).text(),           # direccion
                self.table.item(fila, 5).text(),           # telefono
                self.table.item(fila, 6).text(),           # email
                self.table.item(fila, 7).text()            # creado_en
            )
            form = ClienteForm(self.db, modo='editar', datos=datos)
            if form.exec():
                self.load_clientes()
        except Exception as e:
            QMessageBox.critical(self, "❌ Error", f"Error al editar cliente: {str(e)}")

    def eliminar_cliente(self):
        """Elimina el cliente seleccionado con confirmación"""
        fila = self.table.currentRow()
        if fila < 0:
            QMessageBox.warning(self, "⚠️ Advertencia", "Seleccione un cliente para eliminar")
            return
            
        try:
            cliente_id = int(self.table.item(fila, 0).text())
            nombre_cliente = self.table.item(fila, 3).text()
            
            confirm = QMessageBox.question(
                self, 
                "🗑️ Confirmar Eliminación", 
                f"¿Está seguro de eliminar el cliente '{nombre_cliente}'?\n\nEsta acción no se puede deshacer.",
                QMessageBox.Yes | QMessageBox.No,
                QMessageBox.No
            )
            
            if confirm == QMessageBox.Yes:
                if self.db.eliminar_cliente(cliente_id):
                    self.load_clientes()
                    QMessageBox.information(self, "✅ Éxito", f"Cliente '{nombre_cliente}' eliminado correctamente")
                else:
                    QMessageBox.warning(self, "⚠️ No se puede eliminar", 
                                      f"No se puede eliminar el cliente '{nombre_cliente}' porque tiene compras asociadas")
                
        except Exception as e:
            QMessageBox.critical(self, "❌ Error", f"Error al eliminar cliente: {str(e)}")

    def ver_detalles_cliente(self):
        """Muestra los detalles completos del cliente seleccionado"""
        fila = self.table.currentRow()
        if fila < 0:
            QMessageBox.warning(self, "⚠️ Advertencia", "Seleccione un cliente para ver detalles")
            return
            
        try:
            cliente_id = int(self.table.item(fila, 0).text())
            documento_numero = self.table.item(fila, 2).text()  # Obtener número de documento
            
            # Intentar obtener cliente por ID primero
            cliente_data = self.db.get_cliente_por_id(cliente_id)
            
            # Si no se encuentra por ID, intentar por documento
            if not cliente_data:
                cliente_data = self.db.get_cliente_por_documento(documento_numero)
            
            if not cliente_data:
                QMessageBox.warning(self, "⚠️ Error", "Cliente no encontrado")
                return
            
            # Crear diálogo de detalles
            dialog = ClienteDetallesDialog(cliente_data, self.db)
            dialog.exec()
            
        except Exception as e:
            QMessageBox.critical(self, "❌ Error", f"Error al obtener detalles: {str(e)}")


class ClienteDetallesDialog(QDialog):
    def __init__(self, cliente_data, db):
        super().__init__()
        self.db = db
        self.cliente_data = cliente_data
        self.setWindowTitle(f"👤 Detalles del Cliente: {cliente_data[3]}")
        self.setGeometry(300, 300, 800, 600)
        
        # Aplicar estilos al diálogo
        self.setStyleSheet("""
            QDialog {
                background-color: #2c3e50;
                color: white;
            }
            QLabel {
                color: white;
                font-size: 11px;
            }
        """)
        
        self.setup_ui()
        self.load_data()

    def setup_ui(self):
        layout = QVBoxLayout()
        layout.setSpacing(15)
        layout.setContentsMargins(20, 20, 20, 20)
        
        # Título
        title = QLabel(f"👤 {self.cliente_data[3]}")
        title.setStyleSheet("""
            QLabel {
                font-size: 18px;
                font-weight: bold;
                color: white;
                padding: 15px;
                background-color: #1b2631;
                border: 2px solid #34495e;
                border-radius: 8px;
            }
        """)
        
        # Pestañas
        tabs = QTabWidget()
        tabs.setStyleSheet("""
            QTabWidget::pane {
                border: 2px solid #34495e;
                border-radius: 8px;
                background-color: #2c3e50;
            }
            QTabBar::tab {
                background-color: #34495e;
                color: white;
                padding: 10px 20px;
                margin-right: 2px;
                border-top-left-radius: 8px;
                border-top-right-radius: 8px;
            }
            QTabBar::tab:selected {
                background-color: #2c3e50;
                color: white;
                font-weight: bold;
            }
            QTabBar::tab:hover {
                background-color: #5d6d7e;
            }
        """)
        
        # Pestaña de información personal
        info_tab = QWidget()
        info_layout = QVBoxLayout()
        
        # Información básica
        info_group = QGroupBox("Información Personal")
        info_group.setStyleSheet("""
            QGroupBox {
                font-weight: bold;
                font-size: 12px;
                color: white;
                border: 2px solid #34495e;
                border-radius: 8px;
                margin-top: 8px;
                padding-top: 8px;
                background-color: #2c3e50;
            }
            QGroupBox::title {
                subcontrol-origin: margin;
                left: 10px;
                padding: 0 8px 0 8px;
                background-color: #2c3e50;
                color: white;
                font-weight: bold;
            }
        """)
        
        info_form = QFormLayout()
        info_form.setLabelAlignment(Qt.AlignRight | Qt.AlignVCenter)
        
        self.lbl_documento = QLabel()
        self.lbl_documento.setStyleSheet("color: #ecf0f1; font-weight: bold; font-size: 12px;")
        self.lbl_nombre = QLabel()
        self.lbl_nombre.setStyleSheet("color: #ecf0f1; font-weight: bold; font-size: 12px;")
        self.lbl_direccion = QLabel()
        self.lbl_direccion.setStyleSheet("color: #ecf0f1; font-weight: bold; font-size: 12px;")
        self.lbl_telefono = QLabel()
        self.lbl_telefono.setStyleSheet("color: #ecf0f1; font-weight: bold; font-size: 12px;")
        self.lbl_email = QLabel()
        self.lbl_email.setStyleSheet("color: #ecf0f1; font-weight: bold; font-size: 12px;")
        self.lbl_registro = QLabel()
        self.lbl_registro.setStyleSheet("color: #ecf0f1; font-weight: bold; font-size: 12px;")
        
        info_form.addRow("Documento:", self.lbl_documento)
        info_form.addRow("Nombre:", self.lbl_nombre)
        info_form.addRow("Dirección:", self.lbl_direccion)
        info_form.addRow("Teléfono:", self.lbl_telefono)
        info_form.addRow("Correo electrónico:", self.lbl_email)
        info_form.addRow("Registrado:", self.lbl_registro)
        
        info_group.setLayout(info_form)
        info_layout.addWidget(info_group)
        
        # Estadísticas
        stats_group = QGroupBox("Estadísticas de Compras")
        stats_group.setStyleSheet(info_group.styleSheet())
        
        stats_layout = QVBoxLayout()
        self.lbl_total_compras = QLabel()
        self.lbl_total_compras.setStyleSheet("color: #ecf0f1; font-weight: bold; font-size: 12px; padding: 5px;")
        self.lbl_total_gastado = QLabel()
        self.lbl_total_gastado.setStyleSheet("color: #ecf0f1; font-weight: bold; font-size: 12px; padding: 5px;")
        self.lbl_promedio_compra = QLabel()
        self.lbl_promedio_compra.setStyleSheet("color: #ecf0f1; font-weight: bold; font-size: 12px; padding: 5px;")
        self.lbl_primera_compra = QLabel()
        self.lbl_primera_compra.setStyleSheet("color: #ecf0f1; font-weight: bold; font-size: 12px; padding: 5px;")
        self.lbl_ultima_compra = QLabel()
        self.lbl_ultima_compra.setStyleSheet("color: #ecf0f1; font-weight: bold; font-size: 12px; padding: 5px;")
        
        stats_layout.addWidget(self.lbl_total_compras)
        stats_layout.addWidget(self.lbl_total_gastado)
        stats_layout.addWidget(self.lbl_promedio_compra)
        stats_layout.addWidget(self.lbl_primera_compra)
        stats_layout.addWidget(self.lbl_ultima_compra)
        
        stats_group.setLayout(stats_layout)
        info_layout.addWidget(stats_group)
        
        info_tab.setLayout(info_layout)
        tabs.addTab(info_tab, "📋 Información")
        
        # Pestaña de historial de compras
        compras_tab = QWidget()
        compras_layout = QVBoxLayout()
        
        compras_group = QGroupBox("Historial de Compras")
        compras_group.setStyleSheet(info_group.styleSheet())
        
        self.compras_table = QTableWidget()
        self.compras_table.setAlternatingRowColors(True)
        self.compras_table.setSelectionBehavior(QTableWidget.SelectRows)
        self.compras_table.verticalHeader().setVisible(False)
        self.compras_table.setShowGrid(True)
        
        compras_layout.addWidget(self.compras_table)
        compras_group.setLayout(compras_layout)
        
        compras_tab.setLayout(compras_layout)
        tabs.addTab(compras_tab, "🛒 Compras")
        
        # Botón cerrar
        btn_cerrar = QPushButton("❌ Cerrar")
        btn_cerrar.setStyleSheet("""
            QPushButton {
                background-color: #e74c3c;
                color: white;
                font-weight: bold;
                font-size: 12px;
                padding: 10px 20px;
                border: none;
                border-radius: 6px;
            }
            QPushButton:hover {
                background-color: #c0392b;
            }
        """)
        btn_cerrar.clicked.connect(self.accept)
        
        layout.addWidget(title)
        layout.addWidget(tabs)
        layout.addWidget(btn_cerrar)
        self.setLayout(layout)

    def load_data(self):
        """Carga los datos del cliente"""
        # Obtener documento_numero primero
        documento_numero = self.cliente_data[2]  # documento_numero está en la posición 2
        
        # Información básica
        self.lbl_documento.setText(f"{self.cliente_data[1]} - {self.cliente_data[2]}")
        self.lbl_nombre.setText(self.cliente_data[3])
        self.lbl_direccion.setText(self.cliente_data[4] or "No especificada")
        self.lbl_telefono.setText(self.cliente_data[5] or "No especificado")
        self.lbl_email.setText(self.cliente_data[6] or "No especificado")
        self.lbl_registro.setText(str(self.cliente_data[7]))
        
        # Estadísticas - buscar por documento_numero
        stats = self.db.get_estadisticas_cliente_por_documento(documento_numero)
        if stats:
            self.lbl_total_compras.setText(f"📊 Total de compras: {stats[0]}")
            self.lbl_total_gastado.setText(f"💰 Total gastado: S/. {stats[1]:.2f}")
            self.lbl_promedio_compra.setText(f"📈 Promedio por compra: S/. {stats[2]:.2f}")
            self.lbl_primera_compra.setText(f"📅 Primera compra: {stats[3] or 'N/A'}")
            self.lbl_ultima_compra.setText(f"📅 Última compra: {stats[4] or 'N/A'}")
        
        # Historial de compras - buscar por documento_numero
        compras = self.db.get_historial_compras_cliente_por_documento(documento_numero)
        self.compras_table.setRowCount(len(compras))
        self.compras_table.setColumnCount(7)
        self.compras_table.setHorizontalHeaderLabels(["ID", "Tipo", "Serie", "Número", "Fecha", "Total", "Medio de pago", "Cajero"])
        
        for row_idx, compra in enumerate(compras):
            for col_idx, value in enumerate(compra):
                item = QTableWidgetItem(str(value) if value else "")
                item.setTextAlignment(Qt.AlignCenter)
                
                # Formatear fechas
                if col_idx == 4 and value:  # Columna de fecha
                    try:
                        if hasattr(value, 'strftime'):
                            item.setText(value.strftime("%d/%m/%Y %H:%M"))
                        else:
                            item.setText(str(value))
                    except:
                        item.setText(str(value))
                
                # Formatear montos
                elif col_idx == 5 and value:  # Columna de total
                    try:
                        monto = float(value)
                        item.setText(f"S/. {monto:.2f}")
                    except:
                        item.setText(str(value))
                
                self.compras_table.setItem(row_idx, col_idx, item)
        
        # Ajustar columnas
        self.compras_table.resizeColumnsToContents()
