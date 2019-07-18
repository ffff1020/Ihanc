<?php if (!defined('THINK_PATH')) exit();?><script>
$('#page a').click(function(){
	var href=$(this).attr('href');
	$.get(href, function(result){
	    $("#purchase_table").html(result);
	  });
	return false;
});
function showinfo(id){
	var info=$('#'+id).css("display");
	if(info=='none'){
		$.get('/ihanc/eva/index.php/Home/Purchase/purchaseDetail',{'inorder':id}, function(result){
		    $("#"+id+' div').html(result);
		  });
		$('#'+id).css("display","block");
	}else{
		$('#'+id).css("display","none");
	}
}

</script>

<table class="bordered" style="width:100%;font-size:12px" > 
      <tbody>
    <thead> 
    <tr>
        <th>#</th> 
    	<th>入库单号</th> 
    	<th>合计数量</th> 
    	<th>合计金额</th> 
    	<th>合计费用</th>
    	<th>操作</th>
    </tr>
    </thead>
    <?php if(is_array($purchase_list)): $i = 0; $__LIST__ = $purchase_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr id=tr<?php echo ($vo["inorder"]); ?>>
    <td><?php echo ($i); ?></td>
    <td><?php echo ($vo["inorder"]); ?></td>
    <td><?php echo (number_format($vo["ttl_num"],2)); ?></td>
    <td><?php echo ($vo["ttl_sum"]); ?></td>
    <td><?php echo ($vo["ttl_fee"]); ?></td>
    <td><a onclick="showinfo('<?php echo ($vo["inorder"]); ?>')">展开详情</a></td>
    </tr>
    <tr style="display:none" id=<?php echo ($vo["inorder"]); ?>><td colspan=6><div ><img src="/ihanc/eva/Application/Home/View/public/images/ajax-loader-trans.gif">&nbsp;&nbsp;加载中,请稍候...</div></td></tr><?php endforeach; endif; else: echo "" ;endif; ?>
    </tbody>
  
</table>
       <div class="green-black" style="float:right" id="page"><?php echo ($page); ?></div>