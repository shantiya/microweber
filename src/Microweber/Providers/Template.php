<?php


/*
 * This file is part of the Microweber framework.
 *
 * (c) Microweber LTD
 *
 * For full license information see
 * http://Microweber.com/license/
 *
 */

namespace Microweber\Providers;

use Microweber\Utils\Adapters\Template\MicroweberTemplate;

/**
 * Content class is used to get and save content in the database.
 *
 * @category Content
 * @desc     These functions will allow you to get and save content in the database.
 *
 * @property \Microweber\Utils\Adapters\Template\MicroweberTemplate $adapter_current
 * @property \Microweber\Utils\Adapters\Template\MicroweberTemplate $adapter_default
 */
class Template
{
    /**
     * An instance of the Microweber Application class.
     *
     * @var
     */
    public $app;
    public $head = array();
    public $head_callable = array();
    public $foot = array();
    public $foot_callable = array();

    public $meta_tags = array();
    public $html_opening_tag = array();

    public $adapter_current = null;
    public $adapter_default = null;

    public function __construct($app = null)
    {
        if (!is_object($this->app)) {
            if (is_object($app)) {
                $this->app = $app;
            } else {
                $this->app = mw();
            }
        }

        $this->adapter_current
            = $this->adapter_default = new MicroweberTemplate($app);
    }


