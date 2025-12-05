from PySide6.QtWidgets import (QDialog, QVBoxLayout, QHBoxLayout, QLabel, QLineEdit, QPushButton, 
                               QGroupBox, QFormLayout, QTextEdit, QMessageBox, QTableWidget, 
                               QTableWidgetItem, QHeaderView, QComboBox, QDateTimeEdit, QSpinBox)
from PySide6.QtCore import Qt, QDateTime, QTimer
from PySide6.QtGui import QFont
from datetime import datetime, timedelta

class CierreCajaForm(QDialog):
    def __init__(self, db, usuario_actual, usuario_id):
        super().__init__()
        self.db = db
        self.usuario_actual = usuario_actual
        self.usuario_id = usuario_id
        self.setWindowTitle("💰 Cierre de Caja")
        self.setGeometry(300, 300, 800, 600)
        
        # Aplicar estilos
        self.setStyleSheet(self._get_styles())
        
        self.setup_ui()
        self.cargar_datos_caja()
        
        # Timer para actualizar la hora cada segundo
        self.timer = QTimer()
        self.timer.timeout.connect(self.actualizar_hora)
        self.timer.start(1000)

    def setup_ui(self):
        layout = QVBoxLayout()
        layout.setSpacing(15)
        layout.setContentsMargins(20, 20, 20, 20)
        
        # Título
        title_label = QLabel("💰 Cierre de Caja")
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
        
        # Información del cajero
        info_group = QGroupBox("👤 Información del Cajero")
        info_group.setStyleSheet(self._get_group_style())
        
        info_layout = QFormLayout()
        self.lbl_cajero = QLabel(self.usuario_actual)
        self.lbl_cajero.setStyleSheet("font-weight: bold; color: white; font-size: 14px;")
        self.lbl_hora_actual = QLabel()
        self.lbl_hora_actual.setStyleSheet("font-weight: bold; color: #3498db; font-size: 14px;")
        
        info_layout.addRow("Cajero:", self.lbl_cajero)
        info_layout.addRow("Hora Actual:", self.lbl_hora_actual)
        info_group.setLayout(info_layout)
        
        # Resumen de caja actual
        resumen_group = QGroupBox("📊 Resumen de Caja Actual")
        resumen_group.setStyleSheet(self._get_group_style())
        
        resumen_layout = QFormLayout()
        self.lbl_ultimo_cierre = QLabel("--")
        self.lbl_cantidad_ventas = QLabel("0")
        self.lbl_total_vendido = QLabel("S/. 0.00")
        self.lbl_ticket_promedio = QLabel("S/. 0.00")
        self.lbl_total_efectivo = QLabel("S/. 0.00")
        self.lbl_total_tarjeta = QLabel("S/. 0.00")
        self.lbl_total_otros = QLabel("S/. 0.00")
        self.lbl_total_igv = QLabel("S/. 0.00")
        
        resumen_layout.addRow("Último Cierre:", self.lbl_ultimo_cierre)
        resumen_layout.addRow("Cantidad de Ventas:", self.lbl_cantidad_ventas)
        resumen_layout.addRow("Total Vendido:", self.lbl_total_vendido)
        resumen_layout.addRow("Ticket Promedio:", self.lbl_ticket_promedio)
        resumen_layout.addRow("Total Efectivo:", self.lbl_total_efectivo)
        resumen_layout.addRow("Total Tarjeta:", self.lbl_total_tarjeta)
        resumen_layout.addRow("Total Otros:", self.lbl_total_otros)
        resumen_layout.addRow("Total IGV:", self.lbl_total_igv)
        
        resumen_group.setLayout(resumen_layout)
        
        # Configuración del cierre
        config_group = QGroupBox("⚙️ Configuración del Cierre")
        config_group.setStyleSheet(self._get_group_style())
        
        config_layout = QFormLayout()
        self.tipo_cierre = QComboBox()
        self.tipo_cierre.addItems(['X - Cierre Parcial', 'Z - Cierre Definitivo'])
        self.tipo_cierre.setCurrentText('X - Cierre Parcial')
        
        self.observaciones = QTextEdit()
        self.observaciones.setPlaceholderText("Observaciones del cierre (opcional)")
        self.observaciones.setMaximumHeight(80)
        
        config_layout.addRow("Tipo de Cierre:", self.tipo_cierre)
        config_layout.addRow("Observaciones:", self.observaciones)
        config_group.setLayout(config_layout)
        
        # Botones
        btn_layout = QHBoxLayout()
        
        self.btn_actualizar = QPushButton("🔄 Actualizar Datos")
        self.btn_actualizar.clicked.connect(self.cargar_datos_caja)
        self.btn_actualizar.setStyleSheet(self._get_button_style("#3498db"))
        
        self.btn_cierre_parcial = QPushButton("📊 Cierre Parcial (X)")
        self.btn_cierre_parcial.clicked.connect(self.realizar_cierre_parcial)
        self.btn_cierre_parcial.setStyleSheet(self._get_button_style("#f39c12"))
        
        self.btn_cierre_definitivo = QPushButton("🏁 Cierre Definitivo (Z)")
        self.btn_cierre_definitivo.clicked.connect(self.realizar_cierre_definitivo)
        self.btn_cierre_definitivo.setStyleSheet(self._get_button_style("#e74c3c"))
        
        self.btn_cancelar = QPushButton("❌ Cancelar")
        self.btn_cancelar.clicked.connect(self.reject)
        self.btn_cancelar.setStyleSheet(self._get_button_style("#95a5a6"))
        
        btn_layout.addWidget(self.btn_actualizar)
        btn_layout.addWidget(self.btn_cierre_parcial)
        btn_layout.addWidget(self.btn_cierre_definitivo)
        btn_layout.addWidget(self.btn_cancelar)
        
        layout.addWidget(title_label)
        layout.addWidget(info_group)
        layout.addWidget(resumen_group)
        layout.addWidget(config_group)
        layout.addLayout(btn_layout)
        self.setLayout(layout)

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
        
        QTextEdit {
            padding: 8px 12px;
            border: 2px solid #34495e;
            border-radius: 6px;
            font-size: 11px;
            background-color: #ecf0f1;
            color: #2c3e50;
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
            font-size: 12px;
            padding: 10px 15px;
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

    def actualizar_hora(self):
        """Actualiza la hora actual en el label"""
        hora_actual = datetime.now().strftime("%H:%M:%S")
        self.lbl_hora_actual.setText(hora_actual)

    def cargar_datos_caja(self):
        """Carga los datos actuales de la caja"""
        try:
            resumen = self.db.get_resumen_caja_actual(self.usuario_id)
            
            # Actualizar información del último cierre
            if resumen['ultimo_cierre']:
                ultimo_cierre = resumen['ultimo_cierre']
                fecha_ultimo = ultimo_cierre[2].strftime("%d/%m/%Y %H:%M")
                tipo_ultimo = "Parcial (X)" if ultimo_cierre[1] == 'X' else "Definitivo (Z)"
                self.lbl_ultimo_cierre.setText(f"{fecha_ultimo} - {tipo_ultimo}")
            else:
                self.lbl_ultimo_cierre.setText("Sin cierres previos")
            
            # Actualizar resumen de ventas
            if resumen['ventas_desde_ultimo_cierre']:
                ventas = resumen['ventas_desde_ultimo_cierre']
                self.lbl_cantidad_ventas.setText(str(ventas[0]))
                self.lbl_total_vendido.setText(f"S/. {ventas[1]:.2f}")
                self.lbl_ticket_promedio.setText(f"S/. {ventas[2]:.2f}")
                self.lbl_total_efectivo.setText(f"S/. {ventas[3]:.2f}")
                self.lbl_total_tarjeta.setText(f"S/. {ventas[4]:.2f}")
                self.lbl_total_otros.setText(f"S/. {ventas[5]:.2f}")
                self.lbl_total_igv.setText(f"S/. {ventas[6]:.2f}")
            else:
                self.lbl_cantidad_ventas.setText("0")
                self.lbl_total_vendido.setText("S/. 0.00")
                self.lbl_ticket_promedio.setText("S/. 0.00")
                self.lbl_total_efectivo.setText("S/. 0.00")
                self.lbl_total_tarjeta.setText("S/. 0.00")
                self.lbl_total_otros.setText("S/. 0.00")
                self.lbl_total_igv.setText("S/. 0.00")
                
        except Exception as e:
            QMessageBox.critical(self, "❌ Error", f"Error al cargar datos de caja: {str(e)}")

    def realizar_cierre_parcial(self):
        """Realiza un cierre parcial (X)"""
        self.realizar_cierre('X')

    def realizar_cierre_definitivo(self):
        """Realiza un cierre definitivo (Z)"""
        self.realizar_cierre('Z')

    def realizar_cierre(self, tipo_cierre):
        """Realiza el cierre de caja"""
        try:
            # Obtener datos actuales
            resumen = self.db.get_resumen_caja_actual(self.usuario_id)
            
            if not resumen['ventas_desde_ultimo_cierre'] or resumen['ventas_desde_ultimo_cierre'][0] == 0:
                QMessageBox.warning(self, "⚠️ Advertencia", 
                                  "No hay ventas para cerrar en este período.")
                return
            
            ventas = resumen['ventas_desde_ultimo_cierre']
            
            # Confirmar cierre
            tipo_texto = "Parcial (X)" if tipo_cierre == 'X' else "Definitivo (Z)"
            confirm = QMessageBox.question(
                self,
                f"💰 Confirmar Cierre {tipo_texto}",
                f"¿Está seguro de realizar el cierre {tipo_texto}?\n\n"
                f"📊 Ventas: {ventas[0]}\n"
                f"💰 Total: S/. {ventas[1]:.2f}\n"
                f"💵 Efectivo: S/. {ventas[3]:.2f}\n"
                f"💳 Tarjeta: S/. {ventas[4]:.2f}\n"
                f"🔢 Otros: S/. {ventas[5]:.2f}",
                QMessageBox.Yes | QMessageBox.No,
                QMessageBox.No
            )
            
            if confirm == QMessageBox.Yes:
                # Obtener fechas
                fecha_inicio = resumen['fecha_desde']
                fecha_fin = datetime.now()
                
                # Obtener observaciones
                observaciones = self.observaciones.toPlainText().strip()
                
                # Crear cierre
                self.db.crear_cierre_caja(
                    tipo_cierre=tipo_cierre,
                    fecha_inicio=fecha_inicio,
                    fecha_fin=fecha_fin,
                    total_ventas=ventas[0],
                    ticket_promedio=ventas[2],
                    total_efectivo=ventas[3],
                    total_tarjeta=ventas[4],
                    total_otros_pagos=ventas[5],
                    total_igv=ventas[6],
                    usuario_id=self.usuario_id,
                    observaciones=observaciones
                )
                
                # Mostrar confirmación
                QMessageBox.information(
                    self,
                    "✅ Cierre Exitoso",
                    f"Cierre {tipo_texto} realizado correctamente.\n\n"
                    f"📅 Fecha: {fecha_fin.strftime('%d/%m/%Y %H:%M')}\n"
                    f"📊 Ventas: {ventas[0]}\n"
                    f"💰 Total: S/. {ventas[1]:.2f}"
                )
                
                # Actualizar datos
                self.cargar_datos_caja()
                
                # Si es cierre definitivo, cerrar el diálogo
                if tipo_cierre == 'Z':
                    self.accept()
                    
        except Exception as e:
            QMessageBox.critical(self, "❌ Error", f"Error al realizar cierre: {str(e)}")

    def closeEvent(self, event):
        """Maneja el cierre del diálogo"""
        self.timer.stop()
        event.accept()
