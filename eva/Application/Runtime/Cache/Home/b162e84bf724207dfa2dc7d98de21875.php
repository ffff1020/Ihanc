<?php if (!defined('THINK_PATH')) exit();?><style>
#purchaseDetailtb th {
    background-color: 	#E6E6FA;
</style>
<script>
function purchase_update(idpurchase){
	//alert();	
	$('#purchase_update input[name="idpurchase"]').val(idpurchase);
    var sname=$('#p'+idpurchase+' td:nth-child(2)').text();
    $('#purchase_update input[name="sname"]').val(sname);
    var gname=$('#p'+idpurchase+' td:nth-child(3)').text();
    $('#purchase_update input[name="gname"]').val(gname);
    var price=$('#p'+idpurchase+' td:nth-child(4)').text();
    $('#purchase_update input[name="price"]').val(price);
    var number=$('#p'+idpurchase+' td:nth-child(5)').text();
    $('#purchase_update input[name="number"]').val(number);
    var sum=$('#p'+idpurchase+' td:nth-child(6)').text();
    $('#purchase_update input[name="sum"]').val(sum);
    var idsupply=$('#p'+idpurchase+' input[name="idsupply"]').val();
    $('#purchase_update input[name="idsupply"]').val(idsupply);
    var idgoods=$('#p'+idpurchase+' input[name="idgoods"]').val();
    $('#purchase_update input[name="idgoods"]').val(idsupply);
    var id=$('#p'+idpurchase+' input[name="inorder"]').val();
    //alert(id);
	$("#purchase_update").dialog({
	      height: 400,
	      width: 300,
	      modal: true,
	      close: function () { $(this).remove(); },
	      buttons: {
	        "修改": function(){p_update(id)},
	        "取消": function() { $(this).dialog( "close" ); }
	      },
	    });
}
function p_update(id){	
	$.post("/ihanc/eva/index.php/Home/Purchase/purchaseUpdate",
		{
		'idpurchase':$('#purchase_update input[name="idpurchase"]').val(),
		'price':$('#purchase_update input[name="price"]').val(),
		'number':$('#purchase_update input[name="number"]').val(),
		'sum':$('#purchase_update input[name="sum"]').val()
		},
			function(data){
			// alert(data);return;
		    data=$.parseJSON(data);
		   // alert(data.info);return;
		    if(data.status==1){
			    $("#purchase_update").dialog("close");	
			    $.get('/ihanc/eva/index.php/Home/Purchase/purchaseDetail',{'inorder':id}, function(result){
				    $("#"+id+' div').html(result);
				  });
			    $.post('/ihanc/eva/index.php/Home/Purchase/purchaseTableUpdate',{'inorder':id},
						function(result){
					var obj=$.parseJSON(result);
					$('#tr'+id).children("td:nth(2)").html(obj.ttl_num);
					$('#tr'+id).children("td:nth(3)").html(obj.ttl_sum);
				});
		    }
		    mynotice(data);
	});
}

function mycal(){
	var number=$('#purchase_update input[name="number"]').val();
	var price=$('#purchase_update input[name="price"]').val();
	if(isNaN(number)){$('#purchase_update input[name="number"]').val('');alert("请输入正确的数量!"); return;}
	if(isNaN(price)){$('#purchase_update input[name="price"]').val('');alert("请输入正确的价格!"); return;}
    var sum=Math.round(number*price);
    $('#purchase_update input[name="sum"]').val(sum);
}