    public function compile_css($params)
    {


        $path = false;
        $og = false;
        $template_folder = false;

        if (isset($params['path'])) {
            $path = $params['path'];
        }
        if (isset($params['option_group'])) {
            $og = $params['option_group'];
        }
        if (isset($params['template_folder'])) {
            $template_folder = $params['template_folder'];
        }

        if (!$path or !$og or !$template_folder) {
            return;
        }


        $option_group = mw()->option_manager->get_all('option_group=' . $og);
     //   dd($option_group);

        $path = str_replace('\\', '/', $path);

        // dd(TEMPLATES_DIR);

        $path_dirname = dirname($path);
        $template_url_with_path = templates_url() . $template_folder . '/' . $path_dirname . '/';
        $dir = templates_path() . $template_folder;


        $output_dir = media_uploads_path() . 'css/';
        $output_url = media_uploads_url() . 'css/';
        $output_file = $output_dir . $path . '.css';
        $output_file_map = $output_dir . $path . '.map';
        $output_file_map_url = $output_url . $path . '.map';

        $dn_out = dirname($output_dir . $path);
        if (!is_dir($dn_out)) {
            mkdir_recursive($dn_out);
        }


        $style_file_path = normalize_path($dir . '/' . $path, false);
        $style_file_path = str_replace('..', '', $style_file_path);


        //$all_options
        $options = array(
            'sourceMap' => true,
            'compress' => true,
            'sourceMapWriteTo' => $output_file_map,
            'sourceMapURL' => $output_file_map_url,
        );
        $options2 = array(
            'sourceMap' => true,
            'compress' => true,
            'sourceMapWriteTo' => $output_file_map,
            'sourceMapURL' => $output_file_map_url,
        );


        $variables = array();

        if (is_array($option_group) and !empty($option_group)) {
            foreach ($option_group as $option_group_item) {
                $variables[$option_group_item['option_key']] = $option_group_item['option_value'];
            }
        }


        //   $css_file_name = Less_Cache::Get( $less_files, $options, $variables );

        $css = '';
        try {
            $parser = new \Less_Parser($options);
            $parser->parseFile($style_file_path, $template_url_with_path);
            $parser->ModifyVars($variables);

            $css = $parser->getCss();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        $response = \Response::make($css);
        $response->header('Content-Type', 'text/css');
        return $response;



        //      $imported_files = $parser->allParsedFiles();


//        d($style_file_path);
//        d($imported_files);
//        exit;
//
//        d($path_dirname);
//        exit;
    }


    public function get_stylesheet($path, $option_group_name = false)
    {
        if (!$path) {
            return;
        }

        $folder_name = $this->folder_name();

        if (!$option_group_name) {
            $option_group_name = $folder_name;
        }
        $use_cache = true;

        if (is_live_edit()) {
            $use_cache = false;
        }

       // $css_file_name = Less_Cache::Get( $less_files, $options );




        $url = api_url('template/compile_css?path=' . $path . '&option_group=' . $option_group_name . '&template_folder=' . $folder_name);


        return $url;


        //$vars =


        $url = $this->app->url_manager->site('api/compile_css') . '?mwv=' . MW_VERSION;

        return $url;


//$template =


    }


    public function get_apijs_url()
    {


        $url = $this->app->url_manager->site('apijs') . '?mwv=' . MW_VERSION;
        $compile_assets = \Config::get('microweber.compile_assets');
        if ($compile_assets and defined('MW_VERSION')) {
            $userfiles_dir = userfiles_path();
            $userfiles_cache_dir = normalize_path($userfiles_dir . 'cache' . DS . 'apijs' . DS);
            $hash = md5(site_url());
            $userfiles_cache_filename = $userfiles_cache_dir . 'api.' . $hash . '.' . MW_VERSION . '.js';
            if (is_file($userfiles_cache_filename)) {
                $url = userfiles_url() . 'cache/apijs/' . 'api.' . $hash . '.' . MW_VERSION . '.js';
            }
        }

        return $url;
    }


    public function get_apijs_settings_url()
    {
        $url = $this->app->url_manager->site('apijs_settings') . '?mwv=' . MW_VERSION;;
        $compile_assets = \Config::get('microweber.compile_assets');
        if ($compile_assets and defined('MW_VERSION')) {
            $userfiles_dir = userfiles_path();
            $file = mw_includes_path() . 'api' . DS . 'api_settings.js';
            $mtime = false;
            if (is_file($file)) {
                $mtime = filemtime($file);
            }

            $userfiles_cache_dir = normalize_path($userfiles_dir . 'cache' . DS . 'apijs' . DS);
            $fn = 'api_settings.' . md5(site_url() . template_dir() . $mtime) . '.' . MW_VERSION . '.js';
            $userfiles_cache_filename = $userfiles_cache_dir . $fn;
            if (is_file($userfiles_cache_filename)) {
                if (is_file($userfiles_cache_filename)) {
                    $url = userfiles_url() . 'cache/apijs/' . $fn;
                }
            }
        }

        return $url;
    }

    public function clear_cached_apijs_assets()
    {
        $userfiles_dir = userfiles_path();
        $userfiles_cache_dir = normalize_path($userfiles_dir . 'cache' . DS . 'apijs' . DS);
        if (!is_dir($userfiles_cache_dir)) {
            return;
        }
        $files = glob($userfiles_cache_dir . "*.js");
        if ($files) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
    }

    public function meta($name, $value = false)
    {
        $this->meta_tags[$name] = $value;
    }

    public function html_opening_tag($name, $value = false)
    {
        $this->html_opening_tag[$name] = $value;
    }

    public function folder_name()
    {
        if (!defined('THIS_TEMPLATE_FOLDER_NAME')) {
            $this->app->content_manager->define_constants();
        }
        return THIS_TEMPLATE_FOLDER_NAME;
        //
    }

    public function dir($add = false)
    {
        if (!defined('TEMPLATE_DIR')) {
            $this->app->content_manager->define_constants();
        }
        if (defined('TEMPLATE_DIR')) {
            $val = TEMPLATE_DIR;
        }


        if ($add != false) {
            $val = $val . $add;
        }

        return $val;
    }

    public $template_config_cache = array();

    public function get_config($template = false)
    {


        if ($template == false) {

            $dir = template_dir();

            $file = $dir . 'config.php';

            if (isset($this->template_config_cache[$file])) {
                return $this->template_config_cache[$file];
            }


            if (is_file($file)) {
                include $file;
                if (isset($config)) {
                    $this->template_config_cache[$file] = $config;
                    return $config;
                }

                return false;
            }
        }
    }

    public function url($add = false)
    {
        if (!defined('TEMPLATE_URL')) {
            $this->app->content_manager->define_constants();
        }
        if (defined('TEMPLATE_URL')) {
            $val = TEMPLATE_URL;
        }


        if ($add != false) {
            $val = $val . $add;

        }


        return $val;
    }

    public function adapter($method, $params)
    {
        if (method_exists($this->adapter_current, $method)) {
            return $this->adapter_current->$method($params);
        } else {
            if (method_exists($this->adapter_default, $method)) {
                return $this->adapter_default->$method($params);
            }
        }
    }

    public function get_custom_css()
    {
        if (!defined('MW_NO_SESSION')) {
            define('MW_NO_SESSION', 1);
        }
        ob_start();

        event_trigger('mw.template.print_custom_css_includes');

        $fonts_file = modules_path() . 'editor' . DS . 'fonts' . DS . 'stylesheet.php';
        if (is_file($fonts_file)) {
            include $fonts_file;
        }
        $custom_css = get_option('custom_css', 'template');
        if (is_string($custom_css)) {
            echo $custom_css;
        }

        event_trigger('mw.template.print_custom_css');

        $output = ob_get_contents();
        ob_end_clean();

        $l = $output;
        $compile_assets = \Config::get('microweber.compile_assets');
        if ($compile_assets and defined('MW_VERSION')) {
            $userfiles_dir = userfiles_path();
            $userfiles_cache_dir = normalize_path($userfiles_dir . 'cache' . DS);
            $userfiles_cache_filename = $userfiles_cache_dir . 'custom_css.' . md5(site_url()) . '.' . MW_VERSION . '.css';
            if (!is_file($userfiles_cache_filename)) {
                if (!is_dir($userfiles_cache_dir)) {
                    mkdir_recursive($userfiles_cache_dir);
                }
                if (is_dir($userfiles_cache_dir)) {
                    @file_put_contents($userfiles_cache_filename, $l);
                }
            } else {
                $fmd5 = md5_file($userfiles_cache_filename);
                $fmd = md5($l);
                if ($fmd5 != $fmd) {
                    @file_put_contents($userfiles_cache_filename, $l);
                }
            }
        }

        return $output;
    }


    public function optimize_page_loading($layout)
    {
        $optimize_asset_loading = get_option('optimize_asset_loading', 'website');
        if ($optimize_asset_loading == 'y') {
            $layout = $this->app->parser->optimize_asset_loading_order($layout);

        }


        $static_files_delivery_method = get_option('static_files_delivery_method', 'website');
        $static_files_delivery_domain = get_option('static_files_delivery_method_domain', 'website');

        if ($static_files_delivery_method and $static_files_delivery_domain) {


            $should_replace = false;

            //check if site is fqdn
            $site_host = parse_url(site_url());

            if (isset($site_host['host']) and mw()->format->is_fqdn($site_host['host'])) {
                $should_replace = true;
                $site_host = $site_host['host'];
            }
            if ($should_replace) {
                if ($static_files_delivery_domain and mw()->format->is_fqdn($static_files_delivery_domain)) {
                    $should_replace = true;
                } else {
                    $should_replace = false;
                }
            }
            if ($should_replace) {
                $static_files_delivery_domain = trim($static_files_delivery_domain);

                $replaces = array();
                if ($static_files_delivery_method == 'content_proxy') {
                    $replaces[userfiles_url() . 'cache'] = 'https://' . $static_files_delivery_domain . '/' . userfiles_url() . 'cache';
                    $replaces[media_base_url()] = 'https://' . $static_files_delivery_domain . '/' . media_base_url();
                    $replaces[template_url()] = 'https://' . $static_files_delivery_domain . '/' . template_url();
                    $replaces[modules_url()] = 'https://' . $static_files_delivery_domain . '/' . modules_url();
                } else if ($static_files_delivery_method == 'cdn_domain') {
                    $replaces[userfiles_url() . 'cache'] = str_replace($site_host, $static_files_delivery_domain, userfiles_url() . 'cache');
                    $replaces[media_base_url()] = str_replace($site_host, $static_files_delivery_domain, media_base_url());
                    $replaces[template_url()] = str_replace($site_host, $static_files_delivery_domain, template_url());
                    $replaces[modules_url()] = str_replace($site_host, $static_files_delivery_domain, modules_url());


                }
                if ($replaces) {
                    $layout = str_replace(array_keys($replaces), array_values($replaces), $layout);
                }
            }

        }


        return $layout;
    }

    public function get_custom_css_url()
    {
        $url = api_nosession_url('template/print_custom_css');
        if (in_live_edit()) {
            return $url;
        }

        $compile_assets = \Config::get('microweber.compile_assets');
        if ($compile_assets and defined('MW_VERSION')) {
            $userfiles_dir = userfiles_path();
            $userfiles_cache_dir = normalize_path($userfiles_dir . 'cache' . DS);
            $userfiles_cache_filename = $userfiles_cache_dir . 'custom_css.' . md5(site_url()) . '.' . MW_VERSION . '.css';
            if (is_file($userfiles_cache_filename)) {
                $custom_live_editmtime = filemtime($userfiles_cache_filename);
                $url = userfiles_url() . 'cache/' . 'custom_css.' . md5(site_url()) . '.' . MW_VERSION . '.css?ver=' . $custom_live_editmtime;
            }
        }

        return $url;
    }

    public function clear_cached_custom_css()
    {
        $url = api_nosession_url('template/print_custom_css');
        $compile_assets = \Config::get('microweber.compile_assets');
        $userfiles_dir = userfiles_path();
        $userfiles_cache_dir = normalize_path($userfiles_dir . 'cache' . DS);
        $userfiles_cache_filename = $userfiles_cache_dir . 'custom_css.' . md5(site_url()) . '.' . MW_VERSION . '.css';
        if (!is_dir($userfiles_cache_dir)) {
            return;
        }
        $files = glob($userfiles_cache_dir . "custom_css*.css");
        if ($files) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
    }


    public function name()
    {
        if (!defined('TEMPLATE_NAME')) {
            $this->app->content_manager->define_constants();
        }
        if (defined('TEMPLATE_NAME')) {
            return TEMPLATE_NAME;
        }
    }

    public function admin_head($script_src)
    {
        static $mw_template_headers;
        if ($mw_template_headers == null) {
            $mw_template_headers = array();
        }

        if (is_string($script_src)) {
            if (!in_array($script_src, $mw_template_headers)) {
                $mw_template_headers[] = $script_src;

                return $mw_template_headers;
            }
        } else {
            if (is_bool($script_src)) {
                //   return $mw_template_headers;
                $src = '';
                if (is_array($mw_template_headers)) {
                    foreach ($mw_template_headers as $header) {
                        $ext = get_file_extension($header);
                        switch (strtolower($ext)) {

                            case 'css':
                                $src .= '<link rel="stylesheet" href="' . $header . '" type="text/css" media="all">' . "\n";
                                break;

                            case 'js':
                                $src
                                    .= '<script type="text/javascript" src="' . $header . '"></script>' . "\n";
                                break;

                            default:
                                $src .= $header . "\n";
                                break;
                        }
                    }
                }

                return $src;
            }
        }
    }

    public function head($script_src)
    {
        if ($this->head_callable == null) {
            $this->head_callable = array();
        }

        if (is_string($script_src)) {
            if (!in_array($script_src, $this->head)) {
                $this->head[] = $script_src;

                return $this->head;
            }
        } else {
            if (is_bool($script_src)) {
                //   return $this->head;
                $src = '';

                if (is_array($this->head)) {
                    foreach ($this->head as $header) {
                        $ext = get_file_extension($header);
                        switch (strtolower($ext)) {

                            case 'css':
                                $src .= '<link rel="stylesheet" href="' . $header . '" type="text/css" media="all">' . "\n";
                                break;

                            case 'js':
                                $src
                                    .= '<script type="text/javascript" src="' . $header . '"></script>' . "\n";
                                break;

                            default:
                                $src .= $header . "\n";
                                break;
                        }
                    }
                }

                return $src;
            } elseif (is_callable($script_src)) {
                if (!in_array($script_src, $this->head_callable)) {
                    $this->head_callable[] = $script_src;

                    return $this->head_callable;
                }
            }
        }
    }

    public function head_callback($data = false)
    {
        $data = array();
        if (!empty($this->head_callable)) {
            foreach ($this->head_callable as $callback) {
                $data[] = call_user_func($callback, $data);
            }
        }

        return $data;
    }

    public function foot($script_src)
    {
        if ($this->foot_callable == null) {
            $this->foot_callable = array();
        }

        if (is_string($script_src)) {
            if (!in_array($script_src, $this->foot)) {
                $this->foot[] = $script_src;

                return $this->foot;
            }
        } else {
            if (is_bool($script_src)) {
                $src = '';
                if (is_array($this->foot)) {
                    foreach ($this->foot as $footer) {
                        $ext = get_file_extension($footer);
                        switch (strtolower($ext)) {

                            case 'css':
                                $src .= '<link rel="stylesheet" href="' . $footer . '" type="text/css" media="all">' . "\n";
                                break;

                            case 'js':
                                $src
                                    .= '<script type="text/javascript" src="' . $footer . '"></script>' . "\n";
                                break;

                            default:
                                $src .= $footer . "\n";
                                break;
                        }
                    }
                }

                return $src;
            } elseif (is_callable($script_src)) {
                if (!in_array($script_src, $this->foot_callable)) {
                    $this->foot_callable[] = $script_src;

                    return $this->foot_callable;
                }
            }
        }
    }

    public function foot_callback($data = false)
    {
        $data = array();
        if (!empty($this->foot_callable)) {
            foreach ($this->foot_callable as $callback) {
                $data[] = call_user_func($callback, $data);
            }
        }

        return $data;
    }


    /**
     * Return the path to the layout file that will render the page.
     */
    public function get_layout($params = array())
    {
        return $this->adapter('get_layout', $params);
    }

    public function process_meta($layout)
    {
        $count = 1;
        $replace = '';
        if (!empty($this->html_opening_tag)) {
            foreach ($this->html_opening_tag as $key => $item) {
                if (is_string($item)) {
                    $replace .= $key . '="' . $item . '" ';
                }
            }
        }

        $layout = str_replace('<html ', '<html ' . $replace, $layout, $count);
        $count = 1;
        $replace = '';
        if (!empty($this->meta_tags)) {
            foreach ($this->meta_tags as $key => $item) {
                if (is_string($item)) {
                    $replace .= '<meta name="' . $key . '" content="' . $item . '">' . "\n";
                }
            }
        }
        $count = 1;
        $layout = str_replace('<head>', '<head>' . $replace, $layout, $count);

        return $layout;
    }

    /**
     * Renders the file returned by the get_layout method.
     */
    public function render($params = array())
    {
        $layout = $this->adapter('render', $params);

        $layout = $this->process_meta($layout);

        return $layout;
    }

    public function clear_cache()
    {
        $userfiles_dir = userfiles_path();
        $userfiles_cache_dir = normalize_path($userfiles_dir . 'cache' . DS . 'apijs');
        if (is_dir($userfiles_cache_dir)) {
            if (function_exists('rmdir_recursive')) {
                rmdir_recursive($userfiles_cache_dir);
            }
        }
    }
}
