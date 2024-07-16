<?php
   session_start();
   require 'db.php';
   $group_name = '';
   $group_id = isset($_SESSION['group_id']) ? $_SESSION['group_id'] : 0;
   
   if ($group_id) {
       $stmt = $conn->prepare("SELECT name FROM groups WHERE id = ?");
       $stmt->bind_param('i', $group_id);
       $stmt->execute();
       $result = $stmt->get_result();
       $group = $result->fetch_assoc();
   
       if ($group) {
           $group_name = $group['name'];
       }
   
       $upload_dir = 'uploads/' . $group_name . '/';
       if (!is_dir($upload_dir)) {
           mkdir($upload_dir, 0777, true);
       }
   
       // Handle file upload
       if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
           $file_name = basename($_FILES['file']['name']);
           $upload_file = $upload_dir . $file_name;
   
           if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_file)) {
               $stmt = $conn->prepare("INSERT INTO files (group_id, file_name, file_path) VALUES (?, ?, ?)");
               $stmt->bind_param('iss', $group_id, $file_name, $upload_file);
               $stmt->execute();
           }
       }
   
       // Get list of files
       $files = [];
       if (is_dir($upload_dir)) {
           $files = array_diff(scandir($upload_dir), array('..', '.'));
   
           // Sort files by name (ascending) by default
           natcasesort($files);
       }
   }
   ?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="UTF-8">
      <title><?php echo htmlspecialchars($group_name); ?> - Files</title>
      <script>
        // JavaScript to handle back button
        window.onload = function() {
            if (window.history && window.history.pushState) {
                window.history.pushState(null, null, document.URL);
                window.addEventListener('popstate', function() {
                    window.location.href = 'index.php';
                });
            }
        };
    </script>
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-<hash>" crossorigin="anonymous" />
      <link rel="stylesheet" href="style.css">
      <style>
         .container {
         width: 750px;
         box-sizing: border-box;
         }
         .drag-area {
         border: 2px dashed #ccc;
         border-radius: 10px;
         padding: 20px;
         text-align: center;
         margin-bottom: 20px;
         display: none; 
         }
         .drag-area.dragover {
         background-color: #f0f0f0;
         }
         .header {
         display: flex;
         justify-content: space-between;
         align-items: center;
         margin-bottom: 20px;
         }
         .header h2 {
         margin: 0;
         font-size: 1.8rem;
         color: #333;
         }
         .file-table {
         width: 100%;
         border-collapse: collapse;
         margin-top: 20px;
         }
         .file-table th, .file-table td {
         padding: 12px 12px;
         border: 1px solid #ddd;
         text-align: center;
         font-size: small;
         }
         .file-table th {
         background-color: #007bff;
         cursor: pointer;
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
         /* Adjust icon spacing */
         }
      </style>
   </head>
   <body>
      <div class="container">
         <div class="header">
            <h2><?php echo htmlspecialchars($group_name); ?> Folder</h2>
            <a href="logout.php" class="close-icon">Logout</a>
         </div>
         <button id="toggleUploadBtn">Upload File</button>
         <br>
         <br>
         <div class="drag-area" id="drag-area">
            <h3>Drag & Drop to Upload File</h3>
            <p>or</p>
            <button onclick="document.getElementById('fileInput').click()">Select File</button>
            <input type="file" id="fileInput" name="file" style="display: none;">
         </div>
         <form id="uploadForm" action="group.php" method="post" enctype="multipart/form-data" style="display: none;">
            <input type="file" name="file" id="hiddenFileInput">
            <input type="submit" name="upload_file" value="Upload">
         </form>
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
               <?php foreach ($files as $file) {
                  $file_path = $upload_dir . $file;
                  $file_info = pathinfo($file_path);
                  $file_type = pathinfo($file_path, PATHINFO_EXTENSION);
                  $upload_date = date("d-m-Y", filemtime($file_path));
                  ?>
               <tr>
                  <td><?php echo htmlspecialchars($file); ?></td>
                  <td><?php echo htmlspecialchars($file_type); ?></td>
                  <td><?php echo htmlspecialchars($upload_date); ?></td>
                  <td class="file-actions">
                     <button onclick="downloadFile('<?php echo htmlspecialchars($file_path); ?>')"><i class="fas fa-download"></i></button>
                     <button onclick="deleteFile('<?php echo htmlspecialchars($file); ?>')"><i class="fas fa-trash-alt"></i></button>
                  </td>
               </tr>
               <?php } ?>
            </tbody>
         </table>
      </div>
      <script>
         const toggleUploadBtn = document.getElementById('toggleUploadBtn');
         const dragArea = document.getElementById('drag-area');
         const fileInput = document.getElementById('fileInput');
         const uploadForm = document.getElementById('uploadForm');
         const hiddenFileInput = document.getElementById('hiddenFileInput');
         
         toggleUploadBtn.addEventListener('click', () => {
             dragArea.style.display = dragArea.style.display === 'block' ? 'none' : 'block';
         });
         
         dragArea.addEventListener('dragover', (event) => {
             event.preventDefault();
             dragArea.classList.add('dragover');
         });
         
         dragArea.addEventListener('dragleave', () => {
             dragArea.classList.remove('dragover');
         });
         
         dragArea.addEventListener('drop', (event) => {
             event.preventDefault();
             dragArea.classList.remove('dragover');
             const files = event.dataTransfer.files;
             if (files.length > 0) {
                 hiddenFileInput.files = files;
                 uploadForm.submit();
             }
         });
         
         fileInput.addEventListener('change', () => {
             if (fileInput.files.length > 0) {
                 hiddenFileInput.files = fileInput.files;
                 uploadForm.submit();
             }
         });
         
         // Function to sort table by column index
         function sortTable(colIndex) {
             const table = document.querySelector('.file-table');
             const rows = Array.from(table.rows).slice(1); // Skip header row
         
             rows.sort((rowA, rowB) => {
                 const cellA = rowA.cells[colIndex].textContent.trim();
                 const cellB = rowB.cells[colIndex].textContent.trim();
         
                 if (colIndex === 2) {
                     // Sort by date in YYYY-MM-DD format
                     return new Date(cellA) - new Date(cellB);
                 } else {
                     // Sort alphabetically or numerically
                     return cellA.localeCompare(cellB, undefined, { numeric: true, sensitivity: 'base' });
                 }
             });
         
             // Clear existing rows and append sorted rows
             while (table.rows.length > 1) {
                 table.deleteRow(1);
             }
         
             rows.forEach(row => {
                 table.appendChild(row);
             });
         }
         function downloadFile(filePath) {
         window.location.href = 'download.php?file=' + encodeURIComponent(filePath);
         }
         
         function deleteFile(fileName) {
             if (confirm(`Are you sure you want to delete '${fileName}'?`)) {
                 // Perform AJAX request or form submission to delete the file
                 const formData = new FormData();
                 formData.append('file_name', fileName);
         
                 fetch('delete_file.php', {
                     method: 'POST',
                     body: formData
                 })
                 .then(response => {
                     if (response.ok) {
                         // Refresh the page after successful deletion
                         window.location.reload();
                     } else {
                         // Handle deletion failure
                         alert('Failed to delete file.');
                     }
                 })
                 .catch(error => {
                     console.error('Error deleting file:', error);
                     alert('An error occurred while deleting the file.');
                 });
             }
         }
      </script>
   </body>
</html>