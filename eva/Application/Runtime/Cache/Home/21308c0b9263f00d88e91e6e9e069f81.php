<?php if (!defined('THINK_PATH')) exit();?>
<script>
$('#page a').click(function(){
	var href=$(this).attr('href');
	$.get(href, function(result){
	    $("#sale_table").html(result);
	  });
	return false;
});

function saleDel(id){
	var str=$('#'+id+" td:nth(2)").text()+"-"+$('#'+id+" td:nth(3)").text();
	$( "#sale_del b" ).html(str);
	$("#sale_del input[name=number]").val($('#'+id+" td:nth(4)").text());
	$("#sale_del input[name=price]").val($('#'+id+" td:nth(5)").text().substring(1));
	$("#sale_del input[name=sum]").val($('#'+id+" td:nth(6)").text().substring(1));
	
	$( "#sale_del" ).dialog({
		  // autoOpen:false,
	       modal: true,
	       close: function () { $(this).find('input[name=delpwd]').val('');},
	       buttons: {
	    	修改:function(){
	    		var pwd=$(this).find('input[name=delpwd]').val();
	    		//alert(pwd);return;
	    		if(pwd==''){alert("请输入密码");return;}
	    		$.get("/ihanc/eva/index.php/Home/Sale/saleModify",{
	    			'idsale':id,
	    			'pwd':pwd,
	    			'number':$("#sale_del input[name=number]").val(),
	    			'price':$("#sale_del input[name=price]").val(),
	    			'sum':$("#sale_del input[name=sum]").val()
	    			},
	    		function(result){
	    			//alert(result);return;
	    			var data=$.parseJSON(result);
	    			if(data.status==1){
	    				$("#sale_del").dialog("close");
	    				$('#'+id+" td:nth(4)").text($("#sale_del input[name=number]").val());
	    				$('#'+id+" td:nth(5)").text("￥"+$("#sale_del input[name=price]").val());
	    				$('#'+id+" td:nth(6)").text("￥"+$("#sale_del input[name=sum]").val());
	    				}
	    			mynotice(data);
	    		});
	    	},
	    	删除:function(){
	    		var pwd=$(this).find('input[name=delpwd]').val();
	    		//alert(pwd);return;
	    		if(pwd==''){alert("请输入密码");return;}
	    		$.get("/ihanc/eva/index.php/Home/Sale/saleDel",{'idsale':id,'pwd':pwd},
	    		function(result){
	    			var data=$.parseJSON(result);
	    			if(data.status==1){
	    				$("#sale_del").dialog("close");
	    				$('#'+id).remove();
	    				}
	    			mynotice(data);
	    		});
	    	},
	              取消: function() {
	           $( this ).dialog( "close" );
	         }
	       }
	     });
}
function intime(idintime){
	if(idintime==null) return;
	var divhtml="<div id='intime'></div>";
	$('#main-content').append(divhtml);
	var mname=$('#time'+idintime).text()+":"+$('#time'+idintime).next().text();
	$.get("/ihanc/eva/index.php/Home/Sale/getIntimeInfo",{'idintime':idintime},function(result){
		var data=$.parseJSON(result);
		var table='<p></p><table class="bordered" style="width:100%;font-size:14px" >';
		table+='<tr><td>重量1：</td><td>'+data.number1+'</td><td>'+data.time1+'</td></tr>';
		table+='<tr><td>重量2：</td><td>'+data.number2+'</td><td>'+data.time2+'</td></tr>';
		table+='<tr><td>操作人：</td><td>'+data.user+'</td><td></td></tr>';
		table+='</table>';
		$('#intime').append(table);
		$('#intime').dialog({
			   title:mname,
			   width:400,
			   height:250,
		       modal: true,
		       close: function () { $(this).remove();},
		       buttons:{
		    	   取消: function() {
		           $( this ).dialog( "close" );
		         }
		       }
		});
	});
}
function myprint(idsale){
	var mname=$('#'+idsale+" td:nth(2)").text();
	window.open("/ihanc/eva/index.php/Home/Sale/saleTablePrint/idsale/"+idsale+"/mname/"+mname);
}
</script>
<br/>
<!-- 删除提示 -->
<div id="sale_del" title="修改删除" style="display:none">
 <fieldset>
 <h3><b></b></h3>
 <div style="color:#003472 ;font-size:13px;line-height:200%">数量：</div><input type="text" name='number'>
 <div style="color:#003472 ;font-size:13px;line-height:200%">单价：</div><input type="text" name='price'>
 <div style="color:#003472 ;font-size:13px;line-height:200%">金额：</div><input type="text" name='sum'>
 <div style="color:#003472 ;font-size:13px;line-height:200%">请输入登录密码:</div>
 <input type="password" name='delpwd'/>
 </fieldset>
</div>
	    
<div style=" text-align:center;"><font size="4px"><b><?php echo ($info); ?></b></font></div>
<br/>
<table class="bordered" style="width:100%;font-size:12px" > 
    <thead> 
    <tr>
        <th>#</th> 
    	<th>日期</th> 
    	<th>客户姓名</th> 
    	<th>商品名称</th> 
    	<th>数量</th>
    	<th>价格</th>
    	<th>金额</th>
    	<th>操作</th>
    </tr>
    </thead>
    <tbody>
    <?php if(is_array($sale_list)): $i = 0; $__LIST__ = $sale_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr id=<?php echo ($vo["idsale"]); ?>>
        <td><?php echo ($i); ?></td> 
    	<td><?php echo ($vo["time"]); ?></td> 
    	<td id=time<?php echo ($vo["idintime"]); ?>><?php echo ($vo["mname"]); ?></td> 
    	<td><?php echo ($vo["gname"]); ?></td> 
    	<td><?php echo ($vo["number"]); ?></td>
    	<td>￥<?php echo (number_format($vo["price"],2)); ?></td>
    	<td>￥<?php echo (number_format($vo["sum"])); ?></td>
    	<td><?php if($vo["finish"] == 1): ?>已经收款,不能修改<?php else: ?><a onclick='saleDel(<?php echo ($vo["idsale"]); ?>)'>修改删除<?php endif; ?></a>
    	|<a onclick="myprint(<?php echo ($vo["idsale"]); ?>)">打印</a>|<a onclick="intime(<?php echo ($vo["idintime"]); ?>)">详细信息</a>
    	</td>
    </tr><?php endforeach; endif; else: echo "" ;endif; ?>
    <tr><td colspan='8'>合计数量：<?php echo (number_format($ttl_num,2)); ?></td> </tr>
    </tbody>
    </table>
    <div class="green-black" style="float:right" id="page"><?php echo ($page); ?></div>