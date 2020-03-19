<?php

namespace FF\Library\Sdk;

use FacebookAds\Api;
use Facebook\Facebook;
use Facebook\Exceptions\FacebookSDKException;

class FacebookSdk
{
    private $fb;
    public $fbAdsApi = null;

    private $appId;
    private $appSecret;
    private $defaultGraphVersion;

    public function __construct($options = array())
    {
        $this->appId = $options['appId'];
        $this->appSecret = $options['appSecret'];
        $this->defaultGraphVersion = $options['defaultGraphVersion'];
    }

    public function getFB()
    {
        if ($this->fb) {
            return $this->fb;
        }

        try {
            $facebook = new Facebook([
                'app_id' => $this->appId,
                'app_secret' => $this->appSecret,
                'default_graph_version' => $this->defaultGraphVersion,
            ]);
        } catch (FacebookSDKException $e) {
        }

        $this->fb = $facebook;

        return $facebook;
    }

    public function getFBAdsApi()
    {
        if ($this->fbAdsApi) {
            return $this->fbAdsApi;
        }

        try {
            $fbApi = Api::init($this->appId, $this->appSecret, $this->defaultGraphVersion);

            $this->fbAdsApi = $fbApi;

            return $fbApi;
        } catch (FacebookSDKException $e) {
            return null;
        }
    }

    /**
     * 获取广告报表
     */
    public function getAdReports($startData, $endData, $setting)
    {
        $fb = $this->getFB();

        $parameter = [
            'since' => $startData,
            'until' => $endData,
            'metrics' => ['FB_AD_NETWORK_IMP', 'FB_AD_NETWORK_REVENUE'],
            'breakdowns' => ['COUNTRY', 'PLACEMENT_NAME'],
            'filters' => [["field" => "platform", "operator" => "in", "values" => [$setting['platform']]]],
            'aggregation_period' => "DAY",
            'access_token' => $setting['accessToken']
        ];

        $analytics = $fb->post(
            "/{$setting['propertyId']}/adnetworkanalytics",
            $parameter
        );

        $analyticsData = $analytics->getDecodedBody();

        // $analyticsData的query_id 查询并不一定会立即产生数据
        // 有可能返回状态为 正在运行，只有状态为 已完成 才有数据
        // 这里最多尝试10次
        $data = array();
        $result = array();
        for ($i = 0; $i < 10; $i++) {
            sleep(3);
            $analyticsResult = $fb->sendRequest(
                'GET',
                "{$setting['propertyId']}/adnetworkanalytics_results?query_ids[0]=" . $analyticsData['query_id'],
                $parameter
            );
            $result = $analyticsResult->getDecodedBody();
            if ($result['data'][0]['results']) {
                $data = $result['data'][0]['results'];
                break;
            }
        }

        if (!$data) {
            Log::info([$analyticsData['query_id'], $result], 'adFacebook.log');
        }

        return $data;
    }
}
