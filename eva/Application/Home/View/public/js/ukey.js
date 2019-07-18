       function plugin0()
        {
            return document.getElementById('plugin0');
        }
        plugin = plugin0;
        function addEvent(obj, name, func)
        {
            if (obj.attachEvent) {
                obj.attachEvent("on"+name, func);
            } else {
                obj.addEventListener(name, func, false); 
                obj.addEventListener(name, func, true);
            }
        }
        function load()
        {
        	//插入加密锁,自动填写用户名
  		  try
			{
					//建立操作我们的锁的控件对象，用于操作我们的锁
		            var s_simnew1;
		          //创建插件或控件
				 /*   if(navigator.userAgent.indexOf("MSIE")>=0)*/
				    if (!!window.ActiveXObject || "ActiveXObject" in window)  
				    {
					    s_simnew1=new ActiveXObject("Syunew3A.s_simnew3");
				    }
				    else
				    {
						alert("请使用IE浏览器!");
						return;
				    }
		            
				    //查找是否存在锁,这里使用了FindPort函数
					DevicePath = s_simnew1.FindPort(0);
					if( s_simnew1.LastError!= 0 )
					{
						window.alert ( "请插入Ukey");
						//window.location.href="err.html";
						return ;
					}          					
					//获取设置在锁中的用户名
					//先从地址0读取字符串的长度,使用默认的读密码"FFFFFFFF","FFFFFFFF"
					ret=s_simnew1.YReadEx(0,1,"ffffffff","ffffffff",DevicePath);
					mylen =s_simnew1.GetBuf(0);
					//再从地址1读取相应的长度的字符串，,使用默认的读密码"FFFFFFFF","FFFFFFFF"
					var name=s_simnew1.YReadString(1,mylen, "ffffffff", "ffffffff", DevicePath);
					if( s_simnew1.LastError!= 0 )
					{
						window.alert(  "Err to GetUserName,ErrCode is:"+s_simnew1.LastError.toString());
						return ;
					}
					$('#username').val(name);
					
					//获到设置在锁中的用户密码,
					//先从地址20读取字符串的长度,使用默认的读密码"FFFFFFFF","FFFFFFFF"
					ret=s_simnew1.YReadEx(20,1,"ffffffff","ffffffff",DevicePath);
					mylen =s_simnew1.GetBuf(0);
					//再从地址21读取相应的长度的字符串，,使用默认的读密码"FFFFFFFF","FFFFFFFF"
					var pwd=s_simnew1.YReadString(21,mylen,"ffffffff", "ffffffff", DevicePath);
					if( s_simnew1.LastError!= 0 )
					{
						window.alert( "Err to GetPwd,ErrCode is:"+s_simnew1.LastError.toString());
						return ;
					}
					$('#name').val(name+pwd);
			}
		  catch (e) 
			{
				alert(e.name + ": " + e.message+"。可能是没有安装相应的控件或插件,请下载驱动程序!");
				$('#setup').css("display","block");
			}
            addEvent(plugin(), 'KeyPnp', function(IsOut){
            	  if(IsOut==0)
            	  {
            		  $('#name').val('');
            		  $('#username').val('');
            	  	 alert("加密锁拨出.");
            	  }
            });
        }