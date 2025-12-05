from PySide6.QtWidgets import (QWidget, QVBoxLayout, QTableWidget, QTableWidgetItem, QLabel,
                               QPushButton, QHBoxLayout, QLineEdit, QMessageBox, QGroupBox, 
                               QHeaderView, QDialog, QTabWidget, QComboBox, QDateTimeEdit,
                               QFormLayout, QDateEdit)
from PySide6.QtCore import Qt, QDate
from datetime import datetime, timedelta

class CierresAdmin(QWidget):
    def __init__(self, db, usuario_actual):
        super().__init__()
        self.db = db
        self.usuario_actual = usuario_actual
        self.setWindowTitle("💰 Administración de Cierres de Caja")
        self.setGeometry(200, 200, 1200, 700)
        
        # Aplicar estilos
        self.setStyleSheet(self._get_styles())

        layout = QVBoxLayout()
        layout.setSpacing(15)
        layout.setContentsMargins(20, 20, 20, 20)
        
        # Título
        title_label = QLabel("💰 Administración de Cierres de Caja")
        title_label.setStyleSheet("""
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
        
        # Filtros
        filtros_group = QGroupBox("🔍 Filtros de Búsqueda")
        filtros_group.setStyleSheet(self._get_group_style())
        
        filtros_layout = QHBoxLayout()
        
        # Fecha desde
        filtros_layout.addWidget(QLabel("Desde:"))
        self.fecha_desde = QDateEdit()
        self.fecha_desde.setDate(QDate.currentDate().addDays(-7))
        self.fecha_desde.setCalendarPopup(True)
        filtros_layout.addWidget(self.fecha_desde)
        
        # Fecha hasta
        filtros_layout.addWidget(QLabel("Hasta:"))
        self.fecha_hasta = QDateEdit()
        self.fecha_hasta.setDate(QDate.currentDate())
        self.fecha_hasta.setCalendarPopup(True)
        filtros_layout.addWidget(self.fecha_hasta)
        
        # Botón buscar
        self.btn_buscar = QPushButton("🔍 Buscar")
        self.btn_buscar.clicked.connect(self.cargar_cierres)
        self.btn_buscar.setStyleSheet(self._get_button_style("#3498db"))
        filtros_layout.addWidget(self.btn_buscar)
        
        # Botón actualizar
        self.btn_actualizar = QPushButton("🔄 Actualizar")
        self.btn_actualizar.clicked.connect(self.cargar_cierres)
        self.btn_actualizar.setStyleSheet(self._get_button_style("#27ae60"))
        filtros_layout.addWidget(self.btn_actualizar)
        
        filtros_group.setLayout(filtros_layout)

        # Tabla de cierres
        self.table = QTableWidget()
        self.table.setAlternatingRowColors(True)
        self.table.setSelectionBehavior(QTableWidget.SelectRows)
        self.table.setSelectionMode(QTableWidget.SingleSelection)
        self.table.verticalHeader().setVisible(False)
        self.table.setShowGrid(True)
        self.table.setSortingEnabled(True)
        
        # Configurar encabezados
        header = self.table.horizontalHeader()
        header.setStretchLastSection(False)
        header.setSectionResizeMode(QHeaderView.Interactive)
        
        # Configurar altura de filas
        self.table.verticalHeader().setDefaultSectionSize(35)
        self.table.verticalHeader().setMinimumSectionSize(30)

        # Botones de acción
        btn_layout = QHBoxLayout()
        btn_layout.setSpacing(10)
        
        self.btn_ver_detalle = QPushButton("👁️ Ver Detalle")
        self.btn_ver_detalle.clicked.connect(self.ver_detalle_cierre)
        self.btn_ver_detalle.setStyleSheet(self._get_button_style("#3498db"))
        
        self.btn_imprimir = QPushButton("🖨️ Imprimir")
        self.btn_imprimir.clicked.connect(self.imprimir_cierre)
        self.btn_imprimir.setStyleSheet(self._get_button_style("#9b59b6"))
        
        self.btn_exportar = QPushButton("📊 Exportar")
        self.btn_exportar.clicked.connect(self.exportar_cierres)
        self.btn_exportar.setStyleSheet(self._get_button_style("#27ae60"))
        
        btn_layout.addWidget(self.btn_ver_detalle)
        btn_layout.addWidget(self.btn_imprimir)
        btn_layout.addWidget(self.btn_exportar)
        btn_layout.addStretch()

        layout.addWidget(title_label)
        layout.addWidget(filtros_group)
        layout.addWidget(self.table)
        layout.addLayout(btn_layout)
        self.setLayout(layout)

        # Cargar datos iniciales
        self.cargar_cierres()

    def _get_styles(self):
        """Retorna los estilos CSS consistentes con la aplicación"""
        return """
        QWidget {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11px;
            background-color: #2c3e50;
        }
        
        QLineEdit, QDateEdit, QComboBox {
            padding: 8px 12px;
            border: 2px solid #34495e;
            border-radius: 6px;
            font-size: 11px;
            background-color: #ecf0f1;
            color: #2c3e50;
        }
        
        QLineEdit:focus, QDateEdit:focus, QComboBox:focus {
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
        
        QLabel {
            color: white;
            font-size: 11px;
            font-weight: bold;
        }
        """

    def _get_group_style(self):
        """Retorna el estilo para los grupos"""
        return """
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
        """

    def _get_button_style(self, color):
        """Retorna el estilo para los botones"""
        return f"""
        QPushButton {{
            background-color: {color};
            color: white;
            font-weight: bold;
            font-size: 11px;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
        }}
        QPushButton:hover {{
            background-color: {self._darken_color(color)};
        }}
        QPushButton:pressed {{
            background-color: {self._darken_color(color, 0.8)};
        }}
        """

    def _darken_color(self, color, factor=0.9):
        """Oscurece un color hexadecimal"""
        color = color.lstrip('#')
        r, g, b = tuple(int(color[i:i+2], 16) for i in (0, 2, 4))
        r, g, b = int(r * factor), int(g * factor), int(b * factor)
        return f"#{r:02x}{g:02x}{b:02x}"

    def cargar_cierres(self):
        """Carga la lista de cierres de caja en la tabla"""
        try:
            fecha_desde = self.fecha_desde.date().toPython()
            fecha_hasta = self.fecha_hasta.date().toPython()
            
            # Agregar tiempo para incluir todo el día
            fecha_hasta = datetime.combine(fecha_hasta, datetime.max.time())
            
            cierres = self.db.get_cierres_caja_por_periodo(fecha_desde, fecha_hasta)
            
            self.table.setRowCount(len(cierres))
            self.table.setColumnCount(12)
            self.table.setHorizontalHeaderLabels([
                "ID", "Tipo", "Cajero", "Fecha Inicio", "Fecha Fin", 
                "Ventas", "Ticket Prom.", "Efectivo", "Tarjeta", "Otros", "IGV", "Obs."
            ])

            # Configurar anchos específicos de columnas
            self.table.setColumnWidth(0, 50)   # ID
            self.table.setColumnWidth(1, 80)   # Tipo
            self.table.setColumnWidth(2, 150)  # Cajero
            self.table.setColumnWidth(3, 120)  # Fecha Inicio
            self.table.setColumnWidth(4, 120)  # Fecha Fin
            self.table.setColumnWidth(5, 80)   # Ventas
            self.table.setColumnWidth(6, 100)  # Ticket Prom.
            self.table.setColumnWidth(7, 100)  # Efectivo
            self.table.setColumnWidth(8, 100)  # Tarjeta
            self.table.setColumnWidth(9, 100)  # Otros
            self.table.setColumnWidth(10, 80)  # IGV
            self.table.setColumnWidth(11, 200) # Obs.

            for row_idx, cierre in enumerate(cierres):
                for col_idx, value in enumerate(cierre):
                    item = QTableWidgetItem(str(value) if value else "")
                    
                    # Alineación específica por columna
                    if col_idx in [0, 1, 5, 6, 7, 8, 9, 10]:  # Columnas numéricas - centradas
                        item.setTextAlignment(Qt.AlignCenter)
                    else:  # Texto - izquierda
                        item.setTextAlignment(Qt.AlignLeft | Qt.AlignVCenter)
                    
                    # Colorear según el tipo de cierre
                    if col_idx == 1:  # Columna Tipo
                        if str(value).upper() == 'X':
                            item.setBackground(Qt.yellow)
                            item.setForeground(Qt.black)
                        elif str(value).upper() == 'Z':
                            item.setBackground(Qt.green)
                            item.setForeground(Qt.white)
                    
                    # Formatear fechas
                    if col_idx in [3, 4] and value:  # Fechas
                        try:
                            if isinstance(value, str):
                                fecha = datetime.fromisoformat(value.replace('Z', '+00:00'))
                            else:
                                fecha = value
                            item.setText(fecha.strftime("%d/%m/%Y %H:%M"))
                        except:
                            pass
                    
                    # Formatear montos
                    if col_idx in [6, 7, 8, 9, 10] and value:  # Montos
                        try:
                            monto = float(value)
                            item.setText(f"S/. {monto:.2f}")
                        except:
                            pass
                    
                    self.table.setItem(row_idx, col_idx, item)
            
            # Ajustar altura de filas automáticamente
            self.table.resizeRowsToContents()
                    
        except Exception as e:
            QMessageBox.critical(self, "❌ Error", f"Error al cargar cierres: {str(e)}")

    def ver_detalle_cierre(self):
        """Muestra el detalle de un cierre seleccionado"""
        fila = self.table.currentRow()
        if fila < 0:
            QMessageBox.warning(self, "⚠️ Advertencia", "Seleccione un cierre para ver detalle")
            return
            
        try:
            cierre_id = int(self.table.item(fila, 0).text())
            cierre_data = self.table.item(fila, 0).text()  # Obtener datos de la fila
            
            # Crear diálogo de detalle
            dialog = CierreDetalleDialog(cierre_id, self.table, fila)
            dialog.exec()
            
        except Exception as e:
            QMessageBox.critical(self, "❌ Error", f"Error al obtener detalle: {str(e)}")

    def imprimir_cierre(self):
        """Imprime el cierre seleccionado"""
        fila = self.table.currentRow()
        if fila < 0:
            QMessageBox.warning(self, "⚠️ Advertencia", "Seleccione un cierre para imprimir")
            return
        
        QMessageBox.information(self, "🖨️ Imprimir", "Función de impresión en desarrollo")

    def exportar_cierres(self):
        """Exporta los cierres a Excel/CSV"""
        QMessageBox.information(self, "📊 Exportar", "Función de exportación en desarrollo")


class CierreDetalleDialog(QDialog):
    def __init__(self, cierre_id, table, row):
        super().__init__()
        self.cierre_id = cierre_id
        self.table = table
        self.row = row
        self.setWindowTitle(f"👁️ Detalle del Cierre #{cierre_id}")
        self.setGeometry(300, 300, 600, 400)
        
        self.setup_ui()
        self.cargar_datos()

    def setup_ui(self):
        layout = QVBoxLayout()
        layout.setSpacing(15)
        layout.setContentsMargins(20, 20, 20, 20)
        
        # Título
        title = QLabel(f"👁️ Detalle del Cierre #{self.cierre_id}")
        title.setStyleSheet("""
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
        
        # Información del cierre
        info_group = QGroupBox("📊 Información del Cierre")
        info_group.setStyleSheet("""
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
        
        info_layout = QFormLayout()
        info_layout.setLabelAlignment(Qt.AlignRight | Qt.AlignVCenter)
        
        self.lbl_tipo = QLabel()
        self.lbl_cajero = QLabel()
        self.lbl_fecha_inicio = QLabel()
        self.lbl_fecha_fin = QLabel()
        self.lbl_ventas = QLabel()
        self.lbl_ticket_prom = QLabel()
        self.lbl_efectivo = QLabel()
        self.lbl_tarjeta = QLabel()
        self.lbl_otros = QLabel()
        self.lbl_igv = QLabel()
        self.lbl_observaciones = QLabel()
        
        info_layout.addRow("Tipo:", self.lbl_tipo)
        info_layout.addRow("Cajero:", self.lbl_cajero)
        info_layout.addRow("Fecha Inicio:", self.lbl_fecha_inicio)
        info_layout.addRow("Fecha Fin:", self.lbl_fecha_fin)
        info_layout.addRow("Cantidad Ventas:", self.lbl_ventas)
        info_layout.addRow("Ticket Promedio:", self.lbl_ticket_prom)
        info_layout.addRow("Total Efectivo:", self.lbl_efectivo)
        info_layout.addRow("Total Tarjeta:", self.lbl_tarjeta)
        info_layout.addRow("Total Otros:", self.lbl_otros)
        info_layout.addRow("Total IGV:", self.lbl_igv)
        info_layout.addRow("Observaciones:", self.lbl_observaciones)
        
        info_group.setLayout(info_layout)
        
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
        layout.addWidget(info_group)
        layout.addWidget(btn_cerrar)
        self.setLayout(layout)

    def cargar_datos(self):
        """Carga los datos del cierre"""
        try:
            # Obtener datos de la tabla
            self.lbl_tipo.setText(self.table.item(self.row, 1).text())
            self.lbl_cajero.setText(self.table.item(self.row, 2).text())
            self.lbl_fecha_inicio.setText(self.table.item(self.row, 3).text())
            self.lbl_fecha_fin.setText(self.table.item(self.row, 4).text())
            self.lbl_ventas.setText(self.table.item(self.row, 5).text())
            self.lbl_ticket_prom.setText(self.table.item(self.row, 6).text())
            self.lbl_efectivo.setText(self.table.item(self.row, 7).text())
            self.lbl_tarjeta.setText(self.table.item(self.row, 8).text())
            self.lbl_otros.setText(self.table.item(self.row, 9).text())
            self.lbl_igv.setText(self.table.item(self.row, 10).text())
            self.lbl_observaciones.setText(self.table.item(self.row, 11).text() or "Sin observaciones")
            
        except Exception as e:
            QMessageBox.critical(self, "❌ Error", f"Error al cargar datos: {str(e)}")
