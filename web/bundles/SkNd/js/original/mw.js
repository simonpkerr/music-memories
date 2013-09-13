(function ($) {
    var bar, percentVal;
    
    $('div#mw-options li').hover(function () {
        if(!$('> div', this).hasClass('open')) {
            $('> div', this).css('top', '-9999em');
        }
    }, function () {
    });

    $('div#mw-options li > a').on("click", function () {
        var obj = $(this),
            panel = obj.next(),
            panelIsOpen = panel.hasClass('open');

        $('div#mw-options li > div').removeClass('open').css('top', '-9999em');

        if(!panelIsOpen) {
            panel.addClass('open').attr('style', '');
        } else {
            panel.removeClass('open').css('top', '-9999em');
        }
        return false;
    });

    bar = $('#add-ugc-container #bar');
    $('#add-ugc-form').ajaxForm({
        dataType: 'json',
        beforeSend: function () {
            percentVal = '0%';
            bar.width(percentVal);
        },
        uploadProgress: function (event, position, total, percentComplete) {
            percentVal = percentComplete + '%';
            bar.width(percentVal);
        },
        success: function() {
            percentVal = '100%';
            bar.width(percentVal);
        },
        complete: function(xhr) {
            var json = JSON.parse(xhr.responseText);
        }        
    });

}(jQuery));
