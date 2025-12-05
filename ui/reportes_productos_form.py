import sys
from datetime import datetime, timedelta
from PySide6.QtWidgets import (
    QMainWindow, QWidget, QVBoxLayout, QHBoxLayout, QTabWidget,
    QLabel, QPushButton, QTableWidget, QTableWidgetItem, QDateEdit,
    QComboBox, QSpinBox, QGroupBox, QGridLayout, QMessageBox,
    QHeaderView, QSplitter, QFrame
)
from PySide6.QtCore import Qt, QDate
from PySide6.QtGui import QFont

# Importar matplotlib para gráficos
import matplotlib.pyplot as plt
import matplotlib.dates as mdates
from matplotlib.backends.backend_qt5agg import FigureCanvasQTAgg as FigureCanvas
from matplotlib.figure import Figure
import numpy as np

class GraficosCanvas(FigureCanvas):
    """Canvas para mostrar gráficos de productos"""
    
    def __init__(self, parent=None, width=5, height=4, dpi=100):
        self.fig = Figure(figsize=(width, height), dpi=dpi)
        super().__init__(self.fig)
        self.setParent(parent)
        
    def limpiar(self):
        """Limpiar el gráfico"""
        self.fig.clear()
        self.draw()
    
    def grafico_barras_top_productos(self, productos, titulo="Top Productos"):
        """Crear gráfico de barras para top productos"""
        self.fig.clear()
        ax = self.fig.add_subplot(111)
        
        nombres = [p[1][:20] + "..." if len(p[1]) > 20 else p[1] for p in productos[:10]]
        cantidades = [p[6] for p in productos[:10]]  # total_cantidad
        
        bars = ax.bar(range(len(nombres)), cantidades, color='#2c3e50', alpha=0.8)
        ax.set_title(titulo, fontsize=12, fontweight='bold')
        ax.set_xlabel('Productos', fontsize=10)
        ax.set_ylabel('Cantidad Vendida', fontsize=10)
        ax.set_xticks(range(len(nombres)))
        ax.set_xticklabels(nombres, rotation=45, ha='right')
        
        # Agregar valores en las barras
        for bar, valor in zip(bars, cantidades):
            ax.text(bar.get_x() + bar.get_width()/2, bar.get_height() + 0.1,
                   f'{valor:.0f}', ha='center', va='bottom', fontsize=9)
        
        self.fig.tight_layout()
        self.draw()
    
    def grafico_torta_stock(self, estadisticas):
        """Crear gráfico de torta para distribución de stock"""
        self.fig.clear()
        ax = self.fig.add_subplot(111)
        
        labels = ['Stock Normal', 'Stock Bajo', 'Sin Stock']
        sizes = [
            estadisticas.get('productos_stock_normal', 0),
            estadisticas.get('productos_stock_bajo', 0),
            estadisticas.get('productos_sin_stock', 0)
        ]
        colors = ['#27ae60', '#f39c12', '#c0392b']
        
        # Filtrar valores cero
        non_zero_data = [(label, size, color) for label, size, color in zip(labels, sizes, colors) if size > 0]
        if non_zero_data:
            labels, sizes, colors = zip(*non_zero_data)
            
            wedges, texts, autotexts = ax.pie(sizes, labels=labels, colors=colors, autopct='%1.1f%%',
                                            startangle=90, textprops={'fontsize': 9})
            ax.set_title('Distribución de Stock', fontsize=12, fontweight='bold')
        
        self.fig.tight_layout()
        self.draw()
    
    def grafico_rentabilidad(self, productos):
        """Crear gráfico de barras para rentabilidad"""
        self.fig.clear()
        ax = self.fig.add_subplot(111)
        
        nombres = [p[1][:15] + "..." if len(p[1]) > 15 else p[1] for p in productos[:8]]
        margenes = [p[10] for p in productos[:8]]  # margen_porcentaje
        
        colors = ['#27ae60' if m >= 50 else '#f39c12' if m >= 20 else '#c0392b' for m in margenes]
        bars = ax.barh(range(len(nombres)), margenes, color=colors, alpha=0.7)
        ax.set_title('Productos Más Rentables', fontsize=12, fontweight='bold')
        ax.set_xlabel('Margen de Ganancia (%)', fontsize=10)
        ax.set_yticks(range(len(nombres)))
        ax.set_yticklabels(nombres)
        
        # Agregar valores en las barras
        for bar, valor in zip(bars, margenes):
            ax.text(bar.get_width() + 1, bar.get_y() + bar.get_height()/2,
                   f'{valor:.1f}%', ha='left', va='center', fontsize=9)
        
        self.fig.tight_layout()
        self.draw()

