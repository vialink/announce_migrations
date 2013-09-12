<?php # vim: et ts=2 sw=2

/**
 * Announce Migrations
 *
 * This plugin allows the display of a message for when you
 * want to announce a migration and make users change their
 * configured skin.
 *
 * @package     plugins
 * @uses        rcube_plugin
 * @author      Jan Segre <jan@vialink.com.br>
 * @version     v0.1 (Beta)
 */

class announce_migrations extends rcube_plugin {

  function init() {
    $rcmail = rcmail::get_instance();

    // load settings from local config.php.inc
    $this->load_config();

    // this can be modularized
    $this->new_skin    = $rcmail->config->get('announce_migrations_new_skin');
    $this->old_skin    = $rcmail->config->get('announce_migrations_old_skin');
    $this->incremental = $rcmail->config->get('announce_migrations_incremental');
    $this->min_version = $rcmail->config->get('announce_migrations_min_version');

    // do stuff
    $this->add_texts('localization/', true);
    $this->add_hook('ready', array($this, 'announce_migrations_init'));
    $this->register_action('plugin.announce_migrations_save', array($this, 'save_version'));
  }

  function announce_migrations_init($args) {
    $rcmail = rcmail::get_instance();

    $version = $rcmail->config->get('announce_version');
    if (!$this->ok_version($version)) {
      $this->include_script('show_notification.js');
      $this->include_stylesheet('style.css');

      // try to override the current skin
      //$prev_skin = $rcmail->config->get('skin');
      //$rcmail->config->set('skin', $this->new_skin);
      //$curr_skin = $rcmail->config->get('skin');
      if (method_exists($rcmail->output, 'set_skin'))
        $rcmail->output->set_skin($this->new_skin);

      // debug
      $rcmail->output->command('plugin.log', array('prev' => $prev_skin, 'curr' => $curr_skin));
    }
  }

  function ok_version($version) {
    return $this->incremental ?
           $version >= $this->min_version:
           $version == $this->min_version;
  }

  function save_version($args) {
    $rcmail = rcmail::get_instance();

    // check which skin should be used
    $option = get_input_value('option', RCUBE_INPUT_POST);
    $skin = ($option == 'continue') ? $this->new_skin : $this->old_skin;

    // update and save preferences
    $prefs = $rcmail->user->get_prefs();
    $prefs['skin'] = $skin;
    $prefs['announce_version'] = $this->min_version;
    $ok = $rcmail->user->save_prefs($prefs);
    
    // update the UI
    if (!$ok)
      $rcmail->output->command('display_message', $this->gettext('internalerror'), 'error');

    $rcmail->output->command('plugin.announce_migrations_reload', array('ok' => $ok));
  }
}
?>
