<?php
if(isset($_COOKIE["cit"])) {
	$cities = json_decode($_COOKIE["cit"], true);
	if(count($cities) == 0) {
		$cities = array("3785"=>"Харьков");
		setCookie("cit", json_encode($cities, true));	
	}
} else {
	$cities = array("3785"=>"Харьков");
	setCookie("cit", json_encode($cities, true));
}
$db = mysqli_connect("localhost", "root", "", "weather");
mysqli_query($db, "SET NAMES utf8");
?>
<html>
  <head>
    <title>Погода KitSpit</title>
	<meta http-equiv="Content-Type" content="text/html" charset="utf-8">
	<link href="main.css" rel="stylesheet">
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
	<script src="http://code.jquery.com/jquery-1.10.2.js"></script>
  <script src="http://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
  <script src="jquery.mousewheel.min.js"></script>
  <script>
  function checkTime(i) {
		if (i<10) {
			i="0" + i;
		}
		return i;
	}

    $(function () {
        var next = "find";
        var back = "weather";
        var nadpis = "";
		var sdvig = 400;
		var k = 200/sdvig*4;
		var nowDay = 2;
		var cache;
		var now = 0;
		
		var status1 = null;
		var status2 = null;
        
		$(".add-city:last").hide();
		
        $(".add-city").click(function () {
            $("."+back).fadeOut(400);
            $("."+next).delay(500).fadeIn(400);
			$("#help").toggle();
			$("footer").toggle();
			$(".add-city").toggle();
            //$(".find, .weather").toggle("fade", {}, 400);
            var temp = next;
            next = back;
            back = temp;
            
            if($(".top:first").attr("class").indexOf("del-vis")+1)
                $(".top:first").removeClass("del-vis");
            else
                $(".top:first").addClass("del-vis");
			
			$("#search-input").focus();
            
			
			if(status1 == null) {
				status1 = $(".arrow:first").css("display");
				$(".arrow:first").hide();
			} else {
				$(".arrow:first").css("display", status1);
				status1 = null;
			}
			
			if(status2 == null) {
				status2 = $(".arrow:last").css("display");
				$(".arrow:last").hide();
			} else {
				$(".arrow:last").css("display", status2);
				status1 = null;
			}
            
			$("#city").click();
        });
		
		$(".arrow:first").hide();
        
        $(".arrow:last").click(function (e) {
			var minisdvig = sdvig;
            var sdvig2 = -screen.width/2;
			$(".arrow:first").show();
			
            var goto = $("ul.weather").position().left+sdvig2;

            if(-goto <= $("ul.weather").width()-k*minisdvig)
                $("ul.weather").css("left", goto);
            else {
                $("ul.weather").css("left", -($("ul.weather").width()/minisdvig).toFixed(0)*minisdvig+k*minisdvig);
				$(this).hide();
			}
            
            e.preventDefault();
        });
        
        $(".arrow:first").click(function (e) {
            var sdvig = screen.width/2;
            var goto = $("ul.weather").position().left+sdvig;
			$(".arrow:last").show();

            if(goto <= 0)
                $("ul.weather").css("left", goto);
            else {
                $("ul.weather").css("left", 0);
				$(this).hide();
			}
            
            e.preventDefault();
        });
        
        $("body").on("click", ".del", function (e) {
			var city_id = $(this).attr("href");
            $(this).parent().remove(); 
			$.post("/weather/get.php", {"remove":city_id});
            e.preventDefault();
        });
		
		$("body").on("focus", "ul.weather li a", function(e) {
			$(this).click();
		});
        
        $("body").on("click", "ul.weather li a", function(e) {
            var i = parseInt($(this).parent().attr("id"));
            if(i > 1) {
                goto(i);
            }
			if(i == 2) $(".arrow:first").hide();
			else $(".arrow:first").show();
			nowDay = i;
            e.preventDefault();
        });
        
        function goto(i) {
            $("li").removeClass('active');
            $("#"+i).addClass('active');
            $("ul.weather").css("left","-"+(15*i-15*2)+"rem");
			if($("#"+i).find("img:first").attr("src") == "sun.png") $("body").addClass("bday");
			else $("body").removeClass("bday");
        }
        
        $("#city").click(function () {
           goto(2); 
		   $("#search-input").val("");
		   $("#search-input").keyup();
        });
        /*
        $( "ul.weather" ).draggable({ 
            axis: "x", 
            stop: function (e, ui) {
                var left = $("ul.weather").position().left;
                
                if(left > 0)
                    $("ul.weather").css("left", 0);
                else if(-left >= $("ul.weather").width()-k*sdvig)
                    $("ul.weather").css("left", -($("ul.weather").width()/sdvig).toFixed(0)*sdvig+k*sdvig);
            }
        });
        */
        $("body").mousewheel(function (event, delta) {
            var goto = $("ul.weather").position().left+(event.deltaY*(sdvig));
            
            if(goto <= 0)
                if(-goto <= $("ul.weather").width()-k*sdvig)
                    $("ul.weather").css("left", goto);
                else
                    $("ul.weather").css("left", -($("ul.weather").width()/sdvig).toFixed(0)*sdvig+k*sdvig);
            else
                $("ul.weather").css("left", 0);
        });
		
		$("body").keyup(function(e) {
			switch(e.which) {
				case 37:
					if(nowDay-1 > 1) {
						goto(nowDay-1);
						nowDay--;
						$(".arrow:first").show();
						$(".arrow:last").show();
					} 
					if(nowDay-1 <= 1) $(".arrow:first").hide();
				break;
				case 39:
					if(nowDay+1 <= $("ul.weather").children("li").length) {
						goto(nowDay+1);
						nowDay++;
						$(".arrow:last").show();
						$(".arrow:first").show();
					} 
					if(nowDay+1 > $("ul.weather").children("li").length) $(".arrow:last").hide();
				break;
			}
		});
		
		$(".regions").click(function (e) {
			var region_id = $(this).attr("href");
			if($(".find:last").children("li").length > 1)
				$(".find:last").children("li").slice(1).remove();
			$.post("/weather/get.php", {"region":region_id}).done(function (data) {
				$(".find:last").append(data);
			});
			$(this).parent().parent().find("li").removeClass("act");
			$(this).parent().addClass("act");
			e.preventDefault();
		});
		
		$("body").on("click", ".areas", function (e) {
			var area_id = $(this).attr("href");
			if($(".find:last").children("li").length > 2)
				$(".find:last").children("li").slice(2).remove();
			$.post("/weather/get.php", {"area":area_id}).done(function (data) {
				$(".find:last").append(data);
			});
			$(this).parent().parent().find("li").removeClass("act");
			$(this).parent().addClass("act");
			e.preventDefault();			
		});
		
		var n = 0;
		
		$("body").on("click", ".cities", function (e) {
			var city_id = $(this).attr("href");
			
			if($(this).parent().parent().attr("id") != "menu") $(".add-city:first").click();
			
			if(city_id!=now) {
				$("#preload").fadeIn(200);
				$.post("/weather/get.php", {"city":city_id}).done(function (data) {
					data = $.parseJSON(data);
					$("#city").text(data.city.replace("с", "").replace("пгт", "").replace("пос", "").trim());
					for(var i = 0; i<11; i++) {
						var j = i+1;
						var main = $("#"+j);
						cache = data;
						main.find(".max").text(data['days'][i].max+"°");
						main.find(".min").text(data['days'][i].min+"°");
						
						main.find(".month").text(data['days'][i].month);
						main.find(".day-z").text(data['days'][i].day);
						main.find(".weekday").text(data['days'][i].weekday);
						
						main.find(".night-z").text(data['days'][i].night+"°");
						main.find(".morning").text(data['days'][i].morning+"°");
						main.find(".daytime").text(data['days'][i].daytime+"°");
						main.find(".evening").text(data['days'][i].evening+"°");
						
						main.find(".nightimg").attr("src", data['days'][i].nightimg);
						main.find(".morningimg").attr("src", data['days'][i].morningimg);
						main.find(".daytimeimg").attr("src", data['days'][i].daytimeimg);
						main.find(".eveningimg").attr("src", data['days'][i].eveningimg);
						
						main.find(".wind").text(data['days'][i].wind);
						
						main.find(".main-info").text(data['days'][i].text);
						
						if(data['days'][i].weekday == "Пн") main.addClass("pn");
						
						main.find(".p3").text(data['days'][i].start);
						main.find(".p4").text(data['days'][i].end);
						
						console.log(30-parseFloat(data['days'][i].max));
						main.find("a:first").css("top", 30-parseFloat(data['days'][i].max)*2);
						
						main.find("img:first").attr("src", data['days'][i].image);
	
					}
					
					$("#menu li").removeClass("actm");
					if(!($("#menu").html().indexOf(city_id+"")+1))
						$("#menu li:first").before("<li><a href=\""+city_id+"\" id=\""+city_id+"\" class=\"cities\">"+data.city.trim()+"</a><a class=\"del\" href=\""+city_id+"\">&times;</a></li>");
					$("#"+city_id).parent().addClass("actm");
					$("#preload").fadeOut(200);
					now = city_id;
				});
			}
			e.preventDefault();
			$("#city").click();
		});
		
		$("#fa").click(function (e) {
			for(var i = 0; i<11; i++) {
				var j = i+1;
				var main = $("#"+j);
				var data = cache;
				
				main.find(".max").text((parseFloat(data['days'][i].max)*9/5+32).toFixed(0)+"°");
				main.find(".min").text((parseFloat(data['days'][i].min)*9/5+32).toFixed(0)+"°");
				
				main.find(".night-z").text((parseFloat(data['days'][i].night)*9/5+32).toFixed(0)+"°");
				main.find(".morning").text((parseFloat(data['days'][i].morning)*9/5+32).toFixed(0)+"°");
				main.find(".daytime").text((parseFloat(data['days'][i].daytime)*9/5+32).toFixed(0)+"°");
				main.find(".evening").text((parseFloat(data['days'][i].evening)*9/5+32).toFixed(0)+"°");
			}
			$("#ce").removeClass("actf");
			$("#fa").addClass("actf");
			e.preventDefault();
		});
		
		$("#ce").click(function (e) {
			for(var i = 0; i<11; i++) {
				var j = i+1;
				var main = $("#"+j);
				var data = cache;
				
				main.find(".max").text(data['days'][i].max+"°");
				main.find(".min").text(data['days'][i].min+"°");
				
				main.find(".night-z").text(data['days'][i].night+"°");
				main.find(".morning").text(data['days'][i].morning+"°");
				main.find(".daytime").text(data['days'][i].daytime+"°");
				main.find(".evening").text(data['days'][i].evening+"°");
			}
			$("#ce").addClass("actf");
			$("#fa").removeClass("actf");
			e.preventDefault();
		});		
		
		$("#menu a:first").click();
		
		$("#search-input").keyup(function () {
			$(".find li").hide();
			if($(this).val().trim().length > 3) {
				$("ul li.searchli").remove();
				$.post("/weather/get.php", {"search":$(this).val().trim()}).done(function (data) {
					$("ul.find").append(data);
				});
			} else {
				$("ul li.searchli").remove();
				$("ul.find li").show();
			}
		});
    });
  </script>
  </head>
  
