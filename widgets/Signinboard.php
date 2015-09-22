<?php
    setcookie('_auth',session_id());
?>
<div class="panel panel-primary" style="width:600px;margin:160px auto 0 auto">
    <div class="panel-heading">
        <h3 class="panel-title">用户登录</h3>
    </div>

    <div class="panel-body">
        <input type="hidden" id="ref" name="ref">
        <!--用户名-->
        <div class="input-group input-group-lg">
            <span class="input-group-addon"><span class="glyphicon glyphicon-user"></span></span>
            <input type="text" id="username" name='username' class="form-control" placeholder="请填写帐号">
        </div>
        <br>
        <!--密码框-->
        <div class="input-group input-group-lg">
            <span class="input-group-addon"><span class="glyphicon glyphicon-eye-open"></span></span>
            <input type="password" id="password" name="password" class="form-control" placeholder="请填写密码">
        </div>
        <button id="loginbtn" type="submit" class="btn btn-default" style="margin: 20px auto;min-width: 150px;float: right;">登录</button>
        <script>
            var ref = window.location.pathname;
            document.getElementById('ref').value=ref;
            document.getElementById('loginbtn').onclick = function () {
                var self = this;
                self.innerHTML='登陆中...';
                self.disabled='disabled';
                var timer = setTimeout(function(){self.value='登录失败，重新登录';self.removeAttribute('disabled');},15000);
                var username=document.getElementById('username').value;
                var password=document.getElementById('password').value;
                $.ajax({
                    url:'./Data.php',
                    complete:function(data){
                        var rep = eval("("+data.responseText+")");
                        if(rep.stat==200||rep.stat==205){
                            alert(rep.msg);
                            clearInterval(timer);
                            window.location.pathname=ref;
                        }else if(rep.stat==201){
                            self.innerHTML='帐号或密码错误，请重试';
                            self.removeAttribute('disabled');
                        }
                    },
                    data:{
                        id:'userLogin',
                        username:username,
                        password:password
                    },
                    error: function () {
                        alert('登录出错');
                        clearInterval(timer);
                        self.removeAttribute('disabled');
                    }
                })
            }
        </script>
    </div>
</div>