<?php
/**
 * @version   1.0.6
 * @package   JT - Animsition
 * @copyright 2014 Guido De Gobbis - JoomTools
 * @license   GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link      http://www.joomtools.de
 */

defined('_JEXEC') or die;

class plgSystemJTAnimsition extends JPlugin
{
    protected $FB = false;
    protected $paramsSet = array();

    public function __construct(& $subject, $config)
    {
        if (version_compare(JVERSION, '3', 'ge'))
        {
            // Class overwrites
            JLoader::register('JDocumentHTML', JPATH_ROOT . '/media/jtlibraries/html.php');
        }
        parent::__construct($subject, $config);
        $this->loadLanguage();
    }

    public function onAfterInitialise()
    {
        if (defined('JFIREPHP') && $this->params->get('debug', 0))
        {
            $this->FB = FirePHP::getInstance(true);
        }
        else
        {
            $this->FB = false;
        }

        $FB           = $this->FB;
        $globalParams = $itemParams = array();

        if ($FB) $FB->group('JT - Animsition -> ' . __FUNCTION__);
        if ($FB) $FB->info($this->params, '$this->params');

        $globalParams['in']['active']  = $this->params->get('anim_active_in');
        $globalParams['in']['type']    = $this->params->get('anim_type_in');
        $globalParams['in']['weight']  = $this->params->get('anim_weight_in');
        $globalParams['in']['dur']     = $this->params->get('anim_dur_in');
        $globalParams['out']['active'] = $this->params->get('anim_active_out');
        $globalParams['out']['type']   = $this->params->get('anim_type_out');
        $globalParams['out']['weight'] = $this->params->get('anim_weight_out');
        $globalParams['out']['dur']    = $this->params->get('anim_dur_out');
        if ($FB) $FB->info($globalParams, '$globalParams');

        $menuItems = JFactory::getApplication()->getMenu()->getItems('access', '1');
        if ($FB)$FB->info($menuItems, '$menuItems');

        $this->paramsSet['container'] = $this->params->get('anim_container', 1);
        $this->paramsSet['positions'] = null;

        if ($this->params->get('anim_container', 1) == 2)
        {
            $animPositions                = (array) json_decode($this->params->get('anim_pos_repeatable', ''));
            $this->paramsSet['positions'] = ($animPositions && !empty($animPositions['anim_position'])) ? (array) $animPositions['anim_position'] : null;
        }

        if ($FB) $FB->info($this->paramsSet['positions'], '$this->paramsSet[positions]');

        foreach ($menuItems as &$item)
        {
            if ($FB) $FB->group('$item[' . $item->id . ']');
            if ($FB) $FB->info($item, '$item');

            $itemParams['in']['active']  = $item->params->get('menu_anim_active_in', '-1');
            $itemParams['in']['type']    = $item->params->get('menu_anim_type_in', '-1');
            $itemParams['in']['weight']  = $item->params->get('menu_anim_weight_in', '-1');
            $itemParams['in']['dur']     = $item->params->get('menu_anim_dur_in', '-1');
            $itemParams['out']['active'] = $item->params->get('menu_anim_active_out', '-1');
            $itemParams['out']['type']   = $item->params->get('menu_anim_type_out', '-1');
            $itemParams['out']['weight'] = $item->params->get('menu_anim_weight_out', '-1');
            $itemParams['out']['dur']    = $item->params->get('menu_anim_dur_out', '-1');
            if ($FB) $FB->info($itemParams, '$itemParams');

            $setParams = $this->_setParams($globalParams, $itemParams);

            if ($setParams['in']['active'] || $setParams['out']['active'])
            {
                if ($FB) $FB->info('item[' . $item->id . ']->active');

                $setAnimsition  = $this->_setAnimsition($setParams);
                $activLinkClass = $item->params->get('menu-anchor_css', '');
                $activLinkClass = $activLinkClass ? $activLinkClass . ' animsition-link' : 'animsition-link';

                $item->params->set('menu-anchor_css', $activLinkClass);

                if ($FB) $FB->info('item[' . $item->id . ']->class->set->ok');

                $this->paramsSet['item[' . $item->id . ']'] = $setAnimsition;
            }

            if ($FB) $FB->groupEnd();
       }

        if ($FB) $FB->groupEnd();
    }

