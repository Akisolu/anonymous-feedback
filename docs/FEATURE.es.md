# Especificación Técnica de Anonymous Feedback App

## 1. Descripción General del Sistema

El Sistema de Feedback Anónimo es una plataforma web concebida para la recolección, almacenamiento y gestión de comentarios enviados de forma libre e idénticamente anónima por los usuarios. El sistema garantiza la privacidad del emisor al prescindir de mecanismos de autenticación en la recepción de mensajes, al tiempo que provee un panel privado protegido para que el administrador (Owner) pueda visualizar, organizar y administrar el flujo de retroalimentación recibido.

---

## 2. Descripción de Actores

| Actor | Descripción | Interacción con el Sistema |
| :--- | :--- | :--- |
| **Usuario No Autenticado (Emisor)** | Cualquier persona que accede a la interfaz pública del sistema sin proporcionar credenciales de identidad. | Accede a la página principal, redacta y envía mensajes de feedback de forma anónima dentro de los límites de uso estipulados. |
| **Owner (Administrador)** | Usuario responsable de la lectura, control y organización de los feedbacks recopilados. | Inicia sesión con credenciales válidas, accede al panel de control protegido (`/admin/dashboard`), consulta la lista de feedbacks, cambia su estado (leído, archivado, eliminado) y finaliza sesión de manera segura. |

---

## 3. Especificaciones del Sistema en Sintaxis Gherkin

### 3.1. Recepción y Envío de Feedback

```gherkin
@envio_feedback_anonimo
Feature: Envio de feedback anonimo por usuarios no autenticados

  Scenario: Carga de la pantalla principal para un usuario no autenticado
    Given un usuario no autenticado accede a la URL principal del sistema
    When la página termina de cargar
    Then se debe mostrar un área de texto para ingresar el feedback
    And se debe mostrar el botón "Enviar"
```

```gherkin
@procesamiento_y_almacenamiento
Feature: Procesamiento y guardado de mensajes de feedback

  Scenario: Envio y persistencia exitosa de un mensaje de feedback
    Given un usuario no autenticado ha ingresado un texto válido en la pantalla de feedback
    When el usuario presiona el botón "Enviar"
    Then el sistema debe procesar el mensaje
    And debe guardar el mensaje en la base de datos
    And debe mostrar un mensaje de confirmación al usuario
```

```gherkin
@validacion_feedback
Feature: Validaciones en el envio de feedback anonimo

  Scenario: Intento de envio con el campo de feedback vacio
    Given un usuario no autenticado se encuentra en la pantalla de inicio
    When el usuario presiona el boton "Enviar" sin escribir ningun mensaje
    Then el sistema debe impedir el envio del formulario
    And debe mostrar un mensaje de error indicando que el contenido es obligatorio

  Scenario: Intento de envio superando el limite maximo de caracteres
    Given un usuario no autenticado ingresa un mensaje que supera el limite permitido de caracteres
    When el usuario intenta enviar el formulario
    Then el sistema debe impedir el envio
    And debe notificar al usuario sobre el limite de caracteres superado
```

```gherkin
@rate_limiting
Feature: Control de frecuencia en el envio de feedbacks

  Scenario: Envio de feedback dentro del limite permitido
    Given un usuario no autenticado ha enviado menos de 10 mensajes en los ultimos 10 minutos
    When el usuario envia un nuevo mensaje de feedback
    Then el sistema debe procesar y guardar el mensaje exitosamente

  Scenario: Bloqueo por superar el limite de envios permitidos
    Given un usuario no autenticado ha enviado 10 mensajes en un lapso menor a 10 minutos
    When el usuario intenta enviar un mensaje adicional
    Then el sistema debe rechazar el envio
    And debe mostrar un mensaje indicando que ha alcanzado el limite de envios y debe esperar
```

---

### 3.2. Seguridad y Control de Acceso

```gherkin
@control_de_acceso
Feature: Restricción de acceso a rutas protegidas para usuarios no autenticados

  Scenario: Intento de acceso a una ruta protegida sin autenticación
    Given un usuario no autenticado intenta acceder a una URL que requiere autenticación
    When el sistema procesa la solicitud de acceso
    Then el sistema debe rechazar la petición
    And debe reorientar al usuario a la pantalla de inicio de sesión o mostrar un mensaje de acceso denegado
```

