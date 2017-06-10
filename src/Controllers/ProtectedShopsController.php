<?php

namespace ProtectedShops\Controllers;

use Plenty\Plugin\Application;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Templates\Twig;
use Plenty\Plugin\ConfigRepository;
use Plenty\Modules\Frontend\LegalInformation\Contracts\LegalInformationRepositoryContract;
use Plenty\Modules\Cron\Services\CronContainer;
use Plenty\Modules\Authorization\Services\AuthHelper;

class ProtectedShopsController extends Controller
{
    /**
     * @var string
     */
    private $apiUrl = 'api.stage.protectedshops.de';

    /**
     * @param Twig $twig
     * @param ConfigRepository $config
     * @param LegalInformationRepositoryContract $legalinfoRepository
     * @return string
     */
    public function protectedShopsInfo(Twig $twig, ConfigRepository $config, CronContainer $cron, AuthHelper $authHelper, LegalInformationRepositoryContract $legalinfoRepository):string
    {
        try {
            $shopId = $config->get('ProtectedShopsForPlenty.shopId');
            $plentyId = $config->get('ProtectedShopsForPlenty.plentyId');
            $data['shopId'] = $shopId;
            $remoteResponse = $this->getDocument($shopId, 'agb');
            $data['doc'] = json_decode($remoteResponse);


            $authHelper->processUnguarded(
                function () use ($legalinfoRepository, $shopId, $plentyId, $data) {
                    try {
                        foreach ($data['doc']  as $key => $value) {
                            if ('content' === $key) {
                                echo "Updating ..." . $plentyId . " " . 'TermsConditions';
                                $legalinfoRepository->save(array('htmlText' => $value), $plentyId, 'de', 'TermsConditions');
                            }
                        }

                    } catch (\Exception $e) {
                        echo $e->getMessage();
                    }

                }
            );


        } catch (\Exception $e) {
            $data['plentyId'] = $e->getMessage();
        }

        //$cron->add(CronContainer::EVERY_FIFTEEN_MINUTES, "ProtectedShops\\Cron\\ProtectedShopsCronHandler");

        return $twig->render('ProtectedShopsForPlenty::content.info', $data);
    }

    /**
     * @param $shopId
     * @param $documentType
     * @return string
     */
    public function getDocument($shopId, $documentType):string
    {
        $apiFunction = 'documents/' . $documentType . '/contentformat/html';
        $response = $this->apiRequest($shopId, $apiFunction);

        return $response;
    }

    /**
     * @param $shopId
     * @param $apiFunction
     * @return mixed
     */
    private function apiRequest($shopId, $apiFunction):string
    {
        $dsUrl = "https://$this->apiUrl/v2.0/de/partners/protectedshops/shops/$shopId/$apiFunction/format/json";

        // Open connection
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $dsUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        //close connection
        curl_close($ch);

        return $response;
    }
}