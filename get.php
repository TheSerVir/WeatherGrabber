<?php
include "simple_html_dom.php";
$db = mysqli_connect("localhost", "root", "", "weather");
mysqli_query($db, "SET NAMES utf8");

function setImages($s) {
	$array1 = array("d100","d200","d300", "d412", "d411");
	$array2 = array("d110","d120","d210","d220","d310","d320");
	$array3 = array("d410", "n410", "n110", "n120", "n210", "n220", "n310", "n320", "n410");
	$array4 = array("d130", "d230", "d330", "d420", "d430", "n130", "n230", "n330", "n420", "n430");
	$array5 = array("d140", "d240", "d340", "d440", "n140", "n240", "n340", "n440");
	$array6 = array("d400","n400", "d421");
	$array7 = array("d500", "d000");
	$array8 = array("n100", "n200", "n300");
	$array9 = array("n500", "n000");
	$s = str_replace($array1, "sun-cloud.png", $s);
	$s = str_replace($array2, "sun-cloud-rain.png", $s);
	$s = str_replace($array3, "rain.png", $s);
	$s = str_replace($array4, "bigrain.png", $s);
	$s = str_replace($array5, "shtorm.png", $s);
	$s = str_replace($array6, "cloud.png", $s);
	$s = str_replace($array7, "sun.png", $s);
	$s = str_replace($array8, "moon-cloud.png", $s);
	$s = str_replace($array9, "moon.png", $s);
	return $s;
}

if(isset($_POST["city"])) {
	
    $result = mysqli_query($db, "SELECT `link`, `name` FROM `cities` WHERE `id` = ".$_POST["city"]);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
		
		$cit = json_decode($_COOKIE["cit"], true);
		if(!in_array($_POST["city"], $cit))
			$cit[$_POST["city"]] = $row["name"];
		setCookie("cit", json_encode($cit));
		
        echo "{\"city\":\"".$row["name"]."\" , \"days\":{";
        for($i = 0; $i < 11; $i++) {
            echo "\"".$i."\":{";
            
            if($i == 0) $str = "-1 day";
            elseif($i == 1) $str = "+0 day";
            else $str = "+".($i-1)." day";
            $date = date('Y-m-d', strtotime($str));
            
            $file = file_get_contents("http:".$row["link"]."/".$date);
            
            if($i<2) $number = 1;
            else $number = $i;
            
            //preg_match_all("/<div class=\"main loaded\" id=\"bd".$number."\">(.*?)<div class=\"mid".$number."\">&nbsp;<\/div>/s", $file, $res, PREG_SET_ORDER);
            
            /*
            preg_match("/<p class=\"date(.*?)\">(.*?)<\/p>/", $res[0][0], $r);
            $day = $r[2];
            
            preg_match("/<p class=\"month(.*?)\">(.*?)<\/p>/", $res[0][0], $r);
            $month = $r[2];
            */
            /*
            preg_match("/<div class=\"min\">мин. <span>(.*?)<\/span><\/div>/", $res[0][0], $r);
            $min = $r[1];
            
            preg_match("/<div class=\"max\">макс. <span>(.*?)<\/span><\/div>/", $res[0][0], $r);
            $max = $r[1];
           
            
            preg_match("/<div class=\"weatherIco (.*?)\" title=\"(.*?)\">/", $res[0][0], $r);
            $image = $r[1];
             */
            $html = str_get_html($file);
            
            /*
            preg_match("/<div class=\"infoDaylight\">(.*?)<\/div>/s", $file, $res);
            preg_match_all("/<span>(.*?)<\/span>/s", $res[0], $r);
            */

            
            $image = setImages(trim(str_replace("weatherIco", "", $html->find(".loaded .weatherIco", 0)->class)));
            
            $start = trim($html->find(".tabsContent .infoDaylight span", 0)->plaintext);
            $end = trim($html->find(".tabsContent .infoDaylight span", 1)->plaintext);
            
            $text = trim($html->find(".tabsContent .description", 0)->plaintext);
            
            $min = $html->find(".loaded .temperature .min", 0)->plaintext;
            $max = $html->find(".loaded .temperature .max", 0)->plaintext;
            
            $day = trim($html->find(".loaded p.date", 0)->plaintext);
            $month = trim($html->find(".loaded p.month", 0)->plaintext);
			
			$from = array("Понедельник", "Вторник", "Среда", "Четверг", "Пятница", "Суббота", "Воскресенье");
			$to = array("Пн", "Вт", "Ср", "Чт", "Пт", "Сб", "Вс");
			
			$weekday = str_replace($from, $to, trim($html->find(".loaded .day-link", 0)->plaintext));
            
            $winds = array("ЮВ", "Ю", "В", "СЗ", "З", "С");
            $wind_power = rand(5, 15) . " м/с " . $winds[rand(0,5)];
            
            if($i < 2) {
                $night = $html->find(".tabsContent table.weatherDetails .p2", 2)->plaintext;
                $morning = $html->find(".tabsContent table.weatherDetails .p4", 2)->plaintext;
                $daytime = $html->find(".tabsContent table.weatherDetails .p6", 2)->plaintext;
                $evening = $html->find(".tabsContent table.weatherDetails .p8", 2)->plaintext;
                
                $nightimg = setImages(trim(str_replace("weatherIco", "", $html->find(".tabsContent table.weatherDetails .p2 div", 0)->class)));
                $morningimg = setImages(trim(str_replace("weatherIco", "", $html->find(".tabsContent table.weatherDetails .p4 div", 0)->class)));
                $daytimeimg = setImages(trim(str_replace("weatherIco", "", $html->find(".tabsContent table.weatherDetails .p5 div", 0)->class)));
                $eveningimg = setImages(trim(str_replace("weatherIco", "", $html->find(".tabsContent table.weatherDetails .p8 div", 0)->class)));
                
                $osadki = trim($html->find(".tabsContent table.weatherDetails .p6", 7)->plaintext);
            } else {
                $night = $html->find(".tabsContent table.weatherDetails .p1", 2)->plaintext;
                $morning = $html->find(".tabsContent table.weatherDetails .p2", 2)->plaintext;
                $daytime = $html->find(".tabsContent table.weatherDetails .p3", 2)->plaintext;
                $evening = $html->find(".tabsContent table.weatherDetails .p4", 2)->plaintext;
                
                $nightimg = setImages(trim(str_replace("weatherIco", "", $html->find(".tabsContent table.weatherDetails .p1 div", 0)->class)));
                $morningimg = setImages(trim(str_replace("weatherIco", "", $html->find(".tabsContent table.weatherDetails .p2 div", 0)->class)));
                $daytimeimg = setImages(trim(str_replace("weatherIco", "", $html->find(".tabsContent table.weatherDetails .p3 div", 0)->class)));
                $eveningimg = setImages(trim(str_replace("weatherIco", "", $html->find(".tabsContent table.weatherDetails .p4 div", 0)->class)));
                
                $osadki = trim($html->find(".tabsContent table.weatherDetails .p3", 7)->plaintext);
            }
            
             echo "\"max\":\"".trim(str_replace(array("+", "&deg;", "макс."), "", $max)).
             "\", \"min\":\"".trim(str_replace(array("+", "&deg;", "мин."), "", $min)).
             "\", \"day\":\"".$day.
             "\", \"month\":\"".$month.
             "\", \"weekday\":\"".$weekday.
             "\", \"start\":\"".$start.
             "\", \"end\":\"".$end.
             "\", \"night\":\"".str_replace(array("+", "&deg;"), "", $night).
             "\", \"morning\":\"".str_replace(array("+", "&deg;"), "", $morning).
             "\", \"daytime\":\"".str_replace(array("+", "&deg;"), "", $daytime).
             "\", \"evening\":\"".str_replace(array("+", "&deg;"), "", $evening).
             "\", \"nightimg\":\"".$nightimg.
             "\", \"morningimg\":\"".$morningimg.
             "\", \"daytimeimg\":\"".$daytimeimg.
             "\", \"eveningimg\":\"".$eveningimg.
             "\", \"text\":\"".$text.
             "\", \"osadki\":\"".str_replace("-", "0", $osadki).
             "\", \"wind\":\"".$wind_power.
             "\", \"image\":\"".$image."\"";
            
            if($i < 10)
                echo "}, ";
            else
                echo "}";
        }
		echo "}}";

    } else {
        echo "{}";
    }
}

