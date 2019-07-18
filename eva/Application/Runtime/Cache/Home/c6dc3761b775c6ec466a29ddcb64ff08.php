<?php if (!defined('THINK_PATH')) exit(); if($info == 1): ?><h3>有特殊需要,请联系管理员</h3>
<script>
alert("已完成系统初期数据录入,不能操作!");

</script>
<?php else: ?>

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
	 for (var l = 0; l < 5; l++) { 
	    	var timestamp=new Date().getTime();
	    	var trHTML = "<tr id="+timestamp+"><td  width=8%><a><img src='/eva/Application/Home/View/public/images/plus.png' alt='添加行' onclick='addNew();'/></a>&nbsp;&nbsp;<a><img src='/eva/Application/Home/View/public/images/dd.png' alt='删除行' onclick='del(this);'/></td><td><input type='text' name='sname' onkeyup='getsupply(this)' onkeydown='selectSupply(this)';/><input type='hidden' name='idsupply'/></td><td><input type='text'  name='sum' onblur='cal(this)'  /></td></tr>";
		   $("#tb").append(trHTML);
		    var str_fee="<tr id=m"+timestamp+"><td  width=10%><a><img src='/eva/Application/Home/View/public/images/plus.png' alt='添加行' onclick='addNew_fee();'/></a>&nbsp;&nbsp;<a><img src='/eva/Application/Home/View/public/images/dd.png' alt='删除行' onclick='del_fee(this);'/></td><td><input type='text' name='mname'  onkeyup='getmember(this)' onkeydown='selectmember(this)'><input type='hidden' name='idmember'/></td><td><input type='text'  name='msum' onblur='cal_m(this)'  /></td></tr>";
			$('#tb_fee').append(str_fee);
			var stock="<tr id=s"+timestamp+"><td  width=10%><a><img src='/eva/Application/Home/View/public/images/plus.png' alt='添加行' onclick='addstock();'/></a>&nbsp;&nbsp;<a><img src='/eva/Application/Home/View/public/images/dd.png' alt='删除行' onclick='delstock(this);'/></td><td><input type='text' name='gname'  onkeyup='getgoods(this)' onkeydown='selectgoods(this)'><input type='hidden' name='idgoods'/></td><td><input type='text' name='price' onblur=calsum(this) /></td><td><input type='text' name='number' onblur=calsum(this) /></td><td><input type='text'  name='gsum' readonly tabIndex='-1' ></input></td></tr>";
			$('#stock').append(stock);
	    }
});

