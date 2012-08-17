function scriptify(data,transformation,transformations)
{
	for(var i = 0; i < transformations.length; i++)
	{
		var from = transformations[i].from;
		var to = transformations[i].to;
		helper3(data,transformation,from,to);
	}
}

function helper3(data,transformation,from,to)
{
	//alert(4 + ' ' + JSON.stringify(data) + '	' + JSON.stringify(transformation));
	//alert(1 + '	' + from + '	' + to);
	if(from.indexOf('.') != -1 && to.indexOf('.'))
	{
		var fsub = from.substr(0,from.indexOf('.'));
		var fend = from.substr(from.indexOf('.')+1);
		var tsub = to.substr(0,to.indexOf('.'));
		var tend = to.substr(to.indexOf('.')+1);
		//alert(2 + '	' + fsub + ' ' + tsub);
		if(fsub == "array"){
			for(var i = 0; i < data.length; i++)
			{
				if(typeof transformation[i] == "undefined")
					transformation[i] = {};
				helper3(data[i],transformation[i],fend,tend);
			}
		}
		else
		{
			helper3(data[fsub],transformation[tsub],fend,tend);
		}
	}
	else
	{
		transformation[to] = data[from];	
	}
}

function objectify(schema){
	var object;
	return helper2(schema,object);
}

function helper2(schema,object)
{
	if(typeof schema.properties != "undefined")
	{
		if(typeof schema.properties != "object")
			alert("Invalid JSON schema");
		object = {};
		helper2(schema.properties,object);
	}
	else if(typeof schema.items != "undefined")
	{
		object = [];
	}
	else
	{
		$.each(schema,function(key,value){
			if(typeof schema[key].properties != "undefined" || typeof schema[key].items != "undefined") {
				object[key] = helper2(schema[key],object[key]);
			}
		});
	}
	return object;
}

function schemafy(object){
	var schema = {};
	return helper(object,schema);
}

function helper(object,schema)
{
	if($.isArray(object))
	{
		schema.type = "array";
		schema.items = {};
		helper(object[0],schema.items);
	}
	else if(typeof object == "object")
	{
		schema.type = "object",
		schema.properties = {};
		$.each(object,function(key,value){
			schema.properties[key] = {};
			helper(value,schema.properties[key]);
		});
	}
	else if(typeof object == "string")
	{
		schema.type = "string";	
	}
	else if(typeof object == "number")
	{
		schema.type = "number";	
	}
	else if(typeof object == "boolean")
	{
		schema.type = "boolean";	
	}
	return schema;
}

function YQLtoJSON(xml,name)
{
	var json = {};
	json.name = name;
	var properties = {};
	$(xml).find("key").each(function(){
		var input = {};
		input.type = "string";
		if($(this).attr("required") == "true")
		{
			input.required = true;	
		}
		properties[$(this).attr("id")] = input;
	});
	json.properties = properties;
	return json;
}

function YQL(schema,data)
{
	var query = 'use "http://107.22.179.169/api/' + schema.name + '" as table; select * from table';
	var length = 0;
	$.each(schema.properties, function(key,value){
		length++;
	});
	if(length >  0)
		query += " where ";
	var first = true;
	$.each(schema.properties, function(key,value){
		if(typeof data[key] != "undefined"){
			if(!first)
				query += " and ";
			else
				first = false;
			query += key + '="';
			query += data[key];
			query += '"';
		}
	});
	return YQLQuery(query);
}

function YQLQuery(q)
{
	var data;
	var URL = "http://query.yahooapis.com/v1/public/yql?q=" + encodeURIComponent(q) + "&format=json";
	$.ajax({  
		"url":URL,  
		"dataType":"json",  
		"data": data,  
		"async":false,  
		"success": function(json){ 
			data = json;
		},
		"error":function(){
			throw "YQLQuery: " + URL;
		}
	});
	return data;
}

function JSONtoHTML(json)
{
	var table = "<ul>";
	table += helper4(json);
	table += "</ul>";
	return table;
}

function helper4(json)
{
	var table = "";
	$.each(json.properties,function(key,value){
		table += "<li>" + key;
		if(typeof value.properties != "undefined")
			table += "<ul>" + helper4(value) + "</ul>";
		if(typeof value.items != "undefined")
		{
			table += "<ul>" + helper4(value.items) + "</ul>";
		}
		table += "</li>";
	});
	return table;
}
