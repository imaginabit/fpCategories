<?php
/**
 * @package fpCategorias
 * @version 0.1.1
 */
/*
Plugin Name: fpCategorias
Plugin URI: http://freepress.coop
Description: Muestra Caja con seleccion de categorias predefinida
Author: Freeprees.coop, Fernando Ramírez Pérez
Version: 0.1.1
Author URI: http://freepress.coop
*/
// print plugin_dir_url( __FILE__ );


//activate errors
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


if (!class_exists('FpCategorias') ){

  class FpCategorias
  {
    private $db_version = '0';
    private $plugin_url;
    private $vesion;
    private $fpCategoriasSetings;
    private $currentCat;
    private $catPrinted;

    function __construct()
    {
      global $wpdb, $current_user;
      $this->version = "0.1.0";
      $plugin_prefix = "fpCat_";
      $this->fpCategoriasSetings = array();
      //$this->fpCategoriasSetings['categoriasId'] = array(1,14);
      $this->fpCategoriasSetings['categoriasId'] = array(1,24);
      $this->fpCategoriasSetings['selected-first'] = true;
      $this->currentCat = 0;
      $this->plugin_url = plugin_dir_url( __FILE__ );
      $this->catPrinted = array();
      $this->tmp = '';

      add_action('admin_menu', array( $this, 'metabox_register' ) );
      add_action( 'admin_enqueue_scripts', array($this,'load_custom_wp_admin_style') );

      //lo comento por que ahora mismo esta en function.php del tema hijo
      //add_shortcode('mixedcats', array($this,'shortcode_mixedcats'));
    }

    function load_custom_wp_admin_style($hook) {
        //wp_die($hook);
        // Load only on ?page=mypluginname
        $this->tmp = $hook;
        if(  in_array($hook, array('post.php','post-new.php')) ) {
          if (get_post_type()!=='page'){
            wp_enqueue_script( 'fpCategorias-script', plugins_url('fpCategories.js', __FILE__) );
          }
        } else {

            return;
        }

        //wp_enqueue_style( 'custom_wp_admin_css', plugins_url('admin-style.css', __FILE__) );
        //wp_register_script('fpCategorias-script', plugins_url('fpCategories.js', __FILE__) );

        // js categorias


        return;
    }


    function metabox_register(){
      // $post_types = get_post_types(array("public" => true));
      // foreach ($post_types as $post_type) {
      //   //  && $post_type != 'page'
      //  if($post_type != 'attachment')
      //       add_meta_box("fpCategorias-box" , __('FpCategorias', 'fpCategorias' ), array($this, 'boxes') , $post_type,'side', 'high');
      // }
  		add_meta_box("fpCategorias-box" , __('FpCategorias', 'fpCategorias' ), array($this, 'boxes') , 'post','side', 'high');
      // add_meta_box("fpCategorias-box" , __('FpCategorias', 'fpCategorias' ), array($this, 'boxes') , 'page','side', 'high');

      // add_meta_box("testbox" , __('FpCategorias', 'fpCategorias' ), array($this, 'testbox') , 'all','normal', 'high');

    }

    // function testbox() {
    //   echo "hook:  #{$this->tmp}";
    // }

    function boxes() {
      // echo "hoolaaa";
      // echo "hook:  #{$this->tmp}";

      foreach ($this->fpCategoriasSetings['categoriasId'] as $k => $cat) {
        if (get_cat_name($cat) ){
          echo '<b>'.get_cat_name($cat).'</b>';
          $this->CatBox($cat);
        }
      }
    }

    function print_categoryList($category,$sel_cats){
      $value = $category;
      if ( ! in_array($category->term_id, $this->catPrinted)){
        echo '<li id="category-'.$value->term_id.'" class="popular-category">
        <label class="selectit">
        <input value="'.$value->term_id.'" type="checkbox" name="post_category[]" id="in-category-'.$value->term_id.'"  ';
        if( in_array($value->term_id,$sel_cats)  ) echo ' checked="checked" ';
        echo ">";
        echo $value->name;
        echo "</label>";
        $child_categories = get_categories(array('type'=> 'post','child_of'=>$category->term_id,'hide_empty'=>false));
        if (!empty($child_categories)){
          //print_r($child_categories);
          //if ( ) has children
          echo '<ul class="children">';
          foreach ($child_categories as $child) {
            $this->print_categoryList($child,$sel_cats);
          }
          echo '</ul>';
          // fin chindren
        }
        echo "</li>";
        $this->catPrinted[] = $category->term_id;
      }
    }

    function print_selected($category,$sel_cats){
      $value = $category;
      if ( in_array($value->term_id,$sel_cats) ){
        echo '<li id="category-'.$value->term_id.'" class="popular-category">
        <label class="selectit">
        <input value="'.$value->term_id.'" type="checkbox" name="post_category[]" id="in-category-'.$value->term_id.'"  ';
        echo ' checked="checked" ';
        echo ">";
        echo $value->name;
        echo "</label>";
        // $child_categories = get_categories(array('type'=> 'post','child_of'=>$category->term_id,'hide_empty'=>false));
        // if (!empty($child_categories)){
        //   //print_r($child_categories);
        //   //if ( ) has children
        //   echo '<ul class="children">';
        //   foreach ($child_categories as $child) {
        //     $this->print_categoryList($child,$sel_cats);
        //   }
        //   echo '</ul>';
        //   // fin chindren
        // }
        echo "</li>";
        $this->catPrinted[] = $category->term_id;
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
        <div id="category-fpCatBox fpCatBox-'.$slug.'" class="tabs-panel">';

      echo '<ul id="categorychecklist" data-wp-lists="list:category" class="categorychecklist form-no-clear">';
      if ($this->fpCategoriasSetings['selected-first']){
        foreach ($cats as $key => $value) {
          $this->print_selected($value,$sel_cats);
        }
      }
      foreach ($cats as $key => $value) {
        $this->print_categoryList($value,$sel_cats);
      }
      echo "</ul>";
      echo '</div></div>';

      //post_categories_meta_box($post,$box);
      echo "\n\n\n<!-- Metabox  end -->\n";
    }

    // ---------------------- shortcode mixedcats junta muestra los post que pertenezcan  a dos categorias
    function shortcode_mixedcats($atts)
    {
        global $wpdb;
        $atts = shortcode_atts(array(
            'c' => '',
        ), $atts, 'mixedcats');

        $current_screen = null;
        if (function_exists('get_current_screen')){
          $current_screen = get_current_screen();
        }

        if ($atts['c'] !== '' && !($current_screen && $current_screen->id === "post")) {
          $html = '';
          // 'posts_per_page' => 20,
          //   'no_found_rows' => true // faster query
            $cats =  join(',', explode(',', $atts['c']));
            $mc_query_args = array(
              'category__and' => explode(',', $atts['c']),
              'post_status' => 'publish',
              'no_found_rows' => true
            );
            $mc_query = new WP_Query($mc_query_args);
            $html .= "<article>";
            $html .= "<div>";
            if (!$mc_query->have_posts()) {
                $html .=  "no existen posts con esas condiciones " ;
            }
            if ($mc_query->have_posts()) {
                 while ($mc_query->have_posts()) {
                    $mc_query->the_post();
                    $html .= '<div class="docsrelacionados"><a href="'. get_the_permalink() .'" >';
                    $html .= get_the_title() .'</a></div>';
                 }
            }
            $html .= wp_reset_postdata();
            $html .=  '</div></article>';
            return $html;
        }
        return '';
    }



  };
  $GLOBALS['FpCategorias'] = new FpCategorias();
}
