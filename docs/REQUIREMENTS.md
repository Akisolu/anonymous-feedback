# 📋 System Requirements and Design Specification

**Project:** Anonymous Feedback App  
**Architecture:** Native PHP (MVC / Pattern-based) + SQL + PHPUnit  
**Focus:** Authentication, Authorization, Backend Security, and Automated Testing.

---

## 🎯 System Objective

To provide a one-way, anonymous communication channel where any web user can submit feedback without registration, ensuring that only the authenticated administrator can read, categorize, and manage messages from a secure panel.

---

## 🛠️ Functional Requirements (FR)

| ID | Description | Acceptance Criteria |
:--- |:--- |:--- |
**FR-01** | **Anonymous Feedback Submission** | Any unauthenticated visitor can submit a message through the public form. |
**RF-02** | **Feedback Persistence** | The system saves the message in the database with an initial status of `unread` and a timestamp (`created_at`). |
**RF-03** | **Administrator Authentication** | The *Owner* user logs in using credentials (username/email and password). |
**RF-04** | **Protected Administration Panel** | Only the logged-in user can access the `/admin/dashboard` view. |
**RF-05** | **Feedback Status Management** | The *Owner* can change the status of feedback to `read`, `archived`, or mark it as `deleted`. |
**RF-06** | **Feedback Deletion** | The *Owner* can permanently remove or perform a soft delete (*soft delete*) of a message. |
**RF-07** | **Logout** | The *Owner* can securely destroy their active session. |

---

## 🔒 Non-Functional Requirements (RNF)

| ID | Category | Description |
:--- |:--- |:--- |
**RNF-01** | **Authentication** | Secure password hashing using `password_hash()` with `bcrypt` or `argon2`. |
**RNF-02** | **Authorization** | Strict verification of `$_SESSION` in protected controllers with HTTP `401/403` redirects to login. |
**RNF-03** | **Sanitization (XSS)** | Sanitization of inputs with `htmlspecialchars()` before rendering messages to the panel. 
**RNF-04** | **Secure Queries (SQLi)** | Mandatory use of *Prepared Statements* via PDO in all database interactions. |
**RNF-05** | **Session Security** | Session ID regeneration (`session_regenerate_id(true)`) upon authentication to prevent *Session Fixation*. |
**RNF-06** | **Inactivity Expiration** | Automatic session destruction after **1 hour (3600s)** of inactivity from the *Owner*. |
**RNF-07** | **Resistor Control (Redis)** | Rate limiting of requests via Redis in RAM to a maximum of **10 requests every 10 minutes per anonymous identifier**. |

---

## ⚖️ Business Rules (BN)

1. **BN-01 (Feedback Privacy):** Administrative routes and endpoints are inaccessible to unauthenticated users.
2. **BN-02 (Anonymity and Privacy Decoupled):** The system will not store IP addresses in plain text or associated with saved messages. For rate limiting, a temporary blind hash (`SHA-256(IP + Daily_Salt)`) will be used on a Redis server that expires automatically, preventing the reversal or tracing of the feedback author's identity.
3. **BN-03 (Content Restriction):** Messages cannot be empty or exceed 1000 characters.
4. **BN-04 (Message Lifecycle):** Archived or deleted feedback will not appear in the main inbox of active messages.