```gherkin
@autenticacion_owner
Feature: Inicio de sesion del usuario Owner mediante credenciales

  Scenario: Inicio de sesion exitoso con credenciales validas
    Given un usuario con rol Owner se encuentra en la pantalla de inicio de sesion
    When el usuario ingresa sus credenciales validas de correo o usuario y contraseña
    And presiona el boton de inicio de sesion
    Then el sistema debe autenticar las credenciales
    And debe otorgar acceso al panel de administracion del Owner
```

```gherkin
@seguridad_autenticacion
Feature: Manejo de errores en el inicio de sesion del Owner

  Scenario: Intento de inicio de sesion con credenciales invalidas
    Given un usuario se encuentra en la pantalla de inicio de sesion
    When ingresa un correo o contraseña incorrectos
    And presiona el boton de inicio de sesion
    Then el sistema debe denegar el acceso
    And debe mostrar un mensaje indicando que las credenciales son invalidas
```

```gherkin
@control_acceso_dashboard
Feature: Proteccion de la vista de lectura de feedbacks

  Scenario: Acceso permitido al dashboard para un usuario con sesion activa
    Given un usuario Owner con sesion activa intenta acceder a la ruta "/admin/dashboard"
    When el sistema valida la sesion del usuario
    Then el sistema debe permitir el acceso a la vista de lectura de feedbacks

  Scenario: Acceso denegado al dashboard para un usuario sin sesion activa
    Given un usuario no autenticado intenta acceder a la ruta "/admin/dashboard"
    When el sistema valida la sesion del usuario
    Then el sistema debe rechazar la peticion
    And debe redirigir al usuario a la pantalla de inicio de sesion
```

```gherkin
@gestion_sesion_owner
Feature: Cierre de sesion seguro para el usuario Owner

  Scenario: Cierre de sesion exitoso
    Given un usuario Owner con sesion activa se encuentra en la plataforma
    When el usuario selecciona la opcion de cerrar sesion
    Then el sistema debe destruir la sesion activa de forma segura
    And debe invalidar los tokens o credenciales temporales del usuario
    And debe redirigir al usuario a la pantalla de inicio de sesion
```

```gherkin
@expiracion_sesion
Feature: Expiracion automatica de sesion por inactividad

  Scenario: Sesion finalizada tras periodo de inactividad
    Given un usuario Owner ha mantenido una sesion activa sin realizar ninguna accion durante 1 hora
    When el Owner intenta realizar cualquier accion o navegar a una ruta dentro del dashboard
    Then el sistema debe invalidar la sesion
    And debe redirigir al usuario a la pantalla de inicio de sesion con un aviso de expiracion
```

---

### 3.3. Administración y Gestión de Feedbacks

```gherkin
@gestion_feedbacks_dashboard
Feature: Lectura y gestion de feedbacks en el dashboard

  Scenario: Visualizacion de la lista de feedbacks recibidos
    Given un usuario Owner con sesion activa accede a la ruta "/admin/dashboard"
    When la vista termina de cargar
    Then el sistema debe mostrar la lista de todos los feedbacks almacenados en la base de datos
    And cada feedback debe mostrar su contenido y la fecha o hora de envio
```

```gherkin
@gestion_estados_feedback
Feature: Gestion del estado de los feedbacks en el dashboard

  Scenario: Marcar un feedback como leido
    Given un usuario Owner con sesion activa visualiza la lista de feedbacks en "/admin/dashboard"
    When el Owner selecciona un feedback no leido y lo marca como leido
    Then el sistema debe actualizar el estado del feedback a "Leido" en la base de datos

  Scenario: Archivar un feedback
    Given un usuario Owner con sesion activa se encuentra en el dashboard
    When el Owner selecciona un feedback y lo marca como archivado
    Then el sistema debe cambiar el estado del feedback a "Archivado"
    And debe ocultarlo de la vista principal de feedbacks activos

  Scenario: Eliminar un feedback
    Given un usuario Owner con sesion activa se encuentra en el dashboard
    When el Owner elimina un feedback seleccionado
    Then el sistema debe remover el feedback de la base de datos o marcarlo como eliminado
```