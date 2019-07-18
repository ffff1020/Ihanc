<?php if (!defined('THINK_PATH')) exit();?><link rel="stylesheet" href="/ihanc/eva/Application/Home/View/public/css/reset.css" media="screen" />
<link rel="stylesheet" href="/ihanc/eva/Application/Home/View/public/css/mytable.css"/>
<table class="bordered" style="width:40%;font-size:12px"  >
    <thead> 
    <tr>
    <td colspan='7'><h4><?php echo ($info["sname"]); ?>:<?php echo ($info["remark"]); ?>结转明细</h4>&nbsp;<?php echo (substr($info["time"],0,10)); ?></td>
    </tr>
    <tr>
        <th>#</th> 
        <th>日期</th> 
    	<th>商品名称</th>
    	<th>价格</th>
    	<th>数量</th> 
    	<th>金额</th> 
    </tr>
    </thead>
    <?php if(is_array($plist)): $i = 0; $__LIST__ = $plist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
    <td><?php echo ($i); ?></td>
    <td><?php echo (substr($vo["time"],0,10)); ?></td>
    <td><?php echo ($vo["gname"]); ?></td>
    <td><?php echo ($vo["price"]); ?></td>
    <td><?php echo ($vo["number"]); ?></td>
    <td>￥<?php echo (number_format($vo["sum"])); ?></td>
    </tr><?php endforeach; endif; else: echo "" ;endif; ?>
    <tfoot>
    <tr>
    <td colspan='5'>金额合计:</td>
    <td>￥<?php echo (number_format($ttl_sum)); ?></td>
    </tr>
    </tfoot>
    </table>