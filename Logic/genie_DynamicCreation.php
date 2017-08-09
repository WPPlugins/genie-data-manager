<?php
include_once dirname(dirname(__FILE__)) . '/Logic/genie_GeneralUsage.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_CMSSpecials.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_DataBase.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_Definition.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_Table.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_Form.php';
//genie_Table

  class genie_DynamicCreation
  {
   
      public function CreateTablesFromFile($filename)
      {
          $DM_GeneralUsage = new genie_GeneralUsage();
          $DM_DataBase = new genie_DataBase();
          $dir =  dirname(dirname(__FILE__)) ;     
          $tablesToLoad = $DM_GeneralUsage->getData($dir."/files/".$filename);
          foreach($tablesToLoad as $tableToLoad)
          {
              $DM_CMSSpecials=new genie_CMSSpecials();
              $tableName = $DM_CMSSpecials->GetTableName($tablesToLoad['tableName']);
              $DM_Table = new genie_Table();
              if(! $DM_Table->CheckIfTableExistsOnDataBase($tableName))
              {
                  $tableParams = $DM_DataBase->createParamsFromRow($tableToLoad);
                  $fields = $DM_GeneralUsage->getData($dir."/files/".$tablesToLoad['filenamefields']); 
                  $DM_Table->CreateTable($tableParams,$fields);
                   if($tableToLoad['defaultValuesFile']!="")
                  {
                     $defaultValuesArray = $DM_GeneralUsage->getData($dir."/files/".$tablesToLoad['defaultValuesFile']); 
                       foreach($defaultValuesArray as $defaultValues)
                       {
                           $insertParams = $DM_DataBase->createParamsFromRow($defaultValues);
                           $DM_DataBase->Insert($tablesToLoad['tableName'],$insertParams);
                       }
                  }
              }
          }
      }
      
      public function SaveCurrentSysTables($folderName,$filename)
      {
          //TODO: go over all tables and save their data to folder
          $DM_GeneralUsage = new genie_GeneralUsage();
          $DM_DataBase = new genie_DataBase();
          $dir =  dirname(dirname(__FILE__)) ;     
          $fileNamePath=$dir."/".$folderName."/".$filename;
          $whereParams = $DM_DataBase->createParamsFromRow(array("isSystem"=>true),false);
          $Entities = $DM_DataBase->Select("DMSysEntities",$whereParams,false);
          
          
      }
     
      //TODO: for uploading files - creates all nessesary to start working with
      public function CreateTableFromFile($fileName)
      {
          //load file to array
          //create table 
          //create field list
          //save on database
          //create forms 
          //save from database
      }
            
      public function CreateFieldsListFromTable($tableName,$savedefaults=true)
      {
           $DM_GeneralUsage = new genie_GeneralUsage();
           $DM_DataBase = new genie_DataBase();
           $dir =  dirname(dirname(__FILE__)) ;   
           $definitionsArray = $this->CreateDefinitionsByTableStructure("DMSysEntities");
           if($savedefaults)
           {
              $fileName=  $dir.'/files/defaultsTable.txt';
               if(file_exists($fileName))
               {
                  $tableDefaults = $DM_GeneralUsage->getData($fileName);
                  $tableDefaults[0]['tableName'] = $tableName;
                  $tableDefaults[0]['name'] = $tableName;    
                  $areDefinitions=true;
                  $this->fillDefinitionsWithParams($tableDefaults[0],$definitionsArray);
                  $params =$definitionsArray;       
                  $newEntityId=$DM_DataBase->Insert("DMSysEntities",$params,$areDefinitions);  
                  $thisTableDefinitions =  $this->CreateDefinitionsByTableStructure($tableName);
                  if(is_numeric($newEntityId) && $newEntityId>0)
                  {
                      $areDefinitions=true;    
					/*TODO:Split definitions*/
                      $definitionsInsertArray = $this->CreateDefinitionsByTableStructure("DMSysDefinitions",$newEntityId);   
                      foreach($thisTableDefinitions as &$definition)
                      {                         
                          $newInsertParameters = $DM_GeneralUsage->copyArray($definitionsInsertArray);                        
                          $this->fillDefinitionsWithParams($definition,$newInsertParameters);
                          $DM_DataBase->Insert("DMSysDefinitions",$newInsertParameters,$areDefinitions);
                      }
                  }
               }
               else
               {
                   echo 'file not exists';
               }
          }
          return $definitionsArray;
      }
      
     
      
      public function fillDefinitionsWithParams(&$params,&$definitions)
      {
          foreach($params as $key=>$value)
          {
              foreach($definitions as &$definition)
              {
                  if($definition['fieldName']==$key)
                  {   
                      if($value!="")
                      {
                        $definition['value']=$value;
                      }
                  }
              }
          }
      }

      
      public function CreateDefinitionsByTableStructure($tableName,$entityid=0)
      {
          $errors="";
         $DM_GeneralUsage = new genie_GeneralUsage();
          $dir =  dirname(dirname(__FILE__)) ;  
          $definitionDefaults = $DM_GeneralUsage->getData($dir.'/files/defaultsByFieldTypes.txt');
          $DM_CMSSpecials=new genie_CMSSpecials();
          $definitionsArray = array();
          $currtableName = $DM_CMSSpecials->GetTableName($tableName);
          $query = " show columns from ".$currtableName;
          $columns=  $DM_CMSSpecials->getResults($query);
        
          foreach($columns as $column)
          {
              $found=$this->GetDefaultDefinition($DM_GeneralUsage,$definitionsArray,$definitionDefaults,$column,$column['Type'],$entityid);
              if(!$found)
              {
                 $found=$this->GetDefaultDefinition($DM_GeneralUsage,$definitionsArray,$definitionDefaults,$column,"text",$entityid);
                 if(!$found)
                    $errors.= "error could not find definitions defaults for field ".$column['Field']."<br/>";  
              }
          }         
          return $definitionsArray;
      }
       
       public function GetDefaultDefinition($DM_GeneralUsage,&$definitionsArray,$definitionDefaults,$column,$type,$entityid=0)
       {
           $found=false;
           foreach($definitionDefaults as &$defDefault)
          {
              if($defDefault['Type']==$type)
              {
                  $newDefinition =$DM_GeneralUsage->copyArray($defDefault);
                  $newDefinition['fieldName'] = $column['Field'];
                  $newDefinition['name'] = $column['Field'];
                  $newDefinition['value'] = '';
                  
                  if($column['Field']=="entity_id" && $entityid!=0)
                  {
                       $newDefinition['value'] = $entityid; 
                  }
                  array_push($definitionsArray,$newDefinition);
                 
                  $found=true;
                  break;  
              }
          }         
          return $found;
       }

      public function CreateDataFromTable($tableName)
      {      
          $DM_DataBase = new genie_DataBase();
          return $DM_DataBase->Select($tableName,array());  
      }
      
      
      
      //TODO: rewrite it, using form function
      public function CreateFormByTable($tableName,$formType,$saveDefault=false)
      {
          $isOnPage="";
          if($formType=="group")
          {
              $isOnPage=" data-bind='visible:isOnPage()'" ;
          }
          $htmlPattern = "<div id='form_entity_[@entityId@][@formType@]'>     
          <div class='DmTable' style='display: none;'    data-bind='foreach: [@modelName@], visible:true '>
          <div ".$isOnPage.">[@internalHtml@][@formButton@]</div>
          </div>
          </div>";
          $rowPattern ="<div class='DmRow' ><div class='DmCell' style='width:200px'><b>[@defLabel@]</b></div><div class='DmCell'>[@defInput@]</div></div>";
          
          
          $html=""; 
          $formHtml="";  
           $DM_DataBase = new genie_DataBase();
           $entityId=$DM_DataBase->getEntityId($tableName);
           
            $strroot ='$root';     
            $strparent ='$parent';     
           if($entityId!=0)
           {
               $thisTableDefinitions = $DM_DataBase->getDefinitions($tableName)  ;
               $DM_Definition = new genie_Definition();
               foreach($thisTableDefinitions as $definition)
               {
                   $value=$DM_Definition->GetFieldNameValueForFormHtml($definition,true);
                   $label = $DM_Definition->GetFieldNameLabelForFormHtml($definition,true);
                   $currRow = $rowPattern;
                   $currRow = str_replace("[@defInput@]",$value,$currRow);
                   $currRow = str_replace("[@defLabel@]",$label,$currRow);
                   $currRow = str_replace("[@fieldOptions@]","field_".$definition["id"]."_model",$currRow);   
                     $currRow = str_replace("[@root@]",$strroot,$currRow);
                  $currRow = str_replace("[@parent@]",$strparent,$currRow);
                  $formRowHtml  =  $rowPattern;
                   $formRowHtml = str_replace("[@defInput@]","[@field_id_".$definition["id"]."@]",$formRowHtml);
                   $formRowHtml = str_replace("[@defLabel@]","[@label_id_".$definition["id"]."@]",$formRowHtml);
                  
                   $html.=$currRow;
                  $formHtml.=$formRowHtml; 
               }
              $DM_GeneralUsage = new genie_GeneralUsage();
              $type = $DM_GeneralUsage->GetTypeMapping($formType);
              
              if(count($type)>0)
              {
                  $Pages="";
                  $button = '<a href="#" data-bind="click: function() { '.$type["function"].'(\'entity_id_'.$entityId.'\');} ">'.$type['Text'].'</a> ';
                       if($type['type']=="group")
                       {
                            $button = '<a href="#" data-bind="click: function(data) { '.$type["function"].'(\'entity_id_'.$entityId.'\',data);} ">'.$type['Text'].'</a> ';    
                            $Pages=' <span style="display: none;" data-bind="foreach: entity_id_'.$entityId.'mypages(), visible:true ">
                  <label data-bind="visible:num()=='.$strroot.'.entity_id_'.$entityId.'pagenum()"><b><<</label>
                <a href="#" data-bind="click: function() {'.$strroot.'.changePage(\'entity_id_'.$entityId.'\',num()) ;}">Page <span data-bind="text:num"> </span> </a> 
                  <label data-bind="visible:num()=='.$strroot.'.entity_id_'.$entityId.'pagenum()">>></b></label> |
              </span>';
                       }
                     $newHtml = $html;  
                     $newHtml = str_replace("[@formType@]",$type['formSufix'],$newHtml);
                     $returnHtml = str_replace("[@formType@]",$type['formSufix'],$htmlPattern);
                     $formName = 'form_entity_[@entityId@][@formType@]';
                     $formName = str_replace("[@entityId@]",$entityId,$formName);  
                     $formName = str_replace("[@formType@]",$type['formSufix'],$formName); 
                     $modelName = "entity_id_{$entityId}".$type['formSufix'];
                     $returnHtml = str_replace("[@modelName@]",$modelName,$returnHtml);   
                     $returnHtml = str_replace("[@internalHtml@]",$newHtml,$returnHtml);           
                     $returnHtml = str_replace("[@entityId@]",$entityId,$returnHtml);  
                     
                     
                     $returnHtml = str_replace("[@formButton@]",$button,$returnHtml);   
                     $pos = strrpos($returnHtml,"[@root@]");
                     while($pos)
                     { 
                        $returnHtml = str_replace("[@root@]",$strroot,$returnHtml);
                         $pos = strrpos($returnHtml,"[@root@]");
                     }
                     
                     $finalFormHtml = $returnHtml.$Pages;
                     
                     if($saveDefault)
                     {
                         $insertParameters = $DM_DataBase->createParamsFromRow(array('formHtml'=>$formHtml,'entity_id' => $entityId,'type'=>$formType, 
                        'formName'=>'entity_id_'.$entityId),false);
                         $DM_DataBase->Insert("DMSysForms",$insertParameters,false);
                     }
                     if($formType=="filterRow" || $formType=="new"|| $formType=="search")
                     {
                         $AddedForm =  $this->CreateFormByTable($tableName,"group");
                          $finalFormHtml.="<h2>Group</h2>".$AddedForm;
                     }
                     
                     return    $finalFormHtml;
              } 
           }
           else
           {
               return 'No entity data yet';
           }                                
      }
      
     
      
      public  function CreateModelByTable($tableName,$formType)
      {
           $validators = array();   
            $models = array();    
            $DM_DataBase = new genie_DataBase();
            $entityId=$DM_DataBase->getEntityId($tableName); 
            if($entityId>0)
            {    
                  $definitions = $DM_DataBase->getDefinitions($tableName);
                  $DM_Form = new genie_Form();
                  foreach ($definitions as $definition)
                  {
                       $resArray=$DM_Form->DefineModelsByDefinition($definition,$DM_DataBase,$models);
                     $definition=$resArray['definition'];
                     $models=$resArray['models'];
                         
                  }
                   $DM_Definition = new genie_Definition() ;
                  $results = array(); 
                   foreach ($definitions as $definition)
                   {
                       $DM_Definition->AddValidators($definition,$validators);
                        foreach ($definitions as $definition)
                       {
                           $results['field_'.$definition['id']] = $definition['defaultValue'];
                       }
                   }
                           
                   if($formType=="group"||$formType=="filterRow"||$formType=="new")
                  {
                      $ModelType="group";     
                        $results= $DM_DataBase->Select($tableName,array(),true);    
                        $groupModel = array('name'=>'entity_id_'.$entityId,'type'=>$ModelType,'data'=>$results,'validators'=>$validators, 'pagesize'=>1, pagenum=>1);
                      array_push($models,$groupModel);          
                  }
                  else
                  {
                        $groupModel = array('name'=>'entity_id_'.$entityId,'type'=>$formType,'data'=>array($results),'validators'=>$validators, 'pagesize'=>1, pagenum=>1);
                      array_push($models,$groupModel); 
                      //need empty array to load later
                      if($formType=="search")
                      {
                          $groupModel = array('name'=>'entity_id_'.$entityId,'type'=>"group",'data'=>array($results),'validators'=>$validators, 'pagesize'=>1, pagenum=>1);
                          array_push($models,$groupModel); 
                      } 
                  }         
                                       
            }
            return $models;
      }
      
     
      public function CreateFormFromFieldList($definitions,$RowTemplate="", $MainTemplate="",$savedefaults=true,$entityId)
      {
          $finalHtml ="";
          $DM_Definition = new genie_Definition();
          if($RowTemplate=="")
            $RowTemplate="<div class='DMRow'><div class='DMCell'>[@label@]</div><div class='DMCell'>[@value@]</div></div>";
           if($MainTemplate=="")
            $MainTemplate="<div class='DmTable'>[@value@]<div>";
           //TODO: Change this according to main template
           $html=" <ul style=\"display: none;\" data-bind=\"foreach: entity_{$entityId}[@formType@], visible:true \">
                <li >";
           foreach($definitions as $definition)
           {
               $value=$DM_Definition->GetFieldNameValueForFormHtml($definition,true);
               $label = $DM_Definition->GetFieldNameLabelForFormHtml($definition,true);
               $currRow = $RowTemplate;
               $currRow = str_ireplace("[@value@]",$value,$currRow);
               $currRow = str_ireplace("[@label@]",$label,$currRow);
               $html.=$currRow;
           }
           
           $html.="[@formButton@]";
             $html.="</li>
              </ul>";
           
           if($savedefaults && $entityId>0)
           {
               $DM_DataBase = new genie_DataBase();
               $whereParams = array('id'=>$entityId);
               $tableData = $DM_DataBase->Select("DMSysEntities",$whereParams);
               if($tableData=!null && count($tableData)>0)
               {
                   $DM_GeneralUsage = new genie_GeneralUsage();
                  $dir =  dirname(dirname(__FILE__)) ;  
                  $types = $DM_GeneralUsage->getData($dir.'/files/formTypesMapping.txt');
                   foreach($types as $type)
                   {
                       $button = '<a href="#" data-bind="click: function() { '.$type["function"].'("entity_id_{$entityId}");} ">'.$type['Text'].'</a> ';
                       if($type['type']=="group")
                       {
                            $button = '<a href="#" data-bind="click: function(data) { '.$type["function"].'("entity_id_{$entityId}",data);} ">'.$type['Text'].'</a> ';    
                       }
                       $newHtml = str_replace("[@formButton@]",$button,$html);  
                       $newHtml = str_replace("[@formType@]",$type['formSufix'],$html);
                      
                        $params = $DM_DataBase->createParamsFromRow(array('formHtml'=>$newHtml,'entity_id' => $entityId,'type'=>$type['type'], 
                        'formName'=>'entity_'.$entityId));
                        $newFormId = $DM_DataBase->Insert("DmSysForms",$params);
                        if(is_numeric($newFormId) && $newFormId>0)
                        {
                             $finalHtml.="<h2>".$type['Text']." saved under number ".$newFormId."<h2>".$newHtml."<hr>";   
                        }
                        else
                        {
                            $finalHtml.="<h2>".$type['Text']." not saved! ".$newFormId."<h2>".$newHtml."<hr>";   
                        }
                   }
               }
           }
           else
           {
               $finalHtml = $html;
           }
           return $finalHtml;
      }
      
  }