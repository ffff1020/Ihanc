<?php if (!defined('THINK_PATH')) exit();?>
<style>
table input[type="text"]{
	height:30px;
    border:none; 
	width:100%;
	border-width:0px;
	background-color:transparent;
    box-shadow:  none;
}
table input[type="text"]:focus{
	background-color:white;
}
#result{border:1px solid #817FB2; display:none;background-color: #F1F19B;z-index:999;float:left;}
#result li{background:#FFF; text-align:left;}
#result li.cls{text-align:right;}
#result ul{padding:0px; margin:0px;} 
#result li a{display:block; padding:4px;margin:0px; cursor:pointer; color:#666;text-decoration:none;font-size:12px}
#result li a:hover{background:#D8D8D8; text-decoration:none; color:#000;}
#result li a.try{background:#D8D8D8; text-decoration:none; color:#000;}

.bordered tr:last-child td:first-child {
    -moz-border-radius: 0 0 0 6px;
    -webkit-border-radius: 0 0 0 6px;
    border-radius: 0 0 0 0px;
}

.bordered tr:last-child td:last-child {
    -moz-border-radius: 0 0 6px 0;
    -webkit-border-radius: 0 0 6px 0;
    border-radius: 0 0 0px 0;
}
</style>
<script type="text/javascript">
$(document).ready(function(){
   
    var str_staff=$("input[name='stafflist']").val();
    var str="<select name='subject' ><option>运费</option><option>搬运费</option><option>餐饮费</option><option>其它费用</option></select>";
   
    for (var l = 0; l < 5; l++) { 
    	var timestamp=new Date().getTime();
    	var trHTML = "<tr id="+timestamp+"><td  width=8%><a><img src='/ihanc/eva/Application/Home/View/public/images/plus.png' alt='添加行' onclick='addNew();'/></a>&nbsp;&nbsp;<a><img src='/ihanc/eva/Application/Home/View/public/images/dd.png' alt='删除行' onclick='del(this);'/></td><td width=20% ><input type='text' name='sname' onkeyup='getsupply(this)' onkeydown='selectSupply(this)';/><input type='hidden' name='idsupply'/></td><td width=20%><input type='text' name='gname' onkeyup='getgoods(this)' onkeydown='selectgoods(this)' ;/><input type='hidden' name='idgoods'/></td><td width=15%><input type='text' name='number' onkeydown=focNext() onblur=calsum(this) /></td><td width=15%><input type='text' name='price' onkeydown=focNext() onblur=calsum(this) /></td><td width=15%><input type='text'  name='sum' readonly tabIndex='-1' ></input></td></tr>";
	$("#tb").append(trHTML);
	    var str_fee="<tr><td  width=10%><a><img src='/ihanc/eva/Application/Home/View/public/images/plus.png' alt='添加行' onclick='addNew_fee();'/></a>&nbsp;&nbsp;<a><img src='/ihanc/eva/Application/Home/View/public/images/dd.png' alt='删除行' onclick='del_fee(this);'/></td><td width=20%>"+str+"</td><td width=20%><input type='text' name='freight' onblur='calfee(this)'></td><td width=15%>"+str_staff+"</td><td><input type='text' name='remark'></td></tr>";
		$('#tb_fee').append(str_fee);
    }
	
});

function getsupply(obj){
	 var tx=$(obj); 
	// var index=0;
	 var key = event.keyCode; 
	 var lf=tx.offset().left;
     var tp=tx.offset().top+30;
	 if ($.trim(tx.val()).length == 0){
   	        $('#result').html('');$('#result').hide();
   	        var id=tx.parents("tr").attr('id');
	        $('#'+id).find("input[name='idsupply']").val('');
            return; }
	 if(((key>47)&&(key<106))||(key==8)||(key==32)){
	   $.post('/ihanc/eva/index.php/Home/Purchase/searchSupply',{'key':tx.val()},function(data){
		 var obj=$.parseJSON(data);  
		 if(data) {
			var trs = "<ul>";  
			len=obj.sname.length;
  		    for (var i = 0; i < len; i++) {
  		     var value=obj.sname[i]+"+"+obj.idsupply[i];
  			 trs+='<li style="list-style-type:none;"><a href="javascript: fillsupply('+tx.parents("tr").attr('id')+',\''+value+'\')" title='+obj.idsupply[i]+'>'+obj.sname[i]+'</a></li>';    
  	        }; 
  	    $('#result').css({'width':tx.width()-10});
  	    $('#result').html(trs).css('display','block');
  	    $('#result').show().offset({top:tp,left:lf}); 
		}else{
			$('#result').html('');$('#result').hide();
		    tx.val('');
		    }
		 li = $("#result").find("li:eq(" + index + ")");
	     li.css("background", "#D8D8D8").siblings().css("background", "#fff"); 
		});
	 }
};
    var index=0;
	var li;
