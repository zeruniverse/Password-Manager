<?php session_start(); if(isset($_SESSION["loginok"])&& $_SESSION['loginok']==1) header("Location: ./password.php");

?>
<!DOCTYPE HTML>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<title>ZerUniverse</title>
<link rel="shortcut icon" type="image/x-icon" href="../style/images/favicon.png" />
<link rel="stylesheet" type="text/css" href="../style.css" media="all" />
<!--[if IE 7]>
<link rel="stylesheet" type="text/css" href="style/css/ie7.css" media="all" />
<![endif]-->
<!--[if IE 8]>
<link rel="stylesheet" type="text/css" href="style/css/ie8.css" media="all" />
<![endif]-->
<!--[if IE 9]>
<link rel="stylesheet" type="text/css" href="style/css/ie9.css" media="all" />
<![endif]-->
<script type="text/javascript" src="../style/js/jquery-1.6.4.min.js"></script>
<script type="text/javascript" src="../style/js/ddsmoothmenu.js"></script>
<script type="text/javascript" src="../style/js/jquery.jcarousel.js"></script>
<script type="text/javascript" src="../style/js/jquery.prettyPhoto.js"></script>
<script type="text/javascript" src="../style/js/carousel.js"></script>
<script type="text/javascript" src="../style/js/jquery.flexslider-min.js"></script>
<script type="text/javascript" src="../style/js/jquery.masonry.min.js"></script>
<script type="text/javascript" src="../style/js/jquery.slickforms.js"></script>

