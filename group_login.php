<?php
   session_start();
   require 'db.php';
   
   $group_name = ''; // Initialize $group_name to avoid undefined variable error
   $login_error = ''; // Initialize error message
   
   if (isset($_GET['group_id'])) {
       $group_id = $_GET['group_id'];
   
       $stmt = $conn->prepare("SELECT name FROM groups WHERE id = ?");
       $stmt->bind_param('i', $group_id);
       $stmt->execute();
       $result = $stmt->get_result();
       $group = $result->fetch_assoc();
   
       if ($group) {
           $group_name = $group['name'];
       }
   }
   
   if (isset($_POST['login_group'])) {
       $group_id = $_POST['group_id'];
       $group_password = $_POST['group_password'];
   
       $stmt = $conn->prepare("SELECT * FROM groups WHERE id = ?");
       $stmt->bind_param('i', $group_id);
       $stmt->execute();
       $result = $stmt->get_result();
       $group = $result->fetch_assoc();
   
       if ($group && $group_password ==$group['password']){
           $_SESSION['group_id'] = $group['id'];
           $_SESSION['group_name'] = $group['name'];
           header("Location: group.php");
           exit;
       } else {
           $login_error = 'Invalid password. Please try again.';
       }
   }
   ?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="UTF-8">
      <title>Folder Login</title>
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
      <link rel="stylesheet" href="style.css">
   </head>
   <body>
      <div class="container">
         <div class="header">
            <h2 style="font-size: medium;"><?php echo !empty($group_name) ? htmlspecialchars($group_name) . ' Folder Login' : 'Folder Login'; ?></h2>
         </div>
         <br>
         <?php if ($login_error): ?>
         <p style="color: red;"><?php echo htmlspecialchars($login_error); ?></p>
         <?php endif; ?>
         <form action="group_login.php?group_id=<?php echo isset($group_id) ? htmlspecialchars($group_id) : ''; ?>" method="post">
            <input type="hidden" name="group_id" value="<?php echo isset($group_id) ? htmlspecialchars($group_id) : ''; ?>">
            <input type="password" name="group_password" placeholder="Folder Password" required>
            <input type="submit" name="login_group" value="Login">
         </form>
      </div>
   </body>
</html>