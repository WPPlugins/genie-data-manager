function DMShowHideDiv(div)
{
    $(div).toggle();
}

function DMAddContent(newtitle,newtxt)
{
    tinyMCE.activeEditor.dom.add(tinyMCE.activeEditor.getBody(), 'p', {title : newtitle}, newtxt);
}