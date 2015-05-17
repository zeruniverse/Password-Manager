<?php
function echofooter()
{
	echo '<footer class="footer ">
      <p>&copy;2014 Jeffery<br /><br />ALL RIGHTS RESERVED</p>
</footer>
</body>
</html>';
}
function echoheader($active)
{
	$html='<!DOCTYPE html>
<html lang="zh-cn">
<head>
  <meta charset="utf-8">
  <title>ZerUniverse</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="ZerUniverse">
  <meta name="author" content="Jeffery">
	
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/style.css" rel="stylesheet">

  <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
  <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>

  <![endif]-->

  <!-- Fav and touch icons -->
  <link rel="shortcut icon" href="favicon.ico">
  
	<script type="text/javascript" src="js/jquery.min.js"></script>
	<script type="text/javascript" src="js/bootstrap.min.js"></script>
	<script type="text/javascript" src="js/scripts.js"></script>
<style>
.theme-showcase
{
	margin-top:50px !important;
}
.footer {
color: #777;
text-align: center;
padding: 30px 0;
margin-top: 70px;
border-top: 1px solid #e5e5e5;
background-color: #f5f5f5;
}
</style>
</head>

<body style="color:#666666">
<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
              <div class="container">
				<div class="navbar-header">
					 <button type="button" class="navbar-toggle"  data-toggle="collapse" data-target="#bs-example-navbar-collapse-1"> <span class="sr-only">Toggle navigation</span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></button> <a class="navbar-brand" style="padding-top:16px;padding-bottom:3px;padding-right:55px;font-size:30px;color:blue" href="./">ZerUniverse</a>
				</div>
				
				<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
					<ul class="nav navbar-nav">
						<li';
						if ($active==99){$html=$html.' class="active"';}
						$html=$html.'>
							<a href="http://zzeyu.com">主页</a>
						</li>
						<li';
						if ($active==1){$html=$html.' class="active"';}
			
						$html=$html.'>
							<a href="http://box.zzeyu.com">BOX</a>
						</li>
						<li';if ($active==2){$html=$html.' class="active"';}
			
						$html=$html.'>
							<a href="http://pw.zzeyu.com">密码管理器</a>
						</li>';
						$html=$html.'<li';
						if ($active==6){$html=$html.' class="active"';}
						$html=$html.'><a href="http://talk.zzeyu.com" >Talk</a></li><li';
						if ($active==3){$html=$html.' class="active"';}
			
						$html=$html.'><a href="#" >More...</a></li></li>';
                $html=$html.'</ul>
				</div>
			  </div>	
</nav>';
echo $html;
}
?>