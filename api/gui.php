<?php
function checkEmail($str)
{
	return preg_match("/^[\.A-z0-9_\-\+]+[@][A-z0-9_\-]+([.][A-z0-9_\-]+)+[A-z]{1,4}$/", $str);
}


function send_mail($from,$to,$subject,$body)
{
	$headers = '';
	$headers .= "From: $from\n";
	$headers .= "Reply-to: $from\n";
	$headers .= "Return-Path: $from\n";
	$headers .= "Message-ID: <" . md5(uniqid(time())) . "@" . $_SERVER['SERVER_NAME'] . ">\n";
	$headers .= "MIME-Version: 1.0\n";
	$headers .= "Date: " . date('r', time()) . "\n";

	mail($to,$subject,$body,$headers);
}

/* Database config */

$db_host		= 'localhost';
$db_user		= 'api';
$db_pass		= '';
$db_database	= 'API'; 

/* End config */

$link = mysql_connect($db_host,$db_user,$db_pass) or die('Unable to establish a DB connection');

mysql_select_db($db_database,$link);
mysql_query("SET names UTF8");

session_name('APILogin');
// Starting the session

session_set_cookie_params(2*7*24*60*60);
// Making the cookie live for 2 weeks

session_start();

if($_SESSION['id'] && !isset($_COOKIE['APIRemember']) && !$_SESSION['rememberMe'])
{
	// If you are logged in, but you don't have the APIRemember cookie (browser restart)
	// and you have not checked the rememberMe checkbox:

	$_SESSION = array();
	session_destroy();
	
	// Destroy the session
}


if(isset($_GET['logoff']))
{
	$_SESSION = array();
	session_destroy();
	
	header("Location: gui.php");
	exit;
}

