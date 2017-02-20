<?php

namespace dokuwiki\template\sprintdoc;

/**
 * Class Template
 *
 * provides additional logic for the sprintdoc template
 *
 * @package dokuwiki\template\sprintdoc
 */
class Template {

    /**
     * @var array loaded plugins
     */
    protected $plugins = array(
        'sqlite' => null,
        'tagging' => null,
    );

    /**
     * Get the singleton instance
     *
     * @return Template
     */
    public static function getInstance() {
        static $instance = null;
        if($instance === null) $instance = new Template();
        return $instance;
    }

    /**
     * Template constructor.
     */
    protected function __construct() {
        $this->initializePlugins();
    }

    /**
     * Load all the plugins we support directly
     */
    protected function initializePlugins() {
        $this->plugins['sqlite'] = plugin_load('helper', 'sqlite');
        if($this->plugins['sqlite']) {
            $this->plugins['tagging'] = plugin_load('helper', 'tagging');
        }
    }

    /**
     * Get all the tabs to display
     *
     * @return array
     */
    public function getMetaBoxTabs() {
        global $lang;
        $tabs = array();

        $toc = tpl_toc(true);
        if($toc) {
            $tabs[] = array(
                'id' => 'spr__tab-toc',
                'label' => $lang['toc'],
                'tab' => $toc,
                'count' => null,
            );
        }

        if($this->plugins['tagging']) {
            $tabs[] = array(
                'id' => 'spr__tab-tags',
                'label' => tpl_getLang('tab_tags'),
                'tab' => $this->plugins['tagging']->tpl_tags(false),
                'count' => null, // FIXME
            );
        }

        // fixme add magicmatcher info

        return $tabs;
    }

    /**
     * Creates an image tag and includes the first found image correctly resized
     *
     * @param string $tag
     * @param array $attributes
     * @param int $w
     * @param int $h
     * @return string
     */
    public static function getResizedImgTag($tag, $attributes, $w, $h) {
        $attr = '';
        $medias = array();

        // the attribute having an array defines where the image goes
        foreach($attributes as $attribute => $data) {
            if(is_array($data)) {
                $medias = $data;
                $attr = $attribute;
            }
        }
        // if the image attribute could not be found return
        if(!$attr || !$medias) return '';

        // try all medias until an existing one is found
        $media = '';
        foreach($medias as $media) {
            if(file_exists(mediaFN($media))) break;
            $media = '';
        }
        if($media === '') return '';

        // replace the array
        $media = ml($media, array('w' => $w, 'h' => $h, 'crop' => 1), true, '&');
        $attributes[$attr] = $media;

        // return the full tag
        return '<' . $tag . ' ' . buildAttributes($attributes) . ' />';
    }
}
