<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php
session_start();
include 'config/database.php';

if(isset($_POST['login']))
{
    $username=$_POST['username'];
    $password=md5($_POST['password']);

    $query=mysqli_query($conn,
    "SELECT * FROM admin
     WHERE username='$username'
     AND password='$password'");

    if(mysqli_num_rows($query)>0)
    {
        $_SESSION['admin']=$username;
        header("Location: dashboard.php");
    }
    else{
        echo "<script>alert('Invalid Login');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>CGMS Login</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="login-box">
<h2>ESTI CGMS</h2>

<form method="POST">

<input type="text"
name="username"
placeholder="Username"
required>

<input type="password"
name="password"
placeholder="Password"
required>

<button type="submit"
name="login">
LOGIN
</button>

</form>
</div>

</body>
</html>
</body>
</html>