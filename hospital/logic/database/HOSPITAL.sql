--CREATE DATABASE HOSPITAL
--USE HOSPITAL
--DROP DATABASE HOSPITAL


CREATE TABLE usuario (
	id_usuario INT NOT NULL PRIMARY KEY,
	nombre VARCHAR(50) NOT NULL,
	ape_paterno VARCHAR(50) NOT NULL,
	ape_materno VARCHAR(50),
	telefono VARCHAR(30),
	correo VARCHAR(100),
	contrasena_hash VARCHAR(255) NOT NULL,
	matricula_inicio_sesion VARCHAR(50),
	fecha_registro DATETIME NOT NULL,
	rol VARCHAR(50) NOT NULL,
	estado VARCHAR(30) NOT NULL
);


CREATE TABLE empleados (
	num_empleado INT NOT NULL PRIMARY KEY,
	id_usuario INT NOT NULL,
	horario_lab VARCHAR(100),
	tipo_empleado VARCHAR(50) NOT NULL,
	CONSTRAINT fk_empleados_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
	ON UPDATE CASCADE
);


CREATE TABLE admin (
	id_admin INT NOT NULL PRIMARY KEY,
	id_usuario INT NOT NULL,
	num_empleado INT NOT NULL,
	nivel_acceso VARCHAR(50) NOT NULL,
	CONSTRAINT fk_admin_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
	CONSTRAINT fk_admin_empleados_num FOREIGN KEY (num_empleado) REFERENCES empleados(num_empleado)
	ON UPDATE CASCADE
);


CREATE TABLE doctor (
	id_doctor INT NOT NULL PRIMARY KEY,
	id_usuario INT NOT NULL,
	num_empleado INT NOT NULL,
	cedula_profesional VARCHAR(50) NOT NULL,
	especialidad VARCHAR(100) NOT NULL,
	consultorio VARCHAR(50) NOT NULL,
	CONSTRAINT fk_doctor_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
	CONSTRAINT fk_doctor_empleados_num FOREIGN KEY (num_empleado) REFERENCES empleados(num_empleado)
	ON UPDATE CASCADE
);


CREATE TABLE recepcionista (
	id_recepcionista INT NOT NULL PRIMARY KEY,
	id_usuario INT NOT NULL,
	num_empleado INT NOT NULL,
	estado VARCHAR(30) NOT NULL,
	CONSTRAINT fk_recepcionista_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
	CONSTRAINT fk_recepcionista_empleados_num FOREIGN KEY (num_empleado) REFERENCES empleados(num_empleado)
	ON UPDATE CASCADE
);


CREATE TABLE paciente (
	id_paciente INT NOT NULL PRIMARY KEY,
	id_usuario INT NOT NULL,
	fecha_nacimiento DATETIME NOT NULL,
	sexo VARCHAR(20) NOT NULL,
	domicilio VARCHAR(150),
	tipo_sangre VARCHAR(10),
	telefono_emergencia VARCHAR(30),
	contacto_emergencia VARCHAR(100),
	CONSTRAINT fk_paciente_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
	ON UPDATE CASCADE
);


CREATE TABLE servicios (
	id_servicio INT NOT NULL PRIMARY KEY,
	nombre VARCHAR(100) NOT NULL,
	descripcion VARCHAR(255),
	precio MONEY NOT NULL,
	requiere_cita BIT NOT NULL,
	estado VARCHAR(30) NOT NULL
);


CREATE TABLE cita (
	id_cita INT NOT NULL PRIMARY KEY,
	id_paciente INT NOT NULL,
	id_doctor INT NOT NULL,
	id_servicio INT,
	fecha_cita DATETIME NOT NULL,
	hora_cita VARCHAR(20) NOT NULL,
	fecha_solicitud DATETIME NOT NULL,
	motivo_consulta VARCHAR(255),
	fecha_cancelacion DATETIME,
	estado VARCHAR(30) NOT NULL,
	estado_pago VARCHAR(30) NOT NULL,
	CONSTRAINT fk_cita_paciente FOREIGN KEY (id_paciente) REFERENCES paciente(id_paciente)
	ON UPDATE CASCADE,
	CONSTRAINT fk_cita_doctor FOREIGN KEY (id_doctor) REFERENCES doctor(id_doctor),
	CONSTRAINT fk_cita_servicio FOREIGN KEY (id_servicio) REFERENCES servicios(id_servicio)
	ON UPDATE CASCADE
);


