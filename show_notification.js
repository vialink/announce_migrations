// vim: et ts=2 sw=2
// show a message requesting to select a skin
if (window.rcmail) {
  rcmail.addEventListener('init', function(evt) {
    //var tab = $('<span>').attr('id', 'settingstabpluginawaymessage').addClass('tablink');
    //var button = $('<a>').attr('href', rcmail.env.comm_path+'&_action=plugin.awaymessage').html(rcmail.gettext('awaymessage','awaymessage')).appendTo(tab);
    var modal = $('<div>').attr('id', 'annouce_migrations_modal');//.addClass('tablink');
    
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

    modal.dialog({
      dialogClass: "no-close",
      resizable: false,
      draggable: false,
      autoOpen:true,
      modal: true,
      show: {
        effect: 'fade',
        duration: 800,
        easing: 'easeInCirc'
      },
      width:500,
      height:300,
      title: rcmail.gettext('announcement', 'announce_migrations'),
      buttons: [
        {
          text: rcmail.gettext('continue', 'announce_migrations'),
          click: choose('continue')
        },
        {
          text: rcmail.gettext('cancel', 'announce_migrations'),
          click: choose('cancel')
        }
      ]
    });

    rcmail.add_element(modal, 'body');
    //rcmail.register_command('plugin.announce_migration', function(){ rcmail.goto_url('plugin.announce_migration') }, true);
    //button.bind('click', function(e){ return rcmail.command('plugin.announce_migration', this) });
    //rcmail.add_element(tab, 'tabs');
    //modal.dialog('open');
  })
}