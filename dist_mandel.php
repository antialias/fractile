<?php
date_default_timezone_set('America/New_York');

session_start();
$session_id = session_id();
$time = time();	

$action = false;
if(!empty($_REQUEST['action']))
	$action = $_REQUEST['action'];

switch($_SERVER['SERVER_NAME']) {
case 'localhost':
    $db = mysql_connect('localhost', 'root', 'root');
    mysql_select_db("dist", $db);
break;
default:
    $db = mysql_connect('localhost', 'antialias_di', '69c2cf8c');
    mysql_select_db("antialias_di", $db);
break;
}

switch($action) {
case 'polling_for_work':

	
	$sql = <<<SQL
	INSERT into job_list
	(
		session_id,
		current_job,
		last_polled
	) values (
		"$session_id",
		null,
		"$time"
	)
	ON DUPLICATE KEY UPDATE last_polled = "$time";
SQL;


	$r = mysql_query($sql);
	if(!$r)
		die(mysql_error());
	

	$sql = <<<SQL
	select * from job_list where session_id = "$session_id";
SQL;

	$r = mysql_query($sql);
	if(!$r)
		die(mysql_error());

	if($me = mysql_fetch_assoc($r)) {
		echo ($me['current_job']);
	}

	die();

break;
case 'submit_work':
	$job_description = $_REQUEST['job_description'];
	$job_hash = md5($job_description);
	$job_path = "job_cache/$job_hash";
	
	$img_data = base64_decode($_POST['img_data']);

	// store the completed job in the cache
	file_put_contents($job_path, $img_data);

	// take the job off the queue now that ie has bee completed
	$sql = <<<SQL
	update job_list set current_job=null where current_job="$job_description";
SQL;

	$r = mysql_query($sql);
	if(!$r)
		die(mysql_error());
	echo true;;

break;
case 'request_work':

	$job_hash = md5($_REQUEST['job_description']);
	$job_path = "job_cache/$job_hash";

	if(file_exists($job_path)) {
		echo json_encode(array("job_description" => $_REQUEST['job_description'], "coords" => array($_REQUEST['h'], $_REQUEST['v']), "url" => file_get_contents($job_path)));
	} else {

		$ready_users = <<<SQL
		select * from job_list where current_job is null and last_polled < ($time + 10);
SQL;

		$r = mysql_query($ready_users);
		if(!$r)
			die(mysql_error());

		// else put the work on the queue
		while($client = mysql_fetch_assoc($r)) {

			$job_description = addslashes($_REQUEST['job_description']);
			$client_session_id = $client['session_id'];
			$sql = <<<SQL
			update job_list set current_job="$job_description" where session_id="$client_session_id";
SQL;

			$ur = mysql_query($sql);
			if(!$ur)
				die(mysql_error());
		}

		echo json_encode(false);
	}
	die();
break;
default:



 $rows = 6;
 $cols = 6;

$thegrid = "";

$x= 0;
$y = 0;

for($x=0; $x < $rows; ++$x) {
	$thegrid .="<tr>";

	$y = 0;
	for($y = 0; $y < $cols; ++$y) {
		$thegrid .= "<td><img class = 'sync' id = 'g$x$y' /></td>";			
	}

	$thegrid .= "</tr>";
}



$html = <<<HTML

<!DOCTYPE html5>
<html>
<head>
</head>
<body>

<style>
.out {
	opacity:0;
}

img {
	border:0;
	padding:0;
	margin:0;
}
td {
	padding:0;
	margin:0;
	border:0;
}
.sync {
	width:100px;
	height:100px;
	display:block;
	position:inline;
	border:0;
}

.fractal_table {
	border-spacing:0;
	background-size:100%;
	-o-background-size:100%;
	-webkit-background-size:100%;
	-khtml-background-size:100%;
}



</style>


<table class = 'fractal_table' id = "main_fractal">

$thegrid

</table>


<!--<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3/jquery.min.js"></script>-->
<script type="text/javascript" src="jquery.js"></script>
<!--<script type="text/javascript" src="http://json.org/json2.js"></script>-->
<script type="text/javascript" src="json2.js"></script>
<script type="text/javascript" src="b64.js"></script>

<script type = "text/javascript" >




var the_top ;
var left ;
var the_bottom ;
var right ;
var MinX ;
var MaxX ;
var MinY ;
var MaxY ;
var max_iteration ;













var sx ;
var sy ;
//var max_iteration, MinX,MaxX,MinY,MaxY,dx,dy,ctx;

var Canvas = function(id,width,height,zindex){
  var c = document.createElement("canvas");
  c.setAttribute("width",width);
  c.setAttribute("height",height);
  c.setAttribute("id",id);
	c.style.zIndex = zindex;
  document.body.appendChild(c);
  return c.getContext("2d");
}


function calc_pixel(ca,cbi) {
	var iteration = 0;
  var old_a;
  var a = 0, b = 0;
  var length;
  do {
    old_a = a;
    a = a*a - b*b + ca;
    b = 2*old_a*b+cbi;
    iteration++;
    length = a*a+b*b;
  } while ((length < 4) && (iteration < max_iteration));
  return iteration;
}

//window.addEventListener("load",function ev() {
//  document.getElementById("c1").addEventListener("click",selectMinMax,false);
//  document.getElementById("png").addEventListener("click",convPNG,false);
//},false);

function plotSet() {
  for (var y = 0; y < sy;y++ ) {
      for (var x = 0; x < sx; x++) {
        ctx.save();
        c = calc_pixel(MinX+x*dx,MinY+y*dy);
        c1 = Math.round(max_iteration/4-c);
        c2 = max_iteration-c;
        c3 = ((max_iteration+96-c)<255)?(max_iteration+96-c):255;
        ctx.fillStyle =
"rgba("+((c1>0)?c1:0)+","+((c2>0)?c2:0)+","+((c3>0)?c3:0)+","+((max_iteration-c)/max_iteration)+")";
//"rgba("+((c1>0)?sin(c1)*255:0)+","+((cos(c2)*255>0)?c2:0)+","+((c3>0)?c3:0)+","+((max_iteration-c)/max_iteration)+")";
        ctx.translate(x,y);
        ctx.fillRect(0,0,1,1);
        ctx.restore();
      }
  window.status = (((y/sy)*100).toFixed(2))+"%";
  }
  window.status = "100%";
}

var oMinX, oMaxX, oMinY, oMaxY;

//<label>MinX: <input type="text" name="minx" value="-2"></label>
//<label>MaxX: <input type="text" name="maxx" value="1.25"></label>
//<label>MinY: <input type="text" name="miny" value="-1.25"></label>
//<label>MaxY: <input type="text" name="maxy" value="1.25"></label>
//<label>Iter: <input type="text" name="maxi" value="128"></label>

function plot(the_MinX, the_MaxX, the_MinY, the_MaxY, the_max_iteration) {
	MinX = the_MinX;
	MaxX = the_MaxX;
	MinY = the_MinY;
	MaxY = the_MaxY;
	max_iteration = the_max_iteration;


  dx = (MaxX-MinX)/sx;
  dy = (MaxY-MinY)/sy;
  oMinX = MinX;
  oMaxX = MaxX;
  oMinY = MinY;
  oMaxY = MaxY;
  ctx.fillStyle = "#026";
  ctx.fillRect(0,0,sx,sy);

  plotSet();

  return document.getElementById("c1").toDataURL();
}


points = new Array();
function selectMinMax(ev) {
	points = new Array();
	var frm = document.forms[0];
  
  points[points.length] = oMinX+ev.offsetX*dx;
  points[points.length] = oMinY+ev.offsetY*dy;
  if (points.length == 4) {
    if (points[0] >= points[2] ) {
      frm.minx.value = points[2];
      frm.maxx.value = points[0];
    } else {
      frm.minx.value = points[0];
      frm.maxx.value = points[2];
    }
    
    if (points[1] >= points[3]){
      frm.miny.value = points[3];
      frm.maxy.value = points[1];
    } else {
      frm.miny.value = points[1];
      frm.maxy.value = points[3];
    }
    points.length = 0;
  } 
}

function convPNG() {
//	var img = document.getElementById("c1").toDataURL();
//  var win = window.open(img,"mandel");
}

function resize(frm) {
  var s = frm.res.value.split(/,/);
	document.getElementById("c1").setAttribute("width",s[0]);
  document.getElementById("c1").setAttribute("height",s[1]);
  sx = s[0];
  sy = s[1];

  return false;
}

var ctx;

var current_timestamp = new Date();

jQuery('document').ready( function() {


        the_top = parseFloat("-2.75");
        left = parseFloat("-2");
        the_bottom = parseFloat("1.25");
        right = parseFloat("2");
        MinX = the_bottom;
        MaxX = the_top;
        MinY = left;
        MaxY = right;
        max_iteration = 25;

        sx = 100;
        sy = 100;


	ctx = new Canvas("c1",100,100,1);
	

	jQuery(".sync").addClass("out");

	jQuery(".sync").click(function(e) {

		current_timestamp = new Date();

		jQuery(".sync").addClass("out");

		jQuery("#main_fractal").css("background-image", "url("+jQuery(this).attr("src")+")");

		height = the_bottom - the_top;
		width  = right - left;

		vstep = height / $rows;
		hstep = width  / $cols;


		
		vind = jQuery(this).attr("id")[2];
		hind = jQuery(this).attr("id")[1];

		my_top    = the_top      + vind * vstep;
		my_left   = left     + hind * hstep;
		my_bottom = my_top   + vstep;
		my_right   = my_left  + hstep;



		the_top = my_top;
		left = my_left;
		the_bottom = my_bottom;
		right = my_right;

		MinX = the_bottom;
		MaxX = the_top;
		MinY = left;
		MaxY = right;

		

	});

	//volunteer_loop();
	//sync_loop();
	setInterval(volunteer_loop, 1000);
	setInterval(sync_loop, 1000);

	
	return 0;

});

is_processing = false;

// 		job_description = [my_top, my_left, my_bottom, my_right];
// function plot        (the_MinX, the_MaxX, the_MinY, the_MaxY, the_max_iteration) {

function do_work(job_description) {
//	ret = plot(job_description[0], job_description[2], job_description[1], job_description[3], 50);
	ret = plot(job_description[0], job_description[1], job_description[2], job_description[3], 200);
//	ret = plot(job_description[2], job_description[1], job_description[3], job_description[0], 50);
//	ret = plot(job_description[0], job_description[1], job_description[2], job_description[3], 50);
//	ret = plot(job_description[1], job_description[0], job_description[2], job_description[3], 50);
	return ret;

}

volunteer_loop = function () {
	jQuery.get(
		"dist_mandel.php",
		"action=polling_for_work",
		function (data) {

			if(data) { // work still needs to be done
				if(!is_processing) {
					
					job_description = data;

					is_processing = true;
					computed_data = do_work(job_description);
					
					jQuery.post(
						"dist_mandel.php?action=submit_work",
						{
							img_data: Base64.encode(computed_data),
							job_description:  JSON.stringify(job_description)
						},
						function(data) {
							is_processing = false;
						},
						"json"
					);

				}
			}
		},
		"json"
	);

};

sync_loop = function() {
	jQuery(".out").map( function () {

		height = the_bottom - the_top;
		width  = right - left;

		vstep = height / $rows;
		hstep = width  / $rows;


		
		vind = jQuery(this).attr("id")[2];
		hind = jQuery(this).attr("id")[1];

		my_top    = the_top      + vind * vstep;
		my_left   = left     + hind * hstep;
		my_bottom = my_top   + vstep;
		my_right   = my_left  + hstep;
		
		job_description = [my_top, my_bottom, my_left, my_right];
		coords=[hind, vind];
		caller = this;

		var request_timestamp = current_timestamp;
		jQuery.get(
			"dist_mandel.php",
			"action=request_work&h="+hind+"&v="+vind+"&job_description="+escape(JSON.stringify(job_description)),
			function(data) {
				if(request_timestamp.getTime() == current_timestamp.getTime()) {
					if(data) {
						toupdate = jQuery("#g"+data['coords'][0]+data['coords'][1]);
						toupdate.addClass("out");
						url = data['url'];
						coords = data['coords'];
						toupdate.attr("src", url);
						toupdate.attr("title", JSON.stringify(data['coords']) + "__" + JSON.stringify(data['job_description']));
						toupdate.removeClass("out");
					}
				}
			},
			"json"
		);
		

	});
}

</script>

</body>
</html>

HTML;
echo $html;

}


?>