CREATE TABLE receta_medica (
	id_receta INT NOT NULL PRIMARY KEY,
	id_cita INT NOT NULL,
	id_paciente INT NOT NULL,
	id_doctor INT NOT NULL,
	fecha_emision DATETIME NOT NULL,
	diagnostico VARCHAR(255) NOT NULL,
	observaciones VARCHAR(255),
	indicaciones_generales VARCHAR(255),
	CONSTRAINT fk_receta_medica_cita FOREIGN KEY (id_cita) REFERENCES cita(id_cita)
	ON UPDATE CASCADE,
	CONSTRAINT fk_receta_medica_paciente FOREIGN KEY (id_paciente) REFERENCES paciente(id_paciente),
	CONSTRAINT fk_receta_medica_doctor FOREIGN KEY (id_doctor) REFERENCES doctor(id_doctor)
);


CREATE TABLE medicamento (
	id_medicamento INT NOT NULL PRIMARY KEY,
	nombre VARCHAR(100) NOT NULL,
	descripcion VARCHAR(255),
	concentracion VARCHAR(50),
	presentacion VARCHAR(50),
	precio_unitario MONEY NOT NULL,
	requiere_receta BIT NOT NULL,
	forma_farmaceutica VARCHAR(50),
	estado VARCHAR(30) NOT NULL
);


CREATE TABLE detalles_receta (
	id_detalles_receta INT NOT NULL PRIMARY KEY,
	id_receta INT NOT NULL,
	id_medicamento INT NOT NULL,
	dosis VARCHAR(100) NOT NULL,
	indicaciones VARCHAR(255),
	frecuencia VARCHAR(100) NOT NULL,
	duracion VARCHAR(100) NOT NULL,
	cantidad_prescrita INT NOT NULL,
	CONSTRAINT fk_detalles_receta_receta FOREIGN KEY (id_receta) REFERENCES receta_medica(id_receta)
	ON UPDATE CASCADE,
	CONSTRAINT fk_detalles_receta_medicamento FOREIGN KEY (id_medicamento) REFERENCES medicamento(id_medicamento)
	ON UPDATE CASCADE
);


CREATE TABLE inventario (
	id_inventario INT NOT NULL PRIMARY KEY,
	id_medicamento INT NOT NULL,
	cantidad_minima INT NOT NULL,
	cantidad_disponible INT NOT NULL,
	ubicacion_estante VARCHAR(50),
	fecha_actualizacion DATETIME NOT NULL,
	CONSTRAINT fk_inventario_medicamento FOREIGN KEY (id_medicamento) REFERENCES medicamento(id_medicamento)
	ON UPDATE CASCADE
);


CREATE TABLE venta (
	id_venta INT NOT NULL PRIMARY KEY,
	id_paciente INT NOT NULL,
	id_recepcionista INT NOT NULL,
	fecha_venta DATETIME NOT NULL,
	impuesto MONEY NOT NULL,
	total MONEY NOT NULL,
	CONSTRAINT fk_venta_paciente FOREIGN KEY (id_paciente) REFERENCES paciente(id_paciente)
	ON UPDATE CASCADE,
	CONSTRAINT fk_venta_recepcionista FOREIGN KEY (id_recepcionista) REFERENCES recepcionista(id_recepcionista)
);


CREATE TABLE detalle_venta (
	id_detalle_venta INT NOT NULL PRIMARY KEY,
	id_venta INT NOT NULL,
	id_medicamento INT,
	id_servicio INT,
	subtotal MONEY NOT NULL,
	descuento MONEY NOT NULL,
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
	CONSTRAINT chk_detalle_venta_montos CHECK (
		subtotal >= 0 AND descuento >= 0
	)
);


CREATE TABLE pago_de_med_serv (
	id_pago INT NOT NULL PRIMARY KEY,
	id_paciente INT,
	id_venta INT,
	id_cita INT,
	id_usuario INT,
	fecha_pago DATETIME NOT NULL,
	estado_pago VARCHAR(30) NOT NULL,
	monto MONEY NOT NULL,
	CONSTRAINT fk_pago_paciente FOREIGN KEY (id_paciente) REFERENCES paciente(id_paciente),
	CONSTRAINT fk_pago_venta FOREIGN KEY (id_venta) REFERENCES venta(id_venta),
	CONSTRAINT fk_pago_cita FOREIGN KEY (id_cita) REFERENCES cita(id_cita),
	CONSTRAINT fk_pago_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
	CONSTRAINT chk_pago_concepto CHECK (
		id_venta IS NOT NULL OR id_cita IS NOT NULL
	),
	CONSTRAINT chk_pago_monto CHECK (
		monto > 0
	)
);