(function ($) {
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
    
    function submitMWUGC () {
        var form = $(this),
            formData = form.serialize(),
            formUrl = form.attr('action'),
            formMethod = form.attr('method'),
            formEncType = form.attr('enctype'),
            req;
        
        req = $.ajax({
            url: formUrl,
            type: formMethod,
            data: formData,
            enctype: formEncType,
            dataType: "json",
            success: function (data) {
                //form.parent().html(data);
                //$('#add-ugc-form').submit(submitMWUGC);
            }
        });

        return false;
    }
    
    $('#add-ugc-form').submit(submitMWUGC);

}(jQuery));
