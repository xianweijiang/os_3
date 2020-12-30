<!DOCTYPE html>
<html lang="en">
<head>
    <title>Document</title>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <style>
        * {
            padding: 0px;
            margin: 0px;
        }
        body {
            background-color: #F5F7F9;
            overflow: hidden;
        }
        #container {
            width: 500px;
            height: 100%;
            max-width: 500px;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
        }
        iframe {
            border: none;
            width: 100%;
            height: 100%;
        }
        .msg-box {
            position: absolute;
            top: 87px;
            left: 105%;
            width: 200px;
            height: 340px;
            background-color: #fff;
            box-shadow: 4px 5px 5px  #DADADA;
        }
        .website {
            position: absolute;
            top: 24px;
            left: 50%;
            transform: translateX(-50%);
            width: 180px;
            font-size: 14px;
        }
        input {
            width: 130px;
            color: #8E8E8E;
            outline: none;
        }
        .btn-box {
            display: flex;
            justify-content: space-evenly;
            margin-top: 60px;
            font-size: 12px;
            border: solid 1px #F1F1F1;
            border-radius: 10px;
            width: 120px;
            height: 22px;
            transform: translateX(50%);
            margin-left: -20px;
        }
        .btn {
            padding-left: 12px;
            text-align: center;
            color: #8E8E8E;
            width: 60px;
            height: 22px;
            line-height: 22px;
            border-radius: 10px;
            position: relative;
            cursor: pointer;
        }
        .icon {
            display: inline-block;
            position: absolute;
            width: 14px;
            height: 14px;
            left: 8px;
            top: 3px;
        }
        img {
            width: 100%;
            height: 100%;
        }
        p {
            font-size: 14px;
            margin: 12px 0 0 10px;
        }
        .erweima {
            display: flex;
        }
        .img {
            width: 58px;
            height: 58px;
            margin: 10px 0 0 10px;
        }
        .font {
            font-size: 12px;
            margin: 10px 0 0 10px;
            width: 85px;
        }
        #hide-box {
            position: absolute;
            left: -50px;
            top: 80px;
            width: 220px;
            height: 220px;
            background: url('/public/system/images/xfk1.png');
            background-size: 220px 238px;
            display: none;
            z-index: 10;
        }
        #hide-box1 {
            position: absolute;
            left: -50px;
            top: 290px;
            width: 220px;
            height: 220px;
            background: url('/public/system/images/xfk1.png');
            background-size: 220px 238px;
            display: none;
        }
        #hide-box img{
            display: inline-block;
            position: absolute;
            top: 25px;
            left: 0px;
            margin-left: 35px;
            width: 150px;
            height: 150px;
            margin-top: 25px;
        }
        #hide-box1 img{
            margin-left: 35px;
            width: 150px;
            height: 150px;
            margin-top: 50px;
        }
        #hide-box p{
            position: absolute;
            font-size: 12px;
            margin: 15px 0 0 30px;
            top: 15px;
        }
        #hide-box1 p{
            position: absolute;
            font-size: 12px;
            margin: 15px 0 0 30px;
            top: 15px;
        }
        .title-icon {
            display: inline-block;
            width: 14px;
            height: 14px;
            position: relative;
            top: 2px;
            margin-right: 5px;
        }
        .icon1 {
            background: url('/public/system/images/phone.png');
        }
        .icon2 {
            background: url('/public/system/images/wx.png')
        }
        .hover-btn:hover {
            background-color: #0CA6F2;
            color: white;
        }
        #erweima img {
            position: relative;
        }
        #jump-msg {
            background-color: rgba(0,0,0,0.8);
            color: white;
            width: 86px;
            height: 24px;
            font-size: 12px;
            text-align: center;
            line-height: 24px;
            border-radius: 12px;
            position: absolute;
            top: 50%;
            z-index: 20;
            left: 50%;
            transform: translate(-50%, -50%);
            display: none;
        }
        #think_page_trace_open {
            display: none !important;
        }
    </style>
