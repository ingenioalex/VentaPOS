import sys
from datetime import datetime, timedelta
from PySide6.QtWidgets import (
    QMainWindow, QWidget, QVBoxLayout, QHBoxLayout, QTabWidget,
    QTableWidget, QTableWidgetItem, QPushButton, QGroupBox,
    QLabel, QDateEdit, QComboBox, QSpinBox, QHeaderView,
    QMessageBox, QFrame, QGridLayout, QScrollArea
)
from PySide6.QtCore import Qt, QDate
from PySide6.QtGui import QFont, QColor
import matplotlib.pyplot as plt
from matplotlib.backends.backend_qt5agg import FigureCanvasQTAgg as FigureCanvas
from matplotlib.figure import Figure
import numpy as np

class GraficosCanvas(FigureCanvas):
    """Canvas para mostrar gráficos de usuarios"""
    
    def __init__(self, parent=None, width=5, height=4, dpi=100):
        self.fig = Figure(figsize=(width, height), dpi=dpi)
        super().__init__(self.fig)
        self.setParent(parent)
        
    def limpiar(self):
        """Limpiar el canvas"""
        self.fig.clear()
        self.draw()
    
    def grafico_barras_actividad(self, datos, titulo="Actividad de Usuarios"):
        """Crear gráfico de barras para actividad"""
        self.fig.clear()
        ax = self.fig.add_subplot(111)
        
        if not datos:
            ax.text(0.5, 0.5, 'No hay datos disponibles', 
                   ha='center', va='center', transform=ax.transAxes)
            self.draw()
            return
        
        usuarios = [f"{d[1][:15]}..." if len(d[1]) > 15 else d[1] for d in datos]
        sesiones = [d[4] for d in datos]  # total_sesiones
        
        bars = ax.bar(usuarios, sesiones, color='#2c3e50', alpha=0.7)
        ax.set_title(titulo, fontsize=12, fontweight='bold')
        ax.set_xlabel('Usuarios', fontsize=10)
        ax.set_ylabel('Total Sesiones', fontsize=10)
        
        # Rotar etiquetas del eje X
        ax.tick_params(axis='x', rotation=45, labelsize=8)
        ax.tick_params(axis='y', labelsize=8)
        
        # Agregar valores en las barras
        for bar, valor in zip(bars, sesiones):
            height = bar.get_height()
            ax.text(bar.get_x() + bar.get_width()/2., height + 0.1,
                   f'{valor}', ha='center', va='bottom', fontsize=8)
        
        self.fig.tight_layout()
        self.draw()
    
    def grafico_torta_roles(self, datos, titulo="Distribución por Roles"):
        """Crear gráfico de torta para roles"""
        self.fig.clear()
        ax = self.fig.add_subplot(111)
        
        if not datos:
            ax.text(0.5, 0.5, 'No hay datos disponibles', 
                   ha='center', va='center', transform=ax.transAxes)
            self.draw()
            return
        
        roles = ['admin', 'jefe', 'supervisor', 'cajero', 'barras']
        colores = ['#e74c3c', '#f39c12', '#3498db', '#2ecc71', '#9b59b6']
        valores = [datos.get(rol, 0) for rol in roles]
        
        # Filtrar roles con valor 0
        roles_filtrados = []
        valores_filtrados = []
        colores_filtrados = []
        
        for i, valor in enumerate(valores):
            if valor > 0:
                roles_filtrados.append(roles[i].title())
                valores_filtrados.append(valor)
                colores_filtrados.append(colores[i])
        
        if valores_filtrados:
            wedges, texts, autotexts = ax.pie(valores_filtrados, labels=roles_filtrados, 
                                            colors=colores_filtrados, autopct='%1.1f%%',
                                            startangle=90, textprops={'fontsize': 9})
            ax.set_title(titulo, fontsize=12, fontweight='bold')
        
        self.fig.tight_layout()
        self.draw()
    
    def grafico_lineas_horarios(self, datos, titulo="Actividad por Horarios"):
        """Crear gráfico de líneas para horarios"""
        self.fig.clear()
        ax = self.fig.add_subplot(111)
        
        if not datos:
            ax.text(0.5, 0.5, 'No hay datos disponibles', 
                   ha='center', va='center', transform=ax.transAxes)
            self.draw()
            return
        
        horas = [int(d[0]) for d in datos]
        sesiones = [d[1] for d in datos]
        usuarios = [d[2] for d in datos]
        
        ax.plot(horas, sesiones, marker='o', linewidth=2, markersize=6, 
               color='#2c3e50', label='Total Sesiones')
        ax.plot(horas, usuarios, marker='s', linewidth=2, markersize=6, 
               color='#e74c3c', label='Usuarios Únicos')
        
        ax.set_title(titulo, fontsize=12, fontweight='bold')
        ax.set_xlabel('Hora del Día', fontsize=10)
        ax.set_ylabel('Cantidad', fontsize=10)
        ax.legend(fontsize=9)
        ax.grid(True, alpha=0.3)
        ax.tick_params(labelsize=8)
        
        # Configurar eje X para mostrar todas las horas
        ax.set_xticks(range(0, 24, 2))
        ax.set_xlim(-0.5, 23.5)
        
        self.fig.tight_layout()
        self.draw()

