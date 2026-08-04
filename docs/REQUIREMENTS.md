# 📋 System Requirements and Design Specification

**Project:** Anonymous Feedback App
**Architecture:** Native PHP (MVC / Pattern-based) + SQL + PHPUnit
**Focus:** Demonstration of Authentication, Authorization, and Minimal Backend Security.

---

## 🎯 System Objective

To provide a one-way, anonymous communication channel where any web user can send comments or feedback without needing to create an account, ensuring that only the authenticated administrator can read and manage the messages from a secure panel.

---

## 🛠️ Functional Requirements (FR)

| ID | Description | Acceptance Criteria |
:--- |:--- |:--- |
**FR-01** | **Anonymous Feedback Submission** | Any unauthenticated visitor must be able to submit feedback through a public form. |
**FR-02** | **Feedback Persistence** | The system must save the submitted message in the database with its respective timestamp (`created_at`). |
**FR-03** | **Administrator Authentication** | The *Owner* user must be able to log in using access credentials (username/email and password). |
**FR-04** | **Protected Administration Panel** | Only the user with an active session should be able to access the feedback view (`/admin/dashboard`). |
**FR-05** | **Logout** | The administrator must be able to securely destroy their session. |
---

## 🔒 Non-Functional Requirements (NFR)

| ID | Category | Description |
| :--- | :--- | :--- |
| **NFR-01** | **Authentication** | Use of secure password hashing using the native `bcrypt` or `argon2` algorithm via `password_hash()`. |
| **NFR-02** | **Authorization** | Middleware or strict session checking (`$_SESSION`) in controllers protected with HTTP redirection `401/403` to login if no valid session exists. |
| **NFR-03** | **Security (Sanitization)** | Sanitization of input against **XSS** vulnerabilities (`htmlspecialchars`) before rendering messages to the panel. |
| **NFR-04** | **Security (SQL Injection)** | Mandatory use of prepared statements with PDO for all database interactions. |
| **NFR-05** | **Session Security** | Session ID regeneration (`session_regenerate_id(true)`) upon authentication to prevent session fixation attacks. |

---

## ⚖️ Business Rules (BR)

1. **BR-01 (Feedback Privacy):** Unauthenticated and/or unauthorized individuals cannot view feedback submitted by users or query management API endpoints.

2. **BR-02 (Strict Anonymity):** The system **must not store** IP addresses or any identifiable information about the sender when saving feedback, guaranteeing absolute anonymity.

3. **BR-03 (Content Restriction):** Feedback cannot be sent empty or exceed a predefined character limit (e.g., maximum 1000 characters).