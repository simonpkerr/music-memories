$(document).ready(function(){
var $allGenres = eval($("#mediaSelection_genres").val());
var $selectedMediaGenres = $("select#mediaSelection_selectedMediaGenres");
var $selectedGenre = $selectedMediaGenres.val();

populateGenres($("#mediaSelection_mediaTypes").val());
$("select#mediaSelection_mediaTypes").change(function(){
    populateGenres($(this).val())
});

//when a media is selected, populate the genres
function populateGenres(selectedMedia){
    $selectedMediaGenres.empty();
    $selectedMediaGenres.append("<option value=\"\">All Genres</option>");
    
    //only populate with genres if the film and tv option was not selected
    if(selectedMedia != 4){
        $.each($allGenres, function(i,genre){
            if(genre.mediaType_id == selectedMedia){
                $selectedMediaGenres.append("<option value=\""+ genre.id +"\">"+ genre.genreName +"</option>");

            }
        });
        $selectedMediaGenres.val($selectedGenre);
    }
    
}

});


       



