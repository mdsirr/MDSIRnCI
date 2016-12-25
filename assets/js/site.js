// JavaScript Document
function initMagnificPopup() {
    $('.ajax-popup-link').magnificPopup({
        type: 'ajax',
        midClick: true,
        closeOnBgClick: false,
        enableEscapeKey: false,
        removalDelay: 300,
        mainClass: 'mfp-fade'
                /*callbacks: {
                 open: function() {
                 $('.mfp-close-btn-custom').click(function(){
                 $.magnificPopup.close();
                 });
                 },
                 close: function(){
                 //$('body').css('overflow', 'hidden');
                 }
                 }*/
    });

    $('.image-popup-link').magnificPopup({
        type: 'image',
        removalDelay: 300,
        mainClass: 'mfp-fade'
    });


    $('.inline-popup-link').magnificPopup({
        type: 'inline',
        preloader: false,
        focus: '#name',
        removalDelay: 300,
        mainClass: 'mfp-fade'

                // When elemened is focused, some mobile browsers in some cases zoom in
                // It looks not nice, so we disable it:
                /*callbacks: {
                 beforeOpen: function() {
                 if($(window).width() < 700) {
                 this.st.focus = false;
                 } else {
                 this.st.focus = '#name';
                 }
                 }
                 }*/
    });

    $('.confirm-link').click(function() {
        var the_link = $(this).attr('href');
        bootbox.confirm({
            size: "small",
            message: "Are you sure ?",
            callback: function(result) {
                if(result){
                    window.location = the_link;
                }
            }
        });
        return false;
    });
}



// Auto Load 
$(function() {

    initMagnificPopup();




});
