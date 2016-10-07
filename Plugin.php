<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * 更换为第三方 Markdown 解析引擎.
 * 
 * @package TypechoMarkDown
 * @author 8023
 * @version 16.10.06
 * @link https://8023.Moe
 */
class TypechoMarkDown_Plugin implements Typecho_Plugin_Interface {
    /**
     * 激活插件
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate() {
        Typecho_Plugin::factory('Widget_Abstract_Contents')->markdown = array('TypechoMarkDown_Plugin', 'render');
        Typecho_Plugin::factory('Widget_Abstract_Comments')->markdown = array('TypechoMarkDown_Plugin', 'render');
    }
    
    /**
     * 禁用插件
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate() {}
    
    /**
     * 插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form) {
        /** 解析引擎选择 */
        $engine = new Typecho_Widget_Helper_Form_Element_Radio('engine',
            array(
                   'parsedown' => '<a href="https://github.com/erusev/parsedown" target="_blank">parsedown</a>',
                   'HyperDown' => '<a href="https://github.com/SegmentFault/HyperDown" target="_blank">HyperDown</a>',
                'php-markdown' => '<a href="https://github.com/michelf/php-markdown" target="_blank">php-markdown</a>',
            ),'parsedown', _t('选择 Markdown 解析引擎'), _t('请先确保文章和评论 Markdown 解析功能已经被正确启用.<br />Typecho默认的解析引擎为<a href="https://github.com/thephpleague/commonmark" target="_blank">CommonMark</a>, 路径/var/CommonMark.<br />本插件所依赖的解析引擎位于/usr/plugins/TypechoMarkDown/, 可自行去Github下载最新版替换升级.'));
        $form->addInput($engine);
        /** 超链接打开方式选择 */
        $newtab = new Typecho_Widget_Helper_Form_Element_Radio('newtab', 
            array(
                'notmind' => '不介意',
                   'true' => '是',
                  'false' => '否',
            ), 'true', _t('使用新标签页打开文内链接'), _t('想让所有链接转换为使用新标签页打开请选择是, 否则选否.<br />不介意指以Markdown解析器输出为准.'));
        $form->addInput($newtab);
    }
    
    /**
     * 用户配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form) {}
    
    /**
     * 插件实现方法
     * 
     * @access public
     * @return String(解析完成的HTML代码)
     */
    public static function render($text) {
        switch (Typecho_Widget::widget('Widget_Options')->plugin('TypechoMarkDown')->engine) {
            case 'HyperDown':
                include_once dirname(__FILE__) . '/HyperDown/Parser.php';
                $parser = new HyperDown\Parser();
                $html = $parser->makeHtml($text);
                break;
            case 'parsedown':
                include_once dirname(__FILE__) . '/parsedown/Parsedown.php';
                $Parsedown = new Parsedown();
                // $Parsedown->setMarkupEscaped(true); //转义字符串
                // $Parsedown->setUrlsLinked(false); //不把url转换为链接
                // $Parsedown->setBreaksEnabled(true); //匹配换行为<br />, 否则只匹配段落
                $html = $Parsedown->setBreaksEnabled(true)->text($text);
                break;
            case 'php-markdown':
                include_once dirname(__FILE__) . '/php-markdown/Michelf/MarkdownExtra.inc.php';
                $parser = new Michelf\MarkdownExtra();
                $parser->hard_wrap = true;
                $html = $parser->transform($text);
                break;
            default :
                $html = $text;
        }
        switch (Typecho_Widget::widget('Widget_Options')->plugin('TypechoMarkDown')->newtab) {
            case 'true':
                $html = preg_replace('/<(a.*?)>/', '<$1 target="_blank">', $html);
                break;
            case 'false':
                // $html = preg_replace('', '', $html);
                break;
            default:
                break;
        }
        return $html;
    }
}
