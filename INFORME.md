# Análisis Técnico: Despliegue de Aplicaciones Web con Docker

**Título del Trabajo:** Despliegue de Aplicaciones Web mediante Contenedores Docker
**Alumno:** Alfonso
**Módulo:** Despliegue de Aplicaciones Web
**Curso:** Técnico Superior en Desarrollo de Aplicaciones Web
**Fecha:** 4 de enero de 2026

---

## 1. Introducción

Este documento técnico detalla el proceso de diseño y despliegue de una infraestructura web basada en microservicios utilizando la tecnología de contenedores Docker. El objetivo principal es crear un entorno de ejecución replicable, seguro y escalable que integre un servidor web (Nginx), un motor de procesamiento de scripts (PHP) y un sistema de gestión de bases de datos relacionales (MySQL). A través de este análisis, se explora cómo la orquestación de estos servicios permite optimizar el ciclo de vida del desarrollo de software.

## 2. Análisis del Dockerfile

El archivo `Dockerfile` es el pilar fundamental para la creación de imágenes personalizadas. En este proyecto, se ha utilizado para definir el entorno de ejecución de PHP.

### Instrucciones Implementadas:
- `FROM php:8.2-fpm`: Punto de partida que utiliza una imagen ligera basada en Debian con PHP-FPM preinstalado.
- `RUN docker-php-ext-install mysqli pdo pdo_mysql`: Comando crucial que instala y compila las extensiones necesarias para que PHP pueda comunicarse con el servicio de base de datos MySQL.
- `WORKDIR /var/www/html`: Define el directorio raíz dentro del contenedor donde se alojará el código fuente de la aplicación.

La importancia del `Dockerfile` reside en la **estandarización**. Asegura que cualquier desarrollador o servidor de producción ejecute exactamente la misma versión de PHP con las mismas dependencias, eliminando el clásico problema de "en mi máquina funciona".

---

## 3. Uso de docker-compose

`docker-compose.yml` actúa como el orquestador que permite definir y gestionar múltiples contenedores como un único servicio.

### Componentes y Gestión:
- **Servicios:** Se han definido tres servicios clave (`nginx`, `php`, `mysql`) que interactúan entre sí.
- **Variables de Entorno (.env):** La gestión de credenciales sensibles (usuarios, contraseñas de DB) se realiza mediante un archivo `.env`. Esto es vital para la **seguridad**, ya que permite separar la configuración del código, evitando que información crítica se suba a repositorios de control de versiones.
- **Volúmenes Persistentes:** Se ha definido el volumen `db_data` mapeado a `/var/lib/mysql`. Esto garantiza la **persistencia** de la información; si el contenedor se destruye o se reinicia, los datos de la base de datos permanecen intactos en el sistema de archivos del host.
- **Redes:** Se utiliza `app-network` para que los contenedores puedan comunicarse entre sí mediante nombres de servicio (ej. `php:9000`).

---

## 4. Conclusión Crítica

El uso de Docker simplifica drásticamente el flujo de trabajo de despliegue. He aprendido que la capacidad de empaquetar una infraestructura completa en un par de archivos de configuración no solo ahorra tiempo, sino que reduce errores humanos. En el ámbito profesional, esta tecnología es indispensable para implementar arquitecturas de microservicios y garantizar la escalabilidad y mantenibilidad de las aplicaciones web. La gestión correcta de volúmenes y variables de entorno es la diferencia entre un despliegue amateur y uno profesional y seguro.

---

## 5. Bibliografía
- Docker. (s.f.). *Dockerfile reference*. Docker Documentation. Recuperado el 7 de enero de 2026, de https://docs.docker.com/engine/reference/builder/
- Docker. (s.f.). *Docker Compose*. Docker Documentation. Recuperado el 7 de enero de 2026, de https://docs.docker.com/compose/
- Docker. (s.f.). *php*. Docker Hub. Recuperado el 7 de enero de 2026, de https://hub.docker.com/_/php

---

## Anexos

### Archivo .env
```env
MYSQL_DATABASE=myapp_db
MYSQL_USER=user
MYSQL_PASSWORD=user_password
MYSQL_ROOT_PASSWORD=root_password
```

### Dockerfile (docker/php/Dockerfile)
```dockerfile
FROM php:8.2-fpm

# Instalar extensiones de PHP para MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable mysqli

# Establecer el directorio de trabajo
WORKDIR /var/www/html
```

### Configuración Nginx (docker/nginx/default.conf)
```nginx
server {
    listen 80;
    index index.php index.html;
    server_name localhost;
    root /var/www/html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass php:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }
}
```

### docker-compose.yml
```yaml
services:
  nginx:
    image: nginx:stable-alpine
    container_name: nginx_server
    ports:
      - "80:80"
    volumes:
      - ./src:/var/www/html
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - php
      - mysql
    networks:
      - app-network

  php:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    container_name: php_app
    volumes:
      - ./src:/var/www/html
    environment:
      MYSQL_DATABASE: ${MYSQL_DATABASE}
      MYSQL_USER: ${MYSQL_USER}
      MYSQL_PASSWORD: ${MYSQL_PASSWORD}
    networks:
      - app-network

  mysql:
    image: mysql:8.0
    container_name: mysql_db
    restart: always
    environment:
      MYSQL_DATABASE: ${MYSQL_DATABASE}
      MYSQL_USER: ${MYSQL_USER}
      MYSQL_PASSWORD: ${MYSQL_PASSWORD}
      MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD}
    volumes:
      - db_data:/var/lib/mysql
    networks:
      - app-network

networks:
  app-network:
    driver: bridge

volumes:
  db_data:
```

### Código de la Aplicación (src/index.php)
```php
<?php
$host = 'mysql';
$db   = getenv('MYSQL_DATABASE');
$user = getenv('MYSQL_USER');
$pass = getenv('MYSQL_PASSWORD');
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

echo "<h1>Prueba de Despliegue con Docker</h1>";

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     echo "<p style='color: green;'>¡Conexión a la base de datos establecida con éxito!</p>";
} catch (\PDOException $e) {
     echo "<p style='color: red;'>Error al conectar a la base de datos: " . $e->getMessage() . "</p>";
}

echo "<h3>Detalles del Entorno:</h3>";
echo "<ul>";
echo "<li>Servidor Web: " . $_SERVER['SERVER_SOFTWARE'] . "</li>";
echo "<li>Versión de PHP: " . phpversion() . "</li>";
echo "<li>Base de Datos: " . $db . "</li>";
echo "</ul>";
?>
```
