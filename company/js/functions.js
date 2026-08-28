var counter = 0;   
function addtosupplier(id, url, image)
{
    $("#WebcastFix").show();

    var id = id;
    var i = 0;
    
    var htmls = '';
    var url=url;
    
    if (counter == 20) {
        alert('Only 20 products can be added.');
        return false;
    }
    $.ajax({
       type:'POST',
        url: "dat.php",
         data:{"id":id,"img":image},
        success: function(html)
        {
          counter++;
           
        }
    });


    $(".select_list").each(function(index)
    {
        if($(this).children("input[type='hidden']").val()==id)
            { 
                
                var select_list=$(".select_list");
                select_list.each(function (){
                    //console.log($(this).find( ":hidden" ).val());
                    
                    if($(this).find( ":hidden" ).val()==id){
                        $(this).empty();
                        $('#arrowleft').trigger('click');
                        counter--;
                    }
                });
                 
                return false;
                
            }
                
        

        if (i != 1)
        {
            var images = $(this).find('img');

            if (images.length === 0) {


                var htmls = '<img src="../upload/myproduct/' + image + '" > <input type="hidden" name="selectlist[]" value="' + id + '">'

                $(this).html(htmls);

               
                setTimeout(function() {               
                    $('#arrowright').trigger('click');
                }, 1500);
                

                i++;

            }
        }
    });
    console.log(counter);
}