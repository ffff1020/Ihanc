<?php if (!defined('THINK_PATH')) exit();?><style>
#result{border:1px solid #817FB2; display:none;background-color: #F1F19B;z-index:999;float:left;}
#result li{background:#FFF; text-align:left;}
#result li.cls{text-align:right;}
#result ul{padding:0px; margin:0px;} 
#result li a{display:block; padding:4px;margin:0px; cursor:pointer; color:#666;text-decoration:none;font-size:12px}
#result li a:hover{background:#D8D8D8; text-decoration:none; color:#000;}
#result li a.try{background:#D8D8D8; text-decoration:none; color:#000;}

#tabs { margin:0; padding:0; list-style:none; overflow:hidden; font-size:20px}
#tabs li { float:left; display:block; padding:5px; background-color:#F7F7F7; margin-right:5px;}
#tabs li a { color:#000; text-decoration:none; }
#tabs li.current { background-color:#bbb;}
#tabs li.current a { color:#fff; text-decoration:none; }
#tabs li a.remove { color:#f00; margin-left:10px;}
#content { background-color:white;}
#content p { margin: 0; padding:20px 20px 100px 20px;}

table input[type="text"]{
	height:30px;
    border:none; 
	width:100%;
	border-width:0px;
	background-color:transparent;
    box-shadow:  none;
	font-size:14px;
}
table input[type="text"]:focus{
	background-color:white;
}
.bordered tr:last-child td:first-child {
    -moz-border-radius: 0 0 0 6px;
    -webkit-border-radius: 0 0 0 6px;
    border-radius: 0 0 0 0px;
}

