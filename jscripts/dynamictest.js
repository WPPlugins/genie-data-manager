function DMTest_applyToServer(successcall,failcall,data)
{
     $.ajax({
                      url: 'admin-ajax.php',
                      type: 'POST',
                      data: data,
                      success: successcall,
                      fail: failcall
                    });
}


function GetDefaultForms()
{
    
    var formType = document.getElementById("formType").value;   
     var tableName = document.getElementById("tableName").value; 
  
    var senddata = {
                           'action':'DMSystemDynamicRequest',
                           'formaction':'GetFormTestHtml',
                           'parameters':   { 'formType': formType,'tableName':tableName }
                           };                      
    var funccall = function(result)
    {
       // alert(result);
       
        if(result.search("error")>-1)
        {
            alert(result);
        }  
        else
        {
             document.getElementById("GetFormTestResult").innerHTML = result; 
                var senddata2 = {
                           'action':'DMSystemDynamicRequest',
                           'formaction':'GetFormTestModels',
                           'parameters':   { 'formType': formType,'tableName':tableName }
                           };      
                 var funccall2 = function(result2)
                {
                   
                        if(result2.search("error")>-1)
                            {
                                alert(result2);
                            }  
                            else
                            {
                                
                                ko.cleanNode($("body")[0]); 
                                ko.applyBindings(new DM_Model(result2));
                               
                            }
                } 
                
                  DMTest_applyToServer(funccall2,funccall2,senddata2);                     
                           
        }
    }
    DMTest_applyToServer(funccall,funccall,senddata);
}

