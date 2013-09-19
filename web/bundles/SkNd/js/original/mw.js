(function ($) {
    var bar, percentVal, ugcForm;
    
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
    ugcForm = $('#add-ugc-form');
    ugcForm.ajaxForm({
        dataType: 'json',
        beforeSend: function () {
            percentVal = '0%';
            bar.width(percentVal);
            
        },
        uploadProgress: function (event, position, total, percentComplete) {
            percentVal = percentComplete + '%';
            bar.width(percentVal);
        },
        success: function () {
            $('.error', ugcForm).removeClass('error');
            $('ul.form-errors').remove();
            percentVal = '100%';
            bar.width(percentVal);
        },
        complete: function (xhr) {
            var json = JSON.parse(xhr.responseText),
                form = $(this),
                error,
                errorList;
            if (json.status === 'fail') {
                for (error in json.content) {
                    if (json.content.hasOwnProperty(error)) {
                        errorList = $('<ul class="form-errors" />').append('<li>'+ json.content[error] +'</li>');
                        $('[name*="'+ error +'"]', ugcForm)
                            .parent()
                            .addClass('error')
                            .prepend(errorList);
                    }
                }
            } else {

            }
        }
    });

}(jQuery));
