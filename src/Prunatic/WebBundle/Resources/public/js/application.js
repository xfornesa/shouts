$(document).ready( function () {
    /*
     @link http://twitter.github.io/bootstrap/javascript.html#tooltips
     To use popover and tooltip you'll need to initialize them
     If you need only tooltips you can remove the popover initialization
     and also remove the bootstrap-popover.js include.
     */
    // popover demo
    $("a[data-toggle=tooltip]")
        .tooltip()
        .click(function(e) {
            e.preventDefault();
        });

    // tooltip initialization
    $("a[data-toggle=popover]")
        .popover()
        .click(function(e) {
            e.preventDefault();
        });
});