</head>
<body>
<!-- Begin Wrapper -->
<div id="wrapper">
	<!-- Begin Sidebar -->
	<div id="sidebar">
		 <div id="logo"><a href="/"><img src="../style/images/logo.png" alt="ZZY's HOMEPAGE" /></a></div>
		 
	<!-- Begin Menu -->
     <div id="menu" class="menu-v">
      <ul>
        <li><a href="http://zzeyu.com/">主页</a>
        </li>
        <li><a href="http://zzeyu.com/res.php">资源下载</a></li>
        <li><a href="http://zzeyu.com/zuo.php">作品</a></li>
        <li><a href="#" class="active">业余项目</a>
        	<ul>
        		<li><a href="http://zzeyu.com/box/">云U盘</a></li>
        		<li><a href="http://zzeyu.com/pw/">密码管理器</a></li>
        		<li><a href="http://zzeyu.com/jiaocai/">二手教材分享</a></li>
        		<li><a href="http://zzeyu.com/ip/">ip查看与定位系统</a></li>
                <li><a href="http://zzeyu.com/getfile.html">提取文件</a></li>
                <li><a href="http://zzeyu.com/private.html">其他项目</a></li>
        	</ul>
        </li>
        <li><a href="http://zzeyu.com/contact/">关于</a>

        </li>
        <li><a href="http://me.zzeyu.com">博客(LOFTER)</a></li>
      </ul>
    </div>
    <!-- End Menu -->
   
    
    <div class="sidebox">
    <!--<ul class="share">
    	<li><a href="#"><img src="style/images/icon-rss.png" alt="RSS" /></a></li>
    	<li><a href="#"><img src="style/images/icon-facebook.png" alt="Facebook" /></a></li>
    	<li><a href="#"><img src="style/images/icon-twitter.png" alt="Twitter" /></a></li>
    	<li><a href="#"><img src="style/images/icon-dribbble.png" alt="Dribbble" /></a></li>
    	<li><a href="#"><img src="style/images/icon-linkedin.png" alt="LinkedIn" /></a></li>
    </ul>-->
    </div>

    
	</div>
	<!-- End Sidebar -->
	
	<!-- Begin Content -->
	<div id="content">
	<h1 class="title">密码管理器</h1>
	<div class="line"></div>
	<div class="intro">登录：</div>
	<form>
    用户名: <input type="text" name="user" id="user" /><br />
    密码： <input type="password" name="pwd" id="pwd" /><br />
    <div id="emailcheck" style="display:none">邮箱校验码:<input type="text" name="emailcode" id="emailcode" />(如果需要重发邮件请留空再次提交)<br /></div>
    <div id="vericode" style="display:none" >验证码：<input type="text" class="input" id="code_num" name="code_num" maxlength="4" /><img src="../verify/code_num.php" width="60" height="20" id="getcode_num" style="display:inline" title="看不清，点击换一张">(点击刷新）<br /></div>
    
    <input type="button" class="btn" id="chk"  value=" 提交 " /></form>
    <span id="firstlogin" class="ppla" style="display:none; color:Red">请输入邮箱验证码，系统已向您的邮箱发送邮件(或验证码错误)<br /></span>
    <span id="verierr" class="ppla" style="display:none; color:Red">请输入验证码(或验证码错误,请点击图片刷新验证码)<br /></span>
    <span id="nouser" class="ppla"  style="display:none; color:Red">用户不存在<br /></span>
    <span id="pwderr" class="ppla"  style="display:none; color:Red">密码错误<br /></span>
    <span id="othererror" class="ppla"  style="display:none; color:Red">其它错误，请刷新本页面重试<br /></span>
    <div class="line"></div><input type="button" onClick="window.location.href='reg.html';" value=" 注册 " />
    <!-- Begin Footer -->
    <div id="footer">
  	&copy;Jeffery Zhao; 2014. Alpha<br /><br />
    </div>
    <!-- End Footer -->
    
    
	</div>
	<!-- End Content -->

</div>
<!-- End Wrapper -->
<div class="clear"></div>
<script type="text/javascript" src="../style/js/scripts.js"></script>
<!--[if !IE]> -->
<script type="text/javascript" src="../style/js/jquery.corner.js"></script>
<!-- <![endif]-->

</body>
</html>
<script type="text/javascript">
  $(function(){ 
    $("#getcode_num").click(function(){ 
        $(this).attr("src",'../verify/code_num.php?' + Math.random());
    }); 
    $("#chk").click(function(){ 
        var user = $("#user").val(); 
		var pwd = $("#pwd").val(); 
		var emailcode= $("#emailcode").val(); 
		var vericode = $("#code_num").val();
		$("#chk").attr("disabled", true);
		$("#chk").attr("value", "请稍候");
        $.post("check.php",{csfds:'sdf', emailcode:emailcode, pwd:pwd,  user: user, vericode:vericode},function(msg){ 
        $(".ppla").hide();
		if(msg==0){
			 	$("#nouser").show();
				$("#chk").attr("value", " 提交 ");
				$("#chk").attr("disabled", false);
				$("#getcode_num").attr("src","../verify/code_num.php?"+ Math.random());
		}else
		if(msg==1){
			 	$("#pwderr").show();
				$("#chk").attr("value", " 提交 ");
				$("#chk").attr("disabled", false);
				$("#getcode_num").attr("src","../verify/code_num.php?"+ Math.random());
		}else
		if(msg==2){
			 	$("#firstlogin").show();
				$("#emailcheck").show();
				$("#chk").attr("value", " 提交 ");
				$("#chk").attr("disabled", false);
				$("#getcode_num").attr("src","../verify/code_num.php?"+ Math.random());
		}else
		if(msg==3){
			 	$("#verierr").show();
				$("#vericode").show();
				$("#chk").attr("value", " 提交 ");
				$("#chk").attr("disabled", false);
				$("#getcode_num").attr("src","../verify/code_num.php?"+ Math.random());
		}else
		if(msg==4){
			 	$("#othererror").show();
				$("#chk").attr("value", " 提交 ");
				$("#chk").attr("disabled", false);
				$("#getcode_num").attr("src","../verify/code_num.php?"+ Math.random());
		}else{
			 	window.location.href="password.php";
		}
		 
        }); 
    }); 
}); 
</script>