<?php
  /*
Plugin Name: Genie Data manager
Plugin URI: http://dynamicplugin.com
Description: Genie Data manager
Version: 1.0
Author: Genie Soft
License: http://dynamicplugin.com/terms-and-conditions-and-end-user-license-agreement/
*/
include_once dirname(__FILE__) . '/Logic/genie_PluginHelper.php';
include_once dirname(__FILE__) . '/Logic/genie_Form.php';
include_once dirname(__FILE__) . '/Logic/genie_GeneralUsage.php';

include_once dirname(__FILE__) . '/Logic/genie_DataBase.php';
include_once dirname(__FILE__) . '/Logic/genie_CMSSpecials.php';




register_activation_hook(__FILE__,'genie_install');
register_deactivation_hook( __FILE__, 'genie_remove' );

function genie_install($networkwide) {

    $DM_PluginHelper = new genie_PluginHelper();
    $DM_PluginHelper->Install($networkwide);

}

function genie_remove() {

    $DM_PluginHelper = new genie_PluginHelper();
    $DM_PluginHelper->Remove();

}

add_action('admin_notices', 'genie_update_admin_notice');


define( 'GENIE_CURRENT_VERSION', "Version2" );
//Development mode:
define( 'GENIE_DEVELOPMENT_MODE', false );

function genie_update_admin_notice() {
    $DM_PluginHelper=new genie_PluginHelper();
    $DM_DataBase= new genie_DataBase();
    if ( $DM_PluginHelper->CheckNeedToBeUpgraded()) {

        $entityId=$DM_DataBase->getEntityId("DMSysEntities");
        $DM_CMSSpecials=new genie_CMSSpecials();
        $UpdateMessage = $DM_CMSSpecials->Translate("Genie DataBase needs to be updated",$entityId);
        $UpdateLink = $DM_CMSSpecials->Translate("Update DataBase",$entityId);

        echo '<div class="updated"><p>';
        echo $UpdateMessage."|<a href='"."?DM_UpdateDataBase=1'".">".$UpdateLink."</a>";
        echo "</p></div>";
    }
}

add_action('admin_init', 'genie_UpdateDataBase');
function genie_UpdateDataBase() {

    if ( isset($_GET['DM_UpdateDataBase']) && '1' == $_GET['DM_UpdateDataBase'] ) {
        $DM_PluginHelper=new genie_PluginHelper();
        $DM_PluginHelper->UpdateThisBlog();
    }
}


add_action( 'wpmu_new_blog', 'genie_new_blog', 10, 6);

function genie_new_blog($blog_id, $user_id, $domain, $path, $site_id, $meta ) {
    global $wpdb;

    if (is_plugin_active_for_network('genie/genie.php')) {
        $old_blog = $wpdb->blogid;
        switch_to_blog($blog_id);
        $DM_PluginHelper = new genie_PluginHelper();
        $DM_PluginHelper->ActivateThisBlog();
        switch_to_blog($old_blog);
    }
}


add_action('wp_ajax_nopriv_DMDynamicRequest', 'prefix_ajax_genie_DynamicRequest');
add_action('wp_ajax_DMDynamicRequest', 'prefix_ajax_genie_DynamicRequest');

function prefix_ajax_genie_DynamicRequest()
{
    $DM_PluginHelper = new genie_PluginHelper();
    $DM_PluginHelper->ManageRequest($_REQUEST);
}

add_action('wp_ajax_DMSystemDynamicRequest', 'prefix_ajax_genie_SystemDynamicRequest');

function prefix_ajax_genie_SystemDynamicRequest()
{
    $DM_PluginHelper = new genie_PluginHelper();
    $DM_PluginHelper->ManageSystemRequest($_REQUEST);

}

add_action('wp_ajax_DMWizardsRequest', 'prefix_ajax_genie_WizardRequest');

function prefix_ajax_genie_WizardRequest()
{
    $DM_PluginHelper = new genie_PluginHelper();
    $DM_PluginHelper->ManageWizardRequest($_REQUEST);

}

add_action('wp_enqueue_scripts', 'genie_scripts_method');
add_action('admin_enqueue_scripts', 'genie_scripts_method');

