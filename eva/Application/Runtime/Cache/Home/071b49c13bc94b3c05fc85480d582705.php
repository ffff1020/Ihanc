<?php if (!defined('THINK_PATH')) exit();?>
<script>
$('#page a').click(function(){
	var href=$(this).attr('href');
	$.get(href, function(result){
	    $("#member_table").html(result);
	  });
	return false;
});
$('table tr').dblclick(function(){
	var idmember=$(this).attr('id');
	//alert("hello");
	
	$('#idmember').val(idmember);
	$('#mname').val($('#'+idmember+" td:nth-child(2)").text());
	$('#msn').val($('#'+idmember+" td:nth-child(3)").text());
	$('#mtel').val($('#'+idmember+" td:nth-child(4)").text());
	$('#mname').focus();
});
</script>
<table class="bordered" style="width:100%;font-size:12px"> 
      <tbody>
    <thead> 
    <tr>
        <th>#</th> 
    	<th>客户姓名</th> 
    	<th>客户编码</th> 
    	<th>客户电话</th> 
    </tr>
    </thead>
     <?php if(is_array($member_list)): $i = 0; $__LIST__ = $member_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr id=<?php echo ($vo["idmember"]); ?>>
     <td><?php echo ($i); ?></td>
     <td><?php echo ($vo["mname"]); ?></td>
     <td><?php echo ($vo["msn"]); ?></td>
     <td><?php echo ($vo["mtel"]); ?></td>
     </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                 </tbody> 
                 </table> 
       <div class="green-black" style="float:right" id="page"><?php echo ($page); ?></div>