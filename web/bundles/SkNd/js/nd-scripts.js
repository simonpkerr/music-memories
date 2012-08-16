//check for media queries
/*Modernizr.load([
    {
        test: Modernizr.mq('only screen and (min-width: 600px)'),
        nope: ['/SkNd/web/bundles/SkNd/js/respond.min.js']
    }
]);*/
//console.log(Modernizr.mq('only screen and (min-width: 600px)'));


$(document).ready(function(){

var $allGenres = eval($("#mediaSelection_genres").val());
var $selectedMediaGenre = $("select#mediaSelection_selectedMediaGenre");
var $selectedGenre = $selectedMediaGenre.val();

populateGenres($("select#mediaSelection_mediaType").val());

$("select#mediaSelection_mediaType").change(function(){
    populateGenres($(this).val())
});

//when a media is selected, populate the genres
function populateGenres(selectedMedia){
    $selectedMediaGenre.empty();
    $selectedMediaGenre.append("<option value=\"\">All Genres</option>");
    
    //only populate with genres if the film and tv option was not selected
    if(selectedMedia != 4){
        $.each($allGenres, function(i,genre){
            if(genre.mediaType_id == selectedMedia){
                $selectedMediaGenre.append("<option value=\""+ genre.id +"\">"+ genre.genreName +"</option>");

            }
        });
        $selectedMediaGenre.val($selectedGenre);
    }
    
}

});


       



