<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location:登录首页.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>英汉词典</title>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;font-family:"Microsoft YaHei",sans-serif}
        body{
            background: linear-gradient(135deg,#a8edea 0%,#fed6e3 100%);
            min-height:100vh;
            padding:40px 20px;
        }
        .container{
            max-width:720px;
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
            font-size:34px;
            margin-bottom:8px;
        }
        .welcome{
            text-align:center;
            color:#666;
            margin-bottom:30px;
        }
        .search-row{
            display:flex;
            gap:14px;
            margin-bottom:18px;
        }
        #wordInput{
            flex:1;
            padding:16px 22px;
            border:none;
            border-radius:50px;
            background:#f4f8f8;
            font-size:16px;
            outline:none;
            transition:0.3s;
        }
        #wordInput:focus{
            box-shadow:0 0 0 3px rgba(66,230,149,0.25);
            background:#fff;
        }
        #searchBtn{
            padding:0 32px;
            border:none;
            border-radius:50px;
            background:linear-gradient(135deg,#42e695,#3bb2b8);
            color:#fff;
            font-size:17px;
            font-weight:600;
            cursor:pointer;
            box-shadow:0 8px 16px rgba(66,230,149,0.22);
            transition:0.3s;
        }
        #searchBtn:hover{
            transform:translateY(-2px);
            box-shadow:0 12px 22px rgba(66,230,149,0.3);
        }
        .radio-wrap{
            text-align:center;
            margin-bottom:26px;
            color:#444;
        }
        .radio-wrap label{
            margin:0 12px;
            cursor:pointer;
        }
        /* 结果框：初始就带一个浅底圆角框 */
        #resultBox{
            border-radius:16px;
            background:rgba(255,255,255,0.5);
            padding:18px;
            min-height:120px;
        }
        .result-item{
            background:#fff;
            border-radius:16px;
            padding:16px 20px;
            margin-bottom:12px;
            display:flex;
            align-items:center;
            gap:12px;
        }
        .result-item:last-child{margin-bottom:0;}
        .en{
            font-size:20px;
            font-weight:600;
            color:#224455;
        }
        .part{
            background:#dcf6f0;
            color:#239977;
            padding:3px 9px;
            border-radius:12px;
            font-size:14px;
        }
        .cn{
            color:#555;
        }
        .empty-tip{
            text-align:center;
            line-height:84px;
            color:#888;
            font-size:16px;
        }
        /* 分页区域 */
        .page-wrap{
            display:flex;
            justify-content:center;
            align-items:center;
            gap:16px;
            margin-top:26px;
        }
        .page-btn{
            padding:9px 20px;
            border:none;
            border-radius:50px;
            background:#fff;
            color:#3bb2b8;
            border:1px solid #3bb2b8;
            cursor:pointer;
            transition:0.25s;
        }
        .page-btn:disabled{
            opacity:0.4;
            cursor:not-allowed;
        }
        .page-btn:hover:not(:disabled){
            background:#3bb2b8;
            color:#fff;
        }
        .page-info{
            color:#555;
        }
        .logout{
            display:block;
            text-align:center;
            margin-top:24px;
            color:#777;
            text-decoration:none;
        }
        .logout:hover{
            color:#3bb2b8;
            text-decoration:underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>英汉词典</h1>
    <div class="welcome">欢迎你：<?php echo htmlspecialchars($_SESSION['username']) ?></div>

    <div class="search-row">
        <input type="text" id="wordInput" placeholder="输入英文单词">
        <button id="searchBtn">查询</button>
    </div>
    <div class="radio-wrap">
        <label><input type="radio" name="qtype" value="exact"> 精确查询</label>
        <label><input type="radio" name="qtype" value="fuzzy" checked> 前缀模糊查询</label>
    </div>

    <div id="resultBox"><div class="empty-tip">输入单词开始查询吧～</div></div>
    <div class="page-wrap" id="pageBox" style="display:none;">
        <button class="page-btn" id="prevBtn">上一页</button>
        <span class="page-info" id="pageText"></span>
        <button class="page-btn" id="nextBtn">下一页</button>
    </div>

    <a href="logout.php" class="logout">退出登录</a>
</div>

<script>
    const wordInput = document.getElementById('wordInput');
    const searchBtn = document.getElementById('searchBtn');
    const resultBox = document.getElementById('resultBox');
    const pageBox = document.getElementById('pageBox');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const pageText = document.getElementById('pageText');

    let currentWord = "";
    let currentType = "fuzzy";
    let currentPage = 1;
    let totalPage = 0;

    function loadData(){
        axios.post("search.php",{
            word: currentWord,
            type: currentType,
            page: currentPage
        }).then(res=>{
            if(res.data.code === 200){
                const list = res.data.data;
                totalPage = res.data.totalPage;
                let html = "";
                list.forEach(item=>{
                    html += `
                <div class="result-item">
                    <span class="en">${item.en}</span>
                    <span class="part">${item.part}</span>
                    <span class="cn">${item.cn}</span>
                </div>
                `
                })
                resultBox.innerHTML = html;
                if(currentType === "fuzzy"){
                    pageBox.style.display = "flex";
                    pageText.innerText = `第${currentPage}页 / 共${totalPage}页`;
                    prevBtn.disabled = currentPage <= 1;
                    nextBtn.disabled = currentPage >= totalPage;
                }else{
                    pageBox.style.display = "none";
                }
            }else{
                resultBox.innerHTML = `<div class="empty-tip">${res.data.msg}</div>`;
                pageBox.style.display = "none";
            }
        }).catch(err=>{
            resultBox.innerHTML = `<div class="empty-tip">请求出错</div>`;
            pageBox.style.display = "none";
        })
    }

    searchBtn.onclick = function(){
        currentWord = wordInput.value.trim();
        currentType = document.querySelector('input[name="qtype"]:checked').value;
        currentPage = 1;
        loadData();
    }
    wordInput.onkeydown = function(e){
        if(e.key === 'Enter') searchBtn.click();
    }
    prevBtn.onclick = function(){
        if(currentPage > 1){
            currentPage--;
            loadData();
        }
    }
    nextBtn.onclick = function(){
        if(currentPage < totalPage){
            currentPage++;
            loadData();
        }
    }
</script>
</body>
</html>
