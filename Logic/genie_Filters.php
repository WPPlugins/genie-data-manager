<?php
  include_once dirname(dirname(__FILE__)) . '/Logic/genie_Form.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_GeneralUsage.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_DynamicCreation.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_SystemManager.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_DataBase.php';

class genie_Filters
{
    
    public function __construct()
      {
           
      }
      
        public function getCodeTranslate($code,$args)
        {
            $DM_GeneralUsage= new genie_GeneralUsage();
              $className = "genie_Filters";
            $class_methods = get_class_methods($className);
     
                foreach ($class_methods as $method_name) {
                    if("[@".$method_name."@]"==$code && $method_name!="getCodeTranslate")
                    {
                        $class = new ReflectionClass($className);     
                       $class_instance = $class->newInstanceArgs($args);
                       if(method_exists($class_instance, $method_name))
                       {
                            $reflectionMethod = new ReflectionMethod($className, $method_name);
                            return $reflectionMethod->invoke($class_instance, $args);     
                       }
                       else
                       {
                           
                            return "error";
                       }
                    }
                }          
            
        }
        
        public function DM_GetTables($args)
        {
           $DM_DataBase  = new genie_DataBase();
            $DM_CMSSpecials=new genie_CMSSpecials();
           $id="tableName";
           $inputName=$id;
           $inputId=$id;
             $entityId = $DM_DataBase->getEntityId("DMSysForms");
            $result="<p><label for='".$id."'>".$DM_CMSSpecials->translate( 'Tables' ,$entityId) ."</label></p> <select name='".$inputName."' id='".$inputId."'>";
            $whereArray=array();
            $tables = $DM_DataBase->getThisBlogTables(); 
            if($DM_DataBase->CheckIfNotEmpty($tables))
            {
                foreach($tables as $table)
                {
                     $tableName=$table['tableName'];
                      $whereParams = $DM_DataBase->createParamsFromRow(array("tableName"=>$tableName),false);
                       $existing = $DM_DataBase->Select("DMSysEntities",$whereParams) ;
                       if(!$DM_DataBase->CheckIfNotEmpty($existing))
                       {
                            $result.=" <option  value=".$tableName.">".$tableName."</option>"; 
                       }
                }
            } 
             $result.=" </select>";     
             return $result;
        }  
        
        public function DM_GetEntitiesForTemplate($args)
        {
           $DM_DataBase  = new genie_DataBase();
            $DM_CMSSpecials=new genie_CMSSpecials();
           $id="entityName";
           $inputName=$id;
           $inputId=$id;
            $result="<p><label for='".$id."'>".$DM_CMSSpecials->translate( 'Data Types' ,$entityId) ."</label></p> ";
            $whereArray=array();
            if(!GENIE_DEVELOPMENT_MODE)
            {
                $whereParams = $DM_DataBase->createParamsFromRow(array("isSystem"=>0),false);
            }
            $tables = $DM_DataBase->Select("DMSysEntities",$whereParams,false) ; 
            $counter=0;
            if($DM_DataBase->CheckIfNotEmpty($tables))
            {
                 
                foreach($tables as $table)
                {
                   
                     
                       $result.='<div class="input-control checkbox'.$color.'" data-role="input-control">';
                        
                        $result.="<label><label><input  type=\"checkbox\" name=\"entityName\" value=\"".$table["id"]."\"  /><span class='check'></span>".$table["name"]."</label></label>";
                        $result.="</div><br/>";   
                        $counter++;
                     
                          
                       
                }
                
            } 
             
             return $result;
        }     
        
          public function DM_GetSystemTables($args)
        {
           $DM_DataBase  = new genie_DataBase();
            $DM_CMSSpecials=new genie_CMSSpecials();
           $id="sysTableName";
           $inputName=$id;
           $inputId=$id;
             $entityId = $DM_DataBase->getEntityId("DMSysForms");
            $result="<p><label for='".$id."'>".$DM_CMSSpecials->translate( 'Tables' ,$entityId) ."</label></p> <select name='".$inputName."' id='".$inputId."'>";
            $whereArray=array();
            if(!GENIE_DEVELOPMENT_MODE)
            {
                $whereParams = $DM_DataBase->createParamsFromRow(array("isSystem"=>0),false);
            }
            $tables = $DM_DataBase->Select("DMSysEntities",$whereParams,false) ; 
            if($DM_DataBase->CheckIfNotEmpty($tables))
            {
                foreach($tables as $table)
                {
                     $tableName=$table['tableName'];
                     
                            $result.=" <option  value=".$tableName.">".$tableName."</option>"; 
                       
                }
            } 
             $result.=" </select>";     
             return $result;
        }      
}