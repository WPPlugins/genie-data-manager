<?php
  class genie_CMSSpecials
  {
  public function checkPosition($pattern, $tag)
      {
          $pos =  strrpos($pattern,$tag);
           if($pos ===false)
              {
                return false;
              }
          return true;
            
      }
      
       public function GetDefaultFromValue($value,$definition_id,$url=null)
     {
         if($this->checkPosition($value,"[@user@]"))
          {
              $parameter = str_replace("[@user@]","",$value);
              $userData = $this->getUserParam($parameter);
              if($userData!=null)
              {
                  $value = $userData;
              }
              else
              {
                  $value="";
              }
          }
           if($this->checkPosition($value,"[@bloginfo@]"))
          {
              $parameter =  str_replace("[@bloginfo@]","",$value);  
              $userData = $this->getBlogParam($parameter);
              if($userData!=null)
              {
                  $value = $userData;
              }
          }
          
          
           if($this->checkPosition($value,"[@queryParam@]"))
          {
            
              $parameter="field_id_".$definition_id;
              $userData = $this->getQueryParam($parameter,$url);
              if($userData!=null)
              {
                  $value = $userData;
              }
            
              
          }
            
          
          
          
            if($this->checkPosition($value,"[@ip@]"))
          {
                     if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
                            $ip = $_SERVER['HTTP_CLIENT_IP'];
                            } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
                            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                            } else {
                                $ip = $_SERVER['REMOTE_ADDR'];

                                }
                                $value = $ip;
          }
          
           if($this->checkPosition($value,"[@date@]"))
          {
             $value = date('Y-m-d', time());   
          }
          
         
          return $value;
     }
      
     public function getQueryParam($parameter,$url)
     {
        $parts = parse_url($url);
        parse_str($parts['query'], $query);
        return $query[$parameter];
     }
      
      public function getUserParam($param)
      {
            $user = wp_get_current_user();
                 if($user!=null)
                 {
                   switch($param)
                   {
                       case "ID": return $user->ID;
                           break;
                       case "user_login": return $user->user_login ;
                            break;
                       case "user_email": return $user->user_email  ;
                            break;
                       case "user_firstname": return $user->user_firstname   ;
                            break;
                       case "user_lastname": return $user->user_lastname;
                            break;
                       case "display_name": return $user->display_name;
                            break;
                       default:
                        break;
                   }
                 }
            return "";
      }
      
      
      public function getBlogParam($param)
      {
                 $value = get_bloginfo($param);   
                   if(isset($value))
                   {
                       
                      return $value;
                   }
            return "";
      }
      
     
      
      
       public function getResults($query)
      {
          global $wpdb;
          $myrows = $wpdb->get_results( $query,ARRAY_A );
          return $myrows;
      }
      
      public function execute($query)
      {
          global $wpdb;
          global $wp_error;
          if($wpdb->query($query)===false)
          {
              if ( $wp_error ) {
                      return false;
                  }        
          }
          return true;
      }
      
       public function getLastAddedId()
    {
         global $wpdb;
         return $wpdb->insert_id;
    }
    
    
    
     public function getLastError()
    {
         global $wpdb;
         return $wpdb->last_error;
    }
      
      public static function sendMail($to,$message,$subject)
    {
        add_filter('wp_mail_content_type',create_function('', 'return "text/html";'));
        wp_mail($to,$subject,$message);
    }
    
    
    public function GetTableName($tableName)
      {
          global $wpdb; 
		
		
            if (function_exists('is_multisite') && is_multisite()) {  
                return $wpdb->prefix.$tableName;
            }
            else
            {
               return $wpdb->prefix.$tableName; 
            }

      }
      
      public function getprefix()
      {
         global $wpdb; 
            if (function_exists('is_multisite') && is_multisite()) {  
                return $wpdb->prefix;
            }
            else
            {
               return $wpdb->prefix; 
            } 
      }
      
      
          public  function loadTranslation($language)
            {


                //DF_Session::StoreSession('translate_'.$GLOBALS['language'],"true");
                  wp_cache_set('translate_'.$language,"true");
                  $tableName = $this-> GetTableName("DMSysTranslations");
                   $DM_Database = new genie_DataBase();
         $whereArrayParams = $DM_Database->createParamsFromRow( array('language'=>$language),false);
                

                $results=$DM_Database->Select("DMSysTranslations",$whereArrayParams,false,false);

                if($DM_Database->CheckIfNotEmpty($results))
                {

                    foreach($results as $row)
                    {
                        if(isset($row['keyVal']) && isset($row['translation']))
                        {
                            
                             $str = $row['keyVal']  ;
                              $entityId = $row['entity_id'];
                             $valueWithNoTags = preg_replace("/[^a-zA-Z0-9]/", "", $str);
                             if($valueWithNoTags!="")
                             {
                                wp_cache_set('translate_'. $valueWithNoTags.'_'.$language."_".$entityId,$row['translation']);
                             }
                        }
                    }
                }
            }
    
    public function Remember($value,$entityId=0)
    {
         $language= get_bloginfo("language");
          //$language="ru-RU";  
         $addedId=-1;
         $DM_Database = new genie_DataBase();
         $whereArrayParams = $DM_Database->createParamsFromRow( array('keyVal'=>$value,'language'=>$language,'entity_id'=>$entityId),false);
         $translation = $DM_Database->Select("DMSysTranslations",$whereArrayParams,false);
         if(!$DM_Database->CheckIfNotEmpty($translation))
         {
             $IsSystem=false;
               if($entityId!=0)
                   { 
                    $DMDataBase = new genie_DataBase();
                       $tableDetails = $DMDataBase->GetTableDetailsByEntity($entityId);
                      
                       if($DMDataBase->CheckIfNotEmpty($tableDetails))
                       {
                           if(isset($tableDetails["isSystem"]))
                           {
                                $IsSystem = ($tableDetails["isSystem"]==1?true:false);
                           }
                           
                       }
                   }   
                   
             $insertParams =    $DM_Database->createParamsFromRow( array('keyVal'=>$value,'language'=>$language,'translation'=>$value,'entity_id'=>$entityId,"isSystem"=>$IsSystem),false);
             $addedId = $DM_Database->Insert("DMSysTranslations",$insertParams,false,false);
         }
         return $addedId;
    }
    
      public function Translate($value,$entityId=0)
      {
          
            $result=$value;

            //$valueWithNoTags = trim(str_replace(array('\'','\"'), "", $value));
            
            $str = $value  ;

            $valueWithNoTags = preg_replace("/[^a-zA-Z0-9]/", "", $str);

            if($valueWithNoTags!="")
            {
              
                $language= get_bloginfo("language");
                // $language="ru-RU";  
              
               $translateLanguage = wp_cache_get('translate_'.$language);  //DF_Session::LoadSession('translate_'.$language);

                 
                if( ($translateLanguage==null || $translateLanguage==false || !(isset($translateLanguage))))
                {
                      $this->loadTranslation($language);
                }
                $translateLanguageValue = wp_cache_get('translate_'.$valueWithNoTags.'_'.$language."_".$entityId);
               
               
                 if($translateLanguageValue!=null && $translateLanguageValue!=false && isset($translateLanguageValue))
                {
                    $result = $translateLanguageValue;
                    
                }
                else
                {
                 
                    $this->Remember($result,$entityId);
                    wp_cache_set('translate_'. $valueWithNoTags.'_'.$language."_".$entityId,$value);
                }
            }
          
            return $result;
      }
      
      public function getError($value)
      {
          return $value;
      }
      
       public function getSuccess($value)
      {
          return $value;
      }
      
      function get_current_user_role() {
         
            global $wp_roles;
            if(is_user_logged_in())
            {
            $current_user = wp_get_current_user();
            $roles = $current_user->roles;
            $role = array_shift($roles);
            return isset($wp_roles->role_names[$role]) ? $wp_roles->role_names[$role]  : "guest";
            }
            else
            {
                return "guest";
            }
         }
       public function getDataFromCache($dataName)
      {
          
         /* $lastSaved = wp_cache_get($dataName.'_lastSaved');
          $lastChanged = wp_cache_get($dataName.'_lastChanged');
          if($lastSaved!=null && $lastSaved>$lastChanged)
          {*/
          $dataName = $this->getprefix()."_".$dataName;
              $data= wp_cache_get($dataName);
          /*}*/
          if(!$data)
            return null;
          return $data;
      }
      
      public function saveDataToCache($dataName,$data)
      {
           $now = new DateTime();
           $dataName = $this->getprefix()."_".$dataName;
           wp_cache_set($dataName,$data);
           wp_cache_set($dataName."_lastSaved",$now);
      }
      
      public function setDataChanged($dataName)
      {
          $now = new DateTime();
          $dataName = $this->getprefix()."_".$dataName;
          wp_cache_set($dataName."_lastChanged",$now);
      }
      
  }
