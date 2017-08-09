<?php
  include_once dirname(dirname(__FILE__)) . '/Logic/genie_DynamicCreation.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_CMSSpecials.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_GeneralUsage.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_DataBase.php';
  class genie_WizardManager
  {
      public function Manage($Request)
      {
          
          $results =array("error"=> "No permission or no form action");
          $DM_DataBase=new genie_DataBase();
          if($DM_DataBase->getSpecialPermission() &&  is_admin() && isset($Request['formaction']))
          {
                 $results=array("error"=> "No known action");
                 switch($Request['formaction'])
                {
                    case "createDefaults":
                        $results = $this->CreateDefaults($Request);
                        break;
                    case "createDisplays":
                         $results=$this->CreateDisplays($Request);
                         break;
                    default:
                      $results= array($this->SomeAction($Request['formaction'],$Request));
                    break;
                   
                        
                }
          }
          return json_encode($results);
      }
      
      private function SomeAction($action,$RequestParam)
      {
         return "Action Araized:".$action; 
      }
      
      private function CreateDefaults($Request)
      {
          $DM_DataBase = new genie_DataBase();
          if(isset($Request['tableName']))
          {
              $tableName= $Request['tableName'];
              $whereParam = $DM_DataBase->createParamsFromRow(array("tableName"=>$tableName),false);
              $existsInSystem = $DM_DataBase->Select("DMSysEntities",$whereParam,false,false);
              if(!$DM_DataBase->CheckIfNotEmpty($existsInSystem))
              {
                 $DM_DynamicCreation=new genie_DynamicCreation() ;
                 $DM_DynamicCreation->CreateFieldsListFromTable($tableName);
                 
              }
              $results=$DM_DataBase->getDefinitions($tableName);
          }
          return $results;
          
      }
      
      private function CreateDisplays($Request)
      {
          
      }
      
      
  }
