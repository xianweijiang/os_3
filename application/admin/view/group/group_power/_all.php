<html lang="en"><head>
    <meta charset="UTF-8">
    <title>编辑广告</title>
    <script src="/public/static/plug/vue/dist/vue.min.js"></script>
    <link href="/public/static/plug/iview/dist/styles/iview.css" rel="stylesheet">
    <script src="/public/static/plug/iview/dist/iview.min.js"></script>
    <script src="/public/static/plug/jquery/jquery.min.js"></script>
    <script src="/public/static/plug/form-create/province_city.js"></script>
    <script src="/public/static/plug/form-create/form-create.min.js"></script>
    <style>
        /*弹框样式修改*/
        .ivu-modal-body{padding: 5;}
        .ivu-modal-confirm-footer{display: none;}
        .ivu-date-picker {display: inline-block;line-height: normal;width: 280px;}

        /**链接选择器组件选择范围优化 css**/
        .ivu-icon-ios-close.ivu-input-icon div:before{content: '';}
        .ivu-icon-link-select{width: 100%;text-align: right;}
        .ivu-icon-link-select div{width: 80px;float: right;background: #eeeeee;color: #848484;text-align: center;height: 30px;margin-top: 1px;margin-right: 1px;border-bottom-right-radius: 4px;border-top-right-radius: 4px;font-size: 14px; }
        .ivu-icon-link-select div:before{content: '选择地址';}
        .ivu-icon-link-select:hover div{background: #d2cece;}
        /**链接选择器组件选择范围优化 end**/
    </style>
    <script>
        /**链接选择器组件选择范围优化 js**/
        $(function () {
            $('.ivu-icon-ios-close.ivu-input-icon').html('<div></div>');
            $('.ivu-icon-link-select').html('<div></div>');
        })
        /**链接选择器组件选择范围优化 end**/
    </script>
    <style id="form-create-style">.form-create{padding:25px;} .fc-upload-btn,.fc-files{display: inline-block;width: 58px;height: 58px;text-align: center;line-height: 58px;border: 1px solid #c0ccda;border-radius: 4px;overflow: hidden;background: #fff;position: relative;box-shadow: 2px 2px 5px rgba(0,0,0,.1);margin-right: 4px;box-sizing: border-box;}.__fc_h{display:none;}.__fc_v{visibility:hidden;} .fc-files>.ivu-icon{vertical-align: middle;}.fc-files img{width:100%;height:100%;display:inline-block;vertical-align: top;}.fc-upload .ivu-upload{display: inline-block;}.fc-upload-btn{border: 1px dashed #c0ccda;}.fc-upload-btn>ivu-icon{vertical-align:sub;}.fc-upload .fc-upload-cover{opacity: 0; position: absolute; top: 0; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,.6); transition: opacity .3s;}.fc-upload .fc-upload-cover i{ color: #fff; font-size: 20px; cursor: pointer; margin: 0 2px; }.fc-files:hover .fc-upload-cover{opacity: 1; }.fc-hide-btn .ivu-upload .ivu-upload{display:none;}.fc-upload .ivu-upload-list{margin-top: 0;}.fc-spin-icon-load{animation: ani-fc-spin 1s linear infinite;} @-webkit-keyframes ani-fc-spin{0%{-webkit-transform:rotate(0deg);transform:rotate(0deg)}50%{-webkit-transform:rotate(180deg);transform:rotate(180deg)}to{-webkit-transform:rotate(1turn);transform:rotate(1turn)}}@keyframes ani-fc-spin{0%{-webkit-transform:rotate(0deg);transform:rotate(0deg)}50%{-webkit-transform:rotate(180deg);transform:rotate(180deg)}to{-webkit-transform:rotate(1turn);transform:rotate(1turn)}}</style></head>
<body>
    <form autocomplete="off" class="ivu-form ivu-form-label-right form-create">
        <div class="ivu-row">
            {volist name='power' id='v'}
            {if condition="$v['type'] eq 1"}
            {switch name="$v['input_type']"}
            {case value="text"}
            <div class="ivu-col ivu-col-span-24">
                <div class="ivu-form-item">
                    <label for="fc_{$v['sign']}" class="ivu-form-item-label" style="width: 125px;">{$v.name}</label>
                    <div class="ivu-form-item-content" style="margin-left: 125px;">
                        <div class="ivu-input-wrapper ivu-input-type">
                            <i class="ivu-icon ivu-icon-load-c ivu-load-loop ivu-input-icon ivu-input-icon-validate"></i>
                            <input id="fc_{$v['sign']}" name="{$v['sign']}" autocomplete="off" spellcheck="false" type="text" placeholder="请输入{$v.name}"  class="ivu-input" value="{$v['checked']}">
                        </div>
                    </div>
                </div>
            </div>
            {/case}
            {case value='radio'}
            <div class="ivu-col ivu-col-span-24">
                <div class="ivu-form-item">
                    <label for="fc_6" class="ivu-form-item-label" style="width: 125px;">{$v.name}</label>
                    <div class="ivu-form-item-content" style="margin-left: 125px;">
                        <div  class="ivu-radio-group">
                            {volist name="$v['value_show']" id='vo'}
                            <label class="ivu-radio-wrapper ivu-radio-group-item {notempty name='vo.checked'}ivu-radio-wrapper-checked{/notempty}">
                                <span class="ivu-radio {notempty name='vo.checked'}ivu-radio-checked{/notempty}">
                                    <span class="ivu-radio-inner"></span>
                                    <input type="radio" class="ivu-radio-input" name="{$v['sign']}" value="{$vo['key']}">
                                </span>
                                {$vo['label']}
                            </label>
                            {/volist}
                        </div>
                    </div>
                </div>
            </div>
            {/case}
            {/switch}
            {/if}
            {/volist}
            <div class="ivu-col ivu-col-span-24">
                <div style="font-size:16px;height: 22px;line-height:22px;font-weight: bold;border-bottom:1px solid #ccc;margin-bottom: 10px">社区权限</div>
            </div>
            {volist name='power' id='v'}
            {if condition="$v['type'] eq 2"}
            {switch name="$v['input_type']"}
            {case value="text"}
            <div class="ivu-col ivu-col-span-24">
                <div class="ivu-form-item">
                    <label for="fc_{$v['sign']}" class="ivu-form-item-label" style="width: 125px;">{$v.name}</label>
                    <div class="ivu-form-item-content" style="margin-left: 125px;">
                        <div class="ivu-input-wrapper ivu-input-type">
                            <i class="ivu-icon ivu-icon-load-c ivu-load-loop ivu-input-icon ivu-input-icon-validate"></i>
                            <input id="fc_{$v['sign']}" name="{$v['sign']}" autocomplete="off" spellcheck="false" type="text" placeholder="请输入{$v.name}"  class="ivu-input" value="{$v['checked']}">
                        </div>
                    </div>
                </div>
            </div>
            {/case}
            {case value='radio'}
            <div class="ivu-col ivu-col-span-24">
                <div class="ivu-form-item">
                    <label for="fc_6" class="ivu-form-item-label" style="width: 125px;">{$v.name}</label>
                    <div class="ivu-form-item-content" style="margin-left: 125px;">
                        <div  class="ivu-radio-group">
                            {volist name="$v['value_show']" id='vo'}
                            <label class="ivu-radio-wrapper ivu-radio-group-item {notempty name='vo.checked'}ivu-radio-wrapper-checked{/notempty}">
                                <span class="ivu-radio {notempty name='vo.checked'}ivu-radio-checked{/notempty}">
                                    <span class="ivu-radio-inner"></span>
                                    <input type="radio" class="ivu-radio-input" name="{$v['sign']}" value="{$vo['key']}">
                                </span>
                                {$vo['label']}
                            </label>
                            {/volist}
                        </div>
                    </div>
                </div>
            </div>
            {/case}
            {/switch}
            {/if}
            {/volist}
            <div class="ivu-col ivu-col-span-24">
                <div style="font-size:16px;height: 22px;line-height:22px;font-weight: bold;border-bottom:1px solid #ccc;margin-bottom: 10px">管理权限</div>
            </div>
            {volist name='power' id='v'}
            {if condition="$v['type'] eq 3"}
            {switch name="$v['input_type']"}
            {case value="text"}
            <div class="ivu-col ivu-col-span-24">
                <div class="ivu-form-item">
                    <label for="fc_{$v['sign']}" class="ivu-form-item-label" style="width: 125px;">{$v.name}</label>
                    <div class="ivu-form-item-content" style="margin-left: 125px;">
                        <div class="ivu-input-wrapper ivu-input-type">
                            <i class="ivu-icon ivu-icon-load-c ivu-load-loop ivu-input-icon ivu-input-icon-validate"></i>
                            <input id="fc_{$v['sign']}" name="{$v['sign']}" autocomplete="off" spellcheck="false" type="text" placeholder="请输入{$v.name}"  class="ivu-input" value="{$v['checked']}">
                        </div>
                    </div>
                </div>
            </div>
            {/case}
            {case value='radio'}
            <div class="ivu-col ivu-col-span-24">
                <div class="ivu-form-item">
                    <label for="fc_6" class="ivu-form-item-label" style="width: 125px;">{$v.name}</label>
                    <div class="ivu-form-item-content" style="margin-left: 125px;">
                        <div  class="ivu-radio-group">
                            {volist name="$v['value_show']" id='vo'}
                            <label class="ivu-radio-wrapper ivu-radio-group-item {notempty name='vo.checked'}ivu-radio-wrapper-checked{/notempty}">
                                <span class="ivu-radio {notempty name='vo.checked'}ivu-radio-checked{/notempty}">
                                    <span class="ivu-radio-inner"></span>
                                    <input type="radio" class="ivu-radio-input" name="{$v['sign']}" value="{$vo['key']}">
                                </span>
                                {$vo['label']}
                            </label>
                            {/volist}
                        </div>
                    </div>
                </div>
            </div>
            {/case}
            {/switch}
            {/if}
            {/volist}
        </div>
    </form>
</body>
</html>
<script>
    $('.ivu-radio-wrapper').click(function () {
        $(this).addClass('ivu-radio-wrapper-checked').children().addClass('ivu-radio-checked').children().attr("checked","checked");
        $(this).siblings().removeClass('ivu-radio-wrapper-checked').children().removeClass('ivu-radio-checked').children().remove('checked');
    });
</script>