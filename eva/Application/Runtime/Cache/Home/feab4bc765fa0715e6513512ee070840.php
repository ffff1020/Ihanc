<?php if (!defined('THINK_PATH')) exit();?><script>
$('tr').click(function(){
var strHtml="<div id='dingwei'><img src='/eva/Application/Home/View/public/images/ajax-loader-trans.gif'>&nbsp;&nbsp;加载中...</div>";
$('body').append(strHtml);
var divhtml="<div id='mcredit_detail'></div>";  //
$('#main-content').append(divhtml);              //20150104
var title=$('#sale_form input[name=mname]').val()+$(this).text();
$.get("/eva/index.php/home/sale/mcreditDetail",
	{'idmember':$(this).attr('id')},
	function(result){
		$('#dingwei').remove();
		$('#mcredit_detail').html(result);
		$('#mcredit_detail').dialog({
			 width:600,
			 maxHeight:600,
			 title:title,
			 modal: true,
		     close: function () { $(this).remove(); },// html('');
		     buttons: {
		         "打印":myprint,
		    	 "取消": function() { $(this).dialog( "close" ); },
		      },
		});
});
});
function myprint(){
var idmember=$('#idmember').val();
var idsale=[];
$('#mcreditDetail_tb input[name=idsale]').each(function(){
	 if ($(this).attr("checked")) {  
      idsale.push($(this).val());
	 }
});
$('#mcredit_detail').remove();
if(idsale.length>0){
	$.ajax({
		 type:"GET",
		 url:"/eva/index.php/home/sale/saleprint",
		 data:{idsale:idsale,idmember:idmember},
		// dataType: "json",
		 async: false,
		 success: function(data){
			 myWindow=window.open();
			 myWindow.document.write(data);
			 myWindow.focus();
		 }
	});
}else
window.open("/eva/index.php/home/sale/saleprint/idmember/"+idmember);
}
</script>

<h1 class="page-title">财务概况</h1>
 <div class="container_12 clearfix leading">
                    <section class="grid_12"> 
                        <div class="message info">    
                        <?php if($days < 10 ): ?><h3>
                        <?php if($days < 1 ): ?><h3>
                                 尊敬的<?php echo (cookie('nick')); ?>,谢谢您使用本产品。<br/>
                                 您的当前服务已到期,如果您要继续使用请续费,三个月后系统自动删除数据。</h3>
                                 <?php else: ?>     
                                 尊敬的<?php echo (cookie('nick')); ?>,谢谢您使用本产品。<br/>
                                 您的当前服务还有&nbsp;<font color='red'><?php echo ($days); ?></font>&nbsp;天到期,为了不影响您的使用,请及时续费。</h3><?php endif; endif; ?>                         
                        </div>
                    </section> 
                    <section class="portlet grid_6 leading"> 
 					<header>
                            <h2>客户欠款金额前十</h2> 
                        </header>
                         <section>
                            <table class="full" style="width:100%;font-size:12px"> 
                                <tbody> 
                                <?php if(is_array($slist)): $i = 0; $__LIST__ = $slist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr id=<?php echo ($vo["idmember"]); ?>>
                                <td><?php echo ($i); ?></td>
                                <td><?php echo ($vo["mname"]); ?></td>
                                <td ><?php echo (substr($vo["time"],0,10)); ?></td>
                                <td >￥<?php echo (number_format($vo["sum"])); ?></td>
                                </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                                </tbody>
                                </table>
                                </section>
                    </section>
                    <section class="portlet grid_6 leading"> <header>
                            <h2>客户欠款日期前十</h2> 
                        </header>
                        <section>
                            <table class="full" style="width:100%;font-size:12px"> 
                                <tbody> 
                                 <tbody> 
                                <?php if(is_array($tlist)): $i = 0; $__LIST__ = $tlist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr id=<?php echo ($vo["idmember"]); ?>>
                                <td><?php echo ($i); ?></td>
                                <td><?php echo ($vo["mname"]); ?></td>
                                <td ><?php echo (substr($vo["time"],0,10)); ?></td>
                                <td >￥<?php echo (number_format($vo["sum"])); ?></td>
                                </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                                </tbody>
                                </table>
                                </section>
                    </section>
                   
 </div>