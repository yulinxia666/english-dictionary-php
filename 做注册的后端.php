<?php
session_start();
$connect=mysqli_connect("localhost","root","root","login");

$username = $_POST["username"];
$password =$_POST["password"];
$repassword=$_POST["repassword"];
//后端判断非空
if (empty($username) || empty($password)) {
    die(json_encode(["code" => 400, "msg" => "账号或密码不能为空"]));
}
if ($password === $repassword) {
    $sql_check = "SELECT id FROM user WHERE username=?";
    $stmt_check = $connect->prepare($sql_check);
    $stmt_check->bind_param("s", $username);
    $stmt_check->execute();
    $res = $stmt_check->get_result();

    if ($res->num_rows > 0) {
        echo json_encode(["code" => 100, "msg" => "账号已存在"]);
        exit;
    }else {
        $safe_password = password_hash($password, PASSWORD_DEFAULT);

        $sql_insert = "INSERT INTO user(username, password, role) VALUES (?, ?, ?)";
        $stmt_insert = $connect->prepare($sql_insert);
        $role = "user";
        $stmt_insert->bind_param("sss", $username, $safe_password, $role);

        if ($stmt_insert->execute()) {
            echo json_encode(["code" => 200, "msg" => "注册成功"]);
        } else {
            echo json_encode(["code" => 500, "msg" => "注册失败："]);

        }
    }
}else {
echo json_encode(["code" => 400, "msg" => "两次输入的密码不一致"]);
}

?>
