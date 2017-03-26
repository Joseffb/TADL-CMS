<?php
/**
 * Created by PhpStorm.
 * User: joseffbetancourt
 * Date: 2/5/17
 * Time: 9:48 AM
 *
 * Core cms routes.
 * These can be changed/overridden in the routes ini.
 *
 */

namespace controllers;

class routes extends \core\controller_model
{

    public function __construct()
    {
        parent::__construct();
        $this->set_site_theme('Alice');
        $this->set_admin_theme('RedQueen');
        $this->set_mobile_theme('MadHatter');
        $this->set_default_routes();
        if($this->check_if_table_exists('sites')) {
            //Tables most likely haven't been installed yet.
            $this->determine_site_id();
        }
    }

    public function beforeRoute() {

    }

    public function set_admin_theme($theme = "RedQueen")
    {
        // todo : fire admin theme hook event
        //$theme = do_action('set_admin_theme')?:$theme;
        $this->fw->set("ADMIN_THEME", $theme);
        // todo : fire admin theme hook event
        //$theme = do_action('set_admin_theme')?:$theme;
        $this->fw->set("ADMIN_THEME_URL", "themes/".$theme."/");
    }

    public function set_site_theme($theme = "Alice")
    {
        // todo : fire site theme hook event
        //$theme = do_action('set_site_theme')?:$theme;
        $this->fw->set("SITE_THEME", $theme);
        // todo : fire admin theme hook event
        //$theme = do_action('set_admin_theme')?:$theme;
        $this->fw->set("SITE_THEME_URL",  "themes/".$theme."/");
    }

    public function set_mobile_theme($theme = "MadHatter")
    {
        // todo : fire site theme hook event
        //$theme = do_action('set_site_theme')?:$theme;
        $this->fw->set("MOBILE_THEME", $theme);
        // todo : fire admin theme hook event
        //$theme = do_action('set_admin_theme')?:$theme;
        $this->fw->set("MOBILE_THEME_URL",  "themes/".$theme."/");
    }

    public function set_site_url($url)
    {
        // todo : fire site theme hook event
        //$theme = do_action('set_site_url')?:$url;
        $this->fw->set("SITE_URL", $url);
    }

    public function set_site_from_email($from_email)
    {
        // todo : fire site theme hook event
        //$theme = do_action('set_site_from_email')?:$from_email;
        $this->fw->set("SITE_FROM_EMAIL", $from_email);
    }

    public function set_site_admin_email($admin_email)
    {
        // todo : fire site theme hook event
        //$theme = do_action('set_site_email')?:$admin_email;
        $this->fw->set("SITE_SYSTEM_EMAIL", $admin_email);
    }

    public function set_site_name($name)
    {
        // todo : fire site theme hook event
        //$theme = do_action('set_site_name')?:$name;
        $this->fw->set("SITE_NAME", $name);
    }

    public function set_site_id($id)
    {
        // todo : fire site theme hook event
        //$theme = do_action('set_site_id')?:$id;
        $this->fw->set("SITE_ID", $id);
    }

    public function determine_site_id()
    {
        //we actually want to get what was requested.
        $site = $_SERVER['HTTP_HOST'];
        //todo mongo and jig versions.
        $class = $this->get_model_path(__CLASS__, __NAMESPACE__);
        $response = $class::lookup_site_by_url($site);

/*        echo "<pre>";
        //echo debug_print_backtrace();
        var_dump($response);
        echo "</pre>";*/

        if ($response) {
            $this->set_site_id($response->id);
            $this->set_site_from_email($response->from_email);
            $this->set_site_admin_email($response->admin_email);
            $this->set_site_name($response->name);
            $this->set_site_url($response->url);
            $this->set_site_theme($response->theme);
            $this->set_admin_theme($response->admin_theme);
            $this->set_mobile_theme($response->mobile_theme);
        }

    }

    public function check_if_route_exist($route)
    {
        $retVal = false;
        if (array_key_exists($route, $this->fw->ROUTES)) {
            $retVal = true;
        }
        return $retVal;
    }

