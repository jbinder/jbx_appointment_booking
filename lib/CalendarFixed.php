<?php

class Zend_Gdata_Calendar_Fixed extends \Zend_Gdata_Calendar {
    /**
     * Overridden to fix an issue with the HTTP request/response for deleting.
     * @link http://zendframework.com/issues/browse/ZF-10194
     */
    public function prepareRequest($method,
                                   $url = null,
                                   $headers = array(),
                                   $data = null,
                                   $contentTypeOverride = null) {
        $request = parent::prepareRequest($method, $url, $headers, $data, $contentTypeOverride);

        if($request['method'] == 'DELETE') {
            // Default to any
            $request['headers']['If-Match'] = '*';

            if($data instanceof \Zend_Gdata_App_MediaSource) {
                $rawData = $data->encode();
                if(isset($rawData->etag) && $rawData->etag != '') {
                    // Set specific match
                    $request['headers']['If-Match'] = $rawData->etag;
                }
            }
        }
        return $request;
    }
}

?>