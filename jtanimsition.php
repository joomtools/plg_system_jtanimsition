<?php
/**
 * Using blivesta/animsition libraries for pageload animation on Joomla! 3.
 *
 * @package     Joomla.Plugin
 * @subpackage  System.jtanimsition
 * @author      Guido De Gobbis <guido.de.gobbis@joomtools.de>
 * @copyright   2014 JoomTools
 * @license     GNU/GPLv3 <http://www.gnu.org/licenses/gpl-3.0.de.html>
 * @link        http://joomtools.de
 */

defined('_JEXEC') or die;

/**
 * Class PlgSystemJtAnimsition
 *
 * Using blivesta/animsition libraries for pageload animation on Joomla! 3.
 *
 * @package     Joomla.Plugin
 * @subpackage  System.jtanimsition
 * @since       3
 */
class PlgSystemJtAnimsition extends JPlugin
{
	protected $pSet = array();

	protected $autoloadLanguage = true;

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe.
	 * @param   array   $config    An optional associative array of configuration settings.
	 *
	 * @since   1.5
	 */
	public function __construct(&$subject, $config)
	{
		// Class overwrites
		JLoader::register('JDocumentHTML', JPATH_ROOT . '/media/jtlibraries/html.php');

		parent::__construct($subject, $config);
	}

	/**
	 * onAfterInitialise
	 *
	 * @return  void
	 */
	public function onAfterInitialise()
	{
		if (JFactory::getApplication()->isAdmin())
		{
			return;
		}

		$this->pSet['container'] = $this->params->get('anim_container', 1);
		$this->pSet['positions'] = null;

		if ($this->params->get('anim_container', 1) == 2)
		{
			$animPos                 = (array) json_decode($this->params->get('anim_pos_repeatable', ''));
			$this->pSet['positions'] = ($animPos && !empty($animPos['anim_position']))
					? (array) $animPos['anim_position']
					: null;
		}
	}

	/**
	 * onAfterRoute
	 *
	 * @return  void
	 */
	public function onAfterRoute()
	{
		$app = JFactory::getApplication();

		if ($app->isAdmin())
		{
			return;
		}

		$menu       = $app->getMenu();
		$menuItems  = $menu->getItems('access', '1');
		$activeItem = $menu->getActive();

		$gParams['in']['active']  = $this->params->get('anim_active_in');
		$gParams['in']['type']    = $this->params->get('anim_type_in');
		$gParams['in']['weight']  = $this->params->get('anim_weight_in');
		$gParams['in']['dur']     = $this->params->get('anim_dur_in');
		$gParams['out']['active'] = $this->params->get('anim_active_out');
		$gParams['out']['type']   = $this->params->get('anim_type_out');
		$gParams['out']['weight'] = $this->params->get('anim_weight_out');
		$gParams['out']['dur']    = $this->params->get('anim_dur_out');

		$iParams['in']['active']  = $activeItem->params->get('menu_anim_active_in', '-1');
		$iParams['in']['type']    = $activeItem->params->get('menu_anim_type_in', '-1');
		$iParams['in']['weight']  = $activeItem->params->get('menu_anim_weight_in', '-1');
		$iParams['in']['dur']     = $activeItem->params->get('menu_anim_dur_in', '-1');
		$iParams['out']['active'] = $activeItem->params->get('menu_anim_active_out', '-1');
		$iParams['out']['type']   = $activeItem->params->get('menu_anim_type_out', '-1');
		$iParams['out']['weight'] = $activeItem->params->get('menu_anim_weight_out', '-1');
		$iParams['out']['dur']    = $activeItem->params->get('menu_anim_dur_out', '-1');

		$setParams     = $this->_setParams($gParams, $iParams);
		$setAnimsition = $this->_setAnimsition($setParams);

		if ($setParams['out']['active'])
		{
			foreach ($menuItems as &$item)
			{
				$activLinkClass = $item->params->get('menu-anchor_css', '');
				$activLinkClass = $activLinkClass ? $activLinkClass . ' animsition-link' : 'animsition-link';

				$item->params->set('menu-anchor_css', $activLinkClass);
			}
		}

		$this->pSet['item[' . $activeItem->id . ']'] = $setAnimsition;
	}

