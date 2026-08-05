# 📋 Especificación de Requisitos y Diseño de Sistema

**Proyecto:** Anonymous Feedback App  
**Arquitectura:** PHP Nativo (MVC / Pattern-based) + SQL + PHPUnit  
**Enfoque:** Autenticación, Autorización, Seguridad Backend y Pruebas Automatizadas.

---

## 🎯 Objetivo del Sistema

Proporcionar un canal de comunicación unidireccional y anónimo donde cualquier usuario web pueda enviar comentarios sin necesidad de registro, garantizando que únicamente el administrador autenticado pueda leer, clasificar y administrar los mensajes desde un panel protegido.

---

## 🛠️ Requisitos Funcionales (RF)

| ID | Descripción | Criterio de Aceptación |
| :--- | :--- | :--- |
| **RF-01** | **Envío de Feedback Anónimo** | Cualquier visitante no autenticado puede enviar un mensaje a través del formulario público. |
| **RF-02** | **Persistencia de Feedback** | El sistema guarda el mensaje en la BD con estado inicial `unread` y marca temporal (`created_at`). |
| **RF-03** | **Autenticación del Administrador** | El usuario *Owner* inicia sesión mediante credenciales (usuario/correo y contraseña). |
| **RF-04** | **Panel de Administración Protegido** | Solo el usuario con sesión activa puede acceder a la vista `/admin/dashboard`. |
| **RF-05** | **Gestión de Estados de Feedback** | El *Owner* puede cambiar el estado de un feedback a `read`, `archived` o marcarlo como `deleted`. |
| **RF-06** | **Eliminación de Feedback** | El *Owner* puede remover permanentemente o realizar borrado lógico (*soft delete*) de un mensaje. |
| **RF-07** | **Cierre de Sesión (Logout)** | El *Owner* puede destruir su sesión activa de forma segura. |

---

## 🔒 Requisitos No Funcionales (RNF)

| ID | Categoría | Descripción |
| :--- | :--- | :--- |
| **RNF-01** | **Autenticación** | Hashing seguro de contraseñas mediante `password_hash()` usando `bcrypt` o `argon2`. |
| **RNF-02** | **Autorización** | Verificación estricta de `$_SESSION` en controladores protegidos con redirección HTTP `401/403` a login. |
| **RNF-03** | **Sanitización (XSS)** | Sanitización de entradas con `htmlspecialchars()` antes de renderizar mensajes en el panel. |
| **RNF-04** | **Consultas Seguras (SQLi)** | Uso obligatorio de *Prepared Statements* mediante PDO en toda interacción con la BD. |
| **RNF-05** | **Seguridad de Sesión** | Regeneración de ID de sesión (`session_regenerate_id(true)`) al autenticar para evitar *Session Fixation*. |
| **RNF-06** | **Expiración por Inactividad** | Destrucción automática de la sesión tras **1 hora (3600s)** sin actividad del *Owner*. |
| **RNF-07** | **Control de Frecuencia (Redis)** | Control de tasa (*Rate Limiting*) de peticiones mediante Redis en memoria RAM a un máximo de **10 envíos por cada 10 minutos por identificador anónimo**. |



---

## ⚖️ Reglas de Negocio (RN)

1. **RN-01 (Privacidad del Feedback):** Rutas y endpoints de administración son inaccesibles para usuarios no autenticados.
2. **RN-02 (Anonimato y Privacidad Desvinculada):** El sistema **no almacenará direcciones IP en texto plano ni asociadas al mensaje guardado**. Para el control de tasa (*Rate Limiting*), se utilizará un hash ciego temporal (`SHA-256(IP + Salt_Diario)`) en una **Redis** que expira automáticamente, impidiendo revertir o rastrear la identidad del autor del feedback.
3. **RN-03 (Restricción de Contenido):** El mensaje no puede estar vacío ni superar los **1000 caracteres**.
4. **RN-04 (Ciclo de Vida del Mensaje):** Un feedback archivado o eliminado no se mostrará en la bandeja principal de entradas activas.