<body class="day night">

<div id="preload" style="z-index:200; width:100%; height:100%; background:rgba(255, 255, 255, 0.8);  display:none;   position:absolute; bottom:0; top:0; left:0; right:0;">
<img style="left:50%; top:40%; margin-left:-32px; margin-top:-32px; margin:auto;" src="712.GIF">
</div>

<header>
<p class="cefa"><a id="ce" class="actf">°C</a> / <a id="fa" href="/">°F</a></p>
<h2><img src="logo.png"><span>Погода</span></h2>
<div class="main">
<div class="top ">
<!--
После нажатия на Добавить город
del-vis
-->
<ul id="menu">
<?php
foreach($cities as $k=>$c) {
	echo "<li><a href=\"".$k."\" id=\"".$k."\" class=\"cities\">".$c."</a><a class=\"del\" href=\"".$k."\">&times;</a></li>";
}
?>
<li><a href="#" style="border:0;" class="add-city"><img style="width:20px; margin-top:1px; margin-left:10px;"  src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABoAAAAaCAYAAACpSkzOAAABeUlEQVRIS7WWgVECMRRE91egJWgF0oHSgVSgVCAdWIJYgVqBUoFYAZRgB0oF6+zND5M7j1ySg8wwAzewL/v52R/DkRbJCwCPAM4BPJvZOpa2Y3BITgB8OiRIzs3sNXwYDSIpB1pycNXZ+B42CkTyHsATgCmA7xSsGuROJH4G4HcANq0Ckbz13asBVLIh2KoY5OV6AbB1FzmwtyJQBAn/eQ5MG5lkg3ogObDmO2a2zQIlIEOwSzNTo2AQlAE5BFtkH9gCSBemcjVOBpOhAhI0Z2b20Y223tKNgLTyLenoFJB/zXAqSAvksfJeMTYOlqu3dCSVWdeFoCzI3pEn8Y9DZooMAIoOhacCs29lQ2KQBFW2lZnpfbMSLosgMUgj9w5AS4BkeB47KobEIJ1ilUjZpGEWHAWn4VEVpAH5xWLjSnMfZGoKQW6iC0c1JICWAB4S3fYFYNkXKyUdKkcaXvHtZQdAWaXXuhuOJeKtc0RSjlSiRlxDqlYs9bs/sP+yv+YSeGQAAAAASUVORK5CYII="></a></li>
<li><a href="#" style="border:0;" class="add-city"><img style="width:30px; margin-top:-2px;"  src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAABbUlEQVRoQ+2X/U3DQAzFnyeAEWADVmGDdgQ2KBPABjACG8AGXaGd5KqTEukUtefcO4e4kfNvYse/9+z7EGzkkY1wIEC8ORmOhCMLKRCttZCwdNpwhJZuocBwZCFh6bRmjqSUdgA+hkreROSbrooINAEZIL6K/59E5Jmohw7pBrkCkYs5i8gTXRUR2AVyAyKX8SoiP0Q9dAgNUoHY//d8ZHoKxBsEBeIRohnEK0QTSErpBcAvgMfJRK4yE9NVYdaMeIeY5UgFgl4qGwNPAN61lbDqiAOIkVk9KcwB+QPw0Kii9efqSUGdkcGVNWHOAA5drTXKWoFxsWLNGvYJzPFKz7iAUVurLHwTG2LhTL5AlXeP8dWqzjQ54hmGAslA3tqMBlFg7udipbSZuhNb75hdjlRg1J3YJUjRZp9Dgbu7ubNbK9qbz6S1eouwiA8QCxUtc4Qjlmpa5ApHLFS0zBGOWKppkSscsVDRMsdmHLkA5pF6M3YIXCEAAAAASUVORK5CYII="></a></li>
<!--
<li><input class="search" type="text" placeholder="Введите название"><span><a href="/">Волчанск</a><a href="/">Вошанськ</a><a href="/">Воркута</a></span></li>
-->
</ul>
</div>