if($_POST['submit']=='Login')
{
	// Checking whether the Login form has been submitted
	
	$err = array();
	// Will hold our errors
	
	
	if(!$_POST['username'] || !$_POST['password'])
		$err[] = 'All the fields must be filled in';
	if(!count($err))
	{
		$_POST['username'] = mysql_real_escape_string($_POST['username']);
		if(preg_match('/[^a-z0-9\-\_\.]+/i',$_POST['username']))
		{
			break;
		}		
		if(strlen($_POST['username']) > 12)
		{
			$err[]='To long username';
			break;
		}
		$_POST['password'] = mysql_real_escape_string($_POST['password']);
		if(strlen($_POST['password']) > 32)
		{
			$err[]='To long password';
			break;
		}
		$_POST['rememberMe'] = (int)$_POST['rememberMe'];
		
		// Escaping all input data

		$row = mysql_fetch_assoc(mysql_query("SELECT id,usr FROM API.members WHERE usr='{$_POST['username']}' AND pass='".md5($_POST['password'].'@somesalt')."'"));

		if($row['usr'])
		{
			// If everything is OK login
			
			$_SESSION['usr']=$row['usr'];
			$_SESSION['id'] = $row['id'];
			$_SESSION['rememberMe'] = $_POST['rememberMe'];
			
			// Store some data in the session
			
			setcookie('APIRemember',$_POST['rememberMe']);
		}
		else $err[]='Wrong username and/or password';
	}
	
	if($err)
	$_SESSION['msg']['login-err'] = implode('<br />',$err);
	// Save the error messages in the session

	header("Location: gui.php");
	exit;
}
else if($_POST['submit']=='Register')
{
	// If the Register form has been submitted
	break;
	$err = array();
	
	if(strlen($_POST['username'])<4 || strlen($_POST['username'])>12)
	{
		$err[]='Your username must be between 3 and 12 characters';
	}
	
	if(preg_match('/[^a-z0-9\-\_\.]+/i',$_POST['username']))
	{
		$err[]='Your username contains invalid characters';
	}
	
	if(!checkEmail($_POST['email']))
	{
		$err[]='Your email is not valid';
	}
	
	if(!count($err))
	{
		// If there are no errors
		
		$pass = substr(crypt($_SERVER['REMOTE_ADDR'].microtime().rand(1,100000)),0,8);
		// Generate a random password
		
		$_POST['email'] = mysql_real_escape_string($_POST['email']);
		$_POST['username'] = mysql_real_escape_string($_POST['username']);
		// Escape the input data
		
		
		mysql_query("INSERT INTO API.members(usr,pass,email,regIP,dt)
						VALUES(
						
							'".$_POST['username']."',
							'".md5($pass.'@somesalt')."',
							'".$_POST['email']."',
							'".$_SERVER['REMOTE_ADDR']."',
							NOW()
							
						)");
		
		if(mysql_affected_rows($link)==1)
		{
			send_mail('login@apicreator.com',
						$_POST['email'],
						'Your New Password',
						'Your password is: '.$pass);

			$_SESSION['msg']['reg-success']='We sent you an email with your new password';
		}
		else $err[]='This username is already taken';
	}

	if(count($err))
	{
		$_SESSION['msg']['reg-err'] = implode('<br />',$err);
	}	
	
	header("Location: gui.php");
	exit;
}

$script = '';

if($_SESSION['msg'])
{
	// The script below shows the sliding panel on page load
	
	$script = '
	<script type="text/javascript">
	
		$(function(){
		
			$("div#panel").show();
			$("#toggle a").toggle();
		});
	
	</script>';
	
}
?>


<!DOCTYPE HTML>
<html lang="en">
	<head>
		<script src="http://code.jquery.com/jquery-latest.js"></script>
		<script src="./functions.js"></script>
		<link rel="stylesheet" type="text/css" href="login_panel/css/slide.css" media="screen" />
		<script src="login_panel/js/slide.js" type="text/javascript"></script>
		<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.js"></script>
		<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css" />
        <link href="gui.css" rel="stylesheet" type="text/css" />
		<?php echo $script; ?>

		<script type="text/javascript">
			
			var workSpace = {};
			workSpace.boxes = [];
			var boxes = [];
			var outputs;
			var datatables;
			var zindex = 3;
			var id = 0;

			function find(id){
				for(var i = 0; i < workSpace.boxes.length;i++){
					if(workSpace.boxes[i].id == id){
						return workSpace.boxes[i];
					}
				}
			}
			function getObject(id){
				for(var i = 0; i < boxes.length;i++){
					if(boxes[i].id == id)
						return boxes[i];
				}
			}

			function getFunction(id){
				for(var i = 0; i < boxes.length;i++){
					if(boxes[i].id == id)
						return boxes[i].run;
				}
			}
			
			function getTable(table){
				var table;
				$.ajax({
					"url":table,
					"dataType": "xml",
					"async": false,
					"success":function(data){
						table = data;
					},
					"error":function(){
						alert("Error getting YQL Table");
					}
				});
				return table;
			}
			
			function showSchema(schema){
				$div = $('<div class="Inspect"></div>');
				$div.draggable({containment:"parent","opacity":0.45});
				$div.resizable();
				$div.append(JSONtoHTML(schema));
				$div.click(function(){$(this).remove()});
				$("#area").append($div);
			}

			//Dispalys the data

			$.fn.showData = function(){
				$div = $('<div class="Inspect"></div>');
				$div.draggable({containment:'parent',opacity:0.45});
				$div.resizable();
				$div.append(JSONtoHTML($(this).data));
				$div.click(function(){$(this).remove()});
				$("#area").append($div);
			}

			$.fn.resetChain = function(){
				$link = $(this);
				$save = find($link.attr("id"));
				while($save.previous != undefined){
					$link.droppable("enable");
					$link.draggable("enable");
					$link = $('#'+$save.previous);
					$save = find($save.previous);
				}
				$link.removeClass("noclick");
				$link.droppable("enable");
				$link.draggable("enable");
	
				$link = $(this);
				$save = find($link.attr("id"));
				while($save.next != undefined){
					$link.droppable("enable");
					$link.draggable("enable");
					$link = $('#'+$save.next);
					$save = find($save.next);
				}
				$link.droppable("enable");
				$link.draggable("enable");
			}

			//places the API-boxes in a structure
			
			function order(){
				var no = $("#area .API").length;
				var x = 80;
				var y = 80;
				var sw = false;
				for(var i = 0; i < no; i++){	
					if(find("area #API-"+i).next != undefined || find("area #API-"+i).previous != undefined)
						continue;
					$("#area #API-"+i).css({"left":x+"px","top":y+"px","z-index":2});
					if(sw){
						y += 150;
						x -= 200;
					}
					else
						x += 200;
					sw = !sw;
				}
			}
			
			$.fn.inspectAPI = function(api)
			{
				
				if($("#area #"+$(this).attr("id")+"-inspect").length)
					return true;
				$("#area").append('<div class="inspectAPI" id="' + $(this).attr("id") + '-inspect"><ul /></div>');
				$("#area #"+$(this).attr("id")+"-inspect").find("ul").append(JSONtoHTML(YQLtoJSON(getTable(data)))).draggable({"containment":"parent","opacity":0.45});
				$("#area #"+$(this).attr("id")+"-inspect").resizable().draggable({"containment":"parent","opacity":0.45}).click(function(event){
					if(event.target != this)
						return true;
					$(this).remove();
				});
			}
			
			//Handels relinking of the chain upon removal of link
			
			function unChain(){
				$(this).css("z-index",++zindex);
				$save = find($(this).attr("id"));
				if($save.previous != undefined){
					if($save.next != undefined){
						
						//previous.next = this.next
						
						find($('#'+$save.previous).attr("id")).next = $save.next;
						
						//next.previous = this.previous
						
						find($('#'+$save.next).attr("id")).previous = $save.previous;

						//Move every element after this to the right position
					
						$first = $('#'+$save.previous);
						$second = $('#'+$save.next);
						do{
							$second.css("left",$first.position().left+$first.width()*1.2);
							$first = $second;
							if(find($second.attr("id")).next != undefined)
								$second = $('#'+find($second.attr("id")).next);
						}while($first != $second);
					}
					else{ //No next?
						
						//make previous last link
						find($('#'+$save.previous).attr("id")).next = undefined;
						if(find($('#'+$save.previous).attr("id")).previous == undefined){
							$('#'+$save.previous).draggable("option",{"distance":1, "start":function(event,ui){
								$(this).css("z-index",++zindex);
							}});
						}
					}
				}
				else{ //No previous?
					if($save.next != undefined){
						//Makes next the first element
						
						find($('#'+$save.next).attr("id")).previous = undefined;
											
						$('#'+$save.next).removeClass("noclick");
						if(find($('#'+$save.next).attr("id")).next == undefined){
							$('#'+$save.next).draggable("option",{"distance":1, "start":function(event,ui){
								$(this).css("z-index",++zindex);
							}});
						}
					}
				}
				
				
				//Reset to original state
				
				
				$save.next = undefined;
				$save.previous = undefined;
				$(this).removeClass("noclick");
				$(this).draggable("option",{"distance":1,"start":function(event,ui){$(this).css("z-index",++zindex);}});
			};
			
			function drop(event,ui){	
				$draggable = find(ui.draggable.attr("id"));
				$(this).linkWith(ui.draggable);
				//ui.draggable.linkedWith($(this));
			}

			$.fn.linkWith = function(api){
				$save = find($(this).attr("id"));
				$with = find(api.attr("id"));
				
				//Only the first element in the chain is clickable
				
				api.addClass("noclick");
				api.draggable("option",{"distance":30,"start":unChain});

				//Just for good mesure
				
				$(this).draggable("option",{"distance":30,"start":unChain});
				
				if($save.next != undefined){ //Insert between this and this.next
					
					//new element.next = this.next
					
					$with.next = $save.next
					
					//this.next.prevous = new element
					
					find($('#'+$save.next).attr("id")).previous = api.attr("id");
				}
				//new element.previous = this
				
				$with.previous = $(this).attr("id");
				
				//this.next = new element
				
				$save.next = $with.id;
				$behind = $(this);
				$toMove = api;
				
				while(find($behind.attr("id")).next != undefined){
					$toMove.css({"top":$behind.position().top,"left":$behind.position().left+$behind.width()*1.2});
					$behind = $toMove;
					$toMove = $('#'+find($toMove.attr("id")).next);
				}
			}

			$.fn.linkedWith = function(api){
				$(this).addClass("noclick");
				$(this).draggable("option",{"distance":30,"start":unChain});
			}
			
			
			
			function clickStart(event){
				if(event.target != this)
					return true;
				if($(this).hasClass("noclick"))
					return true;
				$(this).addClass("noclick");
				$(this).start(); //Start the chain
			}

			function addBox(type,x,y){
				var box;
				$.ajax({
					"url":type,
					"dataType":"script",
					"ascync":false,
					"success":function(){
						box = create();
						box.attr("id",id);
						boxes.push({"id":id,"box":box,"run":run,"hooks":{"shrink":shrink,"grow":grow,"reset":reset}});
						workSpace.boxes.push({"id":id++});
						$("#area").append(box);
						box.css({"top":y,"left":x});
						box.fadeIn();
						box.draggable({"containment":"parent","opacity":0.45,"start":function(event,ui){$(this).css("z-index",++zindex);}});
						box.droppable({"drop":drop});
						box.on("click.start",clickStart);
					},
					"error":function(){
						alert("error");
					}
				});
				
			};
			
			function grow(){
				$(this).css({"width":"20%","height":"26%","z-index":++zindex});
				jQuery.fn.hook = getObject($(this).attr("id")).hooks.grow;
				$(this).hook();
				$(this).on("click.shrink",function(){
					if(event.target != this)
						return true;
					$(this).shrink();
				});
				$(this).off("click.grow");
			};

			jQuery.fn.grow = grow;

			function shrink(){
				$(this).css({"width":"10%","height":"13%","opacity":1});
				jQuery.fn.hook = getObject($(this).attr("id")).hooks.shrink;
				$(this).hook();
				$(this).on("click.grow",function(event){
					if(event.target != this)
						return true;
					$(this).grow();
				});
				$(this).off("click.shrink");
			}
			
			jQuery.fn.shrink = shrink;

			function reset(){
				$(this).shrink();
				$(this).off("click.grow");
				jQuery.fn.hook = getObject($(this).attr("id")).hooks.reset;
				$(this).hook();
			}

			jQuery.fn.reset = reset;

			function resetChain(chain){
				chain[0].box.removeClass("noclick");
				chain[0].box.on("click.start",clickStart);
				$.each(chain,function(index,value){
					value.box.reset();
					value.box.droppable("enable");
					value.box.draggable("enable");
				});
			}

			jQuery.fn.start = function(){
				var data = {};
				var box = find($(this).attr("id"));
				var chain = [getObject(box.id)];
				var next = box;
				while(next.next != undefined){
					var temp = getObject(next.next);
					chain.push(temp);
					next = find(next.next);
				}
				$.each(chain,function(index,value){
					value.box.droppable("disable");
					value.box.draggable("disable");
				});
				var func = [];
				
				func.push(function(data){
					chain[chain.length-1].box.shrink();
					chain[chain.length-1].box.off("click.grow");
					resetChain(chain);
				});
				for(var k = chain.length-1; k > 0; k--){
					eval("function temp(data){jQuery.fn.run = chain["+k+"].run;chain["+(k-1)+"].box.shrink();chain["+(k-1)+"].box.off('click.grow');chain["+k+"].box.grow();chain["+k+"].box.run(data,function(data){func[chain.length-"+k+"-1](data);});};");
					func.push(temp);
				}
				$(this).grow();
				jQuery.fn.run = chain[0].run;
				$(this).run(data,func[func.length-1]);
			}

			function saveWorkSpace(){
				if(workSpace.apis.length == 0){
					alert("Empty workspace");
					return;
				}
				workSpace.size = {};
				workSpace.size.width = $("#area").width();
				workSpace.size.height = $("#area").height();
				for(var i = 0; i < workSpace.apis.length; i++){
					workSpace.apis[i].position = $('#'+workSpace.apis[i].id).position();
				}
				$.ajax({
					"type":"POST",
					"url":"save.php",
					"dataType":"json",
					"data":{
						"json":JSON.stringify(workSpace)
					}
				});
			}
			
			function resize(){
				var yScale = $("#area").height()/workSpace.size.height;
				var xScale = $("#area").width()/workSpace.size.width;
				$.each(boxes,function(index,value){
					$api = value.box;
					$api.css({"top":$api.position().top*yScale,"left":$api.position().left*xScale});
				});
				workSpace.size.height = $("#area").height();
				workSpace.size.width = $("#area").width();
			}

			function loadDemos(){
				$.ajax({
					"url":"demo.php",
					"dataType":"json",
					"success":function(data){
						var x = 0;
						$.each(data,function(index,value){
							var demo = $('<div class="Saved" file="'+value+'"></div>');
							demo.css({"margin":"1%"});
							demo.click(function(event){
								$("#close").trigger("click");
								var file = $(this).attr("file");
								var json;
								$.ajax({
									"url":file,
									"dataType":"json",
									"async":false,
									"success":function(data){
										json = data;
									},
									"error":function(data){
										alert("Error loading saved workspaces");
									}
								});
								clearWorkSpace();
								$.each(json.apis,function(index,value){
									var api = addAPI(value.position.left*($("#area").width()/json.size.width) ,value.position.top*($("#area").height()/json.size.height));
									if(value.api != undefined)
										api.loadAPI(value.api);
									find(api.attr("id")).input = value.input;
								});
								$.each(json.apis,function(index,value){
									if(value.next != undefined){
										$('#'+value.id).linkWith($('#'+value.next));
									}
								});
								setOutput(json.stdout.name);
							});
							$.ajax({
								"url":value,
								"dataType":"json",
								"success":function(data){
									$.each(data.apis,function(index,value){
										var api = $('<div class="SmallAPI"></div>');
										api.css({"width":"10%","height":"13%","top":value.position.top*(50/data.size.height),"left":value.position.left*(70/data.size.width)});
										demo.append(api);
									});
								}
							});
							$("#Demos").append(demo);
							x += 70;
						});
					},
					"error":function(){
						alert("Error loading workspace");
					}
				});
				
			}
								

			/*function loadWorkSpace(){
				$.ajax({
					"url":"load.php",
					"dataType":"json",
					"success":function(data){
						var x = 0;
						$.each(data,function(index,value){
							var saved = $('<div class="Saved" file="'+value+'"></div>');
							
							saved.css({"margin":"1%"});
							saved.click(function(event){
								$("#close").trigger("click");
								var file = $(this).attr("file");
								var json;
								$.ajax({
									"url":file,
									"dataType":"json",
									"async":false,
									"success":function(data){
										json = data;
									},
									"error":function(data){
										alert("Error loading saved workspaces");
									}
								});
								clearWorkSpace();
								$.each(json.apis,function(index,value){
									var api = addAPI(value.position.left*($("#area").width()/json.size.width) ,value.position.top*($("#area").height()/json.size.height));
									if(value.api != undefined)
										api.loadAPI(value.api);
									find(api.attr("id")).input = value.input;
								});
								$.each(json.apis,function(index,value){
									if(value.next != undefined){
										$('#'+value.id).linkWith($('#'+value.next));
									}
								});
								setOutput(json.stdout.name);
							});
							$.ajax({
								"url":value,
								"dataType":"json",
								"success":function(data){
									$.each(data.apis,function(index,value){
										var api = $('<div class="SmallAPI"></div>');
										api.width(70*(170/data.size.width));
										api.height(50*(170/data.size.width));
										api.css({"top":value.position.top*(50/data.size.height),"left":value.position.left*(70/data.size.width)});
										saved.append(api);
									});
								}
							});
							$("#WorkSpace").append(saved);
							x += 70;
						});
					},
					"error":function(){
						alert("Error loading workspace");
					}
				});
				
			}*/
			
			function demo(){
				clearWorkSpace();
				$("#close").trigger("click");
				$div = $('<div style="position:absolute;top:'+($("#area").height()/2-$("#area").height()*0.15)+'px;left:'+($("#area").width()/2-$("#area").width()*0.2)+'px;width:40%;height:30%;z-index:2;background-color:#eee"></div>');
				$div.append('<h1>Main working area</h1>');
				$("#area").append($div);
				animateArrow($("#area").width()/2-$("#area").width()*0.2,$("#area").height()/2,function(){
					$div.remove();
					$nordic = addAPI($("#area").width()/2,$("#area").height()/2);
					animateArrow($("#area").width()/2,$("#area").height()/2);
					$nordic.find("input").val("../opentable/opentable/nordic.xml");
					$nordic.loadAPI($nordic.find("input").val());
					$wolfram = addAPI($("#area").width()/2+200,$("#area").height()/2);
					$wolfram.find("input").val("../opentable/opentable/wolfram.xml");
					$wolfram.loadAPI($wolfram.find("input").val());
					$nordic.linkWith($wolfram);
					$nordic.trigger("click");
					$nordic.find("#run").trigger("click");
				});
			}
			

			function animateArrow(x,y,complete){
				x -= 100;
				y -= 36;
				var movement = 40;
				var arrow = $('<div class="Arrow" ></div>');
				arrow.css({"top":y+"px","left":x+"px","z-index":3});
				$("#area").append(arrow);
				function move(d){
					if(d < 2){
						arrow.remove();
						if(complete != undefined)
							complete();
						return true;
					}
					arrow.animate({"left":"-="+d+"px"},{"duration":"slow","complete":function(){
						arrow.animate({"left":"+="+d+"px"},{"duration":"slow","complete":function(){
							move(Math.sqrt(d));
						}});
					}});
				}
				move(movement);
			}

			function clearWorkSpace(){
				$("#area").find(".API").remove();
				workSpace.apis = [];
				$("#area").find(".inspectAPI").remove();
			}
			
			function saveChain(){
				
			}
			
			$(document).ready(function(){
				$("#area").fadeIn('slow').click(function(event){
					if(event.target != this)
						return true;
					var x = event.pageX;
					var y = event.pageY;
					var pos = $(this).offset();
					x -= pos.left;
					y -= pos.top;
					addBox("../boxes/api.js",x,y);
				});
				$.ajax({
					"url":"datatables.php",
					"dataType":"json",
					"async":false,
					success:function(data){
						datatables = data;
					},
					"error":function(){
						alert("Error loading datatables");
					}
				});
				$.ajax({
					"url":"outputs.php",
					"dataType":"json",
					"async":false,
					"success":function(data){
						outputs = data;
					},
					"error":function(){
						alert("Error loading outputs");
					}
				});
				/*$("#area").append('<div class="Order"></div>');
				$("#area .Order").click(function(){
					order();
					return true;
				})*/
				$("#area").append('<div class="Output"></div>');
				$("#area .Output").click(function(){
					addBox("../boxes/output.js",200,200);
					return true;
				});
				$("#area .Output").hover(function(event){$("#area h2").css("display","none").text("Select output").fadeIn(200);},function(event){$("#area h2").fadeOut(100);});
				$("#area").append('<div class="Save"></div>');
				$("#area .Save").click(function(){
					saveWorkSpace();
					return true;
				});
				$("#area .Save").hover(function(event){$("#area h2").css("display","none").text("Save workspace").fadeIn(200);},function(event){$("#area h2").fadeOut(100);});
				$("#area").append('<div class="Clear"></div>');
				$("#area .Clear").click(function(){
					clearWorkSpace();
					return true;
				});
				$("#area .Clear").hover(function(event){$("#area h2").css("display","none").text("Clear workspace").fadeIn(200);},function(event){$("#area h2").fadeOut(100);});
				$("#area").append('<div class="ForLoop"></div>');
				$("#area .ForLoop").click(function(){
					addBox("../boxes/script.js",200,200);
					return true;
				});
				$("#area .ForLoop").hover(function(event){$("#area h2").css("display","none").text("Create script box").fadeIn(200);},function(event){$("#area h2").fadeOut(100);});

				//Add Bin

				var bin = $('<div class="Bin"></div>');
				bin.droppable({"drop":function(event,ui){ui.draggable.remove();}});
				$("#area").append(bin);

				/*$("#Demo").click(function(event){
					demo();
				});*/
				workSpace.size = {};
				workSpace.size.height = $("#area").height();
				workSpace.size.width = $("#area").width();
				$(window).resize(function(event){
					resize();
				});
			});
		</script>
	</head>
	<body>
	<div id="toppanel">
	<div id="panel">
		<div class="content clearfix">
			<div class="left">
				<h1>Demos</h1>
				<div id="Demos"></div>		
			</div>
		<?php
			if(!$_SESSION['id']):
		?>
		<div class="left">
			<!-- Login Form -->
			<h1>Login</h1>
			<form class="clearfix" action="" method="post">
			<?php			
				if($_SESSION['msg']['login-err'])
				{
					echo '<div class="err">'.$_SESSION['msg']['login-err'].'</div>';
					unset($_SESSION['msg']['login-err']);
				}
			?>
			<label class="grey" for="username">Username:</label>
			<input class="field" type="text" name="username" id="username" value="" size="23" />
			<label class="grey" for="password">Password:</label>
			<input class="field" type="password" name="password" id="password" size="23" />
	            	<label><input name="rememberMe" id="rememberMe" type="checkbox" checked="checked" value="1" /> &nbsp;Remember me</label>
        		<div class="clear"></div>
			<input type="submit" name="submit" value="Login" class="bt_login" />
			</form>
			</div>
			<div class="left right">			
				<!-- Register Form -->
				<form action="" method="post">
					<h1>Not a member yet? Sign Up!</h1>		
					<?php
						if($_SESSION['msg']['reg-err'])
						{
							echo '<div class="err">'.$_SESSION['msg']['reg-err'].'</div>';
							unset($_SESSION['msg']['reg-err']);
						}
						if($_SESSION['msg']['reg-success'])
						{
							echo '<div class="success">'.$_SESSION['msg']['reg-success'].'</div>';
							unset($_SESSION['msg']['reg-success']);
						}
					?>
					<label class="grey" for="username">Username:</label>
					<input class="field" type="text" name="username" id="username" value="" size="23" />
					<label class="grey" for="email">Email:</label>
					<input class="field" type="text" name="email" id="email" size="23" />
					<label>A password will be e-mailed to you.</label>
					<input type="submit" name="submit" value="Register" class="bt_register" />
				</form>
			</div>
			<?php
				else:
			?>
		<div class="left">
		<h1>Members panel</h1>
		<div id="WorkSpace"></div>
		<p>You can put member-only data here</p>
		<a href="registered.php">View a special member page</a>
		<p>- or -</p>
		<a href="?logoff">Log off</a>
		</div>
		<div class="left right"></div>
		<?php
			endif;
		?>
		</div>
	</div> <!-- /login -->	
	<!-- The tab on top -->
	<div class="tab">
		<ul class="login">
	    	<li class="left">&nbsp;</li>
	        <li>Hello <?php echo $_SESSION['usr'] ? $_SESSION['usr'] : 'Guest';?></li>
			<li class="sep">|</li>
			<li id="toggle">
				<a id="open" class="open" href="#"><?php echo $_SESSION['id']?'Open Panel':'Log In | Register';?></a>
				<a id="close" style="display: none;" class="close" href="#">Close Panel</a>			
			</li>
	    	<li class="right">&nbsp;</li>
		</ul> 
	</div> <!-- / top -->
	
</div>
		<div id="area">
		<h2></h2>
		</div>
	</body>
</html>