if(isset($_POST["area"])) {
    $result = mysqli_query($db, "SELECT `name`, `id` FROM `cities` WHERE `area_id` = ".$_POST["area"]);
    echo "<li><ul>";
	$i = 0;
	while($row = $result->fetch_assoc()) {
		echo "<li><a href=\"".$row["id"]."\" class=\"cities\">".$row["name"]."</a></li>";
		$i++;
		if($i == 25) {
			echo "</ul></li><li><ul>";
			$i = 1;
		} 
	}
    echo "</ul></li>";  
}

if(isset($_POST["region"])) {
    $result = mysqli_query($db, "SELECT `name`, `id` FROM `areas` WHERE `region_id` = ".$_POST["region"]);
    echo "<li><ul>";
	$i = 0;
	while($row = $result->fetch_assoc()) {
		echo "<li><a href=\"".$row["id"]."\" class=\"areas\">".$row["name"]."</a></li>";
		$i++;
	}
    echo "</ul></li>";  
}

if(isset($_POST["getregions"])) {
    $result = mysqli_query($db, "SELECT `name`, `id` FROM `regions`");
    echo "{";
    $i = 0;
    while($row = $result->fetch_assoc()) {
        echo "\"".$i."\":{\"name\"=>\"".$row["name"]."\", \"id\"=>\"".$row["id"]."\"},";
        $i++;
		if($i == 24) {
			echo "</ul></li><li><ul>";
			$i = 1;
		} 
    }
    echo "\"".$i."\":{}}";  
}

if(isset($_POST["remove"])) {
	$cit = json_decode($_COOKIE["cit"], true);
	if(isset($cit[$_POST["remove"]]))
		unset($cit[$_POST["remove"]]);
	setCookie("cit", json_encode($cit));
}

if(isset($_POST["search"])) {
    $result = mysqli_query($db, "SELECT `name`, `id` FROM `cities` WHERE `name` LIKE '%".$_POST["search"]."%' LIMIT 25");
    echo "<li class=\"searchli\"><ul>";
	$i = 0;
	while($row = $result->fetch_assoc()) {
		echo "<li><a href=\"".$row["id"]."\" class=\"cities\">".$row["name"]."</a></li>";
		$i++;
		if($i == 25) {
			echo "</ul></li><li class=\"searchli\"><ul>";
			$i = 1;
		} 
	}
    echo "</ul></li>";   
}
?>