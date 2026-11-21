<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location:登录首页.html");
    exit;
}
$role = $_SESSION['role'];
if ($role === 'admin'){
    header("Location:admin.php");
}elseif($role === 'user'){
    header("Location:user.php");
}else{
    header("Location:登录首页.html");
}
exit;
?>
