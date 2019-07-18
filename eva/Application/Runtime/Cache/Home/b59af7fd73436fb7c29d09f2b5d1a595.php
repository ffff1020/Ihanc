<?php if (!defined('THINK_PATH')) exit();?>
<script>
$(":date").click(function(){
	//alert();
	$(this).dateinput();
}); 

$('#page a').click(function(){
	var href=$(this).attr('href');
	$.get(href, function(result){
	    $("#bank_detail").html(result);
	  });
	return false;
});
function bankDetail(){
	var sdate=$('#sdate').val();
	var edate=$('#edate').val();
	if(sdate&&edate){
		var start=new Date(sdate.replace("-", "/").replace("-", "/"));   
	    var end=new Date(edate.replace("-", "/").replace("-", "/"));  
	    if(end<start){  
	    	alert("结束日期必须大于开始日期!");
	    	$('#sdate').val('');
	    	$('#edate').val('');
	        return;
	    }  
	/*  $.post("/ihanc/eva/index.php/Home/Finance/bankDetail",{'sdate':sdate,'edate':edate,'idbank':$('#idbank').val()},function(result){
		   $('#bank_detail').html(result);
	   });*/
	}else if($('#summary').val()==''){
		return;
	}
	$.post("/ihanc/eva/index.php/Home/Finance/bankDetail",{'sdate':sdate,'edate':edate,'idbank':$('#idbank').val(),'summary':$('#summary').val()},function(result){
		   $('#bank_detail').html(result);
	   });
}
</script>
<p></p><input type="hidden" value=<?php echo ($idbank); ?> id='idbank'/>
<lable style="display:inline">开始时间:</lable><input type="date" id=sdate value='<?php echo ($sdate); ?>' style="display:inline"/>&nbsp;&nbsp;
<lable style="display:inline">结束时间:</lable><input type="date" id=edate value='<?php echo ($edate); ?>'  style="display:inline"/>&nbsp;&nbsp;
<lable style="display:inline">关键字:</lable><input type="text"  id='summary' value='<?php echo ($summary); ?>' style="display:inline"/>&nbsp;&nbsp;
<button style="display:inline" onclick="bankDetail()">查询</button>
<p></p>
<table class="bordered" style="width:100%;font-size:13px;" id="bankDetail_tb" >
<thead>
<tr>
<th>#</th>
<th>交易时间</th>
<th>交易金额</th>
<th>余额</th>
<th>操作人</th>
<th>备注</th>
</tr>
</thead>
<tbody>
<?php if(is_array($bank_list)): $i = 0; $__LIST__ = $bank_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
<td><?php echo ($i); ?></td>
<td><?php echo ($vo["time"]); ?></td>
<td><?php if(($vo["sum"] < 0) ): ?><font color='red'>￥<?php echo (number_format($vo["sum"],2)); ?>元</font>
<?php else: ?> ￥<?php echo (number_format($vo["sum"],2)); ?>元<?php endif; ?></td>
<td>￥<?php echo (number_format($vo["balance"],2)); ?>元</td>
<td><?php echo ($vo["user"]); ?></td>
<td><?php echo ($vo["summary"]); ?></td>
</tr><?php endforeach; endif; else: echo "" ;endif; ?>
</tbody>
</table>
<div class="green-black" style="float:right;font-size:10px" id="page"><?php echo ($page); ?></div>