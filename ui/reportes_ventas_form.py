#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Módulo de Reportes de Ventas
Sistema completo de análisis de ventas con múltiples filtros y visualizaciones
"""

import sys
from datetime import datetime, timedelta, date
from PySide6.QtWidgets import (QApplication, QMainWindow, QWidget, QVBoxLayout, 
                               QHBoxLayout, QGridLayout, QLabel, QPushButton, 
                               QTableWidget, QTableWidgetItem, QComboBox, 
                               QLineEdit, QSpinBox, QTextEdit, QDateEdit, 
                               QGroupBox, QTabWidget, QMessageBox, QDialog,
                               QHeaderView, QFrame, QSplitter, QScrollArea,
                               QCheckBox, QRadioButton, QButtonGroup, QProgressBar)
from PySide6.QtCore import Qt, QDate, Signal, QTimer
from PySide6.QtGui import QFont, QIcon, QColor, QPainter, QPen
import json

# Importaciones para gráficos
import matplotlib.pyplot as plt
from matplotlib.backends.backend_qt5agg import FigureCanvasQTAgg as FigureCanvas
from matplotlib.figure import Figure
import matplotlib.dates as mdates
import numpy as np

class GraficosCanvas(FigureCanvas):
    """Canvas personalizado para mostrar gráficos"""
    def __init__(self, parent=None, width=5, height=4, dpi=100):
        self.fig = Figure(figsize=(width, height), dpi=dpi, facecolor='white')
        super().__init__(self.fig)
        self.setParent(parent)
        
        # Configurar estilo de matplotlib
        plt.style.use('default')
        
    def limpiar(self):
        """Limpiar todos los gráficos"""
        self.fig.clear()
        self.draw()
        
    def grafico_barras(self, datos, titulo="Gráfico de Barras", xlabel="Categoría", ylabel="Valor"):
        """Crear gráfico de barras"""
        self.fig.clear()
        ax = self.fig.add_subplot(111)
        
        if not datos:
            ax.text(0.5, 0.5, 'No hay datos para mostrar', 
                   horizontalalignment='center', verticalalignment='center',
                   transform=ax.transAxes, fontsize=14)
            self.draw()
            return
            
        categorias = [str(item[0]) for item in datos]
        valores = [float(item[1]) for item in datos]
        
        # Crear gráfico de barras con colores
        colores = plt.cm.Set3(np.linspace(0, 1, len(valores)))
        bars = ax.bar(categorias, valores, color=colores, alpha=0.8, edgecolor='black', linewidth=0.5)
        
        # Personalizar gráfico
        ax.set_title(titulo, fontsize=14, fontweight='bold', pad=20)
        ax.set_xlabel(xlabel, fontsize=12)
        ax.set_ylabel(ylabel, fontsize=12)
        
        # Rotar etiquetas del eje X si son muy largas
        if max(len(cat) for cat in categorias) > 10:
            ax.tick_params(axis='x', rotation=45)
        
        # Agregar valores en las barras
        for bar, valor in zip(bars, valores):
            height = bar.get_height()
            ax.text(bar.get_x() + bar.get_width()/2., height + max(valores)*0.01,
                   f'{valor:.0f}', ha='center', va='bottom', fontsize=10)
        
        # Ajustar layout
        self.fig.tight_layout()
        self.draw()
        
    def grafico_lineas(self, datos, titulo="Gráfico de Líneas", xlabel="Período", ylabel="Valor"):
        """Crear gráfico de líneas"""
        self.fig.clear()
        ax = self.fig.add_subplot(111)
        
        if not datos:
            ax.text(0.5, 0.5, 'No hay datos para mostrar', 
                   horizontalalignment='center', verticalalignment='center',
                   transform=ax.transAxes, fontsize=14)
            self.draw()
            return
            
        periodos = [str(item[0]) for item in datos]
        valores = [float(item[1]) for item in datos]
        
        # Crear gráfico de líneas
        ax.plot(periodos, valores, marker='o', linewidth=2, markersize=6, 
                color='#2E86AB', markerfacecolor='#A23B72', markeredgecolor='white', 
                markeredgewidth=2)
        
        # Personalizar gráfico
        ax.set_title(titulo, fontsize=14, fontweight='bold', pad=20)
        ax.set_xlabel(xlabel, fontsize=12)
        ax.set_ylabel(ylabel, fontsize=12)
        
        # Rotar etiquetas del eje X
        ax.tick_params(axis='x', rotation=45)
        
        # Agregar valores en los puntos
        for i, valor in enumerate(valores):
            ax.annotate(f'{valor:.0f}', (periodos[i], valor), 
                       textcoords="offset points", xytext=(0,10), ha='center', fontsize=9)
        
        # Ajustar layout
        self.fig.tight_layout()
        self.draw()
        
    def grafico_torta(self, datos, titulo="Gráfico de Torta"):
        """Crear gráfico de torta"""
        self.fig.clear()
        ax = self.fig.add_subplot(111)
        
        if not datos:
            ax.text(0.5, 0.5, 'No hay datos para mostrar', 
                   horizontalalignment='center', verticalalignment='center',
                   transform=ax.transAxes, fontsize=14)
            self.draw()
            return
            
        etiquetas = [str(item[0]) for item in datos]
        valores = [float(item[1]) for item in datos]
        
        # Crear gráfico de torta con colores
        colores = plt.cm.Set3(np.linspace(0, 1, len(valores)))
        wedges, texts, autotexts = ax.pie(valores, labels=etiquetas, autopct='%1.1f%%', 
                                         colors=colores, startangle=90, textprops={'fontsize': 10})
        
        # Personalizar gráfico
        ax.set_title(titulo, fontsize=14, fontweight='bold', pad=20)
        
        # Mejorar apariencia de los textos
        for autotext in autotexts:
            autotext.set_color('white')
            autotext.set_fontweight('bold')
            autotext.set_fontsize(9)
        
        # Ajustar layout
        self.fig.tight_layout()
        self.draw()

class ReportesVentasForm(QMainWindow):
    def __init__(self, db, usuario_actual):
        super().__init__()
        self.db = db
        self.usuario_actual = usuario_actual
        self.init_ui()
        
    def init_ui(self):
        """Inicializar la interfaz de usuario"""
        self.setWindowTitle("📊 Reportes de Ventas - Sistema RMS")
        self.setGeometry(100, 100, 1600, 1000)
        
        # Widget central
        central_widget = QWidget()
        self.setCentralWidget(central_widget)
        
        # Layout principal
        main_layout = QVBoxLayout(central_widget)
        main_layout.setSpacing(10)
        main_layout.setContentsMargins(15, 15, 15, 15)
        
        # Título
        titulo = QLabel("📊 REPORTES DE VENTAS")
        titulo.setStyleSheet("""
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
            background: #e8edf4;
            padding: 10px;
            border-radius: 8px;
            border: 2px solid #5d6d7e;
        """)
        main_layout.addWidget(titulo)
        
        # Crear tabs para diferentes tipos de reportes
        self.tab_widget = QTabWidget()
        self.tab_widget.setStyleSheet("""
            QTabWidget::pane {
                border: 1px solid #d0d0d0;
                border-radius: 6px;
                background: white;
            }
            QTabBar::tab {
                background: #f0f0f0;
                color: #333333;
                padding: 10px 20px;
                margin-right: 2px;
                border-top-left-radius: 6px;
                border-top-right-radius: 6px;
                font-size: 10px;
                font-weight: bold;
            }
            QTabBar::tab:selected {
                background: white;
                color: #0078d4;
                border-bottom: 2px solid #0078d4;
            }
            QTabBar::tab:hover {
                background: #e0e0e0;
            }
        """)
        
        # Tab 1: Reportes por Período
        self.tab_periodo = self.create_tab_periodo()
        self.tab_widget.addTab(self.tab_periodo, "📅 Por Período")
        
        # Tab 2: Reportes por Jerarquía
        self.tab_jerarquia = self.create_tab_jerarquia()
        self.tab_widget.addTab(self.tab_jerarquia, "🏢 Por Jerarquía")
        
        # Tab 3: Reportes por Marca
        self.tab_marca = self.create_tab_marca()
        self.tab_widget.addTab(self.tab_marca, "🏷️ Por Marca")
        
        # Tab 4: Comparaciones
        self.tab_comparacion = self.create_tab_comparacion()
        self.tab_widget.addTab(self.tab_comparacion, "📈 Comparaciones")
        
        # Tab 5: Análisis Avanzado
        self.tab_avanzado = self.create_tab_avanzado()
        self.tab_widget.addTab(self.tab_avanzado, "🔍 Análisis Avanzado")
        
        main_layout.addWidget(self.tab_widget)
        
        # Barra de herramientas inferior
        toolbar_layout = QHBoxLayout()
        
        # Botones de acción
        self.btn_exportar_pdf = QPushButton("📄 Exportar PDF")
        self.btn_exportar_excel = QPushButton("📊 Exportar Excel")
        self.btn_imprimir = QPushButton("🖨️ Imprimir")
        self.btn_limpiar = QPushButton("🗑️ Limpiar")
        
        # Estilos de botones
        button_style = """
            QPushButton {
                background: #0078d4;
                color: white;
                border: none;
                padding: 8px 16px;
                border-radius: 4px;
                font-weight: bold;
                font-size: 10px;
            }
            QPushButton:hover {
                background: #106ebe;
            }
            QPushButton:pressed {
                background: #005a9e;
            }
        """
        
        for btn in [self.btn_exportar_pdf, self.btn_exportar_excel, self.btn_imprimir, self.btn_limpiar]:
            btn.setStyleSheet(button_style)
        
        # Conectar botones
        self.btn_exportar_pdf.clicked.connect(self.exportar_pdf)
        self.btn_exportar_excel.clicked.connect(self.exportar_excel)
        self.btn_imprimir.clicked.connect(self.imprimir_reporte)
        self.btn_limpiar.clicked.connect(self.limpiar_filtros)
        
        toolbar_layout.addWidget(self.btn_exportar_pdf)
        toolbar_layout.addWidget(self.btn_exportar_excel)
        toolbar_layout.addWidget(self.btn_imprimir)
        toolbar_layout.addWidget(self.btn_limpiar)
        toolbar_layout.addStretch()
        
        main_layout.addLayout(toolbar_layout)
        
        # Aplicar estilos generales
        self.apply_styles()
        
        # Cargar datos iniciales
        self.cargar_datos_iniciales()
        
    def create_tab_periodo(self):
        """Crear tab de reportes por período"""
        widget = QWidget()
        layout = QVBoxLayout(widget)
        
        # Filtros de período
        filtros_group = QGroupBox("📅 Filtros de Período")
        filtros_layout = QGridLayout(filtros_group)
        
        # Tipo de período
        filtros_layout.addWidget(QLabel("Tipo de Período:"), 0, 0)
        self.combo_tipo_periodo = QComboBox()
        self.combo_tipo_periodo.addItems([
            "Diario", "Semanal", "Mensual", "Anual", "Personalizado"
        ])
        self.combo_tipo_periodo.currentTextChanged.connect(self.on_tipo_periodo_changed)
        filtros_layout.addWidget(self.combo_tipo_periodo, 0, 1)
        
        # Fecha desde
        filtros_layout.addWidget(QLabel("Desde:"), 1, 0)
        self.date_desde = QDateEdit()
        self.date_desde.setDate(QDate.currentDate())
        self.date_desde.setCalendarPopup(True)
        filtros_layout.addWidget(self.date_desde, 1, 1)
        
        # Fecha hasta
        filtros_layout.addWidget(QLabel("Hasta:"), 1, 2)
        self.date_hasta = QDateEdit()
        self.date_hasta.setDate(QDate.currentDate())
        self.date_hasta.setCalendarPopup(True)
        filtros_layout.addWidget(self.date_hasta, 1, 3)
        
        # Botón generar
        self.btn_generar_periodo = QPushButton("🔍 Generar Reporte")
        self.btn_generar_periodo.setStyleSheet("""
            QPushButton {
                background: #27ae60;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 4px;
                font-weight: bold;
                font-size: 11px;
            }
            QPushButton:hover {
                background: #229954;
            }
        """)
        self.btn_generar_periodo.clicked.connect(self.generar_reporte_periodo)
        filtros_layout.addWidget(self.btn_generar_periodo, 1, 4)
        
        layout.addWidget(filtros_group)
        
        # Resultados
        resultados_group = QGroupBox("📊 Resultados")
        resultados_layout = QVBoxLayout(resultados_group)
        
        # Tabla de resultados
        self.tabla_periodo = QTableWidget()
        self.tabla_periodo.setColumnCount(6)
        self.tabla_periodo.setHorizontalHeaderLabels([
            "Período", "Ventas (S/)", "Tickets", "Promedio", "Crecimiento %", "Tendencia"
        ])
        
        # Configurar tabla
        header = self.tabla_periodo.horizontalHeader()
        header.setSectionResizeMode(0, QHeaderView.Stretch)
        for i in range(1, 6):
            header.setSectionResizeMode(i, QHeaderView.ResizeToContents)
        
        resultados_layout.addWidget(self.tabla_periodo)
        
        # Gráfico real
        grafico_group = QGroupBox("📈 Gráfico de Tendencia")
        grafico_layout = QVBoxLayout(grafico_group)
        
        self.grafico_periodo = GraficosCanvas(self, width=8, height=5)
        self.grafico_periodo.setMinimumHeight(200)
        grafico_layout.addWidget(self.grafico_periodo)
        
        resultados_layout.addWidget(grafico_group)
        layout.addWidget(resultados_group)
        
        return widget
    
    def create_tab_jerarquia(self):
        """Crear tab de reportes por jerarquía"""
        widget = QWidget()
        layout = QVBoxLayout(widget)
        
        # Filtros de jerarquía
        filtros_group = QGroupBox("🏢 Filtros de Jerarquía")
        filtros_layout = QGridLayout(filtros_group)
        
        # Departamento
        filtros_layout.addWidget(QLabel("Departamento:"), 0, 0)
        self.combo_departamento = QComboBox()
        self.combo_departamento.addItem("Todos", None)
        filtros_layout.addWidget(self.combo_departamento, 0, 1)
        
        # Subdepartamento
        filtros_layout.addWidget(QLabel("Subdepartamento:"), 0, 2)
        self.combo_subdepartamento = QComboBox()
        self.combo_subdepartamento.addItem("Todos", None)
        filtros_layout.addWidget(self.combo_subdepartamento, 0, 3)
        
        # Clase
        filtros_layout.addWidget(QLabel("Clase:"), 1, 0)
        self.combo_clase = QComboBox()
        self.combo_clase.addItem("Todas", None)
        filtros_layout.addWidget(self.combo_clase, 1, 1)
        
        # Período
        filtros_layout.addWidget(QLabel("Período:"), 1, 2)
        self.combo_periodo_jerarquia = QComboBox()
        self.combo_periodo_jerarquia.addItems([
            "Última semana", "Último mes", "Último trimestre", "Último año"
        ])
        filtros_layout.addWidget(self.combo_periodo_jerarquia, 1, 3)
        
        # Botón generar
        self.btn_generar_jerarquia = QPushButton("🔍 Generar Reporte")
        self.btn_generar_jerarquia.setStyleSheet("""
            QPushButton {
                background: #27ae60;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 4px;
                font-weight: bold;
                font-size: 11px;
            }
            QPushButton:hover {
                background: #229954;
            }
        """)
        self.btn_generar_jerarquia.clicked.connect(self.generar_reporte_jerarquia)
        filtros_layout.addWidget(self.btn_generar_jerarquia, 1, 4)
        
        layout.addWidget(filtros_group)
        
        # Resultados
        resultados_group = QGroupBox("📊 Ventas por Jerarquía")
        resultados_layout = QVBoxLayout(resultados_group)
        
        # Tabla de resultados
        self.tabla_jerarquia = QTableWidget()
        self.tabla_jerarquia.setColumnCount(5)
        self.tabla_jerarquia.setHorizontalHeaderLabels([
            "Jerarquía", "Ventas (S/)", "Productos", "Participación %", "Ranking"
        ])
        
        # Configurar tabla
        header = self.tabla_jerarquia.horizontalHeader()
        header.setSectionResizeMode(0, QHeaderView.Stretch)
        for i in range(1, 5):
            header.setSectionResizeMode(i, QHeaderView.ResizeToContents)
        
        resultados_layout.addWidget(self.tabla_jerarquia)
        layout.addWidget(resultados_group)
        
        return widget
    
    def create_tab_marca(self):
        """Crear tab de reportes por marca"""
        widget = QWidget()
        layout = QVBoxLayout(widget)
        
        # Filtros de marca
        filtros_group = QGroupBox("🏷️ Filtros de Marca")
        filtros_layout = QGridLayout(filtros_group)
        
        # Marca
        filtros_layout.addWidget(QLabel("Marca:"), 0, 0)
        self.combo_marca = QComboBox()
        self.combo_marca.addItem("Todas", None)
        filtros_layout.addWidget(self.combo_marca, 0, 1)
        
        # Período
        filtros_layout.addWidget(QLabel("Período:"), 0, 2)
        self.combo_periodo_marca = QComboBox()
        self.combo_periodo_marca.addItems([
            "Última semana", "Último mes", "Último trimestre", "Último año"
        ])
        filtros_layout.addWidget(self.combo_periodo_marca, 0, 3)
        
        # Top N
        filtros_layout.addWidget(QLabel("Mostrar Top:"), 1, 0)
        self.spin_top_marca = QSpinBox()
        self.spin_top_marca.setRange(5, 50)
        self.spin_top_marca.setValue(10)
        filtros_layout.addWidget(self.spin_top_marca, 1, 1)
        
        # Botón generar
        self.btn_generar_marca = QPushButton("🔍 Generar Reporte")
        self.btn_generar_marca.setStyleSheet("""
            QPushButton {
                background: #27ae60;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 4px;
                font-weight: bold;
                font-size: 11px;
            }
            QPushButton:hover {
                background: #229954;
            }
        """)
        self.btn_generar_marca.clicked.connect(self.generar_reporte_marca)
        filtros_layout.addWidget(self.btn_generar_marca, 1, 2)
        
        layout.addWidget(filtros_group)
        
        # Resultados
        resultados_group = QGroupBox("📊 Ventas por Marca")
        resultados_layout = QVBoxLayout(resultados_group)
        
        # Crear layout horizontal para tabla y gráfico
        contenido_layout = QHBoxLayout()
        
        # Tabla de resultados
        tabla_widget = QWidget()
        tabla_layout = QVBoxLayout(tabla_widget)
        
        self.tabla_marca = QTableWidget()
        self.tabla_marca.setColumnCount(6)
        self.tabla_marca.setHorizontalHeaderLabels([
            "Marca", "Ventas (S/)", "Productos", "Participación %", "Crecimiento %", "Ranking"
        ])
        
        # Configurar tabla
        header = self.tabla_marca.horizontalHeader()
        header.setSectionResizeMode(0, QHeaderView.Stretch)
        for i in range(1, 6):
            header.setSectionResizeMode(i, QHeaderView.ResizeToContents)
        
        tabla_layout.addWidget(self.tabla_marca)
        
        # Canvas para gráficos
        self.grafico_marca = GraficosCanvas(self, width=6, height=4)
        
        # Agregar a layout horizontal
        contenido_layout.addWidget(tabla_widget, 1)
        contenido_layout.addWidget(self.grafico_marca, 1)
        
        resultados_layout.addLayout(contenido_layout)
        layout.addWidget(resultados_group)
        
        return widget
    
    def create_tab_comparacion(self):
        """Crear tab de comparaciones"""
        widget = QWidget()
        layout = QVBoxLayout(widget)
        
        # Filtros de comparación
        filtros_group = QGroupBox("📈 Filtros de Comparación")
        filtros_layout = QGridLayout(filtros_group)
        
        # Tipo de comparación
        filtros_layout.addWidget(QLabel("Tipo:"), 0, 0)
        self.combo_tipo_comparacion = QComboBox()
        self.combo_tipo_comparacion.addItems([
            "Semanas consecutivas", "Meses consecutivos", "Años consecutivos",
            "Mismo período año anterior", "Período personalizado"
        ])
        filtros_layout.addWidget(self.combo_tipo_comparacion, 0, 1)
        
        # Período base
        filtros_layout.addWidget(QLabel("Período Base:"), 0, 2)
        self.date_periodo_base = QDateEdit()
        self.date_periodo_base.setDate(QDate.currentDate())
        self.date_periodo_base.setCalendarPopup(True)
        filtros_layout.addWidget(self.date_periodo_base, 0, 3)
        
        # Botón generar
        self.btn_generar_comparacion = QPushButton("🔍 Generar Comparación")
        self.btn_generar_comparacion.setStyleSheet("""
            QPushButton {
                background: #27ae60;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 4px;
                font-weight: bold;
                font-size: 11px;
            }
            QPushButton:hover {
                background: #229954;
            }
        """)
        self.btn_generar_comparacion.clicked.connect(self.generar_comparacion)
        filtros_layout.addWidget(self.btn_generar_comparacion, 0, 4)
        
        layout.addWidget(filtros_group)
        
        # Resultados
        resultados_group = QGroupBox("📊 Comparación de Períodos")
        resultados_layout = QVBoxLayout(resultados_group)
        
        # Tabla de comparación
        self.tabla_comparacion = QTableWidget()
        self.tabla_comparacion.setColumnCount(5)
        self.tabla_comparacion.setHorizontalHeaderLabels([
            "Período", "Ventas (S/)", "Tickets", "Promedio", "Variación %"
        ])
        
        # Configurar tabla
        header = self.tabla_comparacion.horizontalHeader()
        header.setSectionResizeMode(0, QHeaderView.Stretch)
        for i in range(1, 5):
            header.setSectionResizeMode(i, QHeaderView.ResizeToContents)
        
        resultados_layout.addWidget(self.tabla_comparacion)
        layout.addWidget(resultados_group)
        
        return widget
    
    def create_tab_avanzado(self):
        """Crear tab de análisis avanzado"""
        widget = QWidget()
        layout = QVBoxLayout(widget)
        
        # Filtros avanzados
        filtros_group = QGroupBox("🔍 Filtros Avanzados")
        filtros_layout = QGridLayout(filtros_group)
        
        # Usuario
        filtros_layout.addWidget(QLabel("Usuario:"), 0, 0)
        self.combo_usuario = QComboBox()
        self.combo_usuario.addItem("Todos", None)
        filtros_layout.addWidget(self.combo_usuario, 0, 1)
        
        # Cliente
        filtros_layout.addWidget(QLabel("Cliente:"), 0, 2)
        self.combo_cliente = QComboBox()
        self.combo_cliente.addItem("Todos", None)
        filtros_layout.addWidget(self.combo_cliente, 0, 3)
        
        # Rango de ventas
        filtros_layout.addWidget(QLabel("Rango de Ventas:"), 1, 0)
        self.spin_venta_min = QSpinBox()
        self.spin_venta_min.setRange(0, 999999)
        self.spin_venta_min.setValue(0)
        filtros_layout.addWidget(self.spin_venta_min, 1, 1)
        
        filtros_layout.addWidget(QLabel("a"), 1, 2)
        self.spin_venta_max = QSpinBox()
        self.spin_venta_max.setRange(0, 999999)
        self.spin_venta_max.setValue(999999)
        filtros_layout.addWidget(self.spin_venta_max, 1, 3)
        
        # Botón generar
        self.btn_generar_avanzado = QPushButton("🔍 Generar Análisis")
        self.btn_generar_avanzado.setStyleSheet("""
            QPushButton {
                background: #27ae60;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 4px;
                font-weight: bold;
                font-size: 11px;
            }
            QPushButton:hover {
                background: #229954;
            }
        """)
        self.btn_generar_avanzado.clicked.connect(self.generar_analisis_avanzado)
        filtros_layout.addWidget(self.btn_generar_avanzado, 1, 4)
        
        layout.addWidget(filtros_group)
        
        # Resultados
        resultados_group = QGroupBox("📊 Análisis Avanzado")
        resultados_layout = QVBoxLayout(resultados_group)
        
        # Tabla de resultados
        self.tabla_avanzado = QTableWidget()
        self.tabla_avanzado.setColumnCount(8)
        self.tabla_avanzado.setHorizontalHeaderLabels([
            "Fecha", "Usuario", "Cliente", "Productos", "Ventas (S/)", "Tickets", "Promedio", "Tendencia"
        ])
        
        # Configurar tabla
        header = self.tabla_avanzado.horizontalHeader()
        header.setSectionResizeMode(0, QHeaderView.ResizeToContents)
        header.setSectionResizeMode(1, QHeaderView.ResizeToContents)
        header.setSectionResizeMode(2, QHeaderView.Stretch)
        for i in range(3, 8):
            header.setSectionResizeMode(i, QHeaderView.ResizeToContents)
        
        resultados_layout.addWidget(self.tabla_avanzado)
        layout.addWidget(resultados_group)
        
        return widget
    
    def apply_styles(self):
        """Aplicar estilos generales"""
        self.setStyleSheet("""
            QMainWindow {
                background: #f5f5f5;
                font-size: 10px;
            }
            QGroupBox {
                font-weight: bold;
                border: 1px solid #d0d0d0;
                border-radius: 6px;
                margin-top: 10px;
                padding-top: 10px;
                font-size: 10px;
                color: #333333;
            }
            QGroupBox::title {
                subcontrol-origin: margin;
                left: 10px;
                padding: 0 8px 0 8px;
                background: #f5f5f5;
                font-size: 10px;
                color: #333333;
            }
            QTableWidget {
                background: white;
                border: 1px solid #d0d0d0;
                border-radius: 6px;
                gridline-color: #e0e0e0;
                selection-background-color: #0078d4;
                selection-color: white;
                font-size: 10px;
                alternate-background-color: #f9f9f9;
            }
            QTableWidget::item {
                padding: 6px;
                border-bottom: 1px solid #e0e0e0;
                font-size: 10px;
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
                font-size: 10px;
                border-right: 1px solid #005a9e;
            }
            QHeaderView::section:first {
                border-top-left-radius: 6px;
            }
            QHeaderView::section:last {
                border-top-right-radius: 6px;
                border-right: none;
            }
            QComboBox, QLineEdit, QSpinBox, QDateEdit {
                padding: 6px;
                border: 1px solid #d0d0d0;
                border-radius: 4px;
                background: white;
                font-size: 10px;
                color: #333333;
            }
            QComboBox:focus, QLineEdit:focus, QSpinBox:focus, QDateEdit:focus {
                border-color: #0078d4;
                border-width: 2px;
            }
            QLabel {
                font-size: 10px;
                color: #333333;
            }
        """)
    
    def cargar_datos_iniciales(self):
        """Cargar datos iniciales para los combos"""
        try:
            # Cargar departamentos
            departamentos = self.db.get_departamentos_para_reporte()
            self.combo_departamento.clear()
            self.combo_departamento.addItem("Todos", None)
            for depto in departamentos:
                self.combo_departamento.addItem(depto[1], depto[0])
            
            # Cargar subdepartamentos
            subdepartamentos = self.db.get_subdepartamentos_para_reporte()
            self.combo_subdepartamento.clear()
            self.combo_subdepartamento.addItem("Todos", None)
            for subdepto in subdepartamentos:
                self.combo_subdepartamento.addItem(subdepto[1], subdepto[0])
            
            # Cargar clases
            clases = self.db.get_clases_para_reporte()
            self.combo_clase.clear()
            self.combo_clase.addItem("Todas", None)
            for clase in clases:
                self.combo_clase.addItem(clase[1], clase[0])
            
            # Cargar usuarios
            usuarios = self.db.get_usuarios_para_reporte()
            self.combo_usuario.clear()
            self.combo_usuario.addItem("Todos", None)
            for usuario in usuarios:
                self.combo_usuario.addItem(usuario[1], usuario[0])
            
            # Cargar clientes
            clientes = self.db.get_clientes_para_reporte()
            self.combo_cliente.clear()
            self.combo_cliente.addItem("Todos", None)
            for cliente in clientes:
                self.combo_cliente.addItem(cliente[1], cliente[0])
            
        except Exception as e:
            print(f"Error cargando datos iniciales: {e}")
    
    # Métodos de eventos
    def on_tipo_periodo_changed(self, texto):
        """Manejar cambio en tipo de período"""
        if texto == "Personalizado":
            self.date_desde.setEnabled(True)
            self.date_hasta.setEnabled(True)
        else:
            self.date_desde.setEnabled(False)
            self.date_hasta.setEnabled(False)
            # Configurar fechas según el tipo
            hoy = QDate.currentDate()
            if texto == "Diario":
                self.date_desde.setDate(hoy)
                self.date_hasta.setDate(hoy)
            elif texto == "Semanal":
                inicio_semana = hoy.addDays(-hoy.dayOfWeek() + 1)
                self.date_desde.setDate(inicio_semana)
                self.date_hasta.setDate(inicio_semana.addDays(6))
            elif texto == "Mensual":
                inicio_mes = QDate(hoy.year(), hoy.month(), 1)
                fin_mes = inicio_mes.addMonths(1).addDays(-1)
                self.date_desde.setDate(inicio_mes)
                self.date_hasta.setDate(fin_mes)
            elif texto == "Anual":
                inicio_ano = QDate(hoy.year(), 1, 1)
                fin_ano = QDate(hoy.year(), 12, 31)
                self.date_desde.setDate(inicio_ano)
                self.date_hasta.setDate(fin_ano)
    
    # Métodos de generación de reportes
    def generar_reporte_periodo(self):
        """Generar reporte por período"""
        try:
            # Obtener fechas
            fecha_desde = self.date_desde.date().toPython()
            fecha_hasta = self.date_hasta.date().toPython()
            tipo_periodo = self.combo_tipo_periodo.currentText().lower()
            
            # Obtener datos reales de la base de datos
            datos = self.db.get_ventas_por_periodo(fecha_desde, fecha_hasta, tipo_periodo)
            
            if not datos:
                QMessageBox.information(self, "Sin Datos", "No se encontraron ventas para el período seleccionado")
                return
            
            # Configurar tabla
            self.tabla_periodo.setRowCount(len(datos))
            
            # Calcular totales para comparaciones
            total_ventas = sum(float(fila[1]) for fila in datos)
            
            for i, fila in enumerate(datos):
                # Formatear período según el tipo
                if tipo_periodo in ['diario', 'personalizado']:
                    periodo = str(fila[0])
                elif tipo_periodo == 'semanal':
                    periodo = f"Año {fila[0]}, Semana {fila[1]}"
                elif tipo_periodo == 'mensual':
                    meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 
                            'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic']
                    periodo = f"{fila[0]} - {meses[int(fila[1])-1]}"
                elif tipo_periodo == 'anual':
                    periodo = f"Año {fila[0]}"
                else:
                    periodo = str(fila[0])
                
                # Calcular crecimiento (comparar con período anterior)
                if i > 0:
                    ventas_anterior = float(datos[i-1][1])
                    ventas_actual = float(fila[1])
                    if ventas_anterior > 0:
                        crecimiento = ((ventas_actual - ventas_anterior) / ventas_anterior) * 100
                        crecimiento_str = f"{crecimiento:+.1f}%"
                        tendencia = "📈" if crecimiento > 0 else "📉" if crecimiento < 0 else "➡️"
                    else:
                        crecimiento_str = "N/A"
                        tendencia = "➡️"
                else:
                    crecimiento_str = "Base"
                    tendencia = "➡️"
                
                # Crear items de la tabla
                items = [
                    periodo,
                    f"S/ {float(fila[1]):,.2f}",
                    str(fila[2]),
                    f"S/ {float(fila[3]):,.2f}",
                    crecimiento_str,
                    tendencia
                ]
                
                for j, valor in enumerate(items):
                    item = QTableWidgetItem(str(valor))
                    if j == 5:  # Columna de tendencia
                        item.setTextAlignment(Qt.AlignCenter)
                    elif j in [1, 2, 3, 4]:  # Columnas numéricas
                        item.setTextAlignment(Qt.AlignRight | Qt.AlignVCenter)
                    self.tabla_periodo.setItem(i, j, item)
            
            # Generar gráfico de líneas
            self.grafico_periodo.grafico_lineas(
                datos, 
                titulo=f"Ventas por {tipo_periodo.title()}", 
                xlabel="Período", 
                ylabel="Ventas (S/)"
            )
            
            QMessageBox.information(self, "Éxito", f"Reporte generado correctamente\nTotal de ventas: S/ {total_ventas:,.2f}")
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error generando reporte: {str(e)}")
    
    def generar_reporte_jerarquia(self):
        """Generar reporte por jerarquía"""
        try:
            # Obtener fechas (usar período del combo)
            hoy = datetime.now().date()
            if self.combo_periodo_jerarquia.currentText() == "Última semana":
                fecha_desde = hoy - timedelta(days=7)
                fecha_hasta = hoy
            elif self.combo_periodo_jerarquia.currentText() == "Último mes":
                fecha_desde = hoy - timedelta(days=30)
                fecha_hasta = hoy
            elif self.combo_periodo_jerarquia.currentText() == "Último trimestre":
                fecha_desde = hoy - timedelta(days=90)
                fecha_hasta = hoy
            elif self.combo_periodo_jerarquia.currentText() == "Último año":
                fecha_desde = hoy - timedelta(days=365)
                fecha_hasta = hoy
            else:
                fecha_desde = hoy - timedelta(days=30)
                fecha_hasta = hoy
            
            # Obtener filtros de jerarquía
            departamento_id = self.combo_departamento.currentData()
            subdepartamento_id = self.combo_subdepartamento.currentData()
            clase_id = self.combo_clase.currentData()
            
            # Obtener datos reales de la base de datos
            datos = self.db.get_ventas_por_jerarquia(fecha_desde, fecha_hasta, departamento_id, subdepartamento_id, clase_id)
            
            if not datos:
                QMessageBox.information(self, "Sin Datos", "No se encontraron ventas para la jerarquía seleccionada")
                return
            
            # Configurar tabla
            self.tabla_jerarquia.setRowCount(len(datos))
            
            # Calcular totales para participación
            total_ventas = sum(float(fila[3]) for fila in datos)
            
            for i, fila in enumerate(datos):
                # Calcular participación porcentual
                ventas = float(fila[3])
                participacion = (ventas / total_ventas * 100) if total_ventas > 0 else 0
                
                # Crear jerarquía combinada
                jerarquia = f"{fila[0]} > {fila[1]} > {fila[2]}"
                
                # Crear items de la tabla
                items = [
                    jerarquia,
                    f"S/ {ventas:,.2f}",
                    str(fila[5]),  # num_productos
                    f"{participacion:.1f}%",
                    str(i + 1)  # ranking
                ]
                
                for j, valor in enumerate(items):
                    item = QTableWidgetItem(str(valor))
                    if j in [1, 2, 3, 4]:  # Columnas numéricas
                        item.setTextAlignment(Qt.AlignRight | Qt.AlignVCenter)
                    self.tabla_jerarquia.setItem(i, j, item)
            
            QMessageBox.information(self, "Éxito", f"Reporte de jerarquía generado correctamente\nTotal de ventas: S/ {total_ventas:,.2f}")
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error generando reporte: {str(e)}")
    
    def generar_reporte_marca(self):
        """Generar reporte por marca"""
        try:
            # Obtener fechas (usar período del combo)
            hoy = datetime.now().date()
            if self.combo_periodo_marca.currentText() == "Última semana":
                fecha_desde = hoy - timedelta(days=7)
                fecha_hasta = hoy
            elif self.combo_periodo_marca.currentText() == "Último mes":
                fecha_desde = hoy - timedelta(days=30)
                fecha_hasta = hoy
            elif self.combo_periodo_marca.currentText() == "Último trimestre":
                fecha_desde = hoy - timedelta(days=90)
                fecha_hasta = hoy
            elif self.combo_periodo_marca.currentText() == "Último año":
                fecha_desde = hoy - timedelta(days=365)
                fecha_hasta = hoy
            else:
                fecha_desde = hoy - timedelta(days=30)
                fecha_hasta = hoy
            
            # Obtener top N
            top_n = self.spin_top_marca.value()
            
            # Obtener datos reales de la base de datos
            datos = self.db.get_ventas_por_marca(fecha_desde, fecha_hasta, top_n)
            
            if not datos:
                QMessageBox.information(self, "Sin Datos", "No se encontraron ventas para las marcas seleccionadas")
                return
            
            # Configurar tabla
            self.tabla_marca.setRowCount(len(datos))
            
            for i, fila in enumerate(datos):
                # Calcular crecimiento (simulado por ahora)
                crecimiento = "+0.0%"  # TODO: Implementar cálculo real de crecimiento
                
                # Crear items de la tabla
                items = [
                    fila[0],  # marca
                    f"S/ {float(fila[1]):,.2f}",  # ventas_totales
                    str(fila[3]),  # num_productos
                    f"{float(fila[4]):.1f}%",  # participacion_porcentaje
                    crecimiento,  # crecimiento (simulado)
                    str(i + 1)  # ranking
                ]
                
                for j, valor in enumerate(items):
                    item = QTableWidgetItem(str(valor))
                    if j in [1, 2, 3, 4, 5]:  # Columnas numéricas
                        item.setTextAlignment(Qt.AlignRight | Qt.AlignVCenter)
                    self.tabla_marca.setItem(i, j, item)
            
            # Generar gráfico de torta
            self.grafico_marca.grafico_torta(
                datos, 
                titulo="Distribución de Ventas por Marca"
            )
            
            total_ventas = sum(float(fila[1]) for fila in datos)
            QMessageBox.information(self, "Éxito", f"Reporte de marca generado correctamente\nTotal de ventas: S/ {total_ventas:,.2f}")
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error generando reporte: {str(e)}")
    
    def generar_comparacion(self):
        """Generar comparación de períodos"""
        try:
            # Obtener fechas basadas en el período base
            fecha_base = self.date_periodo_base.date().toPython()
            tipo_comparacion = self.combo_tipo_comparacion.currentText().lower()
            
            # Calcular rango de fechas según el tipo
            if "semanas" in tipo_comparacion:
                fecha_desde = fecha_base - timedelta(days=28)  # 4 semanas atrás
                fecha_hasta = fecha_base
                tipo_db = 'semanas'
            elif "meses" in tipo_comparacion:
                fecha_desde = fecha_base - timedelta(days=120)  # 4 meses atrás
                fecha_hasta = fecha_base
                tipo_db = 'meses'
            elif "años" in tipo_comparacion:
                fecha_desde = fecha_base - timedelta(days=1460)  # 4 años atrás
                fecha_hasta = fecha_base
                tipo_db = 'años'
            else:
                fecha_desde = fecha_base - timedelta(days=28)
                fecha_hasta = fecha_base
                tipo_db = 'semanas'
            
            # Obtener datos reales de la base de datos
            datos = self.db.get_comparacion_ventas(fecha_desde, fecha_hasta, tipo_db)
            
            if not datos:
                QMessageBox.information(self, "Sin Datos", "No se encontraron ventas para la comparación seleccionada")
                return
            
            # Configurar tabla
            self.tabla_comparacion.setRowCount(len(datos))
            
            # Calcular variación
            for i, fila in enumerate(datos):
                # Formatear período
                if tipo_db == 'semanas':
                    periodo = f"Semana {fila[0]}"
                elif tipo_db == 'meses':
                    meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 
                            'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic']
                    periodo = f"{meses[int(fila[0])-1]}"
                elif tipo_db == 'años':
                    periodo = f"Año {fila[0]}"
                else:
                    periodo = str(fila[0])
                
                # Calcular variación
                if i > 0:
                    ventas_anterior = float(datos[i-1][1])
                    ventas_actual = float(fila[1])
                    if ventas_anterior > 0:
                        variacion = ((ventas_actual - ventas_anterior) / ventas_anterior) * 100
                        variacion_str = f"{variacion:+.1f}%"
                    else:
                        variacion_str = "N/A"
                else:
                    variacion_str = "Base"
                
                # Crear items de la tabla
                items = [
                    periodo,
                    f"S/ {float(fila[1]):,.2f}",
                    str(fila[2]),
                    f"S/ {float(fila[3]):,.2f}",
                    variacion_str
                ]
                
                for j, valor in enumerate(items):
                    item = QTableWidgetItem(str(valor))
                    if j in [1, 2, 3, 4]:  # Columnas numéricas
                        item.setTextAlignment(Qt.AlignRight | Qt.AlignVCenter)
                    self.tabla_comparacion.setItem(i, j, item)
            
            total_ventas = sum(float(fila[1]) for fila in datos)
            QMessageBox.information(self, "Éxito", f"Comparación generada correctamente\nTotal de ventas: S/ {total_ventas:,.2f}")
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error generando comparación: {str(e)}")
    
    def generar_analisis_avanzado(self):
        """Generar análisis avanzado"""
        try:
            # Obtener fechas (último mes por defecto)
            hoy = datetime.now().date()
            fecha_desde = hoy - timedelta(days=30)
            fecha_hasta = hoy
            
            # Obtener filtros
            usuario_id = self.combo_usuario.currentData()
            cliente_id = self.combo_cliente.currentData()
            venta_min = self.spin_venta_min.value()
            venta_max = self.spin_venta_max.value()
            
            # Obtener datos reales de la base de datos
            datos = self.db.get_ventas_avanzado(fecha_desde, fecha_hasta, usuario_id, cliente_id, venta_min, venta_max)
            
            if not datos:
                QMessageBox.information(self, "Sin Datos", "No se encontraron ventas para los filtros seleccionados")
                return
            
            # Configurar tabla
            self.tabla_avanzado.setRowCount(len(datos))
            
            for i, fila in enumerate(datos):
                # Calcular tendencia (simulada por ahora)
                tendencia = "📈"  # TODO: Implementar cálculo real de tendencia
                
                # Crear items de la tabla
                items = [
                    str(fila[0]),  # fecha
                    fila[1] or "N/A",  # usuario
                    fila[2] or "N/A",  # cliente
                    str(fila[3]),  # num_productos
                    f"S/ {float(fila[4]):,.2f}",  # ventas_totales
                    str(fila[5]),  # num_tickets
                    f"S/ {float(fila[6]):,.2f}",  # promedio_ticket
                    tendencia  # tendencia
                ]
                
                for j, valor in enumerate(items):
                    item = QTableWidgetItem(str(valor))
                    if j == 7:  # Columna de tendencia
                        item.setTextAlignment(Qt.AlignCenter)
                    elif j in [3, 4, 5, 6]:  # Columnas numéricas
                        item.setTextAlignment(Qt.AlignRight | Qt.AlignVCenter)
                    self.tabla_avanzado.setItem(i, j, item)
            
            total_ventas = sum(float(fila[4]) for fila in datos)
            QMessageBox.information(self, "Éxito", f"Análisis avanzado generado correctamente\nTotal de ventas: S/ {total_ventas:,.2f}")
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error generando análisis: {str(e)}")
    
    # Métodos de exportación
    def exportar_pdf(self):
        """Exportar reporte a PDF"""
        QMessageBox.information(self, "Exportar PDF", "Funcionalidad de exportación PDF en desarrollo")
    
    def exportar_excel(self):
        """Exportar reporte a Excel"""
        QMessageBox.information(self, "Exportar Excel", "Funcionalidad de exportación Excel en desarrollo")
    
    def imprimir_reporte(self):
        """Imprimir reporte"""
        QMessageBox.information(self, "Imprimir", "Funcionalidad de impresión en desarrollo")
    
    def limpiar_filtros(self):
        """Limpiar todos los filtros"""
        # Limpiar filtros de período
        self.combo_tipo_periodo.setCurrentIndex(0)
        self.date_desde.setDate(QDate.currentDate())
        self.date_hasta.setDate(QDate.currentDate())
        
        # Limpiar tablas
        self.tabla_periodo.setRowCount(0)
        self.tabla_jerarquia.setRowCount(0)
        self.tabla_marca.setRowCount(0)
        self.tabla_comparacion.setRowCount(0)
        self.tabla_avanzado.setRowCount(0)
        
        QMessageBox.information(self, "Limpiar", "Filtros limpiados correctamente")
