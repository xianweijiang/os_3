<?php

if (!function_exists('text')) {
    /**
     * text函数用于过滤标签，输出没有html的干净的文本
     * @param string text 文本内容
     * @return string 处理后内容
     */
    function text($text, $addslanshes = false)
    {
        $text = nl2br($text);
        $text = strip_tags($text);
        if ($addslanshes)
            $text = addslashes($text);
        $text = trim($text);
        return $text;
    }
}


if (!function_exists('html')) {
    /**
     * html函数用于过滤不安全的html标签，输出安全的html
     * @param string $text 待过滤的字符串
     * @param string $type 保留的标签格式
     * @return string 处理后内容
     */
    function html($text, $type = 'html')
    {
        // 无标签格式
        $text_tags = '';
        //只保留链接
        $link_tags = '<a>';
        //只保留图片
        $image_tags = '<img>';
        //只存在字体样式
        $font_tags = '<i><b><u><s><em><strong><font><big><small><sup><sub><bdo><h1><h2><h3><h4><h5><h6>';
        //标题摘要基本格式
        $base_tags = $font_tags . '<p><br><hr><a><img><map><area><pre><code><q><blockquote><acronym><cite><ins><del><center><strike>';
        //兼容Form格式
        $form_tags = $base_tags . '<form><input><textarea><button><select><optgroup><option><label><fieldset><legend>';
        //内容等允许HTML的格式
        $html_tags = $base_tags . '<ul><ol><li><dl><dd><dt><table><caption><td><th><tr><thead><tbody><tfoot><col><colgroup><div><span><object><embed><param><section>';
        //专题等全HTML格式
        $all_tags = $form_tags . $html_tags . '<!DOCTYPE><meta><html><head><title><body><base><basefont><script><noscript><applet><object><param><style><frame><frameset><noframes><iframe>';
        //过滤标签
        $text = strip_tags($text, ${$type . '_tags'});
        // 过滤攻击代码
        if ($type != 'all') {
            // 过滤危险的属性，如：过滤on事件lang js
            while (preg_match('/(<[^><]+)(ondblclick|onclick|onload|onerror|unload|onmouseover|onmouseup|onmouseout|onmousedown|onkeydown|onkeypress|onkeyup|onblur|onchange|onfocus|action|background[^-]|codebase|dynsrc|lowsrc)([^><]*)/i', $text, $mat)) {
                $text = str_ireplace($mat[0], $mat[1] . $mat[3], $text);
            }
            while (preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i', $text, $mat)) {
                $text = str_ireplace($mat[0], $mat[1] . $mat[3], $text);
            }
        }
        return $text;
    }
}


if (!function_exists('osx_input')) {
    /**
     * 获取输入数据 支持默认值和过滤，后续osx参数接收统一用该方式
     * @param string    $key 获取的变量名，（post.变量名   get.变量名）
     * @param mixed     $default 默认值
     * @param string    $filter 过滤方法，默认作为text处理
     * @return mixed
     */
    function osx_input($key = '', $default = null, $filter = 'text')
    {
        return input($key,$default,$filter);
    }
}
