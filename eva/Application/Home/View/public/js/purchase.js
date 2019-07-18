
//搜索供应商
function getsupply(obj){
	 var tx=$(obj); 
	// var index=0;
	 var key = event.keyCode; 
	 var lf=tx.offset().left;
     var tp=tx.offset().top+30;
	 if ($.trim(tx.val()).length == 0){
   	        $('#result').hide();
            return; }
	 if(((key>47)&&(key<106))||(key==8)||(key==32)){
	   $.post('searchSupply',{'key':tx.val()},function(data){
		 var obj=$.parseJSON(data);  
		 if(data) {
			var trs = "<ul>";  
			len=obj.sname.length;
  		    for (var i = 0; i < len; i++) { 
  			 trs+='<li style="list-style-type:none;"><a href="javascript: fillgoods('+tx.parents("tr").attr('id')+',\''+obj.sname[i]+'\')">'+obj.sname[i]+'</a></li>';    
  	        }; 
      	$('#result').css({'width':tx.width()-10});
	    $('#result').html(trs).css('display','block');
	    $('#result').show().offset({top:tp,left:lf}); 
		}else
			$('#result').hide();
		 li = $("#result").find("li:eq(" + index + ")");
	     li.css("background", "#D8D8D8").siblings().css("background", "#fff"); 
		});
	 }
};
    var index=0;
	var li;
function selectSupply(){
	var key = event.keyCode;  
    if (key == 13){
  	 var str= $("#result li:eq(" + index + ") a").attr('title');
  	  fill(str);
  	  $('#result').hide();
  	  };
    if (key == 38) { /*向上按钮*/  
        index--; 
        if (index == 0) index = 9; //到顶了，   
    } ;
    if (key == 40) { /*向下按钮*/  
        index++;  
        var len=$('#result li').length;
        if (index == len) index = 0; //到顶了，   
    }else index=0;
      li = $("#result").find("li:eq(" + index + ")");
	  li.css("background", "#D8D8D8").siblings().css("background", "#fff");
}

function del(tdobject){
	var td=$(tdobject);  
	var tr=td.parents("tr");
	var sum=tr.find("input[name='sum']");
	if($("#tb tr").length>2)
		tr.remove();
	if(sum){
		var ttl=0;
   	    $('input[name="sum"]').each(function(i, o){
   		    var temp=$.trim($(o).val());	
   		    if(temp)
   		    ttl = ttl+parseInt(temp);   
   		});
   	// $('#ttl').val("￥"+ttl+"元");
	}
}