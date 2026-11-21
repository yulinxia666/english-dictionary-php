<?php
session_start();
header("Content-type:application/json;charset=utf-8");
if(!isset($_SESSION['user_id'])){
    echo json_encode(["code"=>401,"msg"=>"请先登录"]);
    exit;
}
$connect = new mysqli("localhost","root","root","login");
$connect->set_charset("utf8mb4");
if($connect->connect_error){
    echo json_encode(["code"=>500,"msg"=>"数据库失败"]);
    exit;
}

$raw = file_get_contents("php://input");
$json = json_decode($raw,true);
$word = trim($json['word']);
$type = $json['type'];
$page = intval($json['page'] ?? 1);
$pageSize = 5; // 每页 5 条
$offset = ($page - 1) * $pageSize;

if($type === "exact"){
    $sql = "SELECT en,part,cn FROM words WHERE LOWER(en)=LOWER(?) LIMIT 1";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("s",$word);
}else{
    $likeWord = $word . "%";
    // 查询总条数
    $countSql = "SELECT COUNT(*) total FROM words WHERE LOWER(en) LIKE LOWER(?)";
    $countStmt = $connect->prepare($countSql);
    $countStmt->bind_param("s",$likeWord);
    $countStmt->execute();
    $total = $countStmt->get_result()->fetch_assoc()['total'];
    $countStmt->close();

    $sql = "SELECT en,part,cn FROM words WHERE LOWER(en) LIKE LOWER(?) LIMIT ?,?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("sii",$likeWord,$offset,$pageSize);
}
$stmt->execute();
$res = $stmt->get_result();
$list = [];
while($row = $res->fetch_assoc()){
    $list[] = $row;
}

if($type === "exact"){
    $total = count($list);
}
$totalPage = ceil($total / $pageSize);

if(count($list) > 0){
    echo json_encode([
        "code"=>200,
        "data"=>$list,
        "page"=>$page,
        "pageSize"=>$pageSize,
        "total"=>$total,
        "totalPage"=>$totalPage
    ]);
}else{
    echo json_encode(["code"=>404,"msg"=>"没有匹配单词"]);
}
$stmt->close();
$connect->close();
?>
