#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Módulo de Gestión de inventario avanzada
Sistema de gestión de inventario con movimientos de entrada, salida y ajustes
"""

import sys
from datetime import datetime, date
from PySide6.QtWidgets import (QApplication, QMainWindow, QWidget, QVBoxLayout, 
                               QHBoxLayout, QGridLayout, QLabel, QPushButton, 
                               QTableWidget, QTableWidgetItem, QComboBox, 
                               QLineEdit, QSpinBox, QTextEdit, QDateEdit, 
                               QGroupBox, QTabWidget, QMessageBox, QDialog,
                               QHeaderView, QFrame, QSplitter, QScrollArea)
from PySide6.QtCore import Qt, QDate, Signal
from PySide6.QtGui import QFont, QIcon, QColor

class StockAvanzadoForm(QMainWindow):
    def __init__(self, db, usuario_actual):
        super().__init__()
        self.db = db
        self.usuario_actual = usuario_actual
        self.init_ui()
        self.cargar_datos()
        
    def init_ui(self):
        """Inicializar la interfaz de usuario"""
        self.setWindowTitle("📦 Gestión de inventario avanzada")
        self.setGeometry(100, 100, 1400, 900)
        
        # Widget central
        central_widget = QWidget()
        self.setCentralWidget(central_widget)
        
        # Layout principal
        main_layout = QVBoxLayout(central_widget)
        main_layout.setSpacing(10)
        main_layout.setContentsMargins(15, 15, 15, 15)
        
        # Título
        titulo = QLabel("📦 Gestión de inventario avanzada")
        titulo.setStyleSheet("""
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
        """)
        main_layout.addWidget(titulo)
        
        # Crear tabs
        self.tab_widget = QTabWidget()
        self.tab_widget.setStyleSheet("""
            QTabWidget::pane {
                border: 2px solid #bdc3c7;
                border-radius: 8px;
                background: white;
            }
            QTabBar::tab {
                background: #ecf0f1;
                color: #2c3e50;
                padding: 6px 12px;
                margin-right: 2px;
                border-top-left-radius: 8px;
                border-top-right-radius: 8px;
                font-weight: bold;
                font-size: 9px;
            }
            QTabBar::tab:selected {
                background: #3498db;
                color: white;
            }
            QTabBar::tab:hover {
                background: #5dade2;
                color: white;
            }
        """)
        
        # Tab 1: Vista general de inventario
        self.crear_tab_vista_general()
        
        # Tab 2: Movimientos de inventario
        self.crear_tab_movimientos()
        
        # Tab 3: Alertas de inventario
        self.crear_tab_alertas()
        
        # Tab 4: Estadísticas
        self.crear_tab_estadisticas()
        
        main_layout.addWidget(self.tab_widget)
        
        # Aplicar estilos generales
        self.aplicar_estilos()
        
    def crear_tab_vista_general(self):
        """Crear tab de vista general de inventario"""
        tab = QWidget()
        layout = QVBoxLayout(tab)
        layout.setSpacing(15)
        
        # Panel de controles
        controles_layout = QHBoxLayout()
        
        # Búsqueda
        self.busqueda_input = QLineEdit()
        self.busqueda_input.setPlaceholderText("🔍 Buscar producto...")
        self.busqueda_input.textChanged.connect(self.filtrar_productos)
        controles_layout.addWidget(QLabel("Buscar:"))
        controles_layout.addWidget(self.busqueda_input)
        
        # Botón actualizar
        self.btn_actualizar = QPushButton("🔄 Actualizar")
        self.btn_actualizar.clicked.connect(self.cargar_datos)
        controles_layout.addWidget(self.btn_actualizar)
        
        # Botón movimiento rápido
        self.btn_movimiento = QPushButton("➕ Movimiento Rápido")
        self.btn_movimiento.clicked.connect(self.abrir_movimiento_rapido)
        controles_layout.addWidget(self.btn_movimiento)
        
        controles_layout.addStretch()
        layout.addLayout(controles_layout)
        
        # Tabla de productos
        self.tabla_productos = QTableWidget()
        self.tabla_productos.setColumnCount(8)
        self.tabla_productos.setHorizontalHeaderLabels([
            "ID", "Descripción Larga", "SKU", "Inventario actual", "Inventario mínimo", 
            "Precio de venta", "Costo promedio", "Estado"
        ])
        
        # Configurar tabla
        header = self.tabla_productos.horizontalHeader()
        header.setSectionResizeMode(0, QHeaderView.ResizeToContents)  # ID
        header.setSectionResizeMode(1, QHeaderView.Stretch)          # Descripción Larga
        header.setSectionResizeMode(2, QHeaderView.ResizeToContents) # SKU
        header.setSectionResizeMode(3, QHeaderView.ResizeToContents) # Stock Actual
        header.setSectionResizeMode(4, QHeaderView.ResizeToContents) # Stock Mínimo
        header.setSectionResizeMode(5, QHeaderView.ResizeToContents) # Precio Venta
        header.setSectionResizeMode(6, QHeaderView.ResizeToContents) # Costo Promedio
        header.setSectionResizeMode(7, QHeaderView.ResizeToContents) # Estado
        
        self.tabla_productos.setAlternatingRowColors(True)
        self.tabla_productos.setSelectionBehavior(QTableWidget.SelectRows)
        self.tabla_productos.itemDoubleClicked.connect(self.editar_producto_stock)
        
        layout.addWidget(self.tabla_productos)
        
        self.tab_widget.addTab(tab, "📊 Vista General")
        
    def crear_tab_movimientos(self):
        """Crear tab de movimientos de inventario"""
        tab = QWidget()
        layout = QVBoxLayout(tab)
        layout.setSpacing(15)
        
        # Panel de controles
        controles_layout = QHBoxLayout()
        
        # Filtros
        controles_layout.addWidget(QLabel("Producto:"))
        self.filtro_producto = QComboBox()
        self.filtro_producto.addItem("Todos los productos")
        controles_layout.addWidget(self.filtro_producto)
        
        controles_layout.addWidget(QLabel("Desde:"))
        self.fecha_desde = QDateEdit()
        self.fecha_desde.setDate(QDate.currentDate().addDays(-30))
        controles_layout.addWidget(self.fecha_desde)
        
        controles_layout.addWidget(QLabel("Hasta:"))
        self.fecha_hasta = QDateEdit()
        self.fecha_hasta.setDate(QDate.currentDate())
        controles_layout.addWidget(self.fecha_hasta)
        
        # Botón filtrar
        self.btn_filtrar = QPushButton("🔍 Filtrar")
        self.btn_filtrar.clicked.connect(self.filtrar_movimientos)
        controles_layout.addWidget(self.btn_filtrar)
        
        # Botón nuevo movimiento
        self.btn_nuevo_movimiento = QPushButton("➕ Nuevo Movimiento")
        self.btn_nuevo_movimiento.clicked.connect(self.abrir_nuevo_movimiento)
        controles_layout.addWidget(self.btn_nuevo_movimiento)
        
        controles_layout.addStretch()
        layout.addLayout(controles_layout)
        
        # Tabla de movimientos
        self.tabla_movimientos = QTableWidget()
        self.tabla_movimientos.setColumnCount(8)
        self.tabla_movimientos.setHorizontalHeaderLabels([
            "Fecha", "Producto", "Tipo", "Cantidad", "Motivo", 
            "Usuario", "Documento", "Acciones"
        ])
        
        # Configurar tabla
        header = self.tabla_movimientos.horizontalHeader()
        header.setSectionResizeMode(0, QHeaderView.ResizeToContents)  # Fecha
        header.setSectionResizeMode(1, QHeaderView.Stretch)          # Producto
        header.setSectionResizeMode(2, QHeaderView.ResizeToContents) # Tipo
        header.setSectionResizeMode(3, QHeaderView.ResizeToContents) # Cantidad
        header.setSectionResizeMode(4, QHeaderView.Stretch)          # Motivo
        header.setSectionResizeMode(5, QHeaderView.ResizeToContents) # Usuario
        header.setSectionResizeMode(6, QHeaderView.ResizeToContents) # Documento
        header.setSectionResizeMode(7, QHeaderView.ResizeToContents) # Acciones
        
        self.tabla_movimientos.setAlternatingRowColors(True)
        self.tabla_movimientos.setSelectionBehavior(QTableWidget.SelectRows)
        
        layout.addWidget(self.tabla_movimientos)
        
        self.tab_widget.addTab(tab, "📋 Movimientos")
        
    def crear_tab_alertas(self):
        """Crear tab de alertas de inventario"""
        tab = QWidget()
        layout = QVBoxLayout(tab)
        layout.setSpacing(15)
        
        # Panel de configuración
        config_layout = QHBoxLayout()
        
        config_layout.addWidget(QLabel("Límite de inventario:"))
        self.limite_stock = QSpinBox()
        self.limite_stock.setRange(0, 1000)
        self.limite_stock.setValue(10)
        config_layout.addWidget(self.limite_stock)
        
        self.btn_actualizar_alertas = QPushButton("🔄 Actualizar Alertas")
        self.btn_actualizar_alertas.clicked.connect(self.actualizar_alertas)
        config_layout.addWidget(self.btn_actualizar_alertas)
        
        config_layout.addStretch()
        layout.addLayout(config_layout)
        
        # Tabla de alertas
        self.tabla_alertas = QTableWidget()
        self.tabla_alertas.setColumnCount(7)
        self.tabla_alertas.setHorizontalHeaderLabels([
            "Producto", "SKU", "Inventario actual", "Inventario mínimo", 
            "Diferencia", "Precio de venta", "Acción"
        ])
        
        # Configurar tabla
        header = self.tabla_alertas.horizontalHeader()
        header.setSectionResizeMode(0, QHeaderView.Stretch)          # Producto
        header.setSectionResizeMode(1, QHeaderView.ResizeToContents) # SKU
        header.setSectionResizeMode(2, QHeaderView.ResizeToContents) # Stock Actual
        header.setSectionResizeMode(3, QHeaderView.ResizeToContents) # Stock Mínimo
        header.setSectionResizeMode(4, QHeaderView.ResizeToContents) # Diferencia
        header.setSectionResizeMode(5, QHeaderView.ResizeToContents) # Precio Venta
        header.setSectionResizeMode(6, QHeaderView.ResizeToContents) # Acción
        
        self.tabla_alertas.setAlternatingRowColors(True)
        self.tabla_alertas.setSelectionBehavior(QTableWidget.SelectRows)
        
        layout.addWidget(self.tabla_alertas)
        
        self.tab_widget.addTab(tab, "⚠️ Alertas")
        
    def crear_tab_estadisticas(self):
        """Crear tab de estadísticas de inventario"""
        tab = QWidget()
        layout = QVBoxLayout(tab)
        layout.setSpacing(15)
        
        # Panel de métricas
        metrics_layout = QGridLayout()
        
        # Crear tarjetas de métricas
        self.metricas = {}
        metricas_info = [
            ("total_productos", "Total de productos", "📦"),
            ("stock_total", "Inventario total", "📊"),
            ("valor_inventario", "Valor del inventario", "💰"),
            ("productos_stock_bajo", "Inventario bajo", "⚠️"),
            ("productos_sin_stock", "Sin inventario", "❌"),
            ("promedio_stock", "Inventario promedio", "📈")
        ]
        
        for i, (key, label, icon) in enumerate(metricas_info):
            metric_widget = self.crear_metrica_widget(icon, label, "0")
            self.metricas[key] = metric_widget
            row = i // 3
            col = i % 3
            metrics_layout.addWidget(metric_widget, row, col)
        
        layout.addLayout(metrics_layout)
        
        # Gráfico de distribución de stock (placeholder)
        chart_group = QGroupBox("Distribución del inventario")
        chart_layout = QVBoxLayout(chart_group)
        
        chart_placeholder = QLabel("📊 Gráfico de distribución del inventario\n(Pendiente de implementar)")
        chart_placeholder.setAlignment(Qt.AlignCenter)
        chart_placeholder.setStyleSheet("""
            background: #f8f9fa;
            border: 2px dashed #bdc3c7;
            border-radius: 8px;
            padding: 40px;
            color: #7f8c8d;
            font-size: 16px;
        """)
        chart_layout.addWidget(chart_placeholder)
        
        layout.addWidget(chart_group)
        
        self.tab_widget.addTab(tab, "📈 Estadísticas")
        
    def crear_metrica_widget(self, icon, titulo, valor):
        """Crear widget de métrica"""
        widget = QFrame()
        widget.setFrameStyle(QFrame.Box)
        widget.setStyleSheet("""
            QFrame {
                background: #ffffff;
                border: 2px solid #bdc3c7;
                border-radius: 8px;
                padding: 15px;
            }
        """)
        
        layout = QVBoxLayout(widget)
        layout.setSpacing(5)
        
        # Icono y título
        header_layout = QHBoxLayout()
        icon_label = QLabel(icon)
        icon_label.setStyleSheet("font-size: 24px;")
        header_layout.addWidget(icon_label)
        
        titulo_label = QLabel(titulo)
        titulo_label.setStyleSheet("""
            font-size: 14px;
            font-weight: bold;
            color: #2c3e50;
        """)
        header_layout.addWidget(titulo_label)
        header_layout.addStretch()
        
        layout.addLayout(header_layout)
        
        # Valor
        valor_label = QLabel(valor)
        valor_label.setStyleSheet("""
            font-size: 24px;
            font-weight: bold;
            color: #27ae60;
        """)
        valor_label.setAlignment(Qt.AlignCenter)
        layout.addWidget(valor_label)
        
        return widget
        
    def aplicar_estilos(self):
        """Aplicar estilos generales"""
        self.setStyleSheet("""
            QMainWindow {
                background: #f5f5f5;
                font-size: 9px;
            }
            QTableWidget {
                background: white;
                border: 1px solid #d0d0d0;
                border-radius: 6px;
                gridline-color: #e0e0e0;
                selection-background-color: #0078d4;
                selection-color: white;
                font-size: 9px;
                alternate-background-color: #f9f9f9;
            }
            QTableWidget::item {
                padding: 5px;
                border-bottom: 1px solid #e0e0e0;
                font-size: 9px;
                color: #333333;
            }
            QTableWidget::item:selected {
                background: #0078d4;
                color: white;
            }
            QTableWidget::item:alternate {
                background-color: #f9f9f9;
                color: #333333;
            }
            QHeaderView::section {
                background: #0078d4;
                color: white;
                font-weight: bold;
                padding: 8px;
                border: none;
                font-size: 9px;
                border-right: 1px solid #005a9e;
            }
            QHeaderView::section:first {
                border-top-left-radius: 6px;
            }
            QHeaderView::section:last {
                border-top-right-radius: 6px;
                border-right: none;
            }
            QPushButton {
                background: #0078d4;
                color: white;
                border: none;
                padding: 8px 16px;
                border-radius: 4px;
                font-weight: bold;
                font-size: 9px;
            }
            QPushButton:hover {
                background: #106ebe;
            }
            QPushButton:pressed {
                background: #005a9e;
            }
            QPushButton:disabled {
                background: #cccccc;
                color: #666666;
            }
            QLineEdit, QComboBox, QSpinBox, QDateEdit {
                padding: 6px;
                border: 1px solid #d0d0d0;
                border-radius: 4px;
                background: white;
                font-size: 9px;
                color: #333333;
            }
            QLineEdit:focus, QComboBox:focus, QSpinBox:focus, QDateEdit:focus {
                border-color: #0078d4;
                border-width: 2px;
            }
            QGroupBox {
                font-weight: bold;
                border: 1px solid #d0d0d0;
                border-radius: 6px;
                margin-top: 10px;
                padding-top: 10px;
                font-size: 9px;
                color: #333333;
            }
            QGroupBox::title {
                subcontrol-origin: margin;
                left: 10px;
                padding: 0 8px 0 8px;
                background: #f5f5f5;
                font-size: 9px;
                color: #333333;
            }
            QLabel {
                font-size: 9px;
                color: #333333;
            }
            QTabWidget::pane {
                border: 1px solid #d0d0d0;
                border-radius: 6px;
                background: white;
            }
            QTabBar::tab {
                background: #f0f0f0;
                color: #333333;
                padding: 8px 16px;
                margin-right: 2px;
                border-top-left-radius: 6px;
                border-top-right-radius: 6px;
                font-size: 9px;
            }
            QTabBar::tab:selected {
                background: white;
                color: #0078d4;
                font-weight: bold;
            }
            QTabBar::tab:hover {
                background: #e0e0e0;
            }
        """)
        
    def cargar_datos(self):
        """Cargar datos iniciales"""
        self.cargar_productos()
        self.cargar_movimientos()
        self.cargar_alertas()
        self.cargar_estadisticas()
        
    def cargar_productos(self):
        """Cargar productos en la tabla"""
        try:
            productos = self.db.get_productos_con_stock()
            self.tabla_productos.setRowCount(len(productos))
            
            for i, producto in enumerate(productos):
                # ID
                self.tabla_productos.setItem(i, 0, QTableWidgetItem(str(producto[0])))
                
                # Producto (Descripción Larga)
                self.tabla_productos.setItem(i, 1, QTableWidgetItem(producto[11] or ""))
                
                # SKU
                self.tabla_productos.setItem(i, 2, QTableWidgetItem(producto[2] or ""))
                
                # Stock Actual
                stock_actual = producto[6] or 0
                stock_item = QTableWidgetItem(str(stock_actual))
                if stock_actual <= 0:
                    stock_item.setBackground(QColor(255, 200, 200))  # Rojo suave
                    stock_item.setForeground(QColor(200, 0, 0))  # Texto rojo oscuro
                elif stock_actual <= (producto[7] or 0):
                    stock_item.setBackground(QColor(255, 255, 200))  # Amarillo suave
                    stock_item.setForeground(QColor(150, 100, 0))  # Texto marrón
                else:
                    stock_item.setBackground(QColor(200, 255, 200))  # Verde suave
                    stock_item.setForeground(QColor(0, 100, 0))  # Texto verde oscuro
                self.tabla_productos.setItem(i, 3, stock_item)
                
                # Stock Mínimo
                self.tabla_productos.setItem(i, 4, QTableWidgetItem(str(producto[7] or 0)))
                
                # Precio Venta
                precio = producto[8] or 0
                self.tabla_productos.setItem(i, 5, QTableWidgetItem(f"S/ {precio:.2f}"))
                
                # Costo Promedio
                costo = producto[9] or 0
                self.tabla_productos.setItem(i, 6, QTableWidgetItem(f"S/ {costo:.2f}"))
                
                # Estado
                tipo_estado = producto[10] if len(producto) > 10 else 'activo'
                estado_item = QTableWidgetItem(tipo_estado.upper())
                if tipo_estado == 'activo':
                    estado_item.setBackground(QColor(200, 255, 200))  # Verde suave
                    estado_item.setForeground(QColor(0, 100, 0))  # Texto verde oscuro
                elif tipo_estado == 'inactivo':
                    estado_item.setBackground(QColor(255, 200, 200))  # Rojo suave
                    estado_item.setForeground(QColor(200, 0, 0))  # Texto rojo oscuro
                else:  # descontinuado
                    estado_item.setBackground(QColor(255, 255, 200))  # Amarillo suave
                    estado_item.setForeground(QColor(150, 100, 0))  # Texto marrón
                self.tabla_productos.setItem(i, 7, estado_item)
                
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error cargando productos: {str(e)}")
            
    def cargar_movimientos(self):
        """Cargar movimientos en la tabla"""
        try:
            movimientos = self.db.get_movimientos_stock(limit=50)
            self.tabla_movimientos.setRowCount(len(movimientos))
            
            for i, movimiento in enumerate(movimientos):
                # Fecha
                fecha = movimiento[3].strftime("%d/%m/%Y %H:%M") if movimiento[3] else ""
                self.tabla_movimientos.setItem(i, 0, QTableWidgetItem(fecha))
                
                # Producto
                self.tabla_movimientos.setItem(i, 1, QTableWidgetItem(movimiento[7] or ""))
                
                # Tipo
                tipo = movimiento[1]
                tipo_item = QTableWidgetItem(tipo.upper())
                if tipo == 'entrada':
                    tipo_item.setBackground(QColor(200, 255, 200))  # Verde suave
                    tipo_item.setForeground(QColor(0, 100, 0))  # Texto verde oscuro
                elif tipo == 'salida':
                    tipo_item.setBackground(QColor(255, 200, 200))  # Rojo suave
                    tipo_item.setForeground(QColor(200, 0, 0))  # Texto rojo oscuro
                else:
                    tipo_item.setBackground(QColor(255, 255, 200))  # Amarillo suave
                    tipo_item.setForeground(QColor(150, 100, 0))  # Texto marrón
                self.tabla_movimientos.setItem(i, 2, tipo_item)
                
                # Cantidad
                self.tabla_movimientos.setItem(i, 3, QTableWidgetItem(str(movimiento[2])))
                
                # Motivo
                self.tabla_movimientos.setItem(i, 4, QTableWidgetItem(movimiento[4] or ""))
                
                # Usuario
                self.tabla_movimientos.setItem(i, 5, QTableWidgetItem(movimiento[6] or ""))
                
                # Documento
                self.tabla_movimientos.setItem(i, 6, QTableWidgetItem(movimiento[5] or ""))
                
                # Acciones
                btn_ver = QPushButton("Ver")
                btn_ver.clicked.connect(lambda checked, row=i: self.ver_movimiento(row))
                self.tabla_movimientos.setCellWidget(i, 7, btn_ver)
                
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error cargando movimientos: {str(e)}")
            
    def cargar_alertas(self):
        """Cargar alertas de inventario"""
        try:
            limite = self.limite_stock.value()
            alertas = self.db.get_productos_stock_bajo_avanzado(limite)
            self.tabla_alertas.setRowCount(len(alertas))
            
            for i, alerta in enumerate(alertas):
                # Producto
                self.tabla_alertas.setItem(i, 0, QTableWidgetItem(alerta[1] or ""))
                
                # SKU
                self.tabla_alertas.setItem(i, 1, QTableWidgetItem(alerta[2] or ""))
                
                # Stock Actual
                stock_actual = alerta[3] or 0
                stock_item = QTableWidgetItem(str(stock_actual))
                if stock_actual == 0:
                    stock_item.setBackground(QColor(255, 200, 200))  # Rojo suave
                    stock_item.setForeground(QColor(200, 0, 0))  # Texto rojo oscuro
                else:
                    stock_item.setBackground(QColor(255, 255, 200))  # Amarillo suave
                    stock_item.setForeground(QColor(150, 100, 0))  # Texto marrón
                self.tabla_alertas.setItem(i, 2, stock_item)
                
                # Stock Mínimo
                self.tabla_alertas.setItem(i, 3, QTableWidgetItem(str(alerta[4] or 0)))
                
                # Diferencia
                diferencia = alerta[7] or 0
                diff_item = QTableWidgetItem(str(diferencia))
                if diferencia < 0:
                    diff_item.setBackground(QColor(255, 200, 200))  # Rojo suave
                    diff_item.setForeground(QColor(200, 0, 0))  # Texto rojo oscuro
                else:
                    diff_item.setBackground(QColor(255, 255, 200))  # Amarillo suave
                    diff_item.setForeground(QColor(150, 100, 0))  # Texto marrón
                self.tabla_alertas.setItem(i, 4, diff_item)
                
                # Precio Venta
                precio = alerta[5] or 0
                self.tabla_alertas.setItem(i, 5, QTableWidgetItem(f"S/ {precio:.2f}"))
                
                # Acción
                btn_reponer = QPushButton("Reponer")
                btn_reponer.clicked.connect(lambda checked, row=i: self.reponer_stock(row))
                self.tabla_alertas.setCellWidget(i, 6, btn_reponer)
                
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error cargando alertas: {str(e)}")
            
    def cargar_estadisticas(self):
        """Cargar estadísticas de inventario"""
        try:
            stats = self.db.get_estadisticas_stock()
            
            # Actualizar métricas
            self.metricas['total_productos'].findChild(QLabel, None).setText(str(stats['total_productos']))
            self.metricas['stock_total'].findChild(QLabel, None).setText(str(int(stats['stock_total'])))
            self.metricas['valor_inventario'].findChild(QLabel, None).setText(f"S/ {stats['valor_inventario']:.2f}")
            self.metricas['productos_stock_bajo'].findChild(QLabel, None).setText(str(stats['productos_stock_bajo']))
            self.metricas['productos_sin_stock'].findChild(QLabel, None).setText(str(stats['productos_sin_stock']))
            self.metricas['promedio_stock'].findChild(QLabel, None).setText(f"{stats['promedio_stock']:.1f}")
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error cargando estadísticas: {str(e)}")
            
    def filtrar_productos(self):
        """Filtrar productos por búsqueda"""
        texto = self.busqueda_input.text().lower()
        for i in range(self.tabla_productos.rowCount()):
            producto = self.tabla_productos.item(i, 1).text().lower()
            sku = self.tabla_productos.item(i, 2).text().lower()
            visible = texto in producto or texto in sku
            self.tabla_productos.setRowHidden(i, not visible)
            
    def filtrar_movimientos(self):
        """Filtrar movimientos por criterios"""
        # Implementar filtrado de movimientos
        self.cargar_movimientos()
        
    def actualizar_alertas(self):
        """Actualizar alertas de inventario"""
        self.cargar_alertas()
        
    def abrir_movimiento_rapido(self):
        """Abrir diálogo de movimiento rápido"""
        # Implementar diálogo de movimiento rápido
        QMessageBox.information(self, "Movimiento Rápido", "Funcionalidad en desarrollo")
        
    def abrir_nuevo_movimiento(self):
        """Abrir diálogo de nuevo movimiento"""
        # Implementar diálogo de nuevo movimiento
        QMessageBox.information(self, "Nuevo Movimiento", "Funcionalidad en desarrollo")
        
    def editar_producto_stock(self, item):
        """Editar stock de un producto"""
        row = item.row()
        producto_id = self.tabla_productos.item(row, 0).text()
        # Implementar edición de stock
        QMessageBox.information(self, "Editar inventario", f"Editar inventario del producto ID: {producto_id}")
        
    def ver_movimiento(self, row):
        """Ver detalles de un movimiento"""
        # Implementar vista de detalles de movimiento
        QMessageBox.information(self, "Ver Movimiento", f"Ver movimiento de la fila: {row}")
        
    def reponer_stock(self, row):
        """Reponer stock de un producto"""
        # Implementar reposición de stock
        QMessageBox.information(self, "Reponer inventario", f"Reponer inventario de la fila: {row}")
        
    def closeEvent(self, event):
        """Manejar cierre de la ventana"""
        event.accept()

if __name__ == "__main__":
    app = QApplication(sys.argv)
    # Para testing
    window = StockAvanzadoForm(None, None)
    window.show()
    sys.exit(app.exec())
