<?php
namespace Craft;

class VarnishpurgeService extends BaseApplicationComponent
{
    var $settings = array();

    /**
     * Purge a single element. Just a wrapper for purgeElements().
     *
     * @param  mixed $event
     */
    public function purgeElement($element, $purgeRelated = true)
    {
        $this->purgeElements(array($element), $purgeRelated);
    }    
    
    /**
     * Purge an array of elements
     *
     * @param  mixed $event
     */
    public function purgeElements($elements, $purgeRelated = true)
    {
        if (count($elements)>0) {

            // Assume that we only want to purge elements in one locale.
            // May not be the case if other thirdparty plugins sends elements. 
            $locale = $elements[0]->locale;
            
            $uris = array();
            
            foreach ($elements as $element) {
                $uris = array_merge($uris, $this->_getElementUris($element, $locale, $purgeRelated));
            }
            
            $urls = $this->_generateUrls($uris, $locale);
            $urls = array_merge($urls, $this->_getMappedUrls($urls));
            
            if (count($urls) > 0) {
                $this->_makeTask('Varnishpurge_Purge', $urls, $locale);
            }
            
        }
    }

    /**
     * Get URIs to purge from $element in $locale.
     *
     * Adds the URI of the $element, and all related elements
     *
     * @param $element
     * @param $locale
     * @return array
     */
    private function _getElementUris($element, $locale, $getRelated = true)
    {
        $uris = array();

        // Get elements own uri
        if ($element->uri != '') {
            $uris[] = $element->uri;
        }

        // If this is a matrix block, get the uri of matrix block owner
        if ($element->getElementType() == ElementType::MatrixBlock) {
            if ($element->owner->uri != '') {
                $uris[] = $element->owner->uri;
            }
        }

        // Get related elements and their uris
        if ($getRelated) {
            if ($element->getElementType() == ElementType::Entry) {

                // get directly related entries
                $relatedEntries = $this->_getRelatedElementsOfType($element, $locale, ElementType::Entry);
                foreach ($relatedEntries as $related) {
                    if ($related->uri != '') {
                        $uris[] = $related->uri;
                    }
                }
                unset($relatedEntries);

                // get directly related categories
                $relatedCategories = $this->_getRelatedElementsOfType($element, $locale, ElementType::Category);
                foreach ($relatedCategories as $related) {
                    if ($related->uri != '') {
                        $uris[] = $related->uri;
                    }
                }
                unset($relatedCategories);

                // get directly related matrix block and its owners uri
                $relatedMatrixes = $this->_getRelatedElementsOfType($element, $locale, ElementType::MatrixBlock);
                foreach ($relatedMatrixes as $relatedMatrixBlock) {
                    if ($relatedMatrixBlock->owner->uri != '') {
                        $uris[] = $relatedMatrixBlock->owner->uri;
                    }
                }
                unset($relatedMatrixes);

            }

            if ($element->getElementType() == ElementType::Category) {

                // get directly related entries
                $relatedEntries = $this->_getRelatedElementsOfType($element, $locale, ElementType::Entry);
                foreach ($relatedEntries as $related) {
                    if ($related->uri != '') {
                        $uris[] = $related->uri;
                    }
                }
                unset($relatedEntries);

                // get directly related matrix block and its owners uri
                $relatedMatrixes = $this->_getRelatedElementsOfType($element, $locale, ElementType::MatrixBlock);
                foreach ($relatedMatrixes as $relatedMatrixBlock) {
                    if ($relatedMatrixBlock->owner->uri != '') {
                        $uris[] = $relatedMatrixBlock->owner->uri;
                    }
                }
                unset($relatedMatrixes);

            }

            if ($element->getElementType() == ElementType::MatrixBlock) {

                // get directly related entries
                $relatedEntries = $this->_getRelatedElementsOfType($element, $locale, ElementType::Entry);
                foreach ($relatedEntries as $related) {
                    if ($related->uri != '') {
                        $uris[] = $related->uri;
                    }
                }
                unset($relatedEntries);

                // get directly related categories
                $relatedCategories = $this->_getRelatedElementsOfType($element, $locale, ElementType::Category);
                foreach ($relatedCategories as $related) {
                    if ($related->uri != '') {
                        $uris[] = $related->uri;
                    }
                }
                unset($relatedCategories);

            }

            if ($element->getElementType() == ElementType::Asset) {

                // get directly related entries
                $relatedEntries = $this->_getRelatedElementsOfType($element, $locale, ElementType::Entry);
                foreach ($relatedEntries as $related) {
                    if ($related->uri != '') {
                        $uris[] = $related->uri;
                    }
                }
                unset($relatedEntries);

                // get directly related categories
                $relatedCategories = $this->_getRelatedElementsOfType($element, $locale, ElementType::Category);
                foreach ($relatedCategories as $related) {
                    if ($related->uri != '') {
                        $uris[] = $related->uri;
                    }
                }
                unset($relatedCategories);

                // get directly related matrix block and its owners uri
                $relatedMatrixes = $this->_getRelatedElementsOfType($element, $locale, ElementType::MatrixBlock);
                foreach ($relatedMatrixes as $relatedMatrixBlock) {
                    if ($relatedMatrixBlock->owner->uri != '') {
                        $uris[] = $relatedMatrixBlock->owner->uri;
                    }
                }
                unset($relatedMatrixes);

            }
        }
        
        return array_unique($uris);
    }


