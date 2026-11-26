-- CREATE DATABASE HOSPITAL;
-- GO
-- USE HOSPITAL;
-- GO

CREATE TABLE Tipo_rol (
	id_tipo_rol INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
	nombre_rol VARCHAR(50) NOT NULL,
	descripcion VARCHAR(255),
	activo BIT NOT NULL DEFAULT 1,
	CONSTRAINT uq_tipo_rol_nombre UNIQUE (nombre_rol)
);

CREATE TABLE usuario (
	id_usuario INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
	id_tipo_rol INT NOT NULL,
	nombre VARCHAR(50) NOT NULL,
	ape_paterno VARCHAR(50) NOT NULL,
	ape_materno VARCHAR(50),
	telefono VARCHAR(30),
	correo VARCHAR(100) NOT NULL,
	contrasena_hash VARCHAR(255) NOT NULL,
	fecha_registro DATETIME NOT NULL DEFAULT GETDATE(),
	estado VARCHAR(30) NOT NULL DEFAULT 'Activo',
	CONSTRAINT uq_usuario_correo UNIQUE (correo),
	CONSTRAINT chk_usuario_estado CHECK (estado IN ('Activo', 'Inactivo', 'Bloqueado')),
	CONSTRAINT fk_usuario_tipo_rol FOREIGN KEY (id_tipo_rol) REFERENCES Tipo_rol(id_tipo_rol)
		ON UPDATE CASCADE
);

CREATE TABLE empleados (
	num_empleado INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
	id_usuario INT NOT NULL,
	horario_lab VARCHAR(100),
	tipo_empleado VARCHAR(50) NOT NULL,
	CONSTRAINT fk_empleados_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
		ON UPDATE CASCADE
);

CREATE TABLE admin (
	id_admin INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
	id_usuario INT NOT NULL,
	num_empleado INT NOT NULL,
	nivel_acceso VARCHAR(50) NOT NULL,
	CONSTRAINT fk_admin_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
	CONSTRAINT fk_admin_empleados_num FOREIGN KEY (num_empleado) REFERENCES empleados(num_empleado)
		ON UPDATE CASCADE
);

CREATE TABLE doctor (
	id_doctor INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
	id_usuario INT NOT NULL,
	num_empleado INT NOT NULL,
	cedula_profesional VARCHAR(50) NOT NULL,
	especialidad VARCHAR(100) NOT NULL,
	consultorio VARCHAR(50) NOT NULL,
	CONSTRAINT uq_doctor_cedula UNIQUE (cedula_profesional),
	CONSTRAINT fk_doctor_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
	CONSTRAINT fk_doctor_empleados_num FOREIGN KEY (num_empleado) REFERENCES empleados(num_empleado)
		ON UPDATE CASCADE
);

CREATE TABLE recepcionista (
	id_recepcionista INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
	id_usuario INT NOT NULL,
	num_empleado INT NOT NULL,
	estado VARCHAR(30) NOT NULL DEFAULT 'Activo',
	CONSTRAINT chk_recepcionista_estado CHECK (estado IN ('Activo', 'Inactivo')),
	CONSTRAINT fk_recepcionista_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
	CONSTRAINT fk_recepcionista_empleados_num FOREIGN KEY (num_empleado) REFERENCES empleados(num_empleado)
		ON UPDATE CASCADE
);

CREATE TABLE paciente (
	id_paciente INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
	id_usuario INT NOT NULL,
	fecha_nacimiento DATE NOT NULL,
	sexo VARCHAR(20) NOT NULL,
	domicilio VARCHAR(150),
	tipo_sangre VARCHAR(10),
	telefono_emergencia VARCHAR(30),
	contacto_emergencia VARCHAR(100),
	CONSTRAINT chk_paciente_sexo CHECK (sexo IN ('Masculino', 'Femenino', 'Otro')),
	CONSTRAINT fk_paciente_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
		ON UPDATE CASCADE
);

CREATE TABLE servicios (
	id_servicio INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
	nombre VARCHAR(100) NOT NULL,
	descripcion VARCHAR(255),
	precio DECIMAL(12, 2) NOT NULL,
	requiere_cita BIT NOT NULL,
	estado VARCHAR(30) NOT NULL DEFAULT 'Activo',
	CONSTRAINT chk_servicios_precio CHECK (precio >= 0),
	CONSTRAINT chk_servicios_estado CHECK (estado IN ('Activo', 'Inactivo'))
);

