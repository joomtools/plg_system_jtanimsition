<?php
/**
 * @package JT - Animsition
 * @copyright 2014 Guido De Gobbis - JoomTools
 * @license GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.joomtools.de
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');


/**
 * Class PlgSystemJTAnimsitionInstallerScript
 */
class PlgSystemJTAnimsitionInstallerScript
{
    /**
     * Called before any type of action
     *
     * @param  string  $type  type of current action
     *
     * @return  boolean  True on success
     */
    public function preflight($type)
    {
        // make version check only when installing the plugin
        if ($type != "discover_install" && $type != "install")
        {
            return true;
        }

        $version = new JVersion;

        // Abort if the current Joomla release is older
        if (version_compare($version->getShortVersion(), "3", 'lt'))
        {
            Jerror::raiseWarning(null, 'Cannot install JT - Animsition in a Joomla release prior to 3');

            return false;
        }

        return true;
    }
}