function selectSupply(obj){
	var tx=$(obj); 
	var key = event.keyCode;  
    if (key == 13){
    	if($('#result').css('display')!='block'){
		    window.event.keyCode=9;
  			return;
    	};
  	 var idsupply= $("#result li:eq(" + index + ") a").attr('title');
  	    tx.val($("#result li:eq(" + index + ") a").text()); 
  	    tx.parent().find("input[name='idsupply']").val(idsupply);
  	  $('#result').html('');$('#result').hide();
  	  return;
  	  };
    if (key == 38) {  
        index--; 
        if (index == 0) index = 9; 
    } ;
    if (key == 40) {  
        index++;  
        var len=$('#result li').length;
        if (index == len) index = 0;   
    }else index=0;
      li = $("#result").find("li:eq(" + index + ")");
	  li.css("background", "#D8D8D8").siblings().css("background", "#fff");
}
function fillsupply(id,value){
	 var arr=value.split("+");
	$('#'+id).find("input[name='sname']").val(arr[0]);
	$('#'+id).find("input[name='idsupply']").val(arr[1]);
	$('#result').html('');$('#result').hide();
}

function getgoods(obj){
	 var tx=$(obj); 
	// var index=0;
	 var key = event.keyCode; 
	 var lf=tx.offset().left;
     var tp=tx.offset().top+30;
	 if ($.trim(tx.val()).length == 0){
   	        $('#result').html('');$('#result').hide();
   	        var id=tx.parents("tr").attr('id');
   	        $('#'+id).find("input[name='idgoods']").val('');
            return; }
	 if(((key>47)&&(key<106))||(key==8)||(key==32)){
	   $.post('/ihanc/eva/index.php/Home/Purchase/searchGoods',{'key':tx.val()},function(data){
		 var obj=$.parseJSON(data);  
		 if(data) {
			var trs = "<ul>";  
			len=obj.gname.length;
  		    for (var i = 0; i < len; i++) {
  		     var value=obj.gname[i]+"+"+obj.idgoods[i];
  			 trs+='<li style="list-style-type:none;"><a href="javascript: fillgoods('+tx.parents("tr").attr('id')+',\''+value+'\')" title='+obj.idgoods[i]+'>'+obj.gname[i]+'</a></li>';    
  	        }; 
      	$('#result').css({'width':tx.width()-10});
	    $('#result').html(trs).css('display','block');
	    $('#result').show().offset({top:tp,left:lf}); 
		}else{
			$('#result').html('');$('#result').hide();
			tx.val('');
		}
		 li = $("#result").find("li:eq(" + index + ")");
	     li.css("background", "#D8D8D8").siblings().css("background", "#fff"); 
		});
	 }
};
function selectgoods(obj){
	var tx=$(obj); 
	var key = event.keyCode;  
    if (key == 13){
    	if($('#result').css('display')!='block'){
		    window.event.keyCode=9;
  			return;
    	};
  	 var idgoods= $("#result li:eq(" + index + ") a").attr('title');
  	    tx.val($("#result li:eq(" + index + ") a").text()); 
  	    tx.parent().find("input[name='idgoods']").val(idgoods);
  	   // alert(tx.parent().find("input[name='idgoods']").val());
  	    $('#result').html('');$('#result').hide();
  	    return;
  	  };
    if (key == 38) { 
        index--; 
        if (index == 0) index = 9; 
    } ;
    if (key == 40) { 
        index++;  
        var len=$('#result li').length;
        if (index == len) index = 0;   
    }else index=0;
      li = $("#result").find("li:eq(" + index + ")");
	  li.css("background", "#D8D8D8").siblings().css("background", "#fff");
}
function fillgoods(id,value){
	 var arr=value.split("+");
	$('#'+id).find("input[name='gname']").val(arr[0]);
	$('#'+id).find("input[name='idgoods']").val(arr[1]);
	$('#result').html('');$('#result').hide();
}
function addNew(){
	var timestamp=new Date().getTime();
	var trHTML = "<tr id="+timestamp+"><td  width=8%><a><img src='/ihanc/eva/Application/Home/View/public/images/plus.png' alt='添加行' onclick='addNew();'/></a>&nbsp;&nbsp;<a><img src='/ihanc/eva/Application/Home/View/public/images/dd.png' alt='删除行' onclick='del(this);'/></td><td width=20% ><input type='text' name='sname' onkeyup='getsupply(this)' onkeydown='selectSupply(this)';/><input type='hidden' name='idsupply'/></td><td width=20%><input type='text' name='gname' onkeyup='getgoods(this)' onkeydown='selectgoods(this)' ;/><input type='hidden' name='idgoods'/></td><td width=15%><input type='text' name='number' onkeydown=focNext() onblur=calsum(this)  tabIndex='-1'/></td><td width=15%><input type='text' name='price' onkeydown=focNext() onblur=calsum(this) /></td><td width=15%><input type='text'  name='sum' readonly tabIndex='-1' ></input></td></tr>";
    $('#tb').append(trHTML);
    var height=$('form').css('height');
    height=parseInt(height)+51;
    //alert(height);
    $('form').css('height',height);
    
}
function del(tdobject){
	var td=$(tdobject);  
	var tr=td.parents("tr");
	var sum=tr.find("input[name='sum']").val();
	if($("#tb tr").length>3){
		tr.remove();
		 var height=$('form').css('height');
		    height=parseInt(height)-51;
		    //alert(height);
		    $('form').css('height',height);
	}
	if(sum){
		var ttl=0;
		var num=0;
		 $('input[name="sum"]').each(function(i, o){
	   		    var temp=$.trim($(o).val());	
   		    if(temp>0){
    		    ttl = ttl+parseInt(temp); 
    		    var temp_num=$(o).parents("tr").find("input[name='number']").val();
 		        num=num+parseInt(temp_num);
    		  }
   		});
   	 $('#ttl_sum').text("￥"+ttl+"元");
 	 $('#ttl_num').text(""+num+"斤");
	}
}
function calsum(obj){
	var tx=$(obj);
	if(isNaN(tx.val())){
		tx.val('');
		tx.parents("tr").find("input[name='sum']").val('');
		return;
	}
	var number=tx.parents("tr").find("input[name='number']").val();
	var price=tx.parents("tr").find("input[name='price']").val();
	var sum=Math.round(number*price);
	tx.parents("tr").find("input[name='sum']").val(sum);  
	if(sum){
		var ttl=0;
		var num=0;
   	    $('input[name="sum"]').each(function(i, o){
   		    var temp=$.trim($(o).val());	
   		    if(temp>0){
   		    ttl = ttl+parseInt(temp); 
   		    var temp_num=$(o).parents("tr").find("input[name='number']").val();
		    num=num+parseInt(temp_num);
   		    }
   		});
   	$('#ttl_sum').text("￥"+ttl+"元");
   	$('#ttl_num').text(""+num+"斤");
	}
}

