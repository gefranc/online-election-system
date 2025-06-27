# 🗳️ Church Election Management System

This is a web-based Election Management System designed to facilitate online voting for church-based organizations. It includes secure admin management, voter registration, candidate management, voting functionality, and real-time results tracking.

---

## 🚀 Features

- 🧑💼 Admin and Voter Authentication (with session management)
- 🌗 Dark/Light Mode Toggle (saved in localStorage)
- 📷 Admin and Voter Profile Photo Upload
- 🗃️ CRUD Management for:
  - Positions
  - Candidates
  - Voters
  - Votes Cast
  - Ballot Settings
  - Election Title
- 🗳️ Vote Casting (with confirmation modals and restrictions)
- 📈 Election Analytics and History (with charts)
- 📅 Election Countdown Timer
- 📬 Email Verification (optional)
- 📱 Fully Responsive Design (Bootstrap 5)

---

## 🛠️ Installation

1. **Clone or Download the Project**
   ```bash
   git clone https://github.com/yourusername/church-voting-system.git
2. Move Project to Web Server

  - Place it in your XAMPP htdocs or your live hosting root directory.

3. Set Up the Database

    - Import the provided SQL file:
    -- /database/voting_system.sql
   
    - Use phpMyAdmin or CLI: -- mysql -u root -p church_voting_system < database/voting_system.sql
  
4. Update Database Connection

  - Modify includes/config.php:
  $conn = new mysqli("localhost", "root", "", "voting_system");
5. Set File Upload Permissions:

  - Ensure /uploads/admin_photos/ and /uploads/voter_photos/ folders are writable.

6. Start the Application

  - Open your browser:
  [http://localhost:3000/index.php](http://localhost/voting-system/index.php)

---

👨💼 Default Admin Login:
- Username - church
- Password - church

⚠️ Change the default password after first login.

---

💡 Technologies Used
- Frontend: HTML5, CSS3, Bootstrap 5, JavaScript

- Backend: PHP (OOP & MySQLi)

- Database: MySQL

- Charts: Chart.js or ApexCharts (for analytics)

- Security: Password Hashing (bcrypt), Session Management

---
🔐 Security Tips
- Use HTTPS in production

- Store sensitive config variables outside web root

- Sanitize user inputs (already done in form handlers)

- Disable file uploads outside allowed types (e.g., only .jpg/.png)

---
Developed by: Franc

---
📜 License:
- This project is open-source and free to use under the MIT License.