<h1 class="weather"><a style="top:10px;" href="#" id="city">Харьков</a></h1>
<h1 class="find" style="display:none;"><input autofocus class="search" type="text" id="search-input" placeholder="Введите название населенного пункта"></h1>
</div>
</header>
 
 
<div class="arrow" style="left:0;">
<img style="margin:auto; width:40px;" src="arrow2.png">
</div>

<div class="arrow" style="right:0;">
<img style="" src="arrow.png">
</div>

 
<content>

<img class="clouds" style="position:absolute; z-index:2;" src="clouds.png">

<ul class="weather">

<li id="1"><p>Вчера в <script>var t = new Date();
document.write(checkTime(t.getHours()));
document.write(":");
document.write(checkTime(t.getMinutes()));</script><span class="max">16°</span></p><img src="cloud.png"></li>

<li class="active" id="2" style="visibility: visible;"> 
	<a style="top:0px;" href="#"><p>Сегодня<span class="now max">14°</span></p><img src="cloud.png"></a>
	<ul >
	<li><p>Утром<span class="morning">16°</span></p><img class="morningimg" src="sun.png"></li>
	<li><p>Днем<span class="daytime">25°</span></p><img class="daytimeimg" src="sun.png"></li>
	<li><p>Вечером<span class="evening">23°</span></p><img class="eveningimg" src="sun-cloud.png"></li>
	<li><p>Ночью<span class="night-z">16°</span></p><img class="nightimg" src="moon.png"></li>
	</ul>
	<div class="info">
	<p class="main-info">Начинает холодать. Если вчера было прохладно, одевайтесь теплее.</p>
	<p class="p1">Вероятность осадков <s class="osadki">2</s>%</p>
	<p class="p2">Ветер: <s class="wind">3,0 м/с ЮВ</s></p>
	<p class="p3">6:32</p>
	<p class="p4">18:30</p>
	</div>
