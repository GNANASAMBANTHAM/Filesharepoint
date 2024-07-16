<?php
session_start();
require '../db.php';

// Ensure the user is an admin (adjust security measures as needed)
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: index.php');
    exit();
}

// Fetch group details
$stmt = $conn->prepare("SELECT id, name, password FROM groups");
$stmt->execute();
$groups = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Console</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* General Styles */
        body {
            margin: 0;
            padding: 0;
            background-color: #dee6ed;
            font-family: 'Arial', sans-serif;
        }
        .container {
            width: 70%;
            margin: auto;
            padding: 50px;
            box-sizing: border-box;
            background-color: #fff;
            justify-content: center;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 100px;
            font-size: small;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 2.8rem;
            color: #333;
        }
        .header .close-icon {
            font-size: 1.2rem;
            color: #999;
            cursor: pointer;
            transition: color 0.3s;
        }
        .header .close-icon:hover {
            color: #333;
        }
        .folder {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .folder:hover {
            background-color: #dee6ed;
        }
        .folder-details {
            display: none;
            margin-top: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .folder-details.show {
            display: block;
            background-color: #dee6ed;
        }
        .folder-details h3 {
            margin-bottom: 10px;
        }
        .file-table {
         width: 100%;
         border-collapse: collapse;
         margin-top: 20px;
         border: 1px solid ;
         }
         .file-table th, .file-table td {
         padding: 12px 12px;
         border: 1px solid #fff;
         text-align: center;
         font-size: small;
        
         justify-content: center;
            
         
         }
         .file-table th {
         background-color: #007bff;
         cursor: pointer;
         color:#;
         border: 1px solid #ddd;
         align-items: center;   
         }
         
        .file-actions {
            justify-content: center;
            align-items: center;
        }
        .file-actions button {
            display: inline-block;
            margin: 0 5px;
            padding: 8px 12px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .file-actions button i {
            margin-right: 2px; /* Adjust icon spacing */
        }
        .danger-theme {
            background-color: #ffcccc;
            color: #f44336; /* Red color for danger theme */
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Admin Console</h1>
        <a href="index.php" class="close-icon">Logout</a>
    </div>
    <?php foreach ($groups as $group) { ?>
        <div class="folder" onclick="toggleFolder('<?php echo $group['id']; ?>')">
            <h3>Folder: <?php echo htmlspecialchars($group['name']); ?></h3>
            <p>Password: <?php echo htmlspecialchars($group['password']); ?></p>
        </div>
        <div class="folder-details" id="folder_<?php echo $group['id']; ?>">
            <div class="files">
                <h3>Using Files</h3>
                <table class="file-table">
                    <thead>
                    <tr>
                        <th onclick="sortTable(0)">File Name <span>&#x2195;</span></th>
                        <th onclick="sortTable(1)">File Type <span>&#x2195;</span></th>
                        <th onclick="sortTable(2)">Upload Date <span>&#x2195;</span></th>
                        <th>Action <span>&#x2195;</span></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    // Fetch files from files table
                    $stmt_files = $conn->prepare("SELECT file_name, file_path, upload_date FROM files WHERE group_id = ?");
                    if (!$stmt_files) {
                        echo '<tr><td colspan="5">Failed to prepare statement.</td></tr>';
                    } else {
                        $stmt_files->bind_param('i', $group['id']);
                        if (!$stmt_files->execute()) {
                            echo '<tr><td colspan="5">Error executing query.</td></tr>';
                        } else {
                            $result_files = $stmt_files->get_result();

                            if ($result_files->num_rows === 0) {
                                echo '<tr><td colspan="5">No files found for this group.</td></tr>';
                            } else {
                                while ($file = $result_files->fetch_assoc()) {
                                    $file_name = htmlspecialchars($file['file_name']);
                                    $file_path = htmlspecialchars($file['file_path']);
                                    $upload_date = htmlspecialchars(date("d-m-Y", strtotime($file['upload_date'])));

                                    $file_type = pathinfo($file_path, PATHINFO_EXTENSION);
                                    ?>
                                    <tr>
                                        <td><?php echo $file_name; ?></td>
                                        <td><?php echo $file_type; ?></td>
                                        <td><?php echo $upload_date; ?></td>
                                        <td class="file-actions">
                                            <form action="../download.php" method="get" style="display: inline-block;">
                                                <input type="hidden" name="file" value="<?php echo $file_path; ?>">
                                                <button type="submit" name="download_file"><i class="fas fa-download"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php }
                        }
                    }
                    ?>
                    </tbody>
                </table>

                <!-- Deleted Files Section -->
                <h3>Deleted Files</h3>
                <table class="file-table">
                    <thead>
                    <tr>
                        <th>File Name</th>
                        <th>File Type</th>
                        <th>Upload Date</th>
                        <th>Delete Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    // Fetch deleted files from is_deleted table
                    $stmt_deleted = $conn->prepare("SELECT filename, file_path, upload_date, delete_date FROM is_deleted WHERE group_id = ?");
                    if (!$stmt_deleted) {
                        echo '<tr><td colspan="4">Failed to prepare statement.</td></tr>';
                    } else {
                        $stmt_deleted->bind_param('i', $group['id']);
                        if (!$stmt_deleted->execute()) {
                            echo '<tr><td colspan="4">Error executing query.</td></tr>';
                        } else {
                            $result_deleted = $stmt_deleted->get_result();

                            if ($result_deleted->num_rows === 0) {
                                echo '<tr><td colspan="4">No deleted files found for this group.</td></tr>';
                            } else {
                                while ($deleted_file = $result_deleted->fetch_assoc()) {
                                    $deleted_file_name = htmlspecialchars($deleted_file['filename']);
                                    $deleted_file_path = htmlspecialchars($deleted_file['file_path']);
                                    $file_type = pathinfo($file_path, PATHINFO_EXTENSION);
                                    $deleted_upload_date = htmlspecialchars(date("d-m-Y", strtotime($deleted_file['upload_date'])));
                                    $deleted_delete_date = htmlspecialchars(date("d-m-Y", strtotime($deleted_file['delete_date'])));
                                    ?>
                                    <tr>
                                        <td><?php echo $deleted_file_name; ?></td>
                                        <td><?php echo $file_type; ?></td>
                                        <td><?php echo $deleted_upload_date; ?></td>
                                        <td><?php echo $deleted_delete_date; ?></td>
                                    </tr>
                                <?php } ?>
                            <?php }
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
        <br>
    <?php } ?>
</div>

<script>
    function toggleFolder(groupId) {
        const folderDetails = document.getElementById(`folder_${groupId}`);
        if (folderDetails.classList.contains('show')) {
            folderDetails.classList.remove('show');
        } else {
            folderDetails.classList.add('show');
        }
    }

    // Function to sort table by column index
    function sortTable(colIndex) {
        const tables = document.querySelectorAll('.file-table');

        tables.forEach(table => {
            const rows = Array.from(table.rows).slice(1); // Skip header row

            rows.sort((rowA, rowB) => {
                const cellA = rowA.cells[colIndex].textContent.trim();
                const cellB = rowB.cells[colIndex].textContent.trim();

                if (colIndex === 2 || colIndex === 3) {
                    // Sort by date or size
                    return new Date(cellA.split('-').reverse().join('-')) - new Date(cellB.split('-').reverse().join('-'));
                } else {
                    // Sort alphabetically
                    return cellA.localeCompare(cellB);
                }
            });

            rows.forEach(row => table.tBodies[0].appendChild(row));
        });
    }

    // Example delete file function (implement according to your needs)
    function deleteFile(fileName) {
        if (confirm(`Are you sure you want to delete ${fileName}?`)) {
            // Implement your delete logic here
            alert(`File ${fileName} deleted successfully!`);
            // You might want to reload or update the table after deletion
        }
    }

    // Example restore file function (implement according to your needs)
    function restoreFile(fileName) {
        if (confirm(`Are you sure you want to restore ${fileName}?`)) {
            // Implement your restore logic here
            alert(`File ${fileName} restored successfully!`);
            // You might want to reload or update the table after restoration
        }
    }
</script>
</body>
</html>
