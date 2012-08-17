function scriptify(data,transformations) {
	var transform;
  $.each(transformations,function(index,value) {
		helper3(data,value.from,value.to);
	});
	
	function helper(data,from,to) {
    if(from.indexOf('.') != -1 && to.indexOf('.')) {
     var fsub = from.substr(0,from.indexOf('.'));
     var fend = from.substr(from.indexOf('.')+1);
     var tsub = to.substr(0,to.indexOf('.'));
     var tend = to.substr(to.indexOf('.')+1);
     if(fsub == "array") {
       for(var i = 0; i < data.length; i++) {
	       if(typeof transformation[i] == "undefined")
		       transformation[i] = {};
	       helper3(data[i],transformation[i],fend,tend);
       }
     }
     else {
       helper3(data[fsub],transformation[tsub],fend,tend);
     }
    }
    else {
     transformation[to] = data[from];	
    }
  }
}

/**
 * Generate a schema from a JSON datastructure.
 *
 */

function schemafy(object) {
	var schema = {};
	return helper(object,schema);
	
	function helper(object,schema)
  {
	  if($.isArray(object)) {
		  schema.type = "array";
		  schema.items = {};
		  helper(object[0],schema.items);
	  }
	  else if(typeof object == "object") {
		  schema.type = "object",
		  schema.properties = {};
		  $.each(object,function(key,value){
			  schema.properties[key] = {};
			  helper(value,schema.properties[key]);
		  });
	  }
	  else if(typeof object == "string") {
		  schema.type = "string";
	  }
	  else if(typeof object == "number") {
		  schema.type = "number";
	  }
	  else if(typeof object == "boolean") {
		  schema.type = "boolean";
	  }
	  return schema;
  }
}

function YQLtoJSON(xml) {
	var json = {};
	$(xml).find("key").each(function() {
	  input = {};
		var id = $(this).attr("id");
		input.type = $(this).attr("type");
		input.default = $(this).attr("default");
		json[id] = input;
	});
	return json;
}

function YQL(schema,data,continuation) {
	var query = 'use "https://raw.github.com/fabianbergmark/yql-tables/master/nasdaq/nordic.xml" as table; select * from table';
	var length = 0;
	if(data !== {})
		query += " where ";
	var first = true;
	$.each(data, function(key,value) {
			if(!first)
				query += " and ";
			else
				first = false;
			query += key + '="';
			query += value;
			query += '"';
	});
	YQLQuery(query, continuation);
}

function YQLQuery(query, continuation) {
	var data;
	var URL = "http://query.yahooapis.com/v1/public/yql?q=" + encodeURIComponent(query) + "&format=json";
	$.ajax({  
		"url":URL,
		"dataType":"json",
		"data": data,
		"success": function(json) {
			continuation(json);
		},
		"error":function() {
			throw "YQLQuery: " + URL;
		}
	});
}

function loadOpenTable(opentable,cps) {
  $.ajax({
    "url":opentable
    ,"datatype":"xml"
    ,"success":function(schema) {
      cps(schema);
    }
  });
}
