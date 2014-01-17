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
        showHideLoader = function (obj, show) {
            var loaderSprite = '<span class="loader-sprite">&nbsp;</span>';
            if(show) {
                obj.before(loaderSprite);
            } else {
                $('.loader-sprite', obj.parent()).remove();
            }
        },
        deleteMWC = function () {
            var obj = $(this),
                mwc = obj.parents('li.mwc'),
                url = obj.attr('href') + '/true';

            if (confirm('Are you sure you want to remove this from your wall?')) {
                showHideLoader(obj, true);
                $.get(url, function (data) {
                    showHideLoader(obj, false);
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
                beforeSend: form.beforeSend || function () {
                    percentVal = '0%';
                    percentBar.width(percentVal);
                    flashMessages.empty();
                    showHideLoader(form, true);
                },
                uploadProgress: form.uploadProgress || function (event, position, total, percentComplete) {
                    percentVal = percentComplete + '%';
                    percentBar.width(percentVal);
                },
                success: form.success || function () {
                    $('.error', form).removeClass('error');
                    $('ul.form-errors').remove();
                    percentVal = '100%';
                    percentBar.width(percentVal);
                },
                complete: form.complete || function (xhr) {
                    var json,
                        error,
                        errorList,
                        ugcContent;
                    showHideLoader(form, false);
                    if (xhr.status !== 200) {
                        makeFlashMessage('An error occurred, please try again');
                        return;
                    }
                    json = JSON.parse(xhr.responseText);
                    makeFlashMessage(json.flash);
                    if (json.status === 'fail') {
                        //if a completeFail function was found in arguments, use this one, otherwise fall back to default
                        if (form.completeFail) {
                            form.completeFail();
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
                        if (form.completeSuccess) {
                            form.completeSuccess(ugcContent);
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
        editMWC = function () {
            var obj = $(this),
                mwcView = obj.parents('div.mwc-view'),
                ugcListItem = mwcView.parent(),
                content,
                mwcEdit = $('.mwc-edit', ugcListItem).length > 0 ? $('.mwc-edit', ugcListItem) : $('<div class="mwc-edit" />');

            //if the form hasn't been loaded yet
            if (mwcEdit.children('form').length === 0) {
                showHideLoader(obj, true);
                $.get(obj.attr('href'), function (data) {
                    showHideLoader(obj, false);
                    content = data.content;
                    mwcEdit.append(content).show();
                    ugcListItem.append(mwcEdit);
                    mwcView.hide();
                    var editForm = $('#edit-ugc-form', mwcEdit),
                        editFormOptions;

                    $('a.cancel', mwcEdit).click(function () {
                        mwcEdit.hide();
                        mwcView.show();
                        return false;
                    });

                    //specify a different complete success function for when submit is pressed
                    editForm.completeSuccess = function (ugcContent) {
                        ugcContent.hide();
                        ugcListItem.replaceWith(ugcContent.fadeIn(1000));

                        //bind delete event
                        $('ul.actions a.delete', ugcContent).click(deleteMWC);
                        //bind edit event
                        $('ul.actions a.edit', ugcContent).click(editMWC);
                    };
                    editFormOptions = getAjaxFormOptions.apply(editForm);
                    //rebind the form to the ajax edit method and create a new 'completeSuccess' function for getAjaxFormOptions
                    editForm.ajaxForm(editFormOptions);
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
