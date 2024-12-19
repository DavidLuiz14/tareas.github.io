-- Crear la base de datos
CREATE DATABASE tienda_ropa;
USE tienda_ropa;

-- Tabla: asistencia
CREATE TABLE asistencia (
    id_asistencia INT NOT NULL AUTO_INCREMENT,
    id_trabajador INT NOT NULL,
    fecha DATE NOT NULL,
    hora_llegada TIME NOT NULL,
    hora_salida TIME DEFAULT NULL,
    horas_trabajadas DECIMAL(5,2) DEFAULT NULL,
    PRIMARY KEY (id_asistencia),
    FOREIGN KEY (id_trabajador) REFERENCES trabajadores(id_trabajador)
);

-- Tabla: clientes
CREATE TABLE clientes (
    id_cliente INT NOT NULL AUTO_INCREMENT,
    nombre_completo VARCHAR(100) NOT NULL,
    telefono VARCHAR(10) DEFAULT NULL,
    correo VARCHAR(50) DEFAULT NULL,
    direccion VARCHAR(255) NOT NULL,
    nif VARCHAR(13) DEFAULT NULL,
    PRIMARY KEY (id_cliente)
);

-- Tabla: proveedores
CREATE TABLE proveedores (
    id_proveedor INT NOT NULL AUTO_INCREMENT,
    nombre_completo VARCHAR(100) NOT NULL,
    telefono VARCHAR(10) DEFAULT NULL,
    correo VARCHAR(50) DEFAULT NULL,
    direccion VARCHAR(255) NOT NULL,
    nif VARCHAR(13) DEFAULT NULL,
    PRIMARY KEY (id_proveedor)
);

-- Tabla: configuracion
CREATE TABLE configuracion (
    id INT NOT NULL AUTO_INCREMENT,
    clave VARCHAR(50) NOT NULL,
    valor INT NOT NULL,
    PRIMARY KEY (id),
    UNIQUE (clave)
);

-- Tabla: facturas
CREATE TABLE facturas (
    id_factura INT NOT NULL AUTO_INCREMENT,
    id_transaccion INT NOT NULL,
    numero_factura VARCHAR(20) NOT NULL,
    fecha_expedicion DATE NOT NULL,
    descripcion TEXT NOT NULL,
    valor_total DECIMAL(10,2) NOT NULL,
    vendedor VARCHAR(100) NOT NULL,
    comprador VARCHAR(100) NOT NULL,
    nif_vendedor VARCHAR(13) NOT NULL,
    nif_comprador VARCHAR(13) DEFAULT NULL,
    direccion_vendedor VARCHAR(255) NOT NULL,
    direccion_comprador VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (id_factura),
    UNIQUE (numero_factura),
    FOREIGN KEY (id_transaccion) REFERENCES transacciones(id_transaccion)
);

-- Tabla: productos
CREATE TABLE productos (
    id_producto INT NOT NULL AUTO_INCREMENT,
    nombre_producto VARCHAR(100) NOT NULL,
    tipo_producto VARCHAR(50) NOT NULL,
    descripcion VARCHAR(200) NOT NULL,
    precio_compra DECIMAL(10,2) NOT NULL,
    precio_venta DECIMAL(10,2) NOT NULL,
    stock_actual INT NOT NULL DEFAULT 0,
    stock_minimo INT NOT NULL DEFAULT 10,
    PRIMARY KEY (id_producto)
);

-- Tabla: reportes
CREATE TABLE reportes (
    id_reporte INT NOT NULL AUTO_INCREMENT,
    tipo_reporte ENUM('Reporte de Asistencia', 'Reporte de Inventario', 'Reporte de Transacciones', 'Reporte de Nómina') DEFAULT NULL,
    fecha_generacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    archivo_generado VARCHAR(255) NOT NULL,
    PRIMARY KEY (id_reporte)
);

-- Tabla: trabajadores
CREATE TABLE trabajadores (
    id_trabajador INT NOT NULL AUTO_INCREMENT,
    nombre_completo VARCHAR(100) NOT NULL,
    telefono VARCHAR(10) NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    puesto ENUM('Administrador' , 'vendedor', 'cajero') NOT NULL,
    fecha_registro DATE NOT NULL,
    salario_base DECIMAL(10,2) NOT NULL DEFAULT 100.00,
    dias_trabajados INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id_trabajador)
);

-- Tabla: transacciones
CREATE TABLE compras (
    id_compra INT NOT NULL AUTO_INCREMENT,
    id_producto INT NOT NULL,
    id_proveedor INT NOT NULL,
    cantidad INT NOT NULL,
    fecha_compra DATE NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (id_compra),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor)
);

-- Tabla: usuarios
CREATE TABLE usuarios (
    id_usuario INT NOT NULL AUTO_INCREMENT,
    nombre_usuario VARCHAR(50) NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    rol ENUM('Administrador', 'cliente') NOT NULL DEFAULT 'Usuario',
    PRIMARY KEY (id_usuario),
    UNIQUE (nombre_usuario),
);

CREATE TABLE pedidos(
    id_pedido INT NOT NULL AUTO_INCREMENT,
    fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente', 'procesado', 'realizado', 'pagado', 'cancelado'),
    total DECIMAL(10,2) NOT NULL,
    id_cliente INT NOT NULL,
    PRIMARY KEY (id_pedido),
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
);

CREATE TABLE detalles_pedido(
    id_detalle INT NOT NULL AUTO_INCREMENT,
    id_pedido INT NOT NULL,
    id_producto INT NOT NULL, 
    cantidad INT NOT NULL, 
    precio_unitario DECIMAL(10,2) NOT NULL, 
    subtotal DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (id_detalle),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido)
);

CREATE TABLE lista_deseos(
    id_deseo INT NOT NULL AUTO_INCREMENT, 
    id_cliente INT NOT NULL,
    id_producto INT NOT NULL, 
    fecha_agegado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_deseo),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
);

CREATE TABLE carrito(
    id_carrito INT NOT NULL AUTO_INCREMENT,
    id_cliente INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL,
    fecha_agegado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_carrito),
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
);

CREATE TABLE opiniones(
    id_opinion INT NOT NULL AUTO_INCREMENT,
    id_cliente INT NOT NULL,
    id_producto INT NOT NULL,
    calificacion TINYINT CHECK (calificacion BETWEEN 1 AND 5),
    comentario VARCHAR(200),
    fecha_opinion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_opinion),
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
);

