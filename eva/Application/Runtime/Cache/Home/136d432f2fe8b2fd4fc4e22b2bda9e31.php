<?php if (!defined('THINK_PATH')) exit();?><script>
$('#page a').click(function(){
	var href=$(this).attr('href');
	$.get(href, function(result){
	    $("#mcredit_table").html(result);
	  });
	return false;
});

$('#mcredit_tb tbody tr').dblclick(function(){
	var idmember=$(this).attr('id').substr(6);
	var strHtml="<div id='dingwei'><img src='/ihanc/eva/Application/Home/View/public/images/ajax-loader-trans.gif'>&nbsp;&nbsp;加载中...</div>";
	$('body').append(strHtml);
	var title=$(this).find( "td:nth(1)").text()+":"+$(this).find( "td:nth(2)").text();
	$.get("/ihanc/eva/index.php/Home/Sale/mcreditDetail",
		{'idmember':idmember},
		function(result){
			$('#dingwei').remove();
			$('#mcredit_detail').html(result);
			$('#mcredit_detail').dialog({
				 width:600,
				 maxHeight:600,
				 title:title,
				 modal: true,
			     close: function () { $(this).html(''); },
			     buttons: {
			         "收款":function(){collect(idmember);$(this).dialog( "close" ); },
			         "打印":myprint,
			    	 "取消": function() { $(this).dialog( "close" ); },
			      },
			});
	});
});

function collect(id){
	//alert(id);return;
	var sum=$('#mcreditDetail_sum').val();
	var mname=$('#'+id).find("td:nth(1)").text();
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
		'idmember':id,
	},function(result){
		var data=$.parseJSON(result);
		if(data.status==1){
			fresh();
	        $('#mcredit_detail').dialog("close");
		}
		mynotice(data);
	});
}
function fresh(){
	 $.get("/ihanc/eva/index.php/Home/Sale/mcreditTable", function(result){
		    $("#mcredit_table").html(result);
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
	$('#mcredit_detail').dialog("close");
	if(idsale.length>0){
		$.ajax({
			 type:"GET",
			 url:"/ihanc/eva/index.php/Home/Sale/saleprint",
			 data:{idsale:idsale,idmember:idmember},
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
</script>

<table class="bordered" style="width:100%;font-size:12px" id='mcredit_tb'> 
     
    <thead> 
    <tr>
        <th>#</th> 
    	<th>客户姓名</th> 	 
    	<th>应收金额</th> 	
    	
    </tr>
    </thead>
     <tbody>
    <?php if(is_array($mcredit_list)): $i = 0; $__LIST__ = $mcredit_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr id=member<?php echo ($vo["idmember"]); ?>>
    <td><?php echo ($i); ?></td>
    <td><?php echo ($vo["mname"]); ?></td>
    <td>￥<?php echo (number_format($vo["sum"])); ?>元</td>
    
    </tr><?php endforeach; endif; else: echo "" ;endif; ?>
</table>
<div class="green-black" style="float:right" id="page"><?php echo ($page); ?></div>