</li>

<li id="3">
	<a style="top:-20px;" href="#"><p><s class="weekday">Вс</s>, <s class="day-z">20</s> <s class="month">сентября</s><span><s class="max">29°</s><b class="min">19°</b></span></p><img src="sun-cloud.png"></a>
	<ul >
	<li><p>Утром<span class="morning">16°</span></p><img class="morningimg" src="sun.png"></li>
	<li><p>Днем<span class="daytime">25°</span></p><img class="daytimeimg" src="sun.png"></li>
	<li><p>Вечером<span class="evening">23°</span></p><img class="eveningimg" src="sun-cloud.png"></li>
	<li><p>Ночью<span class="night-z">16°</span></p><img class="nightimg" src="moon.png"></li>
	</ul>
	<div class="info">
	<p class="main-info">Начинает холодать. Если вчера было прохладно, одевайтесь теплее.</p>
	<p class="p1">Вероятность осадков <s class="osadki">2</s>%</p>
	<p class="p2">Ветер: <s class="wind">3,0 м/с ЮВ</s></p>
	<p class="p3">6:32</p>
	<p class="p4">18:30</p>
	</div>
</li>

<li id="4">
	<a style="top:-20px;" href="#"><p><s class="weekday">Вс</s>, <s class="day-z">20</s> <s class="month">сентября</s><span><s class="max">29°</s><b class="min">19°</b></span></p><img src="sun-cloud.png"></a>
	<ul >
	<li><p>Утром<span class="morning">16°</span></p><img class="morningimg" src="sun.png"></li>
	<li><p>Днем<span class="daytime">25°</span></p><img class="daytimeimg" src="sun.png"></li>
	<li><p>Вечером<span class="evening">23°</span></p><img class="eveningimg" src="sun-cloud.png"></li>
	<li><p>Ночью<span class="night-z">16°</span></p><img class="nightimg" src="moon.png"></li>
	</ul>
	<div class="info">
	<p class="main-info">Начинает холодать. Если вчера было прохладно, одевайтесь теплее.</p>
	<p class="p1">Вероятность осадков <s class="osadki">2</s>%</p>
	<p class="p2">Ветер: <s class="wind">3,0 м/с ЮВ</s></p>
	<p class="p3">6:32</p>
	<p class="p4">18:30</p>
	</div>
