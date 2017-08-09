$(document).ready(function(){
    $(".custom_text").focusin(function(){
        var value = $(this).val();
        if(value == "Filter") $(this).val("");
    });
    
    $(".custom_text").focusout(function(){
        var value = $(this).val();
        if(value == "") $(this).val("Filter");
    });
    
    $(".close_button").click(function(){
        var degrees = getRotationDegrees($(this));
        (degrees) ? degrees = 0 : degrees = 180;
        var element = $(this).parent().parent().children(".box_body");
        $(element).slideToggle(400);
        $(this).css({'-webkit-transform' : 'rotate('+ degrees +'deg)',
                 '-moz-transform' : 'rotate('+ degrees +'deg)',
                 '-ms-transform' : 'rotate('+ degrees +'deg)',
                 'transform' : 'rotate('+ degrees +'deg)'});
    });
    /*
    tinymce.init({
        selector: "textarea",
        editor_selector: "mceEditor",
        editor_deselector: "mceNoEditor",
        toolbar: "undo redo | bold italic | bullist numlist | link | code",
        plugins: "link, code",
        menubar: false,
        statusbar: false
    });
      */
      /*
    $(".popup_close").click(function(){
        $(this).parent().parent().hide();
        $(".main_page").css("opacity","1");
    });
    
    $("#new_data").click(function(){
        $("#addnewdatatype").show();
        $(".main_page").css("opacity",".5");
    });
    
    $("#new_view").click(function(){
        $("#addnewview").show();
        $(".main_page").css("opacity",".5");
    });
    
    $("#new_field").click(function(){
        $("#addnewfield").show();
        $(".main_page").css("opacity",".5");
    });
        
    $("#edit_field").click(function(){
        $("#editfield").show();
        $(".main_page").css("opacity",".5");
    });
    */
});

function getRotationDegrees(obj) {
    var matrix = obj.css("-webkit-transform") ||
    obj.css("-moz-transform") ||
    obj.css("-ms-transform") ||
    obj.css("-o-transform") ||
    obj.css("transform");
    if(matrix !== 'none') {
        var values = matrix.split('(')[1].split(')')[0].split(',');
        var a = values[0];
        var b = values[1];
        var angle = Math.round(Math.atan2(b, a) * (180/Math.PI));
    } else { var angle = 0; }
    return (angle < 0) ? angle +=360 : angle;
}