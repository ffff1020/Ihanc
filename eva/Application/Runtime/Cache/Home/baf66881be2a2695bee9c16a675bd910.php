<?php if (!defined('THINK_PATH')) exit();?><script>
$(":date").click(function(){
	$(this).dateinput();
}); 
$('#checks').click(function(){
	var sum=0;
	if ($(this).attr("checked")) {  
        $("input[name=idpurchase]").each(function() {  
            $(this).attr("checked", true);  
            $("#screditDetail_sum").val($('#scredit_ttl').val());
            var id=$(this).parents('tr').attr('id');
            sum=sum+parseInt($('#'+id).find('td:nth-child(6)').text());
            $('#show_cf').css("display","none");
            $('#scredit_cf_btn').css("display","block");
        }); 
        $("#screditDetail_sum").val(sum);
    } else {  
        $("input[name=idpurchase]").each(function() {  
            $(this).attr("checked", false);  
            $("#screditDetail_sum").val(0);
            $('#show_cf').css("display","inline");
            $('#scredit_cf_btn').css("display","none");
        });  
    }  
});
$('#screditDetail_tb input[name=idpurchase]').change(function(){
	var sum=0;
	$('#screditDetail_tb input[name=idpurchase]').each(function(){
		 if ($(this).attr("checked")) {  
             var id=$(this).val();  
             sum=sum+parseInt($('#'+id).find('td:nth-child(6)').text());
         }  
	});
	if(sum==0){ 
		$('#show_cf').css("display","inline");
		}else{$('#show_cf').css("display","none");}
	$('#scredit_cf_btn').css("display","block");
	$("#screditDetail_sum").val(sum);
});

$('#screditDetail_sum').focus(function(){
	$("input[name=idpurchase]").each(function() {  
        $(this).attr("checked", false);  
        $("#screditDetail_sum").val('');
        $('#show_cf').css("display","inline");
        $('#scredit_cf_btn').css("display","none");
    });  
});

