(function ($) {
    var flashMessages = $('<div class="flashMessages fr"><ul></ul></div>'),
        header = $('div#header'),
        wrapper = $('div#wrapper'),
        makeFlashMessage = function (flash) {
            wrapper.remove('.flashMessages');
            flashMessages
                .empty()
                .attr('style', '')
                .append($('<ul />'));
            $('<li class="info">' + flash + '</li>').appendTo($('ul', flashMessages));
            header.after(flashMessages);
            $('<a class="sprites close-icon">hide this message</a>').click(function () {
                $(this).parent().hide();
            }).prependTo(flashMessages);
        },
        deleteMWC = function () {
            var obj = $(this),
                mwc = obj.parents('li.mwc'),
                url = obj.attr('href') + '/true';

            if (confirm('Are you sure you want to remove this from your wall?')) {
                $.get(url, function (data) {
                    if (data.status === 'success') {
                        makeFlashMessage(data.flash);
                        mwc.fadeOut(500, function () {
                            mwc.remove();
                        });
                    } else {
                        makeFlashMessage('An error occurred, please try again');
                    }
                });
            }
            return false;
        },
        getAjaxFormOptions = function () {
            var form = this,
                dataType = 'json',
                percentVal = '0%',
                percentBar = $('.progress-bar', form);                
            return {
                dataType: dataType,
                percentVal: percentVal,
                percentBar: percentBar,
                beforeSend: arguments.beforeSend || function () {
                    percentVal = '0%';
                    percentBar.width(percentVal);
                    flashMessages.empty();
                },
                uploadProgress: arguments.uploadProgress || function (event, position, total, percentComplete) {
                    percentVal = percentComplete + '%';
                    percentBar.width(percentVal);
                },
                success: arguments.success || function () {
                    $('.error', form).removeClass('error');
                    $('ul.form-errors').remove();
                    percentVal = '100%';
                    percentBar.width(percentVal);
                },
                complete: arguments.complete || function (xhr) {
                    var json,
                        error,
                        errorList,
                        ugcContent;
                    if (xhr.status !== 200) {
                        makeFlashMessage('An error occurred, please try again');
                        return;
                    }
                    json = JSON.parse(xhr.responseText);
                    makeFlashMessage(json.flash);
                    if (json.status === 'fail') {
                        //if a completeFail function was found in arguments, use this one, otherwise fall back to default
                        if (arguments.completeFail) {
                            arguments.completeFail();
                        } else {
                            for (error in json.content) {
                                if (json.content.hasOwnProperty(error)) {
                                    errorList = $('<ul class="form-errors" />').append('<li>' + json.content[error] + '</li>');
                                    $('[name*="' + error + '"]', form)
                                        .parent()
                                        .addClass('error')
                                        .prepend(errorList);
                                }
                            }
                        }
                    } else {
                        ugcContent = $(json.content);
                        if (completeSuccess !== 'undefined' && typeof completeSuccess === 'function') {
                            completeSuccess(ugcContent);
                        } else {
                            //if there's nothing on the wall
                            if ($('div#memoryWallContents > ul').length === 0) {
                                $('div#memoryWallContents').empty().prepend($('<ul />'));
                            }
                            ugcContent.hide()
                                .prependTo($('div#memoryWallContents > ul'))
                                .fadeIn(1000);
                            form.clearForm().parent().removeClass('open');
                            //bind delete event
                            $('ul.actions a.delete', ugcContent).click(deleteMWC);
                            //bind edit event
                            $('ul.actions a.edit', ugcContent).click(editMWC);
                        }
                    }
                    percentBar.width(0);
                }
            };
        },
        editMWC = function (ugcContent) {
            var obj = $(this),
                mwcView = obj.parents('div.mwc-view'),
                ugcListItem = mwcView.parent(),
                content,
                mwcEdit = $('.mwc-edit', ugcListItem).length > 0 ? $('.mwc-edit', ugcListItem) : $('<div class="mwc-edit" />'),
                completeSuccess = function () {
                    ugcListItem.empty();
                    ugcContent.hide()
                        .prependTo(ugcListItem)
                        .fadeIn(1000);
                    
                    //bind delete event
                    $('ul.actions a.delete', ugcContent).click(deleteMWC);
                    //bind edit event
                    $('ul.actions a.edit', ugcContent).click(editMWC);
                };

            //if the form hasn't been loaded yet
            if (mwcEdit.children('form').length === 0) {
                $.get(obj.attr('href'), function (data) {
                    content = data.content;
                    mwcEdit.append(content).show();
                    ugcListItem.append(mwcEdit);
                    mwcView.hide();

                    $('a.cancel', mwcEdit).click(function () {
                        mwcEdit.hide();
                        mwcView.show();
                        return false;
                    });
                    var editFormOptions = getAjaxFormOptions.apply($('#edit-ugc-form', mwcEdit), [completeSuccess]);
                    //rebind the form to the ajax edit method and create a new 'completeSuccess' function for getAjaxFormOptions
                    $('#edit-ugc-form', mwcEdit).ajaxForm(editFormOptions);
                }, 'json');
            } else {
                mwcEdit.show();
                mwcView.hide();
            }

            return false;
        };

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

    //make the add ugc form ajax-able
    $('#add-ugc-form').ajaxForm(getAjaxFormOptions.apply($('#add-ugc-form')));

    // ajax call edit form, if form already exists, just show it
    $('li.mwc ul.actions a.edit').click(editMWC);
    
    //bind existing delete buttons to the delete function
    $('li.mwc ul.actions a.delete').click(deleteMWC);


}(jQuery));
