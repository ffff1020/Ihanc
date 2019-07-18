<?php if (!defined('THINK_PATH')) exit();?>
<script>
$('#page a').click(function(){
	var href=$(this).attr('href');
	$.get(href, function(result){
	    $("#goods_table").html(result);
	  });
	return false;
});
//双击产品进行修改,form赋值
$('table tr').dblclick(function(){
	var idgoods=$(this).attr('id');
	$('#idgoods').val(idgoods);
	$('#gname').val($('#'+idgoods+" td:nth-child(2)").text());
	$('#gsn').val($('#'+idgoods+" td:nth-child(3)").text());
	$('#gname').focus();
	var id=$('#'+idgoods+" td:nth-child(1) input[name='idcat']").val();
	//$("#idcat").removeAttr("style");
	$("#idcat select").val(id);	
	$("#idcat span").text($('#'+idgoods+" td:nth-child(4)").text());
});
</script>
<table class="bordered" style="width:100%;font-size:12px"> 
      <tbody>
    <thead> 
    <tr>
        <th>#</th> 
    	<th>产品名称</th> 
    	<th>产品编码</th> 
    	<th>所属种类</th> 
    </tr>
    </thead>
     <?php if(is_array($goods_list)): $i = 0; $__LIST__ = $goods_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr id=<?php echo ($vo["idgoods"]); ?>>
     <td><?php echo ($i); ?><input type="hidden" value=<?php echo ($vo["idcat"]); ?> name="idcat"></td>
     <td><?php echo ($vo["gname"]); ?></td>
     <td><?php echo ($vo["gsn"]); ?></td>
     <td><?php echo ($vo["idname"]); ?></td>
     </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                 </tbody> 
                 </table> 
       <div class="green-black" style="float:right" id="page"><?php echo ($page); ?></div>