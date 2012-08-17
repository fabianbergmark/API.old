var func = function(){
};

function create(){
	var output = $('<div class="API"><input /></div>');
	output.find("input").autocomplete({
		"source":outputs,
		"minLenght":3,
		"select":function(event,ui){
			var data = ui.item.value;
			setOutput(data);
			//Extract the name of the API
			
			var rev = data.split("").reverse().join("");
			if(rev.indexOf('/') != -1)
				rev = rev.substr(0,rev.indexOf('/'));
			if(rev.indexOf('.') != -1)
				rev = rev.substr(rev.indexOf('.')+1);
			var name = rev.split("").reverse().join("");
			var h1 = $("<h1>Output</br>"+name+"</h1>");
			var scale = 1;
			if(name.length > 7)
				scale = 7/name.length;
			h1.css({"width":"100%","height":"30%","font-size":$(this).width()*0.21*scale,"text-align":"center","position":"relative","top":"40%","padding-top":"0px","margin-top":"0px"});
			$(this).parent().append(h1);
			$(this).remove();
		}
	});
	return output;
}

function shrink(){
}

function grow(){
}

function run(data,next){
	$("#area").append('<div id="stdout" class="stdout"></div>');
	$("#area .stdout").css("z-index", ++zindex);
	$("#area .stdout").click(function(){$(this).remove();});
	func(data);
	next(data);
}

function setOutput(name){
	$.ajax({
		"url":name,
		"dataType":"script",
		"success":function(){
			func = output;
		},
		"error":function(){
			alert("There was an error loading module");
		}
	});
}
