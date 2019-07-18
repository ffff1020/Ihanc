<?php if (!defined('THINK_PATH')) exit();?><html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=IE9">
<script src="/ihanc/eva/Application/Home/View/public/js/jquery.min.js"></script>
<script src="/ihanc/eva/Application/Home/View/public/js/jquery-ui.js"></script>
<script src="/ihanc/eva/Application/Home/View/public/js/py.js"></script>
<title>瀚盛销售管理系统--实时销售</title>
<link rel="stylesheet" href="/ihanc/eva/Application/Home/View/public/css/reset.css" media="screen" />
<link rel="stylesheet" href="/ihanc/eva/Application/Home/View/public/css/ui.css" media="screen" />
<link rel="stylesheet" href="/ihanc/eva/Application/Home/View/public/css/jquery-ui.css"/>
<link rel="stylesheet" href="/ihanc/eva/Application/Home/View/public/css/jquery-ui-dialog.css"/>
<link rel="stylesheet" href="/ihanc/eva/Application/Home/View/public/css/mytable.css"/>
<style>
.sltr 
{
 background-color:#dcdcdc;
 color:black;
}
body{
    font: normal normal normal 11px/160% 'Lucida Grande', Verdana, Helvetica, Arial, sans-serif;
	background-color:#272727;
	overflow-x: hidden; 
	overflow-y: hidden; 
}
.itinput{
	background-color:#272727;
	border:none;
	color:white;
	font-size:150%;
	width:60%;
}
</style>
<style>
#result{border:1px solid #817FB2; display:none;background-color: #F1F19B;z-index:999;float:left;}
#result li{background:#FFF; text-align:left;}
#result li.cls{text-align:right;}
#result ul{padding:0px; margin:0px;} 
#result li a{display:block; padding:4px;margin:0px; cursor:pointer; color:#666;text-decoration:none;font-size:15px}
#result li a:hover{background:#D8D8D8; text-decoration:none; color:#000;}
#result li a.try{background:#D8D8D8; text-decoration:none; color:#000;}
</style>
<script type="text/javascript">
$(document).ready(function(){
	 init();	
	 $.get("/ihanc/eva/index.php/Home/Sale/number1",{},function(data){
		 $('#divnumber1').html(data);
	 });
	 $.get("/ihanc/eva/index.php/Home/Sale/intimeTable",{},function(data){
		 $('#divintimeTable').html(data);
	 });
});

function init()
{     
	try
{
    MSComm1 = new ActiveXObject("MSCOMMLib.MSComm.1");
    if ((typeof (MSComm1) == "undefined") || (MSComm1 == null))
    {
    	window.open("/ihanc/eva/Application/Home/View/public/lib/ihanc.exe");
    }
    else
    {
     var comstring;
     for (var i=1;i<17;i++)
		{
    	    /*if(MSComm1.PortOpen==true) MSComm1.PortOpen=false;
			MSComm1.CommPort = i;
			try{
				MSComm1.PortOpen = true ;
			}catch(err){
				continue;
			}
			MSComm1.PortOpen=false;*/
			comstring="<option value="+i+">COM"+i+"</option>";
	        $('#com').append(comstring);
		}
    }
}
catch (err)
{
    alert("请下载连接电子称所需要的控件！");
    window.open("/ihanc/eva/Application/Home/View/public/lib/ihanc.exe");
}
}  
function OperatePort(){
	if(MSComm1.PortOpen){MSComm1.PortOpen=false;$('#port').val("连接电子称");return; }
	var id=$('#com').val();
	MSComm1.CommPort = id;
    MSComm1.Settings="2400,N,8,1";  //华德：2400 耀华：9600
	MSComm1.OutBufferSize = 512;
    MSComm1.InBufferSize = 512;
    MSComm1.InputMode = 0;
    MSComm1.RThreshold = 12;
	MSComm1.OutBufferCount =0;           //清空发送缓冲区
    MSComm1.InBufferCount = 0;           //滑空接收缓冲区
    MSComm1.PortOpen = true;
    $('#port').val("关闭串口");
    setInterval("send()",500); 
	fn();
	
}
var fn=function(){
	function MSComm1::OnComm() {
    MSComm1_OnComm();
	}
}
//事件响应
function   MSComm1_OnComm()
{
    switch(MSComm1.CommEvent)
    {
       // case 1:{ window.alert("Send OK！"); break;}  //发送事件
        case 2: { 
			Receive();
			break;
			} //接收事件
        default: alert("Event Raised!"+MSComm1.CommEvent);
    }
}
function Receive(){
	//耀华表头的数据处理
	/*  var inputvalue = MSComm1.Input+'\n';
	  var index=inputvalue.indexOf('.')-5;
	  var temp=inputvalue.substr(index,8);
	  $('#dataIn').val(parseFloat(temp).toFixed(2));
	 */
	 //华德表头的数据处理
	var a=MSComm1.Input;
	var result='';
	for(var i = a.length; i > 0; i--) {
		    result+= a.charAt(i - 1);
		}
	 result=parseFloat(result)/100;
	 $('#dataIn').val(result.toFixed(1));
	}
