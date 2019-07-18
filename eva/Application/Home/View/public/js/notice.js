function checkForm(id){
	var check=true;
	$(id+' input').each(function(){
		   // if($(this).attr('type')=="hidden"){return true;}
		    if($(this).attr('required')=='required'){
		    if(!$(this).val()){
		    	$(this).focus();
		    	check=false;
		    	return false;
		    	}
		    if($(this).val()==$(this).attr('placeholder')){
			$(this).focus();
			check=false;
			return false;
		    	}
		    }
	});
	return check;
}

function mynotice(data){
	$('#mynotice').length && $('#mynotice').remove();
	var str='<div id="mynotice" title="系统提示信息" style="dispaly:none"><p></p>'+
		     '<h3><p style="text-Align:center"><b>'+data.info+'!</b></p></h3></div>';
	$('#main-content').append(str);
	 $( "#mynotice" ).dialog({
		   autoOpen:false,
	       modal: true,
	       buttons: {
	         Ok: function() {
	           $( this ).dialog( "close" );
	         }
	       }
	     });
	 if(data.status==1){
	 $('.ui-widget-header').css("color","##1A7518");
	 }else $('.ui-widget-header').css("color","#cd0a0a");
	 $('#mynotice').dialog("open");
}

function myconfirm(data){
	$('#mynotice').length && $('#mynotice').remove();
	var str='<div id="mynotice" title="系统提示信息" style="dispaly:none"><p></p>'+
		     '<h3><p style="text-Align:center"><b>'+data+'</b></p></h3></div>';
	$('#main-content').append(str);
	 $( "#mynotice" ).dialog({
		   autoOpen:false,
	       modal: true,
	       buttons: {
	         Ok: function() {
	           $( this ).dialog( "close" );
	         }
	       }
	     });
	// if(data.status==1){
	 //$('.ui-widget-header').css("color","##1A7518");
	 //}else $('.ui-widget-header').css("color","#cd0a0a");
	 $('#mynotice').dialog("open");
}
