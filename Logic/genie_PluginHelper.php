<?php
include_once dirname(dirname(__FILE__)) . '/Logic/genie_Form.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_GeneralUsage.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_DynamicCreation.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_SystemManager.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_DataBase.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_WizardManager.php';

  class genie_PluginHelper
  {
	//genie: DM_install
      public function Install($networkwide)
      {
          //TODO: initiate database
        
            global $wpdb;
           if (function_exists('is_multisite') && is_multisite()) {
                // check if it is a network activation - if so, run the activation function for each blog id
                if ($networkwide) {
                        $old_blog = $wpdb->blogid;
                    // Get all blog ids
                    $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
                    foreach ($blogids as $blog_id) {
                        switch_to_blog($blog_id);
                             $this->ActivateThisBlog();
                    }
                    switch_to_blog($old_blog);
                    return;
                   
                    
                }
             
            } 
            else
            {
                 $this->ActivateThisBlog();
            }
            
            
      }
      
	//this: Install, genie: DM_new_blog
      public function ActivateThisBlog()
      {
          $this->UpdateThisBlog();
      }
      
	//genie: FM_update_admin_notice
      public function CheckNeedToBeUpgraded()
      {
          $DM_Table = new genie_Table();
          $DM_DataBase = new genie_DataBase();
          $DM_GeneralUsage=new genie_GeneralUsage();
          $filePath ="SystemState/".GENIE_CURRENT_VERSION;
          
          $reloadTableState=true;
          if($DM_Table->CheckIfTableExistsOnDataBase("DMSysEntities"))
          {
              $whereParams = $DM_DataBase->createParamsFromRow(array('tableName'=>'DMSysEntities'));
              $entities = $DM_DataBase->Select("DMSysEntities",$whereParams,false,false);
              if($DM_DataBase->CheckIfNotEmpty($entities) && isset($entities[0]['Version']))
              {
                  $entityVersion = $entities[0]['Version'];
                  if($DM_GeneralUsage->checkPosition($filePath,trim($entityVersion)))
                  {
                      $reloadTableState=false;
                  }
              }
              
          }
          return $reloadTableState;
      }
      
	//genie:DMUpdateDataBase, genie: DM_scripts_method
      public function UpdateThisBlog()
      {
          
          $DM_DataBase = new genie_DataBase();
          $filePath =dirname(dirname(__FILE__)) ."/files/SystemState/".GENIE_CURRENT_VERSION.".zip";
          if($this->CheckNeedToBeUpgraded())
          {
              if(file_exists($filePath)) {
                  $DMGeneralUsage = new genie_GeneralUsage();
                  $DMGeneralUsage->unzip($filePath, GENIE_CURRENT_VERSION . ".zip");
              }
              $systemStatePath="/SystemState/".GENIE_CURRENT_VERSION;
              $result = $DM_DataBase->LoadSystemState($systemStatePath, true);
          }
      }
      
	
	  //genie:DM_remove
      public function Remove()
      {
           //nothing right now
      }
      
	//genie: prefix_ajax_DMDynamicRequest 
      public function ManageRequest($request)
      {
          //TODO: validate version of code
           $DM_Form=new genie_Form();
           echo $DM_Form->Manage($request);
           die();
      }
      
	
	//genie:prefix_ajax_DMSystemDynamicRequest 
      public function ManageSystemRequest($request)
      {
          //TODO: validate version of code
           $DM_SystemManager=new genie_SystemManager();
           echo $DM_SystemManager->Manage($request);
           die();
      }

	//genie:prefix_ajax_DMWizardRequest
       public function ManageWizardRequest($request)
      {
          //TODO: validate version of code
           $DM_WizardManager=new genie_WizardManager();
           echo $DM_WizardManager->Manage($request);
           die();
      }      
      
      
       public function UploadImage($PostFILES)
    {
        /*
        $upload_overrides = array( 'test_form' => false );  
        $uploadedfile = $PostFILES['file'];
        $upload = wp_handle_upload($uploadedfile,$upload_overrides);
        */
         $upload=$this->internalUploadImage($PostFILES);
        if(isset($upload['error']))
        {
           return " error:".$upload['error']; 
        }
        else
        {
            return $upload['url'];
        }
        
    }
    
      public function UploadFile($PostFILES)
    {
        /*
        $upload_overrides = array( 'test_form' => false );  
        $uploadedfile = $PostFILES['file'];
        $upload = wp_handle_upload($uploadedfile,$upload_overrides);
        */
         $upload=$this->internalUploadFile($PostFILES);
        if(isset($upload['error']))
        {
           return " error:".$upload['error']; 
        }
        else
        {
            return $upload['url'];
        }
        
    }
    
    private function internalUploadImage($PostFILES)
    {
         $upload_dir = wp_upload_dir();
        $targetFolder = $upload_dir['basedir']."/".$upload_dir['subdir']; // Relative to the root
        if (!empty($PostFILES)) {
            $tempFile = $PostFILES['file']['tmp_name'];
            $targetPath = $targetFolder;
            $targetFile = rtrim($targetPath,'/') . '/' . $PostFILES['file']['name'];
            
            // Validate the file type
            $fileTypes = array('jpg','jpeg','gif','png'); // File extensions
            $fileParts = pathinfo($PostFILES['file']['name']);
            
            if (in_array($fileParts['extension'],$fileTypes)) {
                move_uploaded_file($tempFile,$targetFile);
                 if(file_exists($targetFile))
                {
                    return array('url'=>$upload_dir['baseurl'].''.$upload_dir['subdir']."/".$PostFILES['file']['name']);
                }
                else 
                {
                    return array('error'=>'File could not be written');
                }
            } else {
                return array('error'=>'File Extension do not match');
            }
        }
    }
    
      private function internalUploadFile($PostFILES)
    {
         $upload_dir = wp_upload_dir();
        $targetFolder = $upload_dir['basedir']."/".$upload_dir['subdir']; // Relative to the root
        if (!empty($PostFILES)) {
            $tempFile = $PostFILES['file']['tmp_name'];
            $targetPath = $targetFolder;
            $targetFile = rtrim($targetPath,'/') . '/' . $PostFILES['file']['name'];
            
            // Validate the file type
            $fileTypes = array('jpg','jpeg','gif','png','pdf','doc','xls','docx','txt','xsls','zip'); // File extensions
            $fileParts = pathinfo($PostFILES['file']['name']);
            
            if (in_array($fileParts['extension'],$fileTypes)) {
                move_uploaded_file($tempFile,$targetFile);
                if(file_exists($targetFile))
                {
                    return array('url'=>$upload_dir['baseurl'].''.$upload_dir['subdir']."/".$PostFILES['file']['name']);
                }
                else 
                {
                    return array('error'=>'File could not be written');
                }
            } else {
                return array('error'=>'File Extension do not match');
            }
        }
    }
      
	//genie: DM_scripts_method
      public function RegisterScripts($dirUrl)
      {
          
            //TODO: validate version of code
            $dir =  dirname(dirname(__FILE__)) ;
            $DM_GeneralUsage=new genie_GeneralUsage();
           
            $files = array();
            array_push($files,array('path'=>"/files/cssDependencies.txt","type"=>'style', 'register'=>'wp_register_style','deregister'=>'wp_deregister_style','enque'=>'wp_enqueue_style', 'filename'=>'css','addtopath'=>'css/'));
            array_push($files,array('path'=>"/files/jqueryDependencies.txt","type"=>'script', 'register'=>'wp_register_script','deregister'=>'wp_deregister_script','enque'=>'wp_enqueue_script', 'filename'=>'script', 'addtopath'=>'jscripts/'));
            
            foreach($files as $file)
            {
                 $myDependencies = $DM_GeneralUsage->getData($dir.$file['path']);
                 foreach($myDependencies as $dependency)
                 {
                     $key = $dependency['url'];
                     $pos = strrpos($_SERVER["REQUEST_URI"],$key);
                     $pos6= strrpos($_SERVER["REQUEST_URI"],"wp-admin");  
                     
                     $pottalicMenu = $this->checkPosition($_SERVER["REQUEST_URI"],"portalic_data_menu");
                     $jname='jqury'.$file['type'].'_'.$dependency['url']."_".$dependency['name'];
                     if($pos || (!$pos6 && $dependency['url']=="frontPage") || ($pos6 && $dependency['url']=="wpadmin") ||($dependency['url']=="frontPage" && $pottalicMenu ) )
                     {
                         $fileurl = $dependency[$file['filename']];
                         if($dependency['type']=='internal')
                         {
                              $fileurl = $dirUrl.$file['addtopath'].$dependency[$file['filename']];
                         }
                         
                         call_user_func($file['deregister'], $jname);
                         call_user_func($file['register'], $jname,$fileurl);
                         call_user_func($file['enque'], $jname);
                     }
                 }
            }
            
            $this->addQueryTags();
   
      }
      
      
      public function getQueryTags()
      {
           $DM_DataBase = new genie_DataBase();
        $DM_CMS = new genie_CMSSpecials();
        $cachedName = "DFQValues";
        $DMDFQValues=$DM_CMS->getDataFromCache($cachedName);
        if($DMDFQValues==null)
        {
            $DMDFQValues = $DM_DataBase->Select("DMSysDefaultQueryValues",array(),false,false);
            $DM_CMS->saveDataToCache($cachedName,$DMDFQValues);
        }
        $cachedName = "defQueryValues";
        $defQueryValues=$DM_CMS->getDataFromCache($cachedName);
        if($defQueryValues==null)
        {
            $defQueryValues = array();
            foreach($DMDFQValues as $DMFQValue)
            {
                if($this->checkPosition($DMFQValue["value"],"[@queryParam@]"))
                {   
                    array_push($defQueryValues, array("form_id"=>$DMFQValue["form_id"],"value"=>$DMFQValue["value"]));
                }
            }
            $DM_CMS->saveDataToCache($cachedName,$defQueryValues);
        }
        return $defQueryValues;
      }
      
      public function addQueryTags()
      {
        $DM_DataBase = new genie_DataBase();
        $DM_CMS = new genie_CMSSpecials();
       
        $defQueryValues=$this->getQueryTags();
        if($DM_DataBase->CheckIfNotEmpty($defQueryValues))
        {
            foreach($defQueryValues as $defQueryValue)
            {
                $cachedName="posts_to_form_".$defQueryValue["form_id"];
                $postParameter =    str_replace("[@queryParam@]","",$defQueryValue["value"]); 
                $poststoform = $DM_CMS->getDataFromCache($cachedName);
                if($poststoform==null)
                {
                    $prefix = $DM_CMS->getprefix();
                    $posttable = $prefix."posts";
                    $tag = "[@form_id_".$defQueryValue["form_id"]."@]";
                    $query = "select * from ".$posttable." where post_content like '%".$tag."%'";
                    $poststoform= $DM_CMS->getResults($query);
                    if($DM_DataBase->CheckIfNotEmpty($poststoform))
                    {
                        $DM_CMS->saveDataToCache($cachedName,$poststoform);
                                        
                    }
                }
                if($DM_DataBase->CheckIfNotEmpty($poststoform))
                {
                    global $wp_rewrite;
                    foreach($poststoform as $postform)
                        {
                            $posttitle=$postform["post_name"];
                            //add_rewrite_rule("index.php/".$postParameter.'/(.+?)', 'index.php/'.$postParameter, 'top');
                           //Good one
                            //add_rewrite_rule("index.php/".$postParameter."/(.+?)/".$posttitle, 'index.php?pagename='.$posttitle.'&'.$postParameter.'=$matches[1]', 'top');
                            //
                           // add_rewrite_rule("index.php/catalog/".$posttitle.'/'.$postParameter.'/(.+?)', 'index.php/'.$posttitle.'/?'.$postParameter.'=$matches[1]', 'top');
                           
                        }
                   // flush_rewrite_rules(true);
                } 
            }
        }
      }
      
      
      
     
      
       public function checkPosition($pattern, $tag)
      {
          $pos =  strrpos($pattern,$tag);
           if($pos===false)
              {
                return false;
              }
          return true;
            
      }
      
	//genie: DM_admin_menu
      public function AddAdminMenues()
      {
          //TODO: validate version of code
          //TODO: load menus from table
           $DM_CMSSpecials= new genie_CMSSpecials();
            $userRole = strtolower($DM_CMSSpecials->get_current_user_role());
            
            if($userRole=="administrator")
            {
                      $dir =  dirname(dirname(__FILE__)) ;
                       $DM_DataBase = new genie_DataBase();
                        $DM_CMSSpecials=new genie_CMSSpecials();
                        $entityId=$DM_DataBase->getEntityId("DMSysEntities");
                        
                    $DM_GeneralUsage=new genie_GeneralUsage();
                        $myMenus = $DM_GeneralUsage->getData($dir."/files/adminmenus.txt");
                       
                        foreach($myMenus as $menu)
                        {
                          
                            switch($menu['type'])
                            {
                                case "menu":
                                    add_menu_page($menu['name'], $DM_CMSSpecials->Translate($menu['name'],$entityId), 'manage_options', $menu['uniqueid'], create_function('','DM_CreateDesign("'.$menu['formname'].'");'));
                                    break;
                                case "submenu":
                                    add_submenu_page($menu['parentuniqueid'],$menu['name'], $DM_CMSSpecials->Translate($menu['name'],$entityId), 'manage_options', $menu['uniqueid'], create_function('','DM_CreateDesign("'.$menu['formname'].'");'));
                                    
                                    break;
                                case "subpage":
                                  add_submenu_page(null,$menu['name'], $DM_CMSSpecials->Translate($menu['name'],$entityId), 'manage_options', $menu['uniqueid'], create_function('','DM_CreateDesign("'.$menu['formname'].'");'));
                                break;
                            }
                        }
            }
            
            $DM_DataBase = new genie_DataBase();
            $whereParams = $DM_DataBase->createParamsFromRow(array("isMenu"=>"1"),false);
          $forms = $DM_DataBase->Select("DMSysForms",$whereParams,false,false);
          $design="";
          if($DM_DataBase->CheckIfNotEmpty($forms))
          {
               foreach($forms as $form)
              {
                  $tableName=$DM_DataBase->GetTableNameByEntity($form["entity_id"]);
                   
                    if($DM_DataBase->getPermission($tableName,"Read"))
                    {
                        $design .="<a href='admin.php?page=genie_data_menu_".$form["id"]."'>".$form['displayName']."</a>";
                        }
              }
              
			add_menu_page("Genie Data Menu", $DM_CMSSpecials->Translate("Genie Data Menu",$entityId), 'manage_options', 'genie_data_menu', create_function('','DM_CreateDesignFromHtml("'.$design.'");'));
                
              foreach($forms as $form)
              { 
                  $tableName=$DM_DataBase->GetTableNameByEntity($form["entity_id"]);
                  
                  if($DM_DataBase->getPermission($tableName,"Read"))
                    {
                     $tag ="[@form_id_".$form['id']."@]";  
					add_submenu_page('genie_data_menu',$form['displayName'], $DM_CMSSpecials->Translate($form['displayName'],$entityId), 'manage_options', 'genie_data_menu_'.$form['id'], create_function('','DM_CreateDesign("'.$tag.'");'));
                    }
                                       
              }
                       
          }
      }
      
      function DM_CreateDesignFromHtml($design)
      {
          return $design;
      }
      
      function DM_CreateDesign($formName)
      {
          $DM_Form = new genie_Form();
          return $DM_Form->GetHtml($formName);
      }
      
      function DM_ContentFilter($content)
      {
            $DM_Form = new genie_Form();
          return $DM_Form->getFormHtmlByContent($content);
      }
      
      function DM_CreateMetaBox()
      {
           $DM_Form = new genie_Form();
          return $DM_Form->CreateMetaBox() ;
      }
      
    
  }