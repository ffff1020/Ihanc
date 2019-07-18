<?php if (!defined('THINK_PATH')) exit();?>
<script>
$('#page a').click(function(){
	var href=$(this).attr('href');
	$.get(href, function(result){
	    $("#supply_table").html(result);
	  });
	return false;
});
$('table tr').dblclick(function(){
	var idsupply=$(this).attr('id');
	//alert("hello");
	
	$('#idsupply').val(idsupply);
	$('#sname').val($('#'+idsupply+" td:nth-child(2)").text());
	$('#ssn').val($('#'+idsupply+" td:nth-child(3)").text());
	$('#stel').val($('#'+idsupply+" td:nth-child(4)").text());
	$('#sname').focus();
});
</script>
<table class="bordered" style="width:100%;font-size:12px"> 
      <tbody>
    <thead> 
    <tr>
        <th>#</th> 
    	<th>供应商姓名</th> 
    	<th>供应商编码</th> 
    	<th>供应商电话</th> 
    </tr>
    </thead>
     <?php if(is_array($supply_list)): $i = 0; $__LIST__ = $supply_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr id=<?php echo ($vo["idsupply"]); ?>>
     <td><?php echo ($i); ?></td>
     <td><?php echo ($vo["sname"]); ?></td>
     <td><?php echo ($vo["ssn"]); ?></td>
     <td><?php echo ($vo["stel"]); ?></td>
     </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                 </tbody> 
                 </table> 
       <div class="green-black" style="float:right" id="page"><?php echo ($page); ?></div>