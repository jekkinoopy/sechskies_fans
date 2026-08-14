<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- saved from url=(0040)http://127.0.0.1/test/exercise/collage/? -->
<html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<title>小黃集點卡</title>
<link href="./css/css.css" rel="stylesheet" type="text/css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
<script src="./js/jquery-1.9.1.min.js"></script>
<script src="./js/js.js"></script>
</head>

<body>
<div id="cover" style="display:none; ">
	<div id="coverr">
    	<a style="position:absolute; right:3px; top:4px; cursor:pointer; z-index:9999;" onclick="cl(&#39;#cover&#39;)">X</a>
        <div id="cvr" style="position:absolute; width:99%; height:100%; margin:auto; z-index:9898;"></div>
    </div>
</div>
<iframe style="display:none;" name="back" id="back"></iframe>
	<div id="main">
    	<a title="" href="./home_files/home.htm"><div class="ti" style="background:url(&#39;use/&#39;); background-size:cover;"></div><!--標題--></a>
        	<div id="ms">
             	<div id="lf" style="float:left;">
            		<div id="menuput" class="dbor">
                    <!--主選單放此-->
					<span class="t botli">主選單區</span>
                                                </div>
                    <div class="dbor" style="margin:3px; width:95%; height:20%; line-height:100px;">
                    	<span class="t">進站總人數 : 
                        	1                        </span>
                    </div>
        		</div>
<!--  -->
				<?php
	// 法一
					// include "front/main.php" 
					// 這是寫死main 但應該要點什麼出現什麼區塊:判斷式 兩個以上用switch case
	//法二 switch case寫法
					//$do=(!empty($_GET['do']))?$_GET['do']:"main";
						//if/else縮寫 條件?成立的話:不成立的話
						//將下方switch的$_GET['do']改為$do
					//switch($do){
						//如果$_GET網址?的key是
							// case "admin": =>題目的登入頁面設定是?admin 若要統一則要去修改連結
						 	//=>case "login"
								// 	include "front/login.php";
								// 	break;
							// case "main":
								// 	include "front/main.php";
								// 	break;
							// case "news":
								// 	include "front/news.php";
								// 	break;
						// 	//若只有這三個 網址沒有key=do 或value為亂碼 畫面會壞掉
						//加上預設值
							// default:
								// 	include "front/main.php";
										//只有預設值的話 只能解決value為亂碼 回到最上方加上解決沒有GET			
										// }
						
	//法三 動態 include，不管幾個頁面都不用動 switch
		//$do=(!empty($_GET['do']))?$_GET['do']:"main";
		// !empty不是空白=isset -> 
		// 		if(isset($_GET['do'])){
		//      $do = $_GET['do'];
		// 		}else{$do = "main";}
		
		//  ->縮寫成	變數 = 值A ?? 值B;  (值A 存在且不為 null → 用值A 否則 → 用值B)
		$do=$_GET['do']??"main";
		
		$path="front/$do.php";
		//抓$do的名稱找跟$do同名的路徑
		if(file_exists($path)){
			include $path;
		}else{
			include "front/main.php";
		}
		//匯入$path
 								?>
                <div id="alt" style="position: absolute; width: 350px; min-height: 100px; word-break:break-all; text-align:justify;  background-color: rgb(255, 255, 204); top: 50px; left: 400px; z-index: 99; display: none; padding: 5px; border: 3px double rgb(255, 153, 0); background-position: initial initial; background-repeat: initial initial;"></div>
				<script>
						$(".sswww").hover(
							function ()
							{
								$("#alt").html(""+$(this).children(".all").html()+"").css({"top":$(this).offset().top-50})
								$("#alt").show()
							}
						)
						$(".sswww").mouseout(
							function()
							{
								$("#alt").hide()
							}
						)
                        </script>
                                 <div class="di di ad" style="height:540px; width:23%; padding:0px; margin-left:22px; float:left; ">
                	<!--右邊-->   
                	<button style="width:100%; margin-left:auto; margin-right:auto; margin-top:2px; height:50px;" onclick="lo(&#39;?do=login&#39;)">管理登入</button>
                	<div style="width:89%; height:480px;" class="dbor">
                    	<span class="t botli">校園映象區</span>
						                        <script>
                        	var nowpage=0,num=0;
							function pp(x)
							{
								var s,t;
								if(x==1&&nowpage-1>=0)
								{nowpage--;}
								if(x==2&&(nowpage+1)*3<=num*1+3)
								{nowpage++;}
								$(".im").hide()
								for(s=0;s<=2;s++)
								{
									t=s*1+nowpage*1;
									$("#ssaa"+t).show()
								}
							}
							pp(1)
                        </script>
                    </div>
                </div>
                            </div>
             	<div style="clear:both;"></div>
            	<div style="width:1024px; left:0px; position:relative; background:#FC3; margin-top:4px; height:123px; display:block;">
                	<span class="t" style="line-height:123px;"></span>
                </div>
    </div>

</body></html>