<?php
include_once dirname(dirname(__FILE__)) . '/Logic/genie_CMSSpecials.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_DataBase.php';

  class genie_Table
  {
      private $DM_CMSSpecials;
      private $DM_DataBase;
      
       public function __construct()
      {
           $this->DM_CMSSpecials = new genie_CMSSpecials();
           $this->DM_DataBase = new genie_DataBase();
      }
      
      public function UpdateTable($tableDetails)
      {
  
      }
      
      public function UpdateField( $fieldDetails)
      {
      
          
      }
      
       public function AddTable($tableDetails,$fieldNums=true)
      {
           return $this->CreateTableNoDefinitions($tableDetails,$fieldNums);   
      }
      
      public function AddField($fieldDetails,$fieldNums=true)
      {
            return $this->CreateField($fieldDetails,$fieldNums);  
      }
      
       public function DeleteTable($tableDetails)
      {
            return $this->DeleteTableHelper($tableDetails,true);
      }
      
      public function DeleteField($fieldDetails)
      {
             return $this->DeleteFieldHelper($fieldDetails,true);
      }
      
      
      
      
        public function IsTableExists($tableName)
      {
          if($this->CheckIfTableExistsOnDataBase($tableName))
          {
              return true;
          }
          return $this->CheckIfTableOnEntities($tableName);
      }
      
      public function CheckIfFieldExists($tableName,$fieldName)
      {
          $DM_CMSSpecials = new genie_CMSSpecials();
          $DM_DataBase = new genie_DataBase();
            $currTableName=$DM_CMSSpecials->GetTableName($tableName);    
             $query ="SHOW COLUMNS FROM ".$currTableName." where Field='".$fieldName."'";
              $results = $DM_CMSSpecials->getResults($query);  
              if($DM_DataBase->CheckIfNotEmpty($results))
              {
                  return true;
              }
              return false;
      }
      
       public function CheckIfTableExistsOnDataBase($tableName)
     {
         
         $DM_CMSSpecials = new genie_CMSSpecials();
         $newTableName=$DM_CMSSpecials->GetTableName($tableName);
         $query="show tables like '".$newTableName."'";
         $tables =$DM_CMSSpecials->getResults($query);
         if($tables!=null && count($tables)>0 )
            return true;
         else
            return false;
     }
     
      private function CheckIfTableOnEntities($tableName)
     {
        $results = $this->DM_DataBase->Select("DMSysEntities",array(array("fieldName"=>"tableName","value"=>$tableName)));
        if(count($results)>0 && isset($results['id']) && is_numeric($results['id']))
        {
            return true;
        }
        return false;
     }
      
     public function CreateTable($tableParams,$definitionsParams,$fieldNums=true)
     {  
        $res  = $this->CreateTable($tableParams) ;
        if($res=="success")
        {
            
            $tableName = $this->getTableName($tableParams);   
            $res = $this->CreateDefinitionsToTable($tableName,$definitionsParams,$fieldNums);
        }
        return $res;
       
     } 
      
      private function CreateTableNoDefinitions($tableParamsRow,$fieldNums=true)
     {
		/*TODO:Split definitions*/
		/*Create definitions table*/
        $tableDefinitions=$this->DM_DataBase->createParamsFromRow($tableParamsRow,$fieldNums); 
         
         $tableName = $this->getTableName($tableDefinitions);
         if(preg_match("/^[a-zA-Z]+[a-zA-Z0-9_]*$/", $tableName) != 1) {
            return "error : Data Source System Name must be english letters only";  
         }
         if(!$this->CheckIfTableExistsOnDataBase($tableName))
         {
            $currTableName = $this->DM_CMSSpecials->GetTableName($tableName);
            $createQuery = "CREATE TABLE IF NOT EXISTS ".$currTableName."
                                        (
                                        id bigint NOT NULL AUTO_INCREMENT,
										uniqueId varchar(255)
                                        ,PRIMARY KEY (id)
                                        ) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; ";
                        $result = $this->DM_CMSSpecials->execute($createQuery);
                        if($result)
                        {
                           
                            return "success";
                        }
                        else
                        {
                            return "error: ".$this->DM_CMSSpecials->getLastError()." ".$createQuery;
                        }
           
         }   
         return "error : table already exists";      
     }
     
      public function CreateFirstDefinitions($entityId)
      {
          $DMDataBase = new genie_DataBase();
            $whereParams = $DMDataBase->createParamsFromRow(array("fieldName"=>"id"));
		/*TODO:Split definitions*/
            $Definitions = $DMDataBase->Select ("DMSysDefinitions",$whereParams,false,false);
            if($DMDataBase->CheckIfNotEmpty($Definitions) && count($Definitions)>1)
            {
                
                $Definition = $Definitions[1];
                $Definition["entity_id"]=$entityId;
                $Definition["isSystem"]=false;
                $errorMessage="";
                $insertDefinitions = $DMDataBase->createDefinitionsFromRow("DMSysDefinitions",$Definition,false);
                $resArray=$DMDataBase->Insert("DMSysDefinitions",$insertDefinitions,true,false);
                $res = $resArray['res'];
                $errorMessage= $resArray['error'];
                if($res<0 && trim($errorMessage)!="")
                {
                    return "error: ".$errorMessage;
                }
                   
            }
		
		$whereParams = $DMDataBase->createParamsFromRow(array("fieldName"=>"uniqueId"));
		/*TODO:Split definitions*/
		$Definitions = $DMDataBase->Select ("DMSysDefinitions",$whereParams,false,false);
		if($DMDataBase->CheckIfNotEmpty($Definitions) && count($Definitions)>1)
		{
			
			$Definition = $Definitions[1];
			$Definition["entity_id"]=$entityId;
			$Definition["isSystem"]=false;
			$errorMessage="";
			$insertDefinitions = $DMDataBase->createDefinitionsFromRow("DMSysDefinitions",$Definition,false);
			$resArray=$DMDataBase->Insert("DMSysDefinitions",$insertDefinitions,true,false);
			$res = $resArray['res'];
			$errorMessage= $resArray['error'];
			if($res<0 && trim($errorMessage)!="")
			{
				return "error: ".$errorMessage;
			}
			return "success";    
		}
          return "error: No id field to insert";    
      }
   
     
    
     
     
     
       private function CreateDefinitionsToTable($tableName,$definitionsParams,$fieldNums=true)
       {
            foreach($definitionsParams as $definitionParams)
            {
			/*TODO:Split definitions*/
                $fieldDefinitions = $this->DM_DataBase-> createParamsFromRow("DMSysDefinitions",$definitionParams,$fieldNums);
                $this->CreateField($tableName,$fieldDefinitions,$fieldNums);       
            } 
            return "success";          
       }
     
      
      
      private function getTableName($tableParams)
      {
          foreach($tableParams as $tableParam)
          {
              if($tableParam['fieldName']=="tableName")
              {
                  return $tableParam['value'];
              }
          }
      }
      
      private function DeleteTableHelper($tableDetails,$fieldNums=true)
      {
           $tableDefinitions = $this->DM_DataBase->createParamsFromRow($tableDetails,$fieldNums);
           $tableName = $this->GetParameter($tableDefinitions,"tableName"); 
           if($this->CheckIfTableExistsOnDataBase($tableName))
         {
              $deleteTableName=$this->DM_CMSSpecials->GetTableName($tableName);
              $query="drop table ".$deleteTableName;
              $result = $this->DM_CMSSpecials->execute($query);
              if($result)
                return "success";
         } 
         return "error";
      }
      
      
       private function IsFieldExists($tableName,$fieldName)
      {
          if($this->CheckIfFieldExistsOnDataBase($tableName,$fieldName))
          {
              return true;
          }
          return $this->CheckIfFieldOnDefinitions($tableName,$fieldName);
      }
      
       private function CheckIfFieldExistsOnDataBase($tableName,$fieldName)
     {
        $DM_CMSSpecials = new genie_CMSSpecials();
         $newTableName=$DM_CMSSpecials->GetTableName($tableName);
         $query="show columns in ".$newTableName." like '".$fieldName."'";
         $tables =$DM_CMSSpecials->getResults($query);
         if($tables!=null && count($tables)>0 )
            return true;
         else
            return false;
     }
     
      private function CheckIfFieldOnDefinitions($fieldName,$tableName)
     {
         
         $whereParams =  $this->DM_DataBase->createParamsFromRow(array("tableName"=>$tableName));
         $tables = $this->DM_DataBase->Select("DMSysEntities",$whereParams);
         if($this->DM_DataBase->CheckIfNotEmpty($tables))
         {
             $entityId = $tables[0]['id'];
              $whereParams =  $this->DM_DataBase->createParamsFromRow(array("fieldName"=>$fieldName,'entity_id'=>$entityId));
			/*TODO:Split definitions*/
                $results = $this->DM_DataBase->Select("DMSysDefinitions",$whereParams);
                if($this->DM_DataBase->CheckIfNotEmpty($results))
                {
                    return true;
                }        
         }      
        return false;
     }
     
      
      private function CreateField( $fieldDefinitionsRow,$fieldNums=true)
      {
       
       
          $fieldDefinitions = $this->DM_DataBase->createParamsFromRow($fieldDefinitionsRow,$fieldNums);
           $entityId = $this->GetParameter($fieldDefinitions,"entity_id"); 
           $tableName= $this->DM_DataBase->GetTableNameByEntity($entityId);
          
          $currTableName = $this->DM_CMSSpecials->GetTableName($tableName);
          $fieldName = $this->GetParameter($fieldDefinitions,"fieldName");
           if(preg_match("/^[a-zA-Z]+[a-zA-Z0-9_]*+$/", $fieldName) != 1) {
            return "error : Feature system name Name must be english letters only";  
         }
          $DM_Definition = new genie_Definition();
          if(!$this->IsFieldExists($tableName,$fieldName))
          {
             
             
              //load fieldtype by type
              $fieldType = $this->GetParameter($fieldDefinitions,"type");
                $dbType = $DM_Definition->GetFieldDBType($fieldType);
               $createQuery = "ALTER TABLE ".$currTableName." ADD ".$fieldName." ".$dbType;
                        $result = $this->DM_CMSSpecials->execute($createQuery);
               if($result)
               {
                  
                    return "success";
               }
          }
          return "error";
      }
      
      public function CreateFieldByColumn($tableName,$column)
      {
           $currTableName = $this->DM_CMSSpecials->GetTableName($tableName);
          $fieldName = $column["Field"];
          if(!$this->CheckIfFieldExistsOnDataBase($tableName,$fieldName))
          {
              $fieldType = $column["Type"];
               $createQuery = "ALTER TABLE ".$currTableName." ADD ".$fieldName." ".$fieldType;
                        $result = $this->DM_CMSSpecials->execute($createQuery);
               if($result)
               {
                    return "success";
               }
          }
          return "error";
      }
      
      
      private function GetParameter($fieldDefinitions,$parameter)
      {
           foreach($fieldDefinitions as $fieldParam)
          {
              if($fieldParam['fieldName']==$parameter)
              {
                  return $fieldParam['value'];
              }
          }
      }
      
      private function DeleteFieldHelper($fieldDefinitionsRow,$fieldNums=true )
      {
           $fieldDefinitions = $this->DM_DataBase->createParamsFromRow($fieldDefinitionsRow,$fieldNums);
           $entityId = $this->GetParameter($fieldDefinitions,"entity_id"); 
           $tableName= $this->DM_DataBase->GetTableNameByEntity($entityId);
           $fieldName = $this->GetParameter($fieldDefinitions,"fieldName"); 
          if($this->IsFieldExists($tableName,$fieldName))
          {
              $deleteTableName = $this->DM_CMSSpecials->GetTableName($tableName);
              $query="ALTER TABLE ".$deleteTableName." DROP ".$fieldName;
              $res = $this->DM_CMSSpecials->execute($query);
                if($res)
                {
                    return "success";
                }
          }
          return "error";
      }
      
      
  }