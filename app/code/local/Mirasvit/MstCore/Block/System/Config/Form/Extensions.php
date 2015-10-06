<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Sphinx Search Ultimate
 * @version   2.3.1
 * @revision  675
 * @copyright Copyright (C) 2014 Mirasvit (http://mirasvit.com/)
 */


class Mirasvit_MstCore_Block_System_Config_Form_Extensions extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $this->_getHeaderHtml($element);

        $html .= '<table class="form-list">';
        $html .= '<tr><th style="padding: 5px;">Extension</th><th style="padding: 5px;">Your Version</th><th style="padding: 5px;">Latest Version</th></tr>';
        foreach ($this->getExtensions() as $extension) {
            $html .= $this->_renderExtension($extension);
        }
        $html .= '</table>';

        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    protected function _renderExtension($ext)
    {
        $tds = array();
        $tds[] = '<a href="'.$ext->getUrl().'">'.$ext->getName().'</a>';
        $tds[] = $ext->getVersion();
        $tds[] = $ext->getLatest();
        $tds[] = '';

        $html = '<tr>';
        foreach ($tds as $value) {
            $html .= '<td style="padding: 5px;">'.$value.'</td>';
        }
        $html .= '</tr>';

        return $html;
    }

    protected function getExtensions()
    {
        $result     = array();
        $extensions = Mage::helper('mstcore/code')->getOurExtensions();
        $list       = Mage::getModel('mstcore/feed_extensions')->getList();

        foreach ($extensions as $extension) {
            if (!isset($list[$extension['s']])) {
                continue;
            }
            $info = $list[$extension['s']];

            $version = $extension['v'].'.'.$extension['r'];
            if ($version == '.') {
                $version = '-';
            }

            $result[$extension['s']] = new Varien_Object(array(
                'version' => $version,
                'name'    => $info['name'],
                'url'     => $info['url'],
                'latest'  => $info['version'].'.'.$info['revision'],
            ));
        }
        return $result;
    }
}
