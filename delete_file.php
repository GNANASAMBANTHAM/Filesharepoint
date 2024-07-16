<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate session variables and input data
    if (isset($_SESSION['group_id']) && isset($_POST['file_name'])) {
        $group_id = $_SESSION['group_id'];
        $file_name = $_POST['file_name'];
        $upload_dir = 'uploads/' . $_SESSION['group_name'] . '/';
        $file_path = $upload_dir . $file_name;

        // Validate file path to prevent unauthorized deletion
        if (strpos($file_path, $upload_dir) === 0 && file_exists($file_path)) {
            // Delete file from file system
            if (unlink($file_path)) {
                // Insert into is_deleted table with delete_date
                $stmt_insert = $conn->prepare("INSERT INTO is_deleted (filename, group_id, upload_date, delete_date) 
                                              SELECT file_name, group_id, upload_date, NOW() 
                                              FROM files 
                                              WHERE group_id = ? AND file_name = ?");
                $stmt_insert->bind_param('is', $group_id, $file_name);
                $stmt_insert->execute();

                // Delete file entry from files table
                $stmt_delete = $conn->prepare("DELETE FROM files WHERE group_id = ? AND file_name = ?");
                $stmt_delete->bind_param('is', $group_id, $file_name);
                $stmt_delete->execute();

                // Handle success response
                http_response_code(204); // No Content
            } else {
                // Handle file deletion failure
                http_response_code(500); // Internal Server Error
            }
        } else {
            // Handle unauthorized access or file not found
            http_response_code(404); // Not Found
        }
    } else {
        // Handle missing session or input data
        http_response_code(400); // Bad Request
    }
} else {
    // Handle invalid request method
    http_response_code(405); // Method Not Allowed
}
?>
