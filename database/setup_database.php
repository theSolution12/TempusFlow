<?php
// Check if this script is being run directly or included
$is_direct_access = (basename($_SERVER['SCRIPT_FILENAME']) == basename(__FILE__));

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";

// Create connection to MySQL server (without database)
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    if ($is_direct_access) {
        die("Connection failed: " . $conn->connect_error);
    } else {
        return false; // Silent failure when included
    }
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS notesdb";
if ($conn->query($sql) === TRUE) {
    if ($is_direct_access) echo "Database created successfully or already exists<br>";
} else {
    if ($is_direct_access) {
        echo "Error creating database: " . $conn->error . "<br>";
        die();
    } else {
        return false; // Silent failure when included
    }
}

$conn->select_db("notesdb");

$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reset_token VARCHAR(255) DEFAULT NULL,
    token_expiry DATETIME DEFAULT NULL
);
";

if ($conn->query($sql) === TRUE) {
    if ($is_direct_access) echo "Users table created successfully or already exists<br>";
} else {
    if ($is_direct_access) echo "Error creating users table: " . $conn->error . "<br>";
}

// Create notes table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS notes (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    if ($is_direct_access) echo "Notes table created successfully or already exists<br>";
} else {
    if ($is_direct_access) echo "Error creating notes table: " . $conn->error . "<br>";
}

// Create tasks table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS tasks (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    due_date DATETIME NOT NULL,
    status ENUM('pending', 'in-progress', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    if ($is_direct_access) echo "Tasks table created successfully or already exists<br>";
} else {
    if ($is_direct_access) echo "Error creating tasks table: " . $conn->error . "<br>";
}

// Create events table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS events (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    event_end_time TIME,
    location VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    if ($is_direct_access) echo "Events table created successfully or already exists<br>";
} else {
    if ($is_direct_access) echo "Error creating events table: " . $conn->error . "<br>";
}

// Create notes table
$sql_notes = "CREATE TABLE IF NOT EXISTS notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql_notes) === TRUE) {
    echo "Notes table created successfully<br>";
} else {
    echo "Error creating notes table: " . $conn->error . "<br>";
}

// Create attachments table for note files
$sql_attachments = "CREATE TABLE IF NOT EXISTS attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    note_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(100) NOT NULL,
    file_size INT NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE
)";

if ($conn->query($sql_attachments) === TRUE) {
    echo "Attachments table created successfully<br>";
} else {
    echo "Error creating attachments table: " . $conn->error . "<br>";
}

// Create uploads directory if it doesn't exist
$upload_dir = '../uploads/';
if (!file_exists($upload_dir)) {
    if (mkdir($upload_dir, 0777, true)) {
        echo "Uploads directory created successfully<br>";
    } else {
        echo "Error creating uploads directory<br>";
    }
}

// Close connection
$conn->close();

if ($is_direct_access) {
    echo "<br>Database setup completed successfully!";
    header("Location: ../main/main.php");
    exit();
}

return true; // Success
?>