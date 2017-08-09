

function LoadTablesFunction()
{
    var senddata = {
                           'action':'DMSystemDynamicRequest',
                           'formaction':'LoadTables'
                           };                      
    var funccall = function(result)
    {
        alert(result);
    }
    applyToServer(funccall,funccall,senddata);
}


function RunQuery()
{
    var Query = document.getElementById("queryText").value;   
    var QueryType = document.getElementById("queryType").value;   
    var senddata = {
                           'action':'DMSystemDynamicRequest',
                           'formaction':'RunQuery',
                           'Query':   Query,
                           'QueryType':QueryType
                           };                      
    var funccall = function(result)
    {
       // alert(result);
        document.getElementById("QueryResult").innerHTML = result; 
    }
    applyToServer(funccall,funccall,senddata);
}

function SaveQueryToFile()
{
    var Query = document.getElementById("queryText").value;   
    var QueryType = document.getElementById("queryType").value;   
    var fileNameTxt = document.getElementById("fileNameTxt").value;
    var senddata = {
                           'action':'DMSystemDynamicRequest',
                           'formaction':'SaveQueryToFile',
                           'Query':   Query,
                           'QueryType':QueryType,
                           'fileNameTxt':fileNameTxt
                           };                      
    var funccall = function(result)
    {
       // alert(result);
        document.getElementById("QueryResult").innerHTML = result; 
    }
    applyToServer(funccall,funccall,senddata);
}


function SaveSystemToFile()
{
  
    var fileNameTxt = document.getElementById("fileNameTxt").value;
    var senddata = {
                           'action':'DMSystemDynamicRequest',
                           'formaction':'SaveSystemState',
                           
                           'fileNameTxt':fileNameTxt
                           };                      
    var funccall = function(result)
    {
       // alert(result);
        document.getElementById("QueryResult").innerHTML = result; 
    }
    applyToServer(funccall,funccall,senddata);
}

function LoadSystemFromFile()
{
  
    var fileNameTxt = document.getElementById("fileNameTxt").value;
    var senddata = {
                           'action':'DMSystemDynamicRequest',
                           'formaction':'LoadSystemState',
                           
                           'fileNameTxt':fileNameTxt
                           };                      
    var funccall = function(result)
    {
       // alert(result);
        document.getElementById("QueryResult").innerHTML = result; 
    }
    applyToServer(funccall,funccall,senddata);
}

function GetDefaultDefinitions()
{
    var tableNameText = document.getElementById("tableNameText").value;   
  
    var senddata = {
                           'action':'DMSystemDynamicRequest',
                           'formaction':'GetDefaultDefinitions',
                           'tableNameText':   tableNameText
                           };                      
    var funccall = function(result)
    {
       // alert(result);
        document.getElementById("TableTestResult").innerHTML = result; 
    }
    applyToServer(funccall,funccall,senddata);
}

function GetDefaultForms()
{
    var tableNameText = document.getElementById("tableNameText").value;   
  
    var senddata = {
                           'action':'DMSystemDynamicRequest',
                           'formaction':'GetDefaultForms',
                           'tableNameText':   tableNameText
                           };                      
    var funccall = function(result)
    {
       // alert(result);
        document.getElementById("FormTestResult").innerHTML = result;  
    }
    applyToServer(funccall,funccall,senddata);
}

function applyToServer(successcall,failcall,data)
{
     $.ajax({
                      url: 'admin-ajax.php',
                      type: 'POST',
                      data: data,
                      success: successcall,
                      fail: failcall
                    });
}