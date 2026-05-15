import sys
from PySide6.QtWidgets import (QWidget, QMainWindow, QVBoxLayout, QHBoxLayout, QGridLayout, 
                               QLabel, QFrame, QScrollArea, QGroupBox, QPushButton,
                               QTableWidget, QTableWidgetItem, QHeaderView, QSizePolicy, QMessageBox,
                               QComboBox, QDateEdit)
from PySide6.QtCore import Qt, QTimer, QThread, Signal, QDate
from PySide6.QtGui import QFont, QPixmap, QPainter, QColor, QLinearGradient
from datetime import datetime, timedelta
import json

class DashboardWorker(QThread):
    """Worker thread para actualizar datos del panel principal sin bloquear la UI"""
    data_updated = Signal(dict)
    
    def __init__(self, db):
        super().__init__()
        self.db = db
        self.running = True
    
    def run(self):
        while self.running:
            try:
                # Obtener datos del panel principal
                data = self.get_dashboard_data()
                self.data_updated.emit(data)
                
                # Verificar si debe continuar cada 5 segundos
                for _ in range(12):  # 12 * 5 = 60 segundos
                    if not self.running:
                        break
                    self.msleep(5000)
                    
            except Exception as e:
                print(f"Error en DashboardWorker: {e}")
                if not self.running:
                    break
                self.msleep(10000)  # Esperar más tiempo en caso de error
    
    def get_dashboard_data(self):
        """Obtener todos los datos necesarios para el panel principal"""
        try:
            # Datos del día actual
            hoy = datetime.now().date()
            
            # Ventas del día
            ventas_dia = self.db.get_ventas_del_dia(hoy)
            total_ventas = sum(v[1] for v in ventas_dia) if ventas_dia else 0
            num_tickets = len(ventas_dia)
            promedio_ticket = total_ventas / num_tickets if num_tickets > 0 else 0
            
            # Ventas por hora (últimas 12 horas)
            ventas_por_hora = self.db.get_ventas_por_hora(hoy)
            
            # Top productos del día
            top_productos = self.db.get_top_productos_dia(hoy, limit=5)
            
            # Productos con stock bajo
            stock_bajo = self.db.get_productos_stock_bajo()
            
            # Estado de caja actual
            estado_caja = self.db.get_estado_caja_actual()
            
            # Comparación con día anterior
            ayer = hoy - timedelta(days=1)
            ventas_ayer = self.db.get_ventas_del_dia(ayer)
            total_ayer = sum(v[1] for v in ventas_ayer) if ventas_ayer else 0
            
            return {
                'ventas_dia': total_ventas,
                'num_tickets': num_tickets,
                'promedio_ticket': promedio_ticket,
                'ventas_por_hora': ventas_por_hora,
                'top_productos': top_productos,
                'stock_bajo': stock_bajo,
                'estado_caja': estado_caja,
                'ventas_ayer': total_ayer,
                'cambio_ventas': total_ventas - total_ayer
            }
        except Exception as e:
            print(f"Error obteniendo datos del panel principal: {e}")
            return {}