class ReportesUsuariosForm(QMainWindow):
    """Formulario para reportes de usuarios"""
    
    def __init__(self, db_client, usuario_actual):
        super().__init__()
        self.db = db_client
        self.usuario_actual = usuario_actual
        self.init_ui()
        self.apply_styles()
        self.cargar_datos_iniciales()
    
    def init_ui(self):
        """Inicializar la interfaz de usuario"""
        self.setWindowTitle("📊 Reportes de Usuarios - RMS")
        self.setGeometry(100, 100, 1400, 800)
        
        # Widget central
        central_widget = QWidget()
        self.setCentralWidget(central_widget)
        
        # Layout principal
        main_layout = QVBoxLayout(central_widget)
        main_layout.setSpacing(10)
        main_layout.setContentsMargins(10, 10, 10, 10)
        
        # Título
        title_label = QLabel("📊 Reportes de Usuarios")
        title_label.setAlignment(Qt.AlignCenter)
        title_font = QFont()
        title_font.setPointSize(16)
        title_font.setBold(True)
        title_label.setFont(title_font)
        main_layout.addWidget(title_label)
        
        # Widget de pestañas
        self.tab_widget = QTabWidget()
        main_layout.addWidget(self.tab_widget)
        
        # Crear pestañas
        self.create_tab_actividad()
        self.create_tab_ventas()
        self.create_tab_sesiones()
        self.create_tab_horarios()
        self.create_tab_inactivos()
        self.create_tab_estadisticas()
    
    def create_tab_actividad(self):
        """Crear pestaña de actividad de usuarios"""
        tab = QWidget()
        layout = QVBoxLayout(tab)
        
        # Controles de filtro
        filtros_group = QGroupBox("Filtros")
        filtros_layout = QHBoxLayout(filtros_group)
        
        filtros_layout.addWidget(QLabel("Desde:"))
        self.fecha_desde_actividad = QDateEdit()
        self.fecha_desde_actividad.setDate(QDate.currentDate().addDays(-30))
        self.fecha_desde_actividad.setCalendarPopup(True)
        filtros_layout.addWidget(self.fecha_desde_actividad)
        
        filtros_layout.addWidget(QLabel("Hasta:"))
        self.fecha_hasta_actividad = QDateEdit()
        self.fecha_hasta_actividad.setDate(QDate.currentDate())
        self.fecha_hasta_actividad.setCalendarPopup(True)
        filtros_layout.addWidget(self.fecha_hasta_actividad)
        
        self.btn_generar_actividad = QPushButton("🔄 Generar Reporte")
        self.btn_generar_actividad.clicked.connect(self.generar_reporte_actividad)
        filtros_layout.addWidget(self.btn_generar_actividad)
        
        filtros_layout.addStretch()
        layout.addWidget(filtros_group)
        
        # Layout para tabla y gráfico
        content_layout = QHBoxLayout()
        
        # Tabla de actividad
        table_group = QGroupBox("Actividad de Usuarios")
        table_layout = QVBoxLayout(table_group)
        
        self.tbl_actividad = QTableWidget()
        self.tbl_actividad.setColumnCount(8)
        self.tbl_actividad.setHorizontalHeaderLabels([
            "ID", "Nombre", "Usuario", "Rol", "Sesiones", "Logins", "Logouts", "Estado"
        ])
        
        # Configurar tabla
        header = self.tbl_actividad.horizontalHeader()
        header.setSectionResizeMode(0, QHeaderView.ResizeToContents)  # ID
        header.setSectionResizeMode(1, QHeaderView.Stretch)  # Nombre
        header.setSectionResizeMode(2, QHeaderView.ResizeToContents)  # Usuario
        header.setSectionResizeMode(3, QHeaderView.ResizeToContents)  # Rol
        header.setSectionResizeMode(4, QHeaderView.ResizeToContents)  # Sesiones
        header.setSectionResizeMode(5, QHeaderView.ResizeToContents)  # Logins
        header.setSectionResizeMode(6, QHeaderView.ResizeToContents)  # Logouts
        header.setSectionResizeMode(7, QHeaderView.ResizeToContents)  # Estado
        
        table_layout.addWidget(self.tbl_actividad)
        content_layout.addWidget(table_group, 1)
        
        # Gráfico
        grafico_group = QGroupBox("Gráfico de Actividad")
        grafico_layout = QVBoxLayout(grafico_group)
        
        self.grafico_actividad = GraficosCanvas(self, width=6, height=4)
        grafico_layout.addWidget(self.grafico_actividad)
        
        content_layout.addWidget(grafico_group, 1)
        layout.addLayout(content_layout)
        
        self.tab_widget.addTab(tab, "📈 Actividad")
    
    def create_tab_ventas(self):
        """Crear pestaña de ventas por usuario"""
        tab = QWidget()
        layout = QVBoxLayout(tab)
        
        # Controles de filtro
        filtros_group = QGroupBox("Filtros")
        filtros_layout = QHBoxLayout(filtros_group)
        
        filtros_layout.addWidget(QLabel("Desde:"))
        self.fecha_desde_ventas = QDateEdit()
        self.fecha_desde_ventas.setDate(QDate.currentDate().addDays(-30))
        self.fecha_desde_ventas.setCalendarPopup(True)
        filtros_layout.addWidget(self.fecha_desde_ventas)
        
        filtros_layout.addWidget(QLabel("Hasta:"))
        self.fecha_hasta_ventas = QDateEdit()
        self.fecha_hasta_ventas.setDate(QDate.currentDate())
        self.fecha_hasta_ventas.setCalendarPopup(True)
        filtros_layout.addWidget(self.fecha_hasta_ventas)
        
        self.btn_generar_ventas = QPushButton("🔄 Generar Reporte")
        self.btn_generar_ventas.clicked.connect(self.generar_reporte_ventas)
        filtros_layout.addWidget(self.btn_generar_ventas)
        
        filtros_layout.addStretch()
        layout.addWidget(filtros_group)
        
        # Tabla de ventas
        table_group = QGroupBox("Ventas por Usuario")
        table_layout = QVBoxLayout(table_group)
        
        self.tbl_ventas = QTableWidget()
        self.tbl_ventas.setColumnCount(7)
        self.tbl_ventas.setHorizontalHeaderLabels([
            "ID", "Nombre", "Usuario", "Rol", "Ventas", "Monto Total", "Promedio"
        ])
        
        # Configurar tabla
        header = self.tbl_ventas.horizontalHeader()
        header.setSectionResizeMode(0, QHeaderView.ResizeToContents)  # ID
        header.setSectionResizeMode(1, QHeaderView.Stretch)  # Nombre
        header.setSectionResizeMode(2, QHeaderView.ResizeToContents)  # Usuario
        header.setSectionResizeMode(3, QHeaderView.ResizeToContents)  # Rol
        header.setSectionResizeMode(4, QHeaderView.ResizeToContents)  # Ventas
        header.setSectionResizeMode(5, QHeaderView.ResizeToContents)  # Monto Total
        header.setSectionResizeMode(6, QHeaderView.ResizeToContents)  # Promedio
        
        table_layout.addWidget(self.tbl_ventas)
        layout.addWidget(table_group)
        
        self.tab_widget.addTab(tab, "💰 Ventas")
    
    def create_tab_sesiones(self):
        """Crear pestaña de sesiones detalladas"""
        tab = QWidget()
        layout = QVBoxLayout(tab)
        
        # Controles de filtro
        filtros_group = QGroupBox("Filtros")
        filtros_layout = QHBoxLayout(filtros_group)
        
        filtros_layout.addWidget(QLabel("Desde:"))
        self.fecha_desde_sesiones = QDateEdit()
        self.fecha_desde_sesiones.setDate(QDate.currentDate().addDays(-7))
        self.fecha_desde_sesiones.setCalendarPopup(True)
        filtros_layout.addWidget(self.fecha_desde_sesiones)
        
        filtros_layout.addWidget(QLabel("Hasta:"))
        self.fecha_hasta_sesiones = QDateEdit()
        self.fecha_hasta_sesiones.setDate(QDate.currentDate())
        self.fecha_hasta_sesiones.setCalendarPopup(True)
        filtros_layout.addWidget(self.fecha_hasta_sesiones)
        
        filtros_layout.addWidget(QLabel("Usuario:"))
        self.combo_usuario_sesiones = QComboBox()
        self.combo_usuario_sesiones.addItem("Todos los usuarios")
        filtros_layout.addWidget(self.combo_usuario_sesiones)
        
        self.btn_generar_sesiones = QPushButton("🔄 Generar Reporte")
        self.btn_generar_sesiones.clicked.connect(self.generar_reporte_sesiones)
        filtros_layout.addWidget(self.btn_generar_sesiones)
        
        filtros_layout.addStretch()
        layout.addWidget(filtros_group)
        
        # Tabla de sesiones
        table_group = QGroupBox("Sesiones Detalladas")
        table_layout = QVBoxLayout(table_group)
        
        self.tbl_sesiones = QTableWidget()
        self.tbl_sesiones.setColumnCount(9)
        self.tbl_sesiones.setHorizontalHeaderLabels([
            "ID", "Usuario", "Nombre", "Rol", "Terminal", "Tipo", "Inicio", "Fin", "Estado"
        ])
        
        # Configurar tabla
        header = self.tbl_sesiones.horizontalHeader()
        header.setSectionResizeMode(0, QHeaderView.ResizeToContents)  # ID
        header.setSectionResizeMode(1, QHeaderView.ResizeToContents)  # Usuario
        header.setSectionResizeMode(2, QHeaderView.Stretch)  # Nombre
        header.setSectionResizeMode(3, QHeaderView.ResizeToContents)  # Rol
        header.setSectionResizeMode(4, QHeaderView.ResizeToContents)  # Terminal
        header.setSectionResizeMode(5, QHeaderView.ResizeToContents)  # Tipo
        header.setSectionResizeMode(6, QHeaderView.ResizeToContents)  # Inicio
        header.setSectionResizeMode(7, QHeaderView.ResizeToContents)  # Fin
        header.setSectionResizeMode(8, QHeaderView.ResizeToContents)  # Estado
        
        table_layout.addWidget(self.tbl_sesiones)
        layout.addWidget(table_group)
        
        self.tab_widget.addTab(tab, "🔐 Sesiones")
    
    def create_tab_horarios(self):
        """Crear pestaña de análisis de horarios"""
        tab = QWidget()
        layout = QVBoxLayout(tab)
        
        # Controles de filtro
        filtros_group = QGroupBox("Filtros")
        filtros_layout = QHBoxLayout(filtros_group)
        
        filtros_layout.addWidget(QLabel("Desde:"))
        self.fecha_desde_horarios = QDateEdit()
        self.fecha_desde_horarios.setDate(QDate.currentDate().addDays(-30))
        self.fecha_desde_horarios.setCalendarPopup(True)
        filtros_layout.addWidget(self.fecha_desde_horarios)
        
        filtros_layout.addWidget(QLabel("Hasta:"))
        self.fecha_hasta_horarios = QDateEdit()
        self.fecha_hasta_horarios.setDate(QDate.currentDate())
        self.fecha_hasta_horarios.setCalendarPopup(True)
        filtros_layout.addWidget(self.fecha_hasta_horarios)
        
        self.btn_generar_horarios = QPushButton("🔄 Generar Reporte")
        self.btn_generar_horarios.clicked.connect(self.generar_reporte_horarios)
        filtros_layout.addWidget(self.btn_generar_horarios)
        
        filtros_layout.addStretch()
        layout.addWidget(filtros_group)
        
        # Layout para tabla y gráfico
        content_layout = QHBoxLayout()
        
        # Tabla de horarios
        table_group = QGroupBox("Actividad por Horarios")
        table_layout = QVBoxLayout(table_group)
        
        self.tbl_horarios = QTableWidget()
        self.tbl_horarios.setColumnCount(5)
        self.tbl_horarios.setHorizontalHeaderLabels([
            "Hora", "Sesiones", "Usuarios Únicos", "Logins", "Logouts"
        ])
        
        # Configurar tabla
        header = self.tbl_horarios.horizontalHeader()
        header.setSectionResizeMode(0, QHeaderView.ResizeToContents)  # Hora
        header.setSectionResizeMode(1, QHeaderView.ResizeToContents)  # Sesiones
        header.setSectionResizeMode(2, QHeaderView.ResizeToContents)  # Usuarios Únicos
        header.setSectionResizeMode(3, QHeaderView.ResizeToContents)  # Logins
        header.setSectionResizeMode(4, QHeaderView.ResizeToContents)  # Logouts
        
        table_layout.addWidget(self.tbl_horarios)
        content_layout.addWidget(table_group, 1)
        
        # Gráfico
        grafico_group = QGroupBox("Gráfico de Horarios")
        grafico_layout = QVBoxLayout(grafico_group)
        
        self.grafico_horarios = GraficosCanvas(self, width=6, height=4)
        grafico_layout.addWidget(self.grafico_horarios)
        
        content_layout.addWidget(grafico_group, 1)
        layout.addLayout(content_layout)
        
        self.tab_widget.addTab(tab, "⏰ Horarios")
    
    def create_tab_inactivos(self):
        """Crear pestaña de usuarios inactivos"""
        tab = QWidget()
        layout = QVBoxLayout(tab)
        
        # Controles de filtro
        filtros_group = QGroupBox("Filtros")
        filtros_layout = QHBoxLayout(filtros_group)
        
        filtros_layout.addWidget(QLabel("Días de inactividad:"))
        self.spin_dias_inactivos = QSpinBox()
        self.spin_dias_inactivos.setMinimum(1)
        self.spin_dias_inactivos.setMaximum(365)
        self.spin_dias_inactivos.setValue(7)
        filtros_layout.addWidget(self.spin_dias_inactivos)
        
        self.btn_generar_inactivos = QPushButton("🔄 Generar Reporte")
        self.btn_generar_inactivos.clicked.connect(self.generar_reporte_inactivos)
        filtros_layout.addWidget(self.btn_generar_inactivos)
        
        filtros_layout.addStretch()
        layout.addWidget(filtros_group)
        
        # Tabla de usuarios inactivos
        table_group = QGroupBox("Usuarios Inactivos")
        table_layout = QVBoxLayout(table_group)
        
        self.tbl_inactivos = QTableWidget()
        self.tbl_inactivos.setColumnCount(7)
        self.tbl_inactivos.setHorizontalHeaderLabels([
            "ID", "Nombre", "Usuario", "Rol", "Estado", "Última Sesión", "Días Inactivo"
        ])
        
        # Configurar tabla
        header = self.tbl_inactivos.horizontalHeader()
        header.setSectionResizeMode(0, QHeaderView.ResizeToContents)  # ID
        header.setSectionResizeMode(1, QHeaderView.Stretch)  # Nombre
        header.setSectionResizeMode(2, QHeaderView.ResizeToContents)  # Usuario
        header.setSectionResizeMode(3, QHeaderView.ResizeToContents)  # Rol
        header.setSectionResizeMode(4, QHeaderView.ResizeToContents)  # Estado
        header.setSectionResizeMode(5, QHeaderView.ResizeToContents)  # Última Sesión
        header.setSectionResizeMode(6, QHeaderView.ResizeToContents)  # Días Inactivo
        
        table_layout.addWidget(self.tbl_inactivos)
        layout.addWidget(table_group)
        
        self.tab_widget.addTab(tab, "😴 Inactivos")
    
    def create_tab_estadisticas(self):
        """Crear pestaña de estadísticas generales"""
        tab = QWidget()
        layout = QVBoxLayout(tab)
        
        # Botón para actualizar
        btn_layout = QHBoxLayout()
        self.btn_actualizar_estadisticas = QPushButton("🔄 Actualizar Estadísticas")
        self.btn_actualizar_estadisticas.clicked.connect(self.actualizar_estadisticas)
        btn_layout.addWidget(self.btn_actualizar_estadisticas)
        btn_layout.addStretch()
        layout.addLayout(btn_layout)
        
        # Layout para métricas y gráfico
        content_layout = QHBoxLayout()
        
        # Métricas
        metrics_group = QGroupBox("Estadísticas Generales")
        metrics_layout = QGridLayout(metrics_group)
        
        # Crear etiquetas para métricas
        self.lbl_total_usuarios = QLabel("0")
        self.lbl_usuarios_conectados = QLabel("0")
        self.lbl_usuarios_desconectados = QLabel("0")
        self.lbl_administradores = QLabel("0")
        self.lbl_jefes = QLabel("0")
        self.lbl_supervisores = QLabel("0")
        self.lbl_cajeros = QLabel("0")
        self.lbl_barras = QLabel("0")
        self.lbl_usuarios_activos = QLabel("0")
        self.lbl_usuarios_inactivos = QLabel("0")
        
        # Configurar estilo de métricas
        metric_font = QFont()
        metric_font.setPointSize(14)
        metric_font.setBold(True)
        
        for lbl in [self.lbl_total_usuarios, self.lbl_usuarios_conectados, 
                   self.lbl_usuarios_desconectados, self.lbl_administradores,
                   self.lbl_jefes, self.lbl_supervisores, self.lbl_cajeros,
                   self.lbl_barras, self.lbl_usuarios_activos, self.lbl_usuarios_inactivos]:
            lbl.setFont(metric_font)
            lbl.setAlignment(Qt.AlignCenter)
            lbl.setStyleSheet("background-color: #ecf0f1; padding: 10px; border-radius: 5px;")
        
        # Organizar métricas en grid
        metrics_layout.addWidget(QLabel("Total Usuarios:"), 0, 0)
        metrics_layout.addWidget(self.lbl_total_usuarios, 0, 1)
        metrics_layout.addWidget(QLabel("Conectados:"), 0, 2)
        metrics_layout.addWidget(self.lbl_usuarios_conectados, 0, 3)
        
        metrics_layout.addWidget(QLabel("Desconectados:"), 1, 0)
        metrics_layout.addWidget(self.lbl_usuarios_desconectados, 1, 1)
        metrics_layout.addWidget(QLabel("Administradores:"), 1, 2)
        metrics_layout.addWidget(self.lbl_administradores, 1, 3)
        
        metrics_layout.addWidget(QLabel("Jefes:"), 2, 0)
        metrics_layout.addWidget(self.lbl_jefes, 2, 1)
        metrics_layout.addWidget(QLabel("Supervisores:"), 2, 2)
        metrics_layout.addWidget(self.lbl_supervisores, 2, 3)
        
        metrics_layout.addWidget(QLabel("Cajeros:"), 3, 0)
        metrics_layout.addWidget(self.lbl_cajeros, 3, 1)
        metrics_layout.addWidget(QLabel("Barras:"), 3, 2)
        metrics_layout.addWidget(self.lbl_barras, 3, 3)
        
        metrics_layout.addWidget(QLabel("Activos:"), 4, 0)
        metrics_layout.addWidget(self.lbl_usuarios_activos, 4, 1)
        metrics_layout.addWidget(QLabel("Inactivos:"), 4, 2)
        metrics_layout.addWidget(self.lbl_usuarios_inactivos, 4, 3)
        
        content_layout.addWidget(metrics_group, 1)
        
        # Gráfico de distribución por roles
        grafico_group = QGroupBox("Distribución por Roles")
        grafico_layout = QVBoxLayout(grafico_group)
        
        self.grafico_roles = GraficosCanvas(self, width=6, height=4)
        grafico_layout.addWidget(self.grafico_roles)
        
        content_layout.addWidget(grafico_group, 1)
        layout.addLayout(content_layout)
        
        self.tab_widget.addTab(tab, "📊 Estadísticas")
    
    def apply_styles(self):
        """Aplicar estilos CSS"""
        self.setStyleSheet("""
            QMainWindow {
                background-color: #f5f5f5;
            }
            QTabWidget::pane {
                border: 1px solid #bdc3c7;
                background-color: white;
            }
            QTabBar::tab {
                background-color: #ecf0f1;
                padding: 8px 16px;
                margin-right: 2px;
                border-top-left-radius: 4px;
                border-top-right-radius: 4px;
                font-size: 11px;
            }
            QTabBar::tab:selected {
                background-color: white;
                border-bottom: 2px solid #3498db;
            }
            QTableWidget {
                gridline-color: #bdc3c7;
                background-color: white;
                alternate-background-color: #f8f9fa;
                font-size: 10px;
            }
            QTableWidget::item {
                padding: 5px;
                border-bottom: 1px solid #ecf0f1;
                color: #2c3e50;
            }
            QTableWidget::item:alternate {
                background-color: #f8f9fa;
                color: #2c3e50;
            }
            QHeaderView::section {
                background-color: #34495e;
                color: white;
                padding: 8px;
                border-right: 1px solid #2c3e50;
                font-size: 11px;
                font-weight: bold;
            }
            QPushButton {
                background-color: #3498db;
                color: white;
                border: none;
                padding: 8px 16px;
                border-radius: 4px;
                font-size: 11px;
                font-weight: bold;
            }
            QPushButton:hover {
                background-color: #2980b9;
            }
            QPushButton:pressed {
                background-color: #21618c;
            }
            QGroupBox {
                font-size: 12px;
                font-weight: bold;
                color: #2c3e50;
                border: 2px solid #bdc3c7;
                border-radius: 5px;
                margin-top: 10px;
                padding-top: 10px;
            }
            QGroupBox::title {
                subcontrol-origin: margin;
                left: 10px;
                padding: 0 5px 0 5px;
                font-size: 12px;
            }
            QLabel {
                color: #2c3e50;
                font-size: 10px;
            }
            QDateEdit, QComboBox, QSpinBox {
                padding: 5px;
                border: 1px solid #bdc3c7;
                border-radius: 3px;
                font-size: 10px;
                background-color: white;
            }
        """)
    
    def cargar_datos_iniciales(self):
        """Cargar datos iniciales para los combos"""
        try:
            # Cargar usuarios para el combo de sesiones
            usuarios = self.db.get_usuarios()
            for usuario in usuarios:
                self.combo_usuario_sesiones.addItem(f"{usuario[1]} ({usuario[2]})", usuario[0])
            
            # Cargar estadísticas iniciales
            self.actualizar_estadisticas()
            
        except Exception as e:
            print(f"Error cargando datos iniciales: {e}")
    
    def generar_reporte_actividad(self):
        """Generar reporte de actividad de usuarios"""
        try:
            fecha_desde = self.fecha_desde_actividad.date().toPython()
            fecha_hasta = self.fecha_hasta_actividad.date().toPython()
            
            datos = self.db.get_actividad_usuarios(fecha_desde, fecha_hasta)
            
            # Llenar tabla
            self.tbl_actividad.setRowCount(len(datos))
            for row, data in enumerate(datos):
                for col, value in enumerate(data):
                    item = QTableWidgetItem(str(value))
                    
                    # Colorear estado
                    if col == 7:  # Estado
                        if value == 'si':
                            item.setBackground(QColor(200, 255, 200))
                            item.setText("Conectado")
                        else:
                            item.setBackground(QColor(255, 200, 200))
                            item.setText("Desconectado")
                    
                    self.tbl_actividad.setItem(row, col, item)
            
            # Generar gráfico
            self.grafico_actividad.grafico_barras_actividad(datos, "Actividad de Usuarios")
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error generando reporte de actividad: {e}")
    
    def generar_reporte_ventas(self):
        """Generar reporte de ventas por usuario"""
        try:
            fecha_desde = self.fecha_desde_ventas.date().toPython()
            fecha_hasta = self.fecha_hasta_ventas.date().toPython()
            
            datos = self.db.get_ventas_por_usuario(fecha_desde, fecha_hasta)
            
            # Llenar tabla
            self.tbl_ventas.setRowCount(len(datos))
            for row, data in enumerate(datos):
                for col, value in enumerate(data):
                    if col == 5:  # Monto Total
                        item = QTableWidgetItem(f"S/ {float(value):.2f}")
                    elif col == 6:  # Promedio
                        item = QTableWidgetItem(f"S/ {float(value):.2f}")
                    else:
                        item = QTableWidgetItem(str(value))
                    
                    self.tbl_ventas.setItem(row, col, item)
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error generando reporte de ventas: {e}")
    
    def generar_reporte_sesiones(self):
        """Generar reporte de sesiones detalladas"""
        try:
            fecha_desde = self.fecha_desde_sesiones.date().toPython()
            fecha_hasta = self.fecha_hasta_sesiones.date().toPython()
            
            # Obtener usuario seleccionado
            usuario_id = None
            if self.combo_usuario_sesiones.currentIndex() > 0:
                usuario_id = self.combo_usuario_sesiones.currentData()
            
            datos = self.db.get_sesiones_detalladas(fecha_desde, fecha_hasta, usuario_id)
            
            # Llenar tabla
            self.tbl_sesiones.setRowCount(len(datos))
            for row, data in enumerate(datos):
                for col, value in enumerate(data):
                    if col == 6:  # Fecha registro
                        item = QTableWidgetItem(str(value)[:19] if value else "")
                    elif col == 7:  # Fecha logout
                        item = QTableWidgetItem(str(value)[:19] if value else "")
                    elif col == 8:  # Estado sesión
                        item = QTableWidgetItem(str(value))
                        if value == 'Activa':
                            item.setBackground(QColor(200, 255, 200))
                        elif value == 'Cerrada':
                            item.setBackground(QColor(200, 200, 255))
                        else:
                            item.setBackground(QColor(255, 255, 200))
                    else:
                        item = QTableWidgetItem(str(value))
                    
                    self.tbl_sesiones.setItem(row, col, item)
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error generando reporte de sesiones: {e}")
    
    def generar_reporte_horarios(self):
        """Generar reporte de análisis de horarios"""
        try:
            fecha_desde = self.fecha_desde_horarios.date().toPython()
            fecha_hasta = self.fecha_hasta_horarios.date().toPython()
            
            datos = self.db.get_analisis_horarios_actividad(fecha_desde, fecha_hasta)
            
            # Llenar tabla
            self.tbl_horarios.setRowCount(len(datos))
            for row, data in enumerate(datos):
                for col, value in enumerate(data):
                    if col == 0:  # Hora
                        item = QTableWidgetItem(f"{int(value):02d}:00")
                    else:
                        item = QTableWidgetItem(str(int(value)))
                    
                    self.tbl_horarios.setItem(row, col, item)
            
            # Generar gráfico
            self.grafico_horarios.grafico_lineas_horarios(datos, "Actividad por Horarios")
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error generando reporte de horarios: {e}")
    
    def generar_reporte_inactivos(self):
        """Generar reporte de usuarios inactivos"""
        try:
            dias_inactividad = self.spin_dias_inactivos.value()
            
            datos = self.db.get_usuarios_inactivos(dias_inactividad)
            
            # Llenar tabla
            self.tbl_inactivos.setRowCount(len(datos))
            for row, data in enumerate(datos):
                for col, value in enumerate(data):
                    if col == 4:  # Estado conectado
                        if value == 'si':
                            item = QTableWidgetItem("Conectado")
                            item.setBackground(QColor(200, 255, 200))
                        else:
                            item = QTableWidgetItem("Desconectado")
                            item.setBackground(QColor(255, 200, 200))
                    elif col == 5:  # Última sesión
                        item = QTableWidgetItem(str(value)[:19] if value else "Nunca")
                    elif col == 6:  # Días inactivo
                        item = QTableWidgetItem(str(int(value)) if value else "∞")
                        if value and value > 30:
                            item.setBackground(QColor(255, 200, 200))
                        elif value and value > 14:
                            item.setBackground(QColor(255, 255, 200))
                    else:
                        item = QTableWidgetItem(str(value))
                    
                    self.tbl_inactivos.setItem(row, col, item)
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error generando reporte de inactivos: {e}")
    
    def actualizar_estadisticas(self):
        """Actualizar estadísticas generales"""
        try:
            datos = self.db.get_estadisticas_usuarios_generales()
            
            if datos:
                self.lbl_total_usuarios.setText(str(datos.get('total_usuarios', 0)))
                self.lbl_usuarios_conectados.setText(str(datos.get('usuarios_conectados', 0)))
                self.lbl_usuarios_desconectados.setText(str(datos.get('usuarios_desconectados', 0)))
                self.lbl_administradores.setText(str(datos.get('administradores', 0)))
                self.lbl_jefes.setText(str(datos.get('jefes', 0)))
                self.lbl_supervisores.setText(str(datos.get('supervisores', 0)))
                self.lbl_cajeros.setText(str(datos.get('cajeros', 0)))
                self.lbl_barras.setText(str(datos.get('barras', 0)))
                self.lbl_usuarios_activos.setText(str(datos.get('usuarios_activos', 0)))
                self.lbl_usuarios_inactivos.setText(str(datos.get('usuarios_inactivos', 0)))
                
                # Generar gráfico de distribución por roles
                self.grafico_roles.grafico_torta_roles(datos, "Distribución por Roles")
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error actualizando estadísticas: {e}")