function getsupply(obj){
	 var tx=$(obj); 
	// var index=0;
	 var key = event.keyCode; 
	 var lf=tx.offset().left;
     var tp=tx.offset().top+30;
	 if ($.trim(tx.val()).length == 0){
   	        $('#result').hide();
   	        var id=tx.parents("tr").attr('id');
	        $('#'+id).find("input[name='idsupply']").val('');
            return; }
	 if(((key>47)&&(key<106))||(key==8)||(key==32)){
	   $.post('/eva/index.php/home/purchase/searchSupply',{'key':tx.val()},function(data){
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
			$('#result').hide();
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
  	 var idsupply= $("#result li:eq(" + index + ") a").attr('title');
  	    tx.val($("#result li:eq(" + index + ") a").text()); 
  	    tx.parent().find("input[name='idsupply']").val(idsupply);
  	    //alert(tx.parent().find("input[name='idsupply']").val());
  	  $('#result').hide();
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
	$('#result').hide();
}

function getmember(obj){
	 var tx=$(obj); 
	// var index=0;
	 var key = event.keyCode; 
	 var lf=tx.offset().left;
     var tp=tx.offset().top+30;
	 if ($.trim(tx.val()).length == 0){
   	        $('#result').hide();
   	        var id=tx.parents("tr").attr('id');
   	        $('#'+id).find("input[name='idmember']").val('');
            return; }
	 if(((key>47)&&(key<106))||(key==8)||(key==32)){
	   $.post('/eva/index.php/Home/sale/searchMember',{'key':tx.val()},function(data){
		 var obj=$.parseJSON(data);  
		 if(data) {
			var trs = "<ul>";  
			len=obj.mname.length;
  		    for (var i = 0; i < len; i++) {
  		     var value=obj.mname[i]+"+"+obj.idmember[i];
  			 trs+='<li style="list-style-type:none;"><a href="javascript: fillmember(\''+tx.parents("tr").attr("id")+'\''+',\''+value+'\')" title='+obj.idmember[i]+'>'+obj.mname[i]+'</a></li>';    
  	        }; 
      	$('#result').css({'width':tx.width()-10});
	    $('#result').html(trs).css('display','block');
	    $('#result').show().offset({top:tp,left:lf}); 
		}else{
			$('#result').hide();
			tx.val('');
		}
		 li = $("#result").find("li:eq(" + index + ")");
	     li.css("background", "#D8D8D8").siblings().css("background", "#fff"); 
		});
	 }
};
function selectmember(obj){
	var tx=$(obj); 
	var key = event.keyCode;  
    if (key == 13){
  	 var idmember= $("#result li:eq(" + index + ") a").attr('title');
  	    tx.val($("#result li:eq(" + index + ") a").text()); 
  	    tx.parent().find("input[name='idmember']").val(idmember);
  	   // alert(tx.parent().find("input[name='idgoods']").val());
  	    $('#result').hide();
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

function fillmember(id,value){
	//alert(id);return;
	 var arr=value.split("+");
	$('#'+id).find("input[name='mname']").val(arr[0]);
	$('#'+id).find("input[name='idmember']").val(arr[1]);
	$('#result').hide();
}

function addNew(){
	var timestamp=new Date().getTime();
	var trHTML = "<tr id="+timestamp+"><td  width=8%><a><img src='/eva/Application/Home/View/public/images/plus.png' alt='添加行' onclick='addNew();'/></a>&nbsp;&nbsp;<a><img src='/eva/Application/Home/View/public/images/dd.png' alt='删除行' onclick='del(this);'/></td><td><input type='text' name='sname' onkeyup='getsupply(this)' onkeydown='selectSupply(this)';/><input type='hidden' name='idsupply'/></td><td><input type='text'  name='sum'  tabIndex='-1' onblur='cal(this)'></input></td></tr>";
	 $("#tb").append(trHTML);
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
   	    $('input[name="sum"]').each(function(i, o){
   		    var temp=$.trim($(o).val());	
   		    if(temp>0){
    		    ttl = ttl+parseInt(temp); 
    		  }
   		});
   	 $('#ttl_sum').text("￥"+ttl+"元");
 	
	}
}
function cal(obj){
	var tx=$(obj);
	if(isNaN(tx.val())){
		tx.val('');
		tx.parents("tr").find("input[name='sum']").val('');
		return;
	}
		var ttl=0;
   	    $('input[name="sum"]').each(function(i, o){
   		    var temp=$.trim($(o).val());	
   		    if(temp>0){
   		    ttl = ttl+parseInt(temp); 
   		    }
   		});
   	$('#ttl_sum').text("￥"+ttl+"元");
}

//table_fee中的function
function addNew_fee(){
	var timestamp=new Date().getTime();
    var str_fee="<tr id=m"+timestamp+"><td  width=10%><a><img src='/eva/Application/Home/View/public/images/plus.png' alt='添加行' onclick='addNew_fee();'/></a>&nbsp;&nbsp;<a><img src='/eva/Application/Home/View/public/images/dd.png' alt='删除行' onclick='del_fee(this);'/></td><td><input type='text' name='mname'  onkeyup='getmember(this)' onkeydown='selectmember(this)'><input type='hidden' name='idmember'/></td><td><input type='text'  name='msum' onblur='cal_m(this)'  /></td></tr>";
	$('#tb_fee').append(str_fee);
    var height=$('form').css('height');
    height=parseInt(height)+51;
    $('form').css('height',height);
    
}
function del_fee(tdobject){
	//alert($("#tb_fee tr").length);
	var td=$(tdobject);  
	var tr=td.parents("tr");
	var sum=td.val();
	if($("#tb_fee tr").length>3){
		tr.remove();
		 var height=$('form').css('height');
		    height=parseInt(height)-51;
		    //alert(height);
		    $('form').css('height',height);
	}
	if(sum){
		var ttl=0;
   	    $('input[name="msum"]').each(function(i, o){
   		    var temp=$.trim($(o).val());	
   		    if(temp>0){
    		    ttl = ttl+parseInt(temp); 
    		  }
   		});
   	 $('#ttl_fee').text("￥"+ttl+"元");
	}
}

function cal_m(obj){
	var tx=$(obj);
	if(isNaN(tx.val())){
		tx.val('');
		return;
	}
		var ttl=0;
   	    $('input[name="msum"]').each(function(i, o){
   		    var temp=$.trim($(o).val());	
   		    if(temp>0){
   		    ttl = ttl+parseInt(temp); 
   		    }
   		});
   	 $('#ttl_fee').text("￥"+ttl+"元");
}
</script>  
  
                <h1 class="page-title">系统初期数据录入</h1>
                <div class="container_12 clearfix leading">
                    <div class="grid_12">
                        <!-- wizard -->
                        <form  class="wizard">
                            <nav>
                                <ul class="clearfix">
                                    <li class="active"><strong>1.</strong> 供应商应付金额录入</li>
                                    <li><strong>2.</strong> 客户应收金额录入</li>
                                    <li><strong>3.</strong> 系统库存录入</li>
                                </ul>
                            </nav>
                            <div class="items" >
                                <!-- page1 -->
                                <section>
                                    <header>
                                        <h2>
                                            <strong> 步骤一: </strong> 供应商应付金额录入
                                        </h2>
                                    </header>

                                    <section >
                                    
                                         <table class="bordered" id="tb" style="width:100%;font-size:12px"> 
      <tbody>
    <thead> 
    <tr>
        <th>#</th> 
    	<th ondblclick='addsupply()'>供应商姓名</th> 
    	<th>金额</th> 
    </tr>
    </thead>
    <tfoot>
    <tr><td colspan=2 style="padding-left:500px"><b>合计:</b></td><td id='ttl_sum'></td></tr>
    </tfoot></tbody>
    </table>
    <div class="message info"><h6>双击"供应商姓名"可以添加供应商,</h6>
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
                                            <strong>步骤二: </strong> 客户应收金额录入 <b></b>
                                       
                                        </h2>
                                    </header>

                                    <section>
                                       <ul class="clearfix">
                                         <!-- address -->
                                            <li>
                                                <label>
                             
                                                  
                                                   
                                                </label>
                                            </li>
                                       
                                       </ul>
                                        <table class="bordered" id="tb_fee" style="width:100%;font-size:12px"> 
      <tbody>
    <thead> 
    <tr>
        <th>#</th> 
    	<th onclick="addmember()">客户姓名</th>
    	<th>金额</th> 
    	
 
    </tr>
    </thead>
    <tfoot>
    <tr><td colspan=2><b>应收合计:</b></td><td   id='ttl_fee' style="padding-right:450px" ></td></tr>
    </tfoot></tbody>
    </table> 
                                    </section>

                                    <footer class="clearfix">
                                        <button type="button" class="prev fl">&laquo; 上一步</button>
                                        <button type="button" class="next fr"  >下一步 &raquo;</button>               
                                    </footer>
                                </section>
                                <!-- page3 -->
                                <section>

                                    <header>
                                        <h2>
                                            <strong>步骤三: </strong>库存录入
                                            <em></em>
                                        </h2>
                                    </header>

                                    <section>
                                       <ul class="clearfix">
                                         <!-- address -->
                                            <li>
                                                <label>
           
                                                </label>
                                            </li>
                                       
                                       </ul>
                                        <table class="bordered" id="stock" style="width:100%;font-size:12px"> 
      <tbody>
    <thead> 
    <tr>
        <th>#</th> 
    	<th onclick="addgoods()">商品名称</th>
    	<th>价格</th> 
    	<th>数量</th> 
    	<th>金额</th> 
    	
 
    </tr>
    </thead>
    <tfoot>
    <tr><td colspan=3><b>库存合计:</b></td><td id='stock_num'  ></td><td id='stock_sum'  ></td></tr>
    </tfoot></tbody>
    </table> 
                                    </section>

                                    <footer class="clearfix">
                                        <button type="button" class="prev fl" >上一步</button>
                                        <button type="button" id="btn" class=" fr" onclick="add_data()">保存初期数据</button>
                                    </footer>

                                </section>


                            </div><!--items-->

                        </form><!--wizard-->

                    </div>
                </div>
<div id="result" ></div>
<!-- 添加供应商 -->
<div id="add_supply" title="添加供应商" style="display:none">
 <form  method="post" id="add_supply_dialog_form">
    <fieldset>
      <label for="name" style="font-size:13px">供应商名称:</label>
      <input type="text" name="sname" style="margin-bottom:10px; width:95%; padding: .4em;" onblur="make_py()">
      <label for="ssn" style="font-size:13px">供应商编码:</label>
      <input type="text" name="ssn" style="margin-bottom:10px; width:95%; padding: .4em;">
    </fieldset>
  </form>
</div>
<!-- 添加客户 -->
<div id="add_member" title="添加客户" style="display:none">
 <form  method="post" >
    <fieldset>
      <label for="name" style="font-size:13px">客户名称:</label>
      <input type="text" name="mname" style="margin-bottom:10px; width:95%; padding: .4em;" onblur="make_py_m()">
      <label for="ssn" style="font-size:13px">客户编码:</label>
      <input type="text" name="msn" style="margin-bottom:10px; width:95%; padding: .4em;">
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
    <script type="text/javascript" src="/eva/Application/Home/View/public/js/jquery.wizard.js"></script>
     
    <script>
        $(function(){
            $('.wizard').wizard();
        });
       
        function add_data(){
        	var check=true;
        	//alert("");
        	var scredit=new Array();
        	$('#tb input[name="sum"]').each(function(i, o){
        		 if($.trim($(o).val())){
        		   var tr=$(o).parents('tr').attr('id');
        		   var idsupply=$('#'+tr+" input[name='idsupply']").val();
        		   if(idsupply==''){alert("请输入供应商!");check=false;return;}
        		   var data={
   		    			'idsupply':idsupply,
   		    			'sum':$(o).val(),
   		    	  };	    	
   		    	   scredit.push(data);
        		 }
        	});
 
        	var mcredit=new Array();
        	$('#tb_fee input[name="msum"]').each(function(i, o){
       		 if($.trim($(o).val())){
       			 var tr=$(o).parents('tr').attr('id');
       			 var idmember=$('#'+tr+" input[name='idmember']").val();
       			 if(idmember==''){alert("请输入客户!");check=false;return;}
       		   var data={
       				     'idmember':idmember,
		    			 'sum':$(o).val(),
  		    	  };	    	
       		    mcredit.push(data);
       		 }
       	});
        	var stock=new Array();
        	$('#stock input[name="gsum"]').each(function(i, o){
       		 if($.trim($(o).val())){
       			 var tr=$(o).parents('tr').attr('id');
       			 var idgoods=$('#'+tr+" input[name='idgoods']").val();
       			 if(idgoods==''){alert("请输入商品!");check=false;return;}
       		   var data={
       				     'idgoods':idgoods,
		    			 'sum':$(o).val(),
		    			 'price':$('#'+tr+" input[name='price']").val(),
       		   			 'number': $('#'+tr+" input[name='number']").val(),
  		    	  };	    	
       		    stock.push(data);
       		 }
       	});
       if(check){
        	$.post("/eva/index.php/home/setting/Add",
       			 {'scredit':scredit,
       		 	  'mcredit':mcredit,
       		 	  'stock':stock,
       		     },function(result){
       		    	 alert(result);
       		    	 neworder();
       		  });
        	}
        }
        function neworder(){
        	$('#main-content input').val('');
        	$('#ttl_fee').text('');
        	$('#ttl_sum').text('');
        	$('#stock_num').text('');
        	$('#stock_sum').text('');
        	if(window.confirm('你是否已经完成初期数据输入?确认后不能进行应收应付库存的初期录入')){
        		$.get("/eva/index.php/home/setting/complete",
              			 {'info':1},function(result){
              				// alert(result);return;
              				 if(result) {
              		    		alert("已完成初期数据录入");
              		    		 location.hash='#index/dashboard';
              		    		 $('#dashboard_menu"').click();
              		           
              		    		}else
              		    	alert("未确认完成初期数据录入");
              		  });
                return true;
             }
            	$('button.prev').click();
             	$('button.prev').click();
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
        		$.post("/eva/index.php/Home/purchase/supplyUpdate",{'sname':sname,'ssn':ssn},function(data){
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
        
        function addmember(){
        	$('#add_member input[name="mname"]').val('');
        	$('#add_member input[name="msn"]').val('');
        	$("#add_member").dialog({
      	      height: 280,
      	      width: 300,
      	      modal: true,
      	      buttons: {
      	        "添加": add_m,
      	        "取消": function() {
      	        $(this).dialog( "close" );
      	        }
      	      },
      	    });
        }
        function add_m(){
        	var mname=$('#add_member input[name="mname"]').val();
        	var msn=$('#add_member input[name="msn"]').val();
        	if(mname&&msn){
        		$.post("/eva/index.php/home/sale/memberUpdate",{'mname':mname,'msn':msn},function(data){
        			var obj=$.parseJSON(data);
        			mynotice(obj);
        			$("#add_member").dialog("close");
        		});
        	}else{
        		alert("请输入供应商信息!");
        	}
        }
        //生拼音编码
        function make_py_m(){
        	var str = $('#add_member input[name="mname"]').val();
        	var py=makePy(str);
        	$('#add_member input[name="msn"]').val(py);
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
        		$.post("/eva/index.php/Home/setting/goodsUpdate",{'gname':gname,'gsn':gsn,'idcat':idcat},function(data){
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
        
        function getgoods(obj){
       	 var tx=$(obj); 
       	// var index=0;
       	 var key = event.keyCode; 
       	 var lf=tx.offset().left;
            var tp=tx.offset().top+30;
       	 if ($.trim(tx.val()).length == 0){
          	        $('#result').hide();
          	        var id=tx.parents("tr").attr('id');
          	        $('#'+id).find("input[name='idgoods']").val('');
                   return; }
       	 if(((key>47)&&(key<106))||(key==8)||(key==32)){
       	   $.post('/eva/index.php/home/purchase/searchGoods',{'key':tx.val()},function(data){
       		 var obj=$.parseJSON(data);  
       		 if(data) {
       			var trs = "<ul>";  
       			len=obj.gname.length;
         		    for (var i = 0; i < len; i++) {
         		     var value=obj.gname[i]+"+"+obj.idgoods[i];
         			 trs+='<li style="list-style-type:none;"><a href="javascript: fillgoods(\''+tx.parents("tr").attr("id")+'\''+',\''+value+'\')" title='+obj.idgoods[i]+'>'+obj.gname[i]+'</a></li>';    
         	        }; 
             	$('#result').css({'width':tx.width()-10});
       	    $('#result').html(trs).css('display','block');
       	    $('#result').show().offset({top:tp,left:lf}); 
       		}else{
       			$('#result').hide();
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
         	 var idgoods= $("#result li:eq(" + index + ") a").attr('title');
         	    tx.val($("#result li:eq(" + index + ") a").text()); 
         	    tx.parent().find("input[name='idgoods']").val(idgoods);
         	   // alert(tx.parent().find("input[name='idgoods']").val());
         	    $('#result').hide();
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
       	$('#result').hide();
       }
       
       function calsum(obj){
    		var tx=$(obj);
    		if(isNaN(tx.val())){
    			tx.val('');
    			tx.parents("tr").find("input[name='gsum']").val('');
    			return;
    		}
    		var number=tx.parents("tr").find("input[name='number']").val();
    		var price=tx.parents("tr").find("input[name='price']").val();
    		var sum=Math.round(number*price);
    		tx.parents("tr").find("input[name='gsum']").val(sum);  
    		if(sum){
    			var ttl=0;
    			var num=0;
    	   	    $('input[name="gsum"]').each(function(i, o){
    	   		    var temp=$.trim($(o).val());	
    	   		    if(temp>0){
    	   		    ttl = ttl+parseInt(temp); 
    	   		    var temp_num=$(o).parents("tr").find("input[name='number']").val();
    			    num=num+parseInt(temp_num);
    	   		    }
    	   		});
    	   	$('#stock_sum').text("￥"+ttl+"元");
    	   	$('#stock_num').text(""+num+"斤");
    		}
    	}
       function addstock(){
    		var timestamp=new Date().getTime();
    		var stock="<tr id=s"+timestamp+"><td  width=10%><a><img src='/eva/Application/Home/View/public/images/plus.png' alt='添加行' onclick='addstock();'/></a>&nbsp;&nbsp;<a><img src='/eva/Application/Home/View/public/images/dd.png' alt='删除行' onclick='delstock(this);'/></td><td><input type='text' name='gname'  onkeyup='getgoods(this)' onkeydown='selectgoods(this)'><input type='hidden' name='idgoods'/></td><td><input type='text' name='price' onblur=calsum(this) /></td><td><input type='text' name='number' onblur=calsum(this) /></td><td><input type='text'  name='gsum' readonly tabIndex='-1' ></input></td></tr>";
			$('#stock').append(stock);
			 var height=$('form').css('height');
    	    height=parseInt(height)+51;
    	    //alert(height);
    	    $('form').css('height',height);
    	    
    	}
    	function delstock(tdobject){
    		var td=$(tdobject);  
    		var tr=td.parents("tr");
    		var sum=tr.find("input[name='gsum']").val();
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
    	   	    $('input[name="gsum"]').each(function(i, o){
    	   		    var temp=$.trim($(o).val());	
    	   		    if(temp>0){
    	    		    ttl = ttl+parseInt(temp); 
    	    		    var temp_num=$(o).parents("tr").find("input[name='number']").val();
    	 		        num=num+parseInt(temp_num);
    	    		  }
    	   		});
    	   	 $('#stock_sum').text("￥"+ttl+"元");
    	 	 $('#stock_num').text(""+num+"斤");
    		}
    	}

    </script>
    <!-- WIZARD SETUP END -->

</body>
</html><?php endif; ?>