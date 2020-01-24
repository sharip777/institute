<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://localhost/test.com
 * @since      1.0.0
 *
 * @package    Vnins
 * @subpackage Vnins/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Vnins
 * @subpackage Vnins/includes
 * @author     noname <abakan_ac545@mail.ru>
 */
class Vnins
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Vnins_Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {

        if (defined('VNINS_VERSION')) {
            $this->version = VNINS_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'vnins';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();

    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Vnins_Loader. Orchestrates the hooks of the plugin.
     * - Vnins_i18n. Defines internationalization functionality.
     * - Vnins_Admin. Defines all hooks for the admin area.
     * - Vnins_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-vnins-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-vnins-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-vnins-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-vnins-public.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-institutes.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-parcel.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-post.php';


        $this->loader = new Vnins_Loader();

    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Vnins_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale()
    {

        $plugin_i18n = new Vnins_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');

    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {

        $plugin_admin = new Vnins_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks()
    {

        $plugin_public = new Vnins_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {

        add_filter( 'woocommerce_countries',  array($this, "create_countries") );
        add_filter( 'woocommerce_continents', array($this, "create_continents") );
        add_filter( 'woocommerce_states', array($this, "create_no_sizo_states") );
        add_filter( 'woocommerce_states', array($this, "create_sizo_states") );


        $this->loader->run();
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $is_sizo = 0;
            if (isset($_POST['is_sizo'])) {
                $is_sizo = $_POST['is_sizo'];
            }

            $this->postProcess($_POST, $is_sizo);

        }
    }

    public function postProcess($post, $is_sizo)
    {
        global $wpdb;

        if (isset($post["action"]) && $post["action"] === "new_institute") {
            $prepare_sql = $wpdb->prepare("
				INSERT INTO {$wpdb->prefix}tablename 
				(name, address, post_index, is_sizo)
				VALUES(%s, %s, %s, %d)
				",
                $post["name"],
                $post["address"],
                $post["post_index"],
                $is_sizo
            );
            $wpdb->query($prepare_sql);
        }
    }

    public function create_sizo_states( $states )
    {
        $institutes = new Institutes();
        $sizo = $institutes->get_sizo();

        $new_states = array();
        foreach ($sizo as $item) {
            $new_states[$item->name] = $item->name;
        }
        $states['VSI'] = $new_states;

        return $states;

    }

    public function create_no_sizo_states($states)

    {
        $institutes = new Institutes();
        $no_sizo  = $institutes -> get_not_sizo();

        $new_states = array();
        foreach ( $no_sizo as $item) {
            $new_states[$item->name ] = $item->name; 
        };
    
        $states['VNS'] = $new_states;
        return $states;

    }

    public function create_continents($continents)
    {
        $continents['ASIA']['countries'][] = 'VSI';
        $continents['ASIA']['countries'][] = 'VNS';
        return $continents;
    }

    public function create_countries($countries)
    {
        $new_countries = array(
            'VSI' => __('СИЗО', 'woocommerce'),
            'VNS' => __('Не СИЗО', 'woocommerce')
        );

        return array_merge($countries, $new_countries);
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @return    string    The name of the plugin.
     * @since     1.0.0
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @return    Vnins_Loader    Orchestrates the hooks of the plugin.
     * @since     1.0.0
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @return    string    The version number of the plugin.
     * @since     1.0.0
     */
    public function get_version()
    {
        return $this->version;
    }

}
