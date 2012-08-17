function create(){
	var api = $('<div class="API"></div>');
	return api;
}

function shrink(){
	$(this).find("textarea").fadeOut(0);
	$(this).find("input").fadeOut(0);
}

function grow(){
	$(this).find("input").fadeIn(100);
	$(this).find("textarea").fadeIn(100);
}

function run(data,next){
	//Make the link unclickable, undraggable and undroppable
	var $link = $(this);
	var $save = find($link.attr("id"));
	$save.selected = true;

	$link.append("<textarea />");
	$textarea = $link.find("textarea");
	$textarea.keydown(function(event){
		if(event.keyCode == 9){
			$(this).val($(this).val()+"	");
			event.preventDefault();
		}
	});

	$link.append('<input type="button" id="run" value="Next" />');
	$link.find("#run").click(function(event,ui){
		if(event.target != this)
			return true;

		//Read user input
		var code = $link.find("textarea").val();
		var transformation = "var func = function(object){" + code + "}; data = func(data);";
		eval(transformation);
		
		next(data);
	});
}

/*function next(){
	var $link = $(this);
	var $save = find($(this).attr("id"));
	$previous = find($save.previous);
	$link.addClass("noclick");
	$link.draggable("disable");
	$link.droppable("disable");
	$link.css("opacity",1);

	$link.on("click.shrink",function(event){
		if(event.target != this)
			return true;
		$link.find("h1").fadeIn(100);
		$save.selected = false;
		$link.find("textarea").remove();
		$link.find("#inspect").remove();
		$link.find("#run").remove();
		$link.css({"width":"10%","height":"13%"});
		$link.on("click.resume",function(){
			$link.off("click.resume");
			$link.next();
		});
		$link.off("click.shrink");
	});
	
	//Fetch the YQL Open Table structure
	
	$schema = YQLtoJSON(getTable($save.api),$save.api);
	
	//Adjust apparence
	
	$link.find("h1").fadeOut(0);
	$link.css({"height":"26%","width":"20%"});
	$save.selected = true;
	$link.css({"z-index":++zindex});

	var val = "";
	if($save.input[0] != undefined)
		val = $save.input[0];

	$textarea = $('<textarea id="transformation">'+val+'</textarea> ');
	
	//Make tab indentation possible
	
	
	
	$textarea.keyup(function(event){
		$save.input[0] = $(this).val();
	});
	$link.append($textarea);
	$link.append('<input type="button" id="run" value="Next" />');
	$link.append('<input type="button" id="inspect" class="inspect" value="Inspect" />');
		$link.find("#inspect").click(function(){
		showSchema($previous.data.schema);
		$save.inspect = true;
	});
	$link.find("#run").click(function(){
		
		//Reset apperence
		
		var code = $link.find("textarea").val();
		
		$link.find("h1").fadeIn(100);
		$save.selected = false;
		$link.find("textarea").remove();
		$link.find("#inspect").remove();
		$link.find("#run").remove();
		$link.css({"width":"10%","height":"13%"});
		$link.off("click.shrink");
		$data = {};
		
		
		//Evalute transformation script
		try{
			eval(transformation);
		
			//Fetch data
			for(var i = 0; i < 5; i++){
				try{
					$save.data.data = YQL($schema,$data);
					break;
				}
				catch(err){
					throw err;;
				}
			};
		
			//Evaluate data structure
		
			$save.data.schema = schemafy($save.data.data);
			if($save.next != undefined){
				 $next = $('#' + $save.next);
				 $next.next();
			}
			else{
				try{
					stdout($save.data.data);
				}
				catch(err){
					throw err;
				}
			}
		}
		catch(err){
			alert(err);
		}
		$link.resetChain();
	});
}*/

jQuery.fn.loadAPI = function(api){

	$(this).find("input").remove();

	//Extract the name of the API
	
	var rev = api.split("").reverse().join("");
	if(rev.indexOf('/') != -1)
		rev = rev.substr(0,rev.indexOf('/'));
	if(rev.indexOf('.') != -1)
		rev = rev.substr(rev.indexOf('.')+1);
	var name = rev.split("").reverse().join("");
	var h1 = $("<h1>"+name+"</h1>");
	var scale = 1;
	if(name.length > 7)
		scale = 7/name.length;
	h1.css({"width":"100%","height":"30%","font-size":$(this).width()*0.21*scale,"text-align":"center","position":"relative","top":"40%","padding-top":"0px","margin-top":"0px"});
	$(this).append(h1);
	
	$(this).find("h1").click(function(event){
		if(event.target != this)
			return true;
		$(this).parent().trigger("click.start");
	});
	
	//Make it droppable
	
	$(this).attr("chosen","true");
	
	//Save info
	
	find($(this).attr("id")).api = api;
	
	//Start the chain on click if its the first element
	
	$(this).removeClass("noclick");
	$(this).droppable({"drop":drop});
}
