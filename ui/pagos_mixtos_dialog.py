from PySide6.QtWidgets import (
    QDialog, QVBoxLayout, QHBoxLayout, QLabel, QLineEdit, QPushButton,
    QGroupBox, QGridLayout, QMessageBox, QSpinBox, QDoubleSpinBox,
    QFrame, QTableWidget, QTableWidgetItem, QHeaderView, QComboBox
)
from PySide6.QtCore import Qt, QTimer
from PySide6.QtGui import QFont, QDoubleValidator, QKeyEvent
import json
from decimal import Decimal, ROUND_HALF_UP

# Estilos CSS comunes para reutilización
ESTILOS_CSS = {
    "titulo": "font-size: 18px; font-weight: bold; color: #2c3e50; margin: 10px;",
    "label_texto": "font-size: 16px; font-weight: bold; color: #2c3e50; padding: 5px;",
    "label_pequeno": "font-size: 14px; font-weight: bold; color: #2c3e50; padding: 5px;",
    "total_venta": "color: #e74c3c; background: #ecf0f1; padding: 15px; border-radius: 10px;",
    "total_pagado": "color: #ffffff; background: #27ae60; padding: 15px; border-radius: 10px; border: 2px solid #27ae60; font-weight: bold; min-width: 150px;",
    "falta_pagar": "color: #ffffff; background: #e67e22; padding: 15px; border-radius: 10px; border: 2px solid #e67e22; font-weight: bold; min-width: 150px;",
    "vuelto": "color: #8e44ad; background: #f8f4fc; padding: 20px; border-radius: 15px; border: 3px solid #8e44ad; min-width: 180px;",
    "boton_principal": "QPushButton { background: #e74c3c; color: white; padding: 15px; border-radius: 10px; font-weight: bold; }",
    "boton_secundario": "QPushButton { background: #7f8c8d; color: white; padding: 15px; border-radius: 10px; font-weight: bold; }",
    "boton_accion": "QPushButton { background: #3498db; color: white; padding: 10px; border-radius: 5px; font-weight: bold; }",
    "boton_tarjeta": "QPushButton { background: #9b59b6; color: white; padding: 10px; border-radius: 5px; font-weight: bold; }",
    "boton_limpiar": "QPushButton { background: #95a5a6; color: white; padding: 10px; border-radius: 5px; font-weight: bold; }",
    "boton_habilitado": "QPushButton { background: #27ae60; color: white; padding: 15px; border-radius: 10px; font-weight: bold; }",
    "boton_deshabilitado": "QPushButton { background: #bdc3c7; color: #7f8c8d; padding: 15px; border-radius: 10px; font-weight: bold; }"
}

