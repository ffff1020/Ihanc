<?php if (!defined('THINK_PATH')) exit();?> 
<header>
 <h2>员工一览表</h2>
 </header>
  <section>
    <table class="full"> 
      <tbody>
        <tr>
         <?php if(is_array($staff_list)): $i = 0; $__LIST__ = $staff_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 5 );++$i; if(($mod) == "0"): ?></tr><tr><?php endif; ?>
			<td><font size="2" color="#899AAA"><a onclick="staff_name(<?php echo ($vo["idstaff"]); ?>)"  id=<?php echo ($vo["idstaff"]); ?>><?php echo ($vo["staff_name"]); ?></a></font></td><?php endforeach; endif; else: echo "" ;endif; ?>
				</tr>
                 </tbody> 
                 </table> 
            </section>