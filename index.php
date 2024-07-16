<?php
session_start();
require 'db.php';

if (isset($_POST['create_group'])) {
    $group_name = $_POST['group_name'];
    $group_password = ($_POST['group_password']);

    $stmt = $conn->prepare("INSERT INTO groups (name, password) VALUES (?, ?)");
    $stmt->bind_param('ss', $group_name, $group_password);
    if ($stmt->execute()) {
        mkdir('uploads/' . $group_name);
        echo 'Group created successfully';
    } else {
        echo 'Error creating group';
    }
}

$groups = $conn->query("SELECT * FROM groups");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Additional styles can be added here if necessary */
    </style>
</head>
<body>
<div class="container">
    <div class="header" style="justify-content: center;">
        <button onclick="showSelectGroup()">Select Folder</button>
        <button onclick="showCreateGroup()">Create Folder</button>
    </div>
    <div id="select-group-form">
        <h2>Select Folder</h2>
        <form action="group_login.php" method="get">
           
            <select name="group_id" required>
                <option value="" disabled selected>Choose the one</option>
                <?php while ($row = $groups->fetch_assoc()) { ?>
                    <option value="<?php echo htmlspecialchars($row['id']); ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                <?php } ?>
            </select>
            <input type="submit" value="Select">
        </form>
    </div>
    <div id="create-group-form" style="display: none;">
        <h2>Create Folder</h2>
        <form action="index.php" method="post">
            <input type="text" name="group_name" placeholder="Folder Name" required>
            <input type="password" name="group_password" placeholder="Folder Password" required>
            <input type="submit" name="create_group" value="Create Group">
        </form>
    </div>
</div>

<script>
    function showSelectGroup() {
        document.getElementById('select-group-form').style.display = 'block';
        document.getElementById('create-group-form').style.display = 'none';
    }

    function showCreateGroup() {
        document.getElementById('select-group-form').style.display = 'none';
        document.getElementById('create-group-form').style.display = 'block';
    }

    // Ensuring that by default, the create group form is shown
    document.addEventListener("DOMContentLoaded", function() {
        showSelectGroup();
    });
</script>

</body>
</html>


