$(document).ready(function () {
    var allGenres = eval($("#mediaSelection_genres").val()),
        selectedMediaGenre = $("select#mediaSelection_selectedMediaGenre"),
        selectedGenre = selectedMediaGenre.val();

    //when a media is selected, populate the genres
    function populateGenres(selectedMedia) {
        
        selectedMediaGenre.empty();
        selectedMediaGenre.append("<option value=\"\">All Genres</option>");

        //only populate with genres if the film and tv option was not selected
        if (selectedMedia !== 4) {
            $.each(allGenres, function () {
                if (this.mediaType_id === selectedMedia) {
                    selectedMediaGenre.append("<option value=\"" + this.id + "\">" + this.genreName + "</option>");

                }
            });
            selectedMediaGenre.val(selectedGenre);
        }
    }

    populateGenres($("select#mediaSelection_mediaType").val());

    $("select#mediaSelection_mediaType").change(function () {
        populateGenres(parseInt($(this).val(), 10));//parse as decimal int
    });

    // social media sharing icons pop up
    $(document).on("click", "a.popup", function () {
        var newwindow = window.open($(this).attr('href'), '', 'height=400,width=450');
        if (window.focus) {
            newwindow.focus();
        }
        return false;
    });


});


