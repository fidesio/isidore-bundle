<?php

namespace Fidesio\IsidoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Fidesio\UserBundle\Entity\Family;

class DebugController extends Controller
{
    public function indexAction()
    {
        // OTHER
        $centerService = $this->get('fidesio_ligueparis.service.center');
        $imageService = $this->get('fidesio_ligueparis.service.image');
        $center = $centerService->getCenter(2);

        // STORES
//        $userManager = $this->get('fidesio_user.user_manager');
//        $storeManager = $this->get('fidesio_isidore.store_manager');
//        $storeManager->getClient()->getAuth()->authentifyAsSystem();
//        $data = $storeManager
//                    ->setStoreName("Ligueparis::Isidore::store::grid::ggCentres")
//                    ->getRepository()
//                    ->findAll()
//                    ->getData()
//        ;

        $centreService = $this->get('fidesio_ligueparis.service.center')->getCenter(19);

        return $this->render('FidesioIsidoreBundle:Default:index.html.twig', array('data' => $centreService));
    }
}
