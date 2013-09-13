// vim: et ts=2 sw=2
// show a message requesting to select a skin
if (window.rcmail) {
  rcmail.addEventListener('init', function(evt) {
    rcmail.addEventListener('plugin.log', function(response) { console.log(response); });

    var modal = $('<div>').attr('id', 'annouce_migrations_modal');//.addClass('tablink');
    var open = false;
    
    function choose(option) {
      return function() {
        rcmail.http_post('plugin.announce_migrations_save', {
          option: option
        });
      }
    }

    rcmail.addEventListener('plugin.announce_migrations_reload', function(response) {
      // check if it worked, reload browser if so
      if (response.ok) location.reload();
      // close window if not?
      //else modal.dialog('close');
    });

    rcmail.addEventListener('plugin.announce_migrations_show', function(response) {
      if (!open) {
        modal.html(response.message);

        var buttons = [];
        if (response.migrate_skin) {
          buttons.push({
            text: rcmail.gettext('announce_cancel', 'announce_migrations'),
            addClass: 'btn-cancel',
            click: choose('cancel')
          });
          buttons.push({
            text: rcmail.gettext('announce_continue', 'announce_migrations'),
            click: choose('continue'),
            addClass: 'btn-continue',
            
          });
        } else {
          buttons.push({
            text: rcmail.gettext('announce_ok', 'announce_migrations'),
            click: choose('ok'),
            addClass: 'btn-ok',
          });
        }

        modal.dialog({
          dialogClass: "no-close buttons-right",
          resizable: false,
          draggable: false,
          autoOpen:true,
          modal: true,
          show: {
            effect: 'fade',
            duration: 800,
            easing: 'easeInCirc'
          },
          width: 400,
          //height: 300,
          title: response.title,
          buttons: buttons,
        });

        $('.ui-dialog :button').blur();
        $('.ui-dialog .btn-ok, .ui-dialog .btn-continue').focus();
        open = true;
      }
    });

    rcmail.add_element(modal, 'body');
  })
}
