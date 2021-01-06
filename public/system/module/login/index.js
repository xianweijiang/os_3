$(document).ready(function() {
    $('.login-bg').iosParallax({
        movementFactor: 50
    });

    var $err = $('#err');
    $err.hide();
    var showError = function(err){
        $err.html(err).show();
    };

    $('[data-role="login-button"]').click(function () {
        var account = $('#account').val(),pwd = $('#pwd').val();
        if(!account)return Toast.error('请输入用户名!');
        if(!pwd) return Toast.error('请输入密码',{skin: 'toast-error'});

        var account_data=encrypt($('#account').val());
        var pwd_data=encrypt($('#pwd').val());
        var verify_data=$('#verify').val();
        var url=$('#login_form').attr("action_url");
        $.post(url,{account:account_data,pwd:pwd_data,verify:verify_data} , success, "json");
        return false;

        function success(res){
            if(res.msg=='登录成功'&&res.code==200){
                window.location.href = res.data;
            } else {
                Toast.error(res.msg);
                //刷新验证码
                $('[name=verify]').val('');
                $("#verify_img").click();
            }
        }
    })
});
$(document).ready(function() {
    var tipValue
    $(".form-control").focus(function(){
        tipValue = $(this).attr("placeholder");
        $(this).siblings("span").css({"color":"#bd201f"})
        $(this).parent().siblings("p").css({"color":"#bd201f"});
        $(this).attr("placeholder","");
    });
    $(".form-control").blur(function(){
        $(this).siblings("span").css({"color":"#525252"})
        $(this).parent().siblings("p").css({"color":"#525252"});
        $(this).attr("placeholder",tipValue);
    });
});

(function captcha(){
    var $captcha = $('#verify_img'),src = $captcha[0].src;
    $captcha.on('click',function(){
        this.src = src+'?'+Date.parse(new Date());
    });
})();