<?php
include_once dirname(dirname(__FILE__)) . '/Logic/genie_DataBase.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_Table.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_GeneralUsage.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_CMSSpecials.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_Template.php';
//genie_Table
  class genie_Definition
  {
   
      private $mappings = null;
      private $inputmappings = null;  
      
      private function VarIsSet($var)
      {
          if(isset($var) && trim($var)!="")
          {
              return true;
          }
          return false;
      }
     
      public function GetFieldDBType($type)
      {
           $fileName =  dirname(dirname(__FILE__)).'/files/typesToDb.txt' ; 
           $DM_GeneralUsage  = new genie_GeneralUsage();
             
           $mappings = $DM_GeneralUsage->getData($fileName);   
           if($mappings!=null)
           {
              foreach($mappings as $map)
              {
                 
                  if(isset($map['type']) && ltrim($map['type'])==ltrim($type))
                  {
                      return $map['dbtype'];
                  }
              } 
           }
           return "varchar(50) COLLATE utf8_unicode_ci";
      }
      
      public function GetMappings($type, $inputType=false)
      {  
          
          $mappings = $this->mappings;
          $fileName =  dirname(dirname(__FILE__)).'/files/fieldMappings.txt' ;
          if($inputType)
          {
              
              $mappings = $this->inputmappings; 
              $fileName =  dirname(dirname(__FILE__)).'/files/inputTypeMapping.txt' ;   
              
             
          }
          if($mappings==null)
          {
             $DM_GeneralUsage  = new genie_GeneralUsage();
             
             $mappings = $DM_GeneralUsage->getData($fileName);         
          }
          if($mappings!=null)
          {
              if($inputType)
              {
                  $this->inputmappings=$mappings;
              }
              else
              {
                  $this->mappings=$mappings; 
              }
              foreach($mappings as $map)
              {
                 
                  if(isset($map['type']) && ltrim($map['type'])==ltrim($type))
                  {
                   
                      return $map;
                  }
              }
          }
          return null;
      }
      
      
      
      public function GetMappingByMapType($type,$format,$inputType=false)
      {
         
          if($inputType)
          {
               $DM_Template = new genie_Template();
               return $DM_Template->fieldMapping($type,$format);
          }
          $mappings = $this->GetMappings($type,$inputType);
       
          if($mappings!=null && count($mappings)>0 && isset($mappings[$format]))
          { 
            
              return $mappings[$format];
               
          }
          return "[@mapValue@]";
      }
      
      
      public function utf8_urldecode($str) {
          if(gettype($str)== "array")
          {
             
              $str = $str[0];
          }
        $str = preg_replace("/%u([0-9a-f]{3,4})/i","&#x\\1;",urldecode($str));
        return html_entity_decode($str,null,'UTF-8');;
     }
    
      public function getFormatted($definition, $format,$inputType=false,$multilanguage=false)
      {
          $DM_GeneralUsage = new genie_GeneralUsage();
          $DM_CMSSpecials=new genie_CMSSpecials();
          $DM_DataBase = new genie_DataBase();
          $type = 'type';
          if($inputType)
          {
            $type = 'input_type'; 
          }
          
          if(!isset($definition["entity_id"]) || trim($definition["entity_id"])=="" || $definition["entity_id"]==0)
          {
                  $entityId=  $DM_DataBase->getEntityId("DMSysEntities");
          }
          else
          {
              $entityId=$definition["entity_id"];
          }
          if(isset($definition[$type]))
          { 
              $pattern = $this->GetMappingByMapType($definition[$type],$format,$inputType); 
             
             
             if($multilanguage && $DM_GeneralUsage->checkPosition($pattern,'[@translate@]'))
             {
                 $pattern=$DM_GeneralUsage->translateAll($pattern,$definition["entity_id"]);
             }
             else
             {
                  $pattern=str_replace("[@translate@]","",$pattern);
                  $pattern=str_replace("[@endtranslate@]","",$pattern);
             }
             if(isset($definition["id"]))
             {
              $fieldValue = '<label data-bind="text:field_'.$definition["id"].'" style="font-weight:normal;" width="200"></label>';
              $fieldCode = 'field_'.$definition["id"];  
              
              
              $pattern = str_replace("[@fieldValue@]",$fieldValue,$pattern);   
              $pattern = str_replace("[@fieldCode@]",$fieldCode,$pattern);   
             }
              if($inputType)
              {
                 // echo "pattern:".$pattern."</br>";
                 $extra ="";
                 if($definition["isFormula"]==1 )
                 {
                     $extra ="_computed";
                 }      
                  $result = str_replace("[@field@]","field_".$definition["id"].$extra,$pattern);
                  
                   $strroot ='$root';
                    $strparent ='$parent';
                  $result = str_replace("[@fieldOptions@]","field_".$definition["id"]."_model",$result);
                  if( $definition["IsConnected"]==1)
                  {
                      $result = str_replace("[@root@].","",$result);
                        //$result = str_replace("[@root@]",$strroot,$result);    
                  }
                  else
                  {
                      
                        $result = str_replace("[@root@]",$strroot,$result);    
                  }
                  
                  
                  $result = str_replace("[@parent@]",$strparent,$result);
                 
                 $name=$definition["name"];
                  if($multilanguage)
                  {
                      $name = $DM_CMSSpecials->Translate( $definition["name"],$entityId);
                  }
                  $help="";
                
                 if($this->VarIsSet($definition["helpTxt"]) && (strrpos($result,"[@fieldName@]") || $result=="[@fieldName@]") )
                  {
                      
                      $unigue_ID = "id_".rand(1,10000000);
                      $dir = plugin_dir_url(dirname(__FILE__));   
                   $dirImages=$dir."images";
                    $help="<img style='border:transparent;'  src='".$dirImages."/helpEngine.png' data-bind='attr:{id:\"".$unigue_ID."\"+[@fieldIdId@]}' onmouseover=\"DM_ShowHideOnMouse(this,true)\"  onmouseout=\"DM_ShowHideOnMouse(this,false)\" />";
                    $helptext=$definition["helpTxt"];
                         
                    if($multilanguage)
                    {
                       $helptext = $DM_GeneralUsage-> translateAll($helptext,$entityId);
                    }
                    $help .= "<div data-bind='attr:{id:\"".$unigue_ID."\"+[@fieldIdId@]+\"_help\"}' class='DM_helpDiv'>".$helptext."</div>";
                    if($definition["id"]==118)
                    {
                        $check=2;
                    }
                    $result =$result." ".$help;
                    
                  }
                   
                   if($multilanguage)
                   {
                      $name =   $DM_CMSSpecials ->Translate($name,$entityId);
                   }
                  $result = str_replace("[@fieldName@]",$name,$result);
                  
                 $class="";
                  if($this->VarIsSet($definition["classTxt"]))
                  {
                    $class = "  class='{$definition["classTxt"]}' ";
                  }  
                  $result = str_replace("[@class@]",$class,$result);
                 
                 $style="";
                  if($this->VarIsSet($definition["styleTxt"]))
                  {
                    $style = "  style='{$definition["styleTxt"]}' ";
                  }  
                  $result = str_replace("[@style@]",$style,$result);
                 
                  //TODO: configure radio button and select
              }
              else
              {
                  
                  $value =    $definition['value'];
                  $CheckValue = trim((string)$definition['value']);
                 
                   if(strlen($CheckValue) == 0 || $CheckValue==""||$CheckValue==''||empty($CheckValue) || ( $definition['type']=='boolean' &&  $definition['value']!="1"))
                   {
                       $value=$this->GetMappingByMapType($definition['type'],"DefaultValue");                   
                   }
                    if($multilanguage)
                   {
                      $value =   $DM_CMSSpecials ->Translate($value,$entityId);
                   }
                   
                   //add here all format types connected to work with mysql query
                   if($format=="QueryFormat" || $format=="WhereFormat" )
                   {
                       $value = $this->utf8_urldecode($value);
                       //$value = mysql_real_escape_string($value);
                       $DM_GeneralUsage = new genie_GeneralUsage();
                       $value = $DM_GeneralUsage->mysql_escape_mimic($value);    
                   }
                   
                   
                   
                   if(isset($value))
                   {
                        $result = str_replace("[@mapValue@]",$value,$pattern);   
                   }
                   else
                   {
                        $result = str_replace("[@mapValue@]","",$pattern);   
                   }
              
              }
          }
          else
          {
              $result=$definition['value'];
              if($multilanguage)
              {
                 
                 $result =  $DM_CMSSpecials->Translate($result,$entityId);
              }
          }
          return $result;
      }
      
     
      
      
      public function GetFormattedForWherePart($definition)
      {
          return $this->getFormatted($definition,"WhereFormat");
          
      }
      
      public function GetFormattedForQueryPartValue($definition)
      {
          return $this->getFormatted($definition,"QueryFormat");
      }
      
       public function GetFormattedFieldValue($definition)
      {
          return $this->getFormatted($definition,"fieldReadOnly",true);
      }
      
       public function GetFormattedFieldCode($definition)
      {
          return $this->getFormatted($definition,"fieldCode",true);
      }
      
        public function GetFieldNameValueReadOnly($definition)
      {
          return $this->getFormatted($definition,"FieldNameValueReadOnly",true);
      }
      
       public function GetFieldNameValueForFormHtml($definition,$multilanguage=true)
      {
          return $this->getFormatted($definition,"FieldNameValue",true,$multilanguage);
      }
      
       public function GetFieldNameLabelReadOnly($definition,$multilanguage=false)
      {
          return $this->getFormatted($definition,"FieldNameLabelReadOnly",true,$multilanguage);
      }
      
      public function GetFieldNameLabelForFormHtml($definition,$multilanguage)
      {
          return $this->getFormatted($definition,"FieldNameLabel",true,$multilanguage);
      }
     
      public function UpdateHtmlTagOfDefinitions($definition, $html)
      {
          $definitionHtml= $this->GetFieldNameLabelForFormHtml($definition);
          $definitionLabelHtml= $this->GetFieldNameLabelForFormHtml($definition);
          $result = str_replace("[@field_id_".$definition['id']."@]",$definitionHtml,$html);
          $result = str_replace("[@label_id_".$definition['id']."@]",$definitionLabelHtml,$html);
          return $result;
      }
      
      public function AddValidators($definition, $validators,$tableDetails,$viewDetails)
      {
          $DM_CMSSpecials = new genie_CMSSpecials();
          $validatorsMapp = $this->GetMappingByMapType($definition['type'],"Validators");
          $validatorsArr=  explode ("&", $validatorsMapp);
          $name = 'field_'.$definition['id'];
          foreach($validatorsArr as $validator)
          {
              if($validator!="[@mapValue@]" && ltrim($validator)!="")
              {
                  $message = "wrong input";
                  if($viewDetails==null || $viewDetails['multilanguge']==1)
                  {
                       $message =$DM_CMSSpecials->Translate($message,$definition["entity_id"]);
                  }
                 
                    array_push($validators, array('name'=>$name,'validator'=>$validator,'message'=>$message));
                  
              }
          }
          if($definition['is_must']=="1")
          {
              $message = $tableDetails['mustFieldValidationError'];
              if($viewDetails==null || $viewDetails['multilanguge']==1)
              {
                   $message =$DM_CMSSpecials->Translate($message,$definition["entity_id"]);
              }
             array_push($validators, array('name'=>$name,'validator'=>'required','message'=>$message)); 
          }
           if($definition['input_type']=="email")
          {
              $message = $tableDetails['emailValidationError'];
              if($viewDetails==null || $viewDetails['multilanguge']==1)
              {
                   $message =$DM_CMSSpecials->Translate($message,$definition["entity_id"]);
              }
             array_push($validators, array('name'=>$name,'validator'=>'email','message'=>$message)); 
          }
           if($definition['input_type']=="date")
          {
               $message = $tableDetails['dateValidationError'];
              if($viewDetails==null || $viewDetails['multilanguge']==1)
              {
                   $message =$DM_CMSSpecials->Translate($message,$definition["entity_id"]);
              }
             array_push($validators, array('name'=>$name,'validator'=>'date','message'=>$message)); 
          }
           if($definition['type']=="integer")
          {
               $message = $tableDetails['numberValidationError'];
              if($viewDetails==null || $viewDetails['multilanguge'])
              {
                   $message =$DM_CMSSpecials->Translate($message,$definition["entity_id"]);
              }
             array_push($validators, array('name'=>$name,'validator'=>'number','message'=>$message)); 
          }
          return $validators;
      }
      
      public function AddModelForField($definition,&$models)
      {
          if($definition['input_type']=="dropdownlist" || $definition['input_type']=="radio")
          {
              $defmodel = array();
              $values = explode (",",$definition['valuesList']);
              foreach($values as $value )
              {
                  array_push($defmodel, $value);
              }
               $groupModel = array('name'=>'field_id_model_'.$definition['id'],'type'=>'fieldValues','data'=>$defmodel);
               array_push($models,$groupModel);  
          }             
      }
      
      public function AddFieldFormulas($definitions,$definition,$formulas)
      {
          
          if($definition["isFormula"])
          {
              $cachedName="formula_definition_".$definition["id"];
              $DM_CMS=new genie_CMSSpecials();
              $currFormula=$DM_CMS->getDataFromCache($cachedName);
              if($currFormula==null)
              {
                  $formula = $definition["formula"];
                  $formula = $this->ReplaceTagAll("[@plus@]","+",$formula);
                  $formula=$this->ReplaceTagAll("[@minus@]","-",$formula);
                  $formula=$this->ReplaceTagAll("[@multiply@]","*",$formula);
                  $formula=$this->ReplaceTagAll("[@divide@]","/",$formula);
                  $fields = array();
                  foreach($definitions as $def)
                  {
                     if($this->checkPosition($formula,"[@field_".$def["id"]."@]")) 
                     {
                         array_push($fields,"field_".$def["id"]);
                     }
                     
                  }
                  $currFormula = array('current'=>'field_'.$definition["id"], 'formula'=>$formula,'fields'=>$fields,'isMatematic'=>$definition["isMatematic"]);
                  $DM_CMS->saveDataToCache($cachedName,$currFormula);
              }
              array_push($formulas,$currFormula);
          }
          return $formulas;
      }
      
       private function checkPosition($pattern, $tag)
      {
          $pos =  strrpos($pattern,$tag);
           if($pos ===false)
              {
                return false;
              }
          return true;
            
      }
      
      
      private function ReplaceTagAll($tag,$replacement,$pattern)
      {
          while($this->checkPosition($pattern,$tag))
          {
              $pattern =  str_replace($tag,$replacement,$pattern);
          }
          return $pattern;
      }
     
  }