    protected function _setParams($global, $menu)
    {
        $FB     = $this->FB;
        $return = array();

        if ($FB) $FB->group('-> ' . __FUNCTION__);

        if (!is_array($global) || !is_array($menu))
        {
            if ($FB) $FB->error('$global or $menu are not an array!');
            if ($FB) $FB->groupEnd();

            return null;
        }

        foreach ($menu as $arrayKey => $arrayValue)
        {
            foreach ($arrayValue as $key => $value)
            {
                switch ($value)
                {
                    case '-1':
                        $return[$arrayKey][$key] = ($global[$arrayKey][$key] == '0' && $key == 'weight')
                            ? '' : $global[$arrayKey][$key];
                        break;

                    default:
                        $return[$arrayKey][$key] = $value;
                        break;
                }
            }
        }

        if ($FB) $FB->info($return, 'Nach Fertigstellung');
        if ($FB) $FB->groupEnd();

        return $return;
    }

    protected function _setAnimsition($menu)
    {
        $FB = $this->FB;

        if ($FB) $FB->group('-> ' . __FUNCTION__);

        if (!is_array($menu))
        {
            if ($FB) $FB->error('$menu is not an array!');
            if ($FB) $FB->groupEnd();

            return null;
        }

        $return = $menu;
        if ($FB) $FB->info($return, 'Vor der Bearbeitung');

        foreach ($return as $arrayKey => $arrayValue)
        {
            if ($FB) $FB->info($arrayKey, '$arrayKey');
            if ($FB) $FB->info($arrayValue, '$arrayValue');

            if (!$arrayValue['active'])
            {
                unset($return[$arrayKey]);
                continue;
            }

            if (strpos($arrayValue['type'], '-') !== false)
            {
                if (substr($arrayValue['type'], 0, 7) == 'overlay')
                {
                    if ($FB) $FB->info('Type ist Overlay');

                    $return[$arrayKey]['type'] = str_replace('-', '-slide-' . $arrayKey . '-', $arrayValue['type']);
                    $arrayValue['weight']      = '';
                }
                else
                {
                    $return[$arrayKey]['type'] = str_replace('-', '-' . $arrayKey . '-', $arrayValue['type']);
                }
            }
            elseif (strpos($arrayValue['type'], '-') === false)
            {
                $return[$arrayKey]['type'] = $arrayValue['type'] . '-' . $arrayKey;
            }

            switch ($arrayValue['type'])
            {
                case 'flip-x':
                case 'flip-y':
                    if ($arrayValue['weight'] != '')
                    {
                        $arrayValue['weight'] = ($arrayValue['weight'] == 'sm') ? 'fr' : 'nr';
                    }

                case 'fade':
                    break;

                default:
                    $return[$arrayKey]['type'] = $arrayValue['weight'] != ''
                        ? $return[$arrayKey]['type'] . '-' . $arrayValue['weight']
                        : $return[$arrayKey]['type'];
                    break;
            }

            unset($return[$arrayKey]['weight']);
        }

        if ($FB) $FB->info($return, 'Nach Fertigstellung');
        if ($FB) $FB->groupEnd();

        return $return;
    }

    public function onContentPrepareForm($form, $data)
    {
        if ($form->getName() != 'com_menus.item') return;

        $FB = $this->FB;

        if ($FB) $FB->group('JT - Animsition -> ' . __FUNCTION__);

        $skipMenus = array('alias', 'heading', 'url', 'separator');

        if ($FB) $FB->info($form, '$form');
        if ($FB) $FB->info($data, '$data');

        if (is_array($data))
        {
            if ($FB) $FB->info($data['type'], '$data[type]');

            $skipMenu = in_array($data['type'], $skipMenus);
            if ($FB) $FB->info($skipMenu, '$skipMenu');
        }
        else
        {
            if ($FB) $FB->info($data, '$data');
        }

        if ($FB) $FB->info($form->getName(), '$form->getName()');

        if ($form->getName() == 'com_menus.item' && !$skipMenu)
        {
            JForm::addFormPath(__DIR__ . '/params');
            $form->loadFile('jtanimsition', true);
        }

        if ($FB) $FB->groupEnd();
    }

