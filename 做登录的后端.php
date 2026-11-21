<?php
session_start();
$connect = new mysqli("localhost", "root", "root", "login");
$username = $_POST['username'];
$password =$_POST['password'];

//预处理
$sql = "SELECT id, username, password, role FROM user WHERE username=?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$res = $stmt->get_result();


if ($res->num_rows > 0) {
    // 3. 找到账号了，取出这一行数据变成数组 $user
    $user = $res->fetch_assoc();

    // 4. 校验密码：输入的密码 和数据库加密密码 是否匹配
    if (password_verify($password, $user['password'])) {
        // ✅ 密码正确！登录成功，把身份放进session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        echo json_encode(["code" => 200, "msg" => "登录成功"]);
    } else {

        echo json_encode(["code" => 400, "msg" => "登录失败，账号或密码错误"]);
    }
}else{
    echo json_encode(["code" => 500, "msg" => "登录失败，账号或密码错误"]);
}

?>