</li>

<li id="5">
	<a style="top:-20px;" href="#"><p><s class="weekday">Вс</s>, <s class="day-z">20</s> <s class="month">сентября</s><span><s class="max">29°</s><b class="min">19°</b></span></p><img src="sun-cloud.png"></a>
	<ul >
	<li><p>Утром<span class="morning">16°</span></p><img class="morningimg" src="sun.png"></li>
	<li><p>Днем<span class="daytime">25°</span></p><img class="daytimeimg" src="sun.png"></li>
	<li><p>Вечером<span class="evening">23°</span></p><img class="eveningimg" src="sun-cloud.png"></li>
	<li><p>Ночью<span class="night-z">16°</span></p><img class="nightimg" src="moon.png"></li>
	</ul>
	<div class="info">
	<p class="main-info">Начинает холодать. Если вчера было прохладно, одевайтесь теплее.</p>
	<p class="p1">Вероятность осадков <s class="osadki">2</s>%</p>
	<p class="p2">Ветер: <s class="wind">3,0 м/с ЮВ</s></p>
	<p class="p3">6:32</p>
	<p class="p4">18:30</p>
	</div>
</li>

<li id="6">
	<a style="top:-20px;" href="#"><p><s class="weekday">Вс</s>, <s class="day-z">20</s> <s class="month">сентября</s><span><s class="max">29°</s><b class="min">19°</b></span></p><img src="sun-cloud.png"></a>
	<ul >
	<li><p>Утром<span class="morning">16°</span></p><img class="morningimg" src="sun.png"></li>
	<li><p>Днем<span class="daytime">25°</span></p><img class="daytimeimg" src="sun.png"></li>
	<li><p>Вечером<span class="evening">23°</span></p><img class="eveningimg" src="sun-cloud.png"></li>
	<li><p>Ночью<span class="night-z">16°</span></p><img class="nightimg" src="moon.png"></li>
	</ul>
	<div class="info">
	<p class="main-info">Начинает холодать. Если вчера было прохладно, одевайтесь теплее.</p>
	<p class="p1">Вероятность осадков <s class="osadki">2</s>%</p>
	<p class="p2">Ветер: <s class="wind">3,0 м/с ЮВ</s></p>
	<p class="p3">6:32</p>
	<p class="p4">18:30</p>
	</div>
