<div>
    <div class="checkbox-content row" style="display: flex;margin-top: 15px">
        <label class="col-md-6" style="line-height: 29px;width: 150px;text-align: right">用户组{$num}:</label>
        <div class="col-md-6" style="display: flex;align-items: center">
            <select  id="user_{$num}" name="user_{$num}"  class="layui-input module-input" style="width:241px;">
                {volist name='group' id='v'}
                <option value="{$v.id}" >{$v.name}</option>
                {/volist}
            </select>
            <div class="delete-btn" style="color: #0ca6f2;font-size: 14px;text-decoration: underline;margin-left: 5px;cursor: pointer">删除</div>
        </div>
    </div>
    <div class="checkbox-content row" style="display: flex">
        <label class="col-md-6" style="line-height: 29px;width: 150px;text-align: right">有效期:</label>
        <div class="col-md-6">
            <input type="text" id="time_{$num}" name="time_{$num}" class="layui-input module-input"   style="width:241px;" placeholder="">
        </div>
    </div>
</div>

<script>
    layui.use('laydate', function(){
        var laydate = layui.laydate;
        //执行一个laydate实例
        laydate.render({
            elem: "#time_{$num}", //指定元素
            type:'date',
            value:'',//初始时间
            range: ''
        });
    });
</script>