function send(){
	//耀华表头
	//MSComm1.Output='R';
	//华德表头
	MSComm1.Output='a';
}
function netWeight(){
	MSComm1.Output='T';
}
</script>
</head>
<script>
document.onkeydown=function(){
	 var check=$("#update_sale").css('display');
	 if(check!='none'){if($("#update_sale").dialog("isOpen"))return;}
	 check=$("#updateNumber1").css('display');
	 if(check!='none'){if($("#updateNumber1").dialog("isOpen"))return;}
	 check=$("#number2").css('display');
	 if(check!='none'){if($("#number2").dialog("isOpen"))return;}
	 
	var opt=parseInt($('#opt').val());
	if(window.event.keyCode==16){  //清空表格 shift键
		 $("#saleinfo input").val('');
		 $("#saleinfo input[name='mname']").focus();
		 $('#opt').val(0);
		 $('stock_info').html('');
		 $('#number1').find('tr.sltr').removeClass("sltr");
		 $('#oTable').find('tr.sltr').removeClass("sltr");
		 return;
	}
	if(window.event.keyCode==39){ //->键进行切换
		$('#opt').val(2);
		$('#oTable').find('tr:eq(0)').addClass("sltr");
		$('#number1').find('tr.sltr').removeClass("sltr");
		$('#saleinfo input').blur();
		$('#saleinfo input').val('');
		return;
	}  
	if(window.event.keyCode==37){ //<-键进行切换
		$('#opt').val(1);
		$('#number1').find('tr:eq(0)').addClass("sltr");
		$('#oTable').find('tr.sltr').removeClass("sltr");
		$('#saleinfo input').blur();
		$('#saleinfo input').val('');
		return;
	}  
	 var opt=parseInt($('#opt').val());
	switch(opt)
	{
	case 2:
		var obj=$('#oTable');
	  break;
	case 1:
		var obj=$('#number1');
	  break;
	default:
		return;
	}
    var len=obj.find('tr').length-1;
    var index=obj.find('tr.sltr').index();
    switch(window.event.keyCode){
    case 38 :
    	var check=$("#number2").css('display');
	    if(check!='none'){if($("#number2").dialog("isOpen"))return;}
        if(index>0){
        	obj.find('tr.sltr').removeClass("sltr");
        	obj.find('tr:eq('+(index-1)+')').addClass("sltr");
        var offset1=obj.find('tr.sltr').offset();
        if(opt==1){
    	     var offset2=$('#divnumber1').offset();
        }else{
        	 var offset2=$('#divintimeTable').offset();
        }
    	var hmin=offset2.top;
        if(offset1.top<hmin){
        	var myset=obj.offset().top+21;
    		obj.offset({top:myset});
    		}
        }else{
        	obj.find('tr.sltr').removeClass("sltr");
        	obj.find('tr:eq('+len+')').addClass("sltr");
        	var offset0=obj.find('tr:eq(0)').offset();
        	var offset1=obj.find('tr:eq('+len+')').offset();
        	var hmax=$(document.body).height()-21;
        	if(offset1.top>hmax){
        		var myset=hmax-offset1.top+offset0.top;
        		obj.offset({top:myset});
        	 }
        }
        break;
    case 40 :
    	var check=$("#number2").css('display');
	    if(check!='none'){if($("#number2").dialog("isOpen"))return;}
    	obj.find('tr.sltr').removeClass("sltr");
        if(index==len){
        	obj.find('tr:eq(0)').addClass("sltr");
        	 if(opt==1){
        	     var offset2=$('#divnumber1').offset();
            }else{
            	 var offset2=$('#divintimeTable').offset();
            }
        	obj.offset({top:offset2.top});
        }else
          {
        	obj.find('tr:eq('+(index+1)+')').addClass("sltr");
        var offset1=obj.find('tr.sltr').offset();
    	var hmax=$(document.body).height()-21;
    	if(offset1.top>hmax){
    		var myset=obj.offset().top-21;
    		obj.offset({top:myset});
    	 }
        }
        break;
    case 13 :
    	if(opt==1){
    		var check=$("#number2").css('display');
    	    if(check!='none'){if($("#number2").dialog("isOpen"))return;}
    	    check=$("#updateNumber1").css('display');
    	    if(check!='none'){if($("#updateNumber1").dialog("isOpen"))return;}
    	$('#number2 input[name="weight"]').val($('#dataIn').val());
    	var idintime=obj.find('tr.sltr').attr('id');
    	var mname=$('#'+idintime).find('td:eq(1)').html()+":获取重量2";
    	$('#number2 input[name="title"]').val(mname);
    	$('#number2 input[name="idintime"]').val(idintime);
    	var price=parseFloat($('#'+idintime).find('td:eq(3)').html().substr(1));
    	var number=parseFloat($('#'+idintime).find('td:eq(4)').html())-parseFloat($('#dataIn').val());
    	number=Math.abs(number);
    	var sum=Math.round(price*number);
    	$('#number2 input[name="sum"]').val(sum);
    	$("#number2").dialog({
		      maxheight:450,
		      width: 300,
		      modal: true,
		      buttons: {
		      },
		      open: function (event, ui) {
		    	     $(".ui-dialog-titlebar").hide();
		    	     $('#number2 input[name="title"]').focus();
		           }
		    });
    	}
    	if(opt==2){
    		var check=$("#update_sale").css('display');
    	    if(check!='none'){if($("#update_sale").dialog("isOpen"))return;}
    		var mname=$('#oTable').find('tr.sltr').find('td:eq(1)').html();
    		var sum=$('#oTable').find('tr.sltr').find('td:eq(5)').html().substr(1);
    		if($('#oTable').find('tr.sltr').attr('id')){
    			$('#payment').dialog({
    				  maxheight:300,
      			      width: 300,
      			      modal: true,
      			      buttons: {
      			      },
      			      open: function (event, ui) {
      			    	     $(".ui-dialog-titlebar").hide();
      			    	     $("#payment input[name='title']").val(mname+":"+sum);
      			    	     $("#payment input[name='title']").focus();
      			    	     $('#payment input[name="sum"]').val(sum);
      			           }
   			    });
    		}
    	}
    	break;
    case 32:
    	//alert();
    	if(opt==2){
    		if($('#oTable').find('tr.sltr').attr('id')){
    			idsale=$('#oTable').find('tr.sltr').attr('id').substr(6);
    			$('#update_sale').dialog({
    				  maxheight:730,
     			      width: 300,
     			      modal: true,
     			      buttons: {
     			    	 "修改": function() {updateSale(idsale);},
     			    	 "删除": function() {delSale(idsale);},
     			    	 "取消": function() {$(this).dialog( "close" );}
     			      },
     			      open: function (event, ui) {
     			    	    $(this).find('input[name=mname]').val($('#oTable').find('tr.sltr').find('td:eq(1)').text());
     			    	    $(this).find('input[name=gname]').val($('#oTable').find('tr.sltr').find('td:eq(2)').text());
     			    	    $(this).find('input[name=price]').val($('#oTable').find('tr.sltr').find('td:eq(4)').text().substr(1));
     			    	    $(this).find('input[name=sum]').val($('#oTable').find('tr.sltr').find('td:eq(5)').text().substr(1));
     			    	    $(this).find('input[name=delpwd]').val('');
     			    	   $(this).find('input[name=price]').focus();
     			      }
    			});
    		}
    	}
    	if(opt==1){
    		var idintime=obj.find('tr.sltr').attr('id');
    		var title=obj.find('tr.sltr').find('td:eq(1)').html()+"--修改重量1：";
    		//alert(title);
    	    $('#updateNumber1 input[name="title"]').val(title);
    		//alert(idintime);
    		var price=obj.find('tr.sltr').find('td:eq(3)').html().substr(1);
    		//price=parseFloat(price);
    		$('#updateNumber1').dialog({
    			      maxheight:450,
    			      width: 300,
    			      modal: true,
    			      buttons: {
    			      },
    			      open: function (event, ui) {
    			    	     $(".ui-dialog-titlebar").hide();
    			    	     $('#updateNumber1 input[name="title"]').focus();
    			    	     $('#updateNumber1 input[name="number1"]').val($('#dataIn').val());
    			    	     $('#updateNumber1 input[name="price"]').val(price);
    			    	     $('#updateNumber1 input[name="idintime"]').val(idintime);
    			           }
    		});
    	}
        break;
   		default:
	}
}
</Script>
<body>
<div style="width:50%;display:inline;float:left;height:100%" class="myclass">
<div style="height:20%;z-index:999;background-color:#272727;" id="saleinfo">
<table style="color:white;font-size:250%;width:100%;">
<tr>
<td style="width:15%"><a onclick="addmember();">客户:</a><input type="hidden" name='idmember' id="idmember"></td>
<td><input type="text" name="mname" class='itinput' style="background-color:#272727;border:none;color:white;font-size:150%;width:60%;box-shadow:0px 0px;" onkeyup='getmember(this)' onkeydown='selectmember(this)'/>
</td>
<td width="30%"><div id='credit_info' style="font-size:80%;"></div></td>
</tr>
<tr>
<td>品名:<input type="hidden" name='idgoods' > <input type="hidden" name='inorder'></td>
<td><input type="text" name="gname" class='itinput' style="background-color:#272727;border:none;color:white;font-size:100%;width:95% ;box-shadow:0px 0px;" onkeyup='getgoods(this)' onkeydown='selectgoods(this)'/>
</td>
<td width="30%"><div id='stock_info' style="font-size:80%;"></div></td>
</tr>
<tr>
<td>售价:</td>
<td><input type="text" name="price" class='itinput' style="background-color:#272727;border:none;color:white;font-size:150%;width:60%;box-shadow:0px 0px;" onkeydown='getprice(this)'/><input type="hidden" name='price1'/><input type="hidden" name='price2'/> </td>
</tr>
</table>
</div>
<br/>
<div style="z-index:999;background-color:#272727;">
<div style="display:inline;height:30%;" >
<table>
<tr>
<td width=50%><input type="text" value="110.9" id="dataIn" class='itinput'  style="background-color:#272727;border:none;font-size:600%;width:90%;box-shadow:0px 0px;"  readonly /></td>
<td width=20%></td>
<td>
<table>
<tr><td><select id="com" style="width:100%"></select></td>
<td>&nbsp;<input type="button" value="连接电子称" onclick="OperatePort();" id='port' style="height:50px;"/></td></tr>
<tr>
<td><font color="white">收款银行：</font><br/><select id="idbank" >
<?php if(is_array($bank)): $i = 0; $__LIST__ = $bank;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["idbank"]); ?>"><?php echo ($vo["bname"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
</select></td>
<td >&nbsp;<input type="button" value="去皮" style="height:50px;width:95px" onclick="netWeight();"/></td></tr>
</table>
</td>
</tr>
</table>
</div>

