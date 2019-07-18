<?php if (!defined('THINK_PATH')) exit();?>
<script>
$('#checks').click(function(){
	var sum=0;
	if ($(this).attr("checked")) {  
        $("input[name=idfreight]").each(function() {  
            $(this).attr("checked", true);  
           
            var id=$(this).parents('tr').attr('id');
            sum=sum+parseInt($('#'+id).find('td:nth-child(5)').text());
          
        });  
        $("#freightDetail_sum").val(sum);
    } else {  
        $("input[name=idfreight]").each(function() {  
            $(this).attr("checked", false);  
            $("#freightDetail_sum").val(0);
          
        });  
    }  
});
$('#freightDetail_tb input[name=idfreight]').change(function(){
	var sum=0;
	$('#freightDetail_tb input[name=idfreight]').each(function(){
		 if ($(this).attr("checked")) {  
             var id=$(this).val();  
             sum=sum+parseInt($('#'+id).find('td:nth-child(5)').text());
         }  
	});
	
	$("#freightDetail_sum").val(sum);
});

$('#freightDetail_sum').focus(function(){
	$("input[name=idfreight]").each(function() {  
        $(this).attr("checked", false);  
        $("#freightDetail_sum").val('');
    });  
});
</script>
<fieldset>
<form>
<table class="bordered" style="width:100%;font-size:12px" id="freightDetail_tb" >

    <thead> 
    <tr>
        <th>#</th> 
        <th>日期</th>
        <th>入库单号</th> 
        <th>备注</th> 
    	<th>金额</th> 
    	<th>操作</th>
    </tr>
    </thead>
    <tbody>
    <?php if(is_array($freight_detail_list)): $i = 0; $__LIST__ = $freight_detail_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr id=<?php echo ($vo["idfreight"]); ?>>
    <td><?php echo ($i); ?></td>
    <td><?php echo (substr($vo["time"],0,10)); if($vo["inorder"] == ""): if($vo["freight"] > 0): ?>员工付款<?php else: ?>付款给员工<?php endif; endif; ?></td>
    <td><?php echo ($vo["inorder"]); ?></td>
    <td><?php echo ($vo["subject"]); echo ($vo["remark"]); ?></td>
     <td><?php echo ($vo["freight"]); ?></td>
    <td><input type="checkbox" name="idfreight" value=<?php echo ($vo["idfreight"]); ?>></td>
    </tr><?php endforeach; endif; else: echo "" ;endif; ?></tbody>
</table><div style="float:right;">
<input type="hidden" value=<?php echo ($idstaff); ?> id='idstaff'>
<input type="checkbox" id='checks' style="display:inline-block">
<label style="font-size:12px;display:inline-block">&nbsp;全选</label>
</div>
<p></p><div style="display:inline">
<label style="font-size:12px;display:inline-block">付款金额:</label>
<input type="text" id='freightDetail_sum' name="sum" style="display:inline-block;width:30%"/>
</div>
<div>
<label style="font-size:12px;display:inline-block">付款银行:</label>
<select id='idbank' style="display:inline-block;font-size:15px;">
<?php if(is_array($banklist)): $i = 0; $__LIST__ = $banklist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value=<?php echo ($vo["idbank"]); ?>>
<?php echo ($vo["bname"]); ?>
</option><?php endforeach; endif; else: echo "" ;endif; ?>
</select>
</div>
</form><p></p>
<div class="message info" style="font-size:12px">提示:
<p>1. 逐笔付款删帐时,不能修改付款金额.</p>
</div>
</fieldset>