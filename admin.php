<?php
session_start();
// 校验：登录 + role是admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location:user.php");
    exit;
}
$msg = "";
if($_SERVER['REQUEST_METHOD'] === "POST"){
    $en = trim($_POST['en']);
    $part = trim($_POST['part']);
    $cn = trim($_POST['cn']);
    if($en == "" || $part == "" || $cn == ""){
        $msg = "所有内容不能为空！";
    }else{
        $conn = new mysqli("localhost","root","root","login");
        $conn->set_charset("utf8mb4");
        $sql = "INSERT INTO words(en,part,cn) VALUES (?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss",$en,$part,$cn);
        if($stmt->execute()){
            $msg = "✅ 单词添加成功！";
        }else{
            $msg = "❌ 添加失败，该单词可能已存在";
        }
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>管理员 - 添加单词</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;font-family:"Microsoft YaHei",sans-serif}
        body{
            background: linear-gradient(135deg,#a8edea 0%,#fed6e3 100%);
            min-height:100vh;
            padding:40px 20px;
        }
        .container{
            max-width:620px;
            margin:0 auto;
            background:rgba(255,255,255,0.7);
            backdrop-filter:blur(16px);
            border-radius:24px;
            padding:32px;
            box-shadow:0 12px 40px rgba(0,0,0,0.08);
        }
        h1{
            text-align:center;
            color:#222;
            font-size:28px;
            margin-bottom:8px;
        }
        .tip{
            text-align:center;
            color:#666;
            margin-bottom:24px;
        }
        .form-item{
            margin-bottom:18px;
        }
        label{
            display:block;
            margin-bottom:6px;
            color:#333;
        }
        input{
            width:100%;
            padding:14px 20px;
            border:none;
            border-radius:50px;
            background:#f4f8f8;
            font-size:16px;
            outline:none;
        }
        input:focus{
            box-shadow:0 0 0 3px rgba(66,230,149,0.25);
            background:#fff;
        }
        .submit-btn{
            width:100%;
            padding:14px;
            border:none;
            border-radius:50px;
            background:linear-gradient(135deg,#42e695,#3bb2b8);
            color:#fff;
            font-size:17px;
            font-weight:600;
            cursor:pointer;
            box-shadow:0 8px 16px rgba(66,230,149,0.22);
        }
        .submit-btn:hover{
            transform:translateY(-2px);
        }
        .msg{
            text-align:center;
            margin:16px 0;
            font-size:16px;
        }
        .back{
            display:block;
            text-align:center;
            margin-top:22px;
            color:#777;
            text-decoration:none;
        }
        .back:hover{
            color:#3bb2b8;
            text-decoration:underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>单词管理后台</h1>
    <div class="tip">新增单词到词典库</div>
    <?php if($msg): ?>
        <div class="msg"><?php echo $msg; ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="form-item">
            <label>英文单词</label>
            <input type="text" name="en" placeholder="例如：apple">
        </div>
        <div class="form-item">
            <label>词性</label>
            <input type="text" name="part" placeholder="例如：n.">
        </div>
        <div class="form-item">
            <label>中文释义</label>
            <input type="text" name="cn" placeholder="例如：苹果">
        </div>
        <button class="submit-btn" type="submit">添加单词</button>
    </form>
    <a href="user.php" class="back">← 返回词典主页</a>
</div>
</body>
</html>