$('#scredit_cf_btn').click(function(){
	var str="<div id='remark_dialog' title='结转已选账单'><fieldset><p style='font-size:15px'>请输入结转名称以便查询,例如2016-01,或起止日期.</p><input type='text' name='remark'/></fieldset></div>";
	$('body').append(str);
	$( "#remark_dialog" ).dialog({
		  // autoOpen:false,
	       modal: true,
	       close:function () { $(this).remove();},
	       buttons: {
	    	'结转':function(){
	    		var remark=$(this).find('input[name=remark]').val();
	    		if(remark==''){alert("请输入结转名称");return;}
	    		$( this ).dialog( "close" );
	    		var idpurchase=new Array();
	    		$('#screditDetail_tb input[name=idpurchase]').each(function(){
	    			 if ($(this).attr("checked")) {  
	    	           idpurchase.push($(this).val());
	    			 }
	    		});
	    		$('#scredit_detail').dialog("close");
	    		var id="<?php echo ($idsupply); ?>";
	    		//alert(id);
	    		$.get("/ihanc/eva/index.php/Home/Purchase/screditCf",{'idpurchase':idpurchase,'remark':remark,'idsupply':id},
	    		function(result){
	    			//alert(result);return;
	    			var data=$.parseJSON(result);
	    			mynotice(data);
	    		});
	    	},
	              '取消': function() {
	           $( this ).dialog( "close" );
	         }
	       }
	     });
});
function cf_print(id){
	window.open("/ihanc/eva/index.php/Home/Purchase/cfPrint/idpurchase/"+id);
}
function mcredit_date(){
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
	}	
	 var ttlsum=0;
	 $("input[name=idpurchase]").each(function() {  
		 var idpurchase=$(this).parents('tr').attr('id');
		 var pdate=$('#'+idpurchase+' td:nth-child(2)').text();
		 pdate=new Date(pdate.replace("-", "/").replace("-", "/"));
		 var sum=$('#'+idpurchase+' td:nth-child(6)').text();
		 sum=parseInt(sum);
		 if((pdate>=start)&&(pdate<=end)&&(sum>0)){
           $(this).attr("checked", true);  
           ttlsum=ttlsum+sum;
		 } 
     });  
	 $("#screditDetail_sum").val(ttlsum);
	 $('#show_cf').css("display","none");
     $('#scredit_cf_btn').css("display","block");
}
</script>
<p></p>
<lable style="display:inline">开始时间:</lable><input type="date" id=sdate  style="display:inline"/>&nbsp;&nbsp;
<lable style="display:inline">结束时间:</lable><input type="date" id=edate  style="display:inline"/>&nbsp;&nbsp;
<button style="display:inline" onclick="mcredit_date()">选择</button>
<p></p>
<fieldset>
<form>
<table class="bordered" style="width:100%;font-size:12px" id="screditDetail_tb" >
    <thead> 
    <tr>
        <th>#</th> 
        <th>日期</th> 
    	<th>商品名称</th>
    	<th>价格</th>
    	<th>数量</th> 
    	<th>金额</th> 
    	<th>操作</th>
    </tr>
    </thead>
    <tbody>
    <?php if(is_array($scredit_detail_list)): $i = 0; $__LIST__ = $scredit_detail_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr id=<?php echo ($vo["idpurchase"]); ?>>
    <td><?php echo ($i); ?></td>
    <?php if($vo["idgoods"] > 0): ?><td><?php echo (substr($vo["time"],0,10)); ?></td>
    <td><?php echo ($vo["gname"]); ?></td>
    <td><?php echo ($vo["price"]); ?></td>
    <td><?php echo ($vo["number"]); ?></td>
    <td><?php echo ($vo["sum"]); ?></td>
    <?php elseif($vo["cf"] == 0): ?>
     <td><?php echo (substr($vo["time"],0,10)); ?>付款:</td>
    <td><?php echo ($vo["gname"]); ?></td>
    <td><?php echo ($vo["price"]); ?></td>
    <td><?php echo ($vo["number"]); ?></td>
    <td><font color="red"><?php echo ($vo["sum"]); ?></font></td>
    <?php else: ?>
    <?php if($vo["remark"] != null): ?><td onclick="cf_print(<?php echo ($vo["idpurchase"]); ?>)" title="点击查看明细"><?php echo ($vo["remark"]); ?>&nbsp;结转:</td>
    <?php else: ?>
    <td onclick="cf_print(<?php echo ($vo["idpurchase"]); ?>)" title="点击查看明细"><?php echo (substr($vo["time"],0,10)); ?>结转余额:</td><?php endif; ?>
    <td><?php echo ($vo["gname"]); ?></td>
    <td><?php echo ($vo["price"]); ?></td>
    <td><?php echo ($vo["number"]); ?></td>
    <td><font color="red"><?php echo ($vo["sum"]); ?></font></td><?php endif; ?>
    <td><input type="checkbox" name="idpurchase" value=<?php echo ($vo["idpurchase"]); ?>></td>
    </tr><?php endforeach; endif; else: echo "" ;endif; ?></tbody>
   
</table>
<div style="display:block">&nbsp;
<input type="hidden" value=<?php echo ($idsupply); ?> id='idsupply'/>
<label style="font-size:12px;display:inline;float:right">&nbsp;全选&nbsp;&nbsp;</label>
<input type="checkbox" id='checks' style="display:inline;float:right"/>&nbsp;
</div>
<div style="display:block ">
<div style="display:inline">
<label style="font-size:12px;display:inline">付款金额:</label>
<input type="text" id='screditDetail_sum' name="sum" style="display:inline-block;width:30%"/>
</div>
<div style="display:inline" id="show_cf">
<label style="font-size:12px;display:inline-block">是否结转:</label>
<input type="checkbox" id='cf' style="display:inline-block">
</div>
<div style="float:right;display:inline">
<input type="button" value="结转已选" style="font-size:12px;color:red;display:none" id="scredit_cf_btn"/>
</div>
</div>
<div>
<label style="font-size:12px;display:inline-block">付款银行:</label>
<select id='idbank' style="display:inline-block;font-size:15px;">
<?php if(is_array($banklist)): $i = 0; $__LIST__ = $banklist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value=<?php echo ($vo["idbank"]); ?>>
<?php echo ($vo["bname"]); ?>
</option><?php endforeach; endif; else: echo "" ;endif; ?>
</select>
</div>
</form>
<p></p>
<div class="message info" style="font-size:12px">提示:
<p>1. 逐笔付款删帐时,不能修改付款金额.</p>
<p>2. 任意付款金额支持结转,俗称:"腰帐"</p>
</div>
</fieldset>