    public function onBeforeRender()
    {
        if (!JFactory::getApplication()->isSite()) return;

        $activeLinkId = 'item[' . JFactory::getApplication()->getMenu()->getActive()->id . ']';
        $paramsSet    = $this->paramsSet[$activeLinkId];

        if (empty($paramsSet) || !is_array($paramsSet)) return;

        $FB = $this->FB;

        if ($FB) $FB->group('JT - Animsition -> ' . __FUNCTION__);
        if ($FB) $FB->info($activeLinkId, '$activeLinkId');

        $document = JFactory::getDocument();
        if ($FB) $FB->info($document, '$document');

        $animAttributes = $this->_getAnimAttributes();
        $openAnimTag    = '<div' . $animAttributes . '>';
        $closeAnimTag   = '</div>';
        $liveSite       = rtrim(JURI::base(true), '/');
        $plgName        = $this->get('_name');
        $plgType        = $this->get('_type');
        $pathToLibs     = $liveSite . '/plugins/' . $plgType . '/' . $plgName . '/assets';
        if ($FB) $FB->info($pathToLibs, '$pathToLibs');

        if (version_compare(JVERSION, '3.0', 'ge'))
        {
            $template = $document->getTemplateBuffer();
        }
        else
        {
            $template = $document->get('_template');
        }

        $positions = null;

        if (preg_match_all('#<jdoc:include\stype="modules"\sname="([^"]+)"(.*)?\s?\/>#iU', $template, $matches) && $this->paramsSet['positions'])
        {
            if ($FB) $FB->info($matches, '$matches');

            foreach ($matches[1] as $key => $match)
            {
                if (in_array($match, $this->paramsSet['positions']))
                {
                    $positions[] = $matches[0][$key];
                }
            }
        }

        if ($this->paramsSet['container'] == 1)
        {
            preg_match_all('#(<\s*body[^>]*>)(.*?)(<\s*/\s*body>)#siU', $template, $_template, PREG_SET_ORDER);
            if ($FB) $FB->warn($_template, '$_template');

            $buffer = $_template[0][1] . $openAnimTag . $_template[0][2] . $closeAnimTag . $_template[0][3];
            if ($FB) $FB->warn($buffer, '$buffer');

            $template = preg_replace('#(<\s*body[^>]*>)(.*?)(<\s*/\s*body>)#siU', $buffer, $template);

            if (version_compare(JVERSION, '3.0', 'ge'))
            {
                $document->setTemplateBuffer($template);
            }
            else
            {
                $document->set('_template', $template);
            }
        }
        else
        {
            if (!empty($positions))
            {
                if ($FB) $FB->info($positions, '$positions');

                foreach ($positions as $match)
                {
                    $search   = '#' . $match . '#siU';
                    $replace  = $openAnimTag . $match . $closeAnimTag;
                    $template = preg_replace($search, $replace, $template);
                    if ($FB) $FB->info($template, '$template');

                    if (version_compare(JVERSION, '3.0', 'ge'))
                    {
                        $document->setTemplateBuffer($template);
                    }
                    else
                    {
                        $document->set('_template', $template);
                    }
                }
            }

            $buffer = $openAnimTag . $document->getBuffer('component') . $closeAnimTag;
            $document->setBuffer($buffer, 'component');
        }

        JHtml::_('jquery.framework');
        $document->addScript($pathToLibs . '/jquery.animsition.min.js');
        $script = '
		jQuery(document).ready(function($){
		    var cssLink = $(\'<link rel="stylesheet" type="text/css" href="' . $pathToLibs . '/animsition.min.css">\');
		    $("head").append(cssLink);
		    $(".animsition").animsition();
        });';
        $document->addScriptDeclaration($script);

        if ($FB) $FB->groupEnd();
    }

    protected function _getAnimAttributes()
    {
        $activeLinkId = 'item[' . JFactory::getApplication()->getMenu()->getActive()->id . ']';
        $paramsSet    = $this->paramsSet[$activeLinkId];

        if (!JFactory::getApplication()->isSite() || empty($paramsSet) || !is_array($paramsSet)) return;

        $FB = $this->FB;

        if ($FB) $FB->group('JT - Animsition -> ' . __FUNCTION__);
        if ($FB) $FB->info($activeLinkId, '$activeLinkId');
        if ($FB) $FB->info($paramsSet, '$paramsSet');

        $dataAnimsitionIn  = '';
        $dataAnimsitionOut = '';
        $animOverlay       = '';

//        if ($FB) $FB->info($this, '$this');

        if (isset($paramsSet['in']))
        {
            $dataAnimsitionIn = ' data-animsition-in="' . $paramsSet['in']['type'] . '" data-animsition-in-duration="' . $paramsSet['in']['dur'] . '"';

            $animOverlay = substr($paramsSet['in']['type'], 0, 7) == 'overlay' ? ' data-animsition-overlay="true"' : '';
        }
        elseif (isset($paramsSet['out']))
        {
            $dataAnimsitionOut = ' data-animsition-out="' . $paramsSet['out']['type'] . '" data-animsition-out-duration="' . $paramsSet['out']['dur'] . '"';
            $animOverlay       = substr($paramsSet['out']['type'], 0, 7) == 'overlay' ? ' data-animsition-overlay="true"' : '';
        }

        $animAttributes = ' class="animsition"' . $animOverlay . $dataAnimsitionIn . $dataAnimsitionOut;

        if ($FB) $FB->groupEnd();

        return $animAttributes;
    }
}