	/**
	 * _setParams
	 * Override global params with menu item params
	 *
	 * @param   array  $global  global params
	 * @param   array  $menu    menu item params
	 *
	 * @return array|void
	 */
	protected function _setParams($global, $menu)
	{
		if (!is_array($global) || !is_array($menu))
		{
			return null;
		}

		$return = array();

		foreach ($menu as $mKey => $mValue)
		{
			foreach ($mValue as $key => $value)
			{
				switch ($value)
				{
					case '-1':
						$return[$mKey][$key] = ($global[$mKey][$key] == '0' && $key == 'weight') ? '' : $global[$mKey][$key];
						break;

					default:
						$return[$mKey][$key] = $value;
						break;
				}
			}
		}

		return $return;
	}

	/**
	 * _setAnimsition
	 *
	 * @param   array  $params  Merged params @see _setParams()
	 *
	 * @return array|void
	 */
	protected function _setAnimsition($params)
	{
		if (!is_array($params))
		{
			return null;
		}

		foreach ($params as $pKey => $pValue)
		{
			if (!$pValue['active'])
			{
				unset($params[$pKey]);
				continue;
			}

			if (strpos($pValue['type'], '-') !== false)
			{
				if (substr($pValue['type'], 0, 7) == 'overlay')
				{
					$params[$pKey]['type'] = str_replace('-', '-slide-' . $pKey . '-', $pValue['type']);
					$pValue['weight']      = '';
				}
				else
				{
					$params[$pKey]['type'] = str_replace('-', '-' . $pKey . '-', $pValue['type']);
				}
			}
			elseif (strpos($pValue['type'], '-') === false)
			{
				$params[$pKey]['type'] = $pValue['type'] . '-' . $pKey;
			}

			switch ($pValue['type'])
			{
				case 'flip-x':
				case 'flip-y':
					if ($pValue['weight'] != '')
					{
						$pValue['weight'] = ($pValue['weight'] == 'sm') ? 'fr' : 'nr';
					}
					break;

				case 'fade':
					break;

				default:
					$params[$pKey]['type'] = $pValue['weight'] != ''
						? $params[$pKey]['type'] . '-' . $pValue['weight']
						: $params[$pKey]['type'];
					break;
			}

			unset($params[$pKey]['weight']);
		}

		return $params;
	}

	/**
	 * onContentPrepareForm
	 * Adds additional fields to the menu item form
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  void
	 */
	public function onContentPrepareForm($form, $data)
	{
		if (JFactory::getApplication()->isSite() || $form->getName() != 'com_menus.item')
		{
			return;
		}

		$skipMenu  = false;
		$skipMenus = array('alias', 'heading', 'url', 'separator');

		if (is_array($data))
		{
			$skipMenu = in_array($data['type'], $skipMenus);
		}

		if (!$skipMenu)
		{
			JForm::addFormPath(__DIR__ . '/params');
			$form->loadFile('jtanimsition', true);
		}
	}

