# PI-WEB-JAVA - 3rd Year Web Integration Project

## Getting Started

Follow these steps to set up the project on your local machine:

### 1. Install GitHub Desktop
If you don't already have it, download and install [GitHub Desktop](https://desktop.github.com/).

### 2. Clone the Project
Clone the repository to your local machine using GitHub Desktop.

### 3. Create a New Branch
In GitHub Desktop, create a new branch for your work.

### 4. Merge Main Branch
Merge the latest updates from the main branch into your new branch to stay up to date.

### 5. Open Your Branch in an Editor
You can use any editor, but **PhpStorm** is recommended for this project. Alternatively, you can use [VS Code](https://code.visualstudio.com/).

### 6. Open Terminal in Project Directory
Navigate to your project directory and open the terminal.

### 7. Install Symfony
In the terminal, run the following commands to install Symfony:

```bash
symfony check:requirements
composer require webapp
```

### 8. Start the Server
Start your local server to test the application using the command:

```bash
symfony server:start
```

---

## System Mailing (Local)

To enable local mailing, use the following command:

```bash
./bin/mailpit
```

---

## Community Module Features

### - Interest-based Suggestions
Users can receive suggestions based on their interests.

### - Community Creation Request
- Users can request the creation of a new community.
- When a request is made, an **automatic email** will be sent to the admin.
- A new task is added to the admin dashboard to **approve** or **reject** the request.

### - Role Verification
- **Each role has specific functionalities.**
- **Event/Chat Access Control**
- **Url navigation security**

### - Link Sharing
- Copy a link directly.

### - QR Code Sharing
- Share the project via QR code.
- Option to **download** the QR code.

---
