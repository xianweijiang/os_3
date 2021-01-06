<?php if (!defined('THINK_PATH')) exit(); /*a:6:{s:111:"/Applications/MxSrvs/www/yalian-git/osx/osx_admin/application/admin/view/ump/store_combination/combina_list.php";i:1597214754;s:93:"/Applications/MxSrvs/www/yalian-git/osx/osx_admin/application/admin/view/public/container.php";i:1597214754;s:94:"/Applications/MxSrvs/www/yalian-git/osx/osx_admin/application/admin/view/public/frame_head.php";i:1597214754;s:89:"/Applications/MxSrvs/www/yalian-git/osx/osx_admin/application/admin/view/public/style.php";i:1597214754;s:94:"/Applications/MxSrvs/www/yalian-git/osx/osx_admin/application/admin/view/public/inner_page.php";i:1597214754;s:96:"/Applications/MxSrvs/www/yalian-git/osx/osx_admin/application/admin/view/public/frame_footer.php";i:1597214754;}*/ ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if(empty($is_layui) || (($is_layui instanceof \think\Collection || $is_layui instanceof \think\Paginator ) && $is_layui->isEmpty())): ?>
    <link href="/public/system/frame/css/bootstrap.min.css?v=3.4.0" rel="stylesheet">
    <?php endif; ?>
    <link href="/public/static/plug/layui/css/layui.css" rel="stylesheet">
    <link href="/public/system/css/layui-admin.css" rel="stylesheet"></link>
    <link href="/public/system/frame/css/font-awesome.min.css?v=4.3.0" rel="stylesheet">
    <link href="/public/system/frame/css/animate.min.css" rel="stylesheet">
    <link href="/public/system/frame/css/style.min.css?v=3.0.0" rel="stylesheet">
    <!--专栏Column新增图标-->
    <link href="https://at.alicdn.com/t/font_1685608_u3j40gwewag.css" rel="stylesheet">
    <script src="/public/system/frame/js/jquery.min.js"></script>
    <script src="/public/system/frame/js/bootstrap.min.js"></script>
    <script src="/public/static/plug/layui/layui.all.js"></script>
    <script>
        $eb = parent._mpApi;
        window.controlle="<?php echo strtolower(trim(preg_replace("/[A-Z]/", "_\\0", think\Request::instance()->controller()), "_"));?>";
        window.module="<?php echo think\Request::instance()->module();?>";
    </script>



    <title></title>
    
<script src="/public/static/plug/sweetalert2/sweetalert2.all.min.js"></script>
<script src="/public/static/plug/moment.js"></script>
<link rel="stylesheet" href="/public/static/plug/daterangepicker/daterangepicker.css">
<script src="/public/static/plug/daterangepicker/daterangepicker.js"></script>

    <!--<script type="text/javascript" src="/static/plug/basket.js"></script>-->
<script type="text/javascript" src="/public/static/plug/requirejs/require.js"></script>
<?php /*  <script type="text/javascript" src="/static/plug/requirejs/require-basket-load.js"></script>  */ ?>
<script>
    var hostname = location.hostname;
    if(location.port) hostname += ':' + location.port;
    requirejs.config({
        map: {
            '*': {
                'css': '/public/static/plug/requirejs/require-css.js'
            }
        },
        shim:{
            'iview':{
                deps:['css!iviewcss']
            },
            'layer':{
                deps:['css!layercss']
            }
        },
        baseUrl:'//'+hostname+'/public/',
        paths: {
            'static':'static',
            'system':'system',
            'vue':'static/plug/vue/dist/vue.min',
            'axios':'static/plug/axios.min',
            'iview':'static/plug/iview/dist/iview.min',
            'iviewcss':'static/plug/iview/dist/styles/iview',
            'lodash':'static/plug/lodash',
            'layer':'static/plug/layer/layer',
            'layercss':'static/plug/layer/theme/default/layer',
            'jquery':'static/plug/jquery/jquery.min',
            'moment':'static/plug/moment',
            'sweetalert':'static/plug/sweetalert2/sweetalert2.all.min'

        },
        basket: {
            excludes:['system/js/index','system/util/mpVueComponent','system/util/mpVuePackage']
//            excludes:['system/util/mpFormBuilder','system/js/index','system/util/mpVueComponent','system/util/mpVuePackage']
        }
    });
