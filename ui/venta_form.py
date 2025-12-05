from __future__ import annotations

import json
import os
from datetime import datetime
from dataclasses import dataclass
from typing import Optional, Dict, Any, List

from PySide6.QtCore import Qt, QTimer, QDateTime
from PySide6.QtGui import QFont, QPixmap, QIcon, QIntValidator, QKeyEvent
from PySide6.QtWidgets import (
    QWidget, QVBoxLayout, QHBoxLayout, QLabel, QLineEdit, QPushButton,
    QComboBox, QTableWidget, QTableWidgetItem, QHeaderView, QMessageBox,
    QDialog, QTableWidgetSelectionRange, QInputDialog, QSpinBox, QDoubleSpinBox,
    QGroupBox, QGridLayout, QFrame, QSplitter, QTabWidget, QScrollArea
)
from PySide6.QtCore import Qt

# Import para gestión de clientes
from ui.clientes_admin import ClientesAdmin
from ui.cierre_caja_form import CierreCajaForm

HOLD_PATH = "pos_venta_hold.json"
IGV_TASA = 0.18


@dataclass
class ProductoLite:
    id: int
    sku: str
    descripcion_larga: str
    codigo_barra: str
    costo_compra: float
    costo_promedio: float
    valor_venta: float
    precio_venta: float
    afecto_igv: bool
    unidad_medida: str = "UND"


