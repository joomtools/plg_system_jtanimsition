<?php
/**
 * Using blivesta/animsition libraries for pageload animation on Joomla! 3.
 *
 * @package     Joomla.Plugin
 * @subpackage  System.Jtanimsition
 *
 * @author      Guido De Gobbis <support@joomtools.de>
 * @copyright   Copyright 2020 JoomTools.de - All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

FormHelper::loadFieldClass('text');

/**
 * Form Field class for the Joomla! CMS.
 *
 * @since  1.6
 */
class JFormFieldRepeatableModulePosition extends JFormFieldText
{
	/**
	 * The form field type.
	 *
	 * @var   string
	 *
	 * @since  3.0.1
	 */
	protected $type = 'Repeatablemoduleposition';

	/**
	 * The client ID.
	 *
	 * @var   integer
	 *
	 * @since  3.0.1
	 */
	protected $clientId;

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.0.1
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'clientId':
				return $this->clientId;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to set the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   3.0.1
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'clientId':
				$this->clientId = (string) $value;
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.0.1
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$result = parent::setup($element, $value, $group);

		if ($result === true)
		{
			// Get the client id.
			$clientId = $this->element['client_id'];

			if (!isset($clientId))
			{
				$clientName = $this->element['client'];

				if (isset($clientName))
				{
					$client = ApplicationHelper::getClientInfo($clientName, true);
					$clientId = $client->id;
				}
			}

			if (!isset($clientId) && $this->form instanceof Form)
			{
				$clientId = $this->form->getValue('client_id');
			}

			$this->clientId = (int) $clientId;
		}

		return $result;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string	The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		// Load the modal behavior script.
		HTMLHelper::_('behavior.modal', 'a.modal');
		HTMLHelper::_('script', 'plg_system_jtanimsition/jquery.jtanimsition.min.js', array('version' => 'auto', 'relative' => true));

		// Build the script.
		$script1 = array();
		$script1[] = '		function jtanimsitionModulePosition_' . $this->id . '(name) {';
		$script1[] = '			document.getElementById("' . $this->id . '").value = name;';
		$script1[] = '			jModalClose();';
		$script1[] = '		}';

		// Add the script to the document head.
		if (strpos($this->id, 'X__') === false)
		{
			Factory::getDocument()->addScriptDeclaration(implode("\n", $script1));
		}

		// Setup variables for display.
		$html = array();
		$link = 'index.php?option=com_modules&view=positions&layout=modal&tmpl=component&function=jtanimsitionModulePosition_' . $this->id
			. '&client_id=' . $this->clientId;

		// The current user display field.
		$html[] = '<div class="input-append">';
		$html[] = parent::getInput()
			. '<a class="btn modal repeatable-positions" title="' . Text::_('COM_MODULES_CHANGE_POSITION_TITLE') . '"  href="' . $link
			. '" rel="{handler: \'iframe\', size: {x: 800, y: 450}}">'
			. Text::_('COM_MODULES_CHANGE_POSITION_BUTTON') . '</a>';
		$html[] = '</div>';

		return implode("\n", $html);
	}
}