.bordered tr:last-child td:last-child {
    -moz-border-radius: 0 0 6px 0;
    -webkit-border-radius: 0 0 6px 0;
    border-radius: 0 0 0px 0;
}
</style>
<script>
function getmember(obj){
	//alert("getmember");return;
	//$("#pcredit_info").html('');
	 var tx=$(obj); 
	 var key = event.keyCode; 
	 var lf=tx.offset().left;
     var tp=tx.offset().top+45;
	 if ($.trim(tx.val()).length == 0){
 	        $('#result').html('');$('#result').hide();
	        $("#sale_form input[name='idmember']").val('');
	        $("#pcredit_info").html('');
          return; }
	 if(((key>47)&&(key<106))||(key==8)||(key==32)){
	   $.post('/ihanc/eva/index.php/Home/sale/searchMember',{'key':tx.val()},function(data){
		  // alert(data);
		 var obj=$.parseJSON(data);  
		 if(data) {
			var trs = "<ul>";  
			len=obj.mname.length;
		    for (var i = 0; i < len; i++) {
			 trs+='<li style="list-style-type:none;"><a href="javascript: fillmember('+obj.idmember[i]+',\''+obj.mname[i]+'\')" title='+obj.idmember[i]+'>'+obj.mname[i]+'</a></li>';    
	        }; 
    	$('#result').css({'width':tx.width()-10});
	    $('#result').html(trs).css('display','block');
	    $('#result').show().offset({top:tp,left:lf}); 
		}else{
			$('#result').html('');$('#result').hide();
		    tx.val('');
		    }
		 li = $("#result").find("li:eq(" + index + ")");
	     li.css("background", "#D8D8D8").siblings().css("background", "#fff"); 
		});
	 }
};
  var index=0;
  var li;
  function selectmember(obj){
	var tx=$(obj); 
	var key = event.keyCode;  
    if (key == 13){
    	if($('#result').css('display')!='block'){
    		addTab();
    		return;
    	};
	 var idmember= $("#result li:eq(" + index + ") a").attr('title');
	    tx.val($("#result li:eq(" + index + ") a").text()); 
	    $("#sale_form input[name='idmember']").val(idmember);
	    $('#result').html('');$('#result').hide();
	    $.get("/ihanc/eva/index.php/Home/sale/getCredit",{'idmember':idmember},function(data){
			if(data!=0){
				$("#pcredit_info").html("<font color='red'>收欠款:￥"+data+"</font>");
			}else{
				$("#pcredit_info").html();
			}
		});
	  return;
	  };
  if (key == 38) {  
      index--; 
      if (index == 0) index = 9; 
  } ;
  if (key == 40) {  
      index++;  
      var len=$('#result li').length;
      if (index == len) index = 0;   
  }else index=0;
    li = $("#result").find("li:eq(" + index + ")");
	  li.css("background", "#D8D8D8").siblings().css("background", "#fff");
}
function fillmember(id,value){
	$("#sale_form input[name='mname']").val(value);
	$("#sale_form input[name='idmember']").val(id);
	$('#result').html('');$('#result').hide();
	$.get("/ihanc/eva/index.php/Home/sale/getCredit",{'idmember':id},function(data){
		if(data!=0){
			$("#pcredit_info").html("<font color='red'>收欠款:￥"+data+"</font>");
		}else{
			$("#pcredit_info").html('');
		}
	});
}
$('#pcredit_info').click(function(){
	if($('#dingwei').length!=0) return;
	var strHtml="<div id='dingwei'><img src='/ihanc/eva/Application/Home/View/public/images/ajax-loader-trans.gif'>&nbsp;&nbsp;加载中...</div>";
	$('body').append(strHtml);
	var divhtml="<div id='mcredit_detail'></div>";  //
	$('#main-content').append(divhtml);              //20160104
	var title=$('#sale_form input[name=mname]').val()+$(this).text();
	$.get("/ihanc/eva/index.php/Home/sale/mcreditDetail",
		{'idmember':$('#sale_form input[name=idmember]').val()},
		function(result){
			$('#dingwei').remove();
			$('#mcredit_detail').html(result);
			$('#mcredit_detail').dialog({
				 width:600,
				 maxHeight:600,
				 title:title,
				 modal: true,
			     close: function () { $(this).remove(); },// html('');
			     buttons: {
			         "收款":collect,
			         "打印":myprint,
			    	 "取消": function() { $(this).dialog( "close" ); },
			      },
			});
	});
});
function collect(){
	var sum=$('#mcreditDetail_sum').val();
	var mname=$("#sale_form input[name=mname]").val();
	if(isNaN(sum)){
		alert('请输入正确的付款金额!');
		return;
	} 
	var idsale=new Array();
	var idbank=$('#idbank').val();
	var cf=$('#cf').attr('checked');
	cf=cf?1:0;
	$('#mcreditDetail_tb input[name=idsale]').each(function(){
		 if ($(this).attr("checked")) {  
           idsale.push($(this).val());
		 }
	});
	$.post("/ihanc/eva/index.php/Home/sale/mcreditPayment",{
		'sum':sum,
		'idsale':idsale,
		'idbank':idbank,
		'cf':cf,
		'mname':mname,
		'idmember':$('#idmember').val(),
	},function(result){
		var data=$.parseJSON(result);
		if(data.status==1){
			$('#mcredit_detail').remove();
			$.get("/ihanc/eva/index.php/Home/sale/getCredit",{'idmember':$('#idmember').val()},function(data){
				if(data!=0){
					$("#pcredit_info").html("<font color='red'>收欠款:￥"+data+"</font>");
				}else{
					$("#pcredit_info").html('');
				}
			});
		}
		mynotice(data);
	});
}
function myprint(){
	var idmember=$('#idmember').val();
	var idsale=[];
	$('#mcreditDetail_tb input[name=idsale]').each(function(){
		 if ($(this).attr("checked")) {  
          idsale.push($(this).val());
		 }
	});
	$('#mcredit_detail').remove();
	if(idsale.length>0){
		$.ajax({
			 type:"GET",
			 url:"/ihanc/eva/index.php/Home/sale/saleprint",
			 data:{idsale:idsale,idmember:idmember},
			 async: false,
			 success: function(data){
				 myWindow=window.open();
				 myWindow.document.write(data);
				 myWindow.focus();
			 }
		});
	}else
	window.open("/ihanc/eva/index.php/Home/sale/saleprint/idmember/"+idmember);
}

