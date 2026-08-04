# 📋 Especificación de Requisitos y Diseño de Sistema

**Proyecto:** Anonymous Feedback App  
**Arquitectura:** PHP Nativo (MVC / Pattern-based) + SQL + PHPUnit  
**Enfoque:** Demostración de Autenticación, Autorización y Seguridad Mínima Backend.

---

## 🎯 Objetivo del Sistema

Proporcionar un canal de comunicación unidireccional y anónimo donde cualquier usuario web pueda enviar comentarios o retroalimentación sin necesidad de crear una cuenta, garantizando que únicamente el administrador autenticado pueda leer y gestionar los mensajes desde un panel protegido.

---

## 🛠️ Requisitos Funcionales (RF)

| ID | Descripción | Criterio de Aceptación |
| :--- | :--- | :--- |
| **RF-01** | **Envío de Feedback Anónimo** | Cualquier visitante no autenticado debe poder enviar un mensaje de retroalimentación a través de un formulario público. |
| **RF-02** | **Persistencia de Feedback** | El sistema debe guardar el mensaje enviado en la base de datos con su respectiva marca temporal (`created_at`). |
| **RF-03** | **Autenticación del Administrador** | El usuario *Owner* debe poder iniciar sesión mediante credenciales de acceso (usuario/correo y contraseña). |
| **RF-04** | **Panel de Administración Protected** | Solo el usuario con sesión activa debe poder acceder a la vista de lectura de feedbacks (`/admin/dashboard`). |
| **RF-05** | **Cierre de Sesión (Logout)** | El administrador debe poder destruir su sesión de forma segura. |

---

## 🔒 Requisitos No Funcionales (RNF)

| ID | Categoría | Descripción |
| :--- | :--- | :--- |
| **RNF-01** | **Autenticación** | Uso de hashing seguro de contraseñas utilizando el algoritmo nativo `bcrypt` o `argon2` vía `password_hash()`. |
| **RNF-02** | **Autorización** | Middleware o comprobación estricta de sesión (`$_SESSION`) en controladores protegidos con redirección HTTP `401/403` a login si no existe sesión válida. |
| **RNF-03** | **Seguridad (Sanitización)** | Sanitización de entradas contra vulnerabilidades **XSS** (`htmlspecialchars`) antes de renderizar mensajes en el panel. |
| **RNF-04** | **Seguridad (SQL Injection)** | Uso obligatorio de consultas preparadas (*Prepared Statements*) con PDO para toda interacción con la base de datos. |
| **RNF-05** | **Seguridad de Sesión** | Regeneración de ID de sesión (`session_regenerate_id(true)`) al autenticarse para prevenir ataques de *Session Fixation*. |

---

## ⚖️ Reglas de Negocio (RN)

1. **RN-01 (Privacidad del Feedback):** Una persona no autenticada y/o no autorizada no puede ver el feedback enviado por los usuarios ni consultar endpoints de la API de administración.
2. **RN-02 (Anonimato Estricto):** El sistema **no debe almacenar** direcciones IP ni datos identificables del remitente al guardar el feedback, garantizando anonimato absoluto.
3. **RN-03 (Restricción de Contenido):** Un feedback no puede enviarse vacío ni exceder un límite predefinido de caracteres (ej. máximo 1000 caracteres).