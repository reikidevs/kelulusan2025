<?php
// Include database connection
require_once 'config/database.php';

// Function to check if column exists
function column_exists($conn, $table, $column) {
    $sql = "SHOW COLUMNS FROM `$table` LIKE '$column'";
    $result = $conn->query($sql);
    return $result->num_rows > 0;
}

// Check and fix students table structure
echo "<h2>Memperbaiki Struktur Tabel Students</h2>";

// Check if 'administrasi' column exists and 'status_administrasi' does not
if (column_exists($conn, 'students', 'administrasi') && !column_exists($conn, 'students', 'status_administrasi')) {
    // Rename column from 'administrasi' to 'status_administrasi'
    $sql = "ALTER TABLE `students` CHANGE `administrasi` `status_administrasi` TINYINT(1) DEFAULT 0 NOT NULL COMMENT '0=Belum Lunas, 1=Lunas'";
    if ($conn->query($sql) === TRUE) {
        echo "<p>Kolom 'administrasi' berhasil diubah menjadi 'status_administrasi'</p>";
    } else {
        echo "<p>Error mengubah kolom: " . $conn->error . "</p>";
    }
} else {
    // If 'status_administrasi' doesn't exist, add it
    if (!column_exists($conn, 'students', 'status_administrasi')) {
        $sql = "ALTER TABLE `students` ADD COLUMN `status_administrasi` TINYINT(1) DEFAULT 0 NOT NULL COMMENT '0=Belum Lunas, 1=Lunas' AFTER `status`";
        if ($conn->query($sql) === TRUE) {
            echo "<p>Kolom 'status_administrasi' berhasil ditambahkan</p>";
        } else {
            echo "<p>Error menambahkan kolom: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>Kolom 'status_administrasi' sudah ada</p>";
    }
}

// Check if 'password' column exists
if (!column_exists($conn, 'students', 'password')) {
    $sql = "ALTER TABLE `students` ADD COLUMN `password` VARCHAR(100) NOT NULL AFTER `exam_number`";
    if ($conn->query($sql) === TRUE) {
        echo "<p>Kolom 'password' berhasil ditambahkan</p>";
        
        // Update existing records with random passwords
        $sql = "UPDATE `students` SET `password` = CONCAT(SUBSTRING('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', RAND()*36+1, 1),
                                                         SUBSTRING('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', RAND()*36+1, 1),
                                                         SUBSTRING('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', RAND()*36+1, 1),
                                                         SUBSTRING('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', RAND()*36+1, 1),
                                                         SUBSTRING('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', RAND()*36+1, 1),
                                                         SUBSTRING('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', RAND()*36+1, 1))
                WHERE `password` = '' OR `password` IS NULL";
        if ($conn->query($sql) === TRUE) {
            echo "<p>Password acak berhasil dibuat untuk data siswa yang sudah ada</p>";
        } else {
            echo "<p>Error memperbarui password: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>Error menambahkan kolom: " . $conn->error . "</p>";
    }
} else {
    echo "<p>Kolom 'password' sudah ada</p>";
}

// Check if 'jurusan' column exists
if (!column_exists($conn, 'students', 'jurusan')) {
    $sql = "ALTER TABLE `students` ADD COLUMN `jurusan` VARCHAR(100) NOT NULL DEFAULT 'Belum diisi' AFTER `class`";
    if ($conn->query($sql) === TRUE) {
        echo "<p>Kolom 'jurusan' berhasil ditambahkan</p>";
    } else {
        echo "<p>Error menambahkan kolom: " . $conn->error . "</p>";
    }
} else {
    echo "<p>Kolom 'jurusan' sudah ada</p>";
}

// Show current table structure
echo "<h3>Struktur Tabel Students Saat Ini:</h3>";
$result = $conn->query("DESCRIBE students");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . $row['Default'] . "</td>";
    echo "<td>" . $row['Extra'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<p>Selesai memperbaiki tabel students.</p>";
echo "<p><a href='admin/students.php'>Kembali ke halaman siswa</a></p>";
?>