function freight_update(idfreight){
 		var info=$('#'+idfreight+' td:nth-child(1)').text();
 		$('#freight_update p').html(info);
 		var freight=$('#'+idfreight+' td:nth-child(2)').text();
 		$('#freight_update input[name="freight"]').val(freight);
 		$('#freight_update input[name="idfreight"]').val(idfreight);
 		var remark=$('#'+idfreight+' td:nth-child(1)').find('input[name="remark"]').val();
 		$('#freight_update input[name="remark"]').val(remark);
 		var id=$('#'+idfreight+' input[name="finorder"]').val();
 		
	    $("#freight_update").dialog({
	      height:350,
	      width: 300,
	      modal: true,
	      close: function () { $(this).remove(); },
	      buttons: {
	        "修改": function(){f_update(id)},
	        "取消": function() { $(this).dialog( "close" ); }
	      },
	    });
}
function f_update(id){
	var freight=$('#freight_dialog_form input[name="freight"]').val();
	if(isNaN(freight)){$('#freight_dialog_form input[name="freight"]').val(''); alert("请输入正确的运费!");return;}
	$.post("/ihanc/eva/index.php/Home/Purchase/freightUpdate",
			{'idfreight':$('#freight_dialog_form input[name="idfreight"]').val(),
			 'remark':	$('#freight_dialog_form input[name="remark"]').val(),
			 'freight': freight,
			},
			function(data){
				 data=$.parseJSON(data);
				 if(data.status==1){
					 $("#freight_update").dialog("close");
					 $.get('/ihanc/eva/index.php/Home/Purchase/purchaseDetail',{'inorder':id}, function(result){
						    $("#"+id+' div').html(result);
						  });
					 $.post('/ihanc/eva/index.php/Home/Purchase/purchaseTableFreightUpdate',{'inorder':id},function(result){
						 $('#tr'+id).children("td:nth(4)").html(result);
					 });
				    }
				    mynotice(data);
		});
}

function purchase_del(idpurchase){
	
   // alert();return;
	var id=$('#p'+idpurchase).find("input[name='inorder']").val();
	var count=$('#tr'+id).next().find('tr[id*="p"]').length;
	var tblength=$('#'+id).find('tr').length;
	if(count==1&&tblength>2){
		alert("请先删除进货费用!");
		return;
	}
	$.post("/ihanc/eva/index.php/Home/Purchase/purchaseDel",{'idpurchase':idpurchase},function(result){
		
	   // alert(result);return;
		data=$.parseJSON(result);
		if(data.status==1){
			
			$.post('/ihanc/eva/index.php/Home/Purchase/purchaseTableUpdate',{'inorder':id},
					function(result){
				var obj=$.parseJSON(result);
				$('#tr'+id).children("td:nth(2)").html(obj.ttl_num);
				$('#tr'+id).children("td:nth(3)").html(obj.ttl_sum);
			});
			$('#p'+idpurchase).remove();
		}
		mynotice(data);
	});
	
}