function sale_print(){
	var idmember=$('#idmember').val();
	if(idmember==''){alert("请输入客户名称!");return} 
	var mname=$('#sale_form input[name=mname]').val();
	window.open("/ihanc/eva/index.php/Home/sale/salecheck/idmember/"+idmember+"/mname/"+mname);
}
</script>
<h1 class="page-title">即买即卖</h1>
<div class="container_12 clearfix leading">
<div class="grid_12">
 <form class="form" id="sale_form"  onkeydown="if(event.keyCode==13){return false;}">
 <input type="hidden" name='idmember' id="idmember">
 <div class="clearfix">
       <label  class="form-label">客户姓名<em>*</em></label>
       <div class="form-input"><input type="text"  name="mname" style="width:40%;display:inline"  onkeyup='getmember(this)' onkeydown='selectmember(this)'  /><p style="display:inline;font-size:18px" id="pcredit_info" title="点击打印或收欠款"></p></div>
 </div>
  <div class="form-action clearfix">
  <input type="button" value="录入" onclick="addTab()" />
  <a class="button" onclick="sale_print()" >今日账单</a>
</div>
</form>     
 </div>  
  <div class="clearfix">&nbsp;</div>
 <div class="grid_12">
 <ul id="tabs">
<!-- Tabs go here -->
</ul>
<div id="content" onkeydown="if(event.keyCode==13){return false;}">
<!-- Tab content goes here -->
</div>
 </div>
 
</div>
<section class=" grid_12 leading" style="width:98%">
  <div class="message info"><h6>温馨提示:即买即卖不修改库存，不支持收付款</h6></div>
                 </section>
<div id="result"></div>
<script>

function addTab() {
	// If tab already exist in the list, return
	var idmember=$('#idmember').val();
	if(idmember=='') return;
	if($("#tabs li").length>5){alert("已超出客户输入个数！");return;}
	if ($("#li" + idmember).length >0)
	{
		$("#content div").hide();
		$("#tabs li").removeClass("current");
		$("#li" + idmember+"_content").show();
		$("#li" + idmember).parent().addClass("current");
		return;
	}
	// hide other tabs
	$("#tabs li").removeClass("current");
	$("#content div").hide();
	// add new tab and related content
	$("#tabs").append("<li class='current'><a class='tab' id='li" +
	idmember + "'>" +$('#sale_form input[name="mname"]').val()+
	"</a><a class='remove'>x</a></li>");
	$("#content").append("<div id='li" + idmember + "_content'><table class='bordered' style='width:100%;font-size:12px'><tr><th>#</th><th>供应商</th><th>品名</th>" +
	 "<th>数量</th><th>买价</th><th>金额</th><th style='color:red'>售价</th><th style='color:red'>金额</th><th>备注</th></tr></table></div>");
	//return;
	var idtr;
	for (var l = 0; l < 5; l++) {
    	var timestamp=new Date().getTime();
    	if(l==0) idtr=timestamp;
    	var trHTML = "<tr id="+timestamp+"><td  width=8%><a><img src='/ihanc/eva/Application/Home/View/public/images/plus.png' alt='添加行' onclick='addNew(this);'/></a>&nbsp;&nbsp;<a><img src='/ihanc/eva/Application/Home/View/public/images/dd.png' alt='删除行' onclick='del(this);'/></td><td width=15%><input type='text' name='sname' onkeyup='getsupply(this)' onkeydown='selectSupply(this)' ;/><input type='hidden' name='idsupply'/></td><td width=15%><input type='text' name='gname' onkeyup='getgoods(this)' onkeydown='selectgoods(this)' ;/><input type='hidden' name='idgoods'/></td><td><input type='text' name='number' onkeydown=focNext() onblur=calsum(this) /></td><td><input type='text' name='price' onkeydown=focNext() onblur=calsum(this) /></td><td><input type='text'  name='sum'  tabIndex='-1' onkeydown=focNext() onblur=sum(this) ></input></td><td><input type='text' style='color:red;font-weight:bold; ' name='sprice' onkeydown=focNext() onblur=calSaleSum(this) /></td><td><input type='text' style='color:red;font-weight:bold; ' name='ssum'  tabIndex='-1' onkeydown=focNext() onblur=saleSum(this) ></input></td><td><input type='text'  name='remark'  tabIndex='-1' onkeydown=focNext()></input></td></tr>";
    	$("#li" + idmember + "_content table ").append(trHTML);
    }
	trHTML='<tfoot><tr><td colspan=3 style="padding-left:350px"><b>合计:</b></td><td><input type="text" name="ttl_num" readonly/></td><td colspan=2><input type="text" name="ttl_psum" readonly/></td><td colspan=2><input type="text" name="ttl_ssum" style="color:red;font-weight:bold" readonly/></td><td>'+
    '<input type="button" value="保存" onclick="save('+idmember+')" /></td></tr></tfoot>';
	$("#li" + idmember + "_content table").append(trHTML);
	$("#li" + idmember + "_content").show();
	$('#sale_form input[name="mname"]').val('');
	$("#pcredit_info").html('');
	$('#'+idtr+" td:nth(1)").find('input[name=sname]').focus(); 
	}