</script>
<script type="text/javascript" src="/public/system/util/mpFrame.js"></script>
    
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <?php if(!$is_free_ban): ?>
    <div style="background-color: #FBE0E3;margin-top: 10px;color: #D9001B;padding: 10px 40px 10px 20px">
        <div style="display: flex;justify-content: space-between;align-items: center">
            <span>未开通该功能使用权限，如需开通，请联系客服！</span>
            <a id="contact_service" style="color: #D9001B;display: block;border: 1px solid #D9001B;padding: 7px 24px;border-radius: 5px;font-size: 13px">联系客服</a>
        </div>
    </div>
    <script>
        $("#contact_service").on("click",function () {
            $eb.createModalFrame('联系客服','https://osxbe.demo.opensns.cn/auth/Index/tip_box.html',{h:600,w:700})
        })
    </script>
    <?php endif; if(!$is_end_ban): ?>
    <div style="background-color: #FBE0E3;margin-top: 10px;color: #D9001B;padding: 10px 40px 10px 20px">
        <div style="display: flex;justify-content: space-between;align-items: center">
            <span>功能已到期，请续费后继续使用该功能！</span>
            <a id="contact_service" style="color: #D9001B;display: block;border: 1px solid #D9001B;padding: 7px 24px;border-radius: 5px;font-size: 13px">联系客服</a>
        </div>
    </div>
    <script>
        $("#contact_service").on("click",function () {
            $eb.createModalFrame('联系客服','https://osxbe.demo.opensns.cn/auth/Index/tip_box.html',{h:600,w:700})
        })
    </script>
    <?php endif; ?>

<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-content">
                <div class="row">
                    <div class="m-b m-l">
                        <form action="" class="form-inline">
                            <div class="input-group datepicker">
                                <input style="width: 200px;" type="text" id="data" class="input-sm form-control" name="data" value="<?php echo $where['data']; ?>" placeholder="请选择日期" >
                            </div>
                            <select name="status" aria-controls="editable" class="form-control input-sm">
                                <option value="">全部</option>
                                <option value="1" <?php if($where['status'] == '1'): ?>selected="selected"<?php endif; ?>>进行中</option>
                                <option value="2" <?php if($where['status'] == '2'): ?>selected="selected"<?php endif; ?>>已完成</option>
                                <option value="3" <?php if($where['status'] == '3'): ?>selected="selected"<?php endif; ?>>未完成</option>
                            </select>
                            <div class="input-group">
                                <span class="input-group-btn">
                                    <button type="submit" id="no_export" class="btn btn-sm btn-primary"> <i class="fa fa-search" ></i> 搜索</button>
                                </span>
                            </div>
                           <script>
                                $('#export').on('click',function(){
                                    $('input[name=export]').val(1);
                                });
                                $('#no_export').on('click',function(){
                                    $('input[name=export]').val(0);
                                });
                            </script>
                        </form>
                    </div>

                </div>
                <div class="table-responsive" style="overflow:visible">
                    <table class="table table-striped  table-bordered">
                        <thead>
                        <tr>
                            <th class="text-center">开团团长</th>
                            <th class="text-center">开团时间</th>
                            <th class="text-center">拼团产品</th>
                            <th class="text-center">几人团</th>
                            <th class="text-center">几人参加</th>
                            <th class="text-center">结束时间</th>
                            <th class="text-center">状态</th>
                            <th class="text-center">操作</th>

                        </tr>
                        </thead>
                        <tbody class="">
                        <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                        <tr>
                            <td class="text-center">
                                <?php echo $vo['nickname']; ?>/<?php echo $vo['uid']; ?>
                            </td>
                            <td class="text-center">
                                <?php echo date('Y-m-d H:i:s',$vo['add_time']); ?>
                            </td>
                            <td class="text-center">
                                <?php echo $vo['title']; ?>/<?php echo $vo['cid']; ?>
                            </td>
                            <td class="text-center">
                                <?php echo $vo['people']; ?>人
                            </td>
                            <td class="text-center">
                                <?php echo $vo['count_people']; ?>人
                            </td>
                            <td class="text-center">
                                <?php echo date('Y-m-d H:i:s',$vo['stop_time']); ?>
                            </td>
                            <td class="text-center">
                                <?php if($vo['status'] == 1): ?>
                                   <span style="color: #00a0e9">进行中</span>
                                <?php elseif($vo['status'] == 2): ?>
                                    <span style="color: #e933ce">已完成</span>
                                <?php elseif($vo['status'] == 3): ?>
                                    <span style="color: #2725e9">未完成</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <p><button class="btn btn-default btn-xs btn-outline" type="button" onclick="$eb.createModalFrame('查看详情','<?php echo Url('order_pink',array('id'=>$vo['id'])); ?>')"><i class="fa fa-newspaper-o"></i>查看详情</button></p>
                            </td>
                        </tr>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                        </tbody>
                    </table>
                </div>
                <link href="/public/system/frame/css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">
