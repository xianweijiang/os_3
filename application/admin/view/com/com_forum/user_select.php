{extend name="public/container"}
{block name="head_top"}
<link rel="stylesheet" href="{__PLUG_PATH}formselects/formSelects-v4.css">
<script src="{__PLUG_PATH}formselects/formSelects-v4.min.js"></script>
<script src="{__PLUG_PATH}sweetalert2/sweetalert2.all.min.js"></script>
<style>
    .layui-form-radio{
        margin-top: 0;
    }
    .checkbox-content{
        margin-bottom: 10px;
    }
    .checkbox-box{
        display: flex;
        align-items: center
    }
    .checkbox-box label{
        display: flex;
        align-items: center;
        margin-bottom: 0;
    }
    .checkbox-box label input{
        margin-top: 0;
        margin-right: 3px;
    }
    .open-text{
        color: #0ca6f2;
        cursor: pointer;
    }
    .second-box{
        padding-left: 20px;
        margin-top: 5px;
        display: none;
    }
    .second-box .checkbox-box{
        margin-bottom: 5px;
    }
</style>
{/block}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-content" style="border-radius: 0">
                {volist name='$group' id='vo'}
                    <div class="checkbox-content">
                        <div class="checkbox-box">
                            <label>
                                <input name="first" type="checkbox" value="{$vo.name}" data-name="{$vo['name']}" />{$vo.name}
                            </label>
                            <span class="open-text">[+]</span>
                        </div>
                        <div class="second-box">
                        {volist name='$vo.data' id='v'}
                            <div class="checkbox-box">
                                <label>
                                    <input name="second" type="checkbox" value="{$v.id}" data-name="{$v['name']}" {if condition="in_array($v.id,$group_id)"} data-role="is_check"  checked{/if}/>{$v.name}
                                </label>
                            </div>
                        {/volist}
                        </div>
                    </div>
                {/volist}
                <button style="margin-top: 20px;" class="btn btn-primary" id="save" type="button">确定</button>
            </div>
        </div>
    </div>
</div>
{/block}
{block name="script"}
<script>
    $(function () {
        var name="<?php echo $name?>";
        var name_id=name+"_id";
//        var userData = ['管理员','版主','超级版主'];
//        var idData = ['2','3','4'];
        var check=$('[data-role="is_check"]');
        var userData = [];
        var idData = [];
        for(var i=0;i<check.length;i++){
            userData.push(check.eq(i).attr('data-name'));
            idData.push(check.eq(i).attr('data-id'));
        }
        $(".open-text").on("click",function () {
            var text = $(this).text();
            if(text === "[+]"){
                $(this).text("[-]");
                $(this).parent().next().show();
            }else if(text === "[-]"){
                $(this).text("[+]");
                $(this).parent().next().hide();
            }
        });
        //监控checkbox代码
        $("input[name='first']").on("change", function () {
            var change = $(this).is(':checked'); //checkbox选中判断
            if (change) {
                $(this).parent().parent().next().find('input').prop("checked",true);
                $(this).parent().parent().next().find('input').each(function (k,ele) {
                    var index = $.inArray($(ele).data("name"), userData);
                    if(index < 0){
                        userData.push($(ele).data("name"));
                        idData.push($(ele).val());
                    }
                })
            } else {
                $(this).parent().parent().next().find('input').prop("checked",false);
                $(this).parent().parent().next().find('input').each(function (k,ele) {
                    var index1 = $.inArray($(ele).data("name"),userData);
                    var index2 = $.inArray($(ele).val(),idData);
                    userData.splice(index1,1);
                    idData.splice(index2,1);
                    console.log(idData)
                })
            }

        });
        $("input[name='second']").on("change", function () {
            var change = $(this).is(':checked'); //checkbox选中判断
            if (change) {
                userData.push($(this).data("name"));
                idData.push($(this).val());
            } else {
                var index1 = $.inArray($(this).data("name"),userData);
                var index2 = $.inArray($(this).val(),idData);
                userData.splice(index1,1);
                idData.splice(index2,1);
            }
            console.log(userData);
        });
        $("#save").on("click",function () {
            window.localStorage.setItem(name,userData);
            window.localStorage.setItem(name_id,idData);
            parent.layer.close(parent.layer.getFrameIndex(window.name));
        })
    });

</script>
{/block}
