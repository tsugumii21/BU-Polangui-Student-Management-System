<div align="center">
  <h1>Bicol University Polangui - Student Management System (S.M.S)</h1>
</div>

<div align="center">
  <img src="frontend/images/bup-logo.png" alt="BU Logo" width="150" height="auto" />
  <h3>Excellence in Education, Character, and Service</h3>
  <p>A comprehensive, production-ready Student Management System built with Native PHP and MySQL, designed to manage student records efficiently.</p>

[![Version](https://img.shields.io/badge/version-1.0.0-blue.svg?style=for-the-badge)]()

  <br/>

![HTML5](https://img.shields.io/badge/html5-%23E34F26.svg?style=flat&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/css3-%231572B6.svg?style=flat&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/javascript-%23323330.svg?style=flat&logo=javascript&logoColor=%23F7DF1E)
![PHP](https://img.shields.io/badge/php-%23777BB4.svg?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/mysql-%2300f.svg?style=flat&logo=mysql&logoColor=white)
![XAMPP](https://img.shields.io/badge/xampp-%23FB7A24.svg?style=flat&logo=xampp&logoColor=white)

</div>

## 📋 Overview

The **Bicol University Polangui S.M.S** is a robust web application designed to streamline the management of student information. It serves as a centralized platform for administrators to maintain accurate records, including course details, year levels, and department assignments, while providing a modern and accessible interface for both admins and users.

## ✨ Features

### 🎓 Admin Dashboard

- **Overview Statistics:** Real-time view of total students, system users, and recent activity.
- **Student Management:**
  - Add new students with **auto-assigned departments** based on course selection.
  - Comprehensive editing (Name, ID, Course, Year, Block, Gender).
  - Secure profile photo management (stored as BLOBs).
- **Directory Control:**
  - Delete records and manage data integrity.

### 📂 Students Directory

- **Advanced Filtering:** Dynamic filters for Department, Year Level, Block, and Gender.
- **Smart Sorting:** Sort by Name, Date Added, Recently Modified, Year Level, and more.
- **Global Search:** Instant search functionality for Student Names and IDs.

### 👤 User Dashboard

- **Read-Only Access:** Browse student directories securely.
- **Profile Management:** Update personal credentials and profile avatar.
- **Simplified Interface:** Focused view for non-admin users.

### 🔐 Security & Tech

- **Authentication:** Secure Login/Signup with password hashing (`password_hash`).
- **Role-Based Access:** Distinct Admin and User privileges.
- **Database:** Optimized MySQL schema with relationships and constraints.

## 🚀 Getting Started

### Prerequisites

- **XAMPP** (or any local server with Apache & MySQL)
- **Git** (optional, for cloning)
- A modern web browser

### Installation

1.  **Clone or Download**
    Navigate to your XAMPP `htdocs` directory and clone the repo:

    ```bash
    cd C:\xampp\htdocs\
    git clone https://github.com/tsugumii21/BU-Polangui-Student-Management-System.git student_management_system
    ```

    _(Or simply create a folder `student_management_system` and paste the files there)_

2.  **Setup Database**

    - Open **XAMPP Control Panel** -> Start **Apache** & **MySQL**.
    - Go to `http://localhost/phpmyadmin/`.
    - Create a database named: **`student_management_db`**.
    - Import the **`database/setup.sql`** file provided in the project.

3.  **Configure (Optional)**

    - Check `database/config.php` if you have a custom MySQL password. Default is empty for `root`.

4.  **Launch**
    - Open browser and go to: **`http://localhost/student_management_system/frontend/`**

### Login Credentials

| Role      | Username             | Password        |
| --------- | -------------------- | --------------- |
| **Admin** | `admin`              | `123456`        |
| **User**  | _(Sign up via form)_ | _(Your Choice)_ |

## 📂 Project Structure

```
student_management_system/
├── backend/          # PHP Logic (Auth, CRUD, Image Handling)
├── database/         # SQL Setup & Config
├── frontend/         # UI Components
│   ├── css/          # Stylesheets
│   ├── js/           # Scripts
│   ├── images/       # Assets
│   ├── includes/     # Shared Layouts (Header/Sidebar)
│   └── *.php         # Application Views
└── README.md         # Documentation
```

---

<div align="center">
  <p>© 2025 Bicol University Polangui. All Rights Reserved.</p>
</div>
