{extend name="public/container"}
{block name="head_top"}
<link rel="stylesheet" href="{__PLUG_PATH}formselects/formSelects-v4.css">
<script src="{__PLUG_PATH}formselects/formSelects-v4.min.js"></script>
<script src="{__PLUG_PATH}sweetalert2/sweetalert2.all.min.js"></script>
<style>
    input {
        width: 200px !important;
    }
</style>
{/block}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-content">
                <form class="layui-form" action="" style="padding:20px;">
                    <fieldset>
                        <legend><a name="input">通过搜索昵称、UID快速选择用户</a></legend>
                    </fieldset>
                    <select name="uids" xm-select="user_select" xm-select-search="{:Url('find_users')}">
                        <option value="">请选择用户</option>
                    </select>
                    <br/>
                    <button class="btn btn-primary" data-url="{:Url('recommend_user_all')}" id="save" type="button"><i class="fa  fa-arrow-circle-o-right"></i>
                        设置推荐
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
{/block}
{block name="script"}
<script src="{__FRAME_PATH}js/toast-js.js"></script>
<script>
  var form = layui.form;
  form.render();
  var formSelects = layui.formSelects;
  formSelects.config('user_select', {
    type: 'get',                //请求方式: post, get, put, delete...
    searchName: 'nickname',      //自定义搜索内容的key值
    clearInput: true,          //当有搜索内容时, 点击选项是否清空搜索内容, 默认不清空
  }, false);


  $('#save').on('click', function () {
    var userId = $('[name=uids]').val();
    var url =$(this).data('url');
    if(userId){
      $eb.axios.post(url,{uid:userId}).then(function (res) {
        if (res.status === 200 && res.data.code === 200) {
          Toast.success(res.data.msg);
          setTimeout(function (e) {
            parent.$(".J_iframe:visible")[0].contentWindow.location.reload();
            parent.layer.close(parent.layer.getFrameIndex(window.name));
          },600)
        } else {
          Toast.error(res.data.msg);
        }
      }).catch(function (err) {
        $eb.$swal('error', err);
      });
    }else {
      Toast.error("请选择用户");
    }
  });
</script>
{/block}
