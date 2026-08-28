$(document).ready(function(){
    $.post("cmsmessages",function(data){
        data=data.split('||||');
        var title=data[0].split('^^^^');
        var langmsg=data[1].split('^^^^');
        var ln=title.length;
        msg = new Array();
        for(var i=0; i<ln; i++){
            msg[title[i]] = langmsg[i];
        }
        })
})