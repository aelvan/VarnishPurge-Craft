<?php

namespace Craft;

class VarnishpurgePlugin extends BasePlugin
{

    protected $_version = '0.2.2',
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

        if (craft()->varnishpurge->getSetting('purgeEnabled')) {

            $purgeRelated = craft()->varnishpurge->getSetting('purgeRelated');

            craft()->on('elements.onSaveElement', function (Event $event) { // element saved
                craft()->varnishpurge->purgeElement($event->params['element'], $purgeRelated);
            });

            craft()->on('entries.onDeleteEntry', function (Event $event) { //entry deleted
                craft()->varnishpurge->purgeElement($event->params['entry'], $purgeRelated);
            });

    		craft()->on('elements.onBeforePerformAction', function(Event $event) { //entry deleted via element action
    			$action = $event->params['action']->classHandle;
    		    if ($action == 'Delete') {
        		    $elements = $event->params['criteria']->find();
    		        foreach ($elements as $element) {
    		            if ($element->elementType !== 'Entry') { return; }
    					craft()->varnishpurge->purgeElement($element, $purgeRelated);
    		        }
    		    }
    		});
        }
    }

    public function addEntryActions($source)
    {
		return $this->purgeElement($source);
    }

    public function addCategoryActions()
    {
		return $this->purgeElement($source);
    }

	public function addAssetActions($source)
	{
		return $this->purgeElement($source);
	}

	private function purgeElement($source)
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
