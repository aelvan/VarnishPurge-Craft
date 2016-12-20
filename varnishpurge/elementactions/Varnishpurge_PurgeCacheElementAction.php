<?php
namespace Craft;

class Varnishpurge_PurgeCacheElementAction extends BaseElementAction
{
    public function getName()
    {
        return Craft::t('Purge cache');
    }

    public function isDestructive()
    {
        return false;
    }

    public function performAction(ElementCriteriaModel $criteria)
    {
			if (craft()->varnishpurge->getSetting('purgeEnabled')) {
				$elements = $criteria->find();
				craft()->varnishpurge->purgeElements($elements, false);
				$this->setMessage(Craft::t('Varnish cache was purged.'));
				return true;
			}
    }
}
