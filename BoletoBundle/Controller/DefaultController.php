<?php

namespace BoletoBundle\Controller;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/BoletoFacil/Webhook")
     * @Method({"POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function webhookAction(Request $request)
    {

        /** @var  $logger Logger*/
        $logger = $this->get('logger');

        $logger->info("Boleto Facil Webhook");

        //get the post data
        $post_data = $request->getContent();

        //this log an error for debugging
        $logger->info('Post_data:'. print_r($post_data, true));


        //get boleto manager
        $boletoManager = $this->get('boleto.BoletoManager');


        if($boletoManager->receiveNotification($post_data) == false)
            return new Response('', Response::HTTP_UNAUTHORIZED);
        else
            return new Response('', Response::HTTP_OK);


    }//end of function webhookAction
}
