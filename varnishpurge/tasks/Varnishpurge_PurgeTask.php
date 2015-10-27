<?php
namespace Craft;


class Varnishpurge_PurgeTask extends BaseTask
{
    private $_urls;
    private $_locale;

    public function getDescription()
    {
        return Craft::t('Purging Varnish cache');
    }

    public function getTotalSteps()
    {
        $urls = $this->getSettings()->urls;
        $this->_locale = $this->getSettings()->locale;
        
        $this->_urls = array();
        $this->_urls = array_chunk($urls, 20);

        return count($this->_urls);
    }

    public function runStep($step)
    {
        VarnishpurgePlugin::log('Varnish purge task run step: ' . $step, LogLevel::Info, craft()->varnishpurge->getSetting('varnishLogAll'));
        
        $batch = \Guzzle\Batch\BatchBuilder::factory()
          ->transferRequests(20)
          ->bufferExceptions()
          ->build();

        $client = new \Guzzle\Http\Client();
        $client->setDefaultOption('headers/Accept', '*/*');

        foreach ($this->_urls[$step] as $url) {
            VarnishpurgePlugin::log('Adding url to purge: ' . $url, LogLevel::Info, craft()->varnishpurge->getSetting('varnishLogAll'));

            $request = $client->createRequest('PURGE', $url);
            $batch->add($request);
        }

        $requests = $batch->flush();

        foreach ($batch->getExceptions() as $e) {
            VarnishpurgePlugin::log('An exception occurred: ' . $e->getMessage(), LogLevel::Error);
        }

        $batch->clearExceptions();

        return true;
    }

    protected function defineSettings()
    {
        return array(
          'urls' => AttributeType::Mixed,
          'locale' => AttributeType::String
        );
    }

}
