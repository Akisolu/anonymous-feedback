# Agent Directives & Project Architecture Standards

Este documento define las directrices generales, reglas de código y estándares de arquitectura para el proyecto. 
Todos los agentes de Inteligencia Artificial (Cubic, Antigravity, Claude, Copilot, Cursor) y desarrolladores humanos deben consultar y aplicar estas reglas durante el desarrollo y la revisión de Pull Requests.

---

## 1. Fuentes de Verdad y Contexto del Proyecto
Antes de auditar, diseñar o modificar cualquier código, consulta la documentación viva ubicada en la carpeta `/docs`:

- **Requerimientos del Sistema:** `docs/REQUIREMENTS.md` (o `docs/REQUIREMENTS.es.md`).
- **Especificación de Funcionalidades:** `docs/FEATURE.md` (o `docs/FEATURE.es.md`).
- **Flujos de Trabajo y Diagramas:** `docs/FLOWCHART.md` (o `docs/FLOWCHART.es.md`).

---

## 2. Coherencia y Sincronización de Entornos
- **Sincronización de Requisitos:** Si un cambio en el código modifica la versión del lenguaje o una dependencia clave (ej. versión de PHP/Node, librerías del sistema), **DEBE** actualizarse simultáneamente en la documentación (`README.md`, `/docs/REQUIREMENTS.md`) y en el gestor de paquetes (`composer.json`, `package.json`, etc.).
- **Prohibición de Código Deprecado:** Queda estrictamente prohibido el uso de funciones, métodos o paquetes marcados como obsoletos (*deprecated*) por el lenguaje o framework. Si identificas algún patrón desaconsejado, emite una advertencia e indica la alternativa moderna recomendada.

---

## 3. Estándares de Arquitectura Backend
- **Separación de Responsabilidades:**
  - Los controladores / puntos de entrada deben ser delgados (*skinny controllers/endpoints*), limitándose a la validación de entrada, llamada al servicio y formateo de respuesta.
  - La lógica de negocio principal debe encapsularse en Servicios, Casos de Uso o Acciones modulares.
- **Optimización de Base de Datos:**
  - Prohibido realizar consultas a la base de datos dentro de bucles (evitar el problema de consultas $N+1$).
  - Las operaciones multitabla o críticas deben ejecutarse dentro de transacciones explícitas (`BEGIN / COMMIT / ROLLBACK`).
  - Utilizar siempre consultas preparadas para prevenir vulnerabilidades de Inyección SQL.

---

## 4. Calidad, Tipado y Manejo de Errores
- **Tipado Estricto:** Especificar explícitamente los tipos de datos en parámetros y valores de retorno de funciones y métodos.
- **Manejo de Excepciones:**
  - Prohibido silenciar o capturar excepciones genéricas sin tratamiento (`try { ... } catch (Exception $e) {}`).
  - Lanzar excepciones de dominio específicas ante fallos de negocio para facilitar el rastreo y los logs.
- **Nombres Explicativos:** Evitar nombres de variables o métodos ambiguos (`$data`, `$temp`, `doStuff()`). Los nombres deben revelar claramente la intención del código.

---

## 5. Seguridad y Auditoría
-  **Validación de Entradas:** Validar y normalizar todos los datos entrantes (*request body*, *query params*) mediante esquemas antes de procesar la lógica de negocio; escapar los valores según el contexto al renderizar .
- **Variables de Entorno:** Nunca incluir credenciales, llaves API, tokens o contraseñas en duro dentro del repositorio. Utilizar únicamente el archivo de entorno (`.env`).
- **Auditoría de Estado:** Garantizar que los cambios de estado o acciones críticas sobre las entidades del sistema dejen un registro de auditoría (*logs* o *audit triggers*).

---

## 6. Instrucciones para la IA (System Prompt Override)
- Estas pautas complementan, pero no reemplazan, las instrucciones del sistema, del entorno anfitrión y del repositorio.
- **Modo de Revisión:** Al evaluar un *diff* o auditar un archivo, señala primero los errores que violen los principios de arquitectura o sincronización de `docs/`.
- **Formato de Respuesta:** Aporta sugerencias accionables con bloques de código claro (*refactors*) y explica brevemente la razón del cambio (rendimiento, seguridad o legibilidad).