//table_fee中的function
function addNew_fee(){
	var str_staff=$("input[name='stafflist']").val();
	var str="<select name='subject' ><option>运费</option><option>搬运费</option><option>餐饮费</option><option>其它费用</option></select>";
	var str_fee="<tr><td  width=10%><a><img src='/ihanc/eva/Application/Home/View/public/images/plus.png' alt='添加行' onclick='addNew_fee();'/></a>&nbsp;&nbsp;<a><img src='/ihanc/eva/Application/Home/View/public/images/dd.png' alt='删除行' onclick='del_fee(this);'/></td><td width=20%>"+str+"</td><td width=20%><input type='text' name='freight' onblur='calfee(this)'></td><td width=15%>"+str_staff+"</td><td><input type='text' name='remark'></td></tr>";
	$('#tb_fee').append(str_fee);
    var height=$('form').css('height');
    height=parseInt(height)+51;
    $('form').css('height',height);
    
}
function del_fee(tdobject){
	//alert($("#tb_fee tr").length);
	var td=$(tdobject);  
	var tr=td.parents("tr");
	var sum=tr.find("input[name='freight']").val();
	if($("#tb_fee tr").length>3){
		tr.remove();
		 var height=$('form').css('height');
		    height=parseInt(height)-51;
		    //alert(height);
		    $('form').css('height',height);
	}
	if(sum){
		var ttl=0;
   	    $('input[name="freight"]').each(function(i, o){
   		    var temp=$.trim($(o).val());	
   		    if(temp>0){
    		    ttl = ttl+parseInt(temp); 
    		  }
   		});
   	 $('#ttl_fee').text("￥"+ttl+"元");
	}
}
function calfee(obj){
	var tx=$(obj);
	var fee=tx.val();
	
		var ttl=0;
   	    $('input[name="freight"]').each(function(i, o){
   		    var temp=$.trim($(o).val());	
   		    if(temp>0){
    		    ttl = ttl+parseInt(temp); 
    		  }
   		});
   	 $('#ttl_fee').text("￥"+ttl+"元");
	
}
</script>  
  
                <h1 class="page-title">新建进货单</h1>
                <div class="container_12 clearfix leading">
                    <div class="grid_12">
                        <!-- wizard -->
                        <form action="/ihanc/eva/index.php/Home/Purchase/purchaseAdd" id="purchase_form" class="wizard">
                            <nav>
                                <ul class="clearfix">
                                    <li class="active"><strong>1.</strong> 进货明细</li>
                                    <li><strong>2.</strong> 运输费用</li>
                                    <li><strong>3.</strong> 完成</li>
                                </ul>
                            </nav>
                            <div class="items" >
                                <!-- page1 -->
                                <section>
                                    <header>
                                        <h2>
                                            <strong> 步骤一: </strong> 录入该车货物进货明细
                                            <em>进货单号:<?php echo ($order); ?></em>
                                        </h2>
                                    </header>

                                    <section >
                                    
                                         <table class="bordered" id="tb" style="width:100%;font-size:12px"> 
      <tbody>
    <thead> 
    <tr>
        <th>#</th> 
    	<th ondblclick='addsupply()'>供应商姓名</th> 
    	<th ondblclick='addgoods()'>产品名称</th> 
    	<th>数量</th> 
    	<th>价格</th> 
    	<th>金额</th> 
    </tr>
    </thead>
    <tfoot>
    <tr><td colspan=4 style="padding-left:500px"><b>合计:</b></td><td id='ttl_num'></td><td id='ttl_sum'></td></tr>
    </tfoot></tbody>
    </table>
    <div class="message info"><h6>双击"供应商姓名"可以添加供应商,&nbsp;双击"产品名称"可以添加产品</h6>
                                        </div>
                                    </section>

                                    <footer class="clearfix">
                                        <button type="button" class="next fr" >下一步 &raquo;</button>
                                    </footer>

                                </section>

                                <!-- page2 -->
                                <section>

                                    <header>
                                        <h2>
                                            <strong>步骤二: </strong> 请输入该车货物费用 <b></b>
                                            <em>没有费用可直接按下一步</em>
                                        </h2>
                                    </header>

                                    <section>
                                       <ul class="clearfix">
                                         <!-- address -->
                                            <li>
                                                <label>
                             
                                                    <input type="hidden" name="stafflist" value="<?php echo ($stafflist); ?>" />
                                                   
                                                </label>
                                            </li>
                                       
                                       </ul>
                                        <table class="bordered" id="tb_fee" style="width:100%;font-size:12px"> 
      <tbody>
    <thead> 
    <tr>
        <th>#</th> 
    	<th>费用名称</th>
    	<th>金额</th> 
    	<th>垫付员工</th> 
    	<th>备注</th> 
 
    </tr>
    </thead>
    <tfoot>
    <tr><td colspan=2><b>费用合计:</b></td><td colspan=3  id='ttl_fee' style="padding-right:450px" ></td></tr>
    </tfoot></tbody>
    </table> 
                                    </section>

                                    <footer class="clearfix">
                                        <button type="button" class="prev fl">&laquo; 上一步</button>
                                        <button type="button" class="fr" id='sub_button'  onclick='sub_form()'>下一步 &raquo;</button>
                                        
                                    </footer>

                                </section>

                                <!-- page3 -->
                                <section>

                                    <header>
                                        <h2>
                                            <strong>步骤三: </strong> 已保存该车进货信息!
                                            <em></em>
                                        </h2>
                                    </header>

                                    <section>
                                        <h3 id='information'>&nbsp;</h3>
                                    </section>

                                    <footer class="clearfix">
                                        <button type="button" class="fl" onclick="neworder();">新建进货单</button>
                                        <button type="button" class="fr" onclick="precords();">查看历史进货记录</button>
                                    </footer>

                                </section>


                            </div><!--items-->

                        </form><!--wizard-->

                    </div>
                </div>