function freight_del(idfreight){
	//var inorder=$('#'+idfreight).prev().find("input[name='inorder']").val();
    //alert(inorder);return;
	$.post("/ihanc/eva/index.php/Home/Purchase/freightDel",{'idfreight':idfreight},function(result){
		data=$.parseJSON(result);
		if(data.status==1){
			var id=$('#'+idfreight).prev().find("input[name='inorder']").val();
			$.post('/ihanc/eva/index.php/Home/Purchase/purchaseTableFreightUpdate',{'inorder':id},function(result){
				 $('#tr'+id).children("td:nth(4)").html(result);
			 });
			$('#'+idfreight).remove();
		}
		mynotice(data);
	});
}
</script>
<table class="bordered" style="width:100%;font-size:12px" id="purchaseDetailtb"> 
      <tbody>
    <thead> 
    <tr>
        <th>#</th> 
    	<th>供应商</th> 
    	<th>商品名称</th>
    	<th>价格</th>
    	<th>数量</th> 
    	<th>金额</th> 
    	<th>操作</th>
    </tr>
    </thead>
    <?php if(is_array($pur_detail)): $i = 0; $__LIST__ = $pur_detail;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr id=p<?php echo ($vo["idpurchase"]); ?>>
    <td><?php echo ($i); ?><input type="hidden" name="inorder" value=<?php echo ($vo["inorder"]); ?>></td>
    <td><?php echo ($vo["sname"]); ?><input type="hidden" name='idsupply' value=<?php echo ($vo["idsupply"]); ?>></td>
    <td><?php echo ($vo["gname"]); ?><input type="hidden" name='idgoods' value=<?php echo ($vo["idgoods"]); ?>></td>
    <td><?php echo ($vo["price"]); ?></td>
    <td><?php echo ($vo["number"]); ?></td>
    <td><?php echo ($vo["sum"]); ?></td>
    <td><?php if($vo["finish"] == 0 ): ?><a onclick='purchase_update(<?php echo ($vo["idpurchase"]); ?>)'>修改</a>
    |<A onclick='purchase_del(<?php echo ($vo["idpurchase"]); ?>)'>删除</A></td>
    <?php else: ?>已付款,不能修改<?php endif; ?>
    </tr><?php endforeach; endif; else: echo "" ;endif; ?>
    <?php if(is_array($freight_list)): $i = 0; $__LIST__ = $freight_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr id=<?php echo ($vo["idfreight"]); ?>>
    <td colspan="5"><?php echo ($vo["staff_name"]); ?>的<?php echo ($vo["subject"]); ?>,备注:<?php echo ($vo["remark"]); ?><input type="hidden" name="remark" value="<?php echo ($vo["remark"]); ?>"></td>
    <td><?php echo ($vo["freight"]); ?><input type="hidden" name="finorder" value="<?php echo ($vo["inorder"]); ?>"></td>
    <td><?php if($vo["status"] == 0 ): ?><a onclick='freight_update(<?php echo ($vo["idfreight"]); ?>)'>修改</a>
    |<A onclick='freight_del(<?php echo ($vo["idfreight"]); ?>)'>删除</A></td>
    <?php else: ?>已付款,不能修改<?php endif; ?>
    </tr><?php endforeach; endif; else: echo "" ;endif; ?>
    </tbody>
  
</table>
<!-- 修改purchase记录 -->
<div id="purchase_update" title="修改进货明细" style="display:none">
 <form action="/ihanc/eva/index.php/Home/Purchase/purchaseUpdate" method="post" id="purchase_dialog_form">
    <fieldset>
      <input type="hidden" name="idpurchase">
      <label for="name" style="font-size:13px">供应商名称:</label>
      <input type="text" name="sname" style="margin-bottom:10px; width:95%; padding: .4em;" disabled>
      <input type="hidden" name="idsupply">
      <label for="ssn" style="font-size:13px">商品名称:</label>
      <input type="text" name="gname" style="margin-bottom:10px; width:95%; padding: .4em;" disabled>
      <input type="hidden" name="idgoods"/>
      <label for="name" style="font-size:13px;display:inline">价格:</label><p style="font-size:13px;display:inline;padding-left:120px">数量:</p>
      <input type="text" name="price"  style="margin-bottom:10px; width:40%; padding: .4em;display:inline" onkeyup='mycal()'/>
      &nbsp;<em>*</em>&nbsp;
      <input type="text" name="number" style="margin-bottom:10px; width:40%; padding: .4em;display:inline" onkeyup='mycal()'/>
      <label for="name" style="font-size:13px">金额:</label>
      <input type="text" name="sum" style="margin-bottom:10px; width:95%; padding: .4em;" >
    </fieldset>
  </form>
</div>  
<!-- 修改freihgt信息 -->
<div id="freight_update" title="修改运费信息" style="display:none">
 <form action="/ihanc/eva/index.php/Home/Purchase/freightUpdate" method="post" id="freight_dialog_form">
    <fieldset>
      <input type="hidden" name="idfreight">
      <label for="name" style="font-size:13px">费用信息:</label>
      <p></p>
      <label for="name" style="font-size:13px">金额:</label>
      <input type="text" name="freight" style="margin-bottom:10px; width:95%; padding: .4em;" >
      <label for="name" style="font-size:13px">修改备注:</label>
      <input type="text" name="remark" style="margin-bottom:10px; width:95%; padding: .4em;" >
    </fieldset>
  </form>
</div>