<?php if (!defined('THINK_PATH')) exit();?><style>
#dingwei{
 padding:10px;background-color:#dce9f9;
 width:300px;
 display:block; 
 position: fixed;
 top:50%;
 left:50%;
font-size:14px;
}
</style>
<script>
//查看bank明细
$('#stock_tb tbody tr').dblclick(function(){
	//alert("hello");return;
	var id=$(this).attr('id');
	var title=$('#'+id+' td:nth-child(2)').html()+"明细表";
	var strHtml="<div id='dingwei'><img src='/ihanc/eva/Application/Home/View/public/images/ajax-loader-trans.gif'>&nbsp;&nbsp;加载中...</div>";
	$('body').append(strHtml);
	$.get("/ihanc/eva/index.php/Home/Finance/bankDetail",{'idbank':id},function(result){
		//alert(result);return;
		$('#bank_detail').html(result);
		$('#dingwei').remove();
		$('#bank_detail').dialog({
			 width:900,
			 maxHeight:700,
			 title:title,
			 modal: true,
		     close: function () { $(this).html(''); },
		     buttons: {
		    	 "关闭": function() { $(this).dialog( "close" ); },
		      },
		});
	});
});
function delBank(bankid){
	var del=confirm("是否确认删除该银行信息？");
	if(del==false) return;
	var strHtml="<div id='dingwei'><img src='/ihanc/eva/Application/Home/View/public/images/ajax-loader-trans.gif'>&nbsp;&nbsp;加载中...</div>";
	$('body').append(strHtml);
	$.get("/ihanc/eva/index.php/Home/Finance/delBank",{'idbank':bankid},function(result){
		$('#dingwei').remove();
		data=$.parseJSON(result);
		mynotice(data);
		if(data.status==1){
			fresh();
		}
	});
	
}
function setBank(idbank){
	var strHtml="<div id='dingwei'><img src='/ihanc/eva/Application/Home/View/public/images/ajax-loader-trans.gif'>&nbsp;&nbsp;加载中...</div>";
	$('body').append(strHtml);
	$.get("/ihanc/eva/index.php/Home/Finance/setBank",{'idbank':idbank},function(result){
		$('#dingwei').remove();
		data=$.parseJSON(result);
		mynotice(data);
		if(data.status==1){
			fresh();
		}
	});
	
}
</script>
<h1 class="page-title">银行管理</h1>
<div class="container_12 clearfix leading">
<div class="grid_12">
<table class="bordered" style="width:100%;font-size:12px" id='stock_tb'> 
<thead>
<tr>
<th>#</th>
<th title="双击添加银行" ondblclick="bankAdd()">银行名称</th>
<th>金额</th>
<th>管理</th>
</tr>
</thead>
<tbody>
<?php if(is_array($bank_list)): $i = 0; $__LIST__ = $bank_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr id=<?php echo ($vo["idbank"]); ?> title="双击查看银行明细">
<td><?php echo ($i); ?></td>
<td><?php echo ($vo["bname"]); ?></td>
<td><?php echo ($vo["sum"]); ?></td>
<td><?php if($vo["weight"] > 0): ?>默认银行<?php else: ?>
<a onclick="setBank(<?php echo ($vo["idbank"]); ?>)">设为默认</a><?php endif; if($vo["sum"] == 0): ?>|<a onclick="delBank(<?php echo ($vo["idbank"]); ?>)">删除</a><?php endif; ?>
</td>
</tr><?php endforeach; endif; else: echo "" ;endif; ?>
</tbody>
</table>
<p></p>
 <div class="message info" ><h6>温馨提示:双击"银行名称"可添加银行, 双击银行可查看明细</h6></div>
</div></div>
<script>

function bankAdd(){
	$('#add_bank input[name=bname]').val('');
	$('#add_bank input[name=sum]').val('');
	var divhtml='<div id="add_bank" title="添加银行" style="display:none">';
		divhtml=divhtml+'<form action="/ihanc/eva/index.php/Home/Finance/bankAdd" method="post" id="add_bank_dialog_form">';
		divhtml=divhtml+'<fieldset>';
		divhtml=divhtml+'<label for="name" style="font-size:13px">银行名称:</label>';
		divhtml=divhtml+'<input type="text" name="bname" style="margin-bottom:10px; width:95%; padding: .4em;" >' ;
		divhtml=divhtml+'<label for="ssn" style="font-size:13px">初始金额:</label>';
		divhtml=divhtml+'<input type="text" name="sum" style="margin-bottom:10px; width:95%; padding: .4em;">' ;
		divhtml=divhtml+'</fieldset></form></div>' ;
   		$('#main-content').append(divhtml);
	$('#add_bank').dialog({
		 height: 300,
 	      width: 300,
 	      modal: true,
 	      close: function () { $(this).remove(); },
 	      buttons: {
 	        "添加": function(){
 	        	//if()
 	        	var sum=$('#add_bank input[name=sum]').val();
 	        	if(isNaN(sum)){
 	        		alert("请输入正确的金额!");
 	        		return;
 	        	}
 	        	$.post("/ihanc/eva/index.php/Home/Finance/bankAdd",{
 	        		'bname':$('#add_bank input[name=bname]').val(),
 	        		'sum':sum},
 	        		function(data){
 	        			var obj=$.parseJSON(data);
 	        			mynotice(obj);
 	        			if(obj.status==1){
 	        				$('#add_bank').dialog('close');
 	        				fresh();
 	        			}
 	        			// $('#add_bank').dialog( "close" );
 	        		});
 	        },
 	        "取消": function() {
 	        $(this).dialog( "close" );
 	        }
 	      },
	});

}
function fresh(){
link="/ihanc/eva/index.php/home/finance/bank";
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
<!-- 添加银行 -->

<div id="bank_detail" style="font-size:12px"></div>