<div id="result" ></div>
<!-- 添加供应商 -->
<div id="add_supply" title="添加供应商" style="display:none">
 <form action="/ihanc/eva/index.php/Home/Purchase/supplyUpdate" method="post" id="add_supply_dialog_form">
    <fieldset>
      <label for="name" style="font-size:13px">供应商名称:</label>
      <input type="text" name="sname" style="margin-bottom:10px; width:95%; padding: .4em;" onblur="make_py()">
      <label for="ssn" style="font-size:13px">供应商编码:</label>
      <input type="text" name="ssn" style="margin-bottom:10px; width:95%; padding: .4em;">
    </fieldset>
  </form>
</div>
<!-- 添加商品 -->
<div id="add_goods" title="添加商品" style="display:none">
    <fieldset>
      <label for="name" style="font-size:13px">商品名称:</label>
      <input type="text" name="gname" style="margin-bottom:10px; width:95%; padding: .4em;" onblur="make_goodspy()">
      <label for="ssn" style="font-size:13px">商品编码:</label>
      <input type="text" name="gsn" style="margin-bottom:10px; width:95%; padding: .4em;">
      <label for="ssn" style="font-size:13px">商品类别:</label>
      <select name="idcat" >
      <?php if(is_array($cat_list)): $i = 0; $__LIST__ = $cat_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value=<?php echo ($vo["idcat"]); ?>><?php echo ($vo["cat_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
      </select>
    </fieldset>
</div>
    <!-- WIZARD SETUP -->
    <script type="text/javascript" src="/ihanc/eva/Application/Home/View/public/js/jquery.wizard.js"></script>
    <script>
        $(function(){
            $('.wizard').wizard();
        });
        function neworder(){
        	$('#main-content').html('');
        	link=$('#path').val();
	    	link=link+h.replace(/^\#/, "");
	        id = link.replace(/[\/\.]/,"-");
	        $('#'+id).length && $('#'+id).remove();
	        $.ajax(link, {
	        	type: "POST",
	            dataType: "html",
	            cache: false,
	            success: function(data, textStatus, jqXHR) {
	                return pageDownloaded(data, id);
	            },
	            complete: function(jqXHR, textStatus) {
	            }
	        });
        }
        function precords(){
            $('#purchase_index_menu').click();
            location.hash='#purchase/index.html';
        }
        function sub_form(){
        	var check=true;
        	var indata=new Array();
        	$('#tb input[name="sum"]').each(function(i, o){
        		 if($.trim($(o).val())){
        		   var tr=$(o).parents('tr').attr('id');
        		   var idsupply=$('#'+tr+" input[name='idsupply']").val();
        		   if(idsupply==''){alert("请输入供应商!");check=false;return false;}
        		   var idgoods=$('#'+tr+" input[name='idgoods']").val();
        		   if(idgoods==''){alert("请输入产品!");check=false;return false;}
        		   var data={
   		    			'idsupply':idsupply,
   		    			'idgoods':idgoods,
   		    			'price':$('#'+tr+" input[name='price']").val(),
   		    			'number':$('#'+tr+" input[name='number']").val(),
   		    			'sum':$('#'+tr+" input[name='sum']").val(),
   		    	  };	    	
   		    	   indata.push(data);
        		 }
        	});
        	if(check==false){
        		$('button.prev').click();
        		return;
        	}
        	if(indata.length==0){
        		$('button.prev').click();
        		return;
        	}
        	var strHtml="<div id='dingwei'><img src='/ihanc/eva/Application/Home/View/public/images/ajax-loader-trans.gif'>&nbsp;&nbsp;保存中...</div>";
        	$('body').append(strHtml);
        	$('#sub_button').attr('disabled',false); 
        	//return;
        	var freight=new Array();
        	$('#tb_fee input[name="freight"]').each(function(i, o){
       		 if($.trim($(o).val())){
       		   var subject=$(o).parents('tr').find("select[name='subject']").val();
       		   var staff=$(o).parents('tr').find("select[name='idstaff']").val();
       		   var remark=$(o).parents('tr').find("input[name='remark']").val();
       		   var data={
  		    			'subject':subject,
  		    			'idstaff':staff,
  		    			'remark':remark,
  		    			'freight':parseInt($(o).val()),
  		    	  };	    	
  		    	   freight.push(data);
       		 }
       	});
        	
        	var inorder="<?php echo ($order); ?>";
        	$.post("/ihanc/eva/index.php/Home/Purchase/purchaseAdd",
       			 {'data':indata,
       		 	  'inorder':inorder,
       		 	  'freight':freight,
       		     },function(result){
       		    	$('button.next').click();
       		    	$('#dingwei').remove();
       		    	$('#information').text(result);
       		    	$('#sub_button').removeAttr("disabled");
       		  });
        }
       
        function addsupply(){
        	$('#add_supply input[name="sname"]').val('');
        	$('#add_supply input[name="ssn"]').val('');
        	$("#add_supply").dialog({
      	      height: 280,
      	      width: 300,
      	      modal: true,
      	      buttons: {
      	        "添加": add_s,
      	        "取消": function() {
      	        $(this).dialog( "close" );
      	        }
      	      },
      	    });
        }
        function add_s(){
        	var sname=$('#add_supply input[name="sname"]').val();
        	var ssn=$('#add_supply input[name="ssn"]').val();
        	if(sname&&ssn){
        		$.post("/ihanc/eva/index.php/Home/Purchase/supplyUpdate",{'sname':sname,'ssn':ssn},function(data){
        			var obj=$.parseJSON(data);
        			mynotice(obj);
        			$("#add_supply").dialog("close");
        		});
        	}else{
        		alert("请输入供应商信息!");
        	}
        }
        //生拼音编码
        function make_py(){
        	var str = $('#add_supply input[name="sname"]').val();
        	var py=makePy(str);
        	$('#add_supply input[name="ssn"]').val(py);
        }
        //添加产品
        function addgoods(){
        	$('#add_goods input[name="gname"]').val('');
        	$('#add_goods input[name="gsn"]').val('');
        	$("#add_goods").dialog({
      	      height: 300,
      	      width: 300,
      	      modal: true,
      	      buttons: {
      	        "添加": add_g,
      	        "取消": function() {
      	        $(this).dialog( "close" );
      	        }
      	      },
      	    });
        }
        
        function add_g(){
        	var gname=$('#add_goods input[name="gname"]').val();
        	var gsn=$('#add_goods input[name="gsn"]').val();
        	var idcat=$('#add_goods select[name="idcat"]').val();
        	
        	if(gname&&gsn){
        		$.post("/ihanc/eva/index.php/Home/setting/goodsUpdate",{'gname':gname,'gsn':gsn,'idcat':idcat},function(data){
        			//alert(data);
        			var obj=$.parseJSON(data);
        			mynotice(obj);
        			$("#add_goods").dialog("close");
        		});
        	}else{
        		alert("请输入商品信息!");
        	}
        }
        //生拼音编码
        function make_goodspy(){
        	var str = $('#add_goods input[name="gname"]').val();
        	var py=makePy(str);
        	$('#add_goods input[name="gsn"]').val(py);
        }
        function focNext(){
        	if (event.keyCode == 13) window.event.keyCode=9;
        	//$(obj).parent().next().find("input").focus();
        }
    </script>
    <!-- WIZARD SETUP END -->

</body>
</html>