class DashboardForm(QMainWindow):
    def __init__(self, db, usuario_actual):
        super().__init__()
        self.db = db
        self.usuario_actual = usuario_actual
        self.worker = None
        self.fecha_actual = datetime.now().date()
        self.fecha_seleccionada = self.fecha_actual
        self.init_ui()
        self.setup_timer()
        self.load_initial_data()
    
    def init_ui(self):
        """Inicializar la interfaz del panel principal"""
        self.setWindowTitle("Panel principal - RMS")
        self.setMinimumSize(1200, 800)
        self.resize(1400, 900)
        
        # Configurar flags de ventana para permitir maximización
        self.setWindowFlags(Qt.Window | Qt.WindowMaximizeButtonHint | Qt.WindowMinimizeButtonHint | Qt.WindowCloseButtonHint)
        
        # Widget central
        central_widget = QWidget()
        self.setCentralWidget(central_widget)
        
        # Layout principal
        main_layout = QVBoxLayout(central_widget)
        main_layout.setSpacing(15)
        main_layout.setContentsMargins(20, 20, 20, 20)
        
        # Fila superior con título y controles de fecha
        header_layout = QHBoxLayout()
        
        # Título
        title = QLabel("📊 PANEL PRINCIPAL")
        title.setObjectName("dashboard_title")
        title.setAlignment(Qt.AlignCenter)
        header_layout.addWidget(title)
        
        # Controles de fecha en la esquina derecha
        fecha_controls = self.create_fecha_controls()
        header_layout.addWidget(fecha_controls)
        
        main_layout.addLayout(header_layout)
        
        # Scroll area para contenido
        scroll = QScrollArea()
        scroll.setWidgetResizable(True)
        scroll.setVerticalScrollBarPolicy(Qt.ScrollBarAsNeeded)
        scroll.setHorizontalScrollBarPolicy(Qt.ScrollBarAlwaysOff)
        
        # Widget de contenido
        content_widget = QWidget()
        content_layout = QVBoxLayout(content_widget)
        content_layout.setSpacing(20)
        
        # Fila superior - Métricas principales
        metrics_row = self.create_metrics_row()
        content_layout.addLayout(metrics_row)
        
        # Fila media - Gráficos y alertas
        middle_row = QHBoxLayout()
        
        # Columna izquierda - Gráfico de ventas por hora
        chart_widget = self.create_sales_chart()
        middle_row.addWidget(chart_widget, 2)
        
        # Columna derecha - Alertas y estado
        alerts_widget = self.create_alerts_widget()
        middle_row.addWidget(alerts_widget, 1)
        
        content_layout.addLayout(middle_row)
        
        # Fila inferior - Tablas de datos
        bottom_row = QHBoxLayout()
        
        # Top productos
        top_products_widget = self.create_top_products_widget()
        bottom_row.addWidget(top_products_widget, 1)
        
        # Stock bajo
        low_stock_widget = self.create_low_stock_widget()
        bottom_row.addWidget(low_stock_widget, 1)
        
        content_layout.addLayout(bottom_row)
        
        scroll.setWidget(content_widget)
        main_layout.addWidget(scroll)
        
        self.setLayout(main_layout)
        self.apply_styles()
        
        # Mostrar maximizado después de configurar toda la interfaz
        self.showMaximized()
    
    def create_fecha_controls(self):
        """Crear controles de selección de fecha"""
        controls_widget = QWidget()
        controls_layout = QHBoxLayout(controls_widget)
        controls_layout.setContentsMargins(0, 0, 0, 0)
        controls_layout.setSpacing(10)
        
        # Label de fecha
        fecha_label = QLabel("📅 Ver datos de:")
        fecha_label.setStyleSheet("font-weight: bold; color: #333;")
        controls_layout.addWidget(fecha_label)
        
        # ComboBox para selección rápida
        self.fecha_combo = QComboBox()
        self.fecha_combo.addItems([
            "Hoy",
            "Ayer", 
            "Hace 2 días",
            "Fecha específica"
        ])
        self.fecha_combo.setCurrentText("Hoy")
        self.fecha_combo.currentTextChanged.connect(self.on_fecha_combo_changed)
        controls_layout.addWidget(self.fecha_combo)
        
        # DateEdit para fecha específica
        self.fecha_edit = QDateEdit()
        self.fecha_edit.setDate(QDate.fromString(self.fecha_actual.strftime("%Y-%m-%d"), "yyyy-MM-dd"))
        self.fecha_edit.setCalendarPopup(True)
        self.fecha_edit.setVisible(False)  # Inicialmente oculto
        self.fecha_edit.dateChanged.connect(self.on_fecha_edit_changed)
        controls_layout.addWidget(self.fecha_edit)
        
        # Botón de actualizar
        self.btn_actualizar_fecha = QPushButton("🔄 Actualizar")
        self.btn_actualizar_fecha.clicked.connect(self.actualizar_datos_fecha)
        self.btn_actualizar_fecha.setStyleSheet("""
            QPushButton {
                background: #0078d4;
                color: white;
                border: none;
                padding: 6px 12px;
                border-radius: 4px;
                font-weight: bold;
            }
            QPushButton:hover {
                background: #106ebe;
            }
        """)
        controls_layout.addWidget(self.btn_actualizar_fecha)
        
        return controls_widget
    
    def on_fecha_combo_changed(self, texto):
        """Manejar cambio en el combo de fecha"""
        if texto == "Hoy":
            self.fecha_seleccionada = self.fecha_actual
            self.fecha_edit.setVisible(False)
        elif texto == "Ayer":
            self.fecha_seleccionada = self.fecha_actual - timedelta(days=1)
            self.fecha_edit.setVisible(False)
        elif texto == "Hace 2 días":
            self.fecha_seleccionada = self.fecha_actual - timedelta(days=2)
            self.fecha_edit.setVisible(False)
        elif texto == "Fecha específica":
            self.fecha_edit.setVisible(True)
            self.fecha_edit.setDate(QDate.fromString(self.fecha_actual.strftime("%Y-%m-%d"), "yyyy-MM-dd"))
    
    def on_fecha_edit_changed(self, fecha):
        """Manejar cambio en el date edit"""
        self.fecha_seleccionada = fecha.toPython()
    
    def actualizar_datos_fecha(self):
        """Actualizar datos con la fecha seleccionada"""
        try:
            # Actualizar datos inmediatamente
            self.update_dashboard_data(self.get_dashboard_data())
        except Exception as e:
            QMessageBox.warning(self, "Error", f"Error actualizando datos: {str(e)}")
    
    def get_dashboard_data(self):
        """Obtener datos del panel principal para la fecha seleccionada"""
        try:
            fecha_str = self.fecha_seleccionada.strftime("%Y-%m-%d")
            
            # Ventas del día seleccionado
            ventas_dia = self.db.get_ventas_del_dia(fecha_str)
            total_ventas = sum(v[1] for v in ventas_dia) if ventas_dia else 0
            num_tickets = len(ventas_dia)
            promedio_ticket = total_ventas / num_tickets if num_tickets > 0 else 0
            
            # Ventas por hora
            ventas_por_hora = self.db.get_ventas_por_hora(fecha_str)
            
            # Top productos del día
            top_productos = self.db.get_top_productos_dia(fecha_str, limit=5)
            
            # Productos con stock bajo
            stock_bajo = self.db.get_productos_stock_bajo()
            
            # Estado de caja actual
            estado_caja = self.db.get_estado_caja_actual()
            
            # Comparación con día anterior
            ayer = self.fecha_seleccionada - timedelta(days=1)
            ayer_str = ayer.strftime("%Y-%m-%d")
            ventas_ayer = self.db.get_ventas_del_dia(ayer_str)
            total_ayer = sum(v[1] for v in ventas_ayer) if ventas_ayer else 0
            
            return {
                'ventas_dia': total_ventas,
                'num_tickets': num_tickets,
                'promedio_ticket': promedio_ticket,
                'ventas_por_hora': ventas_por_hora,
                'top_productos': top_productos,
                'stock_bajo': stock_bajo,
                'estado_caja': estado_caja,
                'ventas_ayer': total_ayer,
                'cambio_ventas': total_ventas - total_ayer,
                'fecha_seleccionada': fecha_str
            }
        except Exception as e:
            print(f"Error obteniendo datos del panel principal: {e}")
            return {}
    
    def create_metrics_row(self):
        """Crear la fila de métricas principales"""
        layout = QHBoxLayout()
        layout.setSpacing(15)
        
        # Métrica 1: Ventas del día
        ventas_card, self.ventas_label = self.create_metric_card(
            "💰 VENTAS DEL DÍA",
            "0.00",
            "S/",
            "ventas_metric"
        )
        layout.addWidget(ventas_card)
        
        # Métrica 2: Número de tickets
        tickets_card, self.tickets_label = self.create_metric_card(
            "🎫 COMPROBANTES",
            "0",
            "comprobantes",
            "tickets_metric"
        )
        layout.addWidget(tickets_card)
        
        # Métrica 3: Promedio por ticket
        promedio_card, self.promedio_label = self.create_metric_card(
            "📊 PROMEDIO",
            "0.00",
            "S/",
            "promedio_metric"
        )
        layout.addWidget(promedio_card)
        
        # Métrica 4: Cambio vs ayer
        cambio_card, self.cambio_label = self.create_metric_card(
            "📈 VS AYER",
            "0.00",
            "S/",
            "cambio_metric"
        )
        layout.addWidget(cambio_card)
        
        return layout
    
    def create_metric_card(self, title, value, unit, object_name):
        """Crear una tarjeta de métrica"""
        card = QFrame()
        card.setObjectName(object_name)
        card.setFixedHeight(120)
        
        layout = QVBoxLayout(card)
        layout.setAlignment(Qt.AlignCenter)
        layout.setSpacing(5)
        
        # Título
        title_label = QLabel(title)
        title_label.setObjectName("metric_title")
        title_label.setAlignment(Qt.AlignCenter)
        layout.addWidget(title_label)
        
        # Valor
        value_label = QLabel(value)
        value_label.setObjectName("metric_value")
        value_label.setAlignment(Qt.AlignCenter)
        layout.addWidget(value_label)
        
        # Unidad
        unit_label = QLabel(unit)
        unit_label.setObjectName("metric_unit")
        unit_label.setAlignment(Qt.AlignCenter)
        layout.addWidget(unit_label)
        
        return card, value_label
    
    def create_sales_chart(self):
        """Crear widget de gráfico de ventas por hora"""
        group = QGroupBox("📈 VENTAS POR HORA")
        group.setObjectName("chart_group")
        layout = QVBoxLayout(group)
        
        # Placeholder para el gráfico
        chart_label = QLabel("Gráfico de ventas por hora\n(Pendiente de implementar)")
        chart_label.setObjectName("chart_placeholder")
        chart_label.setAlignment(Qt.AlignCenter)
        chart_label.setMinimumHeight(200)
        layout.addWidget(chart_label)
        
        return group
    
    def create_alerts_widget(self):
        """Crear widget de alertas y estado"""
        group = QGroupBox("⚠️ ALERTAS Y ESTADO")
        group.setObjectName("alerts_group")
        layout = QVBoxLayout(group)
        
        # Estado de caja
        caja_label = QLabel("💼 ESTADO DE CAJA")
        caja_label.setObjectName("alert_section_title")
        layout.addWidget(caja_label)
        
        self.caja_status = QLabel("Cargando...")
        self.caja_status.setObjectName("alert_content")
        layout.addWidget(self.caja_status)
        
        # Stock bajo
        stock_label = QLabel("📦 INVENTARIO BAJO")
        stock_label.setObjectName("alert_section_title")
        layout.addWidget(stock_label)
        
        self.stock_status = QLabel("Cargando...")
        self.stock_status.setObjectName("alert_content")
        layout.addWidget(self.stock_status)
        
        # Botón de actualizar
        refresh_btn = QPushButton("🔄 Actualizar")
        refresh_btn.setObjectName("refresh_btn")
        refresh_btn.clicked.connect(self.refresh_data)
        layout.addWidget(refresh_btn)
        
        return group
    
    def create_top_products_widget(self):
        """Crear widget de top productos"""
        group = QGroupBox("🏆 PRODUCTOS MÁS VENDIDOS DEL DÍA")
        group.setObjectName("table_group")
        layout = QVBoxLayout(group)
        
        # Tabla de productos
        self.top_products_table = QTableWidget()
        self.top_products_table.setObjectName("dashboard_table")
        self.top_products_table.setColumnCount(4)
        self.top_products_table.setHorizontalHeaderLabels(["Producto", "Cantidad", "Total", "Puesto"])
        
        # Configurar tabla
        header = self.top_products_table.horizontalHeader()
        header.setSectionResizeMode(0, QHeaderView.Stretch)
        header.setSectionResizeMode(1, QHeaderView.ResizeToContents)
        header.setSectionResizeMode(2, QHeaderView.ResizeToContents)
        header.setSectionResizeMode(3, QHeaderView.ResizeToContents)
        
        self.top_products_table.setAlternatingRowColors(True)
        self.top_products_table.setSelectionBehavior(QTableWidget.SelectRows)
        self.top_products_table.setMaximumHeight(200)
        
        layout.addWidget(self.top_products_table)
        
        return group
    
    def create_low_stock_widget(self):
        """Crear widget de stock bajo"""
        group = QGroupBox("⚠️ PRODUCTOS CON INVENTARIO BAJO")
        group.setObjectName("table_group")
        layout = QVBoxLayout(group)
        
        # Tabla de stock bajo
        self.low_stock_table = QTableWidget()
        self.low_stock_table.setObjectName("dashboard_table")
        self.low_stock_table.setColumnCount(3)
        self.low_stock_table.setHorizontalHeaderLabels(["Producto", "Inventario actual", "Inventario mínimo"])
        
        # Configurar tabla
        header = self.low_stock_table.horizontalHeader()
        header.setSectionResizeMode(0, QHeaderView.Stretch)
        header.setSectionResizeMode(1, QHeaderView.ResizeToContents)
        header.setSectionResizeMode(2, QHeaderView.ResizeToContents)
        
        self.low_stock_table.setAlternatingRowColors(True)
        self.low_stock_table.setSelectionBehavior(QTableWidget.SelectRows)
        self.low_stock_table.setMaximumHeight(200)
        
        layout.addWidget(self.low_stock_table)
        
        return group
    
    def setup_timer(self):
        """Configurar timer para actualizaciones automáticas"""
        self.timer = QTimer()
        self.timer.timeout.connect(self.refresh_data)
        self.timer.start(60000)  # Actualizar cada minuto
    
    def load_initial_data(self):
        """Cargar datos iniciales"""
        # Cargar datos básicos sin worker para evitar bloqueos
        self.update_basic_data()
        
        # Iniciar worker con delay para actualizaciones automáticas
        QTimer.singleShot(3000, self.start_worker)
    
    def start_worker(self):
        """Iniciar worker thread para actualizaciones automáticas"""
        # Detener worker existente si está corriendo
        if self.worker and self.worker.isRunning():
            self.worker.running = False
            self.worker.quit()
            self.worker.wait(1000)
        
        # Crear nuevo worker
        self.worker = DashboardWorker(self.db)
        self.worker.data_updated.connect(self.update_dashboard_data)
        self.worker.finished.connect(self.worker.deleteLater)  # Limpiar automáticamente
        self.worker.start()
    
    def refresh_data(self):
        """Actualizar todos los datos del panel principal"""
        self.start_worker()
    
    def update_basic_data(self):
        """Actualizar datos básicos sin consultas pesadas"""
        try:
            # Datos básicos sin consultas complejas
            self.ventas_label.setText("0.00 S/")
            self.tickets_label.setText("0 comprobantes")
            self.promedio_label.setText("0.00 S/")
            self.cambio_label.setText("0.00 S/")
            
            # Limpiar tablas
            self.top_products_table.setRowCount(0)
            self.low_stock_table.setRowCount(0)
            
        except Exception as e:
            print(f"Error actualizando datos básicos: {e}")
    
    def update_dashboard_data(self, data):
        """Actualizar la interfaz con nuevos datos"""
        try:
            # Actualizar métricas principales
            self.update_metrics(data)
            
            # Actualizar alertas
            self.update_alerts(data)
            
            # Actualizar tablas
            self.update_top_products(data.get('top_productos', []))
            self.update_low_stock(data.get('stock_bajo', []))
            
        except Exception as e:
            print(f"Error actualizando panel principal: {e}")
    
    def update_metrics(self, data):
        """Actualizar las métricas principales"""
        # Buscar los widgets de métricas por objectName
        ventas_widget = self.findChild(QFrame, "ventas_metric")
        tickets_widget = self.findChild(QFrame, "tickets_metric")
        promedio_widget = self.findChild(QFrame, "promedio_metric")
        cambio_widget = self.findChild(QFrame, "cambio_metric")
        
        if ventas_widget:
            value_label = ventas_widget.findChild(QLabel, "metric_value")
            if value_label:
                value_label.setText(f"{data.get('ventas_dia', 0):.2f}")
        
        if tickets_widget:
            value_label = tickets_widget.findChild(QLabel, "metric_value")
            if value_label:
                value_label.setText(str(data.get('num_tickets', 0)))
        
        if promedio_widget:
            value_label = promedio_widget.findChild(QLabel, "metric_value")
            if value_label:
                value_label.setText(f"{data.get('promedio_ticket', 0):.2f}")
        
        if cambio_widget:
            value_label = cambio_widget.findChild(QLabel, "metric_value")
            if value_label:
                cambio = data.get('cambio_ventas', 0)
                value_label.setText(f"{cambio:+.2f}")
                # Cambiar color según el cambio
                if cambio > 0:
                    value_label.setStyleSheet("color: #28a745; font-weight: bold;")
                elif cambio < 0:
                    value_label.setStyleSheet("color: #dc3545; font-weight: bold;")
                else:
                    value_label.setStyleSheet("color: #6c757d; font-weight: bold;")
    
    def update_alerts(self, data):
        """Actualizar las alertas y estado"""
        # Estado de caja
        estado_caja = data.get('estado_caja', {})
        if estado_caja:
            self.caja_status.setText(f"Efectivo: S/ {estado_caja.get('efectivo', 0):.2f}")
        else:
            self.caja_status.setText("Sin datos de caja")
        
        # Stock bajo
        stock_bajo = data.get('stock_bajo', [])
        if stock_bajo:
            self.stock_status.setText(f"{len(stock_bajo)} productos con inventario bajo")
        else:
            self.stock_status.setText("Todo el inventario está correcto")
    
    def update_top_products(self, productos):
        """Actualizar tabla de top productos"""
        self.top_products_table.setRowCount(len(productos))
        
        for i, producto in enumerate(productos):
            self.top_products_table.setItem(i, 0, QTableWidgetItem(str(producto[0])))  # Nombre
            self.top_products_table.setItem(i, 1, QTableWidgetItem(str(producto[1])))  # Cantidad
            self.top_products_table.setItem(i, 2, QTableWidgetItem(f"S/ {producto[2]:.2f}"))  # Total
            self.top_products_table.setItem(i, 3, QTableWidgetItem(f"#{i+1}"))  # Puesto
    
    def update_low_stock(self, productos):
        """Actualizar tabla de stock bajo"""
        self.low_stock_table.setRowCount(len(productos))
        
        for i, producto in enumerate(productos):
            self.low_stock_table.setItem(i, 0, QTableWidgetItem(str(producto[0])))  # Nombre
            self.low_stock_table.setItem(i, 1, QTableWidgetItem(str(producto[1])))  # Stock actual
            self.low_stock_table.setItem(i, 2, QTableWidgetItem(str(producto[2])))  # Stock mínimo
    
    def apply_styles(self):
        """Aplicar estilos al panel principal"""
        # Usar colores nativos del sistema para mejor compatibilidad y contraste
        pass
    
    def closeEvent(self, event):
        """Manejar cierre de la ventana"""
        try:
            # Detener worker thread
            if self.worker and self.worker.isRunning():
                self.worker.running = False
                self.worker.quit()
                if not self.worker.wait(5000):  # Esperar máximo 5 segundos
                    self.worker.terminate()  # Forzar terminación si no responde
                    self.worker.wait(1000)  # Esperar un poco más después de terminar
            
            # Detener timer
            if self.timer:
                self.timer.stop()
                
            event.accept()
        except Exception as e:
            print(f"Error cerrando panel principal: {e}")
            event.accept()

