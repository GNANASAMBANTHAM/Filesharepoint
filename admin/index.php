<?php
session_start();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'];

    // Simple admin credentials check (replace with a more secure method)
    if ($password == 'nimda@') {
        $_SESSION['admin'] = true;
        header('Location: admin_console.php');
        exit();
    } else {
        $error = "Invalid credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../style.css">
    <style>   
    .header h2 {
  font-size: 1.5rem;
  color: #333;
  margin-left: 15%;
}</style>
 
</head>
<body>
<div class="container">
    <div class="header">
        <h2>Admin Console Login</h2>
        <br>
    </div>
    <br>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="post" action="index.php">
        <input type="password" name="password" placeholder="Password" required>
        <input type="submit" name="submit" value="Login">
    </form>
</div>
</body>
</html>
