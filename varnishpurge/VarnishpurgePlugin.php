<?php

namespace Craft;

class VarnishpurgePlugin extends BasePlugin
{

    public function init()
    {
        parent::init();

        if (craft()->varnishpurge->getSetting('purgeEnabled')) { // element saved
            craft()->on('elements.onSaveElement', function (Event $event) {
                craft()->varnishpurge->purgeElement($event->params['element'], craft()->varnishpurge->getSetting('varnishPurgeRelated'));
            });
        }
    }

    public function getName()
    {
        return Craft::t('Varnish Purge');
    }

    public function getVersion()
    {
        return '0.1';
    }

    public function getDeveloper()
    {
        return 'André Elvan';
    }

    public function getDeveloperUrl()
    {
        return 'http://vaersaagod.no';
    }


    public function addEntryActions()
    {
        $actions = array();

        if (craft()->varnishpurge->getSetting('purgeEnabled')) {
            $purgeAction = craft()->elements->getAction('Varnishpurge_PurgeCache');

            $purgeAction->setParams(array(
              'label' => Craft::t('Purge cache'),
            ));

            $actions[] = $purgeAction;
        }

        return $actions;
    }

    public function addCategoryActions()
    {
        $actions = array();

        if (craft()->varnishpurge->getSetting('purgeEnabled')) {
            $purgeAction = craft()->elements->getAction('Varnishpurge_PurgeCache');

            $purgeAction->setParams(array(
              'label' => Craft::t('Purge cache'),
            ));

            $actions[] = $purgeAction;
        }

        return $actions;
    }

}
