function create(){
	$jmp = $('<div class="API"><h1>Jmp</h1></div>');
	$jmp.find("h1").css({"width":"100%","height":"30%","text-align":"center","position":"relative","top":"40%","padding-top":"0px","margin-top":"0px"});
	$jmp.functions = {};
	$jmp.functions.start = start;
	$jmp.functions.next = next;
}

function start(){
	if(saveJmp.next != undefined){
		$next = $('#'+saveJmp.next);
		$next.start();
	}
};
			
function next(){
	if(saveEnt.next == undefined){
		alert("Something is wrong with the loop");
		return;
	}
	$previous = find(saveJmp.previous);
	saveEnt.data = $previous.data;
	$('#'+saveEnt.next).next();
};

