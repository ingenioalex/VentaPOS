from PySide6.QtWidgets import (
    QDialog, QVBoxLayout, QHBoxLayout, QLabel, QLineEdit, QPushButton,
    QGroupBox, QGridLayout, QMessageBox, QSpinBox, QDoubleSpinBox,
    QFrame, QTableWidget, QTableWidgetItem, QHeaderView
)
from PySide6.QtCore import Qt, QDateTime
from PySide6.QtGui import QFont
import json
from datetime import datetime
from decimal import Decimal, ROUND_HALF_UP

class SupervisorDialog(QDialog):
    def __init__(self, parent=None):
        super().__init__(parent)
        self.setWindowTitle("Supervisor de Caja")
        self.setModal(True)
        self.setMinimumSize(600, 500)
        
        # Variables
        self.fondo_inicial = 0.0
        self.fondo_actual = 0.0
        self.efectivo_ingresado = 0.0
        self.tarjeta_ingresada = 0.0
        self.otros_ingresados = 0.0
        self.ventas_del_dia = []
        
        self.setup_ui()
        self.cargar_datos_caja()
        
    def setup_ui(self):
        layout = QVBoxLayout(self)
        
        # Título
        titulo = QLabel("SUPERVISOR DE CAJA")
        titulo.setFont(QFont("Arial", 16, QFont.Weight.Bold))
        titulo.setAlignment(Qt.AlignmentFlag.AlignCenter)
        layout.addWidget(titulo)
        
        # Información de caja
        info_group = QGroupBox("Información de Caja")
        info_layout = QGridLayout(info_group)
        
        info_layout.addWidget(QLabel("Cajero:"), 0, 0)
        self.lbl_cajero = QLabel("Usuario Actual")
        info_layout.addWidget(self.lbl_cajero, 0, 1)
        
        info_layout.addWidget(QLabel("Fecha:"), 1, 0)
        self.lbl_fecha = QLabel(datetime.now().strftime("%d/%m/%Y"))
        info_layout.addWidget(self.lbl_fecha, 1, 1)
        
        info_layout.addWidget(QLabel("Hora:"), 2, 0)
        self.lbl_hora = QLabel(datetime.now().strftime("%H:%M:%S"))
        info_layout.addWidget(self.lbl_hora, 2, 1)
        
        layout.addWidget(info_group)
        
        # Conteo de fondo
        fondo_group = QGroupBox("Conteo de Fondo")
        fondo_layout = QGridLayout(fondo_group)
        
        fondo_layout.addWidget(QLabel("Fondo Inicial:"), 0, 0)
        self.spin_fondo_inicial = QDoubleSpinBox()
        self.spin_fondo_inicial.setRange(0, 999999.99)
        self.spin_fondo_inicial.setDecimals(2)
        self.spin_fondo_inicial.setSuffix(" S/.")
        fondo_layout.addWidget(self.spin_fondo_inicial, 0, 1)
        
        fondo_layout.addWidget(QLabel("Fondo Actual:"), 1, 0)
        self.lbl_fondo_actual = QLabel("0.00 S/.")
        fondo_layout.addWidget(self.lbl_fondo_actual, 1, 1)
        
        fondo_layout.addWidget(QLabel("Diferencia:"), 2, 0)
        self.lbl_diferencia = QLabel("0.00 S/.")
        fondo_layout.addWidget(self.lbl_diferencia, 2, 1)
        
        layout.addWidget(fondo_group)
        
        # Resumen de ventas
        ventas_group = QGroupBox("Resumen de Ventas del Día")
        ventas_layout = QVBoxLayout(ventas_group)
        
        # Tabla de resumen
        self.tbl_resumen = QTableWidget(0, 4)
        self.tbl_resumen.setHorizontalHeaderLabels([
            "Hora", "Tipo", "Total", "Medio Pago"
        ])
        self.tbl_resumen.horizontalHeader().setSectionResizeMode(QHeaderView.Stretch)
        self.tbl_resumen.verticalHeader().setVisible(False)
        ventas_layout.addWidget(self.tbl_resumen)
        
        # Totales
        totales_layout = QHBoxLayout()
        totales_layout.addWidget(QLabel("Total Efectivo:"))
        self.lbl_total_efectivo = QLabel("0.00 S/.")
        totales_layout.addWidget(self.lbl_total_efectivo)
        
        totales_layout.addWidget(QLabel("Total Tarjeta:"))
        self.lbl_total_tarjeta = QLabel("0.00 S/.")
        totales_layout.addWidget(self.lbl_total_tarjeta)
        
        totales_layout.addWidget(QLabel("Total Otros:"))
        self.lbl_total_otros = QLabel("0.00 S/.")
        totales_layout.addWidget(self.lbl_total_otros)
        
        totales_layout.addWidget(QLabel("TOTAL:"))
        self.lbl_total_general = QLabel("0.00 S/.")
        totales_layout.addWidget(self.lbl_total_general)
        
        ventas_layout.addLayout(totales_layout)
        layout.addWidget(ventas_group)
        
        # Botones
        botones_layout = QHBoxLayout()
        
        self.btn_abrir_gaveta = QPushButton("Abrir Gaveta")
        self.btn_abrir_gaveta.clicked.connect(self.abrir_gaveta)
        botones_layout.addWidget(self.btn_abrir_gaveta)
        
        self.btn_imprimir_ticket = QPushButton("Imprimir Ticket")
        self.btn_imprimir_ticket.clicked.connect(self.imprimir_ticket_conteo)
        botones_layout.addWidget(self.btn_imprimir_ticket)
        
        self.btn_cerrar_caja = QPushButton("Cerrar Caja")
        self.btn_cerrar_caja.clicked.connect(self.cerrar_caja)
        botones_layout.addWidget(self.btn_cerrar_caja)
        
        self.btn_cerrar = QPushButton("Cerrar")
        self.btn_cerrar.clicked.connect(self.close)
        botones_layout.addWidget(self.btn_cerrar)
        
        layout.addLayout(botones_layout)
        
        # Conectar eventos
        self.spin_fondo_inicial.valueChanged.connect(self.calcular_diferencia)
        
    def cargar_datos_caja(self):
        """Carga los datos de caja del día"""
        try:
            # Aquí cargarías desde la base de datos
            # Por ahora usamos datos de ejemplo
            self.ventas_del_dia = [
                {"hora": "09:15", "tipo": "B", "total": 45.80, "medio": "Efectivo"},
                {"hora": "10:30", "tipo": "F", "total": 128.50, "medio": "Tarjeta"},
                {"hora": "11:45", "tipo": "B", "total": 67.20, "medio": "Mixto"},
            ]
            
            self.actualizar_tabla_resumen()
            self.calcular_totales()
            
        except Exception as e:
            QMessageBox.warning(self, "Error", f"Error al cargar datos: {str(e)}")
    
    def actualizar_tabla_resumen(self):
        """Actualiza la tabla de resumen de ventas"""
        self.tbl_resumen.setRowCount(0)
        
        for venta in self.ventas_del_dia:
            row = self.tbl_resumen.rowCount()
            self.tbl_resumen.insertRow(row)
            
            self.tbl_resumen.setItem(row, 0, QTableWidgetItem(venta["hora"]))
            self.tbl_resumen.setItem(row, 1, QTableWidgetItem(venta["tipo"]))
            self.tbl_resumen.setItem(row, 2, QTableWidgetItem(f"{venta['total']:.2f}"))
            self.tbl_resumen.setItem(row, 3, QTableWidgetItem(venta["medio"]))
    
    def calcular_totales(self):
        """Calcula los totales por medio de pago"""
        total_efectivo = 0.0
        total_tarjeta = 0.0
        total_otros = 0.0
        
        for venta in self.ventas_del_dia:
            if venta["medio"] == "Efectivo":
                total_efectivo += venta["total"]
            elif venta["medio"] == "Tarjeta":
                total_tarjeta += venta["total"]
            else:
                total_otros += venta["total"]
        
        self.lbl_total_efectivo.setText(f"{total_efectivo:.2f} S/.")
        self.lbl_total_tarjeta.setText(f"{total_tarjeta:.2f} S/.")
        self.lbl_total_otros.setText(f"{total_otros:.2f} S/.")
        self.lbl_total_general.setText(f"{total_efectivo + total_tarjeta + total_otros:.2f} S/.")
        
        # Actualizar fondo actual
        self.fondo_actual = self.fondo_inicial + total_efectivo
        self.lbl_fondo_actual.setText(f"{self.fondo_actual:.2f} S/.")
        
        self.calcular_diferencia()
    
    def calcular_diferencia(self):
        """Calcula la diferencia entre fondo actual e inicial"""
        self.fondo_inicial = self.spin_fondo_inicial.value()
        diferencia = self.fondo_actual - self.fondo_inicial
        
        self.lbl_diferencia.setText(f"{diferencia:.2f} S/.")
        
        # Cambiar color según si hay diferencia
        if abs(diferencia) > 0.01:
            self.lbl_diferencia.setStyleSheet("color: red; font-weight: bold;")
        else:
            self.lbl_diferencia.setStyleSheet("color: green; font-weight: bold;")
    
    def abrir_gaveta(self):
        """Abre la gaveta de dinero"""
        try:
            # Aquí iría el código para abrir la gaveta física
            QMessageBox.information(self, "Gaveta", "Gaveta abierta. Por favor cuente el fondo.")
            
            # Simular conteo
            self.simular_conteo_fondo()
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error al abrir gaveta: {str(e)}")
    
    def simular_conteo_fondo(self):
        """Simula el conteo de fondo"""
        from PySide6.QtWidgets import QInputDialog
        
        valor, ok = QInputDialog.getDouble(
            self, 
            "Conteo de Fondo", 
            "Ingrese el monto contado en la gaveta:",
            self.fondo_actual, 
            0, 
            999999.99, 
            2
        )
        
        if ok:
            self.fondo_actual = valor
            self.lbl_fondo_actual.setText(f"{valor:.2f} S/.")
            self.calcular_diferencia()
    
    def imprimir_ticket_conteo(self):
        """Imprime el ticket de conteo"""
        try:
            ticket = self.generar_ticket_conteo()
            
            # Aquí iría el código para imprimir
            # Por ahora mostramos en un diálogo
            QMessageBox.information(self, "Ticket de Conteo", f"Ticket generado:\n\n{ticket}")
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error al imprimir ticket: {str(e)}")
    
    def generar_ticket_conteo(self):
        """Genera el contenido del ticket de conteo"""
        ticket = f"""
        ========================================
                    TICKET DE CONTEO
        ========================================
        Fecha: {datetime.now().strftime('%d/%m/%Y')}
        Hora: {datetime.now().strftime('%H:%M:%S')}
        Cajero: {self.lbl_cajero.text()}
        
        FONDO INICIAL: S/. {self.fondo_inicial:.2f}
        FONDO ACTUAL:  S/. {self.fondo_actual:.2f}
        DIFERENCIA:    S/. {self.fondo_actual - self.fondo_inicial:.2f}
        
        RESUMEN DEL DÍA:
        - Efectivo: S/. {self.lbl_total_efectivo.text().replace(' S/.', '')}
        - Tarjeta:  S/. {self.lbl_total_tarjeta.text().replace(' S/.', '')}
        - Otros:    S/. {self.lbl_total_otros.text().replace(' S/.', '')}
        - TOTAL:    S/. {self.lbl_total_general.text().replace(' S/.', '')}
        
        ========================================
        """
        return ticket
    
    def cerrar_caja(self):
        """Cierra la caja y genera reporte final"""
        try:
            reply = QMessageBox.question(
                self,
                "Cerrar Caja",
                "¿Está seguro que desea cerrar la caja?\nEsto generará un reporte final.",
                QMessageBox.StandardButton.Yes | QMessageBox.StandardButton.No,
                QMessageBox.StandardButton.No
            )
            
            if reply == QMessageBox.StandardButton.Yes:
                # Generar reporte de cierre
                reporte = self.generar_reporte_cierre()
                
                QMessageBox.information(self, "Caja Cerrada", 
                    f"Caja cerrada correctamente.\n\nReporte generado:\n{reporte}")
                
                self.close()
                
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error al cerrar caja: {str(e)}")
    
    def generar_reporte_cierre(self):
        """Genera el reporte de cierre de caja"""
        return f"""
        REPORTE DE CIERRE DE CAJA
        =========================
        Fecha: {datetime.now().strftime('%d/%m/%Y')}
        Hora: {datetime.now().strftime('%H:%M:%S')}
        Cajero: {self.lbl_cajero.text()}
        
        FONDO INICIAL: S/. {self.fondo_inicial:.2f}
        FONDO FINAL:   S/. {self.fondo_actual:.2f}
        DIFERENCIA:    S/. {self.fondo_actual - self.fondo_inicial:.2f}
        
        VENTAS DEL DÍA: {len(self.ventas_del_dia)}
        TOTAL VENTAS:   S/. {self.lbl_total_general.text().replace(' S/.', '')}
        """