class ReportesProductosForm(QMainWindow):
    """Formulario principal para reportes de productos"""
    
    def __init__(self, db_client, usuario_actual):
        super().__init__()
        self.db = db_client
        self.usuario_actual = usuario_actual
        self.init_ui()
        
    def init_ui(self):
        """Inicializar la interfaz de usuario"""
        self.setWindowTitle("📊 Reportes de Productos")
        self.setMinimumSize(1200, 800)
        
        # Widget central
        central_widget = QWidget()
        self.setCentralWidget(central_widget)
        
        # Layout principal
        main_layout = QVBoxLayout(central_widget)
        
        # Título
        titulo = QLabel("📊 Reportes de Productos")
        titulo.setFont(QFont("Arial", 16, QFont.Bold))
        titulo.setAlignment(Qt.AlignmentFlag.AlignCenter)
        titulo.setStyleSheet("color: #2c3e50; margin: 10px;")
        main_layout.addWidget(titulo)
        
        # Crear pestañas
        self.tab_widget = QTabWidget()
        main_layout.addWidget(self.tab_widget)
        
        # Crear las pestañas
        self.create_tab_mas_vendidos()
        self.create_tab_stock_bajo()
        self.create_tab_rentabilidad()
        self.create_tab_rotacion()
        self.create_tab_estadisticas()
        
        # Aplicar estilos
        self.apply_styles()
        
    def apply_styles(self):
        """Aplicar estilos CSS"""
        self.setStyleSheet("""
            QMainWindow {
                background-color: #f5f5f5;
            }
            QTabWidget::pane {
                border: 2px solid #2c3e50;
                background-color: white;
                border-radius: 8px;
            }
            QTabBar::tab {
                background-color: #34495e;
                color: white;
                padding: 12px 20px;
                margin-right: 3px;
                border-top-left-radius: 6px;
                border-top-right-radius: 6px;
                font-weight: bold;
                font-size: 11px;
            }
            QTabBar::tab:selected {
                background-color: #2c3e50;
                border-bottom: 3px solid #3498db;
            }
            QTabBar::tab:hover {
                background-color: #2c3e50;
            }
            QTableWidget {
                gridline-color: #bdc3c7;
                background-color: white;
                alternate-background-color: #ecf0f1;
                border: 1px solid #bdc3c7;
                border-radius: 5px;
            }
            QTableWidget::item {
                padding: 10px;
                border-bottom: 1px solid #bdc3c7;
                color: #2c3e50;
                font-size: 10px;
            }
            QTableWidget::item:selected {
                background-color: #3498db;
                color: white;
            }
            QTableWidget::item:alternate {
                background-color: #ecf0f1;
                color: #2c3e50;
            }
            QHeaderView::section {
                background-color: #2c3e50;
                color: white;
                padding: 12px;
                border: 1px solid #34495e;
                font-weight: bold;
                font-size: 11px;
                text-align: center;
            }
            QPushButton {
                background-color: #3498db;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 6px;
                font-weight: bold;
                font-size: 11px;
            }
            QPushButton:hover {
                background-color: #2980b9;
            }
            QPushButton:pressed {
                background-color: #21618c;
            }
            QGroupBox {
                font-weight: bold;
                font-size: 12px;
                color: #2c3e50;
                border: 2px solid #34495e;
                border-radius: 8px;
                margin-top: 15px;
                padding-top: 15px;
                background-color: #ecf0f1;
            }
            QGroupBox::title {
                subcontrol-origin: margin;
                left: 15px;
                padding: 0 8px 0 8px;
                color: #2c3e50;
                font-size: 12px;
            }
            QLabel {
                color: #2c3e50;
                font-weight: bold;
                font-size: 10px;
            }
            QDateEdit, QComboBox, QSpinBox {
                padding: 8px;
                border: 2px solid #bdc3c7;
                border-radius: 4px;
                background-color: white;
                color: #2c3e50;
                font-size: 10px;
            }
            QDateEdit:focus, QComboBox:focus, QSpinBox:focus {
                border-color: #3498db;
            }
        """)
    
    def create_tab_mas_vendidos(self):
        """Crear pestaña de productos más vendidos"""
        tab = QWidget()
        layout = QVBoxLayout(tab)
        
        # Controles de filtro
        filtros_group = QGroupBox("Filtros")
        filtros_layout = QGridLayout(filtros_group)
        
        # Fechas
        filtros_layout.addWidget(QLabel("Fecha Desde:"), 0, 0)
        self.fecha_desde_mv = QDateEdit()
        self.fecha_desde_mv.setDate(QDate.currentDate().addDays(-30))
        filtros_layout.addWidget(self.fecha_desde_mv, 0, 1)
        
        filtros_layout.addWidget(QLabel("Fecha Hasta:"), 0, 2)
        self.fecha_hasta_mv = QDateEdit()
        self.fecha_hasta_mv.setDate(QDate.currentDate())
        filtros_layout.addWidget(self.fecha_hasta_mv, 0, 3)
        
        # Top N
        filtros_layout.addWidget(QLabel("Top:"), 1, 0)
        self.top_n_mv = QSpinBox()
        self.top_n_mv.setRange(5, 100)
        self.top_n_mv.setValue(10)
        filtros_layout.addWidget(self.top_n_mv, 1, 1)
        
        # Ordenar por
        filtros_layout.addWidget(QLabel("Ordenar por:"), 1, 2)
        self.orden_por_mv = QComboBox()
        self.orden_por_mv.addItems(["Cantidad", "Ingresos"])
        filtros_layout.addWidget(self.orden_por_mv, 1, 3)
        
        # Botón generar
        self.btn_generar_mv = QPushButton("📊 Generar Reporte")
        self.btn_generar_mv.clicked.connect(self.generar_reporte_mas_vendidos)
        filtros_layout.addWidget(self.btn_generar_mv, 2, 0, 1, 4)
        
        layout.addWidget(filtros_group)
        
        # Splitter para tabla y gráfico
        splitter = QSplitter(Qt.Orientation.Horizontal)
        
        # Tabla
        self.tabla_mv = QTableWidget()
        self.tabla_mv.setColumnCount(8)
        self.tabla_mv.setHorizontalHeaderLabels([
            "Producto", "Marca", "Código Barra", "Cantidad", 
            "Ingresos", "Ventas", "Precio Prom.", "Stock"
        ])
        self.tabla_mv.horizontalHeader().setSectionResizeMode(1, QHeaderView.Stretch)
        splitter.addWidget(self.tabla_mv)
        
        # Gráfico
        self.grafico_mv = GraficosCanvas(self, width=6, height=4)
        splitter.addWidget(self.grafico_mv)
        
        splitter.setSizes([600, 400])
        layout.addWidget(splitter)
        
        self.tab_widget.addTab(tab, "🏆 Más Vendidos")
    
    def create_tab_stock_bajo(self):
        """Crear pestaña de productos con stock bajo"""
        tab = QWidget()
        layout = QVBoxLayout(tab)
        
        # Controles de filtro
        filtros_group = QGroupBox("Filtros")
        filtros_layout = QGridLayout(filtros_group)
        
        # Stock mínimo personalizado
        filtros_layout.addWidget(QLabel("Stock Mínimo:"), 0, 0)
        self.stock_minimo_sb = QSpinBox()
        self.stock_minimo_sb.setRange(0, 1000)
        self.stock_minimo_sb.setValue(0)
        self.stock_minimo_sb.setSpecialValueText("Usar configurado")
        filtros_layout.addWidget(self.stock_minimo_sb, 0, 1)
        
        # Botón generar
        self.btn_generar_sb = QPushButton("📊 Generar Reporte")
        self.btn_generar_sb.clicked.connect(self.generar_reporte_stock_bajo)
        filtros_layout.addWidget(self.btn_generar_sb, 1, 0, 1, 2)
        
        layout.addWidget(filtros_group)
        
        # Splitter para tabla y gráfico
        splitter = QSplitter(Qt.Orientation.Horizontal)
        
        # Tabla
        self.tabla_sb = QTableWidget()
        self.tabla_sb.setColumnCount(8)
        self.tabla_sb.setHorizontalHeaderLabels([
            "Producto", "Marca", "Stock Actual", "Stock Mín.", 
            "Estado", "Precio Venta", "Costo", "Diferencia"
        ])
        self.tabla_sb.horizontalHeader().setSectionResizeMode(0, QHeaderView.Stretch)
        splitter.addWidget(self.tabla_sb)
        
        # Gráfico
        self.grafico_sb = GraficosCanvas(self, width=6, height=4)
        splitter.addWidget(self.grafico_sb)
        
        splitter.setSizes([600, 400])
        layout.addWidget(splitter)
        
        self.tab_widget.addTab(tab, "⚠️ Stock Bajo")
    
    def create_tab_rentabilidad(self):
        """Crear pestaña de rentabilidad"""
        tab = QWidget()
        layout = QVBoxLayout(tab)
        
        # Controles de filtro
        filtros_group = QGroupBox("Filtros")
        filtros_layout = QGridLayout(filtros_group)
        
        # Fechas
        filtros_layout.addWidget(QLabel("Fecha Desde:"), 0, 0)
        self.fecha_desde_rt = QDateEdit()
        self.fecha_desde_rt.setDate(QDate.currentDate().addDays(-30))
        filtros_layout.addWidget(self.fecha_desde_rt, 0, 1)
        
        filtros_layout.addWidget(QLabel("Fecha Hasta:"), 0, 2)
        self.fecha_hasta_rt = QDateEdit()
        self.fecha_hasta_rt.setDate(QDate.currentDate())
        filtros_layout.addWidget(self.fecha_hasta_rt, 0, 3)
        
        # Top N
        filtros_layout.addWidget(QLabel("Top:"), 1, 0)
        self.top_n_rt = QSpinBox()
        self.top_n_rt.setRange(5, 50)
        self.top_n_rt.setValue(10)
        filtros_layout.addWidget(self.top_n_rt, 1, 1)
        
        # Botón generar
        self.btn_generar_rt = QPushButton("📊 Generar Reporte")
        self.btn_generar_rt.clicked.connect(self.generar_reporte_rentabilidad)
        filtros_layout.addWidget(self.btn_generar_rt, 2, 0, 1, 4)
        
        layout.addWidget(filtros_group)
        
        # Splitter para tabla y gráfico
        splitter = QSplitter(Qt.Orientation.Horizontal)
        
        # Tabla
        self.tabla_rt = QTableWidget()
        self.tabla_rt.setColumnCount(9)
        self.tabla_rt.setHorizontalHeaderLabels([
            "Producto", "Marca", "Cantidad", "Ingresos", 
            "Costos", "Ganancia", "Margen %", "Precio Prom.", "Costo Prom."
        ])
        self.tabla_rt.horizontalHeader().setSectionResizeMode(0, QHeaderView.Stretch)
        splitter.addWidget(self.tabla_rt)
        
        # Gráfico
        self.grafico_rt = GraficosCanvas(self, width=6, height=4)
        splitter.addWidget(self.grafico_rt)
        
        splitter.setSizes([600, 400])
        layout.addWidget(splitter)
        
        self.tab_widget.addTab(tab, "💰 Rentabilidad")
    
    def create_tab_rotacion(self):
        """Crear pestaña de análisis de rotación"""
        tab = QWidget()
        layout = QVBoxLayout(tab)
        
        # Controles de filtro
        filtros_group = QGroupBox("Filtros")
        filtros_layout = QGridLayout(filtros_group)
        
        # Fechas
        filtros_layout.addWidget(QLabel("Fecha Desde:"), 0, 0)
        self.fecha_desde_ro = QDateEdit()
        self.fecha_desde_ro.setDate(QDate.currentDate().addDays(-30))
        filtros_layout.addWidget(self.fecha_desde_ro, 0, 1)
        
        filtros_layout.addWidget(QLabel("Fecha Hasta:"), 0, 2)
        self.fecha_hasta_ro = QDateEdit()
        self.fecha_hasta_ro.setDate(QDate.currentDate())
        filtros_layout.addWidget(self.fecha_hasta_ro, 0, 3)
        
        # Botón generar
        self.btn_generar_ro = QPushButton("📊 Generar Reporte")
        self.btn_generar_ro.clicked.connect(self.generar_reporte_rotacion)
        filtros_layout.addWidget(self.btn_generar_ro, 1, 0, 1, 4)
        
        layout.addWidget(filtros_group)
        
        # Tabla
        self.tabla_ro = QTableWidget()
        self.tabla_ro.setColumnCount(10)
        self.tabla_ro.setHorizontalHeaderLabels([
            "Producto", "Marca", "Stock", "Vendido", 
            "Días Venta", "Prom. Diario", "Días Inv.", "Rotación", "Estado", "Prioridad"
        ])
        self.tabla_ro.horizontalHeader().setSectionResizeMode(0, QHeaderView.Stretch)
        layout.addWidget(self.tabla_ro)
        
        self.tab_widget.addTab(tab, "🔄 Rotación")
    
    def create_tab_estadisticas(self):
        """Crear pestaña de estadísticas generales"""
        tab = QWidget()
        layout = QVBoxLayout(tab)
        
        # Botón actualizar
        self.btn_actualizar_est = QPushButton("🔄 Actualizar Estadísticas")
        self.btn_actualizar_est.clicked.connect(self.actualizar_estadisticas)
        layout.addWidget(self.btn_actualizar_est)
        
        # Splitter para estadísticas y gráfico
        splitter = QSplitter(Qt.Orientation.Horizontal)
        
        # Estadísticas
        stats_widget = QWidget()
        stats_layout = QGridLayout(stats_widget)
        
        # Crear labels para estadísticas
        self.labels_stats = {}
        stats_labels = [
            ("Total Productos", "total_productos"),
            ("Productos Activos", "productos_activos"),
            ("Sin Stock", "productos_sin_stock"),
            ("Stock Bajo", "productos_stock_bajo"),
            ("Stock Normal", "productos_stock_normal"),
            ("Stock Promedio", "stock_promedio"),
            ("Valor Inventario", "valor_inventario_total"),
            ("Valor Venta Total", "valor_venta_total")
        ]
        
        for i, (label_text, key) in enumerate(stats_labels):
            label = QLabel(f"{label_text}:")
            label.setStyleSheet("font-weight: bold; color: #2c3e50;")
            stats_layout.addWidget(label, i, 0)
            
            value_label = QLabel("0")
            value_label.setStyleSheet("font-size: 14px; color: #007bff; padding: 5px;")
            self.labels_stats[key] = value_label
            stats_layout.addWidget(value_label, i, 1)
        
        splitter.addWidget(stats_widget)
        
        # Gráfico
        self.grafico_est = GraficosCanvas(self, width=6, height=4)
        splitter.addWidget(self.grafico_est)
        
        splitter.setSizes([400, 500])
        layout.addWidget(splitter)
        
        self.tab_widget.addTab(tab, "📈 Estadísticas")
        
        # Cargar estadísticas iniciales
        self.actualizar_estadisticas()
    
    def generar_reporte_mas_vendidos(self):
        """Generar reporte de productos más vendidos"""
        try:
            fecha_desde = self.fecha_desde_mv.date().toString("yyyy-MM-dd")
            fecha_hasta = self.fecha_hasta_mv.date().toString("yyyy-MM-dd")
            top_n = self.top_n_mv.value()
            orden_por = 'cantidad' if self.orden_por_mv.currentText() == 'Cantidad' else 'ingresos'
            
            # Obtener datos
            productos = self.db.get_productos_mas_vendidos(fecha_desde, fecha_hasta, top_n, orden_por)
            
            # Llenar tabla
            self.tabla_mv.setRowCount(len(productos))
            for row, producto in enumerate(productos):
                self.tabla_mv.setItem(row, 0, QTableWidgetItem(producto[2] or producto[1]))  # descripcion_larga o descripcion_corta
                self.tabla_mv.setItem(row, 1, QTableWidgetItem(producto[3] or "Sin Marca"))  # marca
                self.tabla_mv.setItem(row, 2, QTableWidgetItem(producto[4] or ""))  # codigo_barra
                self.tabla_mv.setItem(row, 3, QTableWidgetItem(f"{producto[6]:.0f}"))  # total_cantidad
                self.tabla_mv.setItem(row, 4, QTableWidgetItem(f"S/. {producto[7]:.2f}"))  # total_ingresos
                self.tabla_mv.setItem(row, 5, QTableWidgetItem(f"{producto[8]}"))  # num_ventas
                self.tabla_mv.setItem(row, 6, QTableWidgetItem(f"S/. {producto[9]:.2f}"))  # precio_promedio
                self.tabla_mv.setItem(row, 7, QTableWidgetItem(f"{producto[10]:.0f}"))  # stock_disponible
                
                # Colorear stock bajo
                if producto[10] <= producto[11]:  # stock <= stock_minimo
                    for col in range(8):
                        item = self.tabla_mv.item(row, col)
                        if item:
                            item.setBackground(Qt.GlobalColor.darkRed)
                            item.setForeground(Qt.GlobalColor.white)
            
            # Crear gráfico
            if productos:
                self.grafico_mv.grafico_barras_top_productos(productos, f"Top {top_n} Productos Más Vendidos")
            
            QMessageBox.information(self, "Éxito", f"Reporte generado con {len(productos)} productos")
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error generando reporte: {str(e)}")
    
    def generar_reporte_stock_bajo(self):
        """Generar reporte de productos con stock bajo"""
        try:
            stock_minimo = self.stock_minimo_sb.value() if self.stock_minimo_sb.value() > 0 else None
            
            # Obtener datos
            productos = self.db.get_productos_stock_bajo_reporte(stock_minimo)
            
            # Llenar tabla
            self.tabla_sb.setRowCount(len(productos))
            for row, producto in enumerate(productos):
                self.tabla_sb.setItem(row, 0, QTableWidgetItem(producto[2] or producto[1]))  # descripcion_larga o descripcion_corta
                self.tabla_sb.setItem(row, 1, QTableWidgetItem(producto[3] or "Sin Marca"))  # marca
                self.tabla_sb.setItem(row, 2, QTableWidgetItem(f"{producto[6]:.0f}"))  # stock_disponible
                self.tabla_sb.setItem(row, 3, QTableWidgetItem(f"{producto[7]:.0f}"))  # stock_minimo
                self.tabla_sb.setItem(row, 4, QTableWidgetItem(producto[10]))  # estado_stock
                self.tabla_sb.setItem(row, 5, QTableWidgetItem(f"S/. {producto[8]:.2f}"))  # precio_venta
                self.tabla_sb.setItem(row, 6, QTableWidgetItem(f"S/. {producto[9]:.2f}"))  # costo_promedio
                self.tabla_sb.setItem(row, 7, QTableWidgetItem(f"S/. {producto[8] - producto[9]:.2f}"))  # diferencia
                
                # Colorear según estado
                estado_item = self.tabla_sb.item(row, 4)
                if estado_item:
                    if estado_item.text() == "Sin Stock":
                        for col in range(8):
                            item = self.tabla_sb.item(row, col)
                            if item:
                                item.setBackground(Qt.GlobalColor.darkRed)
                                item.setForeground(Qt.GlobalColor.white)
                    elif estado_item.text() == "Stock Bajo":
                        for col in range(8):
                            item = self.tabla_sb.item(row, col)
                            if item:
                                item.setBackground(Qt.GlobalColor.darkYellow)
                                item.setForeground(Qt.GlobalColor.black)
            
            # Obtener estadísticas para gráfico
            stats = self.db.get_estadisticas_generales_productos()
            if stats:
                self.grafico_sb.grafico_torta_stock(stats)
            
            QMessageBox.information(self, "Éxito", f"Reporte generado con {len(productos)} productos con stock bajo")
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error generando reporte: {str(e)}")
    
    def generar_reporte_rentabilidad(self):
        """Generar reporte de rentabilidad"""
        try:
            fecha_desde = self.fecha_desde_rt.date().toString("yyyy-MM-dd")
            fecha_hasta = self.fecha_hasta_rt.date().toString("yyyy-MM-dd")
            top_n = self.top_n_rt.value()
            
            # Obtener datos
            productos = self.db.get_productos_rentabilidad(fecha_desde, fecha_hasta, top_n)
            
            # Llenar tabla
            self.tabla_rt.setRowCount(len(productos))
            for row, producto in enumerate(productos):
                self.tabla_rt.setItem(row, 0, QTableWidgetItem(producto[2] or producto[1]))  # descripcion_larga o descripcion_corta
                self.tabla_rt.setItem(row, 1, QTableWidgetItem(producto[3] or "Sin Marca"))  # marca
                self.tabla_rt.setItem(row, 2, QTableWidgetItem(f"{producto[6]:.0f}"))  # total_cantidad
                self.tabla_rt.setItem(row, 3, QTableWidgetItem(f"S/. {producto[7]:.2f}"))  # total_ingresos
                self.tabla_rt.setItem(row, 4, QTableWidgetItem(f"S/. {producto[8]:.2f}"))  # total_costos
                self.tabla_rt.setItem(row, 5, QTableWidgetItem(f"S/. {producto[9]:.2f}"))  # ganancia_total
                self.tabla_rt.setItem(row, 6, QTableWidgetItem(f"{producto[10]:.1f}%"))  # margen_porcentaje
                self.tabla_rt.setItem(row, 7, QTableWidgetItem(f"S/. {producto[11]:.2f}"))  # precio_promedio
                self.tabla_rt.setItem(row, 8, QTableWidgetItem(f"S/. {producto[12]:.2f}"))  # costo_promedio
                
                # Colorear según margen
                margen_item = self.tabla_rt.item(row, 6)
                if margen_item:
                    margen = float(margen_item.text().replace('%', ''))
                    if margen >= 50:
                        bg_color = Qt.GlobalColor.darkGreen
                        fg_color = Qt.GlobalColor.white
                    elif margen >= 20:
                        bg_color = Qt.GlobalColor.darkYellow
                        fg_color = Qt.GlobalColor.black
                    else:
                        bg_color = Qt.GlobalColor.darkRed
                        fg_color = Qt.GlobalColor.white
                    
                    for col in range(9):
                        item = self.tabla_rt.item(row, col)
                        if item:
                            item.setBackground(bg_color)
                            item.setForeground(fg_color)
            
            # Crear gráfico
            if productos:
                self.grafico_rt.grafico_rentabilidad(productos)
            
            QMessageBox.information(self, "Éxito", f"Reporte generado con {len(productos)} productos rentables")
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error generando reporte: {str(e)}")
    
    def generar_reporte_rotacion(self):
        """Generar reporte de rotación"""
        try:
            fecha_desde = self.fecha_desde_ro.date().toString("yyyy-MM-dd")
            fecha_hasta = self.fecha_hasta_ro.date().toString("yyyy-MM-dd")
            
            # Obtener datos
            productos = self.db.get_analisis_rotacion_productos(fecha_desde, fecha_hasta)
            
            # Llenar tabla
            self.tabla_ro.setRowCount(len(productos))
            for row, producto in enumerate(productos):
                self.tabla_ro.setItem(row, 0, QTableWidgetItem(producto[2] or producto[1]))  # descripcion_larga o descripcion_corta
                self.tabla_ro.setItem(row, 1, QTableWidgetItem(producto[3] or "Sin Marca"))  # marca
                self.tabla_ro.setItem(row, 2, QTableWidgetItem(f"{producto[4]:.0f}"))  # stock_disponible
                self.tabla_ro.setItem(row, 3, QTableWidgetItem(f"{producto[6]:.0f}"))  # cantidad_vendida
                self.tabla_ro.setItem(row, 4, QTableWidgetItem(f"{producto[7]}"))  # dias_con_venta
                self.tabla_ro.setItem(row, 5, QTableWidgetItem(f"{producto[8]:.1f}"))  # promedio_diario
                dias_inv = f"{producto[9]:.0f}" if producto[9] else "N/A"
                self.tabla_ro.setItem(row, 6, QTableWidgetItem(dias_inv))  # dias_inventario
                self.tabla_ro.setItem(row, 7, QTableWidgetItem(producto[11]))  # categoria_rotacion
                
                # Determinar estado y prioridad
                if producto[6] == 0:
                    estado = "Sin Ventas"
                    prioridad = "Revisar"
                elif producto[9] and producto[9] > 90:
                    estado = "Lento"
                    prioridad = "Promocionar"
                elif producto[9] and producto[9] < 30:
                    estado = "Rápido"
                    prioridad = "Reponer"
                else:
                    estado = "Normal"
                    prioridad = "Monitorear"
                
                self.tabla_ro.setItem(row, 8, QTableWidgetItem(estado))
                self.tabla_ro.setItem(row, 9, QTableWidgetItem(prioridad))
                
                # Colorear según prioridad
                prioridad_item = self.tabla_ro.item(row, 9)
                if prioridad_item:
                    if prioridad_item.text() == "Revisar":
                        bg_color = Qt.GlobalColor.darkRed
                        fg_color = Qt.GlobalColor.white
                    elif prioridad_item.text() == "Promocionar":
                        bg_color = Qt.GlobalColor.darkYellow
                        fg_color = Qt.GlobalColor.black
                    elif prioridad_item.text() == "Reponer":
                        bg_color = Qt.GlobalColor.darkBlue
                        fg_color = Qt.GlobalColor.white
                    else:
                        bg_color = Qt.GlobalColor.darkGreen
                        fg_color = Qt.GlobalColor.white
                    
                    for col in range(10):
                        item = self.tabla_ro.item(row, col)
                        if item:
                            item.setBackground(bg_color)
                            item.setForeground(fg_color)
            
            QMessageBox.information(self, "Éxito", f"Reporte generado con {len(productos)} productos")
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error generando reporte: {str(e)}")
    
    def actualizar_estadisticas(self):
        """Actualizar estadísticas generales"""
        try:
            # Obtener estadísticas
            stats = self.db.get_estadisticas_generales_productos()
            
            # Actualizar labels
            for key, value in stats.items():
                if key in self.labels_stats:
                    if key in ['valor_inventario_total', 'valor_venta_total']:
                        self.labels_stats[key].setText(f"S/. {value:,.2f}")
                    else:
                        self.labels_stats[key].setText(str(value))
            
            # Crear gráfico
            if stats:
                self.grafico_est.grafico_torta_stock(stats)
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error actualizando estadísticas: {str(e)}")
