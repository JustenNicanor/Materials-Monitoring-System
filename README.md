# 📦 Material Monitoring System

The **Material Monitoring System** is a simple web-based inventory tracking system designed to manage and monitor the inflow and outflow of materials. Built for local development using **XAMPP**, this system helps track quantity, material type, and responsible personnel.

## 🚀 Features

- Record material entries and withdrawals
- Monitor total balance quantity
- Track user responsible for updates
- Timestamped logs for transparency

---

## 🛠️ Setup Instructions

### 📌 Prerequisites

- [XAMPP](https://www.apachefriends.org/index.html) installed on your local machine
- PHP and MySQL enabled (included in XAMPP)
- A code editor (e.g., VSCode, Sublime Text)

---

### 🗄️ Database Setup using XAMPP

#### Step 1: Create the Database

1. Open **XAMPP** and start both **Apache** and **MySQL** modules.
2. In your browser, go to [http://localhost/phpmyadmin/](http://localhost/phpmyadmin/).
3. Click the **"Databases"** tab.
4. Under **Create database**, enter a name (e.g., `materials_db`).
5. Click **Create**.

#### Step 2: Create the Table

1. Click on your newly created database from the left sidebar.
2. Go to the **"SQL"** tab.
3. Paste the SQL query below:

```sql
CREATE TABLE materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    material_type VARCHAR(50) NOT NULL,
    datetime DATETIME NOT NULL,
    qty_in INT DEFAULT 0,
    qty_out INT DEFAULT 0,
    person_in_charge VARCHAR(100),
    total_bal_qty INT DEFAULT 0
);
```

### 📸 Project Preview

Below is a screenshot of the Material Monitoring System interface:

![Material Monitoring System Screenshot](mms1(1).png)