    /**
     * Gets elements of type $elementType related to $element in $locale
     *
     * @param $element
     * @param $locale
     * @param $elementType
     * @return mixed
     */
    private function _getRelatedElementsOfType($element, $locale, $elementType)
    {
        $criteria = craft()->elements->getCriteria($elementType);
        $criteria->relatedTo = $element;
        $criteria->locale = $locale;
        return $criteria->find();
    }

    /**
     * 
     * 
     * @param $uris
     * @param $locale
     * @return array
     */
    private function _generateUrls ($uris, $locale) 
    {
        $urls = array();
        $varnishUrlSetting = craft()->varnishpurge->getSetting('varnishUrl');
        
        if (is_array($varnishUrlSetting)) {
            $varnishUrl = $varnishUrlSetting[$locale];
        } else {
            $varnishUrl = $varnishUrlSetting;
        }
        
        if (!$varnishUrl) {
            VarnishpurgePlugin::log('Varnish URL could not be found', LogLevel::Error);
            return $urls;
        }
        
        foreach ($uris as $uri) {
            $path = $uri == '__home__' ? '' : $uri;
            $url = rtrim($varnishUrl, '/').'/'.trim($path, '/');

            if ($path && craft()->config->get('addTrailingSlashesToUrls'))
            {
                $url .= '/';
            }

            array_push($urls, $url);
        }

        return $urls;
    }

    /**
     * 
     * 
     * @param $uris
     * @return array
     */
    private function _getMappedUrls($urls) {
        $mappedUrls = array();
        $map = $this->getSetting('varnishPurgeUrlMap');
        
        if (is_array($map)) {
            foreach ($urls as $url) {
                if (isset($map[$url])) {
                    $mappedVal = $map[$url];

                    if (is_array($mappedVal)) {
                        $mappedUrls = array_merge($mappedUrls, $mappedVal);
                    } else {
                        array_push($mappedUrls, $mappedVal);
                    }
                }
            }
        }
        
        return $mappedUrls;
    }
    
    /**
     * Create task for purging urls
     *
     * @param $taskName
     * @param $uris
     * @param $locale
     */

    private function _makeTask($taskName, $urls, $locale)
    {
        $urls = array_unique($urls);
        
        VarnishpurgePlugin::log('Creating task (' . $taskName . ', ' . implode(',', $urls) . ', ' . $locale . ')', LogLevel::Info, craft()->varnishpurge->getSetting('varnishLogAll'));

        // If there are any pending tasks, just append the paths to it
        $task = craft()->tasks->getNextPendingTask($taskName);

        if ($task && is_array($task->settings)) {
            $settings = $task->settings;

            if (!is_array($settings['urls'])) {
                $settings['urls'] = array($settings['urls']);
            }

            if (is_array($urls)) {
                $settings['urls'] = array_merge($settings['urls'], $urls);
            } else {
                $settings['urls'][] = $urls;
            }

            // Make sure there aren't any duplicate paths
            $settings['urls'] = array_unique($settings['urls']);

            // Set the new settings and save the task
            $task->settings = $settings;
            craft()->tasks->saveTask($task, false);
        } else {
            craft()->tasks->createTask($taskName, null, array(
              'urls' => $urls,
              'locale' => $locale
            ));
        }

    }
    
    
    /**
     * Gets a plugin setting
     *
     * @param $name String Setting name
     * @return mixed Setting value
     * @author André Elvan
     */
    public function getSetting($name)
    {
        $this->settings = $this->_initSettings();
        return $this->settings[$name];
    }


    /**
     * Gets settings from config
     *
     * @return array Array containing all settings
     * @author André Elvan
     */
    private function _initSettings()
    {
        $settings = array();
        $settings['varnishPurgeEnabled'] = craft()->config->get('varnishPurgeEnabled');
        $settings['varnishPurgeRelated'] = craft()->config->get('varnishPurgeRelated');
        $settings['varnishUrl'] = craft()->config->get('varnishUrl');
        $settings['varnishLogAll'] = craft()->config->get('varnishLogAll');
        $settings['varnishPurgeUrlMap'] = craft()->config->get('varnishPurgeUrlMap');

        return $settings;
    }


}