function genie_scripts_method() {

    $dir =  plugin_dir_url(__FILE__);
    $DM_PluginHelper = new genie_PluginHelper();
    $DM_PluginHelper->RegisterScripts($dir);

    $checkSystemReloading = false;
    $checkSystemReloading = apply_filters( 'DM_checkSystemReload', $checkSystemReloading );
    if($checkSystemReloading)
    {
        $DM_PluginHelper->UpdateThisBlog();
    }
}




if ( is_admin() ){
    add_action('admin_menu', 'genie_admin_menu');


    function genie_admin_menu()
    {
        $dir =  plugin_dir_url(__FILE__);
        $DM_PluginHelper = new genie_PluginHelper();
        $DM_PluginHelper->AddAdminMenues();


        //TODO: add reading from database
    }
}

function DM_CreateDesignFromHtml($design)
{
    echo $design;
}

function DM_CreateDesign($formName)
{
    $DM_PluginHelper = new genie_PluginHelper();
    echo $DM_PluginHelper->DM_CreateDesign($formName);

}


add_action('parse_request', 'genie_UploadImage');
add_action('admin_action_DMUploadImage', 'genie_UploadImage');
add_action('wp_ajax_nopriv_DMUploadImage', 'genie_UploadImage');
add_action('wp_ajax_DMUploadImage', 'genie_UploadImage');

function genie_UploadImage()
{

    // get the "author" role object


    // add "organize_gallery" to this role object

    $isLoadfile= strpos($_SERVER['REQUEST_URI'],"action=DMUploadImage");
    if($isLoadfile){
        $DM_PluginHelper = new genie_PluginHelper();
        echo $DM_PluginHelper->UploadImage($_FILES);
    }
    //die();
}

add_action('parse_request', 'genie_UploadFile');
add_action('admin_action_DMUploadFile', 'genie_UploadFile');
add_action('wp_ajax_nopriv_DMUploadFile', 'genie_UploadFile');
add_action('wp_ajax_DMUploadFile', 'genie_UploadFile');

function genie_UploadFile()
{

    $isLoadfile= strpos($_SERVER['REQUEST_URI'],"action=DMUploadFile");
    if($isLoadfile){
        $DM_PluginHelper = new genie_PluginHelper();
        echo $DM_PluginHelper->UploadFile($_FILES);
    }

}

add_filter( 'the_content', 'genie_content_filter', 20 );

function genie_content_filter( $content ) {
    $DM_PluginHelper = new genie_PluginHelper();
    $retcontent = $DM_PluginHelper->DM_ContentFilter($content);
    return $retcontent;
}


//plugin Widget
class genie_Widget extends WP_Widget {


    public function __construct() {
        $DM_DataBase=new genie_DataBase();
        $entityId=$DM_DataBase->getEntityId("DMSysEntities");
        $DM_CMSSpecials=new genie_CMSSpecials();
        $widgetName = $DM_CMSSpecials->Translate("Genie Widget",$entityId);
        $description = $DM_CMSSpecials->Translate("Use this widget to show display in sidebars areas",$entityId);

        parent::__construct(
            'genie_Widget', // Base ID
            $widgetName, // Name
            array( 'description' =>$description, ) // Args
        );
    }

    public function widget( $args, $instance ) {
        extract( $args );
        $title = apply_filters( 'widget_title', $instance['title'] );
        $formId= $instance['form_id'];


        echo $before_widget;
        if ( ! empty( $title ) )
            echo $before_title . $title . $after_title;

        $myDynamic =new genie_Form();
        $htmlToAdd= $myDynamic->getFormHtmlById($formId,false,true);




        echo __( $htmlToAdd, 'text_domain' );
        echo $after_widget;
    }

    public function update( $new_instance, $old_instance ) {

        $instance = array();
        $instance['title'] = strip_tags( $new_instance['title'] );

        $instance['form_id'] = strip_tags( $new_instance['form_id'] );

        return $instance;
    }

    public function form( $instance ) {
        $DM_CMSSpecials  = new genie_CMSSpecials();
        $DMDB= new genie_DataBase();
        $entity_Id=$DMDB->getEntityId("DMSysEntities");

        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
            $formid = $instance[ 'form_id' ];
        }
        else {
            $title = $DM_CMSSpecials->translate('New title', $entity_Id );
            $formid = '0';
        }


        $myDb=new genie_DataBase();

