<?php
include_once dirname(dirname(__FILE__)) . '/Logic/genie_CMSSpecials.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_Definition.php';
class genie_DataBase
{
private $DM_CMSSpecials;
private $DM_Definition;
private $developing = GENIE_DEVELOPMENT_MODE;

public function __construct()
{
	$this->DM_CMSSpecials = new genie_CMSSpecials();
	$this->DM_Definition = new genie_Definition();
}

public function getPermission($tableName,$type)
{
	$DM_CMSSpecials=new genie_CMSSpecials();
	$role = strtolower( $DM_CMSSpecials->get_current_user_role());
	if($role=="administrator"  )
	{
		$role="admin";
	}
	
	if(!($role=="admin" || $role=="editor" || $role=="author" || $role=="contributor" ))
	{
		$role="guest";
	}
	$columnName=$role."Can".$type;
	if($this->CheckIfColumnExists("DMSysEntities",$columnName))
	{
		$whereParam = $this->createParamsFromRow( array("tableName"=>$tableName),false);
		$tableDetails = $this->Select("DMSysEntities",$whereParam,false,false);
		if($this->CheckIfNotEmpty($tableDetails))
		{
			if(isset($tableDetails[0]) && isset($tableDetails[0][$columnName]) && $tableDetails[0][$columnName]==1)
			{
				return true;
			} 
		}
	}
	return false;
}   

public function getSpecialPermission()
{
	$DM_CMSSpecials = new genie_CMSSpecials();
	$role = strtolower( $DM_CMSSpecials->get_current_user_role());
	if($role=="administrator"  )
	{
		return true;
	}
	
	
	return false;
}   

public function CheckIfNotEmpty($queryResult)
{
	if($queryResult!=null && count($queryResult)>0)
	{
		return true;
	}
	return false;
}

public function UpdateTable($tableName,$updateParams, $whereParams,$areUpdateDefinitions=false)
{
	$DM_CMSSpecials = new genie_CMSSpecials();
	$currTableName = $this->DM_CMSSpecials->GetTableName($tableName);
	
	if(!$areUpdateDefinitions)
	{
		$updateDefinitions = $this->getDefinitionsFromParams($tableName,$updateParams);
		$whereDefinitions = $this->getDefinitionsFromParams($tableName,$whereParams);
	}
	else
	{
		$updateDefinitions=$updateParams;
		$whereDefinitions = $whereParams;
	}
	$sqlSet = "";
	$sqlWhere="";
	$count=0;
	$countWhere = 0;
	$thisId=  $this->GetId($updateDefinitions);
	$checkUniqueDefinitions = array();
	foreach($updateDefinitions as $definition)
	{
		if($definition['is_id']==1)
		{
			$checkUniqueParams = array();   
			array_push($checkUniqueParams,array("fieldName"=>$definition['fieldName'],"value"=>$definition['value']));
			$hasUniqueValues = $this->Select($tableName,$checkUniqueParams,false);
			if($this->CheckIfNotEmpty($hasUniqueValues) )
			{
				foreach($hasUniqueValues as $unique)
				{
					if( $unique["id"]!=$thisId)
					{  $entityId=$this->getEntityId($tableName); $sysentityId=$this->getEntityId("DMSysEntities"); 
						$errorMessage= $this->DM_CMSSpecials->Translate($definition['name'],$entityId)." ".$this->DM_CMSSpecials->Translate("with value",$sysentityId)." ".$definition['value']." ".$this->DM_CMSSpecials->Translate("already exists at id ",$sysentityId).$unique["id"];
						return array('res'=>-1,'error'=>$errorMessage); 
					}
				}
				
			}
		}
	}
	
	$checkKeys=false;
	$checkUniqueParams = array();
	foreach($updateDefinitions as $definition)
	{
		if($definition['IsPartOfKey']==1)
		{
			$checkKeys=true; 
			array_push($checkUniqueParams,array("fieldName"=>$definition['fieldName'],"value"=>$definition['value']));
			
		}
	}
	if($checkKeys)
	{
		$hasUniqueValues = $this->Select($tableName,$checkUniqueParams,false);
		if($this->CheckIfNotEmpty($hasUniqueValues) )
		{
			foreach($hasUniqueValues as $unique)
			{
				if( $unique["id"]!=$thisId)
				{  
					$entityId=$this->getEntityId($tableName); 
					$sysentityId=$this->getEntityId("DMSysEntities"); 
					$paramsCount=0;
					foreach($checkUniqueParams as $param)
					{
						if($paramsCount>0)
						{
							$paramValues.=",";
							$paramNames.=",";
						}
						$paramValues.=$param["value"];
						$paramNames.=$this->DM_CMSSpecials->Translate($param['fieldName'],$entityId);
						$paramsCount++;
					}
					$errorMessage=$this->DM_CMSSpecials->Translate(" There are already unique rows with features : ",$sysentityId). $paramNames." ".$this->DM_CMSSpecials->Translate(" with values :",$sysentityId)." ".$paramValues;
					return array('res'=>-1,'error'=>$errorMessage); 
				}
			}
			
		}
	}
	
	foreach($updateDefinitions as $definition)
	{
		$formatedValue=$this->DM_Definition->GetFormattedForQueryPartValue($definition);;
		if(trim($definition['fieldName'])!="" && trim($formatedValue)!="[@mapValue@]" )
		{
			if($count>0)
			{
				$sqlSet.=",";
			}
			$sqlSet.=$definition['fieldName']."=".$this->DM_Definition->GetFormattedForQueryPartValue($definition);
			$count++;
		}
	}
	foreach($whereDefinitions as $definition)
	{
		if($countWhere>0)
		{
			$sqlWhere.=" AND ";
		}
		$sqlWhere.=$definition['fieldName']."=".$this->DM_Definition->GetFormattedForWherePart($definition);
		$countWhere++;
	}
	if($countWhere>0)
	{
		$query = " update ".$currTableName." set ".$sqlSet." where ".$sqlWhere;
		
		$res = $this->DM_CMSSpecials->execute($query);
		if($res)
		{ 
			return array('res'=>"success",'error'=>"");
			
		}                                                       
		else
		{
			$errorMessage= "queryProblems:".$this->DM_CMSSpecials->getLastError()." for Query:".$query;
			
			return array('res'=>"error",'error'=>$errorMessage);
		}
	}
}


public function Select($tableName, $whereParams,$fieldNums=true,$areDefinitions=false,$orderParams=array())
{
	$results = array();
	$DMTable = new genie_Table();
	if($DMTable->CheckIfTableExistsOnDataBase($tableName))
	{
		$currTableName = $this->DM_CMSSpecials->GetTableName($tableName);
		
		if($areDefinitions)
		{
			$whereDefinitions = $whereParams;
		}
		else
		{
			$whereDefinitions = $this->getDefinitionsFromParams($tableName,$whereParams);
		}
		$sqlWhere="";
		$countWhere = 0;
		$defUsed = array();
		
		foreach($whereDefinitions as $definition)
		{
			
			if(!isset($defUsed[$definition["id"]]) || $defUsed[$definition["id"]]==false)
			{
				$internalCount =0;      
				
				if($countWhere>0)
				{
					$sqlWhere.=" AND ";
				}
				$sqlWhere.="(".$definition['fieldName']."=".$this->DM_Definition->GetFormattedForWherePart($definition);
				$internalCount++;
				$countWhere++;
				
				
				
				foreach($whereDefinitions as $InternalDefinition)
				{

					if($InternalDefinition["id"]==$definition["id"] && $InternalDefinition["value"]!=$definition["value"])
					{
						
						if($internalCount>0)
						{
							$sqlWhere.=" OR ";
						}
						else
						{
							$sqlWhere.="(";
						}
						
						$sqlWhere.="  ".$InternalDefinition['fieldName']."=".$this->DM_Definition->GetFormattedForWherePart($InternalDefinition);
						$internalCount++;
						
					}
				}  
				if($internalCount>0)
				{
					$sqlWhere.=")";
				}
				$defUsed[$definition["id"]]=true;
				
			}
		}
		
		$columns = "*";
		if($fieldNums)
		{
			$columns=$this->GetColumnsNums($tableName);
		}
		else
		{
			$columns=$this->GetColumnsNames($tableName);
		}
		
		if(trim($columns)!="")
		{
			$query = " select ".$columns." from  ".$currTableName;
			if($countWhere>0)
			{
				$query .="  where ".$sqlWhere;
			}
			// $query.=" order by id desc";
			if($this->CheckIfNotEmpty($orderParams))
			{
				$count=0;
				$query.=" order by ";
				foreach($orderParams as $order)
				{
					if($count>0)
					{
						$query.=",";
					}
					$query.=$order["fieldName"];
					if($order["type"]=="DESC")
					{
						$query.=" DESC"; 
					}
					else
					{
						$query.=" ASC"; 
					}
					$count++;
				}
			}
			if($tableName=="DMLanguages") 
			{
				$checkin=1;
			}
			// echo $query."<br/>";
			$results = $this->DM_CMSSpecials->getResults($query);
			$this->returnHtmlTags($results);
		}
	}
	return $results;
	
}

public function CheckIfColumnExists($tableName,$column)
{
	$tableName= $this->DM_CMSSpecials->GetTableName($tableName);
	$query="show columns from ".$tableName."  like '".$column."'";
	$columns = $this->DM_CMSSpecials->getResults($query);
	if($this->CheckIfNotEmpty($columns))
	{
		return true;
	}
	return false;
}

/*
  public function SelectForDisplay($tableName, $whereParams,$fieldNums=true,$whereDefaultQueryDefinitions=null)
  {
      $results = array();
      $currTableName = $this->genie_CMSSpecials->GetTableName($tableName);
      
      $whereDefinitions = $this->getDefinitionsFromParams($tableName,$whereParams);
      $sqlWhere="";
      $countWhere = 0;
      foreach($whereDefinitions as $definition)
      {

                   if($countWhere>0)
                  {
                      $sqlWhere.=" AND ";
                  }
                  $sqlWhere.=$definition['fieldName']."=".$this->genie_Definition->GetFormattedForWherePart($definition);
                  $countWhere++;
      
      }
      if($whereDefaultQueryDefinitions==null)
      {
         $defaultWhereDefinitions = $this->getDefaultDefinitions($tableName,"defaultQueryValue");
      }
      else
      {
          $defaultWhereDefinitions=$whereDefaultQueryDefinitions;
      }
      foreach($defaultWhereDefinitions as $definition)
      {
           if($countWhere>0)
          {
              $sqlWhere.=" AND ";
          }
          $sqlWhere.=$definition['fieldName']."=".$this->genie_Definition->GetFormattedForWherePart($definition);
          $countWhere++;
      }
      $columns = "*";
      if($fieldNums)
      {
          $columns=$this->GetColumnsNums($tableName);
      }
      else
      {
          $columns=$this->GetColumnsNames($tableName);
      }
    
      $query = " select ".$columns." from  ".$currTableName;
       if($countWhere>0)
      {
         $query .="  where ".$sqlWhere;
      }
     // $query.=" order by id desc";
        // echo $query;   
      $results = $this->genie_CMSSpecials->getResults($query);
      $this->returnHtmlTags($results);
      return $results;
  }
  */
public function returnHtmlTags(&$results)
{
	foreach ($results as &$row)
	{
		foreach($row as $key=>$value)
		{
			$row[$key]=html_entity_decode(stripslashes($row[$key]));
		}
	}
}

public function GetColumnsNums($tableName)
{
	
	$query="";
	$count = 0;
	$columns = $this->getDefinitions($tableName);
	foreach($columns as $column)
	{
		if($count>0) $query.=",";
		if($column["type"]=="boolean")
		{
			$query.="if(cast(".$column['fieldName']." as UNSIGNED)=1,TRUE,FALSE)  as field_".$column['id'];
			
		}
		else
		{
			$query.=$column['fieldName']." as field_".$column['id'];
		}
		$count++;
	}
	return $query;
}

public function GetColumnsNamesOnDB($tableName)
{
	$DMCMS=new genie_CMSSpecials();
	$columnsQ="";
	$columnsCount=0;
	$tableName=$DMCMS->GetTableName($tableName);
	$query = "SHOW COLUMNS FROM ".$tableName;
	$columns = $DMCMS->getResults($query);
	if($this->CheckIfNotEmpty($columns))
	{
		foreach($columns as $column)
		{
			$type= strtolower($column['Type']);
			if($columnsCount>0)
			{
				$columnsQ.=",";
			}
			if($type=="bit(1)")
			{
				$columnsQ.="if(cast(".$column['Field']." as UNSIGNED)=1,TRUE,FALSE)  as ".$column['Field'] ; 
			}
			else
			{
				$columnsQ.=$column['Field'] ;
			}
			$columnsCount++;
		}
	} 
	return $columnsQ;
}

public function GetColumnsNames($tableName)
{
	
	$query="";
	$count = 0;
	$columns = $this->getDefinitions($tableName);
	foreach($columns as $column)
	{
		if($count>0) $query.=",";
		if($column["type"]=="boolean")
		{
			$query.="if(cast(".$column['fieldName']." as UNSIGNED)=1,TRUE,FALSE)  as ".$column['fieldName'];
			
		}
		else
		{
			$query.=$column['fieldName']." as ".$column['fieldName'];
		}
		$count++;
	}
	return $query;
}


public function Insert($tableName,&$insertParams, $areDefinitions=false,$includeId=false)
{
	$errorMessage="";
	//TODO: check unique values by unique definitions.
	$currTableName = $this->DM_CMSSpecials->GetTableName($tableName);
	if(!$areDefinitions)
	{
		$insertDefinitions = $this->getDefinitionsFromParams($tableName,$insertParams);
	}
	else
	{
		$insertDefinitions =    $insertParams;
	}
	$sqlNames = "";
	$sqlValues="";
	$countNames=0;
	
	$DM_Definition = new genie_Definition();
	$checkUniqueDefinitions = array();
	foreach($insertDefinitions as $definition)
	{
		if($definition['is_id']==1)
		{
			$checkUniqueParams = array();   
			array_push($checkUniqueParams,array("fieldName"=>$definition["fieldName"],"value"=>$definition["value"]));
			$hasUniqueValues = $this->Select($tableName,$checkUniqueParams,false,false);
			if($this->CheckIfNotEmpty($hasUniqueValues))
			{   $entityId=$this->getEntityId($tableName); $sysentityId=$this->getEntityId("DMSysEntities"); 
				$unique = $hasUniqueValues[0];
				$errorMessage= $this->DM_CMSSpecials->Translate($definition['name'],$entityId)." ".$this->DM_CMSSpecials->Translate("with value",$sysentityId)." ".$definition['value']." ".$this->DM_CMSSpecials->Translate("already exists with ",$sysentityId).$this->DM_CMSSpecials->Translate($definition["fieldName"],$entityId).$unique[$definition["fieldName"]].$this->DM_CMSSpecials->Translate(" my Value is ",$sysentityId).$definition["value"];
				return array('res'=>-1,'error'=>$errorMessage);
			}
		}
		
	}
	
	$checkKeys=false;
	$checkUniqueParams = array();
	foreach($insertDefinitions as $definition)
	{
		if($definition['IsPartOfKey']==1)
		{
			$checkKeys=true;  
			array_push($checkUniqueParams,$definition);             
		}
	}
	if($checkKeys)
	{
		$hasUniqueValues = $this->Select($tableName,$checkUniqueParams,false,true);
		if($this->CheckIfNotEmpty($hasUniqueValues) )
		{              
			$values="";
			$names=""; 
			$ids="";  
			foreach($checkUniqueParams as $param)
			{
				$values.= $param["value"]." ";
				$names.=$param["fieldName"]." ";
			}
			foreach($hasUniqueValues as $unique)
			{
				$ids.=$unique["id"]." ";
			} $entityId=$this->getEntityId($tableName); $sysentityId=$this->getEntityId("DMSysEntities"); 
			$errorMessage= $names." ".$this->DM_CMSSpecials->Translate("with values",$sysentityId)." ".$values." ".$this->DM_CMSSpecials->Translate("already exist with ids ",$sysentityId).$ids;
			return array('res'=>-1,'error'=>$errorMessage);                 
		}
	}
	
	
	foreach($insertDefinitions as $definition)
	{
		if($definition['fieldName']=="id" && !$includeId)
		continue;
		
		$currvalue=$DM_Definition->GetFormattedForQueryPartValue($definition);
		if(isset($definition['fieldName']) && trim($definition['fieldName'])!="" && isset($definition['type']) && trim($definition['type'])!="" && $currvalue!="[@mapValue@]")
		{
			if($countNames>0)
			{
				$sqlNames.=",";
				$sqlValues.=",";
			}
			
			$sqlNames.=$definition['fieldName'];
				if($definition['fieldName']=="uniqueId" && (trim($currvalue)=="''" || strlen($currvalue)<32) )
			{
				$sqlValues.="uuid()";
			}
			else
			{
				$sqlValues.=$currvalue;	
			}
			$countNames++;
		}
	}
	
	if($countNames>0)
	{
		
		
		$query = " insert into ".$currTableName." (".$sqlNames.") values (".$sqlValues.") ";
		
		$res = $this->DM_CMSSpecials->execute($query);
		
		if($res)
		{ 
			$addedId=$this->getLastValue($tableName,"id");
			
			
			return array('res'=>$addedId,'error'=>"no error");
			
		}
		else
		{
			$errorMessage= "queryProblems:".$this->DM_CMSSpecials->getLastError()." for Query:".$query;
			return -1;
			return array('res'=>-1,'error'=>$errorMessage);                 
			
		}
	}
}



public function Delete($tableName,$whereParams)
{
	$currTableName = $this->DM_CMSSpecials->GetTableName($tableName);
	$whereDefinitions = $this->getDefinitionsFromParams($tableName,$whereParams);
	$sqlWhere="";
	$countWhere = 0;
	foreach($whereParams as $definition)
	{
		if($countWhere>0)
		{
			$sqlWhere.=" AND ";
		}
		$sqlWhere.=$definition['fieldName']."=".$this->DM_Definition->GetFormattedForWherePart($definition);
		$countWhere++;
	}
	if($countWhere>0)
	{
		$query = " delete from  ".$currTableName."  where ".$sqlWhere;
		$res = $this->DM_CMSSpecials->execute($query);
		if($res>0)
		{ 
			return "success";
		}
		else
		{
			return "error";
		}
	}
}

private function VarIsSet($var)
{
	if($var!=null && isset($var) && trim($var)!="")
	{
		return true;
	}
	return false;
}




public function getDefaultFilters($formName,$paramName)
{
	$results=array();
	$tableName = $this->GetTableName($formName);
	$FilterDefinitions = $this->getDefaultDefinitions($tableName,$paramName);
	foreach($FilterDefinitions as &$definition)
	{
		
		$results["field_".$definition["id"]] = $definition["value"];
		
	}
	return $results;
}

public function getLastValue($tableName,$fieldName)
{
	if(trim($fieldName)!="" && $this->CheckIfColumnExists($tableName,$fieldName))
	{
		$DM_CMSSpecials = new genie_CMSSpecials();
		$currtableName = $DM_CMSSpecials ->GetTableName($tableName);
		$query = " select ".$fieldName." from ".$currtableName." WHERE id=(SELECT max(id) FROM ".$currtableName.")";
		$results = $DM_CMSSpecials->getResults($query);
		if($this->CheckIfNotEmpty($results))
		{
			return $results[0][$fieldName];
		}
	}
	return null;
}

public function getDefaultDefinitions($tableName,$paramName)
{
	$result = array();
	$definitions = $this->getDefinitions($tableName); 
	foreach($definitions as $definition)
	{
		if($this->VarIsSet($definition[$paramName]) && trim($definition["fieldName"]!="") && trim($definition[$paramName])!="")
		{
			if($definition[$paramName]=="[@lastValue@]")
			{
				$definition[$paramName]=$this->getLastValue($tableName,$definition["fieldName"]);
			}
			
			$definition["value"]=$definition[$paramName];
			
			if(trim($definition["value"])!="")
			{
				array_push($result,$definition); 
			}
		}
	}
	return $result;
}

public function getDefinitionsFromParams($tableName,$params)
{
	$results = array();
	$definitions = $this->getDefinitions($tableName);
	if(count($params)>0)
	{
		foreach ($params as $param)
		{
			foreach($definitions as $definition)
			{
				if(isset($definition['fieldName']) && isset($param['fieldName']) && $definition['fieldName'] == $param['fieldName'])
				{
					foreach($param as $key=>$value)
					{
						$definition[$key]=$param[$key];
					}
					array_push($results,$definition);
					break;
				}
			}
		}
	}
	return $results;
}


public function getEntityId($tableName)
{
	$definitions = array();
	$DMTable = new genie_Table();
	if($DMTable->CheckIfTableExistsOnDataBase("DMSysEntities") && $this->CheckIfColumnExists("DMSysEntities","tableName"))
	{
		$sysEntities = $this->DM_CMSSpecials->GetTableName("DMSysEntities");
		$query = "select id from ".$sysEntities." where tableName='".$tableName."'";
		
		$resultid = $this->DM_CMSSpecials->getResults($query);
		if(isset($resultid[0]['id']) && is_numeric($resultid[0]['id']))
		{
			return $resultid[0]['id'];
		}
	}
	return 0;
}

public function getDefinitions($tableName)
{   
	$definitions=array(); 
	$DMTable = new genie_Table();
	$definitionsTableName="DMSysDefinitions";
	if($DMTable->CheckIfTableExistsOnDataBase($tableName))
	{
		$entityId= $this->getEntityId($tableName);
		if($entityId!=0)
		{
			if($DMTable->CheckIfTableExistsOnDataBase($definitionsTableName) && $this->CheckIfColumnExists($definitionsTableName,"entity_id"))
			{
				
				$sysDefinitions = $this->DM_CMSSpecials->GetTableName($definitionsTableName);
				/*TODO:Split definitions*/
				$names=$this->GetColumnsNamesOnDB($definitionsTableName);
				
				$query = "select ".$names." from ".$sysDefinitions." where entity_id=".$entityId;
				$definitions = $this->DM_CMSSpecials->getResults($query);
			}
		}
	}
	return $definitions; 
}

public function getIdFromRow($dataRow,$definitionsNums=false)
{
	$definitionsmapping= array();
	if($definitionsNums)
	{
		$definitionsmapping=$this->GetDefinitionsArrayMapping();
	}
	foreach($dataRow as $key=>$value)
	{
		if($this->checkIfFieldValue($key))
		{
			$fieldName=$key;
			if($definitionsNums)
			{
				$fieldName = $definitionsmapping[$key];
			}
			if($fieldName=="id")
			return $value;
			
		}
	}
	return 0;
}

public function getFieldFromRow($dataRow,$fieldName,$definitionsNums=false)
{
	$definitionsmapping= array();
	if($definitionsNums)
	{
		$definitionsmapping=$this->GetDefinitionsArrayMapping();
	}
	foreach($dataRow as $key=>$value)
	{
		if($this->checkIfFieldValue($key))
		{
			$fieldName=$key;
			if($definitionsNums)
			{
				$fieldName = $definitionsmapping[$key];
			}
			if($fieldName==$fieldName)
			return $value;
			
		}
	}
	return null;
}     


public function createParamsFromRow($dataRow,$definitionsNums=false)
{
	$results = array();
	$definitionsmapping= array();
	if($definitionsNums)
	{
		$definitionsmapping=$this->GetDefinitionsArrayMapping();
	}
	if($this->CheckIfNotEmpty($dataRow))
	{
		foreach($dataRow as $key=>$value)
		{
			if($this->checkIfFieldValue($key))
			{
				$fieldName=$key;
				if($definitionsNums)
				{
					if(isset($definitionsmapping[$key]))
					{
						$fieldName = $definitionsmapping[$key];
						array_push($results,array('fieldName'=>$fieldName, 'value'=>$value)); 
					}
				}
				else
				{
					array_push($results,array('fieldName'=>$fieldName, 'value'=>$value)); 
				}
			}
		}
	}
	return $results;
}

public function createParamsFromRows($dataRows,$definitionsNums=false)
{
	
	$results = array();
	$definitionsmapping= array();
	if($definitionsNums)
	{
		$definitionsmapping=$this->GetDefinitionsArrayMapping();
	}
	foreach($dataRows as $dataRow)
	{
		foreach($dataRow as $key=>$value)
		{
			
			if($this->checkIfFieldValue($key))
			{
				$fieldName=$key;
				if($definitionsNums && isset($definitionsmapping[$key]))
				{
					$fieldName = $definitionsmapping[$key];
				}
				
				array_push($results,array('fieldName'=>$fieldName, 'value'=>$value)); 
			}
		}
	}
	return $results;
}

public function checkIfChanged($dataRow,$entityId=0)
{
	$errorMessage='';
	$result = false;
	$DM_CMSSpecials = new genie_CMSSpecials();
	$mappings=$this->GetDefinitionsArrayMapping();
	foreach($dataRow as $key=>$value)
	{
		if($this->checkIfFieldValue($key))
		{
			if(isset($dataRow[$key."_OldValue"]) && isset($dataRow[$key]) &&  $dataRow[$key]!=$dataRow[$key."_OldValue"])
			{
				if(isset($mappings[$key."_permanent"]) && $mappings[$key."_permanent"]==1) 
				{
					$errorMessage=$DM_CMSSpecials->Translate( $mappings[$key],$entityId)." ".$DM_CMSSpecials->Translate("can not to be changed",$entityId);
					return false;
				}
				if(isset($mappings[$key."_IgnoreChanges"]) && $mappings[$key."_IgnoreChanges"]==1)    
				{
					continue;
				}
				$result= true;
			}
		}
	}
	$errorMessage="not changed";
	return array('result'=>$result,'errorMessage'=>$errorMessage);
}
public function GetDefinitionsArrayMapping()
{
	$definitionsMapping = array();
	/*TODO:Split definitions*/
	
	$definitions = $this->Select("DMSysDefinitions",array(),false);
	foreach($definitions as $definition)
	{
		$definitionsMapping['field_'.$definition['id']] = $definition['fieldName'];
		$definitionsMapping['field_'.$definition['id']."_permanent"] = $definition['Permanent'];
		$definitionsMapping['field_'.$definition['id']."_IgnoreChanges"] = $definition['IgnoreChanges'];
	}
	return $definitionsMapping;
}

public function checkIfFieldValue($key)
{
	$searches = array('_OldValue','_destroy','_computed','isvisible','isfiltermatch','newRow','isOnPage','_formula','_model','ShowExtend','isValid','isAnyMessageShown','showErrors');
	foreach($searches as $search)
	{
		if($this->checkPosition($key,$search))
		{
			return false;
		}
	}
	return true;
	
}

public function createDefinitionsFromRow($tableName,$dataRow,$filedNums=false)
{
	$params= $this->createParamsFromRow($dataRow,$filedNums);
	return $this->getDefinitionsFromParams($tableName,$params);
}

public function GetTableName($formName)
{
	$tableName="Unknown";
	$tableDetails = $this->GetTableDetails($formName);
	if(count($tableDetails)>0)
	{
		$tableName = $tableDetails['tableName'];
	}
	return $tableName;
}

public function GetTableNameByEntity($entityId)
{
	$tableName="Unknown";
	$tableDetails = $this->GetTableDetailsByEntity($entityId);
	if(count($tableDetails)>0)
	{
		$tableName = $tableDetails['tableName'];
	}
	return $tableName;
}




public function GetTableDetails($formName)
{
	$tableDetails = array();
	
	$nameParams=  explode ("_", $formName);
	if(count($nameParams)>=2)
	{
		
		$tableDetails= $this->GetTableDetailsByEntity($nameParams[2]);
	}
	return $tableDetails;
}

public function GetTableDetailsByEntity($entityId)
{
	
	$tableDetails=array();                   
	$whereParams = $this->createParamsFromRow(array('id'=>$entityId));
	$EntityDetails = $this->Select("DMSysEntities",$whereParams,false);
	if($EntityDetails!=null && count($EntityDetails)>0)
	{
		$tableDetails = $EntityDetails[0];
		
	}
	
	return $tableDetails;
}

public function GetTableDetailsByTableName($tableName)
{
	
	$tableDetails=array();                   
	$whereParams = $this->createParamsFromRow(array('tableName'=>$tableName));
	$EntityDetails = $this->Select("DMSysEntities",$whereParams,false);
	if($EntityDetails!=null && count($EntityDetails)>0)
	{
		$tableDetails = $EntityDetails[0];
		
	}
	
	return $tableDetails;
}

public function GetId($parameters,$fieldNums=false)
{
	$id=0;
	if($fieldNums)
	{
		$fieldNums = $this->GetDefinitionsArrayMapping();
	}
	foreach($parameters as $parameter)
	{
		if(!$fieldNums && $parameter['fieldName']=="id")
		{
			$id     = $parameter['value'];
		}
		if($fieldNums && count($parameter)>0)
		{
			foreach($parameter as $key=>$value)
			{
				if(isset($fieldNums[$key]) &&$fieldNums[$key]=="id")
				{
					$id=$value;
				}
			}
			
		}
	}
	return $id;
}

public function getIdField($entityId)
{
	/*TODO:Split definitions*/
	$whereParames = $this->createDefinitionsFromRow("DMSysDefinitions",array("fieldName"=>"id","entity_id"=>$entityId));
	$definitions = $this->Select("DMSysDefinitions",$whereParames,false);
	if($this->CheckIfNotEmpty($definitions))
	{
		return  $definitions[0]['id'];
	}
	return -1;
}



public function SaveDatabaseSystemState($filePref)
{
	$paramsRow=array("isSystem"=>1);
	$result=$this->SaveDatabaseState($filePref,$paramsRow);
	return $result["result"];
}

public function SaveDatabaseTableState($filePref,$tableName)
{
	$paramsRow=array("tableName"=>$tableName);
	$result=$this->SaveDatabaseState($filePref,$paramsRow);
	return $result["result"];
}

//$paramsRow => (isSys = true... tableName = something...)
public function SaveDatabaseState($filePref,$paramsRow,$areParams=false,$saveTemplate=false)
{
	$lineDelimiter="LINEDELIMITER"; 
	$specialDelimiter = "SPECIALDELIMITER";
	$DM_GeneralUsage = new genie_GeneralUsage();
	
	$updateParams = $this->createParamsFromRow( array("Version"=>$filePref ),false);
	if(!$areParams)
	{
		$whereParam = $this->createParamsFromRow($paramsRow,false);
	}
	else
	{
		$whereParam = $paramsRow; 
	}
	
	
	
	$fileNames=array();
	$this->UpdateTable("DMSysEntities",$updateParams,$whereParam,false);
	
	
	$results = $this->SaveTableState($filePref,"DMSysEntities",$whereParam,$fileNames,true);
	$Entities=$results["tableDetails"];
	$fileNames=$results["fileNames"];
	
	
	if($this->CheckIfNotEmpty($Entities))
	{
		foreach ($Entities as $entity)
		{
			$fileNames=$this->SaveEntityState($filePref,$entity,$fileNames,$saveTemplate);
		}
	}
	/*$connections = $this->GetConnections();
	if($this->CheckIfNotEmpty($connections))
	{
		$fileName=$filePref."_tableConnections";
		$genie_GeneralUsage->saveArrayNew($connections,$fileName,$specialDelimiter,true,$lineDelimiter);
		array_push($fileNames,$fileName); 
	}
	$connections = $this->getUIDMapping();
	if($this->CheckIfNotEmpty($connections))
	{
		$fileName=$filePref."_tableUidMapping";
		$genie_GeneralUsage->saveArrayNew($connections,$fileName,$specialDelimiter,true,$lineDelimiter);
		array_push($fileNames,$fileName); 
	}*/
	return array("result"=>"finished","fileNames"=>$fileNames);  
}


public function getUIDMapping()
{
	//TODO:Save old uniqueId -> id pairs
	$DM_CMSSpecials = new genie_CMSSpecials();
	$EntitiesTable = $DM_CMSSpecials->GetTableName("DMSysEntities");
	$DMSysDefinitions = $DM_CMSSpecials->GetTableName("DMSysDefinitions");
	$FormsTable = $DM_CMSSpecials->GetTableName("DMSysForms");
	$QVTable = $DM_CMSSpecials->GetTableName("DMSysDefaultQueryValues");
	
	
	$query = " select 'DMSysEntities' tableName,id,uniqueId from ".$EntitiesTable.
		" union ".
		" select 'DMSysDefinitions' tableName,id,uniqueId from ".$DMSysDefinitions.
		" union ".
		" select 'DMSysForms' tableName,id,uniqueId from ".$FormsTable.
		" union ".
		" select 'DMSysDefaultQueryValues' as table,id,uniqueId from ".$QVTable;
	$connections = $DM_CMSSpecials->getResults($query);
	return $connections;
	
}
public function SaveEntityState($filePref,$entity,$fileNames,$saveTemplate=false)
{
	$DM_GeneralUsage = new genie_GeneralUsage();
	$entityId=$entity["id"];
	$tableName= $entity["tableName"];
	
	$paramsRow =   array("entity_id"=>$entityId);
	
	$defWhereParams = $this->createParamsFromRow($paramsRow,false);
	$fileName=$filePref."_".$entityId."_systemDefinitions";
	/*TODO:Split definitions*/
	$result=$this->SaveTableState($fileName,"DMSysDefinitions",$defWhereParams,$fileNames);
	$fileNames=$result["fileNames"];
	
	if($saveTemplate)
	{
		$fileName=$filePref."_".$entityId."_systemForms";
		$result=$this->SaveTableState($fileName,"DMSysForms",$defWhereParams,$fileNames,false);
		$fileNames=$result["fileNames"];
		
		$fileName=$filePref."_".$entityId."_systemDefaultQueryValues";
		$result=$this->SaveTableState($fileName,"DMSysDefaultQueryValues",$defWhereParams,$fileNames,false);
		$fileNames=$result["fileNames"];
		
		
	}
	
	$fileName=$filePref."_".$entityId;
	$result=$this->SaveTableState($fileName,$tableName,array(),$fileNames,true);
	$fileNames=$result["fileNames"];
	
	//Save current system state
	return $fileNames; 
	
}

public function SaveTableState($filePref,$tableName,$defWhereParams,$fileNames,$saveColumns=true)
{
	
	$lineDelimiter="LINEDELIMITER"; 
	$specialDelimiter = "SPECIALDELIMITER";
	$tableDetails = $this->Select($tableName,$defWhereParams,false);
	$DM_GeneralUsage = new genie_GeneralUsage();
	if($tableDetails!=null)
	{
		$fileName=  $filePref."_".$tableName;
		//$genie_GeneralUsage->saveArrayNew($tableDetails,$fileName,$specialDelimiter,true,$lineDelimiter);
        $DM_GeneralUsage->saveArrayJson($tableDetails,$fileName,true);
		array_push($fileNames,$fileName);
	}
	if($saveColumns)
	{
		$DM_CMSSpecials = new genie_CMSSpecials();
		$currTableName = $DM_CMSSpecials->GetTableName($tableName); 
		$query ="SHOW COLUMNS FROM ".$currTableName;
		$tableColumns = $DM_CMSSpecials->getResults($query);
		if($this->CheckIfNotEmpty($tableColumns))
		{
			$fileName=$filePref."_".$tableName."_columns";
		//	$genie_GeneralUsage->saveArrayNew($tableColumns,$fileName,$specialDelimiter,true,$lineDelimiter);
            $DM_GeneralUsage->saveArrayJson($tableColumns,$fileName,true);
			array_push($fileNames,$fileName);
		}  
	}
	return array("tableDetails"=> $tableDetails,"fileNames"=>$fileNames);
}


public function LoadSystemState($filePref,$rewriteUnique,$loadTemplate=false)
{
    //<editor-fold desc="Initiate vars">
	$LastTableIds =$this->LastTableIds();
	$rewriteSystem=false;
	$idFixesArray = array();
	$entitiesPath = $filePref."_DMSysEntities";
	$DMTable = new genie_Table();
	$DM_CMSSpecials =new genie_CMSSpecials();
	$DM_GeneralUsage = new genie_GeneralUsage();
	//</editor-fold>

    //<editor-fold desc="Get Entities new data and if exists current entities version -> $oldVersions">
    $entities = $DM_GeneralUsage->getDataJson($entitiesPath,true);
	$oldVersions=array();
	if($DMTable->IsTableExists("DMSysEntities"))
	{
		$allEntities = $this->Select("DMSysEntities",array(),false,true);
		foreach($allEntities as $entity)
		{
			$oldVersions[$entity["id"]]=$entity["Version"];
		}
	}
	//</editor-fold>

    foreach($entities as $entity)
	{
		if(isset($entity["tableName"]) && trim($entity["tableName"])!="" && isset($entity["id"]) && trim($entity["id"])!="")
		{
            //<editor-fold desc="Check if new entity, create table, mark to insert new data">
			//table
			$tableName = $entity["tableName"];
			$entityId=$entity["id"];
			$insertData = true;
			$justCreated =false;
			//test existing entity version
			//if not equal reload 
			if(!$DMTable->IsTableExists($tableName))
			{
				$DMTable->AddTable($entity,false);
				$justCreated=true;
			}
			else
			{
				$insertData = false;   
			}
            //</editor-fold>

            //<editor-fold desc="Template code to check">
			if($loadTemplate)
			{
				$idFixes=array();
				$parameters = $this->createParamsFromRow($entity,false);
				$oldId=$entity["id"];
				$uniqueId=$entity["uniqueId"];
				$newId = $this->Insert("DMSysEntities",$parameters,false,false);
				$tableMax=$LastTableIds["DMSysEntities"];
				if($oldId!=$newId)
				{
					$hasOldSameId=false;
					array_push($idFixes,array("newId"=>$newId,"oldId"=>$oldId,"oldUniqueId"=>$uniqueId,"tableName"=>"DMSysEntities",'lastId'=>$tableMax,'hasOldSameId'=>$hasOldSameId,"fileName"=>$filePref));
					array_push($idFixesArray,$idFixes);
				}
			}
			//</editor-fold>

            //<editor-fold desc="mark rewrite if system data">
            if($entity["isSystem"]=="1" || $entity["isSystem"]==1)
			{
				$rewriteSystem=true;
			}
			//</editor-fold>

            //<editor-fold desc="Load columns and definitions structure">
            $columns =   $DM_GeneralUsage->getDataJson($filePref."_".$entityId."_".$tableName."_columns",true);
            $Definitions = $DM_GeneralUsage->getDataJson($filePref."_".$entityId."_systemDefinitions"."_DMSysDefinitions",true);
            //</editor-fold>

            //<editor-fold desc="Create new columns">
            foreach($columns as $column)
			{
				$DMTable->CreateFieldByColumn($tableName,$column);
				
				//TODO: fix old null values with default value
				//  $this->SetDefault($tableName,$column,$Definitions);
			}
			//</editor-fold>

            //<editor-fold desc="Template code to check">
            if($loadTemplate)
			{
				/*TODO:Split definitions*/
				$idFixesArray=$this->LoadState($Definitions,"DMSysDefinitions",$entity,$idFixesArray,$LastTableIds,$filePref);
				
				//$Forms = $genie_GeneralUsage->getDataNew($filePref."_".$entityId."_systemForms"."_DMSysForms",$specialDelimiter,true,$lineDelimiter);
                $Forms = $DM_GeneralUsage->getDataJson($filePref."_".$entityId."_systemForms"."_DMSysForms",true);
				$idFixesArray=$this->LoadState($Forms,"DMSysForms",$entity,$idFixesArray,$LastTableIds,$filePref);
				//$DefaultQueryValues = $genie_GeneralUsage->getDataNew($filePref."_".$entityId."_systemDefaultQueryValues"."_DMSysDefaultQueryValues",$specialDelimiter,true,$lineDelimiter);
                $DefaultQueryValues = $DM_GeneralUsage->getDataJson($filePref."_".$entityId."_systemDefaultQueryValues"."_DMSysDefaultQueryValues",true);
				$idFixesArray=$this->LoadState($DefaultQueryValues,"DMSysDefaultQueryValues",$entity,$idFixesArray,$LastTableIds,$filePref);
			}
			//</editor-fold>

            //<editor-fold desc="Check if has old version, otherwise current -> $oldVersion">
            $oldVersion=$filePref;
			if(!$justCreated)
			{
				if(isset($oldVersions[$entity["id"]]))
				{
					$oldVersion = $oldVersions[$entity["id"]];
				}
				else
				{
					$oldVersion="DoNotExists";
				}
			}
            //</editor-fold>

            //<editor-fold desc="load data">
            if($justCreated || (!$this->checkPosition($filePref,$oldVersion)))
			{
                //<editor-fold desc="check fast load -> just truncate and insert,or load system state->$idFixesArray">
				if($entity["FastLoad"]==1 || $entity["FastLoad"]=="1" || $entity["FastLoad"]==true)
				{
					$this->FastLoad($filePref."_".$entityId,$tableName,$Definitions);
				}
				else
				{
					if($insertData || $rewriteSystem || $rewriteUnique)  
					{
						//TODO: reload data by unique Definitions
						$idFixes = $this->LoadSystemTable($filePref."_".$entityId,$tableName,$Definitions,$rewriteUnique,$rewriteSystem,$justCreated);
						array_push($idFixesArray,$idFixes);
					}
				}
                //</editor-fold>

                //<editor-fold desc="if not just created - update entity table with current version">
				if(!$justCreated)
				{ 
					$updateParams = $this->createParamsFromRow(array("Version"=>$filePref));
					$whereUpdateParams = $this->createParamsFromRow(array("tableName"=>$tableName));
					$this->UpdateTable("DMSysEntities",$updateParams,$whereUpdateParams,false);
				}
                //</editor-fold >
			}
            //</editor-fold>
		}
	}

    //<editor-fold desc="fix connections">
	$this->FixConnections($idFixesArray,$LastTableIds);
    //</editor-fold>
	return "finished loading";
	
}


public function LoadState($Objects,$tableName,$entity,$idFixesArray,$LastTableIds,$filePref)
{
	foreach($Objects as $object)
	{
		$idFixes=array();
		$parameters = $this->createParamsFromRow($object,false);
		$oldId=$entity["id"];
		$uniqueId=$entity["uniqueId"];
		$newId = $this->Insert($tableName,$parameters,false,false);
		$tableMax=$LastTableIds[$tableName];
		if($oldId!=$newId)
		{
			$hasOldSameId=false;
			array_push($idFixes,array("newId"=>$newId,"oldId"=>$oldId,"oldUniqueId"=>$uniqueId,"tableName"=>$tableName,'lastId'=>$tableMax,'hasOldSameId'=>$hasOldSameId,'fileName'=>$filePref));
			
		}
		array_push($idFixesArray,$idFixes);
	}
	return $idFixesArray;
}


public function FixConnections($idFixesArray,$LastTableIds)
{
	$DM_CMSSpecials = new genie_CMSSpecials();
	if($this->CheckIfNotEmpty($idFixesArray))
	{
		foreach($idFixesArray as $idFixes)
		{
			if($this->CheckIfNotEmpty($idFixes))
			{
				foreach($idFixes as $idFix)
				{

					$erase=false;
					$tableName= $idFix["tableName"];
					$newId=$idFix["newId"];
					$oldId = $idFix["oldId"];
					$fromId=$idFix["lastId"];
					$fromIdOrigin=$idFix["lastId"];
					$hasOldSameId =$idFix["hasOldSameId"];
					$deleteSystem =$idFix["isSystem"];
					$oldUniqueId=$idFix["oldUniqueId"];
					
					
					if(isset($idFix["erase"]))
					{
						$erase=$idFix["erase"];
					}
					$currentResult = $this->Select($tableName,$this->createParamsFromRow(array("uniqueId"=>$oldUniqueId)),false,false,array());
					if($this->CheckIfNotEmpty($currentResult) && $currentResult[0]["id"]==$newId)
					{
						
						$QueriesfromId=1;
						$FormsfromId=1;
						$EntitiesfromId=1;
						$DefinitionsfromId=1;
						$currtableName = $DM_CMSSpecials->GetTableName($tableName);
						if(isset($LastTableIds["DMSysForms"]) && $LastTableIds["DMSysForms"]!=null &&$LastTableIds["DMSysForms"]>1 )
						{
							$FormsfromId=$LastTableIds["DMSysForms"];
							if($idFix["erase"] || !$hasOldSameId)
							{
								$FormsfromId=1;
							}
						}
						
						if(isset($LastTableIds["DMSysDefaultQueryValues"]) && $LastTableIds["DMSysDefaultQueryValues"]!=null &&$LastTableIds["DMSysDefaultQueryValues"]>1 )
						{
							$FormsfromId=$LastTableIds["DMSysDefaultQueryValues"];
							if($idFix["erase"] || !$hasOldSameId)
							{
								$QueriesfromId=1;
							}
						}
						
						if(isset($LastTableIds["DMSysEntities"]) && $LastTableIds["DMSysEntities"]!=null &&$LastTableIds["DMSysEntities"]>1 )
						{
							$EntitiesfromId=$LastTableIds["DMSysEntities"];
							if($idFix["erase"] || !$hasOldSameId)
							{
								$EntitiesfromId=1;
							}
						}
						/*TODO:Split definitions*/
						if(isset($LastTableIds["DMSysDefinitions"]) && $LastTableIds["DMSysDefinitions"]!=null &&$LastTableIds["DMSysDefinitions"]>1 )
						{
							$DefinitionsfromId=$LastTableIds["DMSysDefinitions"];
							if($idFix["erase"] || !$hasOldSameId)
							{
								$DefinitionsfromId=1;
							}
						}
						
						if(isset($LastTableIds[$currtableName]) && $LastTableIds[$currtableName]!=null &&$LastTableIds[$currtableName]>1 )
						{
							$fromId=$LastTableIds[$currtableName];
							if($idFix["erase"] || !$hasOldSameId)
							{
								$fromId=1;
							}
						}
						/*TODO:Split definitions*/
						if ($tableName != "DMSysDefinitions")
						{
							$this->FixIdOnValues($tableName,"valuesValueField",$newId,$oldId,$oldUniqueId,$fromId,$fromId,$LastTableIds,$erase,$deleteSystem);
						}
						
						if ($tableName == "DMSysEntities")
								{
									$this->FixIdByTableName("DMSysDefinitions",$tableName,"entity_id",$newId,$oldId,$oldUniqueId,$fromId,$DefinitionsfromId,$deleteSystem);
									$this->FixIdByTableName("DMSysForms",$tableName,"entity_id",$newId,$oldId,$oldUniqueId,$fromId,$FormsfromId,$deleteSystem);
									$this->FixIdByTableName("DMSysDefaultQueryValues",$tableName,"entity_id",$newId,$oldId,$oldUniqueId,$fromId,$QueriesfromId,$deleteSystem);
									
									$formsNameTable = $DM_CMSSpecials->GetTableName("DMSysForms");
									
									/*$query = " update ".$formsNameTable." set formName='entity_id_".$newId."' where entity_id=".$oldId." or entity_id=".$newId." and id>=".$FormsfromId;
									$genie_CMSSpecials->execute($query);*/
									$this->FixIdByTableNameFieldName("DMSysForms",$tableName,"formName",$newId,"'entity_id_".$newId."'",$oldId,$oldUniqueId,$fromId,$FormsfromId,$deleteSystem);
						}
						
						
						
						if ($tableName == "DMSysForms")
						{
							$this->FixIdByTableName("DMSysForms",$tableName,"connectedFormId",$newId,$oldId,$oldUniqueId,$fromId,$FormsfromId,$deleteSystem);
							$this->FixIdByTableName("DMSysDefaultQueryValues",$tableName,"form_id",$newId,$oldId,$oldUniqueId,$fromId,$QueriesfromId,$deleteSystem);
							$formsTable =$DM_CMSSpecials->GetTableName("DMSysForms");
							$tag = "[@form_id_".$oldId."@]";
							$query = "select * from ".$formsTable." where formHtml LIKE '%".$tag."%'";
							$forms =  $DM_CMSSpecials->getResults($query);
							if($this->CheckIfNotEmpty($forms))
							{
								foreach($forms as $form)
								{
									if(isset($form['formHtml'])  &&(trim($form['formHtml'])!=""  ) )
									{
										$formHtml = $form['formHtml'];
										
										if($this->checkPosition($formHtml,$tag) && $form["id"]>=$FormsfromId)
										{
											$newFormHtml = str_replace($tag, "[@form_id_".$newId."@]",$formHtml);
											$whereParams = array(array('fieldName'=>'id','value'=>$form["id"]));
											$updateParams = array(array('fieldName'=>'formHtml','value'=>$newFormHtml));
											$this->UpdateTable("DMSysForms",$updateParams,$whereParams);
										}
									}
								}
							}
							
							
							$dir =  dirname(dirname(__FILE__)) ;
							$DM_GeneralUsage=new genie_GeneralUsage();
							$myMenus = $DM_GeneralUsage->getData($dir."/files/adminmenus.txt");
							if($this->CheckIfNotEmpty($myMenus))
							{
								foreach($myMenus as $menu)
								{
									$dir =  dirname(dirname(__FILE__)) ;
									$formName=$menu["formname"];
									if(file_exists($dir."/files/form_".$formName.".html"))
									{
										$DM_GeneralUsage = new genie_GeneralUsage();
										$formHtml = $DM_GeneralUsage->getFileContent($dir."/files/form_".$formName.".html");
										$tag = "[@form_id_".$oldId."@]";
										if($this->checkPosition($formHtml,$tag) && $newId>=$FormsfromId)
										{
											$newFormHtml = str_replace($tag, "[@form_id_".$newId."@]",$formHtml);
											$DM_GeneralUsage->setFileContent($dir."/files/form_".$formName.".html",$newFormHtml);  
											
										}	
									}
								}
							}
							
							$formHtml = $DM_GeneralUsage->getFileContent($dir."/css/designform.css");
							$tag = "#form_id_".$oldId."";
							if($this->checkPosition($formHtml,$tag) && $newId>=$FormsfromId)
							{
								$newFormHtml = str_replace($tag, "#form_id_".$newId."",$formHtml);
								$DM_GeneralUsage->setFileContent($dir."/css/designform.css",$newFormHtml);  
								
							}
						}
						/*TODO:Split definitions*/
						if ($tableName == "DMSysDefinitions")
						{
							$erase=false;
							if(isset($idFix["erased"]) && $idFix["erased"])
							{
								//this means that old row was erased and needs to be fixed
								$fromId=$oldId;
								$FormsfromId=1;
							}
							else
							{
								$currtableName = $DM_CMSSpecials->GetTableName("DMSysForms");
								if(isset($LastTableIds[$currtableName]) && $LastTableIds[$currtableName]!=null &&$LastTableIds[$currtableName]>1 )
								{
									$FormsfromId=$LastTableIds[$currtableName];
									if($idFix["erase"])
									{
										$FormsfromId=1;
										$erase=true;
									}
								}
								
								
							}
							/*TODO:Split definitions*/
							$this->FixIdByTableName("DMSysDefinitions",$tableName,"ParentFieldId",$newId,$oldId,$oldUniqueId,$fromId,$fromId,$deleteSystem); 
							$this->FixIdByTableName("DMSysForms",$tableName,"parentField",$newId,$oldId,$oldUniqueId,$fromId,$FormsfromId,$deleteSystem);
							$this->FixIdByTableName("DMSysForms",$tableName,"childField",$newId,$oldId,$oldUniqueId,$fromId,$FormsfromId,$deleteSystem);   
							$this->FixIdByTableName("DMSysDefaultQueryValues",$tableName,"definition_id",$newId,$oldId,$oldUniqueId,$fromId,$QueriesfromId,$deleteSystem);    
							
							// $this->FixIdOnValues("valuesShowField",$newId,$oldId,$fromId,$fromId,$LastTableIds,$erase);
							
							
							/*TODO:Split definitions*/
							$TableName =$DM_CMSSpecials->GetTableName("DMSysDefinitions");
							$tag = "[@field_".$oldId."@]";
							$query = "select * from ".$TableName." where formula LIKE '%".$tag."%'";
							$fields =  $DM_CMSSpecials->getResults($query);
							if($this->CheckIfNotEmpty($fields))
							{ 
								foreach($fields as $field)
								{
									if(isset( $field['formula']) && trim( $field['formula'])!="")
									{
										
										$formula = $field['formula'];
										
										if($this->checkPosition($formula,$tag) && $field["id"]>=$DefinitionsfromId )
										{
											$newformula = str_replace($tag,  "[@field_".$newId."@]",$formula);
											$whereParams = array(array('fieldName'=>'id','value'=>$field["id"]));
											$updateParams = array(array('fieldName'=>'formula','value'=>$newformula));
											/*TODO:Split definitions*/
											$this->UpdateTable("DMSysDefinitions",$updateParams,$whereParams);
										}
									}
								}
							}
							
							
							$TableName =$DM_CMSSpecials->GetTableName("DMSysForms");
							$tag = "[@field_id_".$oldId."@]";
							$tag2 = "[@label_id_".$oldId."@]";
							$tag3 = "[@field_id_".$oldId."_code@]";
							$tag4 = "[@field_id_".$oldId."_readOnly@]";
							$query = "select * from ".$TableName." where formHtml LIKE '%".$tag."%' OR formHtml LIKE '%".$tag2."%' OR formHtml LIKE '%".$tag3."%' OR formHtml LIKE '%".$tag4."%' OR exerptHtml LIKE '%".$tag."%' OR exerptHtml LIKE '%".$tag2."%'";
							$forms =  $DM_CMSSpecials->getResults($query);
							if($this->CheckIfNotEmpty($forms))
							{ 
								foreach($forms as $form)
								{
									if(isset($form['formHtml']) && isset($form['exerptHtml']) &&(trim($form['formHtml'])!="" or trim($form['exerptHtml'])!="" ) )
									{
										$formHtml = $form['formHtml'];
										$exerptHtml = $form['exerptHtml'];
										
										
										if($this->checkPosition($formHtml,$tag) || $this->checkPosition($formHtml,$tag2) || $this->checkPosition($exerptHtml,$tag) || $this->checkPosition($exerptHtml,$tag2) && $newId>=$DefinitionsfromId)
										{
											$newFormHtml = str_replace($tag,  "[@field_id_".$newId."@]",$formHtml);
											$newFormHtml = str_replace($tag2,  "[@label_id_".$newId."@]",$newFormHtml);
											$newFormHtml = str_replace($tag3,  "[@field_id_".$newId."_code@]",$newFormHtml);
											$newFormHtml = str_replace($tag4,  "[@field_id_".$newId."_readOnly@]",$newFormHtml);
											
											$newexFormHtml = str_replace($tag, "[@field_id_".$newId."@]",$exerptHtml);
											$newexFormHtml = str_replace($tag2,  "[@label_id_".$newId."@]",$newexFormHtml);
											$newexFormHtml = str_replace($tag3, "[@field_id_".$newId."_code@]",$exerptHtml);
											$newexFormHtml = str_replace($tag4, "[@field_id_".$newId."_readOnly@]",$exerptHtml);
											$whereParams = array(array('fieldName'=>'id','value'=>$form["id"]));
											$updateParams = array(array('fieldName'=>'formHtml','value'=>$newFormHtml),array('fieldName'=>'exerptHtml','value'=>$newexFormHtml));
											$this->UpdateTable("DMSysForms",$updateParams,$whereParams);
										}
									}
								}
							}
							
							$dir =  dirname(dirname(__FILE__)) ;
							
							$fileName=$dir."/jscripts/model.js";
							if(file_exists($fileName))
							{
								$DM_GeneralUsage = new genie_GeneralUsage();
								$modeljs = $DM_GeneralUsage->getFileContent($fileName);
								$tag = "[@field_".$oldId."]";  
								if($this->checkPosition($modeljs,$tag) && $newId>=$DefinitionsfromId)
								{
									$newModeljs = str_replace($tag, "[@field_".$newId."@]",$modeljs);
									$DM_GeneralUsage->setFileContent($modeljs,$newModeljs);  
									
								}
								
							}
						}
						
					}
				}
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


public function FixIdOnValues($tableName,$parameter,$newId,$oldId,$oldUniqueId,$fromId=1,$currentLastId=1,$LastTableIds,$erase,$deleteSystem=false)
{
	
	$DM_CMSSpecials = new genie_CMSSpecials();
	$whereArray= $this->createParamsFromRow(array( "valuesTable"=>$tableName ,$parameter=>"id"),false);
	/*TODO:Split definitions*/
	$definitionsToFix = $this->Select("DMSysDefinitions",$whereArray,false);
	if($this->CheckIfNotEmpty($definitionsToFix))
	{
		foreach($definitionsToFix as $def)
		{          
			if(isset($def["entity_id"]) && isset($def["fieldName"]) && trim($def["entity_id"])!="" && trim($def["fieldName"])!="")
			{ 
				$tableNameToFix=$this->GetTableNameByEntity($def["entity_id"]);
				
				$FormsfromId = 1;
				if($LastTableIds[$tableNameToFix]!=null &&$LastTableIds[$tableNameToFix]>1 )
				{
					$FormsfromId=$LastTableIds[$tableNameToFix];
					if($erase)
					{
						$FormsfromId=1;
					}
				} 
				$this->FixIdByTableName($tableNameToFix,$tableName,$def["fieldName"],$newId,$oldId,$oldUniqueId,$fromId,$currentLastId,$FormsfromId,$deleteSystem);       
			}
			
		}
	}
}

public function FixId($tableName,$fieldName,$newId,$oldId,$fromId=1,$currentLastId=1,$deleteSystem=false)
{
	$DM_CMSSpecials = new genie_CMSSpecials();
	$currTableName = $DM_CMSSpecials->GetTableName($tableName);
	$extra = "";
	if(trim($fromId)=="")
	$fromId=1;
	if($deleteSystem)
	{
		$fromId=true;
		$query ="update ".$currTableName." set ".$fieldName."=".$newId." where ".$fieldName."=".$oldId." and ".$fieldName.">".$fromId." and isSystem=1";
	}
	else
	{
		$query ="update ".$currTableName." set ".$fieldName."=".$newId." where ".$fieldName."=".$oldId." and ".$fieldName.">".$fromId."";  
	}
	
	
	
	if($currentLastId>1)
	{
		$query .= " and id > ".$currentLastId;
	}
	
	
	$res = $DM_CMSSpecials->execute($query); 
	return $res;
}

public function FixIdByTableName($tableName, $secondTableName,$fieldName,$newId,$oldId,$oldUniqueId,$fromId=1,$currentLastId=1,$deleteSystem=false)
{
		$this->FixIdByTableNameFieldName($tableName, $secondTableName,$fieldName,$newId,$newId,$oldId,$oldUniqueId,$fromId,$currentLastId,$deleteSystem);
	
}

	public function FixIdByTableNameFieldName($tableName, $secondTableName,$fieldName,$newId,$newFieldValue,$oldId,$oldUniqueId,$fromId=1,$currentLastId=1,$deleteSystem=false)
	{
		$DM_CMSSpecials = new genie_CMSSpecials();
		$currTableName = $DM_CMSSpecials->GetTableName($tableName);
		$currsecondTableName =$DM_CMSSpecials->GetTableName($tableName);
		$extra = "";
		if(trim($oldUniqueId)!="")
		{
			$query = " update ".$currTableName." as A inner join ".$currsecondTableName." as B ".
				" on B.uniqueId='".trim($oldUniqueId)."' and A.".$fieldName."=".$oldId.
				//" and B.id=".$newId. //not sure if this necessary
				" Set A.".$fieldName."=".$newFieldValue;
			
			$res = $DM_CMSSpecials->execute($query); 
		return $res;}
	}
	
	public function GetDefinitionByName($tableName,$definitions,$name, $fieldNums=false)
	{
		if($fieldNums)
		{
			$fieldNumsArray = $this->GetColumnsNums($tableName);
			$name = $fieldNumsArray[$name];
		}
		foreach($definitions as $definition)
		{
			if($definition["fieldName"]==$name)
			{
				return $definition;
			}
			
		}
		return null;
		
	}
	
	
	
	public function SetDefault($tableName,$column,$definitions)
	{
		$DM_CMSSpecials = new genie_CMSSpecials();
		$currTableName = $DM_CMSSpecials->GetTableName($tableName);
		$fieldName = $column['Field'];
		$definition = $this->GetDefinitionByName($tableName,$definitions,$fieldName,false);
		$DM_Definition = new genie_Definition();
		if($definition!=null)
		{
			$definition["value"]= $definition["defaultValue"];
			$defaultValue =$DM_Definition->GetFormattedForQueryPartValue($definition);
			$query ="update ".$currTableName." set ".$fieldName."=".$defaultValue." where ".$fieldName." is null or ".$fieldName."=''  ";
			$DM_CMSSpecials->execute($query); 
		}
	}
	
	public function GetConnections()
	{
		
		//TODO:Save old uniqueId -> id pairs
		$DM_CMSSpecials = new genie_CMSSpecials();
		$FormsTable = $DM_CMSSpecials->GetTableName("DMSysForms");
		
		$query = "select Distinct A.entity_id as parentEntity,B.parentField, B.entity_id as childEntity,B.childField from 
          ".$FormsTable." as A inner join ".$FormsTable." as B
          on 
          A.id = B.connectedFormId";
		$connections = $DM_CMSSpecials->getResults($query);
		$newConnections = array();
		if($this->CheckIfNotEmpty($connections))
		{
			
			$tables =  $this->Select("DMSysEntities",array(),false);
			/*TODO:Split definitions*/
			$fields =   $this->Select("DMSysDefinitions",array(),false);
			foreach($connections as $con)
			{
				$newConnection = array();
				foreach($tables as $table)
				{
					if($table["id"] == $con["parentEntity"])
					{
						$newConnection["parentTable"] = $table["tableName"]; 
					}
					if($table["id"] == $con["childEntity"])
					{
						$newConnection["childTable"] = $table["tableName"]; 
					}
				}   
				
				foreach($fields as $field)
				{
					if($field["id"] == $con["parentField"])
					{
						$newConnection["parentField"] = $field["fieldName"]; 
					}
					if($field["id"] == $con["childField"])
					{
						$newConnection["childField"] = $field["fieldName"]; 
					}
				}
				array_push($newConnections,$newConnection);   
			}
		}
		
		return $newConnections; 
	}
	
	public function FastLoad($filePref,$tableName,&$Definitions)
	{
		//delete Previous
		$DM_CMSSpecials = new genie_CMSSpecials();
		$currtableName = $DM_CMSSpecials->GetTableName($tableName);
		$query = " delete from ".$currtableName." where id>0";
		$DM_CMSSpecials->execute($query);
		
		$errors=array();
		$idFixes = array();
		$tableMax=$this->LastTableId($tableName);
		$lineDelimiter="LINEDELIMITER"; 
		$specialDelimiter = "SPECIALDELIMITER";
		$DM_GeneralUsage = new genie_GeneralUsage();
		//$tableData = $genie_GeneralUsage->getDataNew($filePref."_".$tableName,$specialDelimiter,true,$lineDelimiter);
        $tableData = $DM_GeneralUsage->getDataJson($filePref."_".$tableName,true);
		$UniquParams = array();
		if(count($tableData)>0) {
            foreach ($tableData as $tableRow) {
                $emptyValues = 0;
                $deleteSystem = false;
                foreach ($Definitions as &$def) {
                    if (isset($tableRow[$def["fieldName"]])) {
                        $def["value"] = $tableRow[$def["fieldName"]];
                        if (trim($def["value"]) == "") {
                            $emptyValues++;
                        }

                    } else {
                        $def["value"] = $def["defaultValue"];
                        $emptyValues++;
                    }
                }
                if ($emptyValues < count($tableRow) || count($tableRow) <= 3) {
                    $resArr = $this->Insert($tableName, $Definitions, true, false);
                }
            }
        }
		
	}
	public function LoadSystemTable($filePref,$tableName,&$Definitions,$rewriteUnique=false,$rewriteSystem = false,$justCreated=false)
	{
		$errors=array();
		$idFixes = array();
		$tableMax=$this->LastTableId($tableName);
		$lineDelimiter="LINEDELIMITER"; 
		$specialDelimiter = "SPECIALDELIMITER";
		$DM_GeneralUsage = new genie_GeneralUsage();
		//$tableData = $genie_GeneralUsage->getDataNew($filePref."_".$tableName,$specialDelimiter,true,$lineDelimiter);
        $tableData = $DM_GeneralUsage->getDataJson($filePref."_".$tableName,true);
		$hasUnique=false;
		foreach($tableData as $tableRow)
		{
			$UniquParams = array();
			$emptyValues=0;
			$deleteSystem=false;
			foreach($Definitions as &$def)
			{
				if(isset( $tableRow[$def["fieldName"]] ))
				{
					if($rewriteSystem && $def["fieldName"]=="isSystem" && $tableRow[$def["fieldName"]]=="1")
					{
						$deleteSystem=true; 
					}
					$def["value"] = $tableRow[$def["fieldName"]];
					if(trim($def["value"])=="")
					{
						$emptyValues++;
					}
					if(($def["IsPartOfKey"]=="1"||$def["is_id"]=="1") && $rewriteUnique)
					{
						array_push($UniquParams,$def);
						
					}
					
					
				}
				else
				{
					$def["value"] = $def["defaultValue"];
					$emptyValues++;
				}
				
				
			}
			
			if($emptyValues<count($tableRow) || count($tableRow)<=5)
			{
				$updated=false;
				$oldIds=array();
				if((!$justCreated) && ($rewriteUnique || $deleteSystem || $rewriteSystem) && count($UniquParams)>0)
				{
					$systemId=$tableRow["id"];
					$uniqueId=$tableRow["uniqueId"];
					$whereParam = $UniquParams;
					$oldOnes = $this->Select($tableName,$whereParam,false,true);
					if($this->CheckIfNotEmpty($oldOnes))
					{
						foreach($oldOnes as $old)
							{
								array_push($oldIds,$old["id"]);
								$updateWhereParams =array();
								
								foreach($Definitions as $def)
								{
									if($def["fieldName"]=="id")
									{
										$def["value"]= $old["id"];
										$thisId=$old["id"];
										array_push($updateWhereParams,$def);
									}
									
								}
								
								//TODO: add check Version
								if($tableName=="DMSysForms")
								{
									$check=1;
								}
								$this->UpdateTable($tableName,$Definitions,$updateWhereParams,true);
								$updated=true;
								array_push($idFixes,array("newId"=>$thisId,"oldId"=>$systemId,"oldUniqueId"=>$uniqueId,"tableName"=>$tableName,'lastId'=>$tableMax,'hasOldSameId'=>$hasOldSameId,'isSystem'=>$deleteSystem,'fileName'=>$filePref));  
								
							
						}
					}
					
				}
				
				
				
				if(!$updated)
				{
					$oldId = $tableRow["id"]; 
					$uniqueId = $tableRow["uniqueId"];
					if(!$justCreated)
					{
						$hasOldSameId=false;
						$whereParam = $this->createParamsFromRow(array("id"=>$oldId),false);
						$oldOnes = $this->Select($tableName,$whereParam,false);
						if($this->CheckIfNotEmpty($oldOnes))
						{
							$hasOldSameId=true;    
						}
					}   
					$errorMessage="";
					if(trim($oldId)!="")
					{
						$resArr=$this->Insert($tableName,$Definitions,true,false);
						$errorMessage=$resArr['error'];
						
						$newId = $resArr['res'];
						if($newId>0 )
						{
							
							if( $newId!=$oldId)
						{
						
								array_push($idFixes,array("newId"=>$newId,"oldId"=>$oldId,"oldUniqueId"=>$uniqueId, "tableName"=>$tableName,'lastId'=>$tableMax,'hasOldSameId'=>$hasOldSameId,'isSystem'=>$deleteSystem,'fileName'=>$filePref));
							}
							if(!$justCreated)
							{
								foreach($oldIds as $oldId)
								{
									if($newId!=$oldId)
									{
										array_push($idFixes,array("newId"=>$newId,"oldId"=>$oldId,"oldUniqueId"=>$uniqueId,"tableName"=>$tableName,'lastId'=>$oldId,"erased"=>true,'hasOldSameId'=>$hasOldSameId,'isSystem'=>$deleteSystem,'fileName'=>$filePref));
									}
								}
							}
							
						}
						else
						{
							$tableRow["error"]=$errorMessage; 
							array_push($errors ,$tableRow);
						}
						
					}
					
					else
					{
						$tableRow["error"]="old Id not found";   
						array_push($errors ,$tableRow);
					}
				}
				
			}
			else
			{
				$tableRow["error"]="empty values";   
				array_push($errors ,$tableRow);
			}
		}
		if($this->CheckIfNotEmpty($errors))
		{
			//$genie_GeneralUsage->saveArrayNew($errors,$filePref."_".$tableName."_errors",";",true,"\n");
            $DM_GeneralUsage->saveArrayJson($errors,$filePref."_".$tableName."_errors",true);
		}
		return $idFixes;
		
	}
	
	
	public function getDefinitionById ($id)
	{
		$definitionDetails=null;
		$whereParams=$this->createParamsFromRow(array('id'=>$id));
		/*TODO:Split definitions*/
		$Definitions = $this->Select("DMSysDefinitions",$whereParams,false,false);
		if($this->CheckIfNotEmpty($Definitions))
		{
			$definitionDetails=$Definitions[0];
		}
		return $definitionDetails;        
	}
	public function LastTableIds()
	{
		$lastIds= array();
		$query = "show tables";
		$DM_CMSSpecials = new genie_CMSSpecials();
		$tables = $DM_CMSSpecials->getResults($query);
		$prefix = $DM_CMSSpecials->getprefix();
		if($this->CheckIfNotEmpty($tables))
		{
			foreach($tables as $table)
			{
				if(isset($table[0]))
				{
					if($this->checkPosition($table[0],$prefix))
					{
						$id = $this->LastTableId($table[0],true);
						$tableName=str_replace($prefix,"",$table[0]);
						$lastIds[$tableName]=$id;
					}
				}
			}
		}
		return $lastIds;
	}
	
	public function LastTableId($FormsTable,$actualdbname=false)
	{  
		$DM_Table=new genie_Table();
		$maxId=1;
		$DM_CMSSpecials = new genie_CMSSpecials();
		
		if($DM_Table->CheckIfTableExistsOnDataBase($FormsTable))
		{
			if(!$actualdbname)
			{
				$FormsTable = $DM_CMSSpecials->GetTableName($FormsTable);
			}
			$query = "select Max(id) as max_id from ".$FormsTable;
			$results=$DM_CMSSpecials->getResults($query);
			if($this->CheckIfNotEmpty($results))
			{
				$maxId=$results[0]["max_id"];
			}
		}
		return $maxId;
	}
	
	
	public function getFormsId($inputName,$inputId, $selectedValue,$id)
	{
		$entityId = $this->getEntityId("DMSysForms");
		$result="<label for='".$id."'>".$this->DM_CMSSpecials->translate( 'Display Name' ,$entityId) ."</label> <select name='".$inputName."' id='".$inputId."'>";
		$whereArray=array();
		if(!$this->developing)
		{
			$whereArray = $this->createParamsFromRow(array("isSystem"=>0)) ;
		}
		$Forms = $this->Select("DMSysForms",$whereArray,false); 
		if($this->CheckIfNotEmpty($Forms))
		{
			foreach($Forms as $form)
			{
				$extra="";
				if($form['id']==$selectedValue)
				{
					$extra = " selected=\"selected\"";
				}
				$result.=" <option ".$extra." value=".$form['id'].">".$form['displayName']."</option>"; 
			}
		} 
		$result.=" </select>";     
		return $result;
	}
	
	public function getThisBlogTables()
	{
		$results=array();
		$DM_CMSSpecials=new genie_CMSSpecials();
		
		$prefix=$DM_CMSSpecials->getprefix();
		$query='SHOW TABLES LIKE "' . $prefix .'%"';
		$tables = $DM_CMSSpecials->getResults( $query ); 
		if($this->CheckIfNotEmpty($tables))
		{
			foreach($tables as $table)
			{
				if(count($table)>0)
				{
					foreach($table as $key=>$value)
					{
						$tableName=str_replace($prefix,"",$value);
						
						
						array_push($results,array('tableName'=>$tableName)); 
					}
				}
			}
		}
		return $results;
	} 
	
}