CREATE TABLE cita (
	id_cita INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
	id_paciente INT NOT NULL,
	id_doctor INT NOT NULL,
	id_servicio INT,
	fecha_cita DATE NOT NULL,
	hora_cita TIME(0) NOT NULL,
	fecha_solicitud DATETIME NOT NULL DEFAULT GETDATE(),
	motivo_consulta VARCHAR(255),
	fecha_cancelacion DATETIME,
	estado VARCHAR(30) NOT NULL DEFAULT 'Programada',
	estado_pago VARCHAR(30) NOT NULL DEFAULT 'Pendiente',
	CONSTRAINT chk_cita_estado CHECK (estado IN ('Programada', 'Confirmada', 'Completada', 'Cancelada', 'No asistio')),
	CONSTRAINT chk_cita_estado_pago CHECK (estado_pago IN ('Pendiente', 'Pagado', 'Reembolsado', 'Cancelado')),
	CONSTRAINT fk_cita_paciente FOREIGN KEY (id_paciente) REFERENCES paciente(id_paciente)
		ON UPDATE CASCADE,
	CONSTRAINT fk_cita_doctor FOREIGN KEY (id_doctor) REFERENCES doctor(id_doctor),
	CONSTRAINT fk_cita_servicio FOREIGN KEY (id_servicio) REFERENCES servicios(id_servicio)
		ON UPDATE CASCADE
);

CREATE TABLE receta_medica (
	id_receta INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
	id_cita INT NOT NULL,
	fecha_emision DATETIME NOT NULL DEFAULT GETDATE(),
	diagnostico VARCHAR(255) NOT NULL,
	observaciones VARCHAR(255),
	indicaciones_generales VARCHAR(255),
	CONSTRAINT fk_receta_medica_cita FOREIGN KEY (id_cita) REFERENCES cita(id_cita)
		ON UPDATE CASCADE
);

CREATE TABLE medicamento (
	id_medicamento INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
	nombre VARCHAR(100) NOT NULL,
	descripcion VARCHAR(255),
	concentracion VARCHAR(50),
	presentacion VARCHAR(50),
	precio_unitario DECIMAL(12, 2) NOT NULL,
	requiere_receta BIT NOT NULL,
	forma_farmaceutica VARCHAR(50),
	estado VARCHAR(30) NOT NULL DEFAULT 'Activo',
	CONSTRAINT chk_medicamento_precio CHECK (precio_unitario >= 0),
	CONSTRAINT chk_medicamento_estado CHECK (estado IN ('Activo', 'Inactivo'))
);

CREATE TABLE detalles_receta (
	id_detalles_receta INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
	id_receta INT NOT NULL,
	id_medicamento INT NOT NULL,
	dosis VARCHAR(100) NOT NULL,
	indicaciones VARCHAR(255),
	frecuencia VARCHAR(100) NOT NULL,
	duracion VARCHAR(100) NOT NULL,
	cantidad_prescrita INT NOT NULL,
	CONSTRAINT chk_detalles_receta_cantidad CHECK (cantidad_prescrita > 0),
	CONSTRAINT fk_detalles_receta_receta FOREIGN KEY (id_receta) REFERENCES receta_medica(id_receta)
		ON UPDATE CASCADE,
	CONSTRAINT fk_detalles_receta_medicamento FOREIGN KEY (id_medicamento) REFERENCES medicamento(id_medicamento)
		ON UPDATE CASCADE
);

CREATE TABLE inventario (
	id_inventario INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
	id_medicamento INT NOT NULL,
	cantidad_minima INT NOT NULL,
	cantidad_disponible INT NOT NULL,
	ubicacion_estante VARCHAR(50),
	fecha_actualizacion DATETIME NOT NULL DEFAULT GETDATE(),
	CONSTRAINT chk_inventario_minima CHECK (cantidad_minima >= 0),
	CONSTRAINT chk_inventario_disponible CHECK (cantidad_disponible >= 0),
	CONSTRAINT fk_inventario_medicamento FOREIGN KEY (id_medicamento) REFERENCES medicamento(id_medicamento)
		ON UPDATE CASCADE
);