    public function check_if_route_name_exist($name, $protocol = "GET")
    {
        $retVal = false;
        foreach ($this->fw->ROUTES as $route) {
            if (strtolower($route[$protocol][3]) == strtolower($name)) ;
            $retVal = $route[$protocol][0];
            break;
        }
        return $retVal;
    }

    public function admin_root_route($fw) {
        //$this->fw->set('THEME_CSS',theme::get_localized_css());
        $this->fw->set('THEME_JS',theme::get_localized_js());
        $theme = $fw->get('ADMIN_THEME_URL');
        $view=new \View();
        echo $view->render($theme.'index.php');
    }

    public function frontend_root_route($fw) {
        //$this->fw->set('THEME_CSS',theme::get_localized_css());
        $this->fw->set('THEME_JS',theme::get_localized_js());
        $theme = $fw->get('SITE_THEME_URL');
        $view=new \View();
        echo $view->render($theme.'index.php');
    }

    public function mobile_root_route($fw) {
        // should use browser sniffing
    }


    public function set_default_routes() {
        //themes

        //$this->fw->route('GET @mobile_root: /mbl/*', 'controllers\routes->>mobile_root_route');

        //asset and media files proxies
        $this->fw->route('GET @template_assets:  /assets/*', 'controllers\media->assets');
        $this->fw->route('GET @site_files: /file/*', 'controllers\media->files');
    }

    // Root pages:
    // Will launch the index.php file based on the theme selected in the config.
    // From there the theme will use JSON calls for remainder of uri route.
    function get_root_data()
    {
        return array(
            array($this->fw->SITE_ID => array(
                'site' => array(
                    'name' => $this->fw->SITE_NAME,
                    'url' => $this->fw->SITE_URL,
                    'home' => $this->fw->SITE_URL,
                    'login' => $this->fw->SITE_LOGIN,
                    'logout' => $this->fw->SITE_LOGOUT,
                    'register' => $this->fw->SITE_REGISTER,
                    'search' => $this->fw->SITE_SEARCH,
                    'site_map' => array(
//                        'blog' => '/blog',
//                        'feed' => '/feed',
//                        'photos' => '/gallery',
//                        'videos' => '/videos',
//                        'media' => '/media',
//                        'forum' => '/forums',
//                        'friends' => '/social/friends',
//                        'following' => '/social/following',
//                        'friend_request' => '/social/request',
//                        'likes' => '/social/likes',
//                        'dislikes' => '/social/dislikes',
                    )
                ),
                'api' => array(
                    'end_point' => '/json',
                    'public_key' => $pk = '',
                    'create_date' => $dt = date("Y-m-d H:i:s", time()),
                    'hash' => md5($dt . $pk),
                    'expiration_date' => date("Y-m-d H:i:s", time() + (int)ini_get("max_execution_time")),
                ),
                'user_info' => array(
                    'user_name',
                    'user_email',
//                    'media' => array( //example plugin data
//                        'number_of_photos' => 100,
//                        'number_of_videos' => 100,
//                        'number_of_audio' => 100,
//                    ),
//                    'social' => array ( //example plugin data
//                        'user_liked_friend' => 10000,
//                        'user_disliked_friend' => 10000,
//                        'friend_likes_user' => 10000,
//                        'friend_dislikes_user' => 10000,
//                        'number_of_friend' => 100,
//                        'number_of_followers' => 1000,
//                        'number_of_friend_request' => 10000
//                    ),
                    'permissions' => array(
                        'site_role',
                        'is_admin',
                        'is_authenticated',
                        'is_authorized',
                        'can_use_api',
                        'can_read_own',
                        'can_read_others',
                        'can_write_own',
                        'can_write_others',
                        'can_delete_own',
                        'can_delete_others',
                    ),
                ),
            )),
        );
    }


    function admin_root()
    {
        //todo check if you are a) authenticated, and b) have access to admin. if not forward to homepage or login page.
        //get admin theme.

    }

    function home_root()
    {

    }

}