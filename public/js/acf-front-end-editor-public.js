(function(window, $, undefined) {
    'use strict';

    // popup service
    var msg = {
        makeid: function(length) {
            var text = "";
            var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
            for (var i = 0; i < length; i++)
                text += possible.charAt(Math.floor(Math.random() * possible.length));
            return text;
        },
        count: window.popCount = 0,
        pop: function(message) {
            console.log(message);
            this.count++;
            var $id = this.makeid(5);
            var pops = $('.hd-pop');
            var gap = 10;
            var top = 20;
            var item = 70;
            if (pops.length === 0) this.count = 1;
            $('body').append('<div class="hd-pop" id="pop_' + $id + '">' + message + '</div>');
            $('#pop_' + $id).css('top', top + ((item + gap) * this.count));

            $('#pop_' + $id).addClass('animated lightSpeedIn');
            setTimeout(
                function() {
                    $('#pop_' + $id).addClass('animated lightSpeedOut');
                    setTimeout(
                        function() {
                            $('#pop_' + $id).remove();
                        }, 1000
                    );
                }, 2000
            );
        }
    };


    $(document).ready(function() {
         var frame,
            metaBox = $('#meta-box-id.postbox'), // Your meta box id here
            addImgLink = metaBox.find('.upload-custom-img'),
            delImgLink = metaBox.find( '.delete-custom-img'),
            imgContainer = metaBox.find( '.custom-img-container'),
            imgIdInput = metaBox.find( '.custom-img-id' );
            var editableImages = jQuery('[src*="acfImageEditable"]').addClass('acfImageEditable');
            var target, $target; // make global so that frame select picks up the current target
            editableImages.on('click', function(){
                console.log(this);
              // Get media attachment details from the frame state
              event.preventDefault();
              
              target = this;
              $target = $(target);
              // If the media frame already exists, reopen it.
              if ( frame ) {
                frame.open();
                return;
              }
              
              // Create a new media frame
              frame = wp.media({
                title: 'Select or Upload Media',
                button: {
                  text: 'Choose'
                },
                multiple: false  // Set to true to allow multiple files to be selected
              });

              frame.on( 'select', function() {
                console.log(target);
                var attachment = frame.state().get('selection').first();

                attachment = attachment.toJSON();
                // console.log(attachment);

                // console.log(target);

                // Send the attachment URL to our custom image input field.
                var postid = getParamFromURL('post_id', target.src);
                var key = getParamFromURL('field_key', target.src);
                var field_name = getParamFromURL('field_name', target.src);

                target.src = attachment.url;
                $target.addClass('imageChanged');
                prevent_navigation();
                activate_save_button();
                $target.data('attachmentid', attachment.id);
                $target.data('postid', postid);
                $target.data('name', field_name);
                $target.data('key', key);

                // Send the attachment id to our hidden input
                // imgIdInput.val( attachment.id );

                // Hide the add image link
                addImgLink.addClass( 'hidden' );

                // Unhide the remove image link
                delImgLink.removeClass( 'hidden' );

              });

              // Finally, open the modal on click
              frame.open();
          });
        $('#wp-admin-bar-root-default').append('<li id="wp-admin-bar-edit-live"><a class="ab-item" disabled href="javascript:void(0);">Save</a></li>');
        var elements = document.querySelectorAll('.editableHD');
        var editor = new MediumEditor(elements);

        var textInputs = $('[contenteditable]');
        textInputs.each(function(){
            var $this = $(this);

        })
        $('a').on('click', function(e){
            console.log(e);
            debugger;
            e.preventDefault();
        });
        textInputs.each(function() {
            var $this = $(this);
            var contents = $this.html();
            $this.parents('a').on('click', function(e){
                console.log(e);
                debugger;
                e.preventDefault();
            });
            $this.on('click', function(event){
                event.stopPropagation();
            });
            $this.on('focus', function() {}).on('blur', function() {
                var $el = $(this);
                if (contents != $el.html()) {
                    $el.addClass('textChanged');
                    prevent_navigation();
                    activate_save_button();
                    contents = $el.html();
                }
            });
        });
        
        $('#wp-admin-bar-root-default').on('click', '#wp-admin-bar-edit-live a', function() {
            // console.log('a');
            var editableText = $('[contenteditable].textChanged');
            var textString = [];
            editableText.each(function() {
                var text = $(this).html();
                var key = $(this).data('key');
                var name = $(this).data('name');
                var postid = $(this).data('postid');
                var textArr = [key, text, name, postid];
                // console.log(textArr);
                textString.push(textArr);
            });
            var alteredImage = $('img.imageChanged');
            alteredImage.each(function(){
                var text = $(this).data('attachmentid');
                var key = $(this).data('key');
                var name = $(this).data('name');
                var postid = $(this).data('postid');
                var textArr = [key, text, name, postid];
                // console.log(textArr);
                textString.push(textArr);
            });
            if(editableText.length === 0 && alteredImage.length < 1) {
                // removed @since 2.1.0
                // msg.pop('Nothing to save');
                return;
            } else {
                msg.pop('Saving your changes...');
            }
            $.ajax({
                url: meta.ajaxurl,
                data: {
                    'action': 'update_texts',
                    'siteID': meta.page.ID,
                    'textArr': textString
                },
                success: function(data) {
                    msg.pop('Changes have been saved!');
                    textString = [];
                    // now adds disabled, removes imageChanged class @since 2.1.0
                    $('#wp-admin-bar-edit-live a').text('Save');
                    $('#wp-admin-bar-edit-live a').attr('disabled','disabled');
                    $('[contenteditable].textChanged').removeClass('textChanged');
                    $('img.imageChanged').removeClass('imageChanged');
                    enable_navigation();
                },
                error: function(errorThrown) {
                    msg.pop('Something went wrong!');
                    console.error('errorThrown');
                }
            });

        });
    });
    // @since 2.1.0
    function prevent_navigation(){   
        window.onbeforeunload=(e)=>{return 1;}
    }
    // @since 2.1.0
    function enable_navigation(){
        window.onbeforeunload=(e)=>{}
    }
    // @since 2.1.0
    function activate_save_button(){
        // add warning on window/tab close and refresh
        // update admin bar text
        $('#wp-admin-bar-edit-live a').text('Save unsaved progress');
        $('#wp-admin-bar-edit-live a').removeAttr('disabled');
    }
    // Get url parameters, this was made to get acf field info from image url
    // @since 2.1.0
    function getParamFromURL(sParam, source) {
        if ( !sParam || !source )
            return;
        source = source.substring( source.indexOf('?') + 1 );
        var sURLVariables = source.split('&'),
            sParameterName,
            i;
        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');
            if (sParameterName[0] === sParam) {
                return sParameterName[1] === undefined ? true : sParameterName[1];
            }
        }
    };

})(window, window.jQuery);