# Desafío de Programación KIU

Este proyecto implementa un servicio de búsqueda de vuelos para una compañía aérea. Permite buscar viajes con una o dos conexiones, respetando las restricciones de tiempo y duración definidas en los requisitos.

## Instalación y Configuración

### Requisitos Previos
- PHP 8.1+
- Composer
- MySQL (opcional si se requiere base de datos adicional)
- Docker (opcional para levantar el entorno)

### Instalación Local

1. Clona el repositorio:
   ```bash
   git clone https://github.com/georgiy84/KIU_Test_PHP.git
   cd KIU_Test_PHP
   ```

2. Instala las dependencias de Composer:
   ```bash
   composer install
   ```

3. Copia el archivo `.env.example` y configura las variables de entorno:
   ```bash
   cp .env.example .env
   ```

4. Genera la clave de la aplicación:
   ```bash
   php artisan key:generate
   ```

5. Configura la fuente del YAML en el archivo `.env`:
   ```env
   YAML_SOURCE=local
   ```
   - Si utilizas un archivo local, asegúrate de colocarlo en `storage/app/flight_events.yaml`.
   - Si utilizas una URL remota, configura `YAML_SOURCE=url` y la URL correspondiente.

6. Levanta el servidor:
   ```bash
   php artisan serve
   ```

7. Accede al servicio en:
   ```
   http://localhost:8000/api/journeys/search
   ```

### Uso con Docker

1. Asegúrate de tener Docker instalado y funcionando.

2. Construye y levanta los contenedores:
   ```bash
   docker-compose up --build
   ```

3. Accede al servicio en:
   ```
   http://localhost:8000/api/journeys/search
   ```

## Uso de la API

### Endpoint: `/api/journeys/search`
**Método**: POST  

**Parámetros**:
- `date`: Fecha en formato `YYYY-MM-DD`.
- `from`: Código IATA de la ciudad de origen (3 letras).
- `to`: Código IATA de la ciudad de destino (3 letras).

**Ejemplo de Solicitud**:
```json
{
    "date": "2024-09-12",
    "from": "BUE",
    "to": "MAD"
}
```

**Respuesta de Ejemplo**:
```json
{
    "message": "Viajes encontrados.",
    "journeys": [
        {
            "connections": 1,
            "path": [
                {
                    "flight_number": "XX1234",
                    "from": "BUE",
                    "to": "MAD",
                    "departure_time": "2024-09-12 12:00:00",
                    "arrival_time": "2024-09-12 23:59:59"
                }
            ]
        }
    ]
}
```

## Pruebas Automatizadas

1. Ejecuta todas las pruebas:
   ```bash
   php artisan test
   ```

2. Pruebas específicas para el controlador:
   ```bash
   php artisan test --filter=JourneyControllerTest
   ```

3. Los tests incluyen:
   - **Vuelos directos**.
   - **Conexiones válidas**.
   - **Errores de validación**.
   - **Escenarios sin resultados**.

## Funcionalidades Implementadas

1. **Endpoint `/journeys/search`**:
   - Soporta vuelos directos y con una conexión.
   - Restricciones:
     - Tiempo de conexión ≤ 4 horas.
     - Duración total ≤ 24 horas.

2. **Pruebas Automatizadas**:
   - Validan múltiples escenarios:
     - Vuelos directos.
     - Conexiones válidas e inválidas.
     - Campos obligatorios.

3. **Soporte de Configuración**:
   - Fuente del YAML configurable (`local` o `url`).

## Extras

1. **Docker Compose**:
   - Archivo para levantar el entorno completo con PHP 8.1+ y Composer.

---