</li>

<li id="7">
	<a style="top:-20px;" href="#"><p><s class="weekday">Вс</s>, <s class="day-z">20</s> <s class="month">сентября</s><span><s class="max">29°</s><b class="min">19°</b></span></p><img src="sun-cloud.png"></a>
	<ul >
	<li><p>Утром<span class="morning">16°</span></p><img class="morningimg" src="sun.png"></li>
	<li><p>Днем<span class="daytime">25°</span></p><img class="daytimeimg" src="sun.png"></li>
	<li><p>Вечером<span class="evening">23°</span></p><img class="eveningimg" src="sun-cloud.png"></li>
	<li><p>Ночью<span class="night-z">16°</span></p><img class="nightimg" src="moon.png"></li>
	</ul>
	<div class="info">
	<p class="main-info">Начинает холодать. Если вчера было прохладно, одевайтесь теплее.</p>
	<p class="p1">Вероятность осадков <s class="osadki">2</s>%</p>
	<p class="p2">Ветер: <s class="wind">3,0 м/с ЮВ</s></p>
	<p class="p3">6:32</p>
	<p class="p4">18:30</p>
	</div>
</li>

<li id="8">
	<a style="top:-20px;" href="#"><p><s class="weekday">Вс</s>, <s class="day-z">20</s> <s class="month">сентября</s><span><s class="max">29°</s><b class="min">19°</b></span></p><img src="sun-cloud.png"></a>
	<ul >
	<li><p>Утром<span class="morning">16°</span></p><img class="morningimg" src="sun.png"></li>
	<li><p>Днем<span class="daytime">25°</span></p><img class="daytimeimg" src="sun.png"></li>
	<li><p>Вечером<span class="evening">23°</span></p><img class="eveningimg" src="sun-cloud.png"></li>
	<li><p>Ночью<span class="night-z">16°</span></p><img class="nightimg" src="moon.png"></li>
	</ul>
	<div class="info">
	<p class="main-info">Начинает холодать. Если вчера было прохладно, одевайтесь теплее.</p>
	<p class="p1">Вероятность осадков <s class="osadki">2</s>%</p>
	<p class="p2">Ветер: <s class="wind">3,0 м/с ЮВ</s></p>
	<p class="p3">6:32</p>
	<p class="p4">18:30</p>
	</div>
</li>

<li id="9">
	<a style="top:-20px;" href="#"><p><s class="weekday">Вс</s>, <s class="day-z">20</s> <s class="month">сентября</s><span><s class="max">29°</s><b class="min">19°</b></span></p><img src="sun-cloud.png"></a>
	<ul >
	<li><p>Утром<span class="morning">16°</span></p><img class="morningimg" src="sun.png"></li>
	<li><p>Днем<span class="daytime">25°</span></p><img class="daytimeimg" src="sun.png"></li>
	<li><p>Вечером<span class="evening">23°</span></p><img class="eveningimg" src="sun-cloud.png"></li>
	<li><p>Ночью<span class="night-z">16°</span></p><img class="nightimg" src="moon.png"></li>
	</ul>
	<div class="info">
	<p class="main-info">Начинает холодать. Если вчера было прохладно, одевайтесь теплее.</p>
	<p class="p1">Вероятность осадков <s class="osadki">2</s>%</p>
	<p class="p2">Ветер: <s class="wind">3,0 м/с ЮВ</s></p>
	<p class="p3">6:32</p>
	<p class="p4">18:30</p>
	</div>
</li>

