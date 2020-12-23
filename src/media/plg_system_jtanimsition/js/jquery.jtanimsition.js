/*!
 * @package     Joomla.Plugin
 * @subpackage  System.Jtanimsition
 *
 * @author      Guido De Gobbis <support@joomtools.de>
 * @copyright   Copyright 2020 JoomTools.de - All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

(function ($) {
	$(document).on('subform-row-add', function (event, row) {
		let linkTag = $(row).find('a.repeatable-positions'),
			jtanimsitionInputId = $(row).find('input').attr('id'),
			existingScript = document.head.getElementById(jtanimsitionInputId);
		if (!existingScript) {
			script  = document.createElement('script');
			script.text = "function jtanimsitionModulePosition_" + jtanimsitionInputId + "(name){document.getElementById('" + jtanimsitionInputId + "').value = name;jModalClose();}";
			script.id   = "script_" + jtanimsitionInputId;
			document.head.appendChild(script);
		}

		$(linkTag).attr('href', 'index.php?option=com_modules&view=positions&layout=modal&tmpl=component&function=jtanimsitionModulePosition_' + jtanimsitionInputId + '&client_id=0');
	});
})(jQuery);