$('#tabs a.tab').live('click', function() {
	var contentname = $(this).attr("id") + "_content";
	$("#content div").hide();
	$("#tabs li").removeClass("current");
	$("#" + contentname).show();
	$(this).parent().addClass("current");
	});

$('#tabs a.remove').live('click', function() {
	var tabid = $(this).parent().find(".tab").attr("id");
	var contentname = tabid + "_content";
	$("#" + contentname).remove();
	$(this).parent().remove();
	if ($("#tabs li.current").length == 0 && $("#tabs li").length > 0) {
		var firsttab = $("#tabs li:first-child");
		firsttab.addClass("current");
		var firsttabid = $(firsttab).find("a.tab").attr("id");
		$("#" + firsttabid + "_content").show();
		}
});
function addNew(obj){
	var timestamp=new Date().getTime();
	var td=$(obj);
    var table=td.parents('table');
    var trHTML = "<tr id="+timestamp+"><td  width=8%><a><img src='/ihanc/eva/Application/Home/View/public/images/plus.png' alt='添加行' onclick='addNew(this);'/></a>&nbsp;&nbsp;<a><img src='/ihanc/eva/Application/Home/View/public/images/dd.png' alt='删除行' onclick='del(this);'/></td><td width=15%><input type='text' name='sname' onkeyup='getsupply(this)' onkeydown='selectSupply(this)' ;/><input type='hidden' name='idsupply'/></td><td width=15%><input type='text' name='gname' onkeyup='getgoods(this)' onkeydown='selectgoods(this)' ;/><input type='hidden' name='idgoods'/></td><td><input type='text' name='number' onkeydown=focNext() onblur=calsum(this) /></td><td><input type='text' name='price' onkeydown=focNext() onblur=calsum(this) /></td><td><input type='text'  name='sum'  tabIndex='-1' onkeydown=focNext() onblur=sum(this) ></input></td><td><input type='text' style='color:red;font-weight:bold; ' name='sprice' onkeydown=focNext() onblur=calSaleSum(this) /></td><td><input type='text' style='color:red;font-weight:bold; ' name='ssum'  tabIndex='-1' onkeydown=focNext() onblur=saleSum(this) ></input></td><td><input type='text'  name='remark'  tabIndex='-1' onkeydown=focNext()></input></td></tr>";
    table.append(trHTML);
}
function del(tdobject){
	var tr=$(tdobject).parents("tr");
	var table=$(tdobject).parents("table");
	$(tdobject).parents("tr").remove();
	var sum=tr.find("input[name='sum']").val();
	if(sum){
		var ttl=0;
		var num=0;
		var ttl_s=0;
   	    table.find('input[name="sum"]').each(function(i, o){
   		    var temp=$.trim($(o).val());	
   		    if(temp>0){
   		    ttl = ttl+parseInt(temp); 
   		   // alert(temp);
   		    var temp_num=$(o).parents("tr").find("input[name='number']").val();
		    num=num+parseFloat(temp_num);
		    var temp_s=$(o).parents("tr").find("input[name='ssum']").val();
		    ttl_s=ttl_s+parseInt(temp_s);
   		    }
   		});
   		table.find('tfoot input[name=ttl_num]').val(parseFloat(num));
   		table.find('tfoot input[name=ttl_psum]').val("￥"+ttl);
   		table.find('tfoot input[name=ttl_ssum]').val("￥"+ttl_s);
	}
	
}

