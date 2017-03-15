<?php
/**
 * @package fpCategorias
 * @version 0.1.0
 */
/*
Plugin Name: fpCategorias
Plugin URI: http://freepress.coop
Description: Muestra Caja con seleccion de categorias predefinida
Author: Freeprees.coop, Fernando Ramírez Pérez
Version: 0.1.0
Author URI: http://freepress.coop
*/
// print plugin_dir_url( __FILE__ );


//activate errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if (!class_exists('FpCategorias') ){

  class FpCategorias
  {
    private $db_version = '0';
    private $plugin_url;
    private $vesion;
    private $fpCategoriasSetings;
    private $currentCat;

    // private $json_msg;

    function __construct()
    {
      global $wpdb, $current_user;
      $this->version = "0.1.0";
      $plugin_prefix = "fpCat_";
      $this->fpCategoriasSetings = array();
      $this->fpCategoriasSetings['categoriasId'] = array(1,24);
      $this->currentCat = 0;
      $this->plugin_url = plugin_dir_url( __FILE__ );

      add_action('admin_menu', array( $this, 'metabox_register' ) );
      add_action('wp_enqueue_scripts',  array($this, 'styles_script'));
      //add_action('wp_enqueue_scripts', array($this, 'styles_script'));
      add_action( 'admin_enqueue_scripts', array($this,'load_custom_wp_admin_style') );


    }

    function load_custom_wp_admin_style($hook) {
        //wp_die($hook);
        // Load only on ?page=mypluginname
        if($hook != 'post.php') {
                return;
        }
        //wp_enqueue_style( 'custom_wp_admin_css', plugins_url('admin-style.css', __FILE__) );
        //wp_register_script('fpCategorias-script', plugins_url('fpCategories.js', __FILE__) );
        wp_enqueue_script( 'fpCategorias-script', plugins_url('fpCategories.js', __FILE__) );
    }


    function metabox_register(){
      $post_types = get_post_types(array("public" => true));
      foreach ($post_types as $post_type) {
       if($post_type != 'attachment')
            add_meta_box("fpCategorias-box" , __('FpCategorias', 'fpCategorias' ), array($this, 'boxes') , $post_type,'side', 'high');
      }
    }

    function boxes() {
      // echo plugins_url('fpCategories.js', __FILE__) ;
      // echo "<br>";
      //
      // $enqueue = wp_script_is( 'fpCategorias-script' )? 'si': 'no' ;
      // $done = wp_script_is( 'fpCategorias-script', 'done' )? 'si': 'no' ;
      // $todo = wp_script_is( 'fpCategorias-script', 'to_do' )? 'si':'no';
      // echo "enqueue $enqueue<br> done $done <br>";
      // echo "todo $todo <br>";

      foreach ($this->fpCategoriasSetings['categoriasId'] as $k => $cat) {
        if (get_cat_name($cat) ){
          echo '<b>'.get_cat_name($cat).'</b>';
          $this->CatBox($cat);
        }
      }
    }

    function CatBox($catID) {
      echo "\n\n\n<!-- Metabox $catID-->\n";

      $box = array();
      $post = get_post();

      $post_categories = wp_get_post_categories( get_post()->ID );
      $sel_cats = array();

      foreach($post_categories as $c){
          $cat = get_category( $c );
          $sel_cats[] = $cat->term_id;
      }

      $cats = get_categories( Array('child_of' => $catID , 'hide_empty' => false));
      //id= taxonomy-category
      // <input type="hidden" name="post_category[]" value="0">
      $slug = get_category($catID)->slug;
      echo '
      <div id="taxonomy-category" class="categorydiv fpCatBox '.$slug.'">
        <div id="category-fpCatBox fpCatBox-'.$slug.'" class="tabs-panel">
        ';
      echo '<ul id="categorychecklist" data-wp-lists="list:category" class="categorychecklist form-no-clear">';
      foreach ($cats as $key => $value) {
        echo '<li id="category-'.$value->term_id.'" class="popular-category">
          <label class="selectit">
            <input value="'.$value->term_id.'" type="checkbox" name="post_category[]" id="in-category-'.$value->term_id.'"  ';
        if( in_array($value->term_id,$sel_cats)  ) echo ' checked="checked" ';
        echo ">";
        echo $value->name;
        echo "</label></li>";
      }
      echo "</ul>";
      echo '</div></div>';

      //post_categories_meta_box($post,$box);
      echo "\n\n\n<!-- Metabox  end -->\n";
    }


  };
  $GLOBALS['FpCategorias'] = new FpCategorias();
}
