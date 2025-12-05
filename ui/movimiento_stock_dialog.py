#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Diálogo para movimientos de stock
Permite realizar entradas, salidas y ajustes de inventario
"""

from PySide6.QtWidgets import (QDialog, QVBoxLayout, QHBoxLayout, QGridLayout,
                               QLabel, QPushButton, QComboBox, QLineEdit, 
                               QSpinBox, QTextEdit, QGroupBox, QMessageBox,
                               QFormLayout, QDateEdit, QCheckBox)
from PySide6.QtCore import Qt, QDate
from PySide6.QtGui import QFont

class MovimientoStockDialog(QDialog):
    def __init__(self, db, usuario_actual, producto_id=None, parent=None):
        super().__init__(parent)
        self.db = db
        self.usuario_actual = usuario_actual
        self.producto_id = producto_id
        self.init_ui()
        self.cargar_datos()
        
    def init_ui(self):
        """Inicializar la interfaz de usuario"""
        self.setWindowTitle("📦 Movimiento de Stock")
        self.setModal(True)
        self.resize(500, 600)
        
        # Layout principal
        layout = QVBoxLayout(self)
        layout.setSpacing(15)
        layout.setContentsMargins(20, 20, 20, 20)
        
        # Título
        titulo = QLabel("📦 Movimiento de Stock")
        titulo.setStyleSheet("""
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        """)
        layout.addWidget(titulo)
        
        # Grupo de información del producto
        producto_group = QGroupBox("Información del Producto")
        producto_layout = QFormLayout(producto_group)
        
        # Búsqueda de producto
        self.busqueda_layout = QHBoxLayout()
        self.busqueda_input = QLineEdit()
        self.busqueda_input.setPlaceholderText("Buscar por nombre, SKU o código de barras...")
        self.busqueda_input.textChanged.connect(self.buscar_producto)
        self.busqueda_layout.addWidget(self.busqueda_input)
        
        self.btn_buscar = QPushButton("🔍")
        self.btn_buscar.clicked.connect(self.buscar_producto)
        self.busqueda_layout.addWidget(self.btn_buscar)
        
        producto_layout.addRow("Buscar Producto:", self.busqueda_layout)
        
        # Información del producto seleccionado
        self.producto_info = QLabel("Seleccione un producto")
        self.producto_info.setStyleSheet("""
            background: #f8f9fa;
            border: 1px solid #bdc3c7;
            border-radius: 4px;
            padding: 10px;
            color: #7f8c8d;
        """)
        producto_layout.addRow("Producto:", self.producto_info)
        
        # Stock actual
        self.stock_actual_label = QLabel("0")
        self.stock_actual_label.setStyleSheet("""
            font-size: 16px;
            font-weight: bold;
            color: #27ae60;
        """)
        producto_layout.addRow("Stock Actual:", self.stock_actual_label)
        
        layout.addWidget(producto_group)
        
        # Grupo de movimiento
        movimiento_group = QGroupBox("Detalles del Movimiento")
        movimiento_layout = QFormLayout(movimiento_group)
        
        # Tipo de movimiento
        self.tipo_movimiento = QComboBox()
        self.tipo_movimiento.addItems(["entrada", "salida", "ajuste"])
        self.tipo_movimiento.currentTextChanged.connect(self.cambiar_tipo_movimiento)
        movimiento_layout.addRow("Tipo de Movimiento:", self.tipo_movimiento)
        
        # Cantidad
        self.cantidad_spin = QSpinBox()
        self.cantidad_spin.setRange(1, 999999)
        self.cantidad_spin.setValue(1)
        movimiento_layout.addRow("Cantidad:", self.cantidad_spin)
        
        # Nuevo stock (solo para ajustes)
        self.nuevo_stock_spin = QSpinBox()
        self.nuevo_stock_spin.setRange(0, 999999)
        self.nuevo_stock_spin.setValue(0)
        self.nuevo_stock_spin.setVisible(False)
        movimiento_layout.addRow("Nuevo Stock:", self.nuevo_stock_spin)
        
        # Fecha del movimiento
        self.fecha_movimiento = QDateEdit()
        self.fecha_movimiento.setDate(QDate.currentDate())
        movimiento_layout.addRow("Fecha:", self.fecha_movimiento)
        
        # Motivo
        self.motivo_text = QTextEdit()
        self.motivo_text.setMaximumHeight(80)
        self.motivo_text.setPlaceholderText("Describa el motivo del movimiento...")
        movimiento_layout.addRow("Motivo:", self.motivo_text)
        
        # Documento de referencia
        self.documento_ref = QLineEdit()
        self.documento_ref.setPlaceholderText("Número de factura, orden de compra, etc.")
        movimiento_layout.addRow("Documento Ref:", self.documento_ref)
        
        # Checkbox para confirmar
        self.confirmar_check = QCheckBox("Confirmo que la información es correcta")
        movimiento_layout.addRow("", self.confirmar_check)
        
        layout.addWidget(movimiento_group)
        
        # Botones
        botones_layout = QHBoxLayout()
        
        self.btn_cancelar = QPushButton("❌ Cancelar")
        self.btn_cancelar.clicked.connect(self.reject)
        botones_layout.addWidget(self.btn_cancelar)
        
        botones_layout.addStretch()
        
        self.btn_guardar = QPushButton("💾 Guardar Movimiento")
        self.btn_guardar.clicked.connect(self.guardar_movimiento)
        self.btn_guardar.setEnabled(False)
        botones_layout.addWidget(self.btn_guardar)
        
        layout.addLayout(botones_layout)
        
        # Aplicar estilos
        self.aplicar_estilos()
        
    def aplicar_estilos(self):
        """Aplicar estilos al diálogo"""
        self.setStyleSheet("""
            QDialog {
                background: #ecf0f1;
            }
            QGroupBox {
                font-weight: bold;
                border: 2px solid #bdc3c7;
                border-radius: 8px;
                margin-top: 10px;
                padding-top: 10px;
            }
            QGroupBox::title {
                subcontrol-origin: margin;
                left: 10px;
                padding: 0 5px 0 5px;
                background: #ecf0f1;
            }
            QLineEdit, QComboBox, QSpinBox, QDateEdit, QTextEdit {
                padding: 8px;
                border: 2px solid #bdc3c7;
                border-radius: 4px;
                background: white;
            }
            QLineEdit:focus, QComboBox:focus, QSpinBox:focus, QDateEdit:focus, QTextEdit:focus {
                border-color: #3498db;
            }
            QPushButton {
                background: #3498db;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 4px;
                font-weight: bold;
            }
            QPushButton:hover {
                background: #2980b9;
            }
            QPushButton:pressed {
                background: #21618c;
            }
            QPushButton:disabled {
                background: #bdc3c7;
                color: #7f8c8d;
            }
            QCheckBox {
                font-weight: bold;
                color: #2c3e50;
            }
        """)
        
    def cargar_datos(self):
        """Cargar datos iniciales"""
        if self.producto_id:
            self.cargar_producto_por_id(self.producto_id)
            
    def buscar_producto(self):
        """Buscar producto por texto"""
        texto = self.busqueda_input.text().strip()
        if len(texto) < 2:
            return
            
        try:
            # Buscar productos que coincidan con el texto
            productos = self.db.get_productos_con_stock()
            coincidencias = []
            
            for producto in productos:
                nombre = producto[1] or ""
                sku = producto[2] or ""
                codigo = producto[4] or ""
                
                if (texto.lower() in nombre.lower() or 
                    texto.lower() in sku.lower() or 
                    texto.lower() in codigo.lower()):
                    coincidencias.append(producto)
            
            if coincidencias:
                # Mostrar el primer resultado
                self.seleccionar_producto(coincidencias[0])
            else:
                self.limpiar_seleccion()
                
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error buscando producto: {str(e)}")
            
    def cargar_producto_por_id(self, producto_id):
        """Cargar producto por ID"""
        try:
            productos = self.db.get_productos_con_stock()
            for producto in productos:
                if producto[0] == producto_id:
                    self.seleccionar_producto(producto)
                    break
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error cargando producto: {str(e)}")
            
    def seleccionar_producto(self, producto):
        """Seleccionar un producto"""
        self.producto_id = producto[0]
        nombre = producto[1] or "Sin nombre"
        sku = producto[2] or "Sin SKU"
        stock_actual = producto[6] or 0
        
        self.producto_info.setText(f"{nombre} (SKU: {sku})")
        self.producto_info.setStyleSheet("""
            background: #d5f4e6;
            border: 1px solid #27ae60;
            border-radius: 4px;
            padding: 10px;
            color: #27ae60;
            font-weight: bold;
        """)
        
        self.stock_actual_label.setText(str(stock_actual))
        self.nuevo_stock_spin.setValue(stock_actual)
        
        self.validar_formulario()
        
    def limpiar_seleccion(self):
        """Limpiar selección de producto"""
        self.producto_id = None
        self.producto_info.setText("Seleccione un producto")
        self.producto_info.setStyleSheet("""
            background: #f8f9fa;
            border: 1px solid #bdc3c7;
            border-radius: 4px;
            padding: 10px;
            color: #7f8c8d;
        """)
        
        self.stock_actual_label.setText("0")
        self.validar_formulario()
        
    def cambiar_tipo_movimiento(self, tipo):
        """Cambiar tipo de movimiento"""
        if tipo == "ajuste":
            self.nuevo_stock_spin.setVisible(True)
            self.cantidad_spin.setVisible(False)
        else:
            self.nuevo_stock_spin.setVisible(False)
            self.cantidad_spin.setVisible(True)
            
        self.validar_formulario()
        
    def validar_formulario(self):
        """Validar formulario y habilitar/deshabilitar botón guardar"""
        valido = (
            self.producto_id is not None and
            self.motivo_text.toPlainText().strip() != "" and
            self.confirmar_check.isChecked()
        )
        
        self.btn_guardar.setEnabled(valido)
        
    def guardar_movimiento(self):
        """Guardar movimiento de stock"""
        if not self.validar_movimiento():
            return
            
        try:
            tipo_movimiento = self.tipo_movimiento.currentText()
            motivo = self.motivo_text.toPlainText().strip()
            documento_ref = self.documento_ref.text().strip()
            usuario_id = self.usuario_actual.get('id', 1)
            
            if tipo_movimiento == "ajuste":
                nuevo_stock = self.nuevo_stock_spin.value()
                cantidad = 0  # Se calculará automáticamente
            else:
                cantidad = self.cantidad_spin.value()
                nuevo_stock = 0
                
            # Realizar movimiento
            success, message = self.db.actualizar_stock_producto(
                self.producto_id, nuevo_stock, tipo_movimiento, 
                cantidad, motivo, usuario_id, documento_ref
            )
            
            if success:
                QMessageBox.information(self, "Éxito", f"Movimiento guardado correctamente.\n{message}")
                self.accept()
            else:
                QMessageBox.critical(self, "Error", f"Error guardando movimiento:\n{message}")
                
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error inesperado: {str(e)}")
            
    def validar_movimiento(self):
        """Validar movimiento antes de guardar"""
        tipo_movimiento = self.tipo_movimiento.currentText()
        
        if tipo_movimiento == "salida":
            stock_actual = int(self.stock_actual_label.text())
            cantidad = self.cantidad_spin.value()
            
            if cantidad > stock_actual:
                QMessageBox.warning(self, "Error de Validación", 
                                  f"No hay suficiente stock.\nStock disponible: {stock_actual}\nCantidad solicitada: {cantidad}")
                return False
                
        elif tipo_movimiento == "ajuste":
            nuevo_stock = self.nuevo_stock_spin.value()
            if nuevo_stock < 0:
                QMessageBox.warning(self, "Error de Validación", "El stock no puede ser negativo.")
                return False
                
        return True