class VentaForm(QWidget):
    def __init__(self, db, usuario_actual: str, config: Optional[Dict[str, Any]] = None):
        super().__init__()
        self.db = db
        self.usuario = usuario_actual
        self.config = config or self._leer_config_json("config.json")
        self.setWindowTitle("POS - Venta")
        self.setGeometry(200, 120, 1050, 720)

        # Estado
        self.comprobante_id: Optional[int] = None
        self.items: List[Dict[str, Any]] = []  # espejo de tabla para suspender/recuperar

        self.setup_ui()

    def setup_ui(self):
        # Layout principal con tres paneles
        main_layout = QHBoxLayout(self)
        
        # === PANEL IZQUIERDO: Productos + resumen + vista cliente ===
        left_panel = QVBoxLayout()
        
        # === SECCIÓN SUPERIOR: INFORMACIÓN DEL COMPROBANTE ===
        # (Eliminada la sección innecesaria de MONTO A PAGAR)
        
        # Información del comprobante
        comp_info = QHBoxLayout()
        self.lbl_tipo_documento = QLabel("Tipo: --")
        self.lbl_tipo_documento.setStyleSheet("font-weight: bold; color: #2c3e50;")
        self.lbl_serie = QLabel("Serie: --")
        self.lbl_numero = QLabel("Número: --")
        comp_info.addWidget(self.lbl_tipo_documento)
        comp_info.addWidget(self.lbl_serie)
        comp_info.addWidget(self.lbl_numero)
        
        # Botones principales
        botones_principales = QHBoxLayout()
        self.btn_iniciar = QPushButton("INICIAR")
        self.btn_iniciar.clicked.connect(self.iniciar_comprobante)
        self.btn_iniciar.setStyleSheet("QPushButton { background: #27ae60; color: white; padding: 15px; border-radius: 10px; font-weight: bold; font-size: 14px; }")
        botones_principales.addWidget(self.btn_iniciar)
        
        self.btn_suspender = QPushButton("SUSPENDER")
        self.btn_suspender.clicked.connect(self.suspender_venta)
        self.btn_suspender.setStyleSheet("QPushButton { background: #f39c12; color: white; padding: 15px; border-radius: 10px; font-weight: bold; font-size: 14px; }")
        botones_principales.addWidget(self.btn_suspender)
        
        self.btn_recuperar = QPushButton("RECUPERAR")
        self.btn_recuperar.clicked.connect(self.recuperar_venta)
        self.btn_recuperar.setStyleSheet("QPushButton { background: #3498db; color: white; padding: 15px; border-radius: 10px; font-weight: bold; font-size: 14px; }")
        botones_principales.addWidget(self.btn_recuperar)
        
        self.btn_cancelar = QPushButton("CANCELAR")
        self.btn_cancelar.clicked.connect(self.cancelar_venta)
        self.btn_cancelar.setStyleSheet("QPushButton { background: #e74c3c; color: white; padding: 15px; border-radius: 10px; font-weight: bold; font-size: 14px; }")
        botones_principales.addWidget(self.btn_cancelar)
        
        self.btn_clientes = QPushButton("👥 CLIENTES")
        self.btn_clientes.clicked.connect(self.abrir_gestion_clientes)
        self.btn_clientes.setStyleSheet("QPushButton { background: #5d6d7e; color: white; padding: 15px; border-radius: 10px; font-weight: bold; font-size: 14px; }")
        botones_principales.addWidget(self.btn_clientes)
        
        # Agregar layouts al panel izquierdo
        left_panel.addLayout(comp_info)
        left_panel.addLayout(botones_principales)
        
        # Entrada de código
        entrada = QHBoxLayout()
        entrada.addWidget(QLabel("Código/SKU:"))
        self.input_codigo = QLineEdit()
        self.input_codigo.setPlaceholderText("Escanee o escriba código/SKU y Enter")
        self.input_codigo.returnPressed.connect(self.on_enter_codigo)
        entrada.addWidget(self.input_codigo)

        self.btn_buscar = QPushButton("Buscar")
        self.btn_buscar.clicked.connect(self.buscar_descripcion_dialog)
        entrada.addWidget(self.btn_buscar)

        left_panel.addLayout(entrada)
        
        # Tabla de productos (más grande)
        self.tbl = QTableWidget(0, 8)
        self.tbl.setHorizontalHeaderLabels([
            "Código Barra", "Descripción", "U.M.", "Afecto IGV", "Cant.",
            "IGV Unit.", "Precio Unit.", "Importe"
        ])
        # Configurar columnas con anchos específicos para mejor visualización
        header = self.tbl.horizontalHeader()
        header.setSectionResizeMode(0, QHeaderView.ResizeMode.Fixed)  # Código Barra
        header.setSectionResizeMode(1, QHeaderView.ResizeMode.Stretch)  # Descripción
        header.setSectionResizeMode(2, QHeaderView.ResizeMode.Fixed)  # U.M.
        header.setSectionResizeMode(3, QHeaderView.ResizeMode.Fixed)  # Afecto IGV
        header.setSectionResizeMode(4, QHeaderView.ResizeMode.Fixed)  # Cant.
        header.setSectionResizeMode(5, QHeaderView.ResizeMode.Fixed)  # IGV Unit.
        header.setSectionResizeMode(6, QHeaderView.ResizeMode.Fixed)  # Precio Unit.
        header.setSectionResizeMode(7, QHeaderView.ResizeMode.Fixed)  # Importe
        
        # Establecer anchos específicos
        self.tbl.setColumnWidth(0, 150)  # Código Barra (más ancho)
        self.tbl.setColumnWidth(2, 60)   # U.M.
        self.tbl.setColumnWidth(3, 80)   # Afecto IGV
        self.tbl.setColumnWidth(4, 60)   # Cant.
        self.tbl.setColumnWidth(5, 80)   # IGV Unit.
        self.tbl.setColumnWidth(6, 90)   # Precio Unit.
        self.tbl.setColumnWidth(7, 90)   # Importe
        self.tbl.verticalHeader().setVisible(False)
        self.tbl.setMinimumHeight(300)
        
        # Conectar eventos de la tabla
        self.tbl.itemChanged.connect(self._on_cantidad_changed)
        self.tbl.keyPressEvent = self._on_table_key_press
        
        left_panel.addWidget(self.tbl)
        
        # === SECCIÓN INFERIOR: RESUMEN DETALLADO + VISTA CLIENTE ===
        seccion_inferior = QHBoxLayout()
        
        # === RESUMEN DETALLADO (IZQUIERDA) ===
        resumen_group = QGroupBox("📊 Resumen Detallado")
        resumen_group.setStyleSheet("QGroupBox { font-weight: bold; font-size: 14px; }")
        resumen_layout = QGridLayout(resumen_group)
        
        # Contador de artículos
        resumen_layout.addWidget(QLabel("Artículos:"), 0, 0)
        self.lbl_cant_articulos = QLabel("0")
        self.lbl_cant_articulos.setStyleSheet("font-weight: bold; font-size: 16px; color: #27ae60;")
        resumen_layout.addWidget(self.lbl_cant_articulos, 0, 1)
        
        # Sub Afecto
        resumen_layout.addWidget(QLabel("Sub Afecto:"), 1, 0)
        self.lbl_subaf = QLabel("S/. 0.00")
        self.lbl_subaf.setStyleSheet("font-weight: bold; color: #2c3e50;")
        resumen_layout.addWidget(self.lbl_subaf, 1, 1)
        
        # Sub Inafecto
        resumen_layout.addWidget(QLabel("Sub Inafecto:"), 2, 0)
        self.lbl_subin = QLabel("S/. 0.00")
        self.lbl_subin.setStyleSheet("font-weight: bold; color: #2c3e50;")
        resumen_layout.addWidget(self.lbl_subin, 2, 1)
        
        # IGV
        resumen_layout.addWidget(QLabel("IGV:"), 3, 0)
        self.lbl_igv = QLabel("S/. 0.00")
        self.lbl_igv.setStyleSheet("font-weight: bold; color: #2c3e50;")
        resumen_layout.addWidget(self.lbl_igv, 3, 1)
        
        # Descuento
        resumen_layout.addWidget(QLabel("Descuento:"), 4, 0)
        descuento_layout = QHBoxLayout()
        self.input_descuento = QLineEdit()
        self.input_descuento.setPlaceholderText("0.00")
        self.input_descuento.setStyleSheet("QLineEdit { padding: 5px; font-size: 12px; border: 1px solid #bdc3c7; border-radius: 3px; }")
        self.input_descuento.setMaximumWidth(80)
        self.input_descuento.textChanged.connect(self.aplicar_descuento)
        descuento_layout.addWidget(self.input_descuento)
        descuento_layout.addWidget(QLabel("S/."))
        resumen_layout.addLayout(descuento_layout, 4, 1)
        
        # Descuento Aplicado
        resumen_layout.addWidget(QLabel("Descuento Aplicado:"), 5, 0)
        self.lbl_descuento_aplicado = QLabel("S/. 0.00")
        self.lbl_descuento_aplicado.setStyleSheet("font-weight: bold; color: #e67e22;")
        resumen_layout.addWidget(self.lbl_descuento_aplicado, 5, 1)
        
        # Total
        resumen_layout.addWidget(QLabel("TOTAL:"), 6, 0)
        self.lbl_total = QLabel("S/. 0.00")
        self.lbl_total.setStyleSheet("font-weight: bold; color: #e74c3c; font-size: 18px;")
        resumen_layout.addWidget(self.lbl_total, 6, 1)
        
        seccion_inferior.addWidget(resumen_group)
        
        # === LABELS DE PAGOS MIXTOS ===
        pagos_mixtos_group = QGroupBox("💰 Pagos Mixtos")
        pagos_mixtos_group.setStyleSheet("QGroupBox { font-weight: bold; font-size: 12px; }")
        pagos_mixtos_layout = QVBoxLayout(pagos_mixtos_group)
        
        self.lbl_efectivo_pago = QLabel("Efectivo: S/. 0.00")
        self.lbl_efectivo_pago.setStyleSheet("font-size: 11px; color: #27ae60;")
        pagos_mixtos_layout.addWidget(self.lbl_efectivo_pago)
        
        self.lbl_dolares_pago = QLabel("US Dólares: $ 0.00")
        self.lbl_dolares_pago.setStyleSheet("font-size: 11px; color: #f39c12;")
        pagos_mixtos_layout.addWidget(self.lbl_dolares_pago)
        
        self.lbl_vale_pago = QLabel("Tot. Vale De Alimentación: S/. 0.00")
        self.lbl_vale_pago.setStyleSheet("font-size: 11px; color: #8e44ad;")
        pagos_mixtos_layout.addWidget(self.lbl_vale_pago)
        
        seccion_inferior.addWidget(pagos_mixtos_group)
        
        # === VISTA DEL CLIENTE (DERECHA) ===
        cliente_group = QGroupBox("👁️ Vista del Cliente")
        cliente_group.setStyleSheet("QGroupBox { font-weight: bold; font-size: 14px; }")
        cliente_layout = QVBoxLayout(cliente_group)
        
        # Marca de agua POS VENTA
        marca_agua = QLabel("POS VISOR CLIENTE")
        marca_agua.setStyleSheet("font-size: 18px; color: #ecf0f1; font-weight: bold; text-align: center; margin: 10px; background: #2c3e50; border-radius: 5px; padding: 5px;")
        marca_agua.setAlignment(Qt.AlignmentFlag.AlignCenter)
        cliente_layout.addWidget(marca_agua)
        
        # Lista de productos para el cliente
        self.lbl_cliente_productos = QLabel("Productos:\n")
        self.lbl_cliente_productos.setStyleSheet("font-size: 12px; color: #2c3e50;")
        cliente_layout.addWidget(self.lbl_cliente_productos)
        
        # Resumen azul (como en la imagen)
        resumen_azul_group = QGroupBox("")
        resumen_azul_group.setStyleSheet("QGroupBox { background: #3498db; color: white; border: none; border-radius: 8px; }")
        resumen_azul_layout = QGridLayout(resumen_azul_group)
        
        resumen_azul_layout.addWidget(QLabel("Total Dcto:"), 0, 0)
        self.lbl_total_dcto = QLabel("S/. 0.00")
        self.lbl_total_dcto.setStyleSheet("color: white; font-weight: bold; font-size: 12px;")
        resumen_azul_layout.addWidget(self.lbl_total_dcto, 0, 1)
        
        resumen_azul_layout.addWidget(QLabel("Sub Total:"), 1, 0)
        self.lbl_sub_total = QLabel("S/. 0.00")
        self.lbl_sub_total.setStyleSheet("color: white; font-weight: bold; font-size: 12px;")
        resumen_azul_layout.addWidget(self.lbl_sub_total, 1, 1)
        
        resumen_azul_layout.addWidget(QLabel("V. A. Total:"), 2, 0)
        self.lbl_va_total = QLabel("S/. 0.00")
        self.lbl_va_total.setStyleSheet("color: white; font-weight: bold; font-size: 12px;")
        resumen_azul_layout.addWidget(self.lbl_va_total, 2, 1)
        
        resumen_azul_layout.addWidget(QLabel("Orden Total:"), 3, 0)
        self.lbl_orden_total = QLabel("S/. 0.00")
        self.lbl_orden_total.setStyleSheet("color: white; font-weight: bold; font-size: 14px;")
        resumen_azul_layout.addWidget(self.lbl_orden_total, 3, 1)
        
        cliente_layout.addWidget(resumen_azul_group)
        
        seccion_inferior.addWidget(cliente_group)
        
        # Agregar la sección inferior al panel izquierdo
        left_panel.addLayout(seccion_inferior)
        
        # === PANEL CENTRAL ELIMINADO ===
        
        # === PANEL DERECHO: Vista del cliente + menús + numpad ===
        right_panel = QVBoxLayout()
        
        # Crear un scroll area para el panel derecho
        right_scroll = QScrollArea()
        right_scroll.setWidgetResizable(True)
        right_scroll.setHorizontalScrollBarPolicy(Qt.ScrollBarPolicy.ScrollBarAlwaysOff)
        right_scroll.setVerticalScrollBarPolicy(Qt.ScrollBarPolicy.ScrollBarAsNeeded)
        
        # Widget contenedor para el contenido del scroll
        right_content = QWidget()
        right_content_layout = QVBoxLayout(right_content)
        right_content_layout.setSpacing(10)  # Espaciado entre elementos (reducido)
        right_content_layout.setContentsMargins(15, 15, 15, 15)  # Márgenes externos (reducidos)
        
        # Header con fecha, hora y terminal
        header_cliente = QHBoxLayout()
        
        lbl_fecha_header = QLabel(datetime.now().strftime("%d/%m/%Y"))
        lbl_fecha_header.setStyleSheet("font-size: 12px; color: #2c3e50; font-weight: bold;")
        header_cliente.addWidget(lbl_fecha_header)
        
        lbl_hora_header = QLabel(datetime.now().strftime("%I:%M%p"))
        lbl_hora_header.setStyleSheet("font-size: 12px; color: #2c3e50; font-weight: bold;")
        header_cliente.addWidget(lbl_hora_header)
        
        lbl_terminal = QLabel("Terminal")
        lbl_terminal.setStyleSheet("font-size: 12px; color: #2c3e50; font-weight: bold;")
        header_cliente.addWidget(lbl_terminal)
        
        right_content_layout.addLayout(header_cliente)
        

        
        # Botón para ingresar datos del cliente
        self.btn_datos_cliente = QPushButton("👤 Ingresar Datos del Cliente")
        self.btn_datos_cliente.clicked.connect(self.ingresar_datos_cliente_manual)
        self.btn_datos_cliente.setStyleSheet("QPushButton { background: #9b59b6; color: white; padding: 10px; border-radius: 5px; font-weight: bold; font-size: 12px; }")
        right_content_layout.addWidget(self.btn_datos_cliente)
        
        # Botón para cambiar tipo de documento (solo visible si hay comprobante iniciado)
        self.btn_cambiar_tipo = QPushButton("🔄 Cambiar Tipo de Documento")
        self.btn_cambiar_tipo.clicked.connect(self.cambiar_tipo_documento)
        self.btn_cambiar_tipo.setStyleSheet("QPushButton { background: #f39c12; color: white; padding: 10px; border-radius: 5px; font-weight: bold; font-size: 12px; }")
        self.btn_cambiar_tipo.setVisible(False)  # Inicialmente oculto
        right_content_layout.addWidget(self.btn_cambiar_tipo)
        
        # Información del Cliente
        cliente_info_group = QGroupBox("👤 Información del Cliente")
        cliente_info_group.setStyleSheet("QGroupBox { font-weight: bold; font-size: 12px; margin-top: 5px; }")
        cliente_info_layout = QVBoxLayout(cliente_info_group)
        cliente_info_layout.setSpacing(5)  # Espaciado reducido
        cliente_info_layout.setContentsMargins(8, 8, 8, 8)  # Márgenes reducidos
        
        # Documento del cliente
        documento_layout = QHBoxLayout()
        documento_layout.addWidget(QLabel("Documento:"))
        self.lbl_documento_cliente = QLabel("--")
        self.lbl_documento_cliente.setStyleSheet("font-weight: bold; color: #2c3e50; padding: 5px; background: #ecf0f1; border-radius: 3px;")
        documento_layout.addWidget(self.lbl_documento_cliente)
        cliente_info_layout.addLayout(documento_layout)
        
        # Variable para almacenar el ID del cliente
        self.cliente_id_actual = None
        
        # Nombre del cliente
        nombre_layout = QHBoxLayout()
        nombre_layout.addWidget(QLabel("Nombre:"))
        self.lbl_nombre_cliente = QLabel("--")
        self.lbl_nombre_cliente.setStyleSheet("font-weight: bold; color: #2c3e50; padding: 5px; background: #ecf0f1; border-radius: 3px;")
        nombre_layout.addWidget(self.lbl_nombre_cliente)
        cliente_info_layout.addLayout(nombre_layout)
        
        right_content_layout.addWidget(cliente_info_group)
        
        # Espaciador para separar secciones
        spacer1 = QWidget()
        spacer1.setFixedHeight(10)
        right_content_layout.addWidget(spacer1)
        
        # === BOTÓN DE PAGOS PRINCIPAL ===
        self.btn_pagos = QPushButton("💳 PAGOS")
        self.btn_pagos.clicked.connect(self.abrir_sistema_pagos)
        self.btn_pagos.setStyleSheet("QPushButton { background: #e74c3c; color: white; padding: 14px; border-radius: 10px; font-weight: bold; font-size: 18px; }")
        self.btn_pagos.setMinimumHeight(74)
        right_content_layout.addWidget(self.btn_pagos)
        
        # Espaciador para separar secciones
        spacer2 = QWidget()
        spacer2.setFixedHeight(10)
        right_content_layout.addWidget(spacer2)
        

        
        # === MENÚS DESPLEGABLES ===
        
        # Espaciador antes de los menús
        spacer_menu1 = QWidget()
        spacer_menu1.setFixedHeight(8)
        right_content_layout.addWidget(spacer_menu1)
        
        # Menú de Usuario (Colapsable)
        menu_usuario_group = QGroupBox("👤 Menú de Usuario")
        menu_usuario_group.setStyleSheet("QGroupBox { font-weight: bold; font-size: 13px; margin-top: 8px; color: #2c3e50; }")
        menu_usuario_group.setCheckable(True)
        menu_usuario_group.setChecked(False)
        menu_usuario_layout = QVBoxLayout(menu_usuario_group)
        menu_usuario_layout.setSpacing(6)  # Espaciado mejorado
        menu_usuario_layout.setContentsMargins(10, 10, 10, 10)  # Márgenes mejorados
        menu_usuario_group.toggled.connect(lambda checked, group=menu_usuario_group, layout=menu_usuario_layout: self.toggle_menu(group, layout))
        
        self.btn_bloquear_caja = QPushButton("🔒 BLOQUEAR CAJA")
        self.btn_bloquear_caja.clicked.connect(self.bloquear_caja)
        self.btn_bloquear_caja.setStyleSheet("QPushButton { background: #e74c3c; color: white; padding: 10px; border-radius: 6px; font-weight: bold; font-size: 13px; }")
        menu_usuario_layout.addWidget(self.btn_bloquear_caja)
        
        self.btn_suspender_venta = QPushButton("⏸️ SUSPENDER VENTA")
        self.btn_suspender_venta.clicked.connect(self.suspender_venta)
        self.btn_suspender_venta.setStyleSheet("QPushButton { background: #f39c12; color: white; padding: 10px; border-radius: 6px; font-weight: bold; font-size: 13px; }")
        menu_usuario_layout.addWidget(self.btn_suspender_venta)
        
        self.btn_recuperar_venta = QPushButton("📥 RECUPERAR VENTA")
        self.btn_recuperar_venta.clicked.connect(self.recuperar_venta)
        self.btn_recuperar_venta.setStyleSheet("QPushButton { background: #3498db; color: white; padding: 10px; border-radius: 6px; font-weight: bold; font-size: 13px; }")
        menu_usuario_layout.addWidget(self.btn_recuperar_venta)
        
        self.btn_consultar_precio = QPushButton("🔍 CONSULTAR PRECIO")
        self.btn_consultar_precio.clicked.connect(self.consultar_precio)
        self.btn_consultar_precio.setStyleSheet("QPushButton { background: #16a085; color: white; padding: 10px; border-radius: 6px; font-weight: bold; font-size: 13px; }")
        menu_usuario_layout.addWidget(self.btn_consultar_precio)
        
        # Ocultar submenús inicialmente
        for i in range(menu_usuario_layout.count()):
            menu_usuario_layout.itemAt(i).widget().setVisible(False)
        
        right_content_layout.addWidget(menu_usuario_group)
        
        # Espaciador entre menús
        spacer_menu2 = QWidget()
        spacer_menu2.setFixedHeight(8)
        right_content_layout.addWidget(spacer_menu2)
        
        # Menú de Supervisor (Colapsable)
        menu_supervisor_group = QGroupBox("👨‍💼 Menú de Supervisor")
        menu_supervisor_group.setStyleSheet("QGroupBox { font-weight: bold; font-size: 13px; margin-top: 8px; color: #2c3e50; }")
        menu_supervisor_group.setCheckable(True)
        menu_supervisor_group.setChecked(False)
        menu_supervisor_layout = QVBoxLayout(menu_supervisor_group)
        menu_supervisor_layout.setSpacing(6)  # Espaciado mejorado
        menu_supervisor_layout.setContentsMargins(10, 10, 10, 10)  # Márgenes mejorados
        menu_supervisor_group.toggled.connect(lambda checked, group=menu_supervisor_group, layout=menu_supervisor_layout: self.toggle_menu(group, layout))
        
        self.btn_reimprimir_boleta = QPushButton("🖨️ REIMPRIMIR BOLETA")
        self.btn_reimprimir_boleta.clicked.connect(self.reimprimir)
        self.btn_reimprimir_boleta.setStyleSheet("QPushButton { background: #8e44ad; color: white; padding: 10px; border-radius: 6px; font-weight: bold; font-size: 13px; }")
        menu_supervisor_layout.addWidget(self.btn_reimprimir_boleta)
        
        self.btn_cerrar_x = QPushButton("❌ CERRAR X")
        self.btn_cerrar_x.clicked.connect(self.cierre_x)
        self.btn_cerrar_x.setStyleSheet("QPushButton { background: #e67e22; color: white; padding: 10px; border-radius: 6px; font-weight: bold; font-size: 13px; }")
        menu_supervisor_layout.addWidget(self.btn_cerrar_x)
        
        self.btn_cerrar_z = QPushButton("🔚 CERRAR Z")
        self.btn_cerrar_z.clicked.connect(self.cierre_z)
        self.btn_cerrar_z.setStyleSheet("QPushButton { background: #c0392b; color: white; padding: 10px; border-radius: 6px; font-weight: bold; font-size: 13px; }")
        menu_supervisor_layout.addWidget(self.btn_cerrar_z)
        
        self.btn_reporte_z = QPushButton("📊 REPORTE Z")
        self.btn_reporte_z.clicked.connect(self.reporte_z)
        self.btn_reporte_z.setStyleSheet("QPushButton { background: #8e44ad; color: white; padding: 10px; border-radius: 6px; font-weight: bold; font-size: 13px; }")
        menu_supervisor_layout.addWidget(self.btn_reporte_z)
        
        self.btn_anular_venta = QPushButton("🚫 ANULAR VENTA")
        self.btn_anular_venta.clicked.connect(self.anular_venta)
        self.btn_anular_venta.setStyleSheet("QPushButton { background: #e74c3c; color: white; padding: 10px; border-radius: 6px; font-weight: bold; font-size: 13px; }")
        menu_supervisor_layout.addWidget(self.btn_anular_venta)
        
        self.btn_recobrar_anulado = QPushButton("🔄 RECOBRAR ANULADO")
        self.btn_recobrar_anulado.clicked.connect(self.recobrar_anulado)
        self.btn_recobrar_anulado.setStyleSheet("QPushButton { background: #d35400; color: white; padding: 10px; border-radius: 6px; font-weight: bold; font-size: 13px; }")
        menu_supervisor_layout.addWidget(self.btn_recobrar_anulado)
        
        self.btn_cerrar_cajero = QPushButton("👋 CERRAR CAJERO")
        self.btn_cerrar_cajero.clicked.connect(self.cerrar_cajero)
        self.btn_cerrar_cajero.setStyleSheet("QPushButton { background: #2c3e50; color: white; padding: 10px; border-radius: 6px; font-weight: bold; font-size: 13px; }")
        menu_supervisor_layout.addWidget(self.btn_cerrar_cajero)
        
        # Botón de Cierre de Caja Moderno
        self.btn_cierre_caja_moderno = QPushButton("💰 CIERRE DE CAJA")
        self.btn_cierre_caja_moderno.clicked.connect(self.abrir_cierre_caja)
        self.btn_cierre_caja_moderno.setStyleSheet("QPushButton { background: #e74c3c; color: white; padding: 10px; border-radius: 6px; font-weight: bold; font-size: 13px; }")
        menu_supervisor_layout.addWidget(self.btn_cierre_caja_moderno)
        
        # Ocultar submenús inicialmente
        for i in range(menu_supervisor_layout.count()):
            menu_supervisor_layout.itemAt(i).widget().setVisible(False)
        
        right_content_layout.addWidget(menu_supervisor_group)
        
        # === TECLADO NUMÉRICO EN LA PARTE INFERIOR DERECHA ===
        numpad_group = QGroupBox("🎯 Teclado Numérico")
        numpad_group.setStyleSheet("QGroupBox { font-weight: bold; font-size: 14px; background: #ecf0f1; border: 2px solid #bdc3c7; }")
        numpad_layout = QGridLayout(numpad_group)
        
        # Botones del numpad (más grandes y visibles)
        botones_numpad = [
            ['7', '8', '9', 'X'],
            ['4', '5', '6', '<'],
            ['1', '2', '3', 'C'],
            ['.', '0', 'Enter', '']
        ]
        
        for i, fila in enumerate(botones_numpad):
            for j, texto in enumerate(fila):
                if texto:
                    btn = QPushButton(texto)
                    btn.setMinimumSize(50, 50)
                    btn.setStyleSheet("QPushButton { background: #34495e; color: white; font-weight: bold; font-size: 14px; border-radius: 6px; } QPushButton:hover { background: #2c3e50; }")
                    
                    if texto in '0123456789.':
                        btn.clicked.connect(lambda checked, t=texto: self.tecla_numero_presionada(t))
                    elif texto == 'C':
                        btn.clicked.connect(self.limpiar_entrada)
                    elif texto == 'X':
                        btn.clicked.connect(self.multiplicar_entrada)
                    elif texto == '<':
                        btn.clicked.connect(self.backspace_entrada)
                    elif texto == 'Enter':
                        btn.clicked.connect(self.enter_entrada)
                    
                    numpad_layout.addWidget(btn, i, j)
        
        right_content_layout.addWidget(numpad_group)
        
        # Espaciador final para evitar que el contenido se pegue al borde
        spacer_final = QWidget()
        spacer_final.setFixedHeight(30)
        right_content_layout.addWidget(spacer_final)
        
        # Layout vacío adicional para mejorar el scroll y evitar que se tape información
        layout_vacio = QWidget()
        layout_vacio.setFixedHeight(150)
        right_content_layout.addWidget(layout_vacio)
        
        # Configurar el scroll area y agregarlo al panel derecho
        right_scroll.setWidget(right_content)
        right_panel.addWidget(right_scroll)
        
        # Agregar los DOS paneles al layout principal
        main_layout.addLayout(left_panel, 3)      # 3 partes del ancho (más espacio para productos)
        main_layout.addLayout(right_panel, 1)     # 1 parte del ancho (menús y controles)
        
        # Variables para pagos mixtos
        self.pagos_mixtos_activos = False
        self.total_pagado = 0.0
        self.efectivo_pagado = 0.0
        self.tarjeta_pagada = 0.0
        self.otros_pagados = 0.0
        
        # Variables para el teclado virtual
        self.entrada_actual = ""
        
        # Intentar recuperar venta temporal al inicializar
        self._recuperar_venta_temporal()

    # --- utilidades de archivo HOLD ---
    def _load_holds(self) -> Dict[str, Any]:
        if not os.path.exists(HOLD_PATH):
            return {}
        with open(HOLD_PATH, "r", encoding="utf-8") as f:
            return json.load(f)

    def _save_holds(self, data: Dict[str, Any]) -> None:
        with open(HOLD_PATH, "w", encoding="utf-8") as f:
            json.dump(data, f, ensure_ascii=False, indent=2)

    def _new_hold_code(self) -> str:
        data = self._load_holds()
        i = 1
        while True:
            code = f"HOLD-{i:04d}"
            if code not in data:
                return code
            i += 1
    
    def _new_hold_code_ean13(self) -> str:
        """Genera un código HOLD en formato EAN13 (13 dígitos)"""
        data = self._load_holds()
        i = 1
        while True:
            # Formato: 11100 + serie + correlativo (13 dígitos total)
            # Ejemplo: 1110012345678
            code = f"11100{i:08d}"  # 11100 + 8 dígitos = 13 dígitos
            if code not in data:
                return code
            i += 1
    
    def _validar_codigo_hold(self, codigo: str) -> bool:
        """Valida que el código HOLD tenga formato EAN13 (13 dígitos numéricos)"""
        if not codigo or len(codigo) != 13:
            return False
        return codigo.isdigit()
    
    def generar_ticket_suspension(self, codigo_hold: str, venta_suspendida: dict) -> str:
        """Genera el contenido del ticket de suspensión con código de barras"""
        try:
            # Generar código de barras ASCII
            codigo_barras = self._generar_codigo_barras_ascii(codigo_hold)
            
            # Crear contenido del ticket
            ticket = f"""
╔══════════════════════════════════════════════════════════════╗
║                    TICKET DE SUSPENSIÓN                      ║
╠══════════════════════════════════════════════════════════════╣
║ Código HOLD: {codigo_hold}                                    ║
║ Fecha: {venta_suspendida.get('fecha_suspension', 'N/A')}      ║
║ Usuario: {venta_suspendida.get('usuario', 'N/A')}             ║
║ Tipo: {venta_suspendida.get('tipo', 'N/A')}                  ║
║ Serie: {venta_suspendida.get('serie', 'N/A')}                ║
║ Número: {venta_suspendida.get('numero', 'N/A')}              ║
╠══════════════════════════════════════════════════════════════╣
║                    PRODUCTOS                                 ║
╠══════════════════════════════════════════════════════════════╣"""
            
            # Agregar productos
            for item in venta_suspendida.get('items', []):
                descripcion = item.get('descripcion', '')[:30]  # Limitar a 30 caracteres
                cantidad = item.get('cantidad', 0)
                precio = item.get('precio_unit', 0)
                importe = item.get('importe', 0)
                
                ticket += f"""
║ {descripcion:<30} ║
║ {cantidad:>8.2f} x S/. {precio:>8.2f} = S/. {importe:>8.2f} ║"""
            
            ticket += f"""
╠══════════════════════════════════════════════════════════════╣
║ Total: S/. {venta_suspendida.get('total', 0):>8.2f}                    ║
╠══════════════════════════════════════════════════════════════╣
║                    CÓDIGO DE BARRAS                          ║
║                                                              ║
{codigo_barras}
║                                                              ║
║  ESCANEE ESTE CÓDIGO PARA RECUPERAR LA VENTA                ║
╚══════════════════════════════════════════════════════════════╝
"""
            return ticket
            
        except Exception as e:
            return f"Error generando ticket: {str(e)}"
    
    def _generar_codigo_barras_ascii(self, codigo: str) -> str:
        """Genera una representación ASCII del código de barras EAN13"""
        try:
            # Crear una representación simple del código de barras
            barras = ""
            for i, digito in enumerate(codigo):
                if i % 2 == 0:
                    barras += "█" * 3  # Barra ancha
                else:
                    barras += "█" * 1  # Barra delgada
                barras += " " * 1      # Espacio entre barras
            
            # Crear el código de barras ASCII
            codigo_barras = f"""
║ {barras} ║
║ {codigo} ║
"""
            return codigo_barras
            
        except Exception as e:
            return f"Error: {str(e)}"
    
    def imprimir_ticket_fisico(self, contenido_ticket: str) -> None:
        """Envía el ticket a la impresora física del sistema"""
        try:
            import tempfile
            import subprocess
            
            # Crear archivo temporal con el contenido del ticket
            with tempfile.NamedTemporaryFile(mode='w', suffix='.txt', delete=False) as f:
                f.write(contenido_ticket)
                temp_file = f.name
            
            # Intentar imprimir usando diferentes comandos según el sistema
            try:
                # Para sistemas Linux/Unix
                subprocess.run(['lp', temp_file], check=True)
            except (subprocess.CalledProcessError, FileNotFoundError):
                try:
                    # Alternativa para algunos sistemas
                    subprocess.run(['lpr', temp_file], check=True)
                except (subprocess.CalledProcessError, FileNotFoundError):
                    # Si no hay impresora disponible, solo mostrar en consola
                    print("=== TICKET DE SUSPENSIÓN ===")
                    print(contenido_ticket)
                    print("=== FIN DEL TICKET ===")
            
            # Limpiar archivo temporal
            import os
            os.unlink(temp_file)
            
        except Exception as e:
            # Si hay error, mostrar en consola
            print(f"Error imprimiendo ticket: {str(e)}")
            print("=== TICKET DE SUSPENSIÓN ===")
            print(contenido_ticket)
            print("=== FIN DEL TICKET ===")

    # --- configuración ---
    def _leer_config_json(self, ruta_archivo: str) -> Optional[Dict[str, Any]]:
        try:
            if not os.path.exists(ruta_archivo):
                return None
            with open(ruta_archivo, "r", encoding="utf-8") as f:
                return json.load(f)
        except Exception:
            return None

    # --- acciones principales ---
    def _mapear_medio_pago(self, medio_ui: str) -> str:
        """Mapea los valores del UI a los valores aceptados por la base de datos"""
        mapeo = {
            "Efectivo": "efectivo",      # Valor exacto de la BD
            "Tarjeta": "tarjeta",        # Valor exacto de la BD
            "Mixto": "otros"             # Valor exacto de la BD
        }
        return mapeo.get(medio_ui, "efectivo")  # Por defecto efectivo

    def _mapear_tipo_comprobante(self, tipo_ui: str) -> str:
        """Mapea los valores del UI a los valores aceptados por la base de datos"""
        mapeo = {
            "B": "B",      # Boleta
            "F": "F",      # Factura
            "NC": "NC",    # Nota de Crédito
            "GR": "GR",    # Guía de Remisión
            "NP": "NP",    # Nota de Pedido
            "RE": "RE"     # Recibo
        }
        return mapeo.get(tipo_ui, "B")  # Por defecto Boleta

    def iniciar_comprobante(self):
        if self.comprobante_id:
            QMessageBox.information(self, "Aviso", "Ya hay un comprobante iniciado.")
            return

        # Verificar que se hayan ingresado los datos del cliente
        if self.lbl_documento_cliente.text() == "--" or self.lbl_nombre_cliente.text() == "--":
            QMessageBox.warning(self, "Datos del Cliente", "Debe ingresar los datos del cliente antes de iniciar el comprobante.")
            return

        # El tipo de documento se determina automáticamente basándose en los datos del cliente
        # RUC = Factura, DNI = Boleta

        # Determinar el tipo de documento basándose en los datos del cliente
        documento_cliente = self.lbl_documento_cliente.text()
        if documento_cliente.startswith("RUC:"):
            tipo_bd = "F"  # Factura
        else:
            tipo_bd = "B"  # Boleta (por defecto)
            
        # El medio de pago se seleccionará al momento de cobrar, no al iniciar
        # Por ahora usamos "efectivo" como valor por defecto
        medio_bd = "efectivo"
        
        try:
            usuario_id = self.db.get_usuario_id_por_login(self.usuario)
            if not usuario_id:
                QMessageBox.critical(self, "Error", "No se pudo obtener ID de usuario.")
                return

            terminal_nombre = (self.config or {}).get("terminal", "").strip()
            if not terminal_nombre:
                QMessageBox.critical(self, "Config", "Falta 'terminal' en config.json.")
                return

            # Verificar que la terminal existe antes de crear el comprobante
            try:
                with self.db.conn.cursor() as cur:
                    cur.execute("""
                        SELECT id_terminal, serie, tipo_comprobante, activo 
                        FROM terminal 
                        WHERE nombre = %s AND tipo_comprobante = %s
                    """, (terminal_nombre, tipo_bd))  # Usar tipo mapeado
                    terminal_info = cur.fetchone()
                    
                    if not terminal_info:
                        QMessageBox.critical(self, "Error de Terminal", 
                            f"No se encontró terminal '{terminal_nombre}' para tipo '{tipo_bd}'.\n"
                            f"Verifique que exista en la base de datos.")
                        return
                    
                    if not terminal_info[3]:  # activo = False
                        QMessageBox.critical(self, "Error de Terminal", 
                            f"La terminal '{terminal_nombre}' para tipo '{tipo_bd}' no está activa.\n"
                            f"ID: {terminal_info[0]}, Serie: {terminal_info[1]}")
                        return
                        
            except Exception as db_error:
                QMessageBox.critical(self, "Error de Base de Datos", 
                    f"Error al verificar terminal:\n{str(db_error)}")
                return

            # Obtener datos del cliente de la interfaz
            documento_cliente = self.lbl_documento_cliente.text()
            nombre_cliente = self.lbl_nombre_cliente.text()
            
            # Extraer solo el número del documento (sin "DNI:" o "RUC:")
            if documento_cliente.startswith("DNI: "):
                dni_cliente = documento_cliente.replace("DNI: ", "")
                ruc_cliente = None
            elif documento_cliente.startswith("RUC: "):
                ruc_cliente = documento_cliente.replace("RUC: ", "")
                dni_cliente = None
            else:
                dni_cliente = None
                ruc_cliente = None

            res = self.db.crear_comprobante_por_terminal(
                terminal_nombre=terminal_nombre,
                tipo=tipo_bd,           # Usar el tipo mapeado
                usuario_id=usuario_id,
                medio_pago=medio_bd,    # Usar el medio mapeado
                cliente_id=self.cliente_id_actual,  # Usar el ID del cliente actual
                dni=dni_cliente
            )
            self.comprobante_id = res["id"]
            self.lbl_serie.setText(f"Serie: {res['serie']}")
            self.lbl_numero.setText(f"Número: {res['numero']}")
            # Mostrar tipo de documento en la interfaz
            if tipo_bd == "F":
                self.lbl_tipo_documento.setText("Tipo: FACTURA")
            else:
                self.lbl_tipo_documento.setText("Tipo: BOLETA")
                
            QMessageBox.information(self, "OK", f"Comprobante {tipo_bd}-{res['serie']}-{res['numero']} listo.")
            
            # Mostrar botón para cambiar tipo de documento
            self.btn_cambiar_tipo.setVisible(True)
            
            self.input_codigo.setFocus()
        except Exception as e:
            import traceback
            error_details = traceback.format_exc()
            QMessageBox.critical(self, "Error Detallado", 
                f"No se pudo iniciar el comprobante:\n\n"
                f"Error: {str(e)}\n\n"
                f"Detalles técnicos:\n{error_details}")

    def on_enter_codigo(self):
        code = self.input_codigo.text().strip()
        if not code:
            return
        if not self.comprobante_id:
            QMessageBox.warning(self, "Aviso", "Inicie un comprobante primero.")
            return

        # Primero buscar por código de barras; si no, por SKU
        prod = self._buscar_por_barra(code)
        if not prod:
            prod = self._buscar_por_sku(code)

        if not prod:
            QMessageBox.warning(self, "No encontrado", "No se encontró producto.")
            self.input_codigo.selectAll()
            return

        self._agregar_item(prod, cantidad=1.0)
        self.input_codigo.clear()

    def buscar_descripcion_dialog(self):
        text, ok = QInputDialog.getText(self, "Buscar producto", "Descripción:")
        if not ok or not text.strip():
            return
        results = self.db.buscar_producto_por_descripcion(text.strip())
        if not results:
            QMessageBox.information(self, "Sin resultados", "No se encontraron coincidencias.")
            return

        # Dialogo simple con tabla
        dlg = QDialog(self)
        dlg.setWindowTitle("Elegir producto")
        dlg.setMinimumSize(600, 300)  # Diálogo más grande para mejor visualización
        layout = QVBoxLayout(dlg)
        tbl = QTableWidget(0, 4, dlg)
        tbl.setHorizontalHeaderLabels(["SKU", "Descripción", "Barra", "Precio"])
        
        # Aplicar estilos CSS para mejor apariencia
        tbl.setStyleSheet("""
            QTableWidget {
                background-color: #20203d ;
                gridline-color: #d0d0d0;
                font-size: 12px;
            }
            QTableWidget::item {
                padding: 8px;
                border-bottom: 1px solid #f0f0f0;
            }
            QTableWidget::item:selected {
                background-color: #3498db;
                color: white;
            }
            QHeaderView::section {
                background-color: #34495e;
                color: white;
                padding: 10px;
                border: none;
                font-weight: bold;
                font-size: 13px;
            }
        """)
        # Configurar columnas con anchos específicos para mejor visualización
        header = tbl.horizontalHeader()
        header.setSectionResizeMode(0, QHeaderView.ResizeMode.Fixed)  # Código Barra
        header.setSectionResizeMode(1, QHeaderView.ResizeMode.Stretch)  # Descripción
        header.setSectionResizeMode(2, QHeaderView.ResizeMode.Fixed)  # Barra
        header.setSectionResizeMode(3, QHeaderView.ResizeMode.Fixed)  # Precio
        
        # Establecer anchos específicos
        tbl.setColumnWidth(0, 120)  # SKU (más ancho)
        tbl.setColumnWidth(1, 240)  # Descripción (más ancho)
        tbl.setColumnWidth(2, 120)  # Código de barras (más ancho)
        tbl.setColumnWidth(3, 80)   # Precio (más ancho)
        
        # La columna de descripción se expande automáticamente (Stretch)
        
        # Configurar alineación de encabezados
        header.setDefaultAlignment(Qt.AlignmentFlag.AlignCenter)
        for r in results:
            row = tbl.rowCount()
            tbl.insertRow(row)
            # SKU (centrado)
            sku_item = QTableWidgetItem(r["sku"])
            sku_item.setTextAlignment(Qt.AlignmentFlag.AlignCenter)
            tbl.setItem(row, 0, sku_item)
            
            # Descripción (izquierda, sin centrar)
            desc_item = QTableWidgetItem(r["descripcion_larga"])
            desc_item.setTextAlignment(Qt.AlignmentFlag.AlignLeft | Qt.AlignmentFlag.AlignVCenter)
            # Siempre mostrar tooltip para descripciones largas
            if len(r["descripcion_larga"]) > 40:
                desc_item.setToolTip(r["descripcion_larga"])
            tbl.setItem(row, 1, desc_item)
            
            # Código de barras (izquierda, sin centrar)
            barra_item = QTableWidgetItem(r["codigo_barra"])
            barra_item.setTextAlignment(Qt.AlignmentFlag.AlignLeft | Qt.AlignmentFlag.AlignVCenter)
            tbl.setItem(row, 2, barra_item)
            
            # Precio (centrado, redondeado a 2 decimales)
            precio_redondeado = round(float(r['precio_venta']), 2)
            precio_item = QTableWidgetItem(f"{precio_redondeado:.2f}")
            precio_item.setTextAlignment(Qt.AlignmentFlag.AlignCenter)
            tbl.setItem(row, 3, precio_item)
        layout.addWidget(tbl)

        btns = QHBoxLayout()
        b_ok = QPushButton("Agregar")
        b_ok.clicked.connect(dlg.accept)
        b_cancel = QPushButton("Cancelar")
        b_cancel.clicked.connect(dlg.reject)
        btns.addStretch(); btns.addWidget(b_ok); btns.addWidget(b_cancel)
        layout.addLayout(btns)

        if dlg.exec():
            row = tbl.currentRow()
            if row >= 0:
                r = results[row]
                prod = ProductoLite(
                    id=r["id"], sku=r["sku"], descripcion_larga=r["descripcion_larga"],
                    codigo_barra=r["codigo_barra"], costo_compra=float(r["costo_compra"] or 0),
                    costo_promedio=float(r["costo_promedio"] or 0), valor_venta=float(r["valor_venta"] or 0),
                    precio_venta=float(r["precio_venta"] or 0), afecto_igv=bool(r.get("afecto_igv", True))
                )
                self._agregar_item(prod, cantidad=1.0)

    # --- helpers de producto ---
    def _buscar_por_barra(self, barra: str) -> Optional[ProductoLite]:
        rows = self.db.buscar_producto_por_barra(barra)
        if not rows:
            return None
        r = rows[0]
        return ProductoLite(
            id=r["id"], sku=r["sku"], descripcion_larga=r["descripcion_larga"],
            codigo_barra=r["codigo_barra"], costo_compra=float(r["costo_compra"] or 0),
            costo_promedio=float(r["costo_promedio"] or 0), valor_venta=float(r["valor_venta"] or 0),
            precio_venta=float(r["precio_venta"] or 0), afecto_igv=bool(r.get("afecto_igv", True))
        )

    def _buscar_por_sku(self, sku: str) -> Optional[ProductoLite]:
        rows = self.db.buscar_producto_por_sku(sku)
        if not rows:
            return None
        r = rows[0]
        return ProductoLite(
            id=r["id"], sku=r["sku"], descripcion_larga=r["descripcion_larga"],
            codigo_barra=r["codigo_barra"], costo_compra=float(r["costo_compra"] or 0),
            costo_promedio=float(r["costo_promedio"] or 0), valor_venta=float(r["valor_venta"] or 0),
            precio_venta=float(r["precio_venta"] or 0), afecto_igv=bool(r.get("afecto_igv", True))
        )

    # --- agregar ítem a la tabla + cálculos ---
    def _agregar_item(self, p: ProductoLite, cantidad: float):
        # Cálculo por ítem
        if p.afecto_igv:
            valor_unit = round(p.precio_venta / (1 + IGV_TASA), 2)
            igv_unit = round(valor_unit * IGV_TASA, 2)
            precio_unit = round(valor_unit + igv_unit, 2)
        else:
            valor_unit = round(p.precio_venta, 2)
            igv_unit = 0.0
            precio_unit = valor_unit

        importe = round(precio_unit * cantidad, 2)

        row = self.tbl.rowCount()
        self.tbl.insertRow(row)
        
        # Columnas no editables (sin flag ItemIsEditable)
        for col in [0, 1, 2, 3, 5, 6, 7]:
            item = QTableWidgetItem()
            item.setFlags(item.flags() & ~Qt.ItemIsEditable)  # Remover flag editable
            self.tbl.setItem(row, col, item)
        
        # Asignar valores a las columnas no editables
        self.tbl.item(row, 0).setText(p.codigo_barra)
        
        # Descripción con tooltip para descripciones largas
        desc_item = self.tbl.item(row, 1)
        desc_item.setText(p.descripcion_larga)
        if len(p.descripcion_larga) > 50:
            desc_item.setToolTip(p.descripcion_larga)
        
        self.tbl.item(row, 2).setText(p.unidad_medida)
        self.tbl.item(row, 3).setText("Sí" if p.afecto_igv else "No")
        self.tbl.item(row, 5).setText(f"{igv_unit:.2f}")
        self.tbl.item(row, 6).setText(f"{precio_unit:.2f}")
        self.tbl.item(row, 7).setText(f"{importe:.2f}")
        
        # Columna de cantidad (editable)
        cantidad_item = QTableWidgetItem(f"{cantidad:.2f}")
        cantidad_item.setFlags(cantidad_item.flags() | Qt.ItemIsEditable)
        self.tbl.setItem(row, 4, cantidad_item)
        
        # Guardar datos del producto para recalcular
        self.tbl.item(row, 4).setData(Qt.UserRole, {
            "producto_id": p.id,
            "afecto_igv": p.afecto_igv,
            "valor_unit": valor_unit,
            "igv_unit": igv_unit,
            "precio_unit": precio_unit
        })

        # Guarda en espejo para suspender
        self.items.append({
            "producto_id": p.id, "codigo_barra": p.codigo_barra, "descripcion": p.descripcion_larga,
            "unidad": p.unidad_medida, "afecto_igv": p.afecto_igv,
            "cantidad": cantidad, "valor_unit": valor_unit,
            "igv_unit": igv_unit, "precio_unit": precio_unit, "importe": importe
        })

        self._recalc_totales()
        
        # Limpiar entrada después de agregar producto
        self.entrada_actual = ""
        self.input_codigo.clear()
        self.input_codigo.setFocus()

    def _on_cantidad_changed(self, item):
        """Maneja el cambio de cantidad en la tabla"""
        # Verificar que el item no sea None
        if item is None:
            return
            
        # Solo procesar cambios en la columna de cantidad (columna 4)
        if item.column() != 4:
            return
            
        try:
            # Obtener la nueva cantidad
            nueva_cantidad = float(item.text())
            if nueva_cantidad <= 0:
                QMessageBox.warning(self, "Error", "La cantidad debe ser mayor a 0")
                # Restaurar valor anterior
                self._restaurar_cantidad_anterior(item)
                return
                
            row = item.row()
            
            # Obtener datos del producto guardados en UserRole
            producto_data = item.data(Qt.UserRole)
            if not producto_data:
                return
                
            # Recalcular importe
            valor_unit = producto_data["valor_unit"]
            igv_unit = producto_data["igv_unit"]
            precio_unit = producto_data["precio_unit"]
            
            nuevo_importe = round(precio_unit * nueva_cantidad, 2)
            
            # Actualizar columna de importe
            self.tbl.setItem(row, 7, QTableWidgetItem(f"{nuevo_importe:.2f}"))
            
            # Actualizar lista de items para mantener sincronización
            if row < len(self.items):
                self.items[row]["cantidad"] = nueva_cantidad
                self.items[row]["importe"] = nuevo_importe
            
            # Recalcular totales
            self._recalc_totales()
            
        except ValueError:
            QMessageBox.warning(self, "Error", "La cantidad debe ser un número válido")
            self._restaurar_cantidad_anterior(item)
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error al actualizar cantidad: {str(e)}")
            self._restaurar_cantidad_anterior(item)
    
    def _restaurar_cantidad_anterior(self, item):
        """Restaura la cantidad anterior si hay error"""
        try:
            row = item.row()
            if row < len(self.items):
                cantidad_anterior = self.items[row]["cantidad"]
                item.setText(f"{cantidad_anterior:.2f}")
        except:
            pass

    def _on_table_key_press(self, event):
        """Maneja eventos de teclado en la tabla"""
        if event.key() == Qt.Key_Delete:
            self._eliminar_fila_seleccionada()
        else:
            # Llamar al método original de keyPressEvent
            QTableWidget.keyPressEvent(self.tbl, event)
    
    def _eliminar_fila_seleccionada(self):
        """Elimina la fila seleccionada con confirmación"""
        current_row = self.tbl.currentRow()
        if current_row < 0:
            QMessageBox.information(self, "Aviso", "Seleccione una fila para eliminar")
            return
            
        # Obtener información del producto para el mensaje de confirmación
        try:
            sku = self.tbl.item(current_row, 0).text()
            descripcion = self.tbl.item(current_row, 1).text()
            cantidad = self.tbl.item(current_row, 4).text()
            
            # Mensaje de confirmación
            reply = QMessageBox.question(
                self, 
                "Confirmar Eliminación",
                f"¿Está seguro que desea eliminar el producto?\n\n"
                f"SKU: {sku}\n"
                f"Descripción: {descripcion}\n"
                f"Cantidad: {cantidad}",
                QMessageBox.StandardButton.Yes | QMessageBox.StandardButton.No,
                QMessageBox.StandardButton.No
            )
            
            if reply == QMessageBox.StandardButton.Yes:
                # Eliminar fila de la tabla
                self.tbl.removeRow(current_row)
                
                # Eliminar item de la lista interna
                if current_row < len(self.items):
                    del self.items[current_row]
                
                # Recalcular totales
                self._recalc_totales()
                
                QMessageBox.information(self, "Eliminado", "Producto eliminado correctamente")
                
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error al eliminar fila: {str(e)}")

    # --- totales y vuelto ---
    def _recalc_totales(self):
        sub_af = 0.0
        sub_in = 0.0
        igv = 0.0
        total = 0.0

        for r in range(self.tbl.rowCount()):
            afecto = (self.tbl.item(r, 3).text().strip().lower() == "sí")
            cant = float(self.tbl.item(r, 4).text())
            igv_unit = float(self.tbl.item(r, 5).text())
            precio_unit = float(self.tbl.item(r, 6).text())
            importe = float(self.tbl.item(r, 7).text())

            if afecto:
                sub_af += round((precio_unit - igv_unit) * cant, 2)
                igv += round(igv_unit * cant, 2)
            else:
                sub_in += round(precio_unit * cant, 2)
            total += importe

        self.lbl_subaf.setText(f"S/. {sub_af:.2f}")
        self.lbl_subin.setText(f"S/. {sub_in:.2f}")
        self.lbl_igv.setText(f"S/. {igv:.2f}")
        # Calcular descuento (por ahora 0, pero se puede implementar lógica de descuento)
        descuento = 0.0
        total_con_descuento = total - descuento
        
        self.lbl_descuento_aplicado.setText(f"S/. {descuento:.2f}")
        self.lbl_total.setText(f"S/. {total_con_descuento:.2f}")
        

        
        # Actualizar contador de artículos
        self.lbl_cant_articulos.setText(f"{self.tbl.rowCount()} Artículos")
        
        # Actualizar vista del cliente

        

        



    
    def abrir_supervisor_caja(self):
        """Abre el diálogo de supervisor de caja"""
        try:
            # Importar y abrir diálogo de supervisor
            from ui.supervisor_dialog import SupervisorDialog
            
            dialog = SupervisorDialog(self)
            dialog.lbl_cajero.setText(self.usuario)
            dialog.exec()
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error al abrir supervisor: {str(e)}")
    

    
    def bloquear_caja(self):
        """Bloquea la caja creando solo el overlay"""
        self.crear_overlay_bloqueo()
    
    def crear_overlay_bloqueo(self):
        """Crea un overlay para bloquear la interfaz"""
        from PySide6.QtWidgets import QWidget, QLabel, QVBoxLayout, QPushButton
        from PySide6.QtCore import Qt
        
        # Crear overlay
        self.overlay_bloqueo = QWidget(self)
        self.overlay_bloqueo.setStyleSheet("""
            QWidget {
                background-color: rgba(0, 0, 0, 0.8);
                border: none;
            }
        """)
        
        # Asegurar que el overlay sea interactivo
        self.overlay_bloqueo.setAttribute(Qt.WidgetAttribute.WA_TransparentForMouseEvents, False)
        self.overlay_bloqueo.setAttribute(Qt.WidgetAttribute.WA_NoSystemBackground, False)
        self.overlay_bloqueo.setAttribute(Qt.WidgetAttribute.WA_TranslucentBackground, False)
        
        # Layout del overlay
        layout_bloqueo = QVBoxLayout(self.overlay_bloqueo)
        layout_bloqueo.setAlignment(Qt.AlignmentFlag.AlignCenter)
        layout_bloqueo.setSpacing(20)
        
        # Mensaje de bloqueo
        lbl_bloqueo = QLabel("🔒 CAJA BLOQUEADA")
        lbl_bloqueo.setStyleSheet("""
            font-size: 48px; 
            font-weight: bold; 
            color: #e74c3c; 
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 15px;
        """)
        lbl_bloqueo.setAlignment(Qt.AlignmentFlag.AlignCenter)
        
        lbl_mensaje = QLabel("Presione el botón para desbloquear")
        lbl_mensaje.setStyleSheet("""
            font-size: 24px; 
            color: white; 
            background: rgba(0, 0, 0, 0.8);
            padding: 15px;
            border-radius: 10px;
        """)
        lbl_mensaje.setAlignment(Qt.AlignmentFlag.AlignCenter)
        
        # Botón para desbloquear
        btn_desbloquear = QPushButton("🔓 DESBLOQUEAR CAJA")
        btn_desbloquear.setStyleSheet("""
            QPushButton {
                background: #2ecc71;
                color: white;
                font-size: 18px;
                font-weight: bold;
                padding: 15px 30px;
                border-radius: 12px;
                border: none;
                min-width: 120px;
            }
            /* Estados interactivos (aplican a todos los botones) */
            QPushButton:hover {
                background: #2ecc71;
            }
            
            QPushButton:pressed {
                background: #229954;
            }
            
            /* Estados interactivos ESPECÍFICOS para este botón */
            QPushButton#btn_desbloquear:hover {
                border: 2px solid #27ae60;
            }


        """)
        btn_desbloquear.clicked.connect(lambda: self.desbloquear_desde_overlay())
        
        layout_bloqueo.addWidget(lbl_bloqueo)
        layout_bloqueo.addWidget(lbl_mensaje)
        layout_bloqueo.addWidget(btn_desbloquear)
        
        # Posicionar el overlay sobre toda la ventana
        self.overlay_bloqueo.setGeometry(self.rect())
        self.overlay_bloqueo.show()
        self.overlay_bloqueo.raise_()
        
        # NO deshabilitar toda la ventana, solo crear el overlay
        # El overlay se posiciona encima y captura todos los eventos
        
        # Configurar el overlay para que sea completamente interactivo
        self.overlay_bloqueo.setAttribute(Qt.WidgetAttribute.WA_TransparentForMouseEvents, False)
        self.overlay_bloqueo.setAttribute(Qt.WidgetAttribute.WA_NoSystemBackground, False)
        self.overlay_bloqueo.setAttribute(Qt.WidgetAttribute.WA_TranslucentBackground, False)
        self.overlay_bloqueo.setFocusPolicy(Qt.FocusPolicy.StrongFocus)
        self.overlay_bloqueo.setFocus()
        
        # Conectar eventos de teclado
        self.overlay_bloqueo.keyPressEvent = self.overlay_key_press_event
        
        # Asegurar que el overlay esté en primer plano y sea modal
        self.overlay_bloqueo.raise_()
        self.overlay_bloqueo.activateWindow()
        
        # Bloquear widgets específicos de la interfaz (no toda la ventana)
        self.bloquear_widgets_interfaz()
    
    def bloquear_widgets_interfaz(self):
        """Bloquea widgets específicos de la interfaz sin afectar el overlay"""
        # Lista de widgets principales a bloquear (con verificación de existencia)
        widgets_a_bloquear = []
        
        # Verificar y agregar widgets que existen
        if hasattr(self, 'tbl') and self.tbl is not None:
            widgets_a_bloquear.append(('tbl', self.tbl))
        
        if hasattr(self, 'input_codigo') and self.input_codigo is not None:
            widgets_a_bloquear.append(('input_codigo', self.input_codigo))
        
        if hasattr(self, 'btn_iniciar') and self.btn_iniciar is not None:
            widgets_a_bloquear.append(('btn_iniciar', self.btn_iniciar))
        
        if hasattr(self, 'btn_suspender') and self.btn_suspender is not None:
            widgets_a_bloquear.append(('btn_suspender', self.btn_suspender))
        
        if hasattr(self, 'btn_recuperar') and self.btn_recuperar is not None:
            widgets_a_bloquear.append(('btn_recuperar', self.btn_recuperar))
        
        if hasattr(self, 'btn_pagos') and self.btn_pagos is not None:
            widgets_a_bloquear.append(('btn_pagos', self.btn_pagos))
        
        if hasattr(self, 'btn_datos_cliente') and self.btn_datos_cliente is not None:
            widgets_a_bloquear.append(('btn_datos_cliente', self.btn_datos_cliente))
        
        if hasattr(self, 'btn_cambiar_tipo') and self.btn_cambiar_tipo is not None:
            widgets_a_bloquear.append(('btn_cambiar_tipo', self.btn_cambiar_tipo))
        
        # Bloquear cada widget individualmente
        for nombre, widget in widgets_a_bloquear:
            try:
                widget.setEnabled(False)
            except Exception as e:
                pass
    
    def desbloquear_widgets_interfaz(self):
        """Desbloquea todos los widgets de la interfaz"""
        # Lista de widgets principales a desbloquear (con verificación de existencia)
        widgets_a_desbloquear = []
        
        # Verificar y agregar widgets que existen
        if hasattr(self, 'tbl') and self.tbl is not None:
            widgets_a_desbloquear.append(('tbl', self.tbl))
        
        if hasattr(self, 'btn_iniciar') and self.btn_iniciar is not None:
            widgets_a_desbloquear.append(('btn_iniciar', self.btn_iniciar))
        
        if hasattr(self, 'btn_suspender') and self.btn_suspender is not None:
            widgets_a_desbloquear.append(('btn_suspender', self.btn_suspender))
        
        if hasattr(self, 'btn_recuperar') and self.btn_recuperar is not None:
            widgets_a_desbloquear.append(('btn_recuperar', self.btn_recuperar))
        
        if hasattr(self, 'btn_pagos') and self.btn_pagos is not None:
            widgets_a_desbloquear.append(('btn_pagos', self.btn_pagos))
        
        if hasattr(self, 'btn_datos_cliente') and self.btn_datos_cliente is not None:
            widgets_a_desbloquear.append(('btn_datos_cliente', self.btn_datos_cliente))
        
        if hasattr(self, 'btn_cambiar_tipo') and self.btn_cambiar_tipo is not None:
            widgets_a_desbloquear.append(('btn_cambiar_tipo', self.btn_cambiar_tipo))
        
        # Desbloquear cada widget individualmente
        for nombre, widget in widgets_a_desbloquear:
            try:
                widget.setEnabled(True)
            except Exception as e:
                pass
    
    def desbloquear_desde_overlay(self):
        """Maneja el desbloqueo desde el overlay"""
        # Abrir el diálogo de desbloqueo
        self.mostrar_dialogo_desbloqueo()
    
    def overlay_key_press_event(self, event):
        """Maneja los eventos de teclado en el overlay de bloqueo"""
        if event.key() == Qt.Key.Key_Return or event.key() == Qt.Key.Key_Enter:
            # Al presionar Enter, abrir el diálogo de desbloqueo
            self.desbloquear_desde_overlay()
        elif event.key() == Qt.Key.Key_Escape:
            # Al presionar Escape, no hacer nada (mantener bloqueado)
            pass
        else:
            # Para cualquier otra tecla, abrir el diálogo de desbloqueo
            self.desbloquear_desde_overlay()
    
    def mostrar_dialogo_desbloqueo(self):
        """Muestra el diálogo para ingresar la clave de desbloqueo"""
        from PySide6.QtWidgets import QDialog, QVBoxLayout, QHBoxLayout, QLabel, QLineEdit, QPushButton
        
        # Crear diálogo personalizado
        dialog = QDialog(self)
        dialog.setWindowTitle("🔓 Desbloquear Caja")
        dialog.setModal(True)
        dialog.setFixedSize(450, 400)
        dialog.setStyleSheet("""
            QDialog {
                background: #2c3e50;
                border: 2px solid #34495e;
                border-radius: 10px;
            }
            QLabel {
                color: white;
                font-size: 14px;
                font-weight: bold;
            }
            QLineEdit {
                padding: 12px;
                font-size: 16px;
                border: 2px solid #3498db;
                border-radius: 6px;
                background: #ffffff;
                color: #17202a;
                font-weight: 400;
                text-align: center;
            }
            QLineEdit:focus {
                border: 2px solid #e74c3c;
                background: #f8f9fa;
            }
            QPushButton {
                padding: 15px 25px;
                font-size: 16px;
                font-weight: bold;
                border-radius: 8px;
                color: white;
                border: none;
                min-width: 100px;
                min-height: 40px;
            }
            QPushButton#btn_desbloquear {
                background: #27ae60;
                font-size: 18px;
                padding: 18px 35px;
                border-radius: 12px;
                border: 2px solid #1e8449;
                min-width: 90px;
                min-height: 25px;
            }
            QPushButton#btn_desbloquear:hover {
                background: #2ecc71;
                border: 2px solid #27ae60;
            }
            QPushButton#btn_cancelar {
                background: #e74c3c;
                font-size: 18px;
                padding: 18px 35px;
                border-radius: 12px;
                border: 2px solid #c0392b;
                min-width: 90px;
                min-height: 25px;
            }
            QPushButton#btn_cancelar:hover {
                background: #c0392b;
                border: 2px solid #e74c3c;
            }
        """)
        
        # Layout del diálogo
        layout = QVBoxLayout(dialog)
        layout.setSpacing(20)
        layout.setContentsMargins(30, 30, 30, 30)
        
        # Título
        lbl_titulo = QLabel("Ingrese su clave para desbloquear la caja")
        # Mantener alto visual sin oscurecer el texto
        lbl_titulo.setStyleSheet("font-size: 16px; font-weight: bold; color: white;")
        lbl_titulo.setMinimumHeight(28)
        lbl_titulo.setAlignment(Qt.AlignmentFlag.AlignCenter)
        layout.addWidget(lbl_titulo)
        
        # Mostrar usuario actual
        lbl_usuario = QLabel(f"Usuario: {self.usuario}")
        lbl_usuario.setStyleSheet("font-size: 14px; color: #85c1e9; font-weight: bold;")
        lbl_usuario.setMinimumHeight(24)
        lbl_usuario.setAlignment(Qt.AlignmentFlag.AlignCenter)
        layout.addWidget(lbl_usuario)
        
        # Campo de clave
        lbl_clave = QLabel("Clave:")
        lbl_clave.setStyleSheet("font-size: 14px; color: #ecf0f1;  font-weight: bold;")
        lbl_clave.setMinimumHeight(22)
        lbl_clave.setAlignment(Qt.AlignmentFlag.AlignCenter)
        layout.addWidget(lbl_clave)
        
        self.input_clave_desbloqueo = QLineEdit()
        self.input_clave_desbloqueo.setEchoMode(QLineEdit.EchoMode.Password)
        self.input_clave_desbloqueo.setPlaceholderText("Ingrese su clave")
        self.input_clave_desbloqueo.setAlignment(Qt.AlignmentFlag.AlignCenter)
        self.input_clave_desbloqueo.setMinimumHeight(35)
        self.input_clave_desbloqueo.returnPressed.connect(lambda: self.verificar_clave_desbloqueo(dialog))
        # Aplicando estilo CSS moderno
        self.input_clave_desbloqueo.setStyleSheet("""
            QLineEdit {
                background-color: #f8f9fa;
                border: 2px solid #dee2e6;
                border-radius: 8px;
                padding: 10px 16px;
                font-size: 16px;
                color: #212529;
                selection-background-color: #90e0ef;
                min-width: 90px;
                min-height: 25px;
            }
            
            QLineEdit:focus {
                border: 2px solid #48cae4;
                background-color: #ffffff;
            }
            
            QLineEdit[echoMode="2"] {  /* Estilo específico para campos de password */
                letter-spacing: 2px;  /* Espaciado para los asteriscos */
            }
            
            QLineEdit::placeholder {
                color: #adb5bd;
                font-style: italic;
            }
        """)
        layout.addWidget(self.input_clave_desbloqueo)
        
        # Mensaje de ayuda
        lbl_ayuda = QLabel("💡 Presione Enter o clic en Desbloquear")
        lbl_ayuda.setStyleSheet("font-size: 12px; color: #f1c40f; font-weight: bold;")
        lbl_ayuda.setMinimumHeight(20)
        lbl_ayuda.setAlignment(Qt.AlignmentFlag.AlignCenter)
        layout.addWidget(lbl_ayuda)
        
        # Botones
        botones_layout = QHBoxLayout()
        botones_layout.setSpacing(20)  # Espacio entre botones
        botones_layout.setContentsMargins(10, 10, 10, 10)  # Márgenes del layout
        
        btn_desbloquear = QPushButton("🔓 Desbloquear")
        btn_desbloquear.setObjectName("btn_desbloquear")
        btn_desbloquear.clicked.connect(lambda: self.verificar_clave_desbloqueo(dialog))
        botones_layout.addWidget(btn_desbloquear)
        
        btn_cancelar = QPushButton("❌ Cancelar")
        btn_cancelar.setObjectName("btn_cancelar")
        btn_cancelar.clicked.connect(lambda: self.cancelar_desbloqueo(dialog))
        botones_layout.addWidget(btn_cancelar)
        
        layout.addLayout(botones_layout)
        
        # Mostrar diálogo
        dialog.exec()
    
    def cancelar_desbloqueo(self, dialog):
        """Maneja la cancelación del desbloqueo"""
        # Cerrar el diálogo
        dialog.reject()
    
    def verificar_clave_desbloqueo(self, dialog):
        """Verifica la clave ingresada para desbloquear la caja"""
        clave_ingresada = self.input_clave_desbloqueo.text()
        
        if not clave_ingresada:
            QMessageBox.warning(self, "Error", "Debe ingresar una clave.")
            return
        
        # Verificar si la clave coincide con la del usuario actual
        resultado_verificacion = self.verificar_clave_usuario(clave_ingresada)
        
        if resultado_verificacion:
            # Clave correcta - desbloquear
            try:
                self.desbloquear_caja()
                dialog.accept()
                QMessageBox.information(self, "Caja Desbloqueada", "Clave correcta. La caja ha sido desbloqueada.")
            except Exception as e:
                QMessageBox.critical(self, "Error", f"Error al desbloquear: {str(e)}")
        else:
            # Clave incorrecta
            QMessageBox.critical(
                self, 
                "Clave Incorrecta", 
                "La clave ingresada no es correcta.\n\n"
                "Verifique que esté ingresando la clave correcta del usuario actual.\n\n"
                "Presione Enter o haga clic en 'Desbloquear' para reintentar."
            )
            # Limpiar el campo y mantener el foco para reintentar
            self.input_clave_desbloqueo.clear()
            self.input_clave_desbloqueo.setFocus()
            # NO cerrar el diálogo, permitir reintentar inmediatamente
    
    def verificar_clave_usuario(self, clave_ingresada):
        """Verifica si la clave ingresada coincide con la del usuario actual"""
        try:
            if not self.usuario or not clave_ingresada:
                return False
            
            # Verificar si la base de datos está disponible
            if not hasattr(self.db, 'conn') or not self.db.conn:
                return False
            
            try:
                # Verificar la clave directamente en la base de datos
                cur = self.db.conn.cursor()
                cur.execute("SELECT password FROM usuarios WHERE usuario = %s", (self.usuario,))
                row = cur.fetchone()
                
                if row:
                    stored_clave = row[0]
                    resultado = stored_clave == clave_ingresada
                    return resultado
                else:
                    return False
                    
            except Exception as db_error:
                # Fallback: clave de testing (solo para desarrollo)
                if clave_ingresada == "1234":
                    return True
                return False
                
        except Exception as e:
            return False
    
    def desbloquear_caja(self):
        """Desbloquea la caja y restaura la interfaz"""
        try:
            # Desbloquear todos los widgets de la interfaz
            self.desbloquear_widgets_interfaz()
            
            # Ocultar y eliminar el overlay de bloqueo
            if hasattr(self, 'overlay_bloqueo'):
                self.overlay_bloqueo.hide()
                self.overlay_bloqueo.deleteLater()
                delattr(self, 'overlay_bloqueo')
            
        except Exception as e:
            raise e
    
    def resizeEvent(self, event):
        """Maneja el cambio de tamaño de la ventana"""
        super().resizeEvent(event)
        
        # Ajustar el overlay de bloqueo si existe
        if hasattr(self, 'overlay_bloqueo') and self.overlay_bloqueo.isVisible():
            self.overlay_bloqueo.setGeometry(self.rect())
    
    def consultar_precio(self):
        """Consulta precio de un producto"""
        from PySide6.QtWidgets import QInputDialog
        
        codigo, ok = QInputDialog.getText(self, "Consultar Precio", "Ingrese código/SKU del producto:")
        if ok and codigo:
            # Aquí iría la lógica para consultar precio
            QMessageBox.information(self, "Consulta", f"Consultando precio para: {codigo}")
    
    def recobrar_anulado(self):
        """Recobra una venta anulada"""
        QMessageBox.information(self, "Recobrar", "Funcionalidad de recobrar venta anulada.")
    
    def cerrar_cajero(self):
        """Cierra la sesión del cajero"""
        reply = QMessageBox.question(
            self, "Cerrar Cajero", 
            "¿Está seguro que desea cerrar la sesión del cajero?",
            QMessageBox.StandardButton.Yes | QMessageBox.StandardButton.No,
            QMessageBox.StandardButton.No
        )
        
        if reply == QMessageBox.StandardButton.Yes:
            self.close()
    
    def descuento_producto(self):
        """Aplica descuento a un producto específico"""
        QMessageBox.information(self, "Descuento Producto", "Funcionalidad de descuento por producto.")
    
    def descuento_total(self):
        """Aplica descuento al total de la venta"""
        QMessageBox.information(self, "Descuento Total", "Funcionalidad de descuento total.")


    # --- suspender / recuperar ---
    def suspender_venta(self):
        """Suspende la venta actual con código EAN13 y genera ticket"""
        if not self.comprobante_id or self.tbl.rowCount() == 0:
            QMessageBox.warning(self, "Aviso", "No hay comprobante o ítems para suspender.")
            return
        
        try:
            # Generar código EAN13 para la suspensión
            codigo_hold = self._new_hold_code_ean13()
            
            # Extraer el total del label, removiendo "S/. " y espacios
            total_text = self.lbl_total.text().split(":")[-1].strip()
            total = float(total_text.replace("S/. ", ""))
            
            # Crear datos de la venta suspendida
            venta_suspendida = {
            "comprobante_id": self.comprobante_id,
            "tipo": self.lbl_tipo_documento.text().replace("Tipo: ", "").strip(),
            "serie": self.lbl_serie.text().replace("Serie: ", "").strip(),
            "numero": self.lbl_numero.text().replace("Número: ", "").strip(),
            "items": self.items,
                "total": total,
                "fecha_suspension": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
                "usuario": self.usuario
        }
            
            # Guardar en archivo HOLD
            data = self._load_holds()
            data[codigo_hold] = venta_suspendida
            self._save_holds(data)
            
            # Generar y imprimir ticket de suspensión
            ticket_content = self.generar_ticket_suspension(codigo_hold, venta_suspendida)
            
            # Imprimir ticket físico
            try:
                self.imprimir_ticket_fisico(ticket_content)
            except Exception as e:
                QMessageBox.warning(self, "Advertencia", f"No se pudo imprimir el ticket: {str(e)}")
            
            # Mostrar mensaje de éxito
            QMessageBox.information(
                self, 
                "Venta Suspendida", 
                f"✅ Venta suspendida exitosamente\n\n"
                f"📋 Código HOLD: {codigo_hold}\n"
                f"💰 Total: S/. {total:.2f}\n"
                f"📦 Artículos: {len(self.items)}\n\n"
                f"🎫 Ticket impreso con código de barras\n"
                f"💾 Datos guardados para recuperación"
            )
            
            # Limpiar la venta actual (NO cerrar el POS)
            self.limpiar_venta_actual()
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error al suspender la venta: {str(e)}")

    def recuperar_venta(self):
        """Recupera una venta suspendida usando código EAN13"""
        try:
            # Solicitar código HOLD EAN13
            codigo_hold, ok = QInputDialog.getText(
                self, 
                "Recuperar Venta", 
                "Ingrese el código HOLD (13 dígitos):"
            )
            
            if not ok or not codigo_hold:
                return
            
            # Validar formato EAN13
            if not self._validar_codigo_hold(codigo_hold):
                QMessageBox.warning(
                    self, 
                    "Código Inválido", 
                    "El código debe tener 13 dígitos numéricos.\n"
                    "Ejemplo: 1110012345678"
                )
                return
            
            # Cargar datos de ventas suspendidas
            data = self._load_holds()
            
            if codigo_hold not in data:
                QMessageBox.warning(
                    self, 
                    "Venta No Encontrada", 
                    f"No se encontró una venta suspendida con el código:\n{codigo_hold}"
                )
                return

            # Recuperar datos de la venta
            venta = data[codigo_hold]
            
            # Restaurar información del comprobante
            self.comprobante_id = venta["comprobante_id"]
            self.lbl_tipo_documento.setText(f"Tipo: {venta['tipo']}")
            self.lbl_serie.setText(f"Serie: {venta['serie']}")
            self.lbl_numero.setText(f"Número: {venta['numero']}")

            # Limpiar tabla actual
            self.tbl.setRowCount(0)
            self.items = []
            
            # Restaurar items de la venta
            for item in venta["items"]:
                row = self.tbl.rowCount()
                self.tbl.insertRow(row)
                
                # Columnas no editables
                for col in [0, 1, 2, 3, 5, 6, 7]:
                    table_item = QTableWidgetItem()
                    table_item.setFlags(table_item.flags() & ~Qt.ItemIsEditable)
                    self.tbl.setItem(row, col, table_item)
                
                # Buscar el producto en la base de datos para obtener el código de barras real
                producto_id = item.get("producto_id")
                codigo_barra_real = ""
                
                if producto_id:
                    try:
                        # Buscar producto por ID usando el SKU como referencia
                        sku_guardado = item.get("sku", "")
                        if sku_guardado:
                            # Buscar producto por SKU para obtener código de barras actualizado
                            productos = self.db.buscar_producto_por_sku(sku_guardado)
                            if productos and len(productos) > 0:
                                codigo_barra_real = productos[0].get("codigo_barra", "")
                            else:
                                codigo_barra_real = item.get("codigo_barra") or sku_guardado
                        else:
                            codigo_barra_real = item.get("codigo_barra") or item.get("sku", "")
                    except:
                        # Si falla, usar el valor guardado como fallback
                        codigo_barra_real = item.get("codigo_barra") or item.get("sku", "")
                else:
                    # Si no hay producto_id, usar el valor guardado
                    codigo_barra_real = item.get("codigo_barra") or item.get("sku", "")
                
                self.tbl.item(row, 0).setText(codigo_barra_real)
                self.tbl.item(row, 1).setText(item["descripcion"])
                self.tbl.item(row, 2).setText(item["unidad"])
                self.tbl.item(row, 3).setText("Sí" if item["afecto_igv"] else "No")
                self.tbl.item(row, 5).setText(f"{float(item['igv_unit']):.2f}")
                self.tbl.item(row, 6).setText(f"{float(item['precio_unit']):.2f}")
                self.tbl.item(row, 7).setText(f"{float(item['importe']):.2f}")
                
                # Columna cantidad (editable)
                cantidad_item = QTableWidgetItem(f"{float(item['cantidad']):.2f}")
                cantidad_item.setFlags(cantidad_item.flags() | Qt.ItemIsEditable)
                self.tbl.setItem(row, 4, cantidad_item)
                
                # Guardar datos para recalcular
                self.tbl.item(row, 4).setData(Qt.UserRole, {
                    "producto_id": item["producto_id"],
                    "afecto_igv": item["afecto_igv"],
                    "valor_unit": float(item["valor_unit"]),
                    "igv_unit": float(item["igv_unit"]),
                    "precio_unit": float(item["precio_unit"])
                })
                
                self.items.append(item)
            
            # Recalcular totales
            self._recalc_totales()
            
            # Eliminar venta de la lista de suspendidas
            del data[codigo_hold]
            self._save_holds(data)
            
            # Mostrar mensaje de éxito
            fecha_suspension = venta.get("fecha_suspension", "N/A")
            QMessageBox.information(
                self, 
                "Venta Recuperada", 
                f"✅ Venta recuperada exitosamente\n\n"
                f"📋 Código: {codigo_hold}\n"
                f"💰 Total: S/. {venta['total']:.2f}\n"
                f"📦 Artículos: {len(venta['items'])}\n"
                f"📅 Suspendida: {fecha_suspension}\n\n"
                f"🔄 La venta está lista para continuar"
            )
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error al recuperar la venta: {str(e)}")

    # --- anulación ---
    def anular_venta(self):
        if not self.comprobante_id:
            QMessageBox.warning(self, "Aviso", "No hay comprobante en curso.")
            return

        user, ok1 = QInputDialog.getText(self, "Supervisor", "Usuario supervisor:")
        if not ok1 or not user:
            return
        pwd, ok2 = QInputDialog.getText(self, "Supervisor", "Clave:", echo=QLineEdit.Password)
        if not ok2:
            return

        if not self.db.validar_supervisor(user, pwd):
            QMessageBox.critical(self, "Acceso denegado", "Credenciales inválidas.")
            return

        motivo, ok3 = QInputDialog.getText(self, "Motivo", "Motivo de anulación:")
        if not ok3:
            return

        supervisor_id = self.db.get_usuario_id_por_login(user)
        try:
            self.db.anular_comprobante(self.comprobante_id, supervisor_id, motivo)
            QMessageBox.information(self, "Ok", "Comprobante anulado.")
            self.close()
        except Exception as e:
            QMessageBox.critical(self, "Error", f"No se pudo anular: {e}")

    # --- cancelar venta ---
    def cancelar_venta(self):
        """Cancela la venta actual limpiando todo y eliminando temporal.json"""
        try:
            if self.tbl.rowCount() == 0:
                QMessageBox.information(self, "Aviso", "No hay productos en la venta para cancelar.")
                return
            
            respuesta = QMessageBox.question(
                self, 
                "Cancelar Venta", 
                f"¿Está seguro de cancelar la venta con {self.tbl.rowCount()} productos?\n\n"
                f"Esta acción limpiará todo y no se podrá recuperar.",
                QMessageBox.StandardButton.Yes | QMessageBox.StandardButton.No,
                QMessageBox.StandardButton.No
            )
            
            if respuesta == QMessageBox.StandardButton.No:
                return
            
            # Limpiar tabla de productos
            self.tbl.setRowCount(0)
            
            # Limpiar lista de items
            self.items.clear()
            
            # Limpiar comprobante
            self.comprobante_id = None
            self.lbl_serie.setText("Serie: --")
            self.lbl_numero.setText("Número: --")
            self.lbl_tipo_documento.setText("Tipo: --")
            
            # Limpiar datos del cliente
            self.lbl_documento_cliente.setText("Documento: --")
            self.lbl_nombre_cliente.setText("Nombre: --")
            self.cliente_id_actual = None  # Limpiar ID del cliente
            
            # Limpiar totales
            self.lbl_subaf.setText("S/. 0.00")
            self.lbl_subin.setText("S/. 0.00")
            self.lbl_igv.setText("S/. 0.00")
            self.lbl_descuento_aplicado.setText("S/. 0.00")
            self.lbl_total.setText("S/. 0.00")
            self.lbl_cant_articulos.setText("0 Artículos")
            
            # Limpiar pagos mixtos
            self.lbl_efectivo_pago.setText("Efectivo: S/. 0.00")
            self.lbl_dolares_pago.setText("US Dólares: $ 0.00")
            self.lbl_vale_pago.setText("Tot. Vale De Alimentación: S/. 0.00")
            
            # Limpiar vista del cliente
            self.lbl_cliente_productos.setText("Productos:\n")
            self.lbl_total_dcto.setText("S/. 0.00")
            self.lbl_sub_total.setText("S/. 0.00")
            self.lbl_va_total.setText("S/. 0.00")
            self.lbl_orden_total.setText("S/. 0.00")
            
            # Eliminar archivo temporal.json si existe
            if os.path.exists("temporal.json"):
                try:
                    os.remove("temporal.json")
                    print("✅ Archivo temporal.json eliminado")
                except Exception as e:
                    print(f"⚠️ No se pudo eliminar temporal.json: {e}")
            
            # Ocultar botón de cambiar tipo
            if hasattr(self, 'btn_cambiar_tipo'):
                self.btn_cambiar_tipo.setVisible(False)
            
            # Limpiar entrada de código
            self.input_codigo.clear()
            self.input_codigo.setFocus()
            
            QMessageBox.information(self, "Venta Cancelada", "La venta ha sido cancelada y todo ha sido limpiado.")
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error al cancelar la venta: {str(e)}")

    # --- consultar precio ---
    def consultar_precio(self):
        """Permite consultar el precio de un producto sin agregarlo a la venta"""
        try:
            # Solicitar código/SKU del producto
            codigo, ok = QInputDialog.getText(
                self, 
                "Consultar Precio", 
                "Ingrese el código/SKU del producto:"
            )
            
            if not ok or not codigo.strip():
                return
            
            codigo = codigo.strip()
            
            # Buscar producto por código de barras primero
            producto = self._buscar_por_barra(codigo)
            if not producto:
                # Si no se encuentra por código de barras, buscar por SKU
                producto = self._buscar_por_sku(codigo)
            
            if not producto:
                QMessageBox.warning(self, "Producto no encontrado", 
                    f"No se encontró ningún producto con el código: {codigo}")
                return
            
            # Calcular valores según afecto IGV
            if producto.afecto_igv:
                valor_unit = round(producto.precio_venta / (1 + IGV_TASA), 2)
                igv_unit = round(valor_unit * IGV_TASA, 2)
                precio_unit = round(valor_unit + igv_unit, 2)
            else:
                valor_unit = round(producto.precio_venta, 2)
                igv_unit = 0.0
                precio_unit = valor_unit
            
            # Mostrar información del producto
            mensaje = f"""
📦 **INFORMACIÓN DEL PRODUCTO**

🆔 **SKU:** {producto.sku}
📝 **Descripción:** {producto.descripcion_larga}
🏷️ **Código de Barras:** {producto.codigo_barra}
📏 **Unidad de Medida:** {producto.unidad_medida}
💰 **Precio de Venta:** S/. {producto.precio_venta:.2f}

📊 **DESGLOSE:**
• Valor Unitario: S/. {valor_unit:.2f}
• IGV Unitario: S/. {igv_unit:.2f}
• Precio Final: S/. {precio_unit:.2f}
• Afecto IGV: {'Sí' if producto.afecto_igv else 'No'}

💡 **Nota:** Este producto no se ha agregado a la venta actual
            """
            
            QMessageBox.information(
                self, 
                f"Precio - {producto.sku}", 
                mensaje
            )
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error al consultar precio: {str(e)}")

    # --- cierres / reporte / reimprimir / gaveta ---
    def cierre_x(self):
        usuario_id = self.db.get_usuario_id_por_login(self.usuario)
        data = self.db.resumen_cierre_x_usuario(usuario_id)
        QMessageBox.information(
            self, "Cierre X",
            f"Total: {data['total']:.2f}\nEfectivo: {data['efectivo']:.2f}\nTarjeta: {data['tarjeta']:.2f}\nOtros: {data['otros']:.2f}\nComprobantes: {data['cant']}"
        )

    def cierre_z(self):
        term = (self.config or {}).get("terminal", "0")
        try:
            term_id = int(term)
        except ValueError:
            term_id = 0
        data = self.db.resumen_cierre_z_terminal(term_id)
        QMessageBox.information(
            self, "Cierre Z",
            f"Terminal {term}\nTotal: {data['total']:.2f}\nComprobantes: {data['cant']}"
        )

    def reporte_z(self):
        # Mismo dataset que Z; aquí solo mostramos (o podrías imprimir PDF).
        self.cierre_z()

    def reimprimir(self):
        if not self.comprobante_id:
            QMessageBox.warning(self, "Aviso", "No hay comprobante.")
            return
        data = self.db.reimprimir_cabecera(self.comprobante_id)
        if not data:
            QMessageBox.warning(self, "Aviso", "Cabecera no encontrada.")
            return
        # Integrar con tu stack de impresión aquí:
        QMessageBox.information(self, "Impresión", f"Reimpresión {data['tipo']}-{data['serie']}-{data['numero']}")

    def abrir_gaveta(self):
        try:
            # Placeholder: ajusta a tu entorno (python-escpos / lp / puertos)
            # os.system("echo -e '\x1B\x70\x00\x50\x50' > /dev/usb/lp0")
            QMessageBox.information(self, "Gaveta", "Comando de apertura enviado (placeholder).")
            
            # Solicitar conteo de fondo
            self.solicitar_conteo_fondo()
            
        except Exception as e:
            QMessageBox.warning(self, "Gaveta", f"No se pudo abrir: {e}")
    
    def solicitar_conteo_fondo(self):
        """Solicita el conteo de fondo al abrir la gaveta"""
        try:
            from PySide6.QtWidgets import QInputDialog
            
            # Solicitar fondo inicial si no existe
            if not hasattr(self, 'fondo_inicial') or self.fondo_inicial == 0:
                fondo_inicial, ok = QInputDialog.getDouble(
                    self,
                    "Fondo Inicial",
                    "Ingrese el fondo inicial de la caja:",
                    0.0, 0.0, 999999.99, 2
                )
                if ok:
                    self.fondo_inicial = fondo_inicial
                else:
                    return
            
            # Solicitar conteo actual
            fondo_actual, ok = QInputDialog.getDouble(
                self,
                "Conteo de Fondo",
                f"Fondo inicial: S/. {self.fondo_inicial:.2f}\n\n"
                "Cuente el dinero en la gaveta e ingrese el monto:",
                self.fondo_inicial, 0.0, 999999.99, 2
            )
            
            if ok:
                self.fondo_actual = fondo_actual
                diferencia = fondo_actual - self.fondo_inicial
                
                # Mostrar resultado del conteo
                mensaje = f"""
                RESULTADO DEL CONTEO:
                =====================
                Fondo Inicial: S/. {self.fondo_inicial:.2f}
                Fondo Actual:  S/. {self.fondo_actual:.2f}
                Diferencia:    S/. {diferencia:.2f}
                
                Estado: {'✓ CUADRA' if abs(diferencia) < 0.01 else '⚠ NO CUADRA'}
                """
                
                QMessageBox.information(self, "Conteo de Fondo", mensaje)
                
                # Generar ticket de conteo
                self.generar_ticket_conteo()
                
        except Exception as e:
            QMessageBox.warning(self, "Error", f"Error en conteo de fondo: {str(e)}")
    
    def generar_ticket_conteo(self):
        """Genera el ticket de conteo"""
        try:
            if not hasattr(self, 'fondo_actual'):
                return
                
            ticket = f"""
            ========================================
                        TICKET DE CONTEO
            ========================================
            Fecha: {datetime.now().strftime('%d/%m/%Y')}
            Hora: {datetime.now().strftime('%H:%M:%S')}
            Cajero: {self.usuario}
            Terminal: {self.config.get('terminal', 'N/A') if self.config else 'N/A'}
            
            FONDO INICIAL: S/. {self.fondo_inicial:.2f}
            FONDO ACTUAL:  S/. {self.fondo_actual:.2f}
            DIFERENCIA:    S/. {self.fondo_actual - self.fondo_inicial:.2f}
            
            ========================================
            """
            
            # Aquí iría el código para imprimir
            # Por ahora mostramos en un diálogo
            QMessageBox.information(self, "Ticket de Conteo", f"Ticket generado:\n\n{ticket}")
            
        except Exception as e:
            QMessageBox.warning(self, "Error", f"Error al generar ticket: {str(e)}")

    # --- finalizar / cobrar ---
    def finalizar_comprobante(self):
        if not self.comprobante_id or self.tbl.rowCount() == 0:
            QMessageBox.warning(self, "Aviso", "Nada para cobrar.")
            return

        # En este punto, en tu backend deberías:
        # 1) Insertar detalle_comprobante por cada fila
        # 2) (Trigger/función) recalcular totales en comprobantes
        # 3) Actualizar medio de pago con montos y fecha_pago
        try:
            # 1) Insertar detalle
            for r in range(self.tbl.rowCount()):
                afecto = (self.tbl.item(r, 3).text().strip().lower() == "sí")
                prod = self._buscar_por_sku(self.tbl.item(r, 0).text())
                if not prod:
                    # si no, intenta por descripción (no ideal, pero fallback)
                    prod = self._buscar_por_barra(self.tbl.item(r, 0).text())
                if not prod:
                    # en caso extremo, no insertes ese ítem
                    continue
                cantidad = float(self.tbl.item(r, 4).text())
                igv_unit = float(self.tbl.item(r, 5).text())
                precio_unit = float(self.tbl.item(r, 6).text())
                importe = float(self.tbl.item(r, 7).text())
                tipo_afect = "GRAVADO" if afecto else "INAFECTO"

                
                self._agregar_detalle_backend(
                    comprobante_id=self.comprobante_id,
                    producto_id=prod.id,
                    descripcion=prod.descripcion_larga,
                    cantidad=cantidad,
                    valor_unit=precio_unit - igv_unit,  # Calcular valor_unit
                    igv_unit=igv_unit,
                    precio_unit=precio_unit,
                    importe=importe,
                    tipo_afectacion=tipo_afect
                )

            # 2) Actualizar cabecera con pagos y fecha_pago
            total = float(self.lbl_total.text().split("S/. ")[-1])
            
            if self.pagos_mixtos_activos:
                # Usar sistema de pagos mixtos
                eff = self.efectivo_pagado
                tar = self.tarjeta_pagada
                otr = self.otros_pagados
                
                # Verificar que el pago esté completo
                if self.total_pagado < total:
                    QMessageBox.warning(self, "Pago Incompleto", 
                        f"El pago está incompleto. Falta S/. {total - self.total_pagado:.2f}\n"
                        "Use el botón 'PAGOS MIXTOS' para completar el pago.")
                    return
            else:
                # Si no hay pagos mixtos configurados, usar valores por defecto
                # El usuario debe usar el botón PAGOS para procesar el pago después
                eff = total  # Asumir pago en efectivo por defecto
                tar = 0.0
                otr = 0.0

            self._finalizar_cabecera_backend(
                comprobante_id=self.comprobante_id,
                efectivo=eff, tarjeta=tar, otros=otr, total=total
            )

            QMessageBox.information(self, "OK", "Venta finalizada.")
            
            # Preguntar si desea iniciar otra venta
            reply = QMessageBox.question(
                self,
                "Nueva Venta",
                "¿Desea iniciar otra venta?",
                QMessageBox.StandardButton.Yes | QMessageBox.StandardButton.No,
                QMessageBox.StandardButton.Yes
            )
            
            if reply == QMessageBox.StandardButton.Yes:
                # Limpiar la interfaz para nueva venta
                self.limpiar_venta_actual()
                # Preguntar tipo de documento
                self.seleccionar_tipo_documento()
            else:
                # Solo limpiar, no cerrar el POS
                self.limpiar_venta_actual()
        except Exception as e:
            QMessageBox.critical(self, "Error", f"No se pudo finalizar: {e}")


    def _agregar_detalle_backend(self, comprobante_id: int, producto_id: int, descripcion: str,
                                 cantidad: float, valor_unit: float, igv_unit: float,
                                 precio_unit: float, importe: float, tipo_afectacion: str):
        """
        Llama al método de backend y registra en la tabla venta
        """
        try:
            # 1. Insertar detalle del comprobante (si existe el método)
            if hasattr(self.db, "insertar_detalle_comprobante"):
                self.db.insertar_detalle_comprobante(
                    comprobante_id, producto_id, descripcion, cantidad,
                    precio_unit, igv_unit * cantidad, valor_unit * cantidad,
                    0, tipo_afectacion
                )
            
            # 2. Registrar en la tabla venta
            if hasattr(self.db, "insertar_venta"):
                # Obtener información del cliente y medio de pago
                cliente = self.lbl_nombre_cliente.text() if self.lbl_nombre_cliente.text() != "--" else ""
                dni = self.lbl_documento_cliente.text().split(": ")[-1] if ":" in self.lbl_documento_cliente.text() else ""
                medio_pago = "efectivo"  # Por defecto, se actualizará después
                
                # Obtener terminal_id y usuario_id
                terminal_id = getattr(self, 'terminal_id', 1)  # Por defecto 1 si no está definido
                usuario_id = getattr(self, 'usuario_actual', {}).get('id', 1) if hasattr(self, 'usuario_actual') else 1
                
                # Calcular campos adicionales
                igv_total = igv_unit * cantidad
                subtotal = valor_unit * cantidad
                descto = 0.0  # Por defecto, se puede implementar descuento por producto
                total = importe
                
                # Insertar en tabla venta
                self.db.insertar_venta(
                    comprobante_id=comprobante_id,
                    producto_id=producto_id,
                    nombre_producto=descripcion,
                    cantidad=cantidad,
                    precio_unitario=precio_unit,
                    igv=igv_total,
                    subtotal=subtotal,
                    descto=descto,
                    total=total,
                    cliente=cliente,
                    dni=dni,
                    medio_pago=medio_pago,
                    terminal_id=terminal_id,
                    usuario_id=usuario_id
                )
                
        except Exception as e:
            # No fallar si hay error, solo registrar
            pass

    def _finalizar_cabecera_backend(self, comprobante_id: int,
                                    efectivo: float, tarjeta: float, otros: float, total: float):
        """
        Llama al método de backend para finalizar la cabecera del comprobante
        """
        if hasattr(self.db, "finalizar_cabecera_comprobante"):
            self.db.finalizar_cabecera_comprobante(
                comprobante_id, efectivo, tarjeta, otros, total
            )
        else:
            # placeholder amable
            pass

    # === NUEVAS FUNCIONES PARA MENÚS DESPLEGABLES ===
    def toggle_menu(self, group_box, layout):
        """Alterna la visibilidad de los submenús de un grupo"""
        is_checked = group_box.isChecked()
        for i in range(layout.count()):
            widget = layout.itemAt(i).widget()
            if widget:
                widget.setVisible(is_checked)

    def procesar_cobro(self):
        """Procesa el cobro mostrando un diálogo para seleccionar medio de pago"""
        try:
            if not self.comprobante_id:
                QMessageBox.warning(self, "Aviso", "Inicie un comprobante primero.")
                return
                
            if self.tbl.rowCount() == 0:
                QMessageBox.warning(self, "Aviso", "Agregue productos a la venta primero.")
                return
            
            # Obtener total de la venta
            total_venta = float(self.lbl_total.text().split("S/. ")[-1])
            
            # Crear diálogo simple para seleccionar medio de pago
            from PySide6.QtWidgets import QDialog, QVBoxLayout, QHBoxLayout, QLabel, QPushButton, QComboBox
            
            dialog = QDialog(self)
            dialog.setWindowTitle("Seleccionar Medio de Pago")
            dialog.setModal(True)
            dialog.setFixedSize(400, 200)
            
            layout = QVBoxLayout(dialog)
            
            # Título
            titulo = QLabel("Seleccione el medio de pago:")
            titulo.setStyleSheet("font-size: 16px; font-weight: bold; color: #2c3e50; margin: 10px;")
            titulo.setAlignment(Qt.AlignmentFlag.AlignCenter)
            layout.addWidget(titulo)
            
            # Total a cobrar
            lbl_total = QLabel(f"Total a Cobrar: S/. {total_venta:.2f}")
            lbl_total.setStyleSheet("font-size: 14px; color: #e74c3c; font-weight: bold; margin: 10px;")
            lbl_total.setAlignment(Qt.AlignmentFlag.AlignCenter)
            layout.addWidget(lbl_total)
            
            # Combo box para medio de pago
            layout_medio = QHBoxLayout()
            layout_medio.addWidget(QLabel("Medio de Pago:"))
            cmb_medio = QComboBox()
            cmb_medio.addItems(["Efectivo", "Tarjeta", "Mixto"])
            cmb_medio.setStyleSheet("QComboBox { padding: 8px; font-size: 14px; border: 2px solid #bdc3c7; border-radius: 5px; }")
            layout_medio.addWidget(cmb_medio)
            layout.addLayout(layout_medio)
            
            # Botones
            botones_layout = QHBoxLayout()
            
            btn_cancelar = QPushButton("Cancelar")
            btn_cancelar.setStyleSheet("QPushButton { background: #95a5a6; color: white; padding: 10px; border-radius: 5px; font-weight: bold; }")
            btn_cancelar.clicked.connect(dialog.reject)
            botones_layout.addWidget(btn_cancelar)
            
            btn_aceptar = QPushButton("Cobrar")
            btn_aceptar.setStyleSheet("QPushButton { background: #27ae60; color: white; padding: 10px; border-radius: 5px; font-weight: bold; }")
            btn_aceptar.clicked.connect(dialog.accept)
            botones_layout.addWidget(btn_aceptar)
            
            layout.addLayout(botones_layout)
            
            # Mostrar diálogo
            if dialog.exec() == QDialog.DialogCode.Accepted:
                medio_seleccionado = cmb_medio.currentText()
                
                # Mostrar mensaje de éxito
                mensaje = f"COBRO PROCESADO EXITOSAMENTE\n\n"
                mensaje += f"Total Cobrado: S/. {total_venta:.2f}\n"
                mensaje += f"Medio de Pago: {medio_seleccionado}"
                
                QMessageBox.information(self, "Cobro Exitoso", mensaje)
                
                # Proceder con la finalización del comprobante
                self.finalizar_comprobante()
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error al procesar el cobro: {str(e)}")

    def abrir_sistema_pagos(self):
        """Abre directamente el sistema de pagos mixtos para completar la venta"""
        try:
            if not self.comprobante_id:
                QMessageBox.warning(self, "Aviso", "Inicie un comprobante primero.")
                return
                
            if self.tbl.rowCount() == 0:
                QMessageBox.warning(self, "Aviso", "Agregue productos a la venta primero.")
                return
            
            # Obtener total de la venta
            total_venta = float(self.lbl_total.text().split("S/. ")[-1])
            
            # Importar y abrir diálogo de pagos mixtos
            from ui.pagos_mixtos_dialog import PagosMixtosDialog
            
            dialog = PagosMixtosDialog(total_venta, self)
            if dialog.exec() == QDialog.DialogCode.Accepted:
                # Obtener resultado del pago
                resultado = dialog.get_resultado_pago()
                
                # Verificar que el pago esté completo
                if not resultado['pago_completo']:
                    QMessageBox.warning(self, "Pago Incompleto", 
                        f"El pago está incompleto. Falta S/. {total_venta - resultado['total_pagado']:.2f}")
                    return
                
                # Actualizar el medio de pago en el comprobante según los pagos realizados
                self.actualizar_medio_pago_comprobante(resultado)
                
                # Calcular vuelto
                vuelto = resultado['total_pagado'] - total_venta
                
                # Mostrar mensaje de éxito mejorado con vuelto
                mensaje = f"PAGO PROCESADO EXITOSAMENTE\n\n"
                mensaje += f"Total de la Venta: S/. {total_venta:.2f}\n"
                mensaje += f"Efectivo: S/. {resultado['efectivo']:.2f}\n"
                mensaje += f"Tarjeta: S/. {resultado['tarjeta']:.2f}\n"
                mensaje += f"Otros: S/. {resultado['otros']:.2f}\n"
                mensaje += f"Total Pagado: S/. {resultado['total_pagado']:.2f}\n"
                if vuelto > 0:
                    mensaje += f"Vuelto: S/. {vuelto:.2f}"
                else:
                    mensaje += "Vuelto: S/. 0.00"
                
                QMessageBox.information(self, "Pago Exitoso", mensaje)
                
                # Finalizar comprobante (ya incluye la pregunta de nueva venta)
                self.finalizar_comprobante()
                
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error al abrir sistema de pagos: {str(e)}")

    def actualizar_medio_pago_comprobante(self, resultado_pago):
        """Actualiza el medio de pago en el comprobante según los pagos realizados"""
        try:
            if not self.comprobante_id:
                return
            
            # Determinar el medio de pago principal usando valores válidos de la BD
            if resultado_pago['efectivo'] > 0 and resultado_pago['tarjeta'] > 0:
                medio_pago = "otros"  # Para pagos mixtos usar "otros"
            elif resultado_pago['efectivo'] > 0:
                medio_pago = "efectivo"
            elif resultado_pago['tarjeta'] > 0:
                medio_pago = "tarjeta"
            else:
                medio_pago = "otros"
            
            # Actualizar el comprobante en la BD con fecha de pago
            from datetime import datetime
            fecha_pago = datetime.now()
            
            with self.db.conn.cursor() as cur:
                cur.execute("""
                    UPDATE comprobantes 
                    SET medio_pago = %s, fecha_pago = %s, fecha_proceso = %s
                    WHERE id = %s
                """, (medio_pago, fecha_pago, fecha_pago, self.comprobante_id))
                
            self.db.conn.commit()
            
        except Exception as e:
            pass

    # === NUEVAS FUNCIONES PARA GESTIÓN DE CLIENTES ===
    
    def limpiar_venta_actual(self):
        """Limpia la interfaz para una nueva venta"""
        try:
            # Limpiar tabla
            self.tbl.setRowCount(0)
            
            # Limpiar comprobante
            self.comprobante_id = None
            self.lbl_serie.setText("Serie: --")
            self.lbl_numero.setText("Número: --")
            
            # Limpiar totales
            self._recalc_totales()
            
            # Limpiar información del cliente
            self.lbl_documento_cliente.setText("--")
            self.lbl_nombre_cliente.setText("--")
            self.lbl_tipo_documento.setText("Tipo: --")
            self.cliente_id_actual = None  # Limpiar ID del cliente
            
            # Ocultar botón de cambiar tipo de documento
            self.btn_cambiar_tipo.setVisible(False)
            

            
            # Limpiar campo de descuento
            self.input_descuento.clear()
            self.lbl_descuento_aplicado.setText("S/. 0.00")
            
            # Resetear pagos mixtos
            self.pagos_mixtos_activos = False
            self.total_pagado = 0.0
            self.efectivo_pagado = 0.0
            self.tarjeta_pagada = 0.0
            self.otros_pagados = 0.0
    
            
            # Limpiar entrada de código
            self.input_codigo.clear()
            self.input_codigo.setFocus()
            
        except Exception as e:
            QMessageBox.warning(self, "Error", f"Error al limpiar venta: {str(e)}")

    def seleccionar_tipo_documento(self):
        """Permite al usuario seleccionar el tipo de documento para la nueva venta"""
        try:
            # Crear diálogo personalizado
            dialog = QDialog(self)
            dialog.setWindowTitle("Seleccionar Tipo de Documento")
            dialog.setModal(True)
            dialog.setMinimumWidth(400)
            
            layout = QVBoxLayout(dialog)
            
            # Título
            titulo = QLabel("Seleccione el tipo de documento:")
            titulo.setStyleSheet("font-size: 16px; font-weight: bold; margin: 10px;")
            titulo.setAlignment(Qt.AlignmentFlag.AlignCenter)
            layout.addWidget(titulo)
            
            # Botones para cada tipo
            btn_boleta = QPushButton("📄 BOLETA")
            btn_boleta.setStyleSheet("QPushButton { background: #27ae60; color: white; padding: 15px; border-radius: 8px; font-weight: bold; font-size: 14px; margin: 5px; }")
            btn_boleta.clicked.connect(lambda: self.iniciar_con_tipo_documento("B", dialog))
            layout.addWidget(btn_boleta)
            
            btn_factura = QPushButton("🧾 FACTURA")
            btn_factura.setStyleSheet("QPushButton { background: #3498db; color: white; padding: 15px; border-radius: 8px; font-weight: bold; font-size: 14px; margin: 5px; }")
            btn_factura.clicked.connect(lambda: self.iniciar_con_tipo_documento("F", dialog))
            layout.addWidget(btn_factura)
            
            btn_sin_dni = QPushButton("👤 SIN DNI (Genérico)")
            btn_sin_dni.setStyleSheet("QPushButton { background: #95a5a6; color: white; padding: 15px; border-radius: 8px; font-weight: bold; font-size: 14px; margin: 5px; }")
            btn_sin_dni.clicked.connect(lambda: self.iniciar_con_tipo_documento("B", dialog, cliente_generico=True))
            layout.addWidget(btn_sin_dni)
            
            # Botón cancelar
            btn_cancelar = QPushButton("❌ CANCELAR")
            btn_cancelar.setStyleSheet("QPushButton { background: #e74c3c; color: white; padding: 10px; border-radius: 5px; font-weight: bold; }")
            btn_cancelar.clicked.connect(dialog.reject)
            layout.addWidget(btn_cancelar)
            
            dialog.exec()
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error al seleccionar tipo de documento: {str(e)}")

    def iniciar_con_tipo_documento(self, tipo_documento, dialog, cliente_generico=False):
        """Inicia el comprobante con el tipo seleccionado y solicita datos del cliente"""
        try:
            dialog.accept()
            
            # Si es cliente genérico, agregarlo directamente
            if cliente_generico:
                self.agregar_cliente_generico()
                return
            
            # Solicitar datos del cliente según el tipo
            if tipo_documento == "F":
                self.solicitar_datos_factura()
            else:
                self.solicitar_datos_boleta()
                
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error al iniciar con tipo de documento: {str(e)}")

    def solicitar_datos_factura(self):
        """Solicita RUC y busca automáticamente en BD, si no existe pide solo la razón social"""
        try:
            # Diálogo para RUC
            ruc, ok = QInputDialog.getText(
                self,
                "Datos de Factura",
                "Ingrese el RUC del cliente:",
                text=""
            )
            
            if not ok or not ruc.strip():
                return
                
            ruc = ruc.strip()
            
            # Validar formato de RUC (11 dígitos)
            if not ruc.isdigit() or len(ruc) != 11:
                QMessageBox.warning(self, "Error", "El RUC debe tener 11 dígitos numéricos.")
                return
            
            # Buscar automáticamente en la BD
            cliente_existente = self.buscar_cliente_por_ruc(ruc)
            
            if cliente_existente:
                # Cliente existe, usar sus datos automáticamente
                self.lbl_documento_cliente.setText(f"RUC: {cliente_existente['documento_numero']}")
                self.lbl_nombre_cliente.setText(cliente_existente['nombre'])
                self.lbl_tipo_documento.setText("Tipo: FACTURA")
                self.cliente_id_actual = cliente_existente['id']  # Guardar el ID del cliente
                
                QMessageBox.information(self, "Cliente Encontrado", 
                    f"Cliente encontrado en la base de datos:\n"
                    f"RUC: {cliente_existente['documento_numero']}\n"
                    f"Razón Social: {cliente_existente['nombre']}\n\n"
                    f"Puede proceder a iniciar el comprobante.")
                
                # Iniciar comprobante automáticamente
                self.iniciar_comprobante()
                
            else:
                # Cliente no existe, pedir solo la razón social
                razon_social, ok = QInputDialog.getText(
                    self,
                    "Datos de Factura",
                    "Cliente no encontrado. Ingrese la Razón Social del cliente:",
                    text=""
                )
                
                if not ok or not razon_social.strip():
                    return
                    
                razon_social = razon_social.strip()
                
                # Guardar nuevo cliente en BD
                self.guardar_cliente_factura(ruc, razon_social)
                
                # Actualizar interfaz
                self.lbl_documento_cliente.setText(f"RUC: {ruc}")
                self.lbl_nombre_cliente.setText(razon_social)
                self.lbl_tipo_documento.setText("Tipo: FACTURA")
                
                QMessageBox.information(self, "Cliente Guardado", 
                    f"Nuevo cliente guardado:\n"
                    f"RUC: {ruc}\n"
                    f"Razón Social: {razon_social}\n\n"
                    f"Puede proceder a iniciar el comprobante.")
                
                # Iniciar comprobante automáticamente
                self.iniciar_comprobante()
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error al solicitar datos de factura: {str(e)}")

    def solicitar_datos_boleta(self):
        """Solicita DNI y busca automáticamente en BD, si no existe pide solo el nombre"""
        try:
            # Diálogo para DNI
            dni, ok = QInputDialog.getText(
                self,
                "Datos de Boleta",
                "Ingrese el DNI del cliente:",
                text=""
            )
            
            if not ok or not dni.strip():
                return
                
            dni = dni.strip()
            
            # Validar formato de DNI (8 dígitos)
            if not dni.isdigit() or len(dni) != 8:
                QMessageBox.warning(self, "Error", "El DNI debe tener 8 dígitos numéricos.")
                return
            
            # Buscar automáticamente en la BD
            cliente_existente = self.buscar_cliente_por_dni(dni)
            
            if cliente_existente:
                # Cliente existe, usar sus datos automáticamente
                self.lbl_documento_cliente.setText(f"DNI: {cliente_existente['documento_numero']}")
                self.lbl_nombre_cliente.setText(cliente_existente['nombre'])
                self.lbl_tipo_documento.setText("Tipo: BOLETA")
                self.cliente_id_actual = cliente_existente['id']  # Guardar el ID del cliente
                
                QMessageBox.information(self, "Cliente Encontrado", 
                    f"Cliente encontrado en la base de datos:\n"
                    f"DNI: {cliente_existente['documento_numero']}\n"
                    f"Nombre: {cliente_existente['nombre']}\n\n"
                    f"Puede proceder a iniciar el comprobante.")
                
                # Iniciar comprobante automáticamente
                self.iniciar_comprobante()
                
            else:
                # Cliente no existe, pedir solo el nombre
                nombre, ok = QInputDialog.getText(
                    self,
                    "Datos de Boleta",
                    "Cliente no encontrado. Ingrese el nombre del cliente:",
                    text=""
                )
                
                if not ok or not nombre.strip():
                    return
                    
                nombre = nombre.strip()
                
                # Guardar nuevo cliente en BD
                self.guardar_cliente_boleta(dni, nombre)
                
                # Actualizar interfaz
                self.lbl_documento_cliente.setText(f"DNI: {dni}")
                self.lbl_nombre_cliente.setText(nombre)
                self.lbl_tipo_documento.setText("Tipo: BOLETA")
                
                QMessageBox.information(self, "Cliente Guardado", 
                    f"Nuevo cliente guardado:\n"
                    f"DNI: {dni}\n"
                    f"Nombre: {nombre}\n\n"
                    f"Puede proceder a iniciar el comprobante.")
                
                # Iniciar comprobante automáticamente
                self.iniciar_comprobante()
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error al solicitar datos de boleta: {str(e)}")

    def guardar_cliente_factura(self, ruc, razon_social):
        """Guarda o actualiza cliente de factura en la BD"""
        try:
            # Verificar si ya existe
            with self.db.conn.cursor() as cur:
                cur.execute("""
                    SELECT id FROM clientes 
                    WHERE documento_numero = %s AND documento_tipo = 'RUC'
                """, (ruc,))
                
                if cur.fetchone():
                    # Actualizar si existe
                    cur.execute("""
                        UPDATE clientes 
                        SET nombre = %s
                        WHERE documento_numero = %s AND documento_tipo = 'RUC'
                    """, (razon_social, ruc))
                else:
                    # Insertar si no existe
                    cur.execute("""
                        INSERT INTO clientes (documento_tipo, documento_numero, nombre)
                        VALUES ('RUC', %s, %s)
                    """, (ruc, razon_social))
                
                self.db.conn.commit()
                
        except Exception as e:
            QMessageBox.warning(self, "Advertencia", f"No se pudo guardar cliente en BD: {str(e)}")

    def guardar_cliente_boleta(self, dni, nombre):
        """Guarda o actualiza cliente de boleta en la BD"""
        try:
            # Verificar si ya existe
            with self.db.conn.cursor() as cur:
                cur.execute("""
                    SELECT id FROM clientes 
                    WHERE documento_numero = %s AND documento_tipo = 'DNI'
                """, (dni,))
                
                if cur.fetchone():
                    # Actualizar si existe
                    cur.execute("""
                        UPDATE clientes 
                        SET nombre = %s
                        WHERE documento_numero = %s AND documento_tipo = 'DNI'
                    """, (nombre, dni))
                else:
                    # Insertar si no existe
                    cur.execute("""
                        INSERT INTO clientes (documento_tipo, documento_numero, nombre)
                        VALUES ('DNI', %s, %s)
                    """, (dni, nombre))
                
                self.db.conn.commit()
                
        except Exception as e:
            QMessageBox.warning(self, "Advertencia", f"No se pudo guardar cliente en BD: {str(e)}")

    def ingresar_datos_cliente_manual(self):
        """Permite al usuario ingresar datos del cliente manualmente"""
        try:
            # Crear diálogo para seleccionar tipo de cliente
            dialog = QDialog(self)
            dialog.setWindowTitle("Ingresar Datos del Cliente")
            dialog.setModal(True)
            dialog.setMinimumWidth(400)
            
            layout = QVBoxLayout(dialog)
            
            # Título
            titulo = QLabel("Seleccione el tipo de cliente:")
            titulo.setStyleSheet("font-size: 16px; font-weight: bold; margin: 10px;")
            titulo.setAlignment(Qt.AlignmentFlag.AlignCenter)
            layout.addWidget(titulo)
            
            # Botones para cada tipo
            btn_boleta = QPushButton("📄 BOLETA (DNI)")
            btn_boleta.setStyleSheet("QPushButton { background: #27ae60; color: white; padding: 15px; border-radius: 8px; font-weight: bold; font-size: 14px; margin: 5px; }")
            btn_boleta.clicked.connect(lambda: self.ingresar_datos_cliente_tipo("BOLETA", dialog))
            layout.addWidget(btn_boleta)
            
            btn_factura = QPushButton("🧾 FACTURA (RUC)")
            btn_factura.setStyleSheet("QPushButton { background: #3498db; color: white; padding: 15px; border-radius: 8px; font-weight: bold; font-size: 14px; margin: 5px; }")
            btn_factura.clicked.connect(lambda: self.ingresar_datos_cliente_tipo("FACTURA", dialog))
            layout.addWidget(btn_factura)
            
            btn_sin_dni = QPushButton("👤 SIN DNI (Genérico)")
            btn_sin_dni.setStyleSheet("QPushButton { background: #95a5a6; color: white; padding: 15px; border-radius: 8px; font-weight: bold; font-size: 14px; margin: 5px; }")
            btn_sin_dni.clicked.connect(lambda: self.ingresar_datos_cliente_tipo("SIN_DNI", dialog))
            layout.addWidget(btn_sin_dni)
            
            # Botón cancelar
            btn_cancelar = QPushButton("❌ CANCELAR")
            btn_cancelar.setStyleSheet("QPushButton { background: #e74c3c; color: white; padding: 10px; border-radius: 5px; font-weight: bold; }")
            btn_cancelar.clicked.connect(dialog.reject)
            layout.addWidget(btn_cancelar)
            
            dialog.exec()
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error al ingresar datos del cliente: {str(e)}")

    def ingresar_datos_cliente_tipo(self, tipo_cliente, dialog):
        """Procesa la selección del tipo de cliente"""
        try:
            dialog.accept()
            
            if tipo_cliente == "FACTURA":
                self.solicitar_datos_factura()
            elif tipo_cliente == "SIN_DNI":
                self.agregar_cliente_generico()
            else:
                self.solicitar_datos_boleta()
                
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error al procesar tipo de cliente: {str(e)}")

    def aplicar_descuento(self):
        """Aplica el descuento ingresado y recalcula totales"""
        try:
            # Obtener valor del descuento
            descuento_texto = self.input_descuento.text().strip()
            if not descuento_texto:
                descuento = 0.0
            else:
                descuento = float(descuento_texto)
            
            # Obtener total sin descuento
            total_sin_descuento = 0.0
            for r in range(self.tbl.rowCount()):
                importe = float(self.tbl.item(r, 8).text())
                total_sin_descuento += importe
            
            # Calcular total con descuento
            total_con_descuento = max(0.0, total_sin_descuento - descuento)
            
            # Actualizar labels
            self.lbl_total.setText(f"S/. {total_con_descuento:.2f}")
            
            # Actualizar label del descuento aplicado
            self.lbl_descuento_aplicado.setText(f"S/. {descuento:.2f}")
            
            
            
        except ValueError:
            # Si no es un número válido, limpiar el campo
            self.input_descuento.clear()
        except Exception as e:
            QMessageBox.warning(self, "Error", f"Error al aplicar descuento: {str(e)}")

    def agregar_cliente_generico(self):
        """Agrega un cliente genérico sin DNI"""
        try:
            # Datos genéricos
            dni_generico = "22222222"
            nombre_generico = "Cliente Genérico"
            
            # Guardar en BD si no existe
            self.guardar_cliente_boleta(dni_generico, nombre_generico)
            
            # Actualizar interfaz
            self.lbl_documento_cliente.setText(f"DNI: {dni_generico}")
            self.lbl_nombre_cliente.setText(nombre_generico)
            
            # Mostrar tipo de documento en la interfaz
            self.lbl_tipo_documento.setText("Tipo: BOLETA")
            
            # Mostrar mensaje de confirmación
            QMessageBox.information(self, "Cliente Genérico", 
                f"Cliente genérico agregado:\n"
                f"DNI: {dni_generico}\n"
                f"Nombre: {nombre_generico}\n\n"
                f"Puede proceder a iniciar el comprobante.")
            
            self.iniciar_comprobante()
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error al agregar cliente genérico: {str(e)}")

    def cambiar_tipo_documento(self):
        """Permite cambiar el tipo de documento después de iniciar el comprobante"""
        try:
            if not self.comprobante_id:
                QMessageBox.warning(self, "Aviso", "Debe iniciar un comprobante primero.")
                return
            
          
            
            # Crear diálogo para seleccionar nuevo tipo
            from PySide6.QtWidgets import QDialog, QVBoxLayout, QHBoxLayout, QLabel, QPushButton, QComboBox
            
            dialog = QDialog(self)
            dialog.setWindowTitle("Cambiar Tipo de Documento")
            dialog.setModal(True)
            dialog.setFixedSize(450, 250)
            
            layout = QVBoxLayout(dialog)
            
            # Título
            titulo = QLabel("Seleccione el nuevo tipo de documento:")
            titulo.setStyleSheet("font-size: 16px; font-weight: bold; color: #2c3e50; margin: 10px;")
            titulo.setAlignment(Qt.AlignmentFlag.AlignCenter)
            layout.addWidget(titulo)
            
            # Información actual
            info_actual = QLabel(f"Tipo actual: {self.lbl_tipo_documento.text()}")
            info_actual.setStyleSheet("font-size: 12px; color: #7f8c8d; margin: 5px;")
            info_actual.setAlignment(Qt.AlignmentFlag.AlignCenter)
            layout.addWidget(info_actual)
            
            # Combo box para nuevo tipo
            layout_tipo = QHBoxLayout()
            layout_tipo.addWidget(QLabel("Nuevo Tipo:"))
            cmb_tipo = QComboBox()
            cmb_tipo.addItems(["BOLETA", "FACTURA"])
            cmb_tipo.setStyleSheet("QComboBox { padding: 8px; font-size: 14px; border: 2px solid #bdc3c7; border-radius: 5px; }")
            layout_tipo.addWidget(cmb_tipo)
            layout.addLayout(layout_tipo)
            
            # Botones
            botones_layout = QHBoxLayout()
            
            btn_cancelar = QPushButton("Cancelar")
            btn_cancelar.setStyleSheet("QPushButton { background: #95a5a6; color: white; padding: 10px; border-radius: 5px; font-weight: bold; }")
            btn_cancelar.clicked.connect(dialog.reject)
            botones_layout.addWidget(btn_cancelar)
            
            btn_aceptar = QPushButton("Cambiar")
            btn_aceptar.setStyleSheet("QPushButton { background: #f39c12; color: white; padding: 10px; border-radius: 5px; font-weight: bold; }")
            btn_aceptar.clicked.connect(dialog.accept)
            botones_layout.addWidget(btn_aceptar)
            
            layout.addLayout(botones_layout)
            
            # Mostrar diálogo
            if dialog.exec() == QDialog.DialogCode.Accepted:
                nuevo_tipo = cmb_tipo.currentText()
                
                # Si se cambia a FACTURA, solicitar datos de RUC
                if nuevo_tipo == "FACTURA":
                    self.solicitar_datos_factura_cambio()
                else:
                    # Si se cambia a BOLETA, solicitar DNI
                    self.solicitar_datos_boleta_cambio()
                
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error al cambiar tipo de documento: {str(e)}")

    def solicitar_datos_factura_cambio(self):
        """Solicita datos de factura para cambiar tipo de documento"""
        try:
            # Solicitar RUC
            ruc, ok = QInputDialog.getText(self, "Datos de Factura", "Ingrese el RUC del cliente:")
            if not ok or not ruc.strip():
                return
            
            ruc = ruc.strip()
            
            # Validar formato de RUC (11 dígitos)
            if not ruc.isdigit() or len(ruc) != 11:
                QMessageBox.warning(self, "Error", "El RUC debe tener 11 dígitos numéricos.")
                return
            
            # Buscar automáticamente en la BD
            cliente_existente = self.buscar_cliente_por_ruc(ruc)
            
            if cliente_existente:
                # Cliente existe, usar sus datos automáticamente
                self.lbl_documento_cliente.setText(f"RUC: {cliente_existente['documento_numero']}")
                self.lbl_nombre_cliente.setText(cliente_existente['nombre'])
                self.lbl_tipo_documento.setText("Tipo: FACTURA")
                self.cliente_id_actual = cliente_existente['id']  # Guardar el ID del cliente
                
                # Actualizar el comprobante en la BD
                self.actualizar_comprobante_a_factura()
                
                # Recalcular la venta completa con el nuevo tipo
                self._recalcular_venta_completa()
                
            else:
                # Cliente no existe, pedir solo la razón social
                razon_social, ok = QInputDialog.getText(
                    self,
                    "Datos de Factura",
                    "Cliente no encontrado. Ingrese la Razón Social del cliente:",
                    text=""
                )
                
                if not ok or not razon_social.strip():
                    return
                    
                razon_social = razon_social.strip()
                
                # Guardar nuevo cliente en BD
                self.guardar_cliente_factura(ruc, razon_social)
                
                
                # Actualizar interfaz
                self.lbl_documento_cliente.setText(f"RUC: {ruc}")
                self.lbl_nombre_cliente.setText(razon_social)
                self.lbl_tipo_documento.setText("Tipo: FACTURA")
                
                # Actualizar el comprobante en la BD
                self.actualizar_comprobante_a_factura()
                
                # Recalcular la venta completa con el nuevo tipo
                self._recalcular_venta_completa()
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error al solicitar datos de factura: {str(e)}")

    def solicitar_datos_boleta_cambio(self):
        """Solicita datos de boleta para cambiar tipo de documento"""
        try:
            # Solicitar DNI
            dni, ok = QInputDialog.getText(self, "Datos de Boleta", "Ingrese el DNI del cliente:")
            if not ok or not dni.strip():
                return
            
            dni = dni.strip()
            
            # Validar formato de DNI (8 dígitos)
            if not dni.isdigit() or len(dni) != 8:
                QMessageBox.warning(self, "Error", "El DNI debe tener 8 dígitos numéricos.")
                return
            
            # Buscar automáticamente en la BD
            cliente_existente = self.buscar_cliente_por_dni(dni)
            
            if cliente_existente:
                # Cliente existe, usar sus datos automáticamente
                self.lbl_documento_cliente.setText(f"DNI: {cliente_existente['documento_numero']}")
                self.lbl_nombre_cliente.setText(cliente_existente['nombre'])
                self.lbl_tipo_documento.setText("Tipo: BOLETA")
                self.cliente_id_actual = cliente_existente['id']  # Guardar el ID del cliente
                
                # Actualizar el comprobante en la BD
                self.actualizar_comprobante_a_boleta()
                
                # Recalcular la venta completa con el nuevo tipo
                self._recalcular_venta_completa()
                
            else:
                # Cliente no existe, pedir solo el nombre
                nombre, ok = QInputDialog.getText(
                    self,
                    "Datos de Boleta",
                    "Cliente no encontrado. Ingrese el nombre del cliente:",
                    text=""
                )
                
                if not ok or not nombre.strip():
                    return
                    
                nombre = nombre.strip()
                
                # Guardar nuevo cliente en BD
                self.guardar_cliente_boleta(dni, nombre)
                
                # Actualizar interfaz
                self.lbl_documento_cliente.setText(f"DNI: {dni}")
                self.lbl_nombre_cliente.setText(nombre)
                self.lbl_tipo_documento.setText("Tipo: BOLETA")
                
                # Actualizar el comprobante en la BD
                self.actualizar_comprobante_a_boleta()
                
                # Recalcular la venta completa con el nuevo tipo
                self._recalcular_venta_completa()
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error al solicitar datos de boleta: {str(e)}")

    def buscar_cliente_por_ruc(self, ruc):
        """Busca un cliente por RUC en la base de datos"""
        try:
            with self.db.conn.cursor() as cur:
                cur.execute("""
                    SELECT id, documento_numero, nombre, direccion, telefono, email
                    FROM clientes 
                    WHERE documento_tipo = 'RUC' AND documento_numero = %s
                """, (ruc,))
                resultado = cur.fetchone()
                
                if resultado:
                    return {
                        'id': resultado[0],
                        'documento_numero': resultado[1],
                        'nombre': resultado[2],
                        'direccion': resultado[3],
                        'telefono': resultado[4],
                        'email': resultado[5]
                    }
                return None
                
        except Exception as e:
            return None

    def buscar_cliente_por_dni(self, dni):
        """Busca un cliente por DNI en la base de datos"""
        try:
            with self.db.conn.cursor() as cur:
                cur.execute("""
                    SELECT id, documento_numero, nombre, direccion, telefono, email
                    FROM clientes 
                    WHERE documento_tipo = 'DNI' AND documento_numero = %s
                """, (dni,))
                resultado = cur.fetchone()
                
                if resultado:
                    return {
                        'id': resultado[0],
                        'documento_numero': resultado[1],
                        'nombre': resultado[2],
                        'direccion': resultado[3],
                        'telefono': resultado[4],
                        'email': resultado[5]
                    }
                return None
                
        except Exception as e:
            return None

    def actualizar_comprobante_a_factura(self):
        """Actualiza el comprobante en la BD para cambiar a factura"""
        try:
            if not self.comprobante_id:
                return
            
            # Obtener el RUC del cliente
            documento_cliente = self.lbl_documento_cliente.text()
            if not documento_cliente.startswith("RUC: "):
                return
            
            ruc = documento_cliente.replace("RUC: ", "")
            
            # Obtener información de la terminal para facturas
            terminal_nombre = (self.config or {}).get("terminal", "").strip()
            if not terminal_nombre:
                QMessageBox.critical(self, "Error", "No se pudo obtener el nombre de la terminal.")
                return
            
            # Buscar la terminal para facturas
            with self.db.conn.cursor() as cur:
                cur.execute("""
                    SELECT id_terminal, serie, tipo_comprobante, activo 
                    FROM terminal 
                    WHERE nombre = %s AND tipo_comprobante = 'F' AND activo = true
                """, (terminal_nombre,))
                terminal_info = cur.fetchone()
                
                if not terminal_info:
                    QMessageBox.critical(self, "Error", f"No se encontró terminal activa para facturas: {terminal_nombre}")
                    return
                
                # Obtener el siguiente número de factura (corregido para manejar tipo text)
                cur.execute("""
                    SELECT COALESCE(MAX(CAST(numero AS INTEGER)), 0) + 1
                    FROM comprobantes 
                    WHERE tipo = 'F' AND serie = %s
                """, (terminal_info[1],))
                siguiente_numero = cur.fetchone()[0]
            
            # Actualizar el comprobante con nueva serie y número (en transacción separada)
            with self.db.conn.cursor() as cur:
                cur.execute("""
                    UPDATE comprobantes 
                    SET tipo = 'F', medio_pago = 'efectivo', serie = %s, numero = %s
                    WHERE id = %s
                """, (terminal_info[1], str(siguiente_numero), self.comprobante_id))
                
            self.db.conn.commit()
            
            # Actualizar la interfaz con la nueva serie y número
            self.lbl_serie.setText(f"Serie: {terminal_info[1]}")
            self.lbl_numero.setText(f"Número: {siguiente_numero:06d}")
            
            QMessageBox.information(self, "Comprobante Actualizado", 
                f"El comprobante se ha actualizado a FACTURA.\n"
                f"Serie: {terminal_info[1]}, Número: {siguiente_numero:06d}\n"
                f"RUC: {ruc}")
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error al actualizar comprobante: {str(e)}")

    def actualizar_comprobante_a_boleta(self):
        """Actualiza el comprobante en la BD para cambiar a boleta"""
        try:
            if not self.comprobante_id:
                return
            
            # Obtener el DNI del cliente
            documento_cliente = self.lbl_documento_cliente.text()
            if not documento_cliente.startswith("DNI: "):
                return
            
            dni = documento_cliente.replace("DNI: ", "")
            
            # Obtener información de la terminal para boletas
            terminal_nombre = (self.config or {}).get("terminal", "").strip()
            if not terminal_nombre:
                QMessageBox.critical(self, "Error", "No se pudo obtener el nombre de la terminal.")
                return
            
            # Buscar la terminal para boletas
            with self.db.conn.cursor() as cur:
                cur.execute("""
                    SELECT id_terminal, serie, tipo_comprobante, activo 
                    FROM terminal 
                    WHERE nombre = %s AND tipo_comprobante = 'B' AND activo = true
                """, (terminal_nombre,))
                terminal_info = cur.fetchone()
                
                if not terminal_info:
                    QMessageBox.critical(self, "Error", f"No se encontró terminal activa para boletas: {terminal_nombre}")
                    return
                
                # Obtener el siguiente número de boleta (corregido para manejar tipo text)
                cur.execute("""
                    SELECT COALESCE(MAX(CAST(numero AS INTEGER)), 0) + 1
                    FROM comprobantes 
                    WHERE tipo = 'B' AND serie = %s
                """, (terminal_info[1],))
                siguiente_numero = cur.fetchone()[0]
            
            # Actualizar el comprobante con nueva serie y número (en transacción separada)
            with self.db.conn.cursor() as cur:
                cur.execute("""
                    UPDATE comprobantes 
                    SET tipo = 'B', medio_pago = 'efectivo', serie = %s, numero = %s
                    WHERE id = %s
                """, (terminal_info[1], str(siguiente_numero), self.comprobante_id))
                
            self.db.conn.commit()
            
            # Actualizar la interfaz con la nueva serie y número
            self.lbl_serie.setText(f"Serie: {terminal_info[1]}")
            self.lbl_numero.setText(f"Número: {siguiente_numero:06d}")
            
            QMessageBox.information(self, "Comprobante Actualizado", 
                f"El comprobante se ha actualizado a BOLETA.\n"
                f"Serie: {terminal_info[1]}, Número: {siguiente_numero:06d}\n"
                f"DNI: {dni}")
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error al actualizar comprobante: {str(e)}")

    # === FUNCIONES DE RECÁLCULO DE VENTA ===
    def _recalcular_venta_completa(self):
        """Recalcula toda la venta después de cambiar el tipo de documento"""
        try:
            if self.tbl.rowCount() == 0:
                return
            
            # Obtener el tipo de documento actual
            tipo_actual = self.lbl_tipo_documento.text()
            
            # Recalcular cada producto en la tabla
            for row in range(self.tbl.rowCount()):
                # Obtener datos del producto
                sku = self.tbl.item(row, 0).text()
                cantidad = float(self.tbl.item(row, 4).text())
                
                # Buscar el producto actualizado
                producto = self._buscar_por_sku(sku)
                if not producto:
                    continue
                
                # Recalcular valores según el tipo de documento
                if producto.afecto_igv:
                    valor_unit = round(producto.precio_venta / (1 + IGV_TASA), 2)
                    igv_unit = round(valor_unit * IGV_TASA, 2)
                    precio_unit = round(valor_unit + igv_unit, 2)
                else:
                    valor_unit = round(producto.precio_venta, 2)
                    igv_unit = 0.0
                    precio_unit = valor_unit
                
                importe = round(precio_unit * cantidad, 2)
                
                # Actualizar valores en la tabla
                self.tbl.item(row, 5).setText(f"{igv_unit:.2f}")
                self.tbl.item(row, 6).setText(f"{precio_unit:.2f}")
                self.tbl.item(row, 7).setText(f"{importe:.2f}")
                
                # Actualizar datos guardados en UserRole
                self.tbl.item(row, 4).setData(Qt.UserRole, {
                    "producto_id": producto.id,
                    "afecto_igv": producto.afecto_igv,
                    "valor_unit": valor_unit,
                    "igv_unit": igv_unit,
                    "precio_unit": precio_unit
                })
                
                # Actualizar lista de items
                if row < len(self.items):
                    self.items[row].update({
                        "valor_unit": valor_unit,
                        "igv_unit": igv_unit,
                        "precio_unit": precio_unit,
                        "importe": importe
                    })
            
            # Recalcular totales
            self._recalc_totales()
            
            QMessageBox.information(
                self, 
                "Tipo de Documento Cambiado", 
                f"Tipo cambiado a: {tipo_actual}"
            )
            
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error al recalcular la venta: {str(e)}")

    # === FUNCIONES DEL TECLADO NUMÉRICO ===
    def tecla_numero_presionada(self, tecla):
        """Maneja la presión de teclas numéricas"""
        if tecla == '.' and '.' in self.entrada_actual:
            return  # No permitir múltiples puntos decimales
        
        self.entrada_actual += tecla
        self.input_codigo.setText(self.entrada_actual)
    
    def limpiar_entrada(self):
        """Limpia la entrada actual"""
        self.entrada_actual = ""
        self.input_codigo.clear()
        self.input_codigo.setFocus()
    
    def multiplicar_entrada(self):
        """Multiplica la entrada actual por 2"""
        try:
            if self.entrada_actual:
                valor = float(self.entrada_actual)
                self.entrada_actual = str(valor * 2)
                self.input_codigo.setText(self.entrada_actual)
        except ValueError:
            pass
    
    def backspace_entrada(self):
        """Elimina el último carácter de la entrada"""
        if self.entrada_actual:
            self.entrada_actual = self.entrada_actual[:-1]
            self.input_codigo.setText(self.entrada_actual)
    
    def enter_entrada(self):
        """Confirma la entrada y la envía al campo de código"""
        if self.entrada_actual:
            self.input_codigo.setText(self.entrada_actual)
            # Simular presionar Enter en el campo de código
            self.input_codigo.returnPressed.emit()
            self.limpiar_entrada()

    # === FUNCIONES DE GUARDADO AUTOMÁTICO ===
    def _guardar_venta_temporal(self):
        """Guarda la venta actual en temporal.json si hay productos en la tabla"""
        try:
            if self.tbl.rowCount() == 0:
                return  # No hay productos para guardar
            
            # Preparar datos de la venta temporal
            venta_temporal = {
                "fecha_guardado": datetime.now().isoformat(),
                "usuario": self.usuario,
                "comprobante_id": self.comprobante_id,
                "items": self.items.copy(),
                "total": float(self.lbl_total.text().replace("S/. ", "").replace(",", "")),
                "cliente_documento": self.lbl_documento_cliente.text(),
                "cliente_nombre": self.lbl_nombre_cliente.text(),
                "tipo_documento": self.lbl_tipo_documento.text()
            }
            
            # Guardar en temporal.json
            with open("temporal.json", "w", encoding="utf-8") as f:
                json.dump(venta_temporal, f, ensure_ascii=False, indent=2)
                
            print(f"✅ Venta temporal guardada: {len(self.items)} productos")
            
        except Exception as e:
            print(f"❌ Error al guardar venta temporal: {e}")
    
    def _recuperar_venta_temporal(self):
        """Recupera la venta temporal si existe"""
        try:
            if not os.path.exists("temporal.json"):
                return False
            
            with open("temporal.json", "r", encoding="utf-8") as f:
                venta_temporal = json.load(f)
            
            # Verificar que la venta temporal sea del usuario actual
            if venta_temporal.get("usuario") != self.usuario:
                print("⚠️ Venta temporal de otro usuario, no se recupera")
                return False
            
            # Verificar que haya productos
            if not venta_temporal.get("items"):
                print("⚠️ Venta temporal sin productos")
                return False
            
            # Preguntar si desea recuperar la venta
            respuesta = QMessageBox.question(
                self, 
                "Venta Temporal Encontrada", 
                f"Se encontró una venta temporal con {len(venta_temporal['items'])} productos.\n\n"
                f"¿Desea recuperarla?",
                QMessageBox.StandardButton.Yes | QMessageBox.StandardButton.No
            )
            
            if respuesta == QMessageBox.StandardButton.No:
                # Eliminar archivo temporal si no se quiere recuperar
                os.remove("temporal.json")
                return False
            
            # Recuperar la venta
            self.items = venta_temporal["items"]
            
            # Limpiar tabla actual
            self.tbl.setRowCount(0)
            
            # Restaurar productos en la tabla
            for item in self.items:
                row = self.tbl.rowCount()
                self.tbl.insertRow(row)
                
                # Crear items no editables
                for col in [0, 1, 2, 3, 5, 6, 7]:
                    table_item = QTableWidgetItem()
                    table_item.setFlags(table_item.flags() & ~Qt.ItemIsEditable)
                    self.tbl.setItem(row, col, table_item)
                
                # Asignar valores (compatibilidad con estructura antigua)
                codigo_barra = item.get("codigo_barra") or item.get("sku", "")
                self.tbl.item(row, 0).setText(codigo_barra)
                self.tbl.item(row, 1).setText(item["descripcion"])
                self.tbl.item(row, 2).setText(item["unidad"])
                self.tbl.item(row, 3).setText("Sí" if item["afecto_igv"] else "No")
                self.tbl.item(row, 5).setText(f"{float(item['igv_unit']):.2f}")
                self.tbl.item(row, 6).setText(f"{float(item['precio_unit']):.2f}")
                self.tbl.item(row, 7).setText(f"{float(item['importe']):.2f}")
                
                # Columna cantidad (editable)
                cantidad_item = QTableWidgetItem(f"{float(item['cantidad']):.2f}")
                cantidad_item.setFlags(cantidad_item.flags() | Qt.ItemIsEditable)
                self.tbl.setItem(row, 4, cantidad_item)
                
                # Guardar datos para recalcular
                self.tbl.item(row, 4).setData(Qt.UserRole, {
                    "producto_id": item["producto_id"],
                    "afecto_igv": item["afecto_igv"],
                    "valor_unit": float(item["valor_unit"]),
                    "igv_unit": float(item["igv_unit"]),
                    "precio_unit": float(item["precio_unit"])
                })
            
            # Restaurar información del cliente si existe
            if venta_temporal.get("cliente_documento") and venta_temporal.get("cliente_nombre"):
                self.lbl_documento_cliente.setText(venta_temporal["cliente_documento"])
                self.lbl_nombre_cliente.setText(venta_temporal["cliente_nombre"])
                self.lbl_tipo_documento.setText(venta_temporal.get("tipo_documento", "Tipo: --"))
            
            # Recalcular totales
            self._recalc_totales()
            
            # Eliminar archivo temporal
            os.remove("temporal.json")
            
            QMessageBox.information(
                self, 
                "Venta Recuperada", 
                f"✅ Venta temporal recuperada exitosamente\n\n"
                f"📦 Productos: {len(self.items)}\n"
                f"💰 Total: S/. {venta_temporal['total']:.2f}\n"
                f"🔄 La venta está lista para continuar"
            )
            
            return True
            
        except Exception as e:
            print(f"❌ Error al recuperar venta temporal: {e}")
            # Si hay error, eliminar archivo corrupto
            try:
                if os.path.exists("temporal.json"):
                    os.remove("temporal.json")
            except:
                pass
            return False
    
    def closeEvent(self, event):
        """Maneja el cierre de la ventana"""
        try:
            # Guardar venta temporal si hay productos
            if self.tbl.rowCount() > 0:
                self._guardar_venta_temporal()
            
            # Continuar con el cierre normal
            event.accept()
            
        except Exception as e:
            print(f"❌ Error en closeEvent: {e}")
            event.accept()

    def abrir_gestion_clientes(self):
        """Abre el módulo de gestión de clientes"""
        try:
            self.clientes_admin = ClientesAdmin(self.db)
            self.clientes_admin.show()
        except Exception as e:
            QMessageBox.critical(self, "❌ Error", f"Error al abrir gestión de clientes: {str(e)}")

    def abrir_cierre_caja(self):
        """Abre el módulo de cierre de caja"""
        try:
            # Verificar permisos (solo jefe, supervisor, admin)
            if self.rol not in ['admin', 'jefe', 'supervisor']:
                QMessageBox.warning(self, "⚠️ Acceso Denegado", 
                                  "Solo administradores, jefes y supervisores pueden acceder al cierre de caja.")
                return
            
            # Obtener ID del usuario actual
            usuario_id = self.db.get_usuario_id_por_login(self.usuario)
            if not usuario_id:
                QMessageBox.critical(self, "❌ Error", "No se pudo obtener el ID del usuario.")
                return
            
            # Abrir formulario de cierre de caja
            self.cierre_caja_dialog = CierreCajaForm(self.db, self.usuario, usuario_id)
            self.cierre_caja_dialog.exec()
            
        except Exception as e:
            QMessageBox.critical(self, "❌ Error", f"Error al abrir cierre de caja: {str(e)}")
    
    def keyPressEvent(self, event: QKeyEvent):
        """Manejar atajos de teclado"""
        try:
            # F4: Posicionar cursor en campo de búsqueda
            if event.key() == Qt.Key.Key_F4:
                self.input_codigo.setFocus()
                self.input_codigo.selectAll()
                return
            
            # F5: Posicionar cursor en botón de pagos
            elif event.key() == Qt.Key.Key_F5:
                self.btn_pagos.setFocus()
                return
            
            # F6: Posicionar cursor en tabla de productos, columna cantidad
            elif event.key() == Qt.Key.Key_F6:
                if self.tbl.rowCount() > 0:
                    # Ir a la última fila, columna cantidad (columna 4)
                    last_row = self.tbl.rowCount() - 1
                    self.tbl.setCurrentCell(last_row, 4)  # Columna cantidad
                    self.tbl.setFocus()
                    # Editar la celda directamente
                    self.tbl.edit(self.tbl.currentIndex())
                return
            
            # Llamar al método padre para otros eventos
            super().keyPressEvent(event)
            
        except Exception as e:
            print(f"Error en keyPressEvent: {e}")
            super().keyPressEvent(event)
