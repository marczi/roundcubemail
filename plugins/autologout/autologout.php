<?php

/**
 * Session timeout on no human inactivity
 *
 * @version 1.0
 * @author Daniel Marczisovszky
 * @website 
 * @licence GNU GPL v3
 *
 **/

/**
 * Usage: 
 *
 **/

class autologout extends rcube_plugin
{
    public $task = '?(?!login|logout).*';

    private $rcube;

    /* Timeout in seconds */
    private $timeout = 0;

    function init()
    {
        $this->rcube = rcube::get_instance();

        $this->load_config();
        // Convert from minutes (in the config) to seconds
        $this->timeout = (int) $this->rcube->config->get('autologout_timeout') * 60;

        if ($this->timeout > 0) {
            $this->add_hook('startup', array($this, 'startup'));
        }
//        rcube::write_log('autologout', '[Autologout] init ' . $this->timeout);
    }

    function startup($args)
    {
//        rcube::write_log('autologout', 'Autologout action: ' . $args['action']);

        $a = $args['action'];
        if ($a == 'keep-alive' || $a == 'refresh') {
            $last_access = $_SESSION['last_access'];
//            rcube::write_log('autologout', '[Autologout] last-access: ' . $last_access);
//            rcube::write_log('autologout', '[Autologout] time: ' . (time() - $last_access));
//            rcube::write_log('autologout', '[Autologout] timeout: ' . $this->timeout);
            if ($last_access > 0 && time() - $last_access > $this->timeout) {
//                rcube::write_log('autologout', '[Autologout] automic logout after timeout');
                $this->rcube->logout_actions();
                $this->rcube->kill_session();
                $rcmail->output->redirect(array('_task' => 'logout'));
            }
        }
        else {
            $_SESSION['last_access'] = time();
        }
        return $args;
    }
}
?>