function calsum(obj){
	var tx=$(obj);
	//if (event.keyCode == 13)
	//	//tx.parent().next().find("input").focus();
		 window.event.keyCode=9;
	if(isNaN(tx.val())){
		tx.val('');
		tx.parents("tr").find("input[name='sum']").val('');
		return;
	}
	var number=tx.parents("tr").find("input[name='number']").val();
	var price=tx.parents("tr").find("input[name='price']").val();
	var sum=Math.round(number*price);
	tx.parents("tr").find("input[name='sum']").val(sum);  
	if(sum){
		var ttl=0;
		var num=0;
		var table=tx.parents("table");
   	    table.find('tr input[name="sum"]').each(function(i, o){
   		    var temp=$.trim($(o).val());	
   		    if(temp>0){
   		    ttl = ttl+parseInt(temp); 
   		    var temp_num=$(o).parents("tr").find("input[name='number']").val();
		    num=num+parseFloat(temp_num);
   		    }
   		});
   	tx.parents('table').find('tfoot input[name=ttl_num]').val(parseFloat(num));
   	tx.parents('table').find('tfoot input[name=ttl_psum]').val("￥"+ttl);
   	
	}
}
function sum(obj){
	//if (event.keyCode == 13){ window.event.keyCode=9;}
	var tx=$(obj);
	var ttl=0;
		var table=tx.parents("table");
		    table.find('input[name="sum"]').each(function(i, o){
	   		var temp=$.trim($(o).val());	
	   		//alert(temp);
   		    if(temp>0){
   		    ttl = ttl+parseInt(temp); 
   		    }
   		});
   	tx.parents('table').find('tfoot input[name=ttl_psum]').val("￥"+ttl);
}

function saleSum(obj){
	//if (event.keyCode == 13){ window.event.keyCode=9;}
	var tx=$(obj);
	var ttl=0;
		var table=tx.parents("table");
		    table.find('input[name="ssum"]').each(function(i, o){
	   		var temp=$.trim($(o).val());	
	   		//alert(temp);
   		    if(temp>0){
   		    ttl = ttl+parseInt(temp); 
   		    }
   		});
   	tx.parents('table').find('tfoot input[name=ttl_ssum]').val("￥"+ttl);
}