<div class="row">
    <div class="col-sm-6">
        <div class="dataTables_info" id="DataTables_Table_0_info" role="alert" aria-live="polite" aria-relevant="all">共 <?php echo $total; ?> 项</div>
    </div>
    <div class="col-sm-6">
        <div class="dataTables_paginate paging_simple_numbers" id="editable_paginate">
            <?php echo $page; ?>
        </div>
    </div>
</div>
            </div>
        </div>
    </div>
</div>



<script>
    $(".open_image").on('click',function (e) {
        var image = $(this).data('image');
        $eb.openImage(image);
    })
    $('.btn-danger').on('click',function (e) {
        window.t = $(this);
        var _this = $(this),url =_this.data('url');
        $eb.$swal('delete',function(){
            $eb.axios.get(url).then(function(res){
                if(res.status == 200 && res.data.code == 200) {
                    $eb.$swal('success',res.data.msg);
                }else
                    return Promise.reject(res.data.msg || '收货失败')
            }).catch(function(err){
                $eb.$swal('error',err);
            });
        },{'title':'您确定要修改收货状态吗？','text':'修改后将无法恢复,请谨慎操作！','confirm':'是的，我要修改'})
    })
    $('.offline_btn').on('click',function (e) {
        window.t = $(this);
        var _this = $(this),url =_this.data('url'),pay_price =_this.data('pay');
        $eb.$swal('delete',function(){
            $eb.axios.get(url).then(function(res){
                if(res.status == 200 && res.data.code == 200) {
                    $eb.$swal('success',res.data.msg);
                }else
                    return Promise.reject(res.data.msg || '收货失败')
            }).catch(function(err){
                $eb.$swal('error',err);
            });
        },{'title':'您确定要修改已支付'+pay_price+'元的状态吗？','text':'修改后将无法恢复,请谨慎操作！','confirm':'是的，我要修改'})
    })

    $('.add_mark').on('click',function (e) {
        var _this = $(this),url =_this.data('url'),id=_this.data('id');
        $eb.$alert('textarea',{},function (result) {
            if(result){
                $.ajax({
                    url:url,
                    data:'remark='+result+'&id='+id,
                    type:'post',
                    dataType:'json',
                    success:function (res) {
                        console.log(res);
                        if(res.code == 200) {
                            $eb.$swal('success',res.msg);
                        }else
                            $eb.$swal('error',res.msg);
                    }
                })
            }else{
                $eb.$swal('error','请输入要备注的内容');
            }
        });
    })
    $('.save_mark').on('click',function (e) {
        var _this = $(this),url =_this.data('url'),id=_this.data('id'),make=_this.data('make');
        $eb.$alert('textarea',{title:'请修改内容',value:make},function (result) {
            if(result){
                $.ajax({
                    url:url,
                    data:'remark='+result+'&id='+id,
                    type:'post',
                    dataType:'json',
                    success:function (res) {
                        console.log(res);
                        if(res.code == 200) {
                            $eb.$swal('success',res.msg);
                        }else
                            $eb.$swal('error',res.msg);
                    }
                })
            }else{
                $eb.$swal('error','请输入要备注的内容');
            }
        });
    })
    var dateInput =$('.datepicker');
    dateInput.daterangepicker({
        autoUpdateInput: false,
        "opens": "center",
        "drops": "down",
        "ranges": {
            '今天': [moment(), moment().add(1, 'days')],
            '昨天': [moment().subtract(1, 'days'), moment()],
            '上周': [moment().subtract(6, 'days'), moment()],
            '前30天': [moment().subtract(29, 'days'), moment()],
            '本月': [moment().startOf('month'), moment().endOf('month')],
            '上月': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        "locale" : {
            applyLabel : '确定',
            cancelLabel : '清空',
            fromLabel : '起始时间',
            toLabel : '结束时间',
            format : 'YYYY/MM/DD',
            customRangeLabel : '自定义',
            daysOfWeek : [ '日', '一', '二', '三', '四', '五', '六' ],
            monthNames : [ '一月', '二月', '三月', '四月', '五月', '六月',
                '七月', '八月', '九月', '十月', '十一月', '十二月' ],
            firstDay : 1
        }
    });
    dateInput.on('cancel.daterangepicker', function(ev, picker) {
        $("#data").val('');
    });
    dateInput.on('apply.daterangepicker', function(ev, picker) {
        $("#data").val(picker.startDate.format('YYYY/MM/DD') + ' - ' + picker.endDate.format('YYYY/MM/DD'));
    });
</script>


</div>
</body>
</html>
