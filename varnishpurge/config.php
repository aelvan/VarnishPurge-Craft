<?php
namespace Craft;

return array(
    'varnishUrl' => craft()->getSiteUrl(),
    'purgeEnabled' => false,
    'purgeRelated' => true,
    'logAll' => 0,
    'purgeUrlMap' => [],
);
