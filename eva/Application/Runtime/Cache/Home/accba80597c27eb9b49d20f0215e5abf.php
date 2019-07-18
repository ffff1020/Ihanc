<?php if (!defined('THINK_PATH')) exit();?>
<header>
 <h2>种类一览表</h2> 
 </header>
  <section>
    <table class="full"> 
      <tbody>
        <tr>
         <?php if(is_array($cat_list)): $i = 0; $__LIST__ = $cat_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 5 );++$i; if(($mod) == "0"): ?></tr><tr><?php endif; ?>
			<td><font size="2" color="#899AAA"><a onclick="cat_name(<?php echo ($vo["idcat"]); ?>)"  id=<?php echo ($vo["idcat"]); ?>><?php echo ($vo["cat_name"]); ?></a></font></td><?php endforeach; endif; else: echo "" ;endif; ?>
				</tr>
                 </tbody> 
                 </table> 
            </section>