{extend name="public/container"}
{block name="content"}
<script src="{__FRAME_PATH}js/plugins/chosen/chosen.jquery.js"></script>
<style>
    .layui-table-cell p{
        height: auto !important;
        line-height: !important;
    }
    /* 视频详情的样式 */
    .video-box {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: blue;
        width: 20px;
        height: 30px;
    }
    .position {
        top: 54.375px !important;
    }
</style>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15"  id="app">
        <!--搜索条件-->
        <div class="layui-col-md12" style="margin-top: -20px">
            <div class="layui-tab layui-tab-brief" lay-filter="tab">
                {if condition="$is_weibo eq 1"}
                    <ul class="layui-tab-title" style="background-color: white;top: 10px">
                        <li lay-id="list" {eq name='status' value=''}class="layui-this" {/eq} >
                        <a href="{eq name='status' value=''}javascript:;{else}{:Url('index',['status'=>'', 'is_weibo'=>1])}{/eq}">全部</a>
                        </li>
                        <li lay-id="list" {eq name='status' value='1'}class="layui-this" {/eq} >
                        <a href="{eq name='status' value='1'}javascript:;{else}{:Url('index',['status'=>1, 'is_weibo'=>1])}{/eq}">已审核</a>
                        </li>
                        <li lay-id="list" {eq name='status' value='2'}class="layui-this" {/eq}>
                        <a href="{eq name='status' value='2'}javascript:;{else}{:Url('index',['status'=>2, 'is_weibo'=>1])}{/eq}">待审核</a>
                        </li>
                        <li lay-id="list" {eq name='status' value='-1'}class="layui-this" {/eq}>
                        <a href="{eq name='status' value='-1'}javascript:;{else}{:Url('index',['status'=>-1, 'is_weibo'=>1])}{/eq}">回收站</a>
                        </li>
                    </ul>
                    {else/}
                    <ul class="layui-tab-title" style="background-color: white;top: 10px">
                        <li lay-id="list" {eq name='status' value=''}class="layui-this" {/eq} >
                        <a href="{eq name='status' value=''}javascript:;{else}{:Url('index',['status'=>'', 'type'=>$type])}{/eq}">全部</a>
                        </li>
                        <li lay-id="list" {eq name='status' value='1'}class="layui-this" {/eq} >
                        <a href="{eq name='status' value='1'}javascript:;{else}{:Url('index',['status'=>1, 'type'=>$type])}{/eq}">已审核</a>
                        </li>
                        <li lay-id="list" {eq name='status' value='2'}class="layui-this" {/eq}>
                        <a href="{eq name='status' value='2'}javascript:;{else}{:Url('index',['status'=>2, 'type'=>$type])}{/eq}">待审核</a>
                        </li>
                        <li lay-id="list" {eq name='status' value='0'}class="layui-this" {/eq}>
                        <a href="{eq name='status' value='0'}javascript:;{else}{:Url('index',['status'=>0, 'type'=>$type])}{/eq}">已驳回</a>
                        </li>
                        {eq name="type" value="4"}
                        <li lay-id="list" {eq name='status' value='3'}class="layui-this" {/eq}>
                        <a href="{eq name='status' value='3'}javascript:;{else}{:Url('index',['status'=>3, 'type'=>$type])}{/eq}">草稿箱</a>
                        </li>
                        {/eq}
                        <li lay-id="list" {eq name='status' value='-1'}class="layui-this" {/eq}>
                        <a href="{eq name='status' value='-1'}javascript:;{else}{:Url('index',['status'=>-1, 'type'=>$type])}{/eq}">回收站</a>
                        </li>
                    </ul>
                {/if}
            </div>
            <div class="layui-card">
                <div class="layui-card-header">搜索条件</div>
                <div class="layui-card-body" style="padding: 0; margin-top: 12px">
                    <form class="layui-form">
                        <div class="layui-carousel layadmin-carousel layadmin-shortcut" lay-anim="" lay-indicator="inside" lay-arrow="none" style="background:none">
                            <div class="layui-card-body ">
                                <div class="layui-row layui-col-space10 layui-form-item">
                                    <div class="layui-col-lg12"><div class="layui-inline">
                                            <label class="layui-form-label">关键词:</label>
                                            <div class="layui-input-inline" style="margin-left: 56px">
                                                <input type="text" name="real_name" v-model="where.real_name" lay-verify="title" autocomplete="off" placeholder="标题、正文关键词" class="layui-input" style="width: 173px;padding-left: 5px">
                                            </div>
                                        </div>
                                        <div class="layui-inline">
                                                <label class="layui-form-label">作者昵称:</label>
                                                <div class="layui-input-inline" style="margin-left: 56px">
                                                    <input type="text" name="uid" v-model="where.uid" lay-verify="uid" autocomplete="off" placeholder="作者昵称" class="layui-input" style="width: 173px;padding-left: 5px">
                                                </div>
                                            </div>
                                        <div class="layui-inline" style="margin-left: -26px">
                                            <label class="layui-form-label">所属版块:</label>
                                            <div class="layui-input-inline" style="width: 173px;margin-left: 42px">
                                                <select name="fid" v-model="where.fid" lay-filter="fid">
                                                    <option value="">全部</option>
                                                    {volist name='cate' id='vo'}
                                                    <option value="{$vo.id}" {eq name="vo.pid" value="0"} disabled{/eq}>{$vo.html}{$vo.name}</option>
                                                    {/volist}
                                                </select>
                                            </div>
                                        </div>
                                        <div class="layui-inline">
                                            <label class="layui-form-label">所属分类:</label>
                                            <div class="layui-input-inline" style="width: 173px;margin-left: 42px">
                                                <select id="cid" name="cid" v-model="where.cid" lay-filter="cid">
                                                    <option value="">全部</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="layui-inline">
                                            <label class="layui-form-label">是否置顶:</label>
                                            <div class="layui-input-block" style="width: 173px">
                                                <select name="is_top" v-model="where.is_top" lay-filter="is_top">
                                                    <option value="">全部</option>
                                                    <option value="1">是</option>
                                                    <option value="0">否</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="layui-inline">
                                            <label class="layui-form-label">是否加精:</label>
                                            <div class="layui-input-block" style="width: 173px">
                                                <select name="is_essence" v-model="where.is_essence" lay-filter="is_essence">
                                                    <option value="">全部</option>
                                                    <option value="1">是</option>
                                                    <option value="0">否</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="layui-inline">
                                            <label class="layui-form-label">是否推荐:</label>
                                            <div class="layui-input-block" style="width: 173px">
                                                <select name="is_recommend" v-model="where.is_recommend" lay-filter="is_recommend">
                                                    <option value="">全部</option>
                                                    <option value="1">是</option>
                                                    <option value="0">否</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="layui-inline">
                                            <label class="layui-form-label">评论数:</label>
                                            <div class="layui-input-block"  style="width: 173px">
                                                <select name="comment_num" v-model="where.comment_num" lay-filter="comment_num">
                                                    <option value="">全部</option>
                                                    <option value="1">>0</option>
                                                    <option value="0">=0</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="layui-col-lg12">
                                        <label class="layui-form-label">发布时间:</label>
                                        <div class="layui-input-block" data-type="data" v-cloak="">
                                            <button class="layui-btn layui-btn-sm" type="button" v-for="item in dataList" @click="setData(item)" :class="{'layui-btn-primary':where.data!=item.value}" style="margin-top: 0px">{{item.name}}</button>
                                            <button class="layui-btn layui-btn-sm" type="button" ref="time" @click="setData({value:'zd',is_zd:true})" :class="{'layui-btn-primary':where.data!='zd'}" style="margin-top: 0px">自定义</button>
                                            <button type="button" class="layui-btn layui-btn-sm layui-btn-primary" v-show="showtime==true" ref="date_time" style="margin-top: 0px">{$year.0} - {$year.1}</button>
                                        </div>
                                    </div>
                                    <div class="layui-col-lg12">
                                        <div class="layui-input-block">
                                            <!-- <div class="layui-col-lg12 " style="margin-bottom: 10px">
                                               <input type="checkbox" name="more" lay-skin="primary" title="更多选项">
                                            </div> -->
                                            <button @click="search" type="button" class="layui-btn layui-btn-sm layui-btn-normal" style="margin-top: 0px">
                                                <i class="layui-icon layui-icon-search"></i>搜索</button>
                                            <!-- <button @click="excel" type="button" class="layui-btn layui-btn-warm layui-btn-sm export" type="button">
                                                <i class="fa fa-floppy-o" style="margin-right: 3px;"></i>导出</button> -->
                                            <button @click="refresh" type="reset" class="layui-btn layui-btn-primary layui-btn-sm" style="margin-top: 0px">
                                                <i class="layui-icon layui-icon-refresh" ></i>刷新</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!--end-->
        <!-- 中间详细信息-->
        <!--enb-->
    </div>
    <!--列表-->
    <div class="layui-row layui-col-space15" >
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">帖子列表</div>
                <div class="layui-card-body">
                    <div class="layui-btn-container" style="display:flex;justify-content: space-between;align-items:center;margin-top: 10px">
                        <div>
                            {eq name="status" value="1"}
                            <button class="layui-btn layui-btn-sm" data-type="move">迁移内容</button>
                            {/eq}
                            {if condition="$type eq 4 and $status eq ''"}
                            <button type="button" class="layui-btn layui-btn-sm" onclick="$eb.createModalFrame(this.innerText,'{:Url('create_news', ['type'=>$type])}')">发资讯</button>
                            {/if}

                            {if condition="$type eq 1 and $status eq '' and $is_weibo eq 0" }
                            <button type="button" class="layui-btn layui-btn-sm" onclick="$eb.createModalFrame(this.innerText,'{:Url('create_thread', ['type'=>$type])}')">发帖子</button>
                            {/if}

                            {if condition="$type eq 6 and $status eq ''"}
                            {if condition="$is_free_ban AND $is_end_ban"}
                            <button type="button" class="layui-btn layui-btn-sm" onclick="$eb.createModalFrame(this.innerText,'{:Url('create_video', ['type'=>$type])}')">发视频</button>
                            {else/}
                            <button type="button" class="layui-btn layui-btn-sm" data-type="unable">发视频</button>
                            {/if}
                            {/if}

                            <!--{if condition="$is_weibo eq 1 and $status eq ''"}
                            <button type="button" class="layui-btn layui-btn-sm" onclick="$eb.createModalFrame(this.innerText,'{:Url('create_weibo', ['type'=>$type])}')">发动态</button>
                            {/if}-->

                            {eq name="status" value="2"}
                            <button class="layui-btn layui-btn-sm" data-type="verify">批量审核</button>
                            {/eq}

                            {neq name="status" value="-1"}
                            <button class="layui-btn layui-btn-sm" onclick="delete_forum()">批量删除</button>
                            {/neq}

                            {eq name="status" value="2"}
                            <button class="layui-btn layui-btn-sm" onclick="audit()">批量驳回</button>
                            {/eq}

                            {eq name="status" value="-1"}
                            <button class="layui-btn layui-btn-sm" data-type="restore">还原</button>
                            <!--<button class="layui-btn layui-btn-sm" data-type="remove">清理</button>-->
                            {/eq}
                            <!-- <button class="layui-btn layui-btn-sm" data-type="verify">批量移动</button> -->
                            <!-- <button class="layui-btn layui-btn-sm" data-type="verify">批量置顶</button> -->
                            <!-- <button class="layui-btn layui-btn-sm" data-type="verify">批量加精</button> -->
                        </div>
                        <div style="position:relative">
                            <!-- <button type="button" class="layui-btn layui-btn-sm"  onclick="$eb.createModalFrame('','{:Url('add_comment')}')">注入 <span class="caret"></span></button> -->
                            {if condition="in_array('user_vest',$open_list)"}
                            <button type="button" class="layui-btn layui-btn-sm" style="width:80px" onclick="dropdown(this)">注入评论 <span class="caret"></span></button>
                            {else/}
                            <button type="button" class="layui-btn layui-btn-sm" style="width:80px" id="unable">注入评论 <span class="caret"></span></button>
                            {/if}

                            <ul class="layui-nav-child layui-anim layui-anim-upbit" style="line-height:20px;padding:10px;width:80px">
                                <li style="height:20px">
                                    <a lay-event='set_top' href="javascript:void(0);" onclick="injection()" style="font-size:12px">
                                        批量注入
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <!--用户信息-->
                    <script type="text/html" id="userinfo">
                        {{d.nickname==null ? '暂无信息':d.nickname}}/{{d.author_uid}}
                    </script>

                    <!--订单状态-->
                    <script type="text/html" id="status">
                        {{d.status_name}}
                    </script>

                    <script type="text/html" id="title">
                        <div style="width:218px;display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 1;overflow: hidden;">{{d.title}}</div>
                        <p><a>[{{d.forum_name}}]</a> | <a>[{{d.class_name}}]</a></p>
                    </script>
                    <script type="text/html" id="content">
                        {{# if(d.type == 4){ }}
                        <div style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 3;overflow: hidden;">{{d.summary}}</div>
                        {{# }else{ }}
                        <div style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 3;overflow: hidden;">{{d.content}}</div>
                        {{# } }}
                        {{# if(d.type == 6){ }}
                        <!-- <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('查看视频','{:Url('edit_news')}?id={{d.id}}')">
                            <i class="fa fa-paste"></i> 查看视频
                        </button> -->
                        <!-- 增加优化查看视频详情的功能，弹出video窗口 -->
                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('查看视频','{:Url('view_video')}?id={{d.id}}&type=1')">
                            <i class="fa fa-paste"></i> 查看视频
                        </button>
                        {{# } }}
                    </script>

                    <script type="text/html" id="act_common">
                        {if condition="$type eq 4"}
                        {{# if(d.status > 0){ }}
                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('编辑资讯内容','{:Url('edit_news')}?id={{d.id}}')">
                            <i class="fa fa-paste"></i> 编辑
                        </button>
                        {{# } }}
                        <!-- <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('编辑资讯详情','{:Url('view_news')}?id={{d.id}}')">
                            <i class="fa fa-paste"></i> 详情
                        </button> -->
                        <!-- 增加优化查看资讯详情的功能 -->
                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('查看资讯详情','{:Url('view_news')}?id={{d.id}}')">
                            <i class="fa fa-paste"></i> 详情
                        </button>
                        {elseif condition="$type eq 6"}
                        {{# if(d.status > 0){ }}
                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('编辑视频内容','{:Url('edit_video')}?id={{d.id}}')">
                            <i class="fa fa-paste"></i> 编辑
                        </button>
                        {{# } }}
                        <!-- <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('编辑视频详情','{:Url('view_video')}?id={{d.id}}')">
                            <i class="fa fa-paste"></i> 详情
                        </button> -->
                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('查看视频详情','{:Url('view_video')}?id={{d.id}}&type=2')">
                            <i class="fa fa-paste"></i> 详情
                        </button>
                            {else/}

                        {{# if(d.is_weibo == 1){ }}
                        {{# if(d.status > 0){ }}
                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('编辑动态内容','{:Url('edit_weibo')}?id={{d.id}}')">
                            <i class="fa fa-paste"></i> 编辑
                        </button>
                        {{# } }}
                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('查看动态详情','{:Url('view_weibo')}?id={{d.id}}')">
                            <i class="fa fa-paste"></i> 详情
                        </button>
                        {{# }else{ }}
                        {{# if(d.status > 0){ }}
                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('编辑帖子内容','{:Url('edit')}?id={{d.id}}')">
                            <i class="fa fa-paste"></i> 编辑
                        </button>
                        {{# } }}
                        <!-- <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('编辑帖子详情','{:Url('view')}?id={{d.id}}')">
                            <i class="fa fa-paste"></i> 详情
                        </button> -->
                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('查看帖子详情','{:Url('view_thread')}?id={{d.id}}')">
                            <i class="fa fa-paste"></i> 详情
                        </button>
                        {{# } }}


                        {/if}
                        {{# if(d.status == 1){ }}
                        <button type="button" class="layui-btn layui-btn-xs" onclick="dropdown(this)">操作 <span class="caret"></span></button>
                        <ul class="layui-nav-child layui-anim layui-anim-upbit">
                            <li>
                                {{# if(d.is_top == 1){ }}
                                <a lay-event='set_top' href="javascript:void(0);" >
                                    取消置顶
                                </a>
                                {{# }else{ }}
                                <a onclick="$eb.createModalFrame('置顶','{:Url('set_management')}?id={{d.id}}&type=top'),{w:600,h:600}">
                                    置顶
                                </a>
                                {{# } }}
                            </li>
                            <li>
                                <a lay-event='set_essence' href="javascript:void(0);" >
                                    {{# if(d.is_essence == 1){ }}
                                    取消加精
                                    {{# }else{ }}
                                    加精
                                    {{# } }}
                                </a>
                            </li>
                            <li>
                                {{# if(d.is_recommend == 1){ }}
                                    <a lay-event='set_recommend' href="javascript:void(0);" >
                                        取消推荐
                                    </a>
                                {{# }else{ }}
                                    <a onclick="$eb.createModalFrame('推荐','{:Url('set_management')}?id={{d.id}}&type=recommend'),{w:600,h:600}">
                                        推荐
                                    </a>
                                {{# } }}
                            </li>
                            <li>
                                {{# if(d.detail_top == 1){ }}
                                <a lay-event='set_detail_top' href="javascript:void(0);" >
                                    取消详情置顶
                                </a>
                                {{# }else{ }}
                                <a onclick="$eb.createModalFrame('详情置顶','{:Url('set_management')}?id={{d.id}}&type=detail_top'),{w:600,h:600}">
                                    详情置顶
                                </a>
                                {{# } }}
                            </li>
                            <li>
                                {{# if(d.index_top == 1){ }}
                                <a lay-event='set_index_top' href="javascript:void(0);" >
                                    取消首页置顶
                                </a>
                                {{# }else{ }}
                                <a onclick="$eb.createModalFrame('首页置顶','{:Url('set_management')}?id={{d.id}}&type=index_top'),{w:600,h:600}">
                                    首页置顶
                                </a>
                                {{# } }}
                            </li>
                            <li>
                                <a href="{:Url('com.com_post/index')}?tid={{d.id}}" >
                                    查看评论
                                </a>
                            </li>
                        </ul>
                        {{#  }else if(d.status==2){ }}
                        <button type="button" class="layui-btn layui-btn-xs" onclick="dropdown(this)">操作 <span class="caret"></span></button>
                        <ul class="layui-nav-child layui-anim layui-anim-upbit position">
                            <li>
                                <a lay-event='verify' href="javascript:void(0);" >
                                 审核通过
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" onclick="$eb.createModalFrame('理由','{:Url('audit')}?id={{d.id}}')">驳回</a>
                            </li>
                        </ul>
                        {{# } }}
                        {{# if(d.status > -1){ }}
                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('理由','{:Url('delete_forum')}?id={{d.id}}'),{w:600,h:600}">
                            <i class="fa fa-warning"></i> 删除
                        </button>
                        {{# }else{ }}
                        <button class="layui-btn layui-btn-xs" lay-event="restore"><i class="fa fa-paste"></i>还原</button>
                        <!--<button class="layui-btn layui-btn-xs" lay-event="remove"><i class="fa fa-paste"></i>清理</button>-->
                        {{# } }}
                    </script>
                    <script type="text/html" id="view_count">
                        <p>浏览量:{{d.view_count}} </p>
                        {{# if(d.type == 1){ }}
                        <p>虚拟浏览量:{{d.false_view}} </p>
                        {{# } }}
                        <p>评论数:{{d.reply_count}}</p>
                        <p>分享数:{{d.share_count}} </p>
                        <p>收藏数:{{d.collect_count}} </p>
                    </script>
                    <script type="text/html" id="is_top_name">
                        <p>是否置顶:{{d.is_top_name}} </p>
                        <p>是否加精:{{d.is_essence_name}}</p>
                        <p>是否推荐:{{d.is_recommend_name}} </p>
                    </script>
                    <script type="text/html" id="operation_uid">
                        {{# if(d.operation_uid>0){ }}
                        <p>{{d.operation_nickname}}</p>
                        <p>{{d.operation_identity}}</p>
                        {{# }else{ }}
                        {{# } }}
                    </script>
                    <script type="text/html" id="update_time">
                        {{# if(d.update_time!='1970-01-01 08:00:00'){ }}
                       {{d.update_time}}
                        {{# }else{ }}
                        无
                        {{# } }}
                    </script>
                    <script type="text/html" id="act_need_verify">
                        <button class="layui-btn layui-btn-xs" lay-event='verify'>
                            <i class="fa fa-warning"></i> 审核通过
                        </button>
                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('理由','{:Url('audit')}?id={{d.id}}')">
                            <i class="fa fa-warning"></i> 驳回
                        </button>
                    </script>
                    <script type="text/html" id="act_band">
                        <button class="layui-btn layui-btn-xs" lay-event='verify'>
                            <i class="fa fa-warning"></i> 审核通过
                        </button>
                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('理由','{:Url('delete_forum')}?id={{d.id}}'),{w:600,h:600}">
                            <i class="fa fa-warning"></i> 删除
                        </button>
                    </script>
                    <script type="text/html" id="act_draft">
                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('编辑帖子内容','{:Url('edit')}?id={{d.id}}')">
                            <i class="fa fa-paste"></i> 编辑
                        </button>
                        <button type="button" class="layui-btn layui-btn-xs" onclick="dropdown(this)">操作 <span class="caret"></span></button>
                        <ul class="layui-nav-child layui-anim layui-anim-upbit">
                            <li>
                                <a lay-event='to_verify' href="javascript:void(0);" >
                                    <i class="fa fa-paste"></i> 提交审核
                                </a>
                            </li>
                            <li>
                                <a lay-event='delstor' href="javascript:void(0);" >
                                    <i class="fa fa-warning"></i> 移到回收站
                                </a>
                            </li>
                        </ul>
                    </script>
                    <script type="text/html" id="act_recycle">
                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('编辑帖子详情','{:Url('view')}?id={{d.id}}')">
                            <i class="fa fa-paste"></i> 详情
                        </button>
                    </script>
                    <script type="text/html" id="act">
                        <button type="button" class="layui-btn layui-btn-xs" onclick="dropdown(this)">操作 <span class="caret"></span></button>
                        <ul class="layui-nav-child layui-anim layui-anim-upbit">
                            <li>
                                <a href="javascript:void(0);" onclick="$eb.createModalFrame('删除帖子','{:Url('order_info')}?oid={{d.id}}')">
                                    <i class="fa fa-file-text"></i> 删除帖子
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" onclick="$eb.createModalFrame('编辑帖子内容','{:Url('edit')}?id={{d.id}}')">
                                    <i class="fa fa-edit"></i> 编辑帖子
                                </a>
                            </li>
                            <li>
                                <a lay-event='marke' href="javascript:void(0);" >
                                    <i class="fa fa-paste"></i> 置顶帖子
                                </a>
                            </li>
                            <li>
                                <a lay-event='marke' href="javascript:void(0);" >
                                    <i class="fa fa-paste"></i> 加精帖子
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" onclick="$eb.createModalFrame('查看回帖','{:Url('order_status')}?oid={{d.id}}')">
                                    <i class="fa fa-newspaper-o"></i> 查看回帖
                                </a>
                            </li>
                        </ul>
                    </script>
                </div>
            </div>
        </div>
    </div>
    <!--end-->
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
{/block}
{block name="script"}
<script>
    var status = '<?= $status?>';
    var type = '<?= $type?>';
    var is_weibo = '<?= $is_weibo?>';
    layList.tableList('List',"{:Url('thread_list',['type'=>$type,'status'=>$status,'is_weibo'=>$is_weibo,'real_name'=>$real_name,'oid'=>$oid])}",function (){
        switch(parseInt(status)){
            case 1:
                var join = [
                    {type:'checkbox'},
                    {field: 'id', title: 'ID', event:'id',width:'3%'},
                    {field: 'title', title: '标题',templet:'#title',width:'10%'},
                    {field: 'content', title: '内容',templet:'#content',width:'16%'},
                    {field: 'nickname', title: '作者',templet:'#userinfo',width:'6%'},
                    {field: 'forum_name', title: '所属版块',width:'6%'},
                    {field: 'class_name', title: '所属分类',width:'6%'},
                    {field: 'create_time', title: '发帖时间',width:'9%',sort: true},
                    {field: 'update_time', title: '更新时间',width:'9%',templet:'#update_time',sort: true},
                    {field: 'status_name', title: '状态',width:'6%'},
                    {field: 'view_count', title: '数据统计',templet:'#view_count',width:'5%'},
                    {field: 'is_top_name', title: '运营',templet:'#is_top_name'},
                    {field: 'operation_uid', title: '操作人',templet:'#operation_uid'},
                    {field: 'right', title: '操作',align:'center',toolbar:'#act_common',width:'9%'},
                ];
                break;
            case 2:
                var join = [
                    {type:'checkbox'},
                    {field: 'id', title: 'ID', event:'id',width:'3%'},
                    {field: 'title', title: '标题',templet:'#title',width:'10%'},
                    {field: 'content', title: '内容',templet:'#content',width:'16%'},
                    {field: 'nickname', title: '作者',templet:'#userinfo',width:'6%'},
                    {field: 'forum_name', title: '所属版块',width:'6%'},
                    {field: 'class_name', title: '所属分类',width:'6%'},
                    {field: 'create_time', title: '发帖时间',width:'9%',sort: true},
                    {field: 'update_time', title: '更新时间',width:'9%',templet:'#update_time',sort: true},
                    {field: 'status_name', title: '状态',width:'6%'},
                    {field: 'view_count', title: '数据统计',templet:'#view_count',width:'5%'},
                    {field: 'is_top_name', title: '运营',templet:'#is_top_name'},
                    {field: 'operation_uid', title: '操作人',templet:'#operation_uid'},
                    {field: 'right', title: '操作',align:'center',toolbar:'#act_common',width:'9%'},
                ];
                break;
            case 3:
                var join = [
                    {type:'checkbox'},
                    {field: 'id', title: 'ID', event:'id',width:'3%'},
                    {field: 'title', title: '标题',templet:'#title',width:'10%'},
                    {field: 'content', title: '内容',templet:'#content',width:'16%'},
                    {field: 'nickname', title: '作者',templet:'#userinfo',width:'6%'},
                    {field: 'forum_name', title: '所属版块',width:'6%'},
                    {field: 'class_name', title: '所属分类',width:'6%'},
                    {field: 'create_time', title: '发帖时间',width:'9%',sort: true},
                    {field: 'update_time', title: '更新时间',width:'9%',templet:'#update_time',sort: true},
                    {field: 'status_name', title: '状态',width:'6%'},
                    {field: 'view_count', title: '数据统计',templet:'#view_count',width:'5%'},
                    {field: 'is_top_name', title: '运营',templet:'#is_top_name'},
                    {field: 'operation_uid', title: '操作人',templet:'#operation_uid'},
                    {field: 'right', title: '操作',align:'center',toolbar:'#act_common',width:'9%'},
                ];
                break;
            case 0:
                var join = [
                    {type:'checkbox'},
                    {field: 'id', title: 'ID', event:'id',width:'3%'},
                    {field: 'title', title: '标题',templet:'#title',width:'10%'},
                    {field: 'content', title: '内容',templet:'#content',width:'16%'},
                    {field: 'nickname', title: '作者',templet:'#userinfo',width:'6%'},
                    {field: 'forum_name', title: '所属版块',width:'6%'},
                    {field: 'class_name', title: '所属分类',width:'6%'},
                    {field: 'create_time', title: '发帖时间',width:'9%',sort: true},
                    {field: 'update_time', title: '更新时间',width:'9%',templet:'#update_time',sort: true},
                    {field: 'status_name', title: '状态',width:'6%'},
                    {field: 'view_count', title: '数据统计',templet:'#view_count',width:'5%'},
                    {field: 'is_top_name', title: '运营',templet:'#is_top_name'},
                    {field: 'operation_uid', title: '操作人',templet:'#operation_uid'},
                    {field: 'right', title: '操作',align:'center',toolbar:'#act_common',width:'9%'},
                ];
                break;
            case -1:
                var join = [
                    {type:'checkbox'},
                    {field: 'id', title: 'ID', event:'id',width:'3%'},
                    {field: 'title', title: '标题',templet:'#title',width:'10%'},
                    {field: 'content', title: '内容',templet:'#content',width:'16%'},
                    {field: 'nickname', title: '作者',templet:'#userinfo',width:'6%'},
                    {field: 'forum_name', title: '所属版块',width:'6%'},
                    {field: 'class_name', title: '所属分类',width:'6%'},
                    {field: 'create_time', title: '发帖时间',width:'9%',sort: true},
                    {field: 'update_time', title: '更新时间',width:'9%',templet:'#update_time',sort: true},
                    {field: 'status_name', title: '状态',width:'6%'},
                    {field: 'view_count', title: '数据统计',templet:'#view_count',width:'5%'},
                    {field: 'is_top_name', title: '运营',templet:'#is_top_name'},
                    {field: 'operation_uid', title: '操作人',templet:'#operation_uid'},
                    {field: 'right', title: '操作',align:'center',toolbar:'#act_common',width:'9%'},
                ];
                break;
            default:
                switch(parseInt(type)){
                    case 1:
                        var join = [
                            {type:'checkbox'},
                            {field: 'id', title: 'ID', event:'id',width:'3%'},
                            {field: 'title', title: '标题',templet:'#title',width:'10%'},
                            {field: 'content', title: '内容',templet:'#content',width:'16%'},
                            {field: 'nickname', title: '作者',templet:'#userinfo',width:'6%'},
                            {field: 'forum_name', title: '所属版块',width:'6%'},
                            {field: 'class_name', title: '所属分类',width:'6%'},
                            {field: 'create_time', title: '发帖时间',width:'9%',sort: true},
                            {field: 'update_time', title: '更新时间',width:'9%',templet:'#update_time',sort: true},
                            {field: 'status_name', title: '状态',width:'6%'},
                            {field: 'view_count', title: '数据统计',templet:'#view_count',width:'5%'},
                            {field: 'is_top_name', title: '运营',templet:'#is_top_name'},
                            {field: 'operation_uid', title: '操作人',templet:'#operation_uid'},
                            {field: 'right', title: '操作',align:'center',toolbar:'#act_common',width:'9%'},
                        ];
                        break;
                    case 4:
                        var join = [
                            {type:'checkbox'},
                            {field: 'id', title: 'ID', event:'id',width:'3%'},
                            {field: 'title', title: '标题',templet:'#title',width:'10%'},
                            {field: 'content', title: '内容',templet:'#content',width:'16%'},
                            {field: 'nickname', title: '作者',templet:'#userinfo',width:'6%'},
                            {field: 'forum_name', title: '所属版块',width:'6%'},
                            {field: 'class_name', title: '所属分类',width:'6%'},
                            {field: 'create_time', title: '发帖时间',width:'9%',sort: true},
                            {field: 'update_time', title: '更新时间',width:'9%',templet:'#update_time',sort: true},
                            {field: 'status_name', title: '状态',width:'6%'},
                            {field: 'view_count', title: '数据统计',templet:'#view_count',width:'5%'},
                            {field: 'is_top_name', title: '运营',templet:'#is_top_name'},
                            {field: 'operation_uid', title: '操作人',templet:'#operation_uid'},
                            {field: 'right', title: '操作',align:'center',toolbar:'#act_common',width:'9%'},
                        ];
                        break;
                    case 6:
                        var join = [
                            {type:'checkbox'},
                            {field: 'id', title: 'ID', event:'id',width:'3%'},
                            {field: 'title', title: '标题',templet:'#title',width:'10%'},
                            {field: 'content', title: '内容',templet:'#content',width:'16%'},
                            {field: 'nickname', title: '作者',templet:'#userinfo',width:'6%'},
                            {field: 'forum_name', title: '所属版块',width:'6%'},
                            {field: 'class_name', title: '所属分类',width:'6%'},
                            {field: 'create_time', title: '发帖时间',width:'9%',sort: true},
                            {field: 'update_time', title: '更新时间',width:'9%',templet:'#update_time',sort: true},
                            {field: 'status_name', title: '状态',width:'6%'},
                            {field: 'view_count', title: '数据统计',templet:'#view_count',width:'5%'},
                            {field: 'is_top_name', title: '运营',templet:'#is_top_name'},
                            {field: 'operation_uid', title: '操作人',templet:'#operation_uid'},
                            {field: 'right', title: '操作',align:'center',toolbar:'#act_common',width:'9%'},
                        ];
                        break;
                }
                break;
        }
        return join;
    });
    layList.tool(function (event,data,obj) {
        switch (event) {
            case 'set_top':
                var url=layList.U({c:'com.com_thread',a:'quick_edit',q:{id:data.id, field:'is_top', value:data.is_top== 1? 0:1}});
                if(data.is_top){
                    var code = {title:"操作提示",text:"你确定要取消置顶吗？",type:'info',confirm:'是的，我要取消置顶'};
                }else{
                    var code = {title:"操作提示",text:"你确定要置顶吗？",type:'info',confirm:'是的，我要置顶'};
                }
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success', '');
                            layList.reload();
                        }else
                            return Promise.reject(res.data.msg || '审核失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                }, code)
                break;
            case 'set_essence':
                var url=layList.U({c:'com.com_thread',a:'quick_edit',q:{id:data.id, field:'is_essence', value:data.is_essence== 1? 0:1}});
                if(data.is_essence){
                    var code = {title:"操作提示",text:"你确定要取消加精吗？",type:'info',confirm:'是的，我要取消加精'};
                }else{
                    var code = {title:"操作提示",text:"你确定要加精吗？",type:'info',confirm:'是的，我要加精'};
                }
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success', '');
                            layList.reload();
                        }else
                            return Promise.reject(res.data.msg || '审核失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                }, code)
                break;
            case 'set_recommend':
                var url=layList.U({c:'com.com_thread',a:'quick_edit_recommend',q:{id:data.id, field:'is_recommend', value:data.is_recommend== 1? 0:1}});
                if(data.is_recommend){
                    var code = {title:"操作提示",text:"你确定要取消推荐吗？",type:'info',confirm:'是的，我要取消推荐'};
                }else{
                    var code = {title:"操作提示",text:"你确定要推荐吗？",type:'info',confirm:'是的，我要推荐'};
                }
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success', '');
                            layList.reload();
                        }else
                            return Promise.reject(res.data.msg || '审核失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                }, code)
                break;
            case 'set_detail_top':
                var url=layList.U({c:'com.com_thread',a:'quick_edit_detail_top',q:{id:data.id, field:'detail_top', value:data.detail_top== 1? 0:1}});
                if(data.detail_top){
                    var code = {title:"操作提示",text:"你确定要取消详情置顶吗？",type:'info',confirm:'是的，我要取消详情置顶'};
                }else{
                    var code = {title:"操作提示",text:"你确定要详情置顶吗？",type:'info',confirm:'是的，我要详情置顶'};
                }
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success', '');
                            layList.reload();
                        }else
                            return Promise.reject(res.data.msg || '置顶失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                }, code)
                break;
            case 'set_index_top':
                var url=layList.U({c:'com.com_thread',a:'quick_edit_index_top',q:{id:data.id, field:'index_top', value:data.index_top== 1? 0:1}});
                if(data.index_top){
                    var code = {title:"操作提示",text:"你确定要取消首页置顶吗？",type:'info',confirm:'是的，我要取消首页置顶'};
                }else{
                    var code = {title:"操作提示",text:"你确定要首页置顶吗？",type:'info',confirm:'是的，我要首页置顶'};
                }
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success', '');
                            layList.reload();
                        }else
                            return Promise.reject(res.data.msg || '置顶失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                }, code)
                break;
            case 'verify':
                var url=layList.U({c:'com.com_thread',a:'quick_edit',q:{id:data.id, field:'status', value:1}});
                var code = {title:"操作提示",text:"你确定要审核通过吗？",type:'info',confirm:'是的，我要审核通过'};
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success', '');
                            obj.del();
                        }else
                            return Promise.reject(res.data.msg || '审核失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                }, code)
                break;
            case 'to_verify':
                var url=layList.U({c:'com.com_thread',a:'quick_edit',q:{id:data.id, field:'status', value:2}});
                var code = {title:"操作提示",text:"你确定要提交审核该版块吗？",type:'info',confirm:'是的，我要提交审核'};
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success', '');
                            obj.del();
                        }else
                            return Promise.reject(res.data.msg || '审核失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                }, code)
                break;
            case 'delstor':
                var url=layList.U({c:'com.com_thread',a:'quick_edit',q:{id:data.id, field:'status', value:-1}});
                var code = {title:"是否要删除该帖子",text:"删除后可在回收站中还原",confirm:'是的，我要删除'};
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success', '');
                            obj.del();
                        }else
                            return Promise.reject(res.data.msg || '删除失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                },code)
                break;
            case 'set_verify_fail':
                var url=layList.U({c:'com.com_thread',a:'quick_edit',q:{id:data.id, field:'status', value: 0}});
                var code = {title:"操作提示",text:"确定驳回吗？",type:'info',confirm:'是的，驳回'};
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
            case 'remove':
                var code = {title:"操作提示",text:"清空后，数据将同步清空，无法恢复，请慎重考虑",type:'info',confirm:'确定'};
                $eb.$swal('delete',function(){
                    layList.basePost(layList.Url({c:'com.com_thread',a:'remove'}),{ids:data.id},function (res) {
                        layList.msg(res.msg);
                        layList.reload();
                    });
                }, code)
                break;
            case 'restore':
                var code = {title:"操作提示",text:"确定还原吗？",type:'info',confirm:'是的，还原'};
                $eb.$swal('delete',function(){
                    layList.basePost(layList.Url({c:'com.com_thread',a:'restore'}),{ids:data.id},function (res) {
                        layList.msg(res.msg);
                        layList.reload();
                    });
                }, code)
                break;
        }
    })

    //自定义方法
    var action={
        move: function(){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                $eb.createModalFrame('迁移帖子', layList.Url({c:'com.com_thread',a:'move', p:{
                    ids:ids
                }}));
            }else{
                layList.msg('请选择要迁移的帖子');
            }
        },
        // 批量删除
        delete:function(field,id,value){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                var code = {title:"操作提示",text:"确定批量删除帖子吗？",type:'info',confirm:'是的，删除'};
                $eb.$swal('delete',function(){
                    layList.basePost(layList.Url({c:'com.com_thread',a:'delete'}),{ids:ids},function (res) {
                        layList.msg(res.msg);
                        layList.reload();
                    });
                },code);
            }else{
                layList.msg('请选择要删除的帖子');
            }
        },
        // 审核
        verify:function(){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                var code = {title:"操作提示",text:"确定审核通过吗？",type:'info',confirm:'是的，审核通过'};
                $eb.$swal('delete',function(){
                    layList.basePost(layList.Url({c:'com.com_thread',a:'verify'}),{ids:ids},function (res) {
                        layList.msg(res.msg);
                        layList.reload();
                    });
                },code);
            }else{
                layList.msg('请选择要审核的版块');
            }
        },
        // 清理
        remove:function(){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                var code = {title:"操作提示",text:"清空后，数据将同步清空，无法恢复，请慎重考虑",type:'info',confirm:'确定'};
                $eb.$swal('delete',function(){
                    layList.basePost(layList.Url({c:'com.com_thread',a:'remove'}),{ids:ids},function (res) {
                        layList.msg(res.msg);
                        layList.reload();
                    });
                },code);
            }else{
                layList.msg('请选择要清理的帖子');
            }
        },
        restore:function(){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                var code = {title:"操作提示",text:"确定还原吗？",type:'info',confirm:'是的，还原'};
                $eb.$swal('delete',function(){
                    layList.basePost(layList.Url({c:'com.com_thread',a:'restore'}),{ids:ids},function (res) {
                        layList.msg(res.msg);
                        layList.reload();
                    });
                },code);
            }else{
                layList.msg('请选择要还原的帖子');
            }
        },
        unable:function(){
            var code = {title:"提示",text:"该功能未开通或已过期，如需开通，请联系客服！",type:'info',confirm:'联系客服',cancel:'取消',confirmBtnColor:'#0ca6f2'};
            $eb.$swal('delete',function(){
                $eb.createModalFrame('联系客服','https://osxbe.demo.opensns.cn/auth/Index/tip_box.html',{h:600,w:700})
            }, code)
        },
    };
    $("#unable").on("click",function () {
        var code = {title:"提示",text:"该功能未开通或已过期，如需开通，请联系客服！",type:'info',confirm:'联系客服',cancel:'取消',confirmBtnColor:'#0ca6f2'};
        $eb.$swal('delete',function(){
            $eb.createModalFrame('联系客服','https://osxbe.demo.opensns.cn/auth/Index/tip_box.html',{h:600,w:700})
        }, code)
    })
    //多选事件绑定
    $('.layui-btn-container').find('button').each(function () {
        var type=$(this).data('type');
        $(this).on('click',function(){
            action[type] && action[type]();
        })
    });

    //下拉框
    $(document).click(function (e) {
        $('.layui-nav-child').hide();
    })
    // 注入
    function injection(){
        var ids=layList.getCheckData().getIds('id');

        if(ids.length){
            var str='';
            for(var i=0;i<ids.length;i++){
                str+=ids[i]+',';
            }
            if (str.length > 0) {
                str = str.substr(0, str.length - 1);
            }

            $eb.createModalFrame('注入',"{:Url('add_comment')}?ids="+str);
            console.log(ids)
        }else{
            layList.msg('请选择要批量注入的帖子');
        }
    }

    // 批量删除
    function delete_forum(){
        var ids=layList.getCheckData().getIds('id');

        if(ids.length){
            var str='';
            for(var i=0;i<ids.length;i++){
                str+=ids[i]+',';
            }
            if (str.length > 0) {
                str = str.substr(0, str.length - 1);
            }
            $eb.createModalFrame('理由',"{:Url('delete_forum')}?id="+str);
            console.log(ids)
        }else{
            layList.msg('请选择要批量删除的帖子');
        }
    }


    // 批量删除
    function audit(){
        var ids=layList.getCheckData().getIds('id');

        if(ids.length){
            var str='';
            for(var i=0;i<ids.length;i++){
                str+=ids[i]+',';
            }
            if (str.length > 0) {
                str = str.substr(0, str.length - 1);
            }
            $eb.createModalFrame('理由',"{:Url('audit')}?id="+str);
            console.log(ids)
        }else{
            layList.msg('请选择要批量删除的帖子');
        }
    }
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
                'top': - ($(that).parents('td').height() / 2 + $(that).height() + $(that).next('ul').height()/2),
                'min-width': 'inherit',
                'position': 'absolute'
            }).toggle();
        }else{
            $(that).next('ul').css({
                'padding': 10,
                'top':$(that).parents('td').height() / 2 + $(that).height(),
                'min-width': 'inherit',
                'position': 'absolute'
            }).toggle();
        }
    }
    var real_name='<?=$real_name?>';
    var orderCount=<?=json_encode($orderCount)?>;
    require(['vue'],function(Vue) {
        new Vue({
            el: "#app",
            data: {
                orderType: [
                    {name: '全部', value: ''},
                    {name: '1.普通版式', value: 1,count:orderCount.general},
                    {name: '2.微博', value: 2,count:orderCount.pink},
                    {name: '3.朋友圈', value: 3,count:orderCount.seckill},
                    {name: '4.资讯', value: 4,count:orderCount.bargain},
                    {name: '5.活动', value: 4,count:orderCount.bargain},
                    {name: '6.视频横版（PGC为主）', value: 4,count:orderCount.bargain},
                    {name: '7.视频竖版（UGC为主）', value: 4,count:orderCount.bargain},

                ],
                orderStatus: [
                    {name: '全部', value: ''},
                    {name: '正常', value: 1,count:orderCount.wz},
                    {name: '禁用', value: 0,count:orderCount.wf,class:true},
                    {name: '待审核', value: 2,count:orderCount.ds},
                    {name: '已删除', value: -1,count:orderCount.dp},
                ],
                dataList: [
                    {name: '全部', value: ''},
                    {name: '昨天', value: 'yesterday'},
                    {name: '今天', value: 'today'},
                    {name: '本周', value: 'week'},
                    {name: '本月', value: 'month'},
                    {name: '本季度', value: 'quarter'},
                    {name: '本年', value: 'year'},
                ],
                where:{
                    is_top:'',
                    data:'',
                    status:'',
                    type:'',
                    fid:'',
                    cid:'',
                    uid:'',
                    is_essence:'',
                    is_recommend:'',
                    real_name:real_name || '',
                    excel:0,
                    comment_num:'',
                },
                showtime: false,
            },
            watch: {

            },
            methods: {
                setFid:function(value){
                    var that = this;
                    this.where.fid = value;
                },
                setData:function(item){
                    var that=this;
                    if(item.is_zd==true){
                        that.showtime=true;
                        this.where.data=this.$refs.date_time.innerText;
                    }else{
                        this.showtime=false;
                        this.where.data=item.value;
                    }
                },
                search:function () {
                    this.where.excel=0;
                    layList.reload(this.where,true);
                },
                refresh:function () {
                    $('[data-type="data"]').children(":first").click();
                    layList.reload();
                },
                excel:function () {
                    this.where.excel=1;
                    location.href=layList.U({c:'order.store_order',a:'order_list',q:this.where});
                },
                get_class:function(){
                    console.log(this.where.fid);
                }
            },
            mounted:function () {
                var that=this;
                layList.laydate.render({
                    elem:this.$refs.date_time,
                    trigger:'click',
                    eventElem:this.$refs.time,
                    range:true,
                    change:function (value){
                        that.where.data=value;
                    }
                });
                layList.form.render();
                layList.form.on("select(fid)", function (data) {
                    that.where.fid = data.value;
                    if(data.value){
                        var url = layList.U({c:'com.com_thread',a:'getThreadClassByForum',q:{fid:data.value}});
                        $.getJSON(url,function(result){
                            $('#cid').html('<option value="">全部</option>');
                            $.each(result.data, function(i, field){
                                $('#cid').append(`<option value="${field.value}">${field.label}</option>`);
                            });
                            layList.form.render('select');//最后记得渲染
                        });
                    }else{
                        $('#cid').html('<option value="">全部</option>');
                    }
                });
                layList.form.on("select(cid)", function (data) {
                    that.where.cid = data.value;
                });
                layList.form.on("select(is_top)", function (data) {
                    that.where.is_top = data.value;
                });
                layList.form.on("select(is_essence)", function (data) {
                    that.where.is_essence = data.value;
                });
                layList.form.on("select(is_recommend)", function (data) {
                    that.where.is_recommend = data.value;
                });
                layList.form.on("select(detail_top)", function (data) {
                    that.where.detail_top = data.value;
                });
                layList.form.on("select(index_top)", function (data) {
                    that.where.index_top = data.value;
                });
                layList.form.on("select(comment_num)", function (data) {
                    that.where.comment_num = data.value;
                });
                // 定义查看视频点击按钮事件
                $('body').on("click", '.video-btn', function(e) {
                    var url = e.target.attr('data-url');
                    $('#video-box').css('display', 'block');
                    $('#video').attr('src', url);
                })
            }
        })
    });
    
</script>
{/block}