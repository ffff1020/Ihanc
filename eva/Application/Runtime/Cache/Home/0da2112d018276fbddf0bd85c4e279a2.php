<?php if (!defined('THINK_PATH')) exit();?><script>
$('#page a').click(function(){
	var href=$(this).attr('href');
	$.get(href, function(result){
	    $("#scredit_table").html(result);
	  });
	return false;
});
$('#scredit_tb tbody tr').dblclick(function(){
	var id=$(this).attr('id').substr(6);
	var sname=$(this).find(' td:nth-child(2)').text();
	var sum=$(this).find(' td:nth-child(3)').text()
	var title=sname+":"+sum;
	var strHtml="<div id='dingwei'><img src='/ihanc/eva/Application/Home/View/public/images/ajax-loader-trans.gif'>&nbsp;&nbsp;加载中...</div>";
	$('body').append(strHtml);
	var divhtml="<div id='scredit_detail'></div>";
	$('#main-content').append(divhtml);
	//alert(id);
	$.get("/ihanc/eva/index.php/Home/Purchase/screditDetail",{'idsupply':id},function(result){
		$('#dingwei').remove();
		$('#scredit_detail').html(result);
		$('#scredit_detail').dialog({
			 width:600,
			 maxHeight:600,
			 title:title,
			 modal: true,
		     close: function () { $(this).remove(); },
		     buttons: {
		         "付款":payment,
		         "打印":myprint,
		    	 "取消": function() { $(this).dialog( "close" ); },
		    	 
		      },
		});
	});
	
});
function payment(){
	if(isNaN($('#screditDetail_sum').val())||($('#screditDetail_sum').val()=='')){
		alert('请输入正确的付款金额!');
		return;
	} 
	var sum=parseInt($('#screditDetail_sum').val());
	var idpurchase=new Array();
	var idbank=$('#idbank').val();
	var cf=$('#cf').attr('checked');
	cf=cf?1:0;
	$('#screditDetail_tb input[name=idpurchase]').each(function(){
		 if ($(this).attr("checked")) {  
           idpurchase.push($(this).val());
		 }
	});
	$.post("/ihanc/eva/index.php/Home/Purchase/screditPayment",{
		'sum':sum,
		'idpurchase':idpurchase,
		'idbank':idbank,
		'cf':cf,
		'idsupply':$('#idsupply').val(),
	},function(result){
		var data=$.parseJSON(result);
		//$('#scredit_detail').remove();
		if(data.status==1){
			fresh();
			$('#scredit_detail').remove();
		}
		mynotice(data);
		//$('#scredit_detail').dialog("close");
	});
	
}
function fresh(){
	 $.get("/ihanc/eva/index.php/Home/Purchase/screditTable", function(result){
		    $("#scredit_table").html(result);
		  });
}
function myprint(){
	var idsupply=$('#idsupply').val();
	var idpurchase=[];
	$('#screditDetail_tb input[name=idpurchase]').each(function(){
		 if ($(this).attr("checked")) {  
          idpurchase.push($(this).val());
		 }
	});
	$('#scredit_detail').dialog("close");
	if(idpurchase.length>0){
		$.ajax({
			 type:"GET",
			 url:"/ihanc/eva/index.php/Home/Purchase/purchaseprint",
			 data:{idpurchase:idpurchase,idsupply:idsupply},
			 async: false,
			 success: function(data){
				 myWindow=window.open();
				 myWindow.document.write(data);
				 myWindow.focus();
			 }
		});
	}else
	window.open("/ihanc/eva/index.php/Home/Purchase/purchaseprint/idsupply/"+idsupply);
}

</script>
<h6><?php echo ($ttl); ?></h6>
<table class="bordered" style="width:100%;font-size:12px" id='scredit_tb'> 
      <tbody>
    <thead> 
    <tr>
        <th>#</th> 
    	<th>供应商姓名</th> 	 
    	<th>应付金额</th> 	
    	
    </tr>
    </thead>
    <?php if(is_array($scredit_list)): $i = 0; $__LIST__ = $scredit_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr id=supply<?php echo ($vo["idsupply"]); ?>>
    <td><?php echo ($i); ?></td>
    <td><?php echo ($vo["sname"]); ?></td>
    <td>￥<?php echo (number_format($vo["sum"])); ?>元</td>
    
    </tr><?php endforeach; endif; else: echo "" ;endif; ?>
</table>
<div class="green-black" style="float:right" id="page"><?php echo ($page); ?></div>