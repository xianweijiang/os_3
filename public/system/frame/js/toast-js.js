var Toast={
    // 主要消息
    primary(data){
        layer.msg(data,{skin: 'toast-primary'})
    },
    // 信息消息
    info(data){
        layer.msg(data,{skin: 'toast-info'})
    },
    // 错误消息
    error(data){
        layer.msg(data,{skin: 'toast-error'});
    },
    // 成功消息
    success(data){
        layer.msg(data,{skin: 'toast-success'});
    },
    // 警告消息
    warning(data){
        layer.msg(data,{skin: 'toast-warning'})
    },
    // 重要消息
    important(data){
        layer.msg(data,{skin: 'toast-important'})
    },
    // 特别消息
    special(data){
        layer.msg(data,{skin: 'toast-special'})
    },
}