</div>
<div style="background-color:#272727;z-index:999">
<hr style="height:2px;border:none;border-top:2px dotted #185598;" />
</div>
<div id='divnumber1' style="height:60%;z-index:-1">
</div>
</div>
<div id="divintimeTable" style="display:inline;width:49%;height:100%;float:left;border-left:2px dotted #185598">
</div>
<object id="plugin" type="application/npsyunew3-plugin"  style="width:0;height:0"></object>
<input type="hidden" value=0 id='opt'/>
<div id="result"></div>
<script>
function addmember(){
	//alert();
	$('#add_member input[name="mname"]').val('');
	$('#add_member input[name="msn"]').val('');
	$("#add_member").dialog({
	      height: 380,
	      width: 300,
	      modal: true,
	      draggable: false,
		  resizable: false,
	      buttons: {
	        "添加": add_s,
	        "取消": function() {
	        $(this).dialog( "close" );
	        }
	      }
	    });
}

function add_s(){
	var mname=$('#add_member input[name="mname"]').val();
	var msn=$('#add_member input[name="msn"]').val();
	if(mname&&msn){
		$.post("/ihanc/eva/index.php/Home/Sale/memberUpdate",{'mname':mname,'msn':msn},function(data){
			var obj=$.parseJSON(data);
			if(obj.status==1){
				alert("保存成功");
				$("#add_member").dialog("close");
			}
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

function getmember(obj){
	 var tx=$(obj); 
	 var key = event.keyCode; 
	 var lf=tx.offset().left;
     var tp=tx.offset().top+45;
	 if ($.trim(tx.val()).length == 0){
 	        $('#result').hide();
 	        $('#result').html('');
	        $("#saleinfo input[name='idmember']").val('');
	        $("#credit_info").html('');
	      //  keycheck=0;
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
//var keycheck=0;
function selectmember(obj){
	var tx=$(obj); 
	var key = event.keyCode; 
    if (key == 13){
	 var idmember= $("#result li:eq(" + index + ") a").attr('title');
	    tx.val($("#result li:eq(" + index + ") a").text()); 
	    $("#saleinfo input[name='idmember']").val(idmember);
	    $('#result').hide();
	    $.get("/ihanc/eva/index.php/Home/Sale/getCredit",{'idmember':idmember},function(data){
			if(data!=0){
				$("#credit_info").html("<font color='red'>收欠款:￥"+data+"</font>");
			}else{
				$("#credit_info").html();
			}
		});
	    $("#saleinfo input[name='gname']").focus();
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
	$("#saleinfo input[name='mname']").val(value);
	$("#saleinfo input[name='idmember']").val(id);
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
 	        $('#result').html('');
 	        index=0;
	        $("#saleinfo input[name='idgoods']").val('');
	        $("#saleinfo input[name='inoder']").val('');
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
	    var str=$("#result li:eq(" + index + ") a").attr('href');
	    str=str.substr(22);
	    var strs= new Array(); 
	    strs=str.split(",");
	    var inorder=strs[1];
	    inorder=inorder.replace(/\'/g, "");
	    $("#saleinfo input[name='idgoods']").val(strs[0]);
		$("#saleinfo input[name='inorder']").val(inorder);
	    var stock= $("#result li:eq(" + index + ") a").attr('title');
	    tx.val($("#result li:eq(" + index + ") a").text()); 
	    $("#stock_info").html("库存:"+stock);
	    $('#result').hide();
	    $('#result').html('');
	    $.get('/ihanc/eva/index.php/Home/Sale/itPrice',{'idgoods':parseInt(strs[0]),'inorder':inorder},function(data){
	    	 var obj=$.parseJSON(data);
	    	 $("#saleinfo input[name='price']").val(obj.price1);
	    	 $("#saleinfo input[name='price1']").val(obj.price1);
	    	 $("#saleinfo input[name='price2']").val(obj.price2);
	    });
	    $("#saleinfo input[name='price']").focus();
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
$('#credit_info').click(function(){
	if($('#checkAjax').val()==1){
		alert("加载中，请稍后！");
		return;
	}
	$('#checkAjax').val(1);
	var divhtml="<div id='mcredit_detail' ></div>";  //
	$('#number1').append(divhtml);              //20150104
	var title=$('#saleinfo input[name=mname]').val()+$('#credit_info').text();
	$.get("/ihanc/eva/index.php/Home/Sale/mcreditDetail",
		{'idmember':$('#saleinfo input[name=idmember]').val()},
		function(result){
			$('#checkAjax').val(0);
			$('#mcredit_detail').html(result);
			$('#mcredit_detail').dialog({
				 width:600,
				// dialogClass: "no-close",
				 draggable: false,
				 resizable: false,
				 maxHeight:600,
				 title:title,
				 modal: true,
			     close: function () { $(this).remove(); },
			     buttons: {
			         "收款":collect,
			         "打印":myprint,
			    	 "取消": function() { $(this).dialog( "close" ); }
			      }
			});
	});
});
function collect(){
	var sum=$('#mcreditDetail_sum').val();
	var mname=$("#saleinfo input[name=mname]").val();
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
		'idmember':$("#saleinfo input[name=idmember]").val()
	},function(result){
		var data=$.parseJSON(result);
		if(data.status==1){
			$('#mcredit_detail').remove();
			 $.get("/ihanc/eva/index.php/Home/Sale/intimeTable",{},function(data){
				 $('#divintimeTable').html(data);
			 });
			$.get("/ihanc/eva/index.php/Home/Sale/getCredit",{'idmember':$('#idmember').val()},function(data){
				if(data!=0){
					$("#credit_info").html("<font color='red'>收欠款:￥"+data+"</font>");
				}else{
					$("#credit_info").html('');
				}
			});
			alert("收款成功！");
		}
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
function fillgoods(id,value,index){
	$("#saleinfo input[name='idgoods']").val(id);
	$("#saleinfo input[name='inorder']").val(value);
	if(opt==2){$('#update_sale input[name=gname]').val($("#result li:eq(" + index + ") a").text()); return; }
	 var stock= $("#result li:eq(" + index + ") a").attr('title');
	    $('#saleinfo input[name=gname').val($("#result li:eq(" + index + ") a").text()); 
	    $("#stock_info").html("库存:"+stock);
	$('#result').hide();
	 $.get('/ihanc/eva/index.php/Home/Sale/itPrice',{'idgoods':parseInt(strs[0]),'inorder':inorder},function(data){
    	 var obj=$.parseJSON(data);
    	 $("#saleinfo input[name='price1']").val(obj.price1);
    	 $("#saleinfo input[name='price2']").val(obj.price2);
    	 
    });
}
function getprice(obj){
	var idgoods=$("#saleinfo input[name='idgoods']").val();
	if(idgoods==''){$("#saleinfo input[name='gname']").focus();return;}
	var price=$("#saleinfo input[name='price']").val();
	if(isNaN(price)){ $("#saleinfo input[name='price']").val('');return};
	var tx=$(obj);
	var key = event.keyCode;  
	if(key==17){  //ctrl键进行价格切换
		if(tx.val()==$("#saleinfo input[name='price1']").val())
		{
			tx.val($("#saleinfo input[name='price2']").val());
		}else{
		tx.val($("#saleinfo input[name='price1']").val());
		}
	}
	if(key==32){
		 $("#weight input[name='sum']").val('');
		 $("#weight input[name='weight']").val('');
	     $("#weight input[name='gweight']").val($('#dataIn').val());
		 $("#weight").dialog({
		      minheight: 300,
		      minwidth: 300,
		      draggable: false,
			  resizable: false,
		      modal: true,
		      buttons: {
		      },
		      open: function (event, ui) {
		    	     $(".ui-dialog-titlebar").hide(); 
		    	     $("#weight input[name='weight']").focus();
		           }
		    });
	}
	if(key==38){
		 $("#weight input[name='sum']").val('');
		 $("#weight input[name='weight']").val('');
	     $("#weight input[name='gweight']").val(0);
		 $("#weight").dialog({
		      minheight: 300,
		      minwidth: 300,
		      draggable: false,
			  resizable: false,
		      modal: true,
		      buttons: {
		      },
		      open: function (event, ui) {
		    	     $(".ui-dialog-titlebar").hide(); 
		    	     $("#weight input[name='weight']").focus();
		           }
		    });
	}
	if(key==13){
		if($('#checkAjax').val()==1) return;
		$('#checkAjax').val(1);
		var idmember=$("#saleinfo input[name='idmember']").val();
		var idgoods=$("#saleinfo input[name='idgoods']").val();
		var inorder=$("#saleinfo input[name='inorder']").val();
		var number1=parseFloat($('#dataIn').val());
		if(idmember=='') {alert("请重新输入客户");return;}
		if(idgoods=='') {alert("请重新输入产品");return;}
		$.post("/ihanc/eva/index.php/Home/Sale/inSale",{
			'idmember': idmember,
			'idgoods': idgoods,
			'price':parseFloat(price),
			'inorder':inorder,
			'number1':number1
		},function(result){
			$('#checkAjax').val(0);
			var data=$.parseJSON(result);
			if(data.status==1){
				 alert("保存成功！");
				 $("#saleinfo input").val('');
				 $("#saleinfo input[name='mname']").focus();
				 $('#opt').val(0);
				 $('#stock_info').html('');
				 $('#credit_info').html('');
				 $.get("/ihanc/eva/index.php/Home/Sale/number1",{},function(data){
					 $('#divnumber1').html(data);
				 });
			}else{
				alert(data.info);
			}
		});
	}
}
function gweight(){
	var number2=$('#weight input[name="weight"]').val();
	if(isNaN(number2)){$('#weight input[name="weight"]').val('');return;}
	var price=$("#saleinfo input[name='price']").val();
	if($('#weight input[name="gweight"]').val()==''){
		number1=0;
		$('#weight input[name="gweight"]').val(0);
	}else{
	  var number1=parseFloat($('#weight input[name="gweight"]').val());
	}
	var sum=Math.round(parseFloat(price)*Math.abs(number2-number1));
	$('#weight input[name="sum"]').val(sum);
	var key = event.keyCode;  
	if(key==13){
		if($('#checkAjax').val()==1) return;
		$('#checkAjax').val(1);
	var idmember=$("#saleinfo input[name='idmember']").val();
	var idgoods=$("#saleinfo input[name='idgoods']").val();
	var inorder=$("#saleinfo input[name='inorder']").val();
	if(idmember=='') return;
	if(idgoods=='') return;
	$.post("/ihanc/eva/index.php/Home/Sale/inSale",{
		'idmember': idmember,
		'idgoods': idgoods,
		'price':parseFloat(price),
		'inorder':inorder,
		'number1':number1,
		'number2':parseFloat(number2),
		'sum':parseInt($('#weight input[name="sum"]').val())
	},function(result){
		$('#checkAjax').val(0);
		var data=$.parseJSON(result);
		if(data.status==1){
			 alert("保存成功！");
			 $("#weight").dialog("close");
			 $("#saleinfo input").val('');
			 $('#weight input[name="weight"]').val('');
			 $("#saleinfo input[name='mname']").focus();
			 $('#opt').val(0);
			 $.get("/ihanc/eva/index.php/Home/Sale/intimeTable",{},function(data){
				 $('#divintimeTable').html(data);
			 });
		}
	});
	}
	if(key==40){
		var t=$('#weight input[name="sum"]').val();
		$('#weight input[name="sum"]').val('').focus().val(t);
	}
}
function gweightsum(){
	var sum=$('#weight input[name="sum"]').val();
	if(isNaN(sum)){$('#weight input[name="sum"]').val('');return;}
	var number2=$('#weight input[name="weight"]').val();
	var price=$("#saleinfo input[name='price']").val();
	if($('#weight input[name="gweight"]').val()==''){
	  var	number1=0;
	}else{
	  var number1=parseFloat($('#weight input[name="gweight"]').val());
	}
	var key = event.keyCode;  
	if(key==13){
		if($('#checkAjax').val()==1) return;
		$('#checkAjax').val(1);
	var idmember=$("#saleinfo input[name='idmember']").val();
	var idgoods=$("#saleinfo input[name='idgoods']").val();
	var inorder=$("#saleinfo input[name='inorder']").val();
	var number1=parseFloat($('#dataIn').val());
	if(idmember=='') return;
	if(idgoods=='') return;
	$.post("/ihanc/eva/index.php/Home/Sale/inSale",{
		'idmember': idmember,
		'idgoods': idgoods,
		'price':parseFloat(price),
		'inorder':inorder,
		'number1':number1,
		'number2':parseFloat(number2),
		'sum':parseInt($('#weight input[name="sum"]').val())
	},function(result){
		$('#checkAjax').val(0);
		var data=$.parseJSON(result);
		if(data.status==1){
			 alert("保存成功！");
			 $("#weight").dialog("close");
			 $("#saleinfo input").val('');
			 $('#weight input[name="weight"]').val('');
			 $("#saleinfo input[name='mname']").focus();
			 $('#opt').val(0);
			 $.get("/ihanc/eva/index.php/Home/Sale/intimeTable",{},function(data){
				 $('#divintimeTable').html(data);
			 });
		}
	});
	}
	if(key==38){
		var t=$('#weight input[name="weight"]').val();
		$('#weight input[name="weight"]').val('').focus().val(t);
	}
}

function addintime(){
	var sum=$('#number2 input[name="sum"]').val();
	if(isNaN(sum)){$('#number2 input[name="sum"]').val('');return;}
	if(event.keyCode==38){
		var t=$('#number2 input[name="weight"]').val();
		$('#number2 input[name="weight"]').val('').focus().val(t);
		return;
	}
	if(event.keyCode==13){
	var number2=$('#number2 input[name="weight"]').val();
	var idintime=parseInt($('#number2 input[name="idintime"]').val());
	sum=parseInt(sum);
	$.post("/ihanc/eva/index.php/Home/Sale/updataIntime",{'idintime':idintime,'number2':number2,'sum':sum},function(result){
		var data=$.parseJSON(result);
		if(data.status==1){
			alert("保存成功！");
			$('#number2').dialog("close");
			$('#number2 input[name="idintime"]').val('');
			$('#number2 input[name="weight"]').val('');
			$.get("/ihanc/eva/index.php/Home/Sale/number1",{},function(data){
				 $('#divnumber1').html(data);
				 $('#number1').find('tr:eq(0)').addClass("sltr");
			 });
			$.get("/ihanc/eva/index.php/Home/Sale/intimeTable",{},function(data){
				 $('#divintimeTable').html(data);
			 });
		}
	});
	}
	
}
function number2(){
	if(event.keyCode==40){
		var t=$('#number2 input[name="sum"]').val();
		$('#number2 input[name="sum"]').val('').focus().val(t);
		return;
	}
	if(event.keyCode==13){
		var number2=$('#number2 input[name="weight"]').val();
		var idintime=parseInt($('#number2 input[name="idintime"]').val());
		var sum=parseInt($('#number2 input[name="sum"]').val());
		$.post("/ihanc/eva/index.php/Home/Sale/updataIntime",{'idintime':idintime,'number2':number2,'sum':sum},function(result){
			var data=$.parseJSON(result);
			if(data.status==1){
				alert("保存成功！");
				$('#number2').dialog("close");
				$('#number2 input[name="idintime"]').val('');
				$('#number2 input[name="weight"]').val('');
				$.get("/ihanc/eva/index.php/Home/Sale/number1",{},function(data){
					 $('#divnumber1').html(data);
					 $('#number1').find('tr:eq(0)').addClass("sltr");
				 });
				$.get("/ihanc/eva/index.php/Home/Sale/intimeTable",{},function(data){
					 $('#divintimeTable').html(data);
				 });
			}
		});
		return;
	}
	var number2=$('#number2 input[name="weight"]').val();
	if(isNaN(number2)){$('#number2 input[name="weight"]').val('');$('#number2 input[name="sum"]').val('');return;}
	var id=$('#number2 input[name="idintime"]').val();
	var price=parseFloat($('#'+id).find('td:eq(3)').html().substr(1));
	var number=parseFloat($('#'+id).find('td:eq(4)').html())-parseFloat(number2);
	number=Math.abs(number);
	var sum=Math.round(price*number);
	$('#number2 input[name="sum"]').val(sum);
	
}
function fnumber2(){
	//$('#number2_check').val(1);
	var t=$('#number2 input[name="weight"]').val();
	$('#number2 input[name="weight"]').val('').focus().val(t);
}
function fpayment(){
	var t=$('#payment input[name="sum"]').val();
	$('#payment input[name="sum"]').val('').focus().val(t);
}
function scollect(){
	if($('#checkAjax').val()==1){
		alert("加载中，请稍后！");
		return;
	}
	$('#checkAjax').val(1);
	var sum=parseInt($('#payment input[name="sum"]').val());
	if(isNaN(sum)){
		alert('请输入正确的付款金额!');
		$('#payment input[name="sum"]').val('');
		return;
	}
	var idsale=$('#oTable').find('tr.sltr').attr('id');
	idsale=idsale.substr(6);
	var idmember=$('#oTable').find('tr.sltr td:eq(0)').find('input[name="idmember"]').val();
	var index=$('#oTable').find('tr.sltr').index();
	var mname=$('#oTable').find('tr.sltr').find('td:eq(1)').html();
	if(event.keyCode==13){
	var idbank=$('#idbank').val();
	//alert(idmember);
	$.post("/ihanc/eva/index.php/Home/Sale/mcreditPayment",{
		'sum':sum,
		'idsale':idsale,
		'idbank':idbank,
		'cf':0,
		'mname':mname,
		'idmember':idmember
	},function(result){
		$('#checkAjax').val(0);
		var data=$.parseJSON(result);
		if(data.status==1){
			 $.get("/ihanc/eva/index.php/Home/Sale/intimeTable",{},function(data){
				 $('#divintimeTable').html(data);
				 $('#oTable tr:eq('+index+')').addClass('sltr');
			 });
			$('#payment').dialog("close");
			alert("收款成功！");
		}
	});
	}
}

function delSale(idsale){
	var pwd=$('#update_sale input[name=delpwd]').val();
	$.get("/ihanc/eva/index.php/Home/Sale/saleDel",{'idsale':idsale,'pwd':pwd},function(result){
		var data=$.parseJSON(result);
		if(data.status==1){
			 $.get("/ihanc/eva/index.php/Home/Sale/intimeTable",{},function(data){
				 $('#divintimeTable').html(data);
			 });
			 $('#update_sale').dialog("close");
			alert("删除成功！");
			return;
		}else{
			alert(data.info);
		}
		
	});
}
function updateSale(idsale){
	var sum=$('#update_sale input[name=sum]').val();
	if(isNaN(sum)){return;}
	var idmember=$("#saleinfo input[name='idmember']").val();
	var idgoods=$("#saleinfo input[name='idgoods']").val();
	var inorder=$("#saleinfo input[name='inorder']").val();
	$.post("/ihanc/eva/index.php/Home/Sale/updateSale",{
		'idsale':idsale,
		'idgoods':idgoods,
		'inorder':inorder,
		'idmember':idmember,
		'price':parseFloat($('#update_sale input[name=price]').val()),
		'sum':parseInt(sum)
	},function(result){
		var data=$.parseJSON(result);
		if(data.status==1){
			 alert("修改成功！");
			 $.get("/ihanc/eva/index.php/Home/Sale/intimeTable",{},function(data){
				 $('#divintimeTable').html(data);
			 });
			 $('#update_sale').dialog("close");
			return;
		}else{
			alert(data.info);
		}
	});	
}
function fnumber1(){
	var t=$('#updateNumber1 input[name="number1"]').val();
	$('#updateNumber1 input[name="number1"]').val('').focus().val(t);
}
function updateNum1(obj){
	var tx=$(obj);
	var name=tx.attr('name');
	if((name=='number1')&&(event.keyCode==40)){
		var t=$('#updateNumber1 input[name="price"]').val();
		$('#updateNumber1 input[name="price"]').val('').focus().val(t);
	}
	if((name=='price')&&(event.keyCode==38)){
		var t=$('#updateNumber1 input[name="number1"]').val();
		$('#updateNumber1 input[name="number1"]').val('').focus().val(t);
	}
	if(event.keyCode==13){
		if($('#checkAjax').val()==1){
			alert("加载中，请稍后！");
			return;
		}
		$('#checkAjax').val(1);
		$.post("/ihanc/eva/index.php/Home/Sale/updateNum1",{
			'number1':$('#updateNumber1 input[name="number1"]').val(),
			'price':$('#updateNumber1 input[name="price"]').val(),
			'idintime':$('#updateNumber1 input[name="idintime"]').val()
		},function(result){
			var data=$.parseJSON(result);
			$('#checkAjax').val(0);
			if(data.status==1){
				 $.get("/ihanc/eva/index.php/Home/Sale/number1",{},function(data){
					 $('#divnumber1').html(data);
				 });
				 alert("修改成功！");
				 $('#updateNumber1').dialog("close");
				return;
			}else{
				alert(data.info);
			}
	});
  }
}
</script>
<div id="weight"  style="display:none ;font-size:200%;">
<label style="padding: 5px 0px 5px 0px; ">毛重:</label>
<input type="text" name="gweight"  style='height:20% ;width:100%;font-size:150%;' readonly>
<label style="padding: 5px 0px 5px 0px; ">皮重:</label>
<input type="text" name="weight"  style='height:20% ;width:100%;font-size:150%;' onkeyup='gweight();'>
<label style="padding: 5px 0px 5px 0px; ">金额:</label>
<input type="text" name="sum"  style='height:20% ;width:100%;font-size:150%;' onkeyup='gweightsum();'>
<br/>
</div>
<div id="number2"  style="display:none ;">
<br/>
<input type="text" value='获取重量2:' name='title' style='font-size:200%;margin-left:5%;border:none;background-color:white;color:black;width:95%;' class="itinput" onkeyup="fnumber2()"/>
<input type="text" name="weight"  style='height:20% ;width:90%;font-size:300%;margin-left:5%' onkeyup='number2();'>
<p style='font-size:200%;padding: 5px 0px 5px 0px;'>应收金额：</p>
<input type="text" name="sum"  style='height:20% ;width:90%;font-size:300%;margin-left:5%' onkeyup='addintime();'>
<input type="hidden" name='idintime'/>
<br/>
</div>
<div id="payment"  style="display:none;">
<input type="text" value='' name='title' style='font-size:200%;margin-left:5%;background-color:white;border:none;color:black;width:95%;' class="itinput" onkeyup="fpayment()" readonly/>
<input type="text" name="sum"  style='height:50% ;width:90%;font-size:300%;margin-left:5%' onkeyup='scollect();'>
<br/>
</div>
<div id="add_member" title="添加客户" style="display:none">
 <form action="/ihanc/eva/index.php/Home/Sale/memberUpdate" method="post">
    <fieldset>
      <label for="name" style="font-size:13px">客户名称:</label>
      <input type="text" name="mname" style="margin-bottom:10px; width:95%; padding: .4em;" onblur="make_py()" >
      <label for="msn" style="font-size:13px">客户编码:</label>
      <input type="text" name="msn" style="margin-bottom:10px; width:95%; padding: .4em;">
    </fieldset>
  </form>
</div>

<div id="update_sale" title="修改销售单" style="display:none">
 <form>
    <fieldset>
      <label for="name" style="font-size:13px">客户:</label>
      <input type="text" name="mname" style="margin-bottom:10px; width:95%; padding: .4em;" onkeyup='getmember(this)' onkeydown='selectmember(this)'>
      <label for="gname" style="font-size:13px">品名:</label>
      <input type="text" name="gname" style="margin-bottom:10px; width:95%; padding: .4em;" onkeyup='getgoods(this)' onkeydown='selectgoods(this)'>
       <label for="price" style="font-size:13px">价格:</label>
      <input type="text" name="price" style="margin-bottom:10px; width:95%; padding: .4em;">
       <label for="sum" style="font-size:13px">金额:</label>
      <input type="text" name="sum" style="margin-bottom:10px; width:95%; padding: .4em;">
       <label for="sum" style="font-size:13px">请输入密码:</label>
      <input type="password" name="delpwd" style="margin-bottom:10px; width:95%; padding: .4em;">
    </fieldset>
  </form>
</div>
<div id="updateNumber1"  style="display:none ;">
<br/>
<input type="text" value='获取重量2:' name='title' style='font-size:150%;margin-left:0%;border:none;background-color:white;color:black;width:95%;' class="itinput" onkeyup="fnumber1()"/>
<input type="text" name="number1"  style='height:20% ;width:90%;font-size:300%;margin-left:5%' onkeyup='updateNum1(this);'>
<p style='font-size:200%;padding: 5px 0px 5px 0px;'>价格：</p>
<input type="text" name="price"  style='height:20% ;width:90%;font-size:300%;margin-left:5%' onkeyup='updateNum1(this);'>
<input type="hidden" name='idintime'/>
<br/>
</div>
<input type="hidden" value=0 id="checkAjax"/>
</body>
</html>