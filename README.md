# TaskFlow - AI-Powered To-Do List Web App

## 📌 Prerequisites
Before setting up TaskFlow, make sure you have the following installed:
- **XAMPP** (Download from [apachefriends.org](https://www.apachefriends.org/))

## 🔧 Setup Instructions

### 1️⃣ Download and Move the Project Folder
1. Clone or download the repository:
   ```bash
   git clone https://github.com/your-username/TaskFlow.git
   ```
   *OR* manually download the ZIP and extract it.

2. Move the entire **TaskFlow** folder into `htdocs` inside your XAMPP directory:
   - Example path: `C:\xampp\htdocs\TaskFlow` (Windows)

### 2️⃣ Start XAMPP Services
1. Open **XAMPP Control Panel**.
2. Start **Apache** and **MySQL** by clicking the **Start** button.
3. Click **Admin** next to MySQL to open phpMyAdmin in your browser.

### 3️⃣ Create the Database
1. In **phpMyAdmin**, click **New** to create a new database.
2. Open the `create_database.txt` file inside the **TaskFlow** folder.
3. Copy and paste the SQL query into phpMyAdmin and run it to set up the database.

### 4️⃣ Run the Web App
1. Open your web browser.
2. Type `http://localhost/TaskFlow/` in the address bar and press **Enter**.
3. TaskFlow should now be running! 🚀

## ⚠️ Troubleshooting
- If Apache/MySQL fails to start, make sure no other apps (like Skype) are using ports 80/3306.
- If you see a database error, double-check that the SQL script was executed correctly in phpMyAdmin.

---
Now you're all set! 🎉 Enjoy using **TaskFlow** to manage your tasks efficiently!