	/**
	 * onBeforeRender
	 *
	 * @return void
	 */
	public function onBeforeRender()
	{
		$app = JFactory::getApplication();

		if ($app->isAdmin())
		{
			return;
		}

		$actLinkId = 'item[' . $app->getMenu()->getActive()->id . ']';
		$pSet      = isset($this->pSet[$actLinkId]) ? $this->pSet[$actLinkId] : null;

		if (empty($pSet) || !is_array($pSet))
		{
			return;
		}

		$document       = JFactory::getDocument();
		$animAttributes = $this->_getAnimAttributes();
		$openAnimTag    = '<div' . $animAttributes . '>';
		$closeAnimTag   = '</div>';
		$liveSite       = rtrim(JURI::base(true), '/');
		$plgName        = $this->get('_name');
		$plgType        = $this->get('_type');
		$pathToLibs     = $liveSite . '/plugins/' . $plgType . '/' . $plgName . '/assets';
		$tmplBuffer     = $document->getTemplateBuffer();
		$positions      = null;

		if ($this->pSet['positions'])
		{
			$p1 = preg_match_all('#<jdoc:include\stype="modules"\sname="([^"]+)"(.*)?\s?\/>#iU', $tmplBuffer, $matches);

			if ($p1)
			{
				foreach ($matches[1] as $key => $match)
				{
					if (in_array($match, $this->pSet['positions']))
					{
						$positions[] = $matches[0][$key];
					}
				}
			}
		}

		if ($this->pSet['container'] == 1)
		{
			preg_match('#(<body[^>]*>)(.*?)(<\/body>)#siU', $tmplBuffer, $tmpl);

			$buffer     = $tmpl[1] . $openAnimTag . $tmpl[2] . $closeAnimTag . $tmpl[3];
			$tmplBuffer = preg_replace('#(<body[^>]*>)(.*?)(</\body>)#siU', $buffer, $tmplBuffer);

			$document->setTemplateBuffer($tmplBuffer);
		}
		else
		{
			if (!empty($positions))
			{
				foreach ($positions as $match)
				{
					$search     = '#' . $match . '#siU';
					$replace    = $openAnimTag . $match . $closeAnimTag;
					$tmplBuffer = preg_replace($search, $replace, $tmplBuffer);

					$document->setTemplateBuffer($tmplBuffer);
				}
			}

			$buffer = $openAnimTag . $document->getBuffer('component') . $closeAnimTag;
			$document->setBuffer($buffer, 'component');
		}

		JHtml::_('jquery.framework');
		$document->addScript($pathToLibs . '/jquery.animsition.min.js');

		$script = '
			var cssLink = document.createElement("link");
			cssLink.rel = "stylesheet" ;
			cssLink.type = "text/css";
			cssLink.href = "' . $pathToLibs . '/animsition.min.css";
			document.getElementsByTagName("head")[0].appendChild(cssLink);
			jQuery(document).ready(function($){
				$(".animsition").animsition({
					inDuration  : 0,
					outDuration : 0
				});
			});
		';

		$document->addScriptDeclaration($script);
	}

	/**
	 * _getAnimAttributes
	 * Get attributes for animsition container
	 *
	 * @return string|void
	 */
	protected function _getAnimAttributes()
	{
		$app       = JFactory::getApplication();
		$actLinkId = 'item[' . $app->getMenu()->getActive()->id . ']';
		$pSet      = isset($this->pSet[$actLinkId]) ? $this->pSet[$actLinkId] : null;

		if ($app->isAdmin() || empty($pSet) || !is_array($pSet))
		{
			return null;
		}

		$dataAnimsitionIn  = '';
		$dataAnimsitionOut = '';
		$animOverlay       = '';

		if (isset($pSet['in']))
		{
			$dataAnimsitionIn = ' data-animsition-in="'
					. $pSet['in']['type']
					. '" data-animsition-in-duration="'
					. $pSet['in']['dur'] . '"';

			$animOverlay = substr($pSet['in']['type'], 0, 7) == 'overlay'
					? ' data-animsition-overlay="true"'
					: '';
		}

		if (isset($pSet['out']))
		{
			$dataAnimsitionOut = ' data-animsition-out="'
					. $pSet['out']['type']
					. '" data-animsition-out-duration="'
					. $pSet['out']['dur']
					. '"';

			$animOverlay = substr($pSet['out']['type'], 0, 7) == 'overlay'
					? ' data-animsition-overlay="true"'
					: '';
		}

		$animAttributes = ' class="animsition"'
				. $animOverlay
				. $dataAnimsitionIn
				. $dataAnimsitionOut;

		return $animAttributes;
	}
}