<li id="10">
	<a style="top:-20px;" href="#"><p><s class="weekday">Вс</s>, <s class="day-z">20</s> <s class="month">сентября</s><span><s class="max">29°</s><b class="min">19°</b></span></p><img src="sun-cloud.png"></a>
	<ul >
	<li><p>Утром<span class="morning">16°</span></p><img class="morningimg" src="sun.png"></li>
	<li><p>Днем<span class="daytime">25°</span></p><img class="daytimeimg" src="sun.png"></li>
	<li><p>Вечером<span class="evening">23°</span></p><img class="eveningimg" src="sun-cloud.png"></li>
	<li><p>Ночью<span class="night-z">16°</span></p><img class="nightimg" src="moon.png"></li>
	</ul>
	<div class="info">
	<p class="main-info">Начинает холодать. Если вчера было прохладно, одевайтесь теплее.</p>
	<p class="p1">Вероятность осадков <s class="osadki">2</s>%</p>
	<p class="p2">Ветер: <s class="wind">3,0 м/с ЮВ</s></p>
	<p class="p3">6:32</p>
	<p class="p4">18:30</p>
	</div>
</li>

<li id="11">
	<a style="top:-20px;" href="#"><p><s class="weekday">Вс</s>, <s class="day-z">20</s> <s class="month">сентября</s><span><s class="max">29°</s><b class="min">19°</b></span></p><img src="sun-cloud.png"></a>
	<ul >
	<li><p>Утром<span class="morning">16°</span></p><img class="morningimg" src="sun.png"></li>
	<li><p>Днем<span class="daytime">25°</span></p><img class="daytimeimg" src="sun.png"></li>
	<li><p>Вечером<span class="evening">23°</span></p><img class="eveningimg" src="sun-cloud.png"></li>
	<li><p>Ночью<span class="night-z">16°</span></p><img class="nightimg" src="moon.png"></li>
	</ul>
	<div class="info">
	<p class="main-info">Начинает холодать. Если вчера было прохладно, одевайтесь теплее.</p>
	<p class="p1">Вероятность осадков <s class="osadki">2</s>%</p>
	<p class="p2">Ветер: <s class="wind">3,0 м/с ЮВ</s></p>
	<p class="p3">6:32</p>
	<p class="p4">18:30</p>
	</div>
</li>


</ul>

<ul class="find" style="display:none;">
<li>
	<ul>
	<?php
		$result = mysqli_query($db, "SELECT `name`, `id` FROM `regions`");
		$i = 0;
		while($row = $result->fetch_assoc()) {
			echo "<li><a href=\"".$row["id"]."\" class=\"regions\">".$row["name"]."</a></li>";
			$i++;
		}
	?>
	</ul>
</li>
</ul>



</content>

<footer>
<p>Создали Т. Г. и С. В. в 2015 году</p>
</footer>

<div id="help" class="help hidden" style="z-index:200; width:100%; height:100%; background:rgba(255, 255, 255, 0.8);     position:absolute; bottom:0; display:flex; align-items: flex-end;">
<div style="width:90%; text-align:center; font-size:1.3rem;">

<p style="    top: 325;    left: 600;">↑ По горизонтали прогноз на 11 дней. Покрутите колесико или понажимайте на стрелки.</p>
<p style="    top: 600;    left: 350;">↑ Прогноз на день.</p>
<p style="    top: 45;    left: 290;">↑ Это сохраненные города. Нажмите «Добавить город» чтобы найти еще.</p>
<p style="    top: 15;    right: 100;">Переключатель шкалы. →</p>
</div>
<button class="button" id="button">?</button>
</div>

<script>
	document.getElementById('button').onclick = function() {
    document.getElementById('help').classList.toggle("hidden");
}




</script>
<style>
.help > div > p { color:#000; position:absolute;}

.hidden {
  visibility: hidden;
}
ul.visib > li > a, ul.visib > li > p, ul.visib > li > img{  visibility: visible; }
ul.visib > li > ul , ul.visib > li > div { visibility: hidden; }

.hidden .help {
  visibility: visible;
}

.help button {
outline:none;
border:1px solid #000;
    background: rgba(0,0,0,0.8);
    border: 0;
    color: #FFF;
    padding: 5px 20px;
    margin-right: 15px;
    border-radius: 8px;
    font-size: 1.05rem;
	margin-bottom:25px;
    transition: 0.3s;
	
}

.help button:last-child {
margin-right:2%;
margin-left:2%;
}

.help button:hover {
cursor:pointer;
background: #222;
}

.hidden #button {
 visibility: visible;
  mix-blend-mode: normal;
  background:rgba(0,0,0,0.2);
}


</style>



  </body>
</html>