function calSaleSum(obj){
	//if (event.keyCode == 13){ window.event.keyCode=9;}
	var tx=$(obj);
	if(isNaN(tx.val())){
		tx.val('');
		tx.parents("tr").find("input[name='sum']").val('');
		return;
	}
	var number=tx.parents("tr").find("input[name='number']").val();
	var price=tx.parents("tr").find("input[name='sprice']").val();
	var sum=Math.round(number*price);
	tx.parents("tr").find("input[name='ssum']").val(sum);  
	if(sum){
		var ttl=0;
		var num=0;
		var table=tx.parents("table");
   	    table.find('tr input[name="ssum"]').each(function(i, o){
   		    var temp=$.trim($(o).val());	
   		    if(temp>0){
   		    ttl = ttl+parseInt(temp); 
   		    }
   		});
   	tx.parents('table').find('tfoot input[name=ttl_ssum]').val("￥"+ttl);
	}
}
function getgoods(obj){
	 var tx=$(obj); 
	// var index=0;
	 var key = event.keyCode; 
	 var lf=tx.offset().left;
    var tp=tx.offset().top+30;
	 if ($.trim(tx.val()).length == 0){
  	        $('#result').html('');$('#result').hide();
  	        var id=tx.parents("tr").attr('id');
  	        $('#'+id).find("input[name='idgoods']").val('');
           return; }
	 if(((key>47)&&(key<106))||(key==8)||(key==32)){
	   $.post('/ihanc/eva/index.php/Home/Purchase/searchGoods',{'key':tx.val()},function(data){
		 var obj=$.parseJSON(data);  
		 if(data) {
			var trs = "<ul>";  
			len=obj.gname.length;
 		    for (var i = 0; i < len; i++) {
 		     var value=obj.gname[i]+"+"+obj.idgoods[i];
 			 trs+='<li style="list-style-type:none;"><a href="javascript: fillgoods('+tx.parents("tr").attr('id')+',\''+value+'\')" title='+obj.idgoods[i]+'>'+obj.gname[i]+'</a></li>';    
 	        }; 
     	$('#result').css({'width':tx.width()-10});
	    $('#result').html(trs).css('display','block');
	    $('#result').show().offset({top:tp,left:lf}); 
		}else{
			$('#result').html('');$('#result').hide();
			tx.val('');
		}
		 li = $("#result").find("li:eq(" + index + ")");
	     li.css("background", "#D8D8D8").siblings().css("background", "#fff"); 
		});
	 }
};
function selectgoods(obj){
	var tx=$(obj); 
	var key = event.keyCode;  
    if (key == 13){
	   if($('#result').css('display')!='block'){
		    window.event.keyCode=9;
  			return;
    	};
 	    var idgoods= $("#result li:eq(" + index + ") a").attr('title');
 	    tx.val($("#result li:eq(" + index + ") a").text()); 
 	    tx.parent().find("input[name='idgoods']").val(idgoods);
 	    $('#result').html('');$('#result').hide();
 	   //alert(tx.parent().parent.html());
 	   // return;
 	  };
   if (key == 38) { 
       index--; 
       if (index == 0) index = 9; 
   } ;
   if (key == 40) { 
       index++;  
       var len=$('#result li').length;
       if (index == len) index = 0;   
   }else index=0;
      li = $("#result").find("li:eq(" + index + ")");
	  li.css("background", "#D8D8D8").siblings().css("background", "#fff");
}
function fillgoods(id,value){
	 var arr=value.split("+");
	$('#'+id).find("input[name='gname']").val(arr[0]);
	$('#'+id).find("input[name='idgoods']").val(arr[1]);
	$('#result').html('');$('#result').hide();
}

function save(idmember){
	if($('#dingwei').length!=0) return;
	var inpdata=new Array();
	var insdata=new Array();
	$('#li'+idmember+'_content table input[name="sum"]').each(function(i, o){
	    if($.trim($(o).val())){
  		   var tr=$(o).parents('tr').attr('id');
  		   var idgoods=$('#'+tr+" input[name='idgoods']").val();
  		   var idsupply=$('#'+tr+" input[name='idsupply']").val();
  		  // alert(idgoods);
  		   if(idgoods==''){alert("请输入产品!");return false;}
  		   if(idsupply==''){alert("请输入供应商!");return false;}
  		   var pdata={
		    			'idsupply':idsupply,
		    			'idgoods':idgoods,
		    			'price':$('#'+tr+" input[name='price']").val(),
		    			'number':$('#'+tr+" input[name='number']").val(),
		    			'sum':$('#'+tr+" input[name='sum']").val(),
		    	  };	
  		 inpdata.push(pdata);
  		 var sdata={
	    			'idmember':idmember,
	    			'idgoods':idgoods,
	    			'price':$('#'+tr+" input[name='sprice']").val(),
	    			'number':$('#'+tr+" input[name='number']").val(),
	    			'sum':$('#'+tr+" input[name='ssum']").val(),
	    			'remark':$('#'+tr+" input[name='remark']").val(),
	    	  };	
		     insdata.push(sdata);
  		 }
	});
	if(insdata.length==0) return;
	if(inpdata.length==0) return;
	var strHtml="<div id='dingwei'><img src='/ihanc/eva/Application/Home/View/public/images/ajax-loader-trans.gif'>&nbsp;&nbsp;保存中...</div>";
	$('body').append(strHtml);
	//alert();return;
	$.post("/ihanc/eva/index.php/Home/purchase/psaleAdd",
  			 {'pdata':inpdata,'sdata':insdata},function(result){
  		    	$('#dingwei').remove();
  		    	if(result==0){
  		    		alert("即买即卖保存成功！");
  		    		var tabid = "li"+idmember;
  		    		var mname=$("#" + tabid).html();
  		    		var contentname = tabid + "_content";
  		    		$("#" + contentname).remove();
  		    		$("#" + tabid).parent().remove();
  		    		if ($("#tabs li.current").length == 0 && $("#tabs li").length > 0) {
  		    			var firsttab = $("#tabs li:first-child");
  		    			firsttab.addClass("current");
  		    			var firsttabid = $(firsttab).find("a.tab").attr("id");
  		    			$("#" + firsttabid + "_content").show();
  		    			}
  		    	}else{
  		    		alert(result);
  		    	}
  		  });
	
}

