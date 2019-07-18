<?php if (!defined('THINK_PATH')) exit();?>
<style>
#result{border:1px solid #817FB2; display:none;background-color: #F1F19B;z-index:999;float:left;}
#result li{background:#FFF; text-align:left;}
#result li.cls{text-align:right;}
#result ul{padding:0px; margin:0px;} 
#result li a{display:block; padding:4px;margin:0px; cursor:pointer; color:#666;text-decoration:none;font-size:12px}
#result li a:hover{background:#D8D8D8; text-decoration:none; color:#000;}
#result li a.try{background:#D8D8D8; text-decoration:none; color:#000;}
</style>
<script>

$('#sale_form').ajaxForm({
	beforeSubmit: check,
	async: false,
    success: function(data){
    	$('#dingwei').remove();
    	$('#sale_form input[name=mname]').focus();
    	mynotice(data);
    	if(data.status==1){ 
    		$('#report').html('');
    		 if($('#save_name').attr('checked')){
    			 var id=$('#idmember').val();
    			 $.get("/ihanc/eva/index.php/Home/Sale/getCredit",{'idmember':id},function(data){
    					if(data!=0){
    						$("#credit_info").html("<font color='red'>收欠款:￥"+data+"</font>");
    					}else{
    						$("#credit_info").html('');
    					}
    				});
    		 }else{
    			 $('#idmember').val('');
    			 $('#sale_form input[name=mname]').val('');
    			 $("#credit_info").html('');
    		 };
    		 if($('#save_goods').attr('checked')){
    			 var inorder=$('#sale_form input[name=inorder]').val();
    			 var idgoods=$('#sale_form input[name=idgoods]').val();
    			 $.get("/ihanc/eva/index.php/Home/Sale/getStock",{'idgoods':idgoods,'inorder':inorder},function(data){
    				 $('#stock_info').html("库存数量:"+data);
    			 });
    		 }else{
    			 $('#sale_form input[name=inorder]').val('');
    			 $('#sale_form input[name=idgoods]').val('');
    			 $('#sale_form input[name=gname]').val('');
    			 $('#sale_form input[name=price]').val('');
    			 $('#stock_info').html('');
    		 }
    		 $('#result').html('');
    		 $('#sale_form input[name=number]').val('');
    		 $('#sale_form input[name=sum]').val('');
    		 $('#sale_form input[name=paid]').val('');
    		 $('#sale_form input[name=remark]').val('');
    		 
    		}
    	},  // post-submit callback
    dataType: 'json'
});
function check(){
	if($('#idmember').val()==''){alert("请输入客户!");return false;}
	var price=$("#sale_form input[name='price']").val();
	var number=$("#sale_form input[name='number']").val();
	var sum=$("#sale_form input[name='sum']").val();
	var paid=$("#sale_form input[name='paid']").val();
	if(paid=='') paid=0;
	if(isNaN(paid)){alert("请输入正确的付款金额!");return false;};
	//alert(isNaN(price));
	if((price=='')&&(number=='')&&(paid>0)){
		var html='<div id="dingwei"><img src="/ihanc/eva/Application/Home/View/public/images/ajax-loader-trans.gif">&nbsp;&nbsp;保存中,请稍候...</div>';
		$('body').append(html);
	   return true;
	};
	if(isNaN(price)||isNaN(number)||isNaN(sum)){
		$("#sale_form input[name='sum']").val(0);
		return false;
	}
	if($("#sale_form input[name='idgoods']").val()==''){alert("请输入商品!");return false;};
	var check=Math.abs(price*number-sum)/sum;
	if(check>0.2){
		alert("您修改的金额范围过大,请修改价格!");
		$("#sale_form input[name='sum']").val(price*number);
		return false;
		}
	var html='<div id="dingwei"><img src="/ihanc/eva/Application/Home/View/public/images/ajax-loader-trans.gif">&nbsp;&nbsp;保存中,请稍候...</div>';
	$('body').append(html);
	return true;
}

