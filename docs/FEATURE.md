# Anonymous Feedback App Technical Specifications

## 1. System Overview

The Anonymous Feedback System is a web platform designed for collecting, storing, and managing feedback submitted freely and anonymously by users. The system guarantees the sender's privacy by eliminating authentication mechanisms for message reception, while providing a secure private panel for the administrator (Owner) to view, organize, and manage the feedback flow.

---

## 2. Actor Descriptions

| Actor | Description | System Interaction |
| :--- | :--- | :--- |
**Unauthenticated User (Sender)** | Anyone who accesses the system's public interface without providing identification credentials. | Accesses the homepage, composes, and sends feedback messages anonymously within the stipulated usage limits. |
**Owner (Administrator)** | User responsible for reading, monitoring, and organizing collected feedback. | Log in with valid credentials, access the secure control panel (`/admin/dashboard`), view the feedback list, change its status (read, archived, deleted), and securely log out. |

---

## 3. System Specifications in Gherkin Syntax

### 3.1. Feedback Reception and Sending

```gherkin
@anonymous_feedback_sending
Feature: Sending anonymous feedback from unauthenticated users

  Scenario: Loading the main screen for an unauthenticated user
      Given that an unauthenticated user accesses the system's main URL
      When the page finishes loading
      Then a text area should be displayed for entering feedback
      And the "Submit" button should be displayed
```

```gherkin
@processing_and_storage
Feature: Processing and saving feedback messages

  Scenario: Successful sending and persistence of a feedback message
    Given that an unauthenticated user has entered valid text on the feedback screen
    When the user presses the "Submit" button
    Then the system should process the message
    And save the message in the database
    And display a confirmation message to the user
```

```gherkin
@feedback_validation
Feature: Validations in Anonymous Feedback Submission

  Scenario: Attempt to submit with an empty feedback field
    Given an unauthenticated user is on the home screen
    When the user presses the "Submit" button without entering a message
    Then the system should prevent the form from being submitted
    And should display an error message indicating that the content is required

  Scenario: Attempt to submit exceeding the maximum character limit
    Given an unauthenticated user enters a message that exceeds the allowed character limit
    When the user attempts to submit the form
    Then the system should prevent the submission
    And should notify the user about the character limit being exceeded

```

```gherkin
@rate_limiting
Feature: Feedback sending frequency control

  Scenario: Feedback sent within the allowed limit
    Given an unauthenticated user has sent fewer than 10 messages in the last 10 minutes
    When the user sends a new feedback message
    Then the The system must successfully process and save the message.

  Scenario: Blocked due to exceeding the allowed sending limit
    Given that an unauthenticated user has sent 10 messages in less than 10 minutes
    When the user attempts to send an additional message
    Then the system must reject the transmission
    And must display a message indicating that the sending limit has been reached and the user must wait
```

---

### 3.2. Security and Access Control

```gherkin
@access_control
Feature: Restricting access to protected paths for unauthenticated users

  Scenario: Attempt to access a protected path without authentication
    Given an unauthenticated user attempts to access a URL that requires authentication
    When the system processes the access request
    Then the system must reject the request
    And must redirect the user to the login screen or display an access denied message
```

```gherkin
@owner_authentication
Feature: Owner user login using credentials

  Scenario: Successful login with valid credentials
    Given a user with the Owner role is on the login screen
    When the user enters their valid email or username and password credentials
    And presses the login button
    Then the system must authenticate the credentials
    And must grant access to the administration panel Owner
```

```gherkin
@security_authentication
Feature: Owner login error handling

  Scenario: Login attempt with invalid credentials
    Given a user is on the login screen
    When an incorrect email or password is entered
    And the login button is pressed
    Then the system should deny access
    And display a message indicating that the credentials are invalid
```

```gherkin
@access_control_dashboard
Feature: Protection of the feedback reading view

  Scenario: Access to the dashboard is allowed for a logged-in user
    Given that an Owner user with an active session attempts to access the path "/admin/dashboard"
    When the system validates the user's session
    Then the system should allow access to the feedback reading view

  Scenario: Access to the dashboard is denied for a user without an active session
    Given that an unauthenticated user attempts to access the path "/admin/dashboard"
    When the system validates the user's session
    Then the system should reject the request
    And redirect the user to the home screen session
```

```gherkin
@owner_session_management
Feature: Secure logout for the Owner user

  Scenario: Successful logout
    Given an Owner user with an active session is on the platform
    When the user selects the logout option
    Then the system must securely destroy the active session
    And must invalidate the user's temporary tokens or credentials
    And must redirect the user to the login screen
```

```gherkin
@expiration_sesion
Feature: Automatic session expiration due to inactivity

  Scenario: Session terminated after a period of inactivity
    Given an Owner user has maintained an active session without performing any action for 1 hour
    When the Owner attempts to perform any action or navigate to a route within the dashboard
    Then the system must invalidate the session
    And must redirect the user to the login screen with a notification Expiration
```

---

### 3.3. Feedback Administration and Management

```gherkin
@Management_feedback_dashboard
Feature: Reading and managing feedback on the dashboard

  Scenario: Displaying the list of received feedback
    Given an Owner user with an active session accesses the path "/admin/dashboard"
    When the view finishes loading
    Then the system should display the list of all feedback stored in the database
    And each piece of feedback should display its content and the date or time it was sent
```

```gherkin
@state_management_feedback
Feature: Managing the status of feedback on the dashboard

  Scenario: Marking feedback as read
    Given an Owner user with an active session views the list of feedback in "/admin/dashboard"
    When the Owner selects an unread piece of feedback and marks it as read
    Then the system should update the feedback status to "Read" in the database
    
  Scenario: Archiving feedback
    Given an active Owner user logged in to the dashboard
    When the Owner selects a feedback item and marks it as archived
    Then the system should change the feedback's status to "Archived"
    And hide it from the main view of active feedback items

  Scenario: Deleting feedback
    Given an active Owner user logged in to the dashboard
    When the Owner deletes a selected feedback item
    Then the system should remove the feedback from the database or mark it as deleted
```