function getsupply(obj){
	 var tx=$(obj); 
	 var key = event.keyCode; 
	 var lf=tx.offset().left;
    var tp=tx.offset().top+30;
	 if ($.trim(tx.val()).length == 0){
  	        $('#result').html('');$('#result').hide();
  	        var id=tx.parents("tr").attr('id');
	        $('#'+id).find("input[name='idsupply']").val('');
           return; }
	 if(((key>47)&&(key<106))||(key==8)||(key==32)){
	   $.post('/ihanc/eva/index.php/Home/Purchase/searchSupply',{'key':tx.val()},function(data){
		 var obj=$.parseJSON(data);  
		 if(data) {
			var trs = "<ul>";  
			len=obj.sname.length;
 		    for (var i = 0; i < len; i++) {
 		     var value=obj.sname[i]+"+"+obj.idsupply[i];
 			 trs+='<li style="list-style-type:none;"><a href="javascript: fillsupply('+tx.parents("tr").attr('id')+',\''+value+'\')" title='+obj.idsupply[i]+'>'+obj.sname[i]+'</a></li>';    
 	        }; 
 	    $('#result').css({'width':tx.width()});
 	    $('#result').html(trs).css('display','block');
 	    $('#result').show().offset({top:tp,left:lf}); 
		}else{
			$('#result').html('');$('#result').hide();
		    tx.val('');
		    }
		 li = $("#result").find("li:eq(" + index + ")");
	     li.css("background", "#D8D8D8").siblings().css("background", "#fff"); 
		});
	 }
};
   var index=0;
   var li;
function selectSupply(obj){
	var tx=$(obj); 
	var key = event.keyCode;  
   if (key == 13){
	   if($('#result').css('display')!='block'){
		    window.event.keyCode=9;
   			return;
     	};
 	    var idsupply= $("#result li:eq(" + index + ") a").attr('title');
 	    tx.val($("#result li:eq(" + index + ") a").text()); 
 	    tx.parent().find("input[name='idsupply']").val(idsupply);
 	  $('#result').html('');$('#result').hide();
 	  return;
 	  };
   if (key == 38) {  
       index--; 
       if (index == 0) index = 9; 
   } ;
   if (key == 40) {  
       index++;  
       var len=$('#result li').length;
       if (index == len) index = 0;   
   }else index=0;
     li = $("#result").find("li:eq(" + index + ")");
	  li.css("background", "#D8D8D8").siblings().css("background", "#fff");
}
function fillsupply(id,value){
	 var arr=value.split("+");
	$('#'+id).find("input[name='sname']").val(arr[0]);
	$('#'+id).find("input[name='idsupply']").val(arr[1]);
	$('#result').html('');$('#result').hide();
}

function focNext(){
	if (event.keyCode == 13) window.event.keyCode=9;
	//$(obj).parent().next().find("input").focus();
}

</script>