function load(box){
 var input = $('<input />');
 box.append(input);
	input.autocomplete({
		source: datatables,
		minLength: 3,
		select: function(event,ui){
			$(this).parent().loadAPI(ui.item.value);
			$(this).remove();
		}
	});
	box.addClass("noclick");
}

function run(data,next){
	//Make the link unclickable, undraggable and undroppable
	var $link = $(this);
	var $save = find($link.attr("id"));
	$save.selected = true;

	//Fetch the YQL Open Table structure
	
	$schema = YQLtoJSON(getTable($save.api),$save.api);
	
	//Adjust apperence
	
	$link.find("h1").fadeOut(0);
	
	//Insert inputs for all the YQL Data Table variables
	
	var i = 0;
	$.each($schema.properties,function(key,value){
		
		if(value.required == true){
			var val = key;
			var color = "LightSlateGray";
			var isset = "false";
			if(data[key] != undefined){
				val = data[key];
				color = "black";
				isset = "true";
			}
			$input = $('<input type="text" id="fields"  style="color:'+color+';" name="' + key + '" value="' + val + '" isset="'+isset+'" /></br>');
			
			//Eye candy
	
			$input.focus(function(event){
				if($(this).attr("isset") == "false"){
					$(this).val("");
					$(this).css({"color":"black"});
				}
			});
			$input.blur(function(event){
				if($(this).val() == ""){
					$(this).val($(this).attr("name"));
					$(this).attr("isset","false");
					$(this).css({"color":"LightSlateGray"});
				}
				else{
					$(this).attr("isset","true");
					data[key] = $(this).val();
				}
			});
			$link.append($input);
		}
		i++;
	});
	
	$link.append('<input type="button" id="run" value="Next" />');
	$link.find("#run").click(function(event,ui){
		if(event.target != this)
			return true;

		//Read user input
		var input = {};
		$link.find("#fields").each(function(){
			input[$(this).attr("name")] = $(this).val();
		});

		//Reset apperence
		
		$link.find("h1").fadeIn(100);
		$link.find("#fields").remove();
		$link.find("br").remove();
		
		//Fetch data
		try{
			for(var i = 0; i < 5; i++){
				try{
					data.data = YQL($schema,input);
					break;
				}
				catch(err){
					if(i == 4)
						throw err;	
				}
			};
			data.schema = schemafy(data.data);
			next(data);
		}
		catch(err){
			throw err;
		}
	});
}

jQuery.fn.loadAPI = function(api){

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
	});
	
	
	//Save info
	
	find($(this).attr("id")).api = api;
	
	//Start the chain on click if its the first element
	
	$(this).removeClass("noclick");
}
