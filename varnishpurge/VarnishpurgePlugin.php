<?php

namespace Craft;

class VarnishpurgePlugin extends BasePlugin
{

    protected $_version = '0.2.1',
      $_schemaVersion = '1.0.0',
      $_name = 'Varnish Purge',
      $_url = 'https://github.com/aelvan/VarnishPurge-Craft',
      $_releaseFeedUrl = 'https://raw.githubusercontent.com/aelvan/VarnishPurge-Craft/master/releases.json',
      $_documentationUrl = 'https://github.com/aelvan/VarnishPurge-Craft/blob/master/README.md',
      $_description = 'Purge that Varnish cache!',
      $_developer = 'André Elvan',
      $_developerUrl = 'http://vaersaagod.no/',
      $_minVersion = '2.4';

    public function getName()
    {
        return Craft::t($this->_name);
    }

    public function getUrl()
    {
        return $this->_url;
    }

    public function getVersion()
    {
        return $this->_version;
    }

    public function getDeveloper()
    {
        return $this->_developer;
    }

    public function getDeveloperUrl()
    {
        return $this->_developerUrl;
    }

    public function getDescription()
    {
        return $this->_description;
    }

    public function getDocumentationUrl()
    {
        return $this->_documentationUrl;
    }

    public function getSchemaVersion()
    {
        return $this->_schemaVersion;
    }

    public function getReleaseFeedUrl()
    {
        return $this->_releaseFeedUrl;
    }

    public function getCraftRequiredVersion()
    {
        return $this->_minVersion;
    }


    public function init()
    {
        parent::init();

        if (craft()->varnishpurge->getSetting('purgeEnabled')) { // element saved
            craft()->on('elements.onSaveElement', function (Event $event) {
                craft()->varnishpurge->purgeElement($event->params['element'], craft()->varnishpurge->getSetting('purgeRelated'));
            });
        }
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
