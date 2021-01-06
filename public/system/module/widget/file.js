layui.use(['layer','upload'], function() {
    var upload = layui.upload;
    var layer = layui.layer;
    //图片上传
    upload.render({
        elem: '#upload',
        url: uploadurl,
        multiple: true,
        size: 10000000, //限制文件大小，单位 KB
        done: function(res){
            console.log(res);
            layer.msg(res.msg,{time:1000});
            setTimeout(function () {
                window.location.reload();
            }, 1000);

        }
    });
});
//非组件修改样式
if(!parent.$f){
    $('.main-top').hide();
    $('.main').css('margin','0px');
    $('.foot-tool').css('bottom','20px');
}