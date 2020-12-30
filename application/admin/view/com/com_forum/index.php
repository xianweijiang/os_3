{extend name="public/container"}
{block name="content"}
<div class="layui-fluid" style="background: #fff;margin-top: -10px;">
    <div class="layui-tab layui-tab-brief" lay-filter="tab">
       <!-- <ul class="layui-tab-title">
            <li lay-id="list" {eq name='status' value='1'}class="layui-this" {/eq} >
                <a href="{eq name='status' value='1'}javascript:;{else}{:Url('index',['status'=>1])}{/eq}">正式版块({$common})</a>
            </li>
            <li lay-id="list" {eq name='status' value='2'}class="layui-this" {/eq}>
                <a href="{eq name='status' value='2'}javascript:;{else}{:Url('index',['status'=>2])}{/eq}">待审核版块({$need_verify})</a>
            </li>
            <li lay-id="list" {eq name='status' value='0'}class="layui-this" {/eq}>
                <a href="{eq name='status' value='0'}javascript:;{else}{:Url('index',['status'=>0])}{/eq}">已驳回版块({$band})</a>
            </li>
            <li lay-id="list" {eq name='status' value='-1'}class="layui-this" {/eq}>
                <a href="{eq name='status' value='-1'}javascript:;{else}{:Url('index',['status'=>-1])}{/eq}">版块回收站({$recycle})</a>
            </li>
        </ul>-->
    </div>
    <div class="layui-row layui-col-space15" id="app">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body" style="padding-bottom: 0px">
                    <form class="layui-form layui-form-pane" action="">

                        <div class="layui-card-body" style="margin-left: -10px;margin-top: -8px">
                            <div class="layui-form-item">
                                <div class="layui-inline">
                                    <label class="layui-form-label">版块级别</label>
                                    <div class="layui-input-block">
                                        <select name="level">
                                            <option value="">全部</option>
                                            <option value="1">顶级</option>
                                            <option value="2">二级</option>
                                            <option value="3">三级</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="layui-inline">
                                    <label class="layui-form-label">版块名称</label>
                                    <div class="layui-input-block">
                                        <input type="text" name="name" class="layui-input" placeholder="请输入版块名称,关键字">
                                    </div>
                                </div>
                                <div class="layui-inline">
                                    <label class="layui-form-label">状态</label>
                                    <div class="layui-input-block">
                                        <select name="display">
                                            <option value="">全部</option>
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="layui-inline">
                                    <label class="layui-form-label">是否推荐</label>
                                    <div class="layui-input-block">
                                        <select name="is_hot">
                                            <option value="">全部</option>
                                            <option value="1">推荐</option>
                                            <option value="0">不推荐</option>
                                        </select>
                                    </div>
                                </div>
                                {in name="status" value="-1,0,2"}
                                <div class="layui-col-lg12">
                                    {switch name="status"}
                                    {case value="2"}
                                    <input type="hidden" name="time_field" value="create_time">
                                    <label class="layui-form-label">创建时间:</label>
                                    {/case}
                                    {case value="0"}
                                    <input type="hidden" name="time_field" value="create_time">
                                    <label class="layui-form-label">创建时间:</label>
                                    {/case}
                                    {case value="-1"}
                                    <input type="hidden" name="time_field" value="update_time">
                                    <label class="layui-form-label">删除时间:</label>
                                    {/case}
                                    {/switch}
                                    <div class="layui-input-block">
                                        <input type="hidden" name="data" id="data" data-zd="false">
                                        <button class="layui-btn layui-btn-sm" type="button" onclick="setData(this.dataset.value)" data-value=""  style="margin-left: 20px;margin-top: 4px">全部</button>
                                        <button class="layui-btn layui-btn-sm layui-btn-primary" type="button" onclick="setData(this.dataset.value, this)" data-value="yesterday" style="margin-top: 4px">昨天</button>
                                        <button class="layui-btn layui-btn-sm layui-btn-primary" type="button" onclick="setData(this.dataset.value, this)" data-value="today" style="margin-top: 4px">今天</button>
                                        <button class="layui-btn layui-btn-sm layui-btn-primary" type="button" onclick="setData(this.dataset.value, this)" data-value="week" style="margin-top: 4px">本周</button>
                                        <button class="layui-btn layui-btn-sm layui-btn-primary" type="button" onclick="setData(this.dataset.value, this)" data-value="month" style="margin-top: 4px">本月</button>
                                        <button class="layui-btn layui-btn-sm layui-btn-primary" type="button" onclick="setData(this.dataset.value, this)" data-value="quarter" style="margin-top: 4px">本季度</button>
                                        <button class="layui-btn layui-btn-sm layui-btn-primary" type="button" onclick="setData(this.dataset.value, this)" data-value="year" style="margin-top: 4px">本年</button>
                                        <button class="layui-btn layui-btn-sm layui-btn-primary" type="button" onclick="setData(this.dataset.value, this)" data-value="zd" id="zd" style="margin-top: 4px">自定义</button>
                                        <label id="date_time"></label>
                                    </div>
                                </div>
                                {/in}
                            </div>

                            <div class="layui-row layui-col-space10 layui-form-item">
                                <div class="layui-col-lg12">
                                    <div class="layui-input-block">
                                        <div class="layui-inline">
                                            <div class="layui-input-inline" style="margin-left: -108px; margin-top: 10px">
                                                <button class="layui-btn layui-btn-sm layui-btn-normal" lay-submit="search" lay-filter="search">
                                                    <i class="layui-icon layui-icon-search"></i>搜索</button>
                                                <button onclick="javascript:layList.reload();" type="reset" class="layui-btn layui-btn-primary layui-btn-sm">
                                            <i class="layui-icon layui-icon-refresh" ></i>刷新</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!--版块列表-->
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body">
                    <div class="alert alert-info" role="alert">
                        列表[版块描述],[排序]可进行快速修改,双击或者单击进入编辑模式,失去焦点可进行自动保存
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="layui-btn-container"  style="margin-top: -4px">
                        <a href="{:Url('index',['status'=>1])}" class="layui-btn layui-btn-sm">版块首页</a>
                        {switch name='status'}
                            {case value="0"}
                                <button class="layui-btn layui-btn-sm" data-type="set_verify">提交审核</button>
                                <button class="layui-btn layui-btn-sm" data-type="del">批量删除</button>
                            {/case}
                            {case value="1"}
                                <button class="layui-btn layui-btn-sm" onclick="$eb.createModalFrame(this.innerText,'{:Url('create')}',{h:document.body.clientHeight,w:document.body.clientWidth})">创建版块</button>
                                <button class="layui-btn layui-btn-sm" onclick="$eb.createModalFrame(this.innerText,'{:Url('create_table')}',{h:600,w:750})">创建分区</button>
                            {/case}
                            {case value="2"}
                                <button class="layui-btn layui-btn-sm" data-type="verify">批量审核</button>
                                <button class="layui-btn layui-btn-sm" data-type="del">批量删除</button>
                                <button class="layui-btn layui-btn-sm" data-type="band">批量驳回</button>
                            {/case}
                            {case value="-1"}
                                <button class="layui-btn layui-btn-sm" data-type="restore">还原</button>
                                <button class="layui-btn layui-btn-sm" data-type="remove">清理</button>
                            {/case}
                        {/switch}
                    </div>
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <!--图片-->
                    <script type="text/html" id="logo">
                        {{#  if(d.logo==''){ }}
                        {{#  } else { }}
                        <img style="cursor: pointer" onclick="javascript:$eb.openImage(this.src);" src="{{d.logo}}">
                        {{#  } }}
                    </script>
                    <script type="text/html" id="background">
                        {{#  if(d.background==''){ }}
                        {{#  } else { }}
                        <img style="cursor: pointer;width: 120px!important;height: 50px!important;" onclick="javascript:$eb.openImage(this.src);" src="{{d.background}}">
                        {{#  } }}
                    </script>
                    <!--上架|下架-->
                    <script type="text/html" id="checkboxstatus">
                        <input type='checkbox' name='id' lay-skin='switch' value="{{d.id}}" lay-filter='is_verify' lay-text='启用|禁用'  {{ d.display == 1 ? 'checked' : '' }}>
                    </script>
                    <!--收藏-->
                    <script type="text/html" id="thread_count">
                         <a title="管理帖子" href="{:Url('thread')}?id={{d.id}}"><p><i class="layui-icon layui-icon-dialogue"></i> 共有<font color="red">{{d.post_count}}</font>帖</p></a>
                    </script>
                    <!-- 子版块 -->
                    <script type="text/html" id="sub_forum">
                        {{# if(d.sub_count){ }}
                        <a href="javascript:$eb.createModalFrame(this.innerText,'{:Url('sub_index')}?pid={{d.id}}&status=1',{h:document.body.clientHeight,w:document.body.clientWidth});">查看子版块</a>
                        {{# } }}
                    </script>
                    <script type="text/html" id="sub_forum_index">
                        {{# if(d.sub_count){ }}
                        <a href="{:Url('index')}?pid={{d.id}}&status=1">查看【{{d.sub_count}}】</a>
                        {{# } }}
                    </script>
                    <script type="text/html" id="class_forum_index">
                        {{# if(d.class_count){ }}
                        <a href="{:Url('com.com_thread_class/index')}?fid={{d.id}}&status=1"">查看【{{d.class_count}}】</a>
                        {{# } }}
                    </script>
                    <!--点赞-->
                    <script type="text/html" id="collect">
                        <span><i class="layui-icon layui-icon-star"></i> {{d.collect}}</span>
                    </script>
                    <!--版块名称-->
                    <script type="text/html" id="forum_name">
                        <h4>{{d.name}}</h4>
                        <a href="{:Url('com.com_thread_class/index')}?fid={{d.id}}" >
                            分类{{d.class_count}}
                        </a>
                    </script>
                    <script type="text/html" id="forum_name_index">
                        <h4>{{d.name}}</h4>
                        <p>{{d.type_name}}</p>
                        {{# if(d.is_hot== '是'){ }}
                        <p>【推荐】</p>
                        {{# } }}
                        <!--<a href="{:Url('com.com_thread_class/index')}?fid={{d.id}}" >
                            分类{{d.class_count}}
                        </a>-->
                    </script>
                    <script type="text/html" id="is_hot">
                        <h4>{{d.is_hot}}</h4>

                    </script>
                    <script type="text/html" id="admin_users">
                        {{#  if(d.admin_uid!=0){ }}
                        <p>{{d.admin_users}}</p>
                        {{#  } else { }}
                        <p>暂无版主</p>
                        {{#  } }}
                    </script>
                    <!--操作-->
                    <script type="text/html" id="act_common">
                        {{# if(d.pid_name!='顶级'){ }}
                        <button type="button" class="layui-btn layui-btn-xs layui-btn-normal" onclick="$eb.createModalFrame('{{d.name}}-编辑','{:Url('edit')}?id={{d.id}}',{h:document.body.clientHeight,w:document.body.clientWidth})">
                            编辑
                        </button>
                        {{# }else{ }}
                        <button type="button" class="layui-btn layui-btn-xs layui-btn-normal" onclick="$eb.createModalFrame('{{d.name}}-编辑','{:Url('create_table')}?id={{d.id}}',{h:600,w:750})">
                            编辑
                        </button>
                        {{# } }}
                        <button type="button" class="layui-btn layui-btn-xs" onclick="dropdown(this)">操作 <span class="caret"></span></button>
                        <ul class="layui-nav-child layui-anim layui-anim-upbit">
                            <li>
                                <a href="javascript:void(0);" class="" onclick="$eb.createModalFrame(this.innerText,'{:Url('edit_content')}?id={{d.id}}')">
                                    <i class="fa fa-pencil"></i> 版块详情</a>
                            </li>
                            {{# if(d.pid_name!='顶级'){ }}
                            <li>
                                <a href="javascript:void(0);" lay-event='set_hot'>
                                    {{# if(d.is_hot== '是'){ }}
                                    取消推荐
                                    {{# }else{ }}
                                    设为推荐
                                    {{# } }}
                                </a>
                            </li>
                            {{# } }}
                            {{# if(d.pid!=0){ }}
                            <li>
                                {if condition="in_array('osapi_forum_power',$open_list)"}
                                <a href="javascript:void(0);" class="" onclick="$eb.createModalFrame(this.innerText,'{:Url('create_power')}?id={{d.id}}')">
                                    <i class="fa fa-pencil"></i> 版块权限</a>
                                {else/}
                                <a href="javascript:void(0);" class="" lay-event='unable'>
                                    <i class="fa fa-pencil"></i> 版块权限</a>
                                {/if}
                            </li>
                            {{# } }}
                            <li>
                                <a href="javascript:void(0);" lay-event='delstor'>
                                    <i class="fa fa-trash"></i> 删除
                                </a>
                            </li>
                        </ul>
                    </script>
                    <script type="text/html" id="admin_html">
                        {{#  layui.each(d.admin, function(index, item){ }}
                        <p>{{item.nickname}}{{#  if(item.level=="2"){ }}【S】{{# } }}</p>
                        {{#  }); }}
                    </script>
                    <script type="text/html" id="pid_name">
                        {{# if(d.pid_name!='顶级'){ }}
                        <p>{{d.pid_name}}</p>
                        {{# }else{ }}
                        <p>{{d.pid_name}}</p>
                        {{# } }}
                    </script>
                    <script type="text/html" id="post_count">
                        <p>帖子数:{{d.post_count}} </p>
                        <p>分享数:{{d.share_count}}</p>
                        <p>虚拟关注人数:{{d.false_num}} </p>
                        <p>真实关注人数:{{d.member_count}} </p>
                        <p>收藏数:暂无统计</p>
                    </script>
                    <script type="text/html" id="act_need_verify">
                        <button type="button" class="layui-btn layui-btn-xs" onclick="dropdown(this)">操作 <span class="caret"></span></button>
                        <ul class="layui-nav-child layui-anim layui-anim-upbit">
                            <li>
                                <a href="javascript:void(0);" class="" onclick="$eb.createModalFrame(this.innerText,'{:Url('edit_content')}?id={{d.id}}')">
                                    <i class="fa fa-pencil"></i> 版块详情</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" lay-event='set_verify_pass'>审核通过</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" lay-event='set_verify_fail'>驳回</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" lay-event='delstor'>
                                    <i class="fa fa-trash"></i> 移到回收站
                                </a>
                            </li>
                        </ul>
                    </script>
                    <script type="text/html" id="act_band">
                        <button type="button" class="layui-btn layui-btn-xs layui-btn-normal" onclick="$eb.createModalFrame('{{d.name}}-编辑','{:Url('edit')}?id={{d.id}}',{h:document.body.clientHeight,w:document.body.clientWidth})">
                            编辑
                        </button>
                        <button type="button" class="layui-btn layui-btn-xs" onclick="dropdown(this)">操作 <span class="caret"></span></button>
                        <ul class="layui-nav-child layui-anim layui-anim-upbit">
                            <li>
                                <a href="javascript:void(0);" class="" onclick="$eb.createModalFrame(this.innerText,'{:Url('edit_content')}?id={{d.id}}')">
                                    <i class="fa fa-pencil"></i> 版块详情</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" lay-event='set_verify_from_band'>提交审核</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" lay-event='delstor'>
                                    <i class="fa fa-trash"></i> 移到回收站
                                </a>
                            </li>
                        </ul>
                    </script>
                    <script type="text/html" id="act_draft">
                        <button type="button" class="layui-btn layui-btn-xs layui-btn-normal" onclick="$eb.createModalFrame('{{d.name}}-编辑','{:Url('edit')}?id={{d.id}}',{h:document.body.clientHeight,w:document.body.clientWidth})">
                            编辑
                        </button>
                        <button type="button" class="layui-btn layui-btn-xs" onclick="dropdown(this)">操作 <span class="caret"></span></button>
                        <ul class="layui-nav-child layui-anim layui-anim-upbit">
                            <li>
                                <a href="javascript:void(0);" lay-event='set_verify_from_band'>提交审核</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" lay-event='delstor'>
                                    <i class="fa fa-trash"></i> 移到回收站
                                </a>
                            </li>
                        </ul>
                    </script>
                    <script type="text/html" id="act_recycle">
                        <button type="button" class="layui-btn layui-btn-xs" onclick="dropdown(this)">操作 <span class="caret"></span></button>
                        <ul class="layui-nav-child layui-anim layui-anim-upbit">
                            <li>
                                <a href="javascript:void(0);" class="" onclick="$eb.createModalFrame(this.innerText,'{:Url('edit_content')}?id={{d.id}}')">
                                    <i class="fa fa-pencil"></i> 版块详情</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="" onclick="$eb.createModalFrame(this.innerText,'{:Url('create_power')}?id={{d.id}}')">
                                    <i class="fa fa-pencil"></i> 版块</a>
                            </li>
                            <li><a href="javascript:void(0);" lay-event='restore'>还原</a></li>
                            <li>
                                <a href="javascript:void(0);" lay-event='delstor'>
                                    <i class="fa fa-trash"></i> 清理
                                </a>
                            </li>
                        </ul>
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
<script>
    var status=<?=$status?>;
    var level = <?=$level?>
    //实例化form
    layList.form.render();
    //加载列表
    layList.tableList('List',"{:Url('forum_list',['status'=>$status,'pid'=>$pid])}",function (){
        var join=new Array();
        switch (parseInt(status)){
            // 禁用
            case 0:
                join=[
                    {type:'checkbox'},
                    {field: 'id', title: 'ID', event:'fid',width:'3%'},
                    {field: 'name', title: '版块名称',templet:'#forum_name',width:'7%'},
                    {field: 'logo', title: '版块logo',templet:'#logo',width:'6%'},
                    {field: 'background', title: '版块背景图',templet:'#background',width:'7%'},
                    {field: 'type_name', title: '版块类型',templet:'type_name',width:'6%'},
                    {field: 'private',title:'版块属性',width:'3%'},
                    {field: 'pid_name', title: '上级版块',templet:'#pid_name',width:'6%'},
                    {field: 'sort', title: '排序',edit:'sort',width:'6%'},
                    {field: 'post_count', title: '数据统计',templet:'#post_count',width:'10%'},
                    {field: 'status', title: '状态',templet:"#checkboxstatus",width:'6%'},
                    {field: 'create_time', title: '创建时间',edit:'create_time',width:'6%'},
                    {field: 'update_time', title: '驳回时间',edit:'update_time',width:'6%'},
                    // {field: 'admin_html',title:'版主',templet:'#admin_html',width:'5%'},
                    // {field: 'summary', title: '版块描述',edit:'summary'},
                    {field: 'right', title: '操作',align:'center',toolbar:'#act_band',width:'11%'},
                ];
                break;
            case 1:
                join=[
                    {field: 'id', title: 'ID', event:'fid',width:'3%'},
                    {field: 'pid_name', title: '父级',templet:'#pid_name',width:'10%'},
                    {field: 'name', title: '版块名称',templet:'#forum_name_index'},
                    {field: 'logo', title: '版块logo',templet:'#logo',width:'8%'},
                    //{field: 'background', title: '版块背景图',templet:'#background',width:'8%'},
                    {field: 'private',title:'版块属性',width:'6%'},
                    {field: 'admin_html',title:'版主',templet:'#admin_html',width:'8%'},
                    {field: 'post_count', title: '数据统计',templet:'#post_count',width:'10%'},
                    {field: 'sort', title: '排序',edit:'sort',width:'6%'},
                    {field: 'status', title: '状态',templet:"#checkboxstatus",width:'7%'},
                    {field: 'id', title:'查看子版块', templet:'#sub_forum_index',width:'7%'},
                    {field: 'id', title:'查看分类', templet:'#class_forum_index',width:'7%'},
                    {field: 'right', title: '操作',align:'center',toolbar:'#act_common',width:'11%'},
                ];
                break;
            // 待审核
            case 2:
            join=[
                {type:'checkbox'},
                {field: 'id', title: 'ID', event:'fid',width:'3%'},
                {field: 'name', title: '版块名称',templet:'#forum_name',width:'7%'},
                {field: 'logo', title: '版块logo',templet:'#logo',width:'6%'},
                {field: 'background', title: '版块背景图',templet:'#background',width:'7%'},
                {field: 'private',title:'版块属性',width:'3%'},
                {field: 'type_name', title: '版块类型',templet:'type_name',width:'6%'},
                {field: 'pid_name', title: '上级版块',templet:'#pid_name',width:'6%'},
                {field: 'is_hot', title: '是否推荐',templet:'#is_hot',width:'7%'},
                {field: 'post_count', title: '数据统计',templet:'#post_count',width:'10%'},
                // {field: 'admin_html',title:'版主',templet:'#admin_html',width:'5%'},
                // {field: 'summary', title: '版块描述',edit:'summary'},
                // {field: 'thread_count', title: '主题帖数',templet:'#thread_count',width:'10%'},
                {field: 'status', title: '状态',templet:"#checkboxstatus",width:'6%'},
                {field: 'sort', title: '排序',edit:'sort',width:'6%'},
                {field: 'right', title: '操作',align:'center',toolbar:'#act_need_verify',width:'12%'},
            ];
            break;
            // 回收站
            case -1:
                join=[
                    {type:'checkbox'},
                    {field: 'id', title: 'ID', event:'fid',width:'3%'},
                    {field: 'name', title: '版块名称',templet:'#forum_name',width:'7%'},
                    {field: 'logo', title: '版块logo',templet:'#logo',width:'6%'},
                    {field: 'background', title: '版块背景图',templet:'#background',width:'7%'},
                    {field: 'private',title:'版块属性',width:'3%'},
                    {field: 'type_name', title: '版块类型',templet:'type_name',width:'6%'},
                    {field: 'pid_name', title: '上级版块',templet:'#pid_name',width:'10%'},
                    {field: 'post_count', title: '数据统计',templet:'#post_count',width:'10%'},
                    // {field: 'summary', title: '版块描述',edit:'summary'},
                    {field: 'create_time', title: '创建时间',edit:'create_time',width:'9%'},
                    {field: 'update_time', title: '删除时间',edit:'update_time',width:'9%'},
                    // {field: 'thread_count', title: '主题帖数',templet:'#thread_count',width:'10%'},
                    {field: 'right', title: '操作',align:'center',toolbar:'#act_recycle',width:'12%'},
                ];
                break;
        }
        return join;
    })

    //下拉框
    $(document).click(function (e) {
        $('.layui-nav-child').hide();
    })
    function dropdown(that){
        var oEvent = arguments.callee.caller.arguments[0] || event;
        oEvent.stopPropagation();
        var offset = $(that).offset();
        var top=offset.top-$(window).scrollTop();
        var index = $(that).parents('tr').data('index');
        $('.layui-nav-child').each(function (key) {
            if (key != index) {
                $(this).hide();
            }
        })
        if($(document).height() < top+$(that).next('ul').height()){
            $(that).next('ul').css({
                'padding': 10,
                'top': - ($(that).parent('td').height() / 2 + $(that).height() + $(that).next('ul').height()/2),
                'min-width': 'inherit',
                'position': 'absolute'
            }).toggle();
        }else{
            $(that).next('ul').css({
                'padding': 10,
                'top':$(that).parent('td').height() / 2 + $(that).height(),
                'min-width': 'inherit',
                'position': 'absolute'
            }).toggle();
        }
    }
    //快速编辑
    layList.edit(function (obj) {
        var id=obj.data.id,value=obj.value;
        switch (obj.field) {
            case 'summary':
                action.set_forum('summary',id,value);
                break;
            case 'sort':
                action.set_forum('sort',id,value);
                break;
        }
    });
    //审核版块
    layList.switch('is_verify',function (odj,value) {
        if(odj.elem.checked==true){
            layList.baseGet(layList.Url({c:'com.com_forum',a:'set_verify',p:{display:0,id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }else{
            layList.baseGet(layList.Url({c:'com.com_forum',a:'set_verify',p:{display:1,id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }
    });
    //点击事件绑定
    layList.tool(function (event,data,obj) {
        switch (event) {
            case 'delstor':
                console.log($eb);
                if(data.status == -1){
                    var url  = layList.U({c:'com.com_forum',a:'delete',q:{id:data.id}});
                    var code = {title:"操作提示",text:"你确定要清理该版块吗？",type:'info',confirm:'是的，我要删除'};
                }else{
                    var url  = layList.U({c:'com.com_forum',a:'set_forum',q:{id:data.id, field:'status', value: -1}});
                    var code = {title:"操作提示",text:"版块删除后无法进行恢复，请谨慎操作",type:'info',confirm:'确定删除'};
                }
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success','');
                            obj.del();
                        }else
                            return Promise.reject(res.data.msg || '删除失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                },code)
                break;
            case 'set_hot':
                var url=layList.U({c:'com.com_forum',a:'set_forum',q:{id:data.id, field:'is_hot', value: data.is_hot == '是'?0:1}});
                if(data.is_hot == '是'){
                    var code = {title:"操作提示",text:"确定取消版块推荐操作吗？",type:'info',confirm:'是的，取消推荐该版块'};
                }else{
                    var code = {title:"操作提示",text:"确定将该版块设为推荐吗？",type:'info',confirm:'是的，设为推荐'};
                }
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success','');
                            layList.reload({},true,null,obj);
                        }else{
                            return Promise.reject(res.data.msg || '设置失败');
                        }
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                },code);
                break;
            case 'restore':
                var url=layList.U({c:'com.com_forum',a:'set_forum',q:{id:data.id, field:'status', value: 1}});
                var code = {title:"操作提示",text:"确定还原该版块吗？",type:'info',confirm:'是的，还原'};
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success','');
                            layList.reload({},true,null,obj);
                        }else{
                            return Promise.reject(res.data.msg || '失败');
                        }
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                },code);
                break;
            case 'set_verify_from_band':
                var url=layList.U({c:'com.com_forum',a:'set_forum',q:{id:data.id, field:'status', value: 2}});
                var code = {title:"操作提示",text:"确定提交审核该版块吗？",type:'info',confirm:'是的，提交审核'};
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success','');
                            layList.reload({},true,null,obj);
                        }else{
                            return Promise.reject(res.data.msg || '失败');
                        }
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                },code);
                break;
            case 'set_verify_pass':
                var url=layList.U({c:'com.com_forum',a:'set_forum',q:{id:data.id, field:'status', value: 1}});
                var code = {title:"操作提示",text:"确定审核通过该版块吗？",type:'info',confirm:'是的，审核通过'};
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success','');
                            layList.reload({},true,null,obj);
                        }else{
                            return Promise.reject(res.data.msg || '失败');
                        }
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                },code);
                break;
            case 'set_verify_fail':
                var url=layList.U({c:'com.com_forum',a:'set_forum',q:{id:data.id, field:'status', value: 0}});
                var code = {title:"操作提示",text:"确定驳回该版块吗？",type:'info',confirm:'是的，驳回'};
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success','');
                            layList.reload({},true,null,obj);
                        }else{
                            return Promise.reject(res.data.msg || '失败');
                        }
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                },code);
                break;
            case 'unable':
                var code = {title:"提示",text:"该功能未开通或已过期，如需开通，请联系客服！",type:'info',confirm:'联系客服',cancel:'取消',confirmBtnColor:'#0ca6f2'};
                $eb.$swal('delete',function(){
                    $eb.createModalFrame('联系客服','https://osxbe.demo.opensns.cn/auth/Index/tip_box.html',{h:600,w:700})
                }, code)
                break;
        }
    })
    //排序
    layList.sort(function (obj) {
        var type = obj.type;
        switch (obj.field){
            case 'id':
                // layList.reload({order: layList.order(type,'p.id')},true,null,obj);
                break;
            case 'sales':
                layList.reload({order: layList.order(type,'p.sales')},true,null,obj);
                break;
        }
    });
    //查询
    layList.search('search',function(where){
        console.log(where)
        layList.reload(where,true);
    });
    //自定义方法
    var action={
        set_forum:function(field,id,value){
            layList.baseGet(layList.Url({c:'com.com_forum',a:'set_forum',q:{field:field,id:id,value:value}}),function (res) {
                layList.msg(res.msg);
            });
        },
        set_verify:function(){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                var code = {title:"操作提示",text:"确定提交审核版块吗？",type:'info',confirm:'是的，提交审核'};
                $eb.$swal('delete',function(){
                    layList.basePost(layList.Url({c:'com.com_forum',a:'forum_verify'}),{ids:ids, status:2},function (res) {
                        layList.msg(res.msg);
                        layList.reload();
                    });
                },code);
            }else{
                layList.msg('请选择要提交审核的版块');
            }
        },
        verify:function(){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                var code = {title:"操作提示",text:"确定审核通过版块吗？",type:'info',confirm:'是的，审核通过'};
                $eb.$swal('delete',function(){
                    layList.basePost(layList.Url({c:'com.com_forum',a:'forum_verify'}),{ids:ids},function (res) {
                        layList.msg(res.msg);
                        layList.reload();
                    });
                },code);
            }else{
                layList.msg('请选择要审核的版块');
            }
        },
        del: function(){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                var code = {title:"操作提示",text:"确定批量删除版块吗？",type:'info',confirm:'是的，删除'};
                $eb.$swal('delete',function(){
                    layList.basePost(layList.Url({c:'com.com_forum',a:'del'}),{ids:ids},function (res) {
                        layList.msg(res.msg);
                        layList.reload();
                    });
                },code);
            }else{
                layList.msg('请选择要删除的版块');
            }
        },
        band:function(){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                var code = {title:"操作提示",text:"确定批量驳回版块吗？",type:'info',confirm:'是的，驳回'};
                $eb.$swal('delete',function(){
                    layList.basePost(layList.Url({c:'com.com_forum',a:'ban'}),{ids:ids},function (res) {
                        layList.msg(res.msg);
                        layList.reload();
                    });
                },code);
            }else{
                layList.msg('请选择要驳回的版块');
            }
        },
        restore:function(){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                var code = {title:"操作提示",text:"确定还原吗？",type:'info',confirm:'是的，还原'};
                $eb.$swal('delete',function(){
                     layList.basePost(layList.Url({c:'com.com_forum',a:'forum_verify'}),{ids:ids, status:1},function (res) {
                        layList.msg(res.msg);
                        layList.reload();
                    });
                },code);
            }else{
                layList.msg('请选择要还原的版块');
            }
        },
        remove:function(){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                var code = {title:"操作提示",text:"清空版块后，该版块下的所有分类、帖子数据将同步清空，无法恢复，请慎重考虑。 ",type:'info',confirm:'确定'};
                $eb.$swal('delete',function(){
                    layList.basePost(layList.Url({c:'com.com_forum',a:'remove'}),{ids:ids},function (res) {
                        layList.msg(res.msg);
                        layList.reload();
                    });
                },code);
            }else{
                layList.msg('请选择要清理的版块');
            }
        }
    };

    //多选事件绑定
    $('.layui-btn-container').find('button').each(function () {
        var type=$(this).data('type');
        $(this).on('click',function(){
            action[type] && action[type]();
        })
    });

    layList.laydate.render({
        elem:'#date_time',
        trigger:'click',
        eventElem:'#zd',
        range:true,
        change:function (value){
            $('#data').val(value);
            $('#date_time').text(value);
        }
    });

    var setData = function(val, ele){
        var $data = $('#data');
        $data.val(val);
        $(ele).parent().find('button').addClass('layui-btn-primary');
        $(ele).removeClass('layui-btn-primary');
        if(val == 'zd'){
            $('#date_time').show();
        }else{
            $('#date_time').hide();
        }
    }


</script>
{/block}
