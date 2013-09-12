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

    $migration = $rcmail->config->get('announce_migrations_migration');

    $this->migrate_skin  = array_key_exists('skin', $migration);
    if ($this->migrate_skin) {
      $this->new_skin    = $migration['skin']['new'];
      $this->old_skin    = $migration['skin']['old'];
    }
    $this->incremental   = $migration['incremental'];
    $this->version       = $migration['version'];
    $this->message_title = $migration['title'];
    $this->message_file  = $migration['message_file'];

    // register hooks/actions
    $this->add_texts('localization/', true);
    $this->add_hook('ready', array($this, 'announce_migrations_init'));
    $this->register_action('plugin.announce_migrations_save', array($this, 'save_version'));
  }

  function announce_migrations_init($args) {
    $rcmail = rcmail::get_instance();

    if (!$this->version_ok()) {
      $this->include_script('show_notification.js');
      $this->include_stylesheet('style.css');

      // process the message file and save it on a var
      ob_start(); include $this->message_file;
      $response = array(
        'message'      => ob_get_clean(),
        'title'        => $this->message_title,
        'migrate_skin' => $this->migrate_skin
      );

      $rcmail->output->command('plugin.announce_migrations_show', $response);

      if ($this->migrate_skin && method_exists($rcmail->output, 'set_skin'))
        $rcmail->output->set_skin($this->new_skin);

      // debug
      $rcmail->output->command('plugin.log', array('prev' => $prev_skin, 'curr' => $curr_skin));
    }
  }

  function version_ok() {
    $version = rcmail::get_instance()->config->get('announce_version', 0);
    return $this->incremental ?
           $version >= $this->version:
           $version == $this->version;
  }

  function save_version($args) {
    $rcmail = rcmail::get_instance();

    // check which skin should be used
    $option = get_input_value('option', RCUBE_INPUT_POST);
    $skin = ($option == 'continue') ? $this->new_skin : $this->old_skin;

    // update and save preferences
    $prefs = $rcmail->user->get_prefs();
    if ($this->migrate_skin)
      $prefs['skin'] = $skin;
    $prefs['announce_version'] = $this->version;
    $ok = $rcmail->user->save_prefs($prefs);
    
    // update the UI
    if (!$ok)
      $rcmail->output->command('display_message', $this->gettext('internalerror'), 'error');
    $rcmail->output->command('plugin.announce_migrations_reload', array('ok' => $ok));
  }
}
?>
