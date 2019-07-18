<?php if (!defined('THINK_PATH')) exit();?><header>
 <h2>支出科目一览表</h2> 
 </header>
  <section>
    <table class="full"> 
      <tbody>
        <tr>
         <?php if(is_array($sub_list_1)): $i = 0; $__LIST__ = $sub_list_1;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 5 );++$i; if(($mod) == "0"): ?></tr><tr><?php endif; ?>
			<td><font size="2" color="#899AAA"><a onclick="sub_name(<?php echo ($vo["idsubject"]); ?>)"  id=<?php echo ($vo["idsubject"]); ?>><?php echo ($vo["subject"]); ?></a></font></td><?php endforeach; endif; else: echo "" ;endif; ?>
				</tr>
                 </tbody> 
                 </table> 
            </section>
<header>
 <h2>收入科目一览表</h2> 
 </header>
  <section>
    <table class="full"> 
      <tbody>
        <tr>
         <?php if(is_array($sub_list_2)): $i = 0; $__LIST__ = $sub_list_2;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 5 );++$i; if(($mod) == "0"): ?></tr><tr><?php endif; ?>
			<td><font size="2" color="#899AAA"><a onclick="sub_name(<?php echo ($vo["idsubject"]); ?>)"  id=<?php echo ($vo["idsubject"]); ?>><?php echo ($vo["subject"]); ?></a></font></td><?php endforeach; endif; else: echo "" ;endif; ?>
				</tr>
                 </tbody> 
                 </table> 
            </section>