CREATE TABLE venta (
	id_venta INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
	id_paciente INT NOT NULL,
	id_recepcionista INT NOT NULL,
	fecha_venta DATETIME NOT NULL DEFAULT GETDATE(),
	impuesto DECIMAL(12, 2) NOT NULL DEFAULT 0,
	total DECIMAL(12, 2) NOT NULL,
	CONSTRAINT chk_venta_impuesto CHECK (impuesto >= 0),
	CONSTRAINT chk_venta_total CHECK (total >= 0),
	CONSTRAINT fk_venta_paciente FOREIGN KEY (id_paciente) REFERENCES paciente(id_paciente)
		ON UPDATE CASCADE,
	CONSTRAINT fk_venta_recepcionista FOREIGN KEY (id_recepcionista) REFERENCES recepcionista(id_recepcionista)
);

CREATE TABLE detalle_venta (
	id_detalle_venta INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
	id_venta INT NOT NULL,
	id_medicamento INT,
	id_servicio INT,
	cantidad INT NOT NULL DEFAULT 1,
	precio_unitario DECIMAL(12, 2) NOT NULL,
	descuento DECIMAL(12, 2) NOT NULL DEFAULT 0,
	subtotal DECIMAL(12, 2) NOT NULL,
	CONSTRAINT fk_detalle_venta_venta FOREIGN KEY (id_venta) REFERENCES venta(id_venta)
		ON UPDATE CASCADE,
	CONSTRAINT fk_detalle_venta_medicamento FOREIGN KEY (id_medicamento) REFERENCES medicamento(id_medicamento)
		ON UPDATE CASCADE,
	CONSTRAINT fk_detalle_venta_servicio FOREIGN KEY (id_servicio) REFERENCES servicios(id_servicio)
		ON UPDATE CASCADE,
	CONSTRAINT chk_detalle_venta_item CHECK (
		(id_medicamento IS NOT NULL AND id_servicio IS NULL)
		OR (id_medicamento IS NULL AND id_servicio IS NOT NULL)
	),
	CONSTRAINT chk_detalle_venta_cantidad CHECK (cantidad > 0),
	CONSTRAINT chk_detalle_venta_precio CHECK (precio_unitario >= 0),
	CONSTRAINT chk_detalle_venta_montos CHECK (
		subtotal >= 0 AND descuento >= 0
	)
);

CREATE TABLE pago_de_med_serv (
	id_pago INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
	id_paciente INT,
	id_venta INT,
	id_cita INT,
	id_usuario INT,
	fecha_pago DATETIME NOT NULL DEFAULT GETDATE(),
	estado_pago VARCHAR(30) NOT NULL DEFAULT 'Completado',
	monto DECIMAL(12, 2) NOT NULL,
	CONSTRAINT chk_pago_estado CHECK (estado_pago IN ('Pendiente', 'Completado', 'Reembolsado', 'Cancelado')),
	CONSTRAINT fk_pago_paciente FOREIGN KEY (id_paciente) REFERENCES paciente(id_paciente),
	CONSTRAINT fk_pago_venta FOREIGN KEY (id_venta) REFERENCES venta(id_venta),
	CONSTRAINT fk_pago_cita FOREIGN KEY (id_cita) REFERENCES cita(id_cita),
	CONSTRAINT fk_pago_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
	CONSTRAINT chk_pago_concepto CHECK (
		(id_venta IS NOT NULL AND id_cita IS NULL)
		OR (id_venta IS NULL AND id_cita IS NOT NULL)
	),
	CONSTRAINT chk_pago_monto CHECK (
		monto > 0
	)
);

CREATE TABLE especialidad (
	id_especialidad INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
	nombre_especialidad VARCHAR(100) NOT NULL,
	descripcion VARCHAR(255),
	activa BIT NOT NULL DEFAULT 1,
	CONSTRAINT uq_especialidad_nombre UNIQUE (nombre_especialidad)
);

ALTER TABLE doctor
ADD id_especialidad INT NOT NULL;

ALTER TABLE doctor
ADD CONSTRAINT fk_doctor_especialidad FOREIGN KEY (id_especialidad)
	REFERENCES especialidad(id_especialidad)
	ON UPDATE CASCADE;

ALTER TABLE paciente
DROP COLUMN domicilio;
/*```[cite: 1]

* Al insertar en `usuario`, ahora deberás pasar `id_tipo_rol` (por ejemplo, consultando primero `SELECT id_tipo_rol FROM Tipo_rol WHERE nombre_rol = ?`).
* Para consultar paciente y doctor de una receta médica, basta hacer `JOIN cita ON receta_medica.id_cita = cita.id_cita` y unir desde ahí con `paciente` y `doctor`[cite: 1].
