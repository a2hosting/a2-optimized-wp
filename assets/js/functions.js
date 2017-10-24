var url = window.location.pathname
var filename = url.substring(url.lastIndexOf('/') + 1);

jQuery(document).ready(function($) {
    if (filename == 'update-core.php' && $('form.upgrade').length > 0) {
        $('form.upgrade input#upgrade').attr('disabled', true);
        $('ul.core-updates').prepend('<div id="w3tc-warning" class="notice notice-error"><p>W3 Total Cache may cause issues during major WordPress core updates. We recommend you <a href="plugins.php">disable W3 Total Cache</a> before continuing. Please remember to re-activate it after the update.</p><p><a href="#" id="upgrade-confirm" class="button">I understand the risks, let me update</a></p></div>');
    }
    $('#upgrade-confirm').click( function(){
        $('#w3tc-warning').hide();
        $('form.upgrade input#upgrade').attr('disabled', false);
    });
});

