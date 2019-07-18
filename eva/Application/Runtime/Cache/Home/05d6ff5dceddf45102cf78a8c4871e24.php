<?php if (!defined('THINK_PATH')) exit();?>
<h1 class="page-title">库存管理</h1>
<div class="container_12 clearfix leading">
<div class="grid_12">
<table class="bordered" style="width:100%;font-size:12px" id='stock_tb'> 
<thead>
<tr>
<th>#</th>
<th>进货单号</th>
<th>商品名称</th>
<th>数量</th>
<th>预置售价1</th>
<th>预置售价2</th>
<th>操作</th>
</tr>
</thead>
<tbody>
<?php if(is_array($stock_list)): $i = 0; $__LIST__ = $stock_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr id=<?php echo ($vo["idstock"]); ?> title="双击查看销售明细">
<td><?php echo ($i); ?></td>
<td><?php echo ($vo["inorder"]); ?></td>
<td><?php echo ($vo["gname"]); ?><input type="hidden" name='idgoods' value=<?php echo ($vo["idgoods"]); ?>></td>
<td><?php echo (round($vo["stock"],2)); ?></td>
<td><?php echo (round($vo["price1"],2)); ?></td>
<td><?php echo (round($vo["price2"],2)); ?></td>
<td><a onclick="update('<?php echo ($vo["idstock"]); ?>')">修改</a>|<a onclick="del('<?php echo ($vo["idstock"]); ?>')">删除</a></td>
</tr><?php endforeach; endif; else: echo "" ;endif; ?>
</tbody>
</table>
</div>
<p>&nbsp;</p>
 <div class="message info" ><h6>温馨提示:<br>&nbsp;&nbsp;&nbsp;&nbsp;不设置预售价格，系统直接带入该客户以前成交价。<br>
 &nbsp;&nbsp;&nbsp;&nbsp;设置预售价格，系统直接优先带入预售价格。
 </h6></div>
</div>

<script>
$('#stock_tb tbody tr').dblclick(function(){
	var strHtml="<div id='dingwei'><img src='/ihanc/eva/Application/Home/View/public/images/ajax-loader-trans.gif'>&nbsp;&nbsp;加载中...</div>";
	$('body').append(strHtml);
	var divhtml="<div id='sale_table'></div>";  
	$('#main-content').append(divhtml);              
	var title=$(this).find("td:nth-child(3)").text()+"销售明细单";
	var idgoods=$(this).find("input[name='idgoods']").val();
	var inorder=$(this).find("td:nth-child(2)").text();
	$.get("/ihanc/eva/index.php/home/sale/saleTable",
		{'idgoods':idgoods,'inorder':inorder},
		function(result){
			$('#dingwei').remove();
			$('#sale_table').html(result);
			$('#sale_table').dialog({
				 width:800,
				 maxHeight:700,
				 title:title,
				 modal: true,
			     close: function () { $(this).remove(); },// html('');
			     buttons: {
			    	 "取消": function() { $(this).remove();},
			      },
			});
	});
});
function del(id){
	var str=$('#'+id+" td:nth-child(2)").html()+"--"+$('#'+id+" td:nth-child(3)").html();
	str=str+"<p>数量为:<em><font color='red'>"+$('#'+id+" td:nth-child(4)").html()+"</font></em></p>";
	$('#stockDel p').html(str);
	$( "#stockDel" ).dialog({
	      height: 200,
	      width: 300,
	      modal: true,
	      buttons: {
	        "删除": function(){
	        	$.post("/ihanc/eva/index.php/Home/Stock/stockDel",{'idstock':id},
	        			function(data){
	        		    var obj=$.parseJSON(data);
      			        mynotice(obj);
      			       if(obj.status==1){
      			    	$( "#stockDel" ).dialog('close');
      			    	$('#'+id).remove();
      			    }
	        	});
	        },
	        "取消": function() {
	        $(this).dialog( "close" );
	        }
	      },
	    });
}
function update(id){
	var strHtml='<div id="stockUpdate" title="修改库存数量和预置售价"><fieldset><p></p>'+
				' <lable style="font-size:12px;display:inline">&nbsp;输入修改数量:</lable>'+
      '<input type="text" name="stock" value=0 class="text ui-widget-content ui-corner-all" style="width:50%;display:inline">'+
      '<BR/><lable style="font-size:12px;display:inline">修改预置售价1:</lable>'+
      '<input type="text" name="price1" value=0 class="text ui-widget-content ui-corner-all" style="width:50%;display:inline">'+
      '<BR/><lable style="font-size:12px;display:inline">修改预置售价2:</lable>'+
      '<input type="text" name="price2" value=0 class="text ui-widget-content ui-corner-all" style="width:50%;display:inline">'+	
				'</fieldset></div>';
	$('body').append(strHtml);
	var str=$('#'+id+" td:nth-child(2)").html()+"--"+$('#'+id+" td:nth-child(3)").html();
	$('#stockUpdate p').html(str);
	var number=$('#'+id+" td:nth-child(4)").html();
	var price1=parseFloat($('#'+id+" td:nth-child(5)").html());
	var price2=parseFloat($('#'+id+" td:nth-child(6)").html());
	//alert(price1);
	$('#stockUpdate input[name="stock"]').val(number);
	$('#stockUpdate input[name="price1"]').val(price1);
	$('#stockUpdate input[name="price2"]').val(price2);
	$( "#stockUpdate" ).dialog({
	      height: 320,
	      width: 300,
	      modal: true,
	      close:function () { $(this).remove();},
	      buttons: {
	        "修改": function(){
	        	var stock=parseFloat($('#stockUpdate input[name="stock"]').val());
	        	//alert(stock);
	        	if(isNaN(stock)){
	        		alert("请输入正确的数量!");
	        		$('#stockUpdate input[name="stock"]').val('');
	        		return;
	        	}
	        	var sprice1=parseFloat($('#stockUpdate input[name="price1"]').val());
	        	var sprice2=parseFloat($('#stockUpdate input[name="price2"]').val());
	        	$.post("/ihanc/eva/index.php/Home/Stock/stockUpdate",{'stock':stock,'idstock':id,'price1':sprice1,'price2':sprice2},
	        			function(data){
	        			//alert(data);return;
	        		    var obj=$.parseJSON(data);
        			    mynotice(obj);
        			    if(obj.status==1){
        			    	$( "#stockUpdate" ).dialog('close');
        			    	$('#'+id+" td:nth-child(4)").html(stock);
        			    	$('#'+id+" td:nth-child(5)").html(sprice1);
        			    	$('#'+id+" td:nth-child(6)").html(sprice2);
        			    }
	        	});
	        },
	        "取消": function() {
	        $(this).dialog( "close" );
	        }
	      },
	    });
}
</script>
<div id="stockDel" title="删除库存" style="display:none">
 <fieldset>
    <p></p>
    </fieldset>
</div>