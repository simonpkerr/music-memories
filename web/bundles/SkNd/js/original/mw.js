(function ($) {
    var percentVal = '0%',
        ugcForm = ugcForm = $('#add-ugc-form'),
        bar = $('#add-ugc-container #bar'),
        flashMessages = $('<div class="flashMessages fr"><ul></ul></div>');
        
    
    $('<a class="sprites close-icon">hide this message</a>').click(function (){
        $(this).parent().hide();
    }).prependTo(flashMessages);

    $('div#mw-options li').hover(function () {
        if (!$('> div', this).hasClass('open')) {
            $('> div', this).css('top', '-9999em');
        }
    }, function () {
    });

    $('div#mw-options li > a').on("click", function () {
        var obj = $(this),
            panel = obj.next(),
            panelIsOpen = panel.hasClass('open');

        $('div#mw-options li > div').removeClass('open').css('top', '-9999em');

        if (!panelIsOpen) {
            panel.addClass('open').attr('style', '');
        } else {
            panel.removeClass('open').css('top', '-9999em');
        }
        return false;
    });

    ugcForm.ajaxForm({
        dataType: 'json',
        beforeSend: function () {
            percentVal = '0%';
            bar.width(percentVal);
            $('div.flashMessages').empty();
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
                error,
                errorList,
                ugcContent,
                makeFlashMessage = function (flash) {
                    $('div.flashMessages').attr('style', '').children('ul').empty();
                    $('<li class="info">' + flash + '</li>').appendTo($('ul', flashMessages));
                    $('div#header').after(flashMessages);
                };
            makeFlashMessage(json.flash);
            if (json.status === 'fail') {
                for (error in json.content) {
                    if (json.content.hasOwnProperty(error)) {
                        errorList = $('<ul class="form-errors" />').append('<li>' + json.content[error] + '</li>');
                        $('[name*="' + error + '"]', ugcForm)
                            .parent()
                            .addClass('error')
                            .prepend(errorList);
                    }
                }
            } else {
                //if there's nothing on the wall
                if($('div#memoryWallContents > ul').length === 0) {
                   $('div#memoryWallContents').empty().prepend($('<ul />'));
                }
                ugcContent = $(json.content)
                    .hide()
                    .prependTo($('div#memoryWallContents > ul'))
                    .fadeIn(1000);
                ugcForm.clearForm().parent().removeClass('open');

            }

            bar.width(0);
        }
    });

}(jQuery));