        $entityId=$myDb->getEntityId("DMSysEntities");
        $formIdHtml = $myDb->getFormsId($this->get_field_name( 'form_id' ),$this->get_field_id( 'form_id' ),  esc_attr( $formid ),$this->get_field_id( 'form_id' ));

        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php $DM_CMSSpecials->translate( 'Title',$entityId ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>

        <p>
            <?php
            echo $formIdHtml;
            ?>
        </p>
        <?php

    }


}

add_action( 'widgets_init', create_function( '', 'register_widget( "genie_Widget" );' ) );


add_action( 'add_meta_boxes', 'genie_add_custom_box' );

function genie_add_custom_box()
{
    $myDb=new genie_DataBase();

    $entityId=$myDb->getEntityId("DMSysEntities");
    $DM_CMSSpecials = new genie_CMSSpecials();
    $boxText = $DM_CMSSpecials->Translate("Add Display",$entityId);
    add_meta_box( 'genie_box',     // Unique ID
        $boxText,     // Title
        'genie_add_forms_box',     // Callback function
        'page',     // Admin page (or post type)
        'side',     // Context
        'high'     // Priority
    );

    add_meta_box( 'genie2_box',     // Unique ID
        $boxText,     // Title
        'genie_add_forms_box',     // Callback function
        'post',     // Admin page (or post type)
        'side',     // Context
        'high'     // Priority
    );

}

function genie_add_forms_box(  ) {

    $DM_PluginHelper = new genie_PluginHelper();
    echo $DM_PluginHelper->DM_CreateMetaBox();
}

function genie_shortGenie_func( $atts ) {
    $attributes = shortcode_atts( array(
        'form_id' => '',
        'form_name'=>''
    ), $atts );
    $DMCMS = new genie_CMSSpecials();
    $DMDataBase=new genie_DataBase();
    $entityId=$DMDataBase->getEntityId("DMSysEntities");
    $formHtml=$DMCMS->Translate("Display Not Found",$entityId);
    $whereParams=array();
    if(isset($attributes['form_id']) && trim($attributes['form_id'])!=null)
    {
        $whereParams=array('id'=>$attributes['form_id']);
    }

    if(count($whereParams)==0 && isset($attributes['form_name']) && trim($attributes['form_name'])!=null)
    {
        $whereParams=array('displayName'=>$attributes['form_name']);
    }


    if($DMDataBase->CheckIfNotEmpty($whereParams))
    {
        $whereParams=$DMDataBase->createParamsFromRow($whereParams,false);
        $forms=$DMDataBase->Select("DMSysForms",$whereParams,false,false);
        if($DMDataBase->CheckIfNotEmpty($forms))
        {
            $id=$forms[0]["id"];
            $DMForm=new genie_Form();
            $formHtml = $DMForm->getFormHtmlById($id);
            $dir = plugin_dir_url(dirname(__FILE__));
            $dirImages=$dir."images";
            $formHtml=$DMForm->ReplaceTagAll("[@Images@]",$dirImages,$formHtml);
        }
    }
    return $formHtml;

}

add_shortcode( 'genie', 'genie_shortGenie_func' );

//set_site_transient( 'update_plugins', null );


function genie_custom_rewrite_basic() {
    $DM_PluginHelper = new genie_PluginHelper();
    $DM_PluginHelper->addQueryTags();

}


add_action('init', 'genie_custom_rewrite_basic');

function genie_add_query_vars($aVars) {

    $DM_PluginHelper = new genie_PluginHelper();
    $defQueryValues=$DM_PluginHelper->getQueryTags();
    $DM_form = new genie_Form();
    foreach($defQueryValues as $defQueryValue)
    {
        if($DM_form->checkPosition($defQueryValue["value"],"[@queryParam@]"))
        {
            $postParameter =  "field_id_".$defQueryValue["definition_id"];
            $aVars[] .= $postParameter;
        }
    }
    // represents the name of the product category as shown in the URL
    return $aVars;
}

// hook add_query_vars function into query_vars
add_filter('query_vars', 'genie_add_query_vars');

add_action( 'wp_head', 'genie_action_javascript' ); // Write our JS below here
add_action( 'admin_head', 'genie_action_javascript' ); // Write our JS below here

function genie_action_javascript() { ?>
    <script type="text/javascript" >
        function DM_getUrl() {
         return "<?php echo admin_url('admin-ajax.php'); ?>";
        }
    </script> <?php
}
?>