$('#member_add').dblclick(function(){
	$('#add_member input[name="mname"]').val('');
	$('#add_member input[name="msn"]').val('');
	$("#add_member").dialog({
	      height: 280,
	      width: 300,
	      modal: true,
	      buttons: {
	        "添加": add_s,
	        "取消": function() {
	        $(this).dialog( "close" );
	        }
	      },
	    });
});
function add_s(){
	var mname=$('#add_member input[name="mname"]').val();
	var msn=$('#add_member input[name="msn"]').val();
	if(mname&&msn){
		$.post("/ihanc/eva/index.php/Home/Sale/memberUpdate",{'mname':mname,'msn':msn},function(data){
			var obj=$.parseJSON(data);
			mynotice(obj);
			$("#add_member").dialog("close");
		});
	}else{
		alert("请输入客户信息!");
	}
}
//生拼音编码
function make_py(){
	var str = $('#add_member input[name="mname"]').val();
	var py=makePy(str);
	$('#add_member input[name="msn"]').val(py);
}

$('#credit_info').click(function(){
	var strHtml="<div id='dingwei'><img src='/ihanc/eva/Application/Home/View/public/images/ajax-loader-trans.gif'>&nbsp;&nbsp;加载中...</div>";
	$('body').append(strHtml);
	var divhtml="<div id='mcredit_detail'></div>";  //
	$('#main-content').append(divhtml);              //20150104
	var title=$('#sale_form input[name=mname]').val()+$(this).text();
	$.get("/ihanc/eva/index.php/Home/Sale/mcreditDetail",
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
	$.post("/ihanc/eva/index.php/Home/Sale/mcreditPayment",{
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
			$.get("/ihanc/eva/index.php/Home/Sale/getCredit",{'idmember':$('#idmember').val()},function(data){
				if(data!=0){
					$("#credit_info").html("<font color='red'>收欠款:￥"+data+"</font>");
				}else{
					$("#credit_info").html('');
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
			 url:"/ihanc/eva/index.php/Home/Sale/saleprint",
			 data:{idsale:idsale,idmember:idmember},
			// dataType: "json",
			 async: false,
			 success: function(data){
				 myWindow=window.open();
				 myWindow.document.write(data);
				 myWindow.focus();
			 }
		});
	}else
	window.open("/ihanc/eva/index.php/Home/Sale/saleprint/idmember/"+idmember);
}

function report(){
	$.get("/ihanc/eva/index.php/Home/Sale/saleReport",function(result){
		$('#report').html(result);
	});
}
</script>
 <h1 class="page-title">新建销售单</h1>
                <div class="container_12 clearfix leading">
                    <div class="grid_12">
 <form class="form" id="sale_form" action="/ihanc/eva/index.php/Home/Sale/saleAdd" method="post" onkeydown="if(event.keyCode==13){return false;}">
 <input type="hidden" name='idmember' id="idmember">
 <div class="clearfix">
                                <label for="form-email" class="form-label" id="member_add">客户姓名<em>*</em>
                                <div style="display:inline;float:right;font-size:11px"> <input type="checkbox" id="save_name" tabindex="-1" >保存该客户</div></label>
                                <div class="form-input"><input type="text"  name="mname" style="width:40%;display:inline" placeholder="请输入客户姓名或编码" onkeyup='getmember(this)' onkeydown='selectmember(this)'  /><p style="display:inline;font-size:18px" id="credit_info" title="点击打印或收欠款"></p></div>
 
 </div>
 <div class="clearfix">
                                <label for="form-email" class="form-label">库存商品名称<em>*</em>
                                <div style="display:inline;float:right;font-size:11px"><input type="checkbox" id="save_goods"  tabindex="-1">保存该商品</div></label>
                                <div class="form-input"><input type="text" id='gname' name="gname" style="width:40%;display:inline" placeholder="请输入商品名称或编码" onkeyup='getgoods(this)' onkeydown='selectgoods(this)' /><p  style="display:inline;font-size:18px" id="stock_info" title="点击查看进价"></p></div>
                            <input type="hidden" name='idgoods' >
                            <input type="hidden" name='inorder'>
                            </div>
                            
                            <div class="clearfix">
                            <label class="form-label">&nbsp;</label>
                            
                            <div style="display:inline-block;height:60px;width:10%;padding-top:6px"><font size=4 color='#003472'>数量</font><input type="text" name="number" style="height:65%;font-size:20px;color:red;font-weight: bold;width:100%"onblur='cal()' onkeydown='focNext()'/></div>
                            <font size=6 >×</font><div style="display:inline-block;height:60px;width:10%"><font size=4 color='#003472'>价格</font><input type="text" name="price" style="height:65%;font-size:20px;color:red;font-weight: bold;width:100%"onblur='cal()' onkeydown='focNext()'/></div>
                            <font size=6>=</font><div style="display:inline-block;height:60px;width:15%"><font size=4 color='#003472'>金额</font><input type="text" name="sum" style="height:65%;font-size:20px;color:red;font-weight: bold;width:100%" onkeydown='focNext()'/></div><font size=4>元</font>
                            </div>
                            
                            <div class="clearfix">
                                <label for="form-birthday" class="form-label">收款银行
                                <div style="display:inline;float:right;height:100%">
                                <select name='idbank' style="height:120%" tabindex="-1">
                                <?php if(is_array($bank_list)): $i = 0; $__LIST__ = $bank_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value=<?php echo ($vo["idbank"]); ?>><?php echo ($vo["bname"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>&nbsp;&nbsp;
                                </div>
                                </label>
                              <div class="form-input" >
                                <input type="text"  name="paid" style="width:40%;"  onkeydown='focSubmit()'/>
                                </div>
                            </div>
                            <div class="clearfix">
                                <label for="form-birthday" class="form-label">备注
                                
                                </label>
                              <div class="form-input" >
                                <input type="text"  name="remark"  tabindex="-1" />
                                </div>
                            </div>
                            <div class="form-action clearfix">
                               &nbsp; <button class="button" type="submit">保存</button>&nbsp;
                                <a class="button" onclick="report()" >查看当日销售报表</a>
                                <a class="button" onclick="sale_print()" >打印对账单</a>
                            </div>
  </form>     
                       <div class="clearfix"></div> <p></p><p></p>
                        <div id="report" ></div>          
                    </div>
                   
              </div>
              
              <div id="result"></div>
              
              <!-- 添加客户 -->
<div id="add_member" title="添加客户" style="display:none">
 <form action="/ihanc/eva/index.php/Home/Sale/memberUpdate" method="post">
    <fieldset>
      <label for="name" style="font-size:13px">客户名称:</label>
      <input type="text" name="mname" style="margin-bottom:10px; width:95%; padding: .4em;" onblur="make_py()">
      <label for="msn" style="font-size:13px">客户编码:</label>
      <input type="text" name="msn" style="margin-bottom:10px; width:95%; padding: .4em;">
    </fieldset>
  </form>
</div>
<!-- 
<div id="mcredit_detail" title="客户应付明细" style="display:none"></div> -->
<script>
function sale_print(){
	var idmember=$('#idmember').val();
	if(idmember==''){alert("请输入客户名称!");return} 
	var mname=$('#sale_form input[name=mname]').val();
	window.open("/ihanc/eva/index.php/Home/Sale/salecheck/idmember/"+idmember+"/mname/"+mname);
}
function getmember(obj){
	//alert("getmember");return;
	//$("#credit_info").html('');
	 var tx=$(obj); 
	 var key = event.keyCode; 
	 var lf=tx.offset().left;
     var tp=tx.offset().top+45;
	 if ($.trim(tx.val()).length == 0){
 	        $('#result').hide();
	        $("#sale_form input[name='idmember']").val('');
	        $("#credit_info").html('');
          return; }
	 if(((key>47)&&(key<106))||(key==8)||(key==32)){
	   $.post('/ihanc/eva/index.php/Home/Sale/searchMember',{'key':tx.val()},function(data){
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
			$('#result').hide();
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
    		$('#gname').focus();
    		return;
    	};
	 	var idmember= $("#result li:eq(" + index + ") a").attr('title');
	    tx.val($("#result li:eq(" + index + ") a").text()); 
	    $("#sale_form input[name='idmember']").val(idmember);
	    $('#result').hide();
	    $.get("/ihanc/eva/index.php/Home/Sale/getCredit",{'idmember':idmember},function(data){
			if(data!=0){
				$("#credit_info").html("<font color='red'>收欠款:￥"+data+"</font>");
			}else{
				$("#credit_info").html();
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
	$('#result').hide();
	$.get("/ihanc/eva/index.php/Home/Sale/getCredit",{'idmember':id},function(data){
		if(data!=0){
			$("#credit_info").html("<font color='red'>收欠款:￥"+data+"</font>");
		}else{
			$("#credit_info").html('');
		}
	});
}
//goods
function getgoods(obj){
	 var tx=$(obj); 
	 var key = event.keyCode; 
	 var lf=tx.offset().left;
   var tp=tx.offset().top+45;
	 if ($.trim(tx.val()).length == 0){
 	        $('#result').hide();
	        $("#sale_form input[name='idgoods']").val('');
	        $("#sale_form input[name='inoder']").val('');
	        $("#stock_info").html('');
          return; }
	 if(((key>47)&&(key<106))||(key==8)||(key==32)){
	   $.post('/ihanc/eva/index.php/Home/Sale/searchStockGoods',{'key':tx.val()},function(data){
		 // alert(data);
		 var obj=$.parseJSON(data);  
		 if(data) {
			var trs = "<ul>";  
			len=obj.gname.length;
		    for (var i = 0; i < len; i++) {
			 trs+='<li style="list-style-type:none;"><a href="javascript: fillgoods('+obj.idgoods[i]+',\''+obj.inorder[i]+'\','+i+')" title='+obj.stock[i]+'>'+obj.inorder[i]+":"+obj.gname[i]+'</a></li>';    
	        }; 
    	$('#result').css({'width':tx.width()-10});
	    $('#result').html(trs).css('display','block');
	    $('#result').show().offset({top:tp,left:lf}); 
		}else{
			
			$('#result').hide();
		    tx.val('');
		    }
		 li = $("#result").find("li:eq(" + index + ")");
	     li.css("background", "#D8D8D8").siblings().css("background", "#fff"); 
		});
	 }
};
  var index=0;
  var li;
function selectgoods(obj){
	var tx=$(obj); 
	var key = event.keyCode;  
  if (key == 13){
	  if($('#result').css('display')!='block'){
		  $("#sale_form input[name='number']").focus();
		  return;
  	   };
	    var str=$("#result li:eq(" + index + ") a").attr('href');
	    str=str.substr(22);
	    var strs= new Array(); 
	    strs=str.split(",");
	    var inorder=strs[1];
	    inorder=inorder.replace(/\'/g, "");
	    $("#sale_form input[name='idgoods']").val(strs[0]);
		$("#sale_form input[name='inorder']").val(inorder);
	    var stock= $("#result li:eq(" + index + ") a").attr('title');
	    tx.val($("#result li:eq(" + index + ") a").text()); 
	    $("#stock_info").html("库存数量:"+stock);
	    $('#result').hide();
	    var idmember=$("#sale_form input[name='idmember']").val();
	    $.get('/ihanc/eva/index.php/Home/Sale/itPrice',{'idgoods':strs[0],'idmember':idmember,'inorder':inorder},function(data){
	    	 var obj=$.parseJSON(data);
	    	 $("#sale_form input[name='price']").val(parseFloat(obj.price1).toFixed(2));
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

function fillgoods(id,value,index){
	$("#sale_form input[name='idgoods']").val(id);
	$("#sale_form input[name='inorder']").val(value);
	 var stock= $("#result li:eq(" + index + ") a").attr('title');
	    $('#gname').val($("#result li:eq(" + index + ") a").text()); 
	    $("#stock_info").html("库存数量:"+stock);
	    $('#result').hide();
	 var idmember=$("#sale_form input[name='idmember']").val();
	    $.get('/ihanc/eva/index.php/Home/Sale/itPrice',{'idgoods':id,'idmember':idmember,'inorder':value},function(data){
	    	 var obj=$.parseJSON(data);
	    	 $("#sale_form input[name='price']").val(parseFloat(obj.price1).toFixed(2));
	    });
	
}

function cal(){
	var price=$("#sale_form input[name='price']").val();
	var number=$("#sale_form input[name='number']").val();
	if(isNaN(price)||isNaN(number)){
		$("#sale_form input[name='sum']").val(0);
		return;
	}
	$("#sale_form input[name='sum']").val(Math.round(price*number));
}

$("#stock_info").click(function(){
	var idgoods=$("#sale_form input[name='idgoods']").val();
	var inorder=$("#sale_form input[name='inorder']").val();
	$.get("/ihanc/eva/index.php/Home/Sale/getInprice",{idgoods:idgoods,inorder:inorder},function(data){
		mynotice($.parseJSON(data));
	});
});

function focNext(){
	if (event.keyCode == 13) window.event.keyCode=9;
}
function focSubmit(){
	if (event.keyCode == 13) $("#sale_form button").click();
}
</script>