class PagosMixtosDialog(QDialog):
    def __init__(self, total_venta, parent=None):
        super().__init__(parent)
        self.setWindowTitle("Sistema de Pagos Mixtos")
        self.setModal(True)
        self.setMinimumSize(700, 600)
        
        # Variables
        self.total_venta = Decimal(str(total_venta))
        self.total_pagado = Decimal('0.00')
        self.efectivo = Decimal('0.00')
        self.tarjeta = Decimal('0.00')
        self.otros = Decimal('0.00')
        self.vuelto = Decimal('0.00')
        
        # Medios de pago disponibles
        self.medios_pago = [
            "Efectivo",
            "Tarjeta de Crédito",
            "Tarjeta de Débito", 
            "Transferencia",
            "Yape",
            "Plin",
            "Otros"
        ]
        
        self.setup_ui()
        self.actualizar_totales()
        
    def setup_ui(self):
        layout = QVBoxLayout(self)
        
        # Título
        titulo = QLabel("SISTEMA DE PAGOS MIXTOS")
        titulo.setFont(QFont("Arial", 18, QFont.Weight.Bold))
        titulo.setAlignment(Qt.AlignmentFlag.AlignCenter)
        titulo.setStyleSheet(ESTILOS_CSS["titulo"])
        layout.addWidget(titulo)
        
        # Total de la venta
        total_group = QGroupBox("Total de la Venta")
        total_group.setStyleSheet("QGroupBox { font-weight: bold; font-size: 14px; }")
        total_layout = QHBoxLayout(total_group)
        
        self.lbl_total_venta = QLabel(f"S/. {self.total_venta:.2f}")
        self.lbl_total_venta.setFont(QFont("Arial", 24, QFont.Weight.Bold))
        self.lbl_total_venta.setStyleSheet(ESTILOS_CSS["total_venta"])
        self.lbl_total_venta.setAlignment(Qt.AlignmentFlag.AlignCenter)
        total_layout.addWidget(self.lbl_total_venta)
        
        layout.addWidget(total_group)
        
        # Sección de pagos
        pagos_group = QGroupBox("Ingrese los Pagos")
        pagos_layout = QGridLayout(pagos_group)
        
        # Efectivo
        lbl_efectivo = QLabel("Efectivo:")
        lbl_efectivo.setStyleSheet(ESTILOS_CSS["label_pequeno"])
        pagos_layout.addWidget(lbl_efectivo, 0, 0)
        
        self.spin_efectivo = QDoubleSpinBox()
        self.spin_efectivo.setRange(0, 999999.99)
        self.spin_efectivo.setDecimals(2)
        self.spin_efectivo.setSuffix(" S/.")
        self.spin_efectivo.setFont(QFont("Arial", 14))
        self.spin_efectivo.valueChanged.connect(self.actualizar_totales)
        pagos_layout.addWidget(self.spin_efectivo, 0, 1)
        
        # Tarjeta
        lbl_tarjeta = QLabel("Tarjeta:")
        lbl_tarjeta.setStyleSheet(ESTILOS_CSS["label_pequeno"])
        pagos_layout.addWidget(lbl_tarjeta, 1, 0)
        
        self.spin_tarjeta = QDoubleSpinBox()
        self.spin_tarjeta.setRange(0, 999999.99)
        self.spin_tarjeta.setDecimals(2)
        self.spin_tarjeta.setSuffix(" S/.")
        self.spin_tarjeta.setFont(QFont("Arial", 14))
        self.spin_tarjeta.valueChanged.connect(self.actualizar_totales)
        pagos_layout.addWidget(self.spin_tarjeta, 1, 1)
        
        # Otros medios
        lbl_otros = QLabel("Otros:")
        lbl_otros.setStyleSheet(ESTILOS_CSS["label_pequeno"])
        pagos_layout.addWidget(lbl_otros, 2, 0)
        
        self.spin_otros = QDoubleSpinBox()
        self.spin_otros.setRange(0, 999999.99)
        self.spin_otros.setDecimals(2)
        self.spin_otros.setSuffix(" S/.")
        self.spin_otros.setFont(QFont("Arial", 14))
        self.spin_otros.valueChanged.connect(self.actualizar_totales)
        pagos_layout.addWidget(self.spin_otros, 2, 1)
        
        # Tipo de tarjeta
        lbl_tipo_tarjeta = QLabel("Tipo Tarjeta:")
        lbl_tipo_tarjeta.setStyleSheet(ESTILOS_CSS["label_pequeno"])
        pagos_layout.addWidget(lbl_tipo_tarjeta, 1, 2)
        
        self.cmb_tipo_tarjeta = QComboBox()
        self.cmb_tipo_tarjeta.addItems(["Crédito", "Débito"])
        self.cmb_tipo_tarjeta.setFont(QFont("Arial", 12))
        pagos_layout.addWidget(self.cmb_tipo_tarjeta, 1, 3)
        
        # Otros medios específicos
        lbl_medio = QLabel("Medio:")
        lbl_medio.setStyleSheet(ESTILOS_CSS["label_pequeno"])
        pagos_layout.addWidget(lbl_medio, 2, 2)
        
        self.cmb_medio_otros = QComboBox()
        self.cmb_medio_otros.addItems(["Transferencia", "Yape", "Plin", "Otros"])
        self.cmb_medio_otros.setFont(QFont("Arial", 12))
        pagos_layout.addWidget(self.cmb_medio_otros, 2, 3)
        
        layout.addWidget(pagos_group)
        
        # Resumen de pagos
        resumen_group = QGroupBox("Resumen de Pagos")
        resumen_layout = QGridLayout(resumen_group)
        
        # Labels con mejor visibilidad y estilos optimizados
        lbl_total_pagado_text = QLabel("Total Pagado:")
        lbl_total_pagado_text.setStyleSheet("font-size: 16px; font-weight: bold; color: #2c3e50; padding: 5px;")
        resumen_layout.addWidget(lbl_total_pagado_text, 0, 0)
        
        self.lbl_total_pagado = QLabel("S/. 0.00")
        self.lbl_total_pagado.setFont(QFont("Arial", 20, QFont.Weight.Bold))
        self.lbl_total_pagado.setStyleSheet("color: #ffffff; background: #27ae60; padding: 15px; border-radius: 10px; border: 2px solid #27ae60; font-weight: bold; min-width: 150px;")
        self.lbl_total_pagado.setAlignment(Qt.AlignmentFlag.AlignCenter)
        resumen_layout.addWidget(self.lbl_total_pagado, 0, 1)
        
        lbl_falta_pagar_text = QLabel("Falta por Pagar:")
        lbl_falta_pagar_text.setStyleSheet("font-size: 16px; font-weight: bold; color: #2c3e50; padding: 5px;")
        resumen_layout.addWidget(lbl_falta_pagar_text, 1, 0)
        
        self.lbl_falta_pagar = QLabel("S/. 0.00")
        self.lbl_falta_pagar.setFont(QFont("Arial", 20, QFont.Weight.Bold))
        self.lbl_falta_pagar.setStyleSheet("color: #ffffff; background: #e67e22; padding: 15px; border-radius: 10px; border: 2px solid #e67e22; font-weight: bold; min-width: 150px;")
        self.lbl_falta_pagar.setAlignment(Qt.AlignmentFlag.AlignCenter)
        resumen_layout.addWidget(self.lbl_falta_pagar, 1, 1)
        
        lbl_vuelto_text = QLabel("Vuelto:")
        lbl_vuelto_text.setStyleSheet("font-size: 16px; font-weight: bold; color: #2c3e50; padding: 5px;")
        resumen_layout.addWidget(lbl_vuelto_text, 2, 0)
        
        # Vuelto más grande y destacado con mejor visibilidad
        self.lbl_vuelto = QLabel("S/. 0.00")
        self.lbl_vuelto.setFont(QFont("Arial", 26, QFont.Weight.Bold))
        self.lbl_vuelto.setStyleSheet("color: #8e44ad; background: #f8f4fc; padding: 20px; border-radius: 15px; border: 3px solid #8e44ad; min-width: 180px;")
        self.lbl_vuelto.setAlignment(Qt.AlignmentFlag.AlignCenter)
        resumen_layout.addWidget(self.lbl_vuelto, 2, 1)
        
        # Información de redondeo (debajo del vuelto)
        self.lbl_info_redondeo = QLabel("")
        self.lbl_info_redondeo.setFont(QFont("Arial", 12, QFont.Weight.Bold))
        self.lbl_info_redondeo.setStyleSheet("color: #27ae60; background: #d5f4e6; padding: 10px; border-radius: 8px; border: 2px solid #27ae60; margin: 5px;")
        self.lbl_info_redondeo.setAlignment(Qt.AlignmentFlag.AlignCenter)
        self.lbl_info_redondeo.setVisible(False)
        resumen_layout.addWidget(self.lbl_info_redondeo, 3, 0, 1, 2)  # Span 2 columnas
        
        layout.addWidget(resumen_group)
        
        # Botones de acción rápida
        acciones_group = QGroupBox("Acciones Rápidas")
        acciones_layout = QHBoxLayout(acciones_group)
        
        self.btn_pagar_efectivo = QPushButton("Pagar solo en efectivo")
        self.btn_pagar_efectivo.clicked.connect(self.pagar_solo_efectivo)
        self.btn_pagar_efectivo.setStyleSheet(ESTILOS_CSS["boton_accion"])
        acciones_layout.addWidget(self.btn_pagar_efectivo)
        
        self.btn_pagar_tarjeta = QPushButton("Pagar solo con tarjeta")
        self.btn_pagar_tarjeta.clicked.connect(self.pagar_solo_tarjeta)
        self.btn_pagar_tarjeta.setStyleSheet(ESTILOS_CSS["boton_tarjeta"])
        acciones_layout.addWidget(self.btn_pagar_tarjeta)
        
        self.btn_limpiar = QPushButton("Limpiar")
        self.btn_limpiar.clicked.connect(self.limpiar_pagos)
        self.btn_limpiar.setStyleSheet(ESTILOS_CSS["boton_limpiar"])
        acciones_layout.addWidget(self.btn_limpiar)
        
        layout.addWidget(acciones_group)
        
        # Botones principales
        botones_layout = QHBoxLayout()
        
        self.btn_cobrar = QPushButton("COBRAR")
        self.btn_cobrar.clicked.connect(self.cobrar_venta)
        self.btn_cobrar.setFont(QFont("Arial", 16, QFont.Weight.Bold))
        self.btn_cobrar.setStyleSheet(ESTILOS_CSS["boton_principal"])
        self.btn_cobrar.setMinimumHeight(60)
        botones_layout.addWidget(self.btn_cobrar)
        
        self.btn_cancelar = QPushButton("Cancelar")
        self.btn_cancelar.clicked.connect(self.reject)
        self.btn_cancelar.setStyleSheet(ESTILOS_CSS["boton_secundario"])
        self.btn_cancelar.setMinimumHeight(60)
        botones_layout.addWidget(self.btn_cancelar)
        
        layout.addLayout(botones_layout)
        
        # Conectar eventos
        self.actualizar_estado_botones()
        
        # Conectar eventos de teclado para Enter
        self.spin_efectivo.installEventFilter(self)
        self.spin_tarjeta.installEventFilter(self)
        self.spin_otros.installEventFilter(self)
        
    def actualizar_totales(self):
        """Actualiza todos los totales y cálculos con redondeo a favor del cliente"""
        self.efectivo = Decimal(str(self.spin_efectivo.value()))
        self.tarjeta = Decimal(str(self.spin_tarjeta.value()))
        self.otros = Decimal(str(self.spin_otros.value()))
        
        self.total_pagado = self.efectivo + self.tarjeta + self.otros
        
        # Aplicar redondeo a favor del cliente solo cuando hay efectivo
        total_efectivo_redondeado = self.total_venta
        if self.efectivo > 0:
            total_efectivo_redondeado = self.aplicar_redondeo_favor_cliente(self.total_venta)
        
        # Calcular vuelto considerando el redondeo
        if self.efectivo > 0:
            # Si hay efectivo, usar el total redondeado
            self.vuelto = max(Decimal('0.00'), self.total_pagado - total_efectivo_redondeado)
        else:
            # Si no hay efectivo, usar el total original
            self.vuelto = max(Decimal('0.00'), self.total_pagado - self.total_venta)
        
        # Actualizar labels
        self.lbl_total_pagado.setText(f"S/. {self.total_pagado:.2f}")
        
        # Mostrar falta a pagar considerando redondeo
        if self.efectivo > 0:
            falta_pagar = max(Decimal('0.00'), total_efectivo_redondeado - self.total_pagado)
            self.lbl_falta_pagar.setText(f"S/. {falta_pagar:.2f}")
        else:
            falta_pagar = max(Decimal('0.00'), self.total_venta - self.total_pagado)
            self.lbl_falta_pagar.setText(f"S/. {falta_pagar:.2f}")
        
        self.lbl_vuelto.setText(f"S/. {self.vuelto:.2f}")
        
        # Mostrar información de redondeo cuando hay efectivo
        if self.efectivo > 0:
            total_redondeado = self.aplicar_redondeo_favor_cliente(self.total_venta)
            if total_redondeado != self.total_venta:
                redondeo = self.total_venta - total_redondeado
                self.lbl_info_redondeo.setText(f"💰 REDONDEO A DÉCIMA INFERIOR: S/. {self.total_venta:.2f} → S/. {total_redondeado:.2f} (Redondeo: S/. {redondeo:.2f})")
                self.lbl_info_redondeo.setVisible(True)
            else:
                self.lbl_info_redondeo.setVisible(False)
        else:
            self.lbl_info_redondeo.setVisible(False)
        
        # Cambiar colores según el estado
        if self.total_pagado >= (total_efectivo_redondeado if self.efectivo > 0 else self.total_venta):
            self.lbl_falta_pagar.setStyleSheet("color: #ffffff; background: #27ae60; padding: 15px; border-radius: 10px; border: 2px solid #27ae60; font-weight: bold;")
            self.lbl_falta_pagar.setText("PAGO COMPLETO ✓")
        else:
            self.lbl_falta_pagar.setStyleSheet("color: #ffffff; background: #e67e22; padding: 15px; border-radius: 10px; border: 2px solid #e67e22; font-weight: bold;")
        
        # Actualizar estado de botones
        self.actualizar_estado_botones()
        
    def actualizar_estado_botones(self):
        """Actualiza el estado de los botones según los pagos"""
        # Considerar redondeo cuando hay efectivo
        total_a_considerar = self.total_venta
        if self.efectivo > 0:
            total_a_considerar = self.aplicar_redondeo_favor_cliente(self.total_venta)
        
        if self.total_pagado >= total_a_considerar:
            self.btn_cobrar.setEnabled(True)
            self.btn_cobrar.setStyleSheet(ESTILOS_CSS["boton_habilitado"])
            self.btn_cobrar.setText("COBRAR ✓")
        else:
            self.btn_cobrar.setEnabled(False)
            self.btn_cobrar.setStyleSheet(ESTILOS_CSS["boton_deshabilitado"])
            self.btn_cobrar.setText("COMPLETAR EL PAGO")
    
    def aplicar_redondeo_favor_cliente(self, total):
        """
        Aplica redondeo a favor del cliente cuando hay pagos en efectivo.
        Los centavos de 0.01 a 0.99 se redondean a la décima inferior (a favor del cliente).
        Ejemplos: 30.22→30.20, 23.01→23.00, 345.99→345.90
        """
        try:
            # Convertir a Decimal para precisión
            total_decimal = Decimal(str(total))
            
            # Obtener la parte entera y los centavos
            parte_entera = int(total_decimal)
            centavos = total_decimal - parte_entera
            
            # Si hay centavos (0.01 a 0.99), redondear a la décima inferior
            if Decimal('0.01') <= centavos <= Decimal('0.99'):
                # Calcular la décima inferior
                # Ejemplo: 30.22 → centavos = 0.22 → décima = 0.20
                decima = Decimal(str(int(centavos * 10) / 10))
                total_redondeado = parte_entera + decima
                return total_redondeado
            else:
                # No aplicar redondeo, mantener el total original
                return total_decimal
                
        except Exception as e:
            print(f"Error en redondeo: {e}")
            return total
    
    def pagar_solo_efectivo(self):
        """Paga solo con efectivo"""
        self.spin_efectivo.setValue(float(self.total_venta))
        self.spin_tarjeta.setValue(0.0)
        self.spin_otros.setValue(0.0)
        
    def pagar_solo_tarjeta(self):
        """Paga solo con tarjeta"""
        self.spin_efectivo.setValue(0.0)
        self.spin_tarjeta.setValue(float(self.total_venta))
        self.spin_otros.setValue(0.0)
        
    def limpiar_pagos(self):
        """Limpia todos los pagos"""
        self.spin_efectivo.setValue(0.0)
        self.spin_tarjeta.setValue(0.0)
        self.spin_otros.setValue(0.0)
        
    def cobrar_venta(self):
        """Procesa el cobro de la venta"""
        # Considerar redondeo cuando hay efectivo
        total_a_considerar = self.total_venta
        if self.efectivo > 0:
            total_a_considerar = self.aplicar_redondeo_favor_cliente(self.total_venta)
        
        if self.total_pagado < total_a_considerar:
            falta = total_a_considerar - self.total_pagado
            QMessageBox.warning(self, "Pago Incompleto", 
                f"El pago está incompleto. Falta S/. {falta:.2f}")
            return
            
        # Generar resumen de cobro
        resumen = self.generar_resumen_cobro()
        
        # Mostrar confirmación
        reply = QMessageBox.question(
            self,
            "Confirmar Cobro",
            f"¿Confirmar el cobro?\n\n{resumen}",
            QMessageBox.StandardButton.Yes | QMessageBox.StandardButton.No,
            QMessageBox.StandardButton.Yes
        )
        
        if reply == QMessageBox.StandardButton.Yes:
            # Cerrar el diálogo directamente (el mensaje se mostrará en venta_form.py)
            self.accept()
    
    def generar_resumen_cobro(self):
        """Genera el resumen del cobro con información de redondeo"""
        # Verificar si se aplicó redondeo
        total_original = self.total_venta
        total_efectivo = self.total_venta
        redondeo_aplicado = False
        
        if self.efectivo > 0:
            total_efectivo = self.aplicar_redondeo_favor_cliente(self.total_venta)
            redondeo_aplicado = total_efectivo != total_original
        
        resumen = f"""
        RESUMEN DE COBRO
        ================
        Total Venta Original: S/. {total_original:.2f}
        """
        
        if redondeo_aplicado:
            resumen += f"""
        REDONDEO (a favor del cliente):
        Total a Pagar: S/. {total_efectivo:.2f}
        Redondeo:      S/. {total_original - total_efectivo:.2f}
        """
        
        resumen += f"""
        
        PAGOS REALIZADOS:
        - Efectivo: S/. {self.efectivo:.2f}
        - Tarjeta:  S/. {self.tarjeta:.2f}
        - Otros:    S/. {self.otros:.2f}
        ---------------------------
        Total Pagado: S/. {self.total_pagado:.2f}
        ---------------------------
        💰 VUELTO: S/. {self.vuelto:.2f} 💰
        ---------------------------
        MEDIOS DE PAGO:
        - Tipo Tarjeta: {self.cmb_tipo_tarjeta.currentText()}
        - Otro Medio:   {self.cmb_medio_otros.currentText()}
        """
        return resumen
    
    def mostrar_pago_exitoso(self):
        """Muestra el diálogo de pago exitoso simplificado"""
        # Crear mensaje simple y directo
        mensaje = f"PAGO REGISTRADO CORRECTAMENTE\n\n"
        mensaje += f"💰 VUELTO: S/. {self.vuelto:.2f} 💰\n\n"
        mensaje += f"¿Desea iniciar otra venta?"
        
        # Mostrar diálogo con opciones
        reply = QMessageBox.question(
            self,
            "Cobro correcto",
            mensaje,
            QMessageBox.StandardButton.Yes | QMessageBox.StandardButton.No,
            QMessageBox.StandardButton.Yes
        )
        
        # Si el usuario quiere hacer otra venta, se puede implementar aquí
        if reply == QMessageBox.StandardButton.Yes:
            # Aquí se podría implementar la lógica para nueva venta
            pass
        # Si no, el diálogo se cierra automáticamente
    
    def eventFilter(self, obj, event):
        """Filtra eventos para manejar Enter en los campos de entrada"""
        if event.type() == event.Type.KeyPress:
            if event.key() == Qt.Key.Key_Return or event.key() == Qt.Key.Key_Enter:
                # Si el pago está completo, redirigir a cobrar
                if self.total_pagado >= self.total_venta:
                    self.cobrar_venta()
                else:
                    # Si no está completo, mostrar mensaje y enfocar el siguiente campo
                    self.mostrar_mensaje_pago_incompleto()
                return True
        return super().eventFilter(obj, event)
    
    def mostrar_mensaje_pago_incompleto(self):
        """Muestra mensaje cuando el pago está incompleto"""
        falta = self.total_venta - self.total_pagado
        QMessageBox.information(
            self, 
            "Pago Incompleto", 
            f"El pago está incompleto. Falta S/. {falta:.2f}\n\n"
            "Complete el pago antes de continuar."
        )
    
    def get_resultado_pago(self):
        """Retorna el resultado del pago para el sistema principal"""
        return {
            "efectivo": float(self.efectivo),
            "tarjeta": float(self.tarjeta),
            "otros": float(self.otros),
            "total_pagado": float(self.total_pagado),
            "vuelto": float(self.vuelto),
            "tipo_tarjeta": self.cmb_tipo_tarjeta.currentText(),
            "medio_otros": self.cmb_medio_otros.currentText(),
            "pago_completo": self.total_pagado >= self.total_venta
        }
    
    def keyPressEvent(self, event: QKeyEvent):
        """Manejar eventos de teclado"""
        try:
            # Enter: Posicionar cursor en botón Cobrar si está habilitado
            if event.key() == Qt.Key.Key_Return or event.key() == Qt.Key.Key_Enter:
                if self.btn_cobrar.isEnabled():
                    self.btn_cobrar.setFocus()
                    # Simular click en el botón si está habilitado
                    self.btn_cobrar.click()
                else:
                    # Si no está habilitado, mantener el foco en el campo actual
                    pass
                return
            
            # Llamar al método padre para otros eventos
            super().keyPressEvent(event)
            
        except Exception as e:
            print(f"Error en keyPressEvent: {e}")
            super().keyPressEvent(event)