</head>
<body>
    <div class="web-box">
        <div id="container">
                <iframe src="{$url}" scrolling="auto" id="frame"></iframe>
            <div class="msg-box">
                <div id="jump-msg">复制成功</div>
                <div class="website">
                    <label for="">网址</label>
                    <input type="text" value="{$url}" readonly id="text">
                </div>
                <div class="btn-box">
                    <div class="share btn hover-btn" onclick="shareErWei()" id="share" onmouseover="changeWhite('share')" onmouseout="changeGray('share')">
                        <i class="icon"><img src="/public/system/images/share-01.png" alt="" id="share-img"></i>
                        <span>分享</span>
                    </div>
                    <div class="copy btn hover-btn" onclick="copyText()"  id="copy"  onmouseover="changeWhite('copy')" onmouseout="changeGray('copy')">
                        <i class="icon"><img src="/public/system/images/copy-01.png" alt="" id="copy-img"></i>
                        <span>复制</span>
                    </div>
                </div>
                <p><i class="icon1 title-icon"></i>手机访问</p>
                <div class="erweima">
                    <div class="img" id="erweima" onmouseover="showErWei('phone')" onmouseleave="hideErWei('phone')"></div>
                    <div class="font">打开手机浏览器扫一扫</div>
                    <div id="hide-box">
                        <p>扫描二维码，分享网页到微信</p>
                        <img src="" alt="">
                    </div>
                    <div id="hide-box1"><p>扫描二维码，关注微信公众号</p><img src="{$info.image}" alt=""></div>
                </div>
                <p><i class="icon2 title-icon"></i>关注微信公众号</p>
                <div class="erweima">
                    <div class="img"  onmouseover="showErWei('wx')" onmouseleave="hideErWei('wx')" data-wxUrl="{$info.image}"><img src="{$info.image}" alt=""></div>
                    <div class="font">打开微信扫一扫</div>
                </div>
                <p style="font-size: 12px">注：鼠标悬停可显示二维码</p>
            </div>
        </div>
    </div>
</body>
<script src="/public/install/js/qrcode.min.js"></script>
<script type="text/javascript" src="/public/install/js/jquery.js"></script>
<script>
    document.getElementById('container').style.height = window.innerHeight + 'px';
    let img = document.getElementById('img');
    let text = document.getElementById('text').value;
    function copyText() {
        let inputElement = document.getElementById('text');
        inputElement.select();//选中input框的内容
        document.execCommand("Copy");// 执行浏览器复制命令
        document.getElementById('jump-msg').style.display = 'block';
        setTimeout(() => {
            document.getElementById('jump-msg').style.display = 'none';
        }, 3000);
    }
    function shareErWei() {
        document.getElementById('hide-box').style.display = 'block';
        setTimeout(() => {
            document.getElementById('hide-box').style.display = 'none';
        }, 5000);
    }
    function showErWei(type) {
        if (type == 'phone') {
            document.getElementById('hide-box').style.top = '180px';
            document.getElementById('hide-box').style.display = 'block';
        } else if (type == 'wx') {
            document.getElementById('hide-box1').style.display = 'block';
        }
    }
    function hideErWei(type) {
        if (type == 'phone') {
            document.getElementById('hide-box').style.top = '80px';
            document.getElementById('hide-box').style.display = 'none';
        } else if (type == 'wx') {
            document.getElementById('hide-box1').style.display = 'none';
        }
    }
    var qrcode = new QRCode(document.getElementById("hide-box"), {
	    width : 180,
	    height : 180
    });
    var qrcode1 = new QRCode(document.getElementById("erweima"), {
	    width : 58,
	    height : 58
    });
    function makeCode() {		
	    if (text) {
            qrcode.makeCode(text)
        } else {
            $eb.message('error', '失败');
        }
    }
    function makecode1() {
        qrcode1.makeCode(text)
    }
    makeCode()
    makecode1()

    let share1 = document.getElementById('share-img');
    let copy1 = document.getElementById('copy-img');
    function changeWhite(type) {
        if (type == 'share') {
            share1.src = '/public/system/images/share-chose.png'
        } else if (type == 'copy') {
            copy1.src = '/public/system/images/copy-chose.png'
        } else {

        }
    }

    function changeGray(type) {
        if (type == 'share') {
            share1.src = '/public/system/images/share-01.png'
        } else if (type == 'copy') {
            copy1.src = '/public/system/images/copy-01.png'
        } else {
            
        }
    }
    
    // axios.interceptors.response.use(response => console.log('5.11', response))
    window.addEventListener('message', function(event) {
        console.log('5.19', event.data);
        document.getElementById('text').value = event.data.data;
        text = event.data.data;
        makeCode()
        makecode1()
    });
    axios.get('/osapi/common/about_us')
    .then(res => document.title = res.data.data.website_name)
</script>
</html>