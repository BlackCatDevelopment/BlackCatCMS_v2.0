(function($) {
    $('button.eraser').on('click',function(e) {
        e.preventDefault();
        //console.log('erase: '+$(this).data('widget'));
        //$('#confirm span.moreinfo').html( cattranslate('Widget name') + ': ' + $(this).data('name') );
        //$('#confirm span.moreinfo').show();

        $('#confirm span#modal_widget_name').remove();
        $('.modal-title').append(': <span id="modal_widget_name">' + $(this).data('name') + '</span>');
    });
}(jQuery));