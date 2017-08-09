

function CreateDataSource()
{
    var tableNameText = document.getElementById("tableName").value;   
  
    var senddata = {
                           'action':'DMWizardsRequest',
                           'formaction':'createDefaults',
                           "tableName":   tableNameText
                           };                      
    var funccall = function(result)
    {
       // alert(result);
        document.getElementById("FormTestResult").innerHTML = result;  
    }
    applyToServer(funccall,funccall,senddata);
}

function CreateDisplays()
{
    var tableNameText = document.getElementById("tableName").value;   
  
    var senddata = {
                           'action':'DMWizardsRequest',
                           'formaction':"createDisplays",
                           "tableName":   tableNameText
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