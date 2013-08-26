jQuery(document).ready(function($) {
    try {
        $("#se_site").msDropDown();
    } catch($) {
        console.log('Error creating images dropdown: ' + $.message);
    }
});