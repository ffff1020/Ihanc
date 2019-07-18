<?php if (!defined('THINK_PATH')) exit();?>
<script>
$('#page a').click(function(){
	var href=$(this).attr('href');
	$.get(href, function(result){
	    $("#freight_table").html(result);
	  });
	return false;
});
$('#freight_tb tbody tr').dblclick(function(){
	var id=$(this).attr('id').substr(7);
	var sname=$(this).find(' td:nth-child(2)').text();
	var sum=$(this).find(' td:nth-child(3)').text()
	var title=sname+":"+sum;
	var strHtml="<div id='dingwei'><img src='/ihanc/eva/Application/Home/View/public/images/ajax-loader-trans.gif'>&nbsp;&nbsp;加载中...</div>";
	$('body').append(strHtml);
	var divhtml="<div id='freight_detail'></div>";  //
	$('#main-content').append(divhtml);              //20150104
	$.get("/ihanc/eva/index.php/Home/Purchase/freightDetail",{'idstaff':id},function(result){
		$('#dingwei').remove();
		$('#freight_detail').html(result);
		$('#freight_detail').dialog({
			 width:600,
			 maxHeight:600,
			 title:title,
			 modal: true,
		     close: function () { $(this).remove(); },
		     buttons: {
		         "付款":payment,
		    	 "取消": function() { $(this).remove(); },
		      },
		});
	});
	
});
function payment(){
	var sum=$('#freightDetail_sum').val();
	if(isNaN(sum)){
		alert('请输入正确的付款金额!');
		return;
	} 
	var idfreight=new Array();
	var idbank=$('#idbank').val();
	var idstaff=$('#idstaff').val();
	$('#freightDetail_tb input[name=idfreight]').each(function(){
		 if ($(this).attr("checked")) {  
			 idfreight.push($(this).val());
		 }
	});
	$.post("/ihanc/eva/index.php/Home/Purchase/freightPayment",{
		'sum':sum,
		'idfreight':idfreight,
		'idbank':idbank,
		'idstaff':idstaff,
		'staff_name':$('#'+idstaff).find(' td:nth-child(2)').text(),
	},function(result){
		//alert(result);return;
		var data=$.parseJSON(result);
		mynotice(data);
		if(data.status==1){
			$('#freight_detail').dialog("close");
			fresh();
		}
		$('#freight_detail').dialog("close");
	});
	
}
function fresh(){
	link="/ihanc/eva/index.php/home/purchase/freight";
	//alert(link);
	//link=link+h.replace(/^\#/, "");
    id = link.replace(/[\/\.]/,"-");
    $('#'+id).length && $('#'+id).remove();
    $.ajax(link, {
    	type: "POST",
    	async: false,
       // dataType: "html",
        cache: false,
        success: function(data, textStatus, jqXHR) {
            return pageDownloaded(data, id);
        },
        complete: function(jqXHR, textStatus) {
        }
    });
}
</script>
<h1 class="page-title">员工垫付费用管理</h1>
<div class="container_12 clearfix leading">
<div class="grid_12">
<h6><?php echo ($ttl); ?></h6>
<table class="bordered" style="width:100%;font-size:12px" id='freight_tb'> 
      <tbody>
    <thead> 
    <tr>
        <th>#</th> 
    	<th>员工姓名</th> 	 
    	<th>应付金额</th> 	
    	
    </tr>
    </thead>
    <?php if(is_array($freight_list)): $i = 0; $__LIST__ = $freight_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr id=idstaff<?php echo ($vo["idstaff"]); ?>>
    <td><?php echo ($i); ?></td>
    <td><?php echo ($vo["sname"]); ?></td>
    <td>￥<?php echo (number_format($vo["freight"])); ?>元<?php if($vo["freight"] < 0): ?><font color="red">(有预支)</font><?php endif; ?></td>
    </tr><?php endforeach; endif; else: echo "" ;endif; ?>
</table>


<section class=" grid_12 leading" >
  <div class="message info"><h6>温馨提示:双击员工姓名,可查看明细,进行付款</h6></div>
                 </section></div></div>