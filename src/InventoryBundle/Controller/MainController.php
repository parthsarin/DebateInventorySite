<?php

namespace InventoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


class MainController extends Controller
{
    /**
     * @Route("/")
     * @Template
     */
    public function indexAction()
    {
      $em = $this->getDoctrine()->getManager();
      $items_unparsed = $em->getRepository('InventoryBundle:Item')->findAll();

      $profit = floatval(0);
      $items = array();
      foreach ($items_unparsed as $item) {
        $profit = $profit - floatval($item->getCostAcquisition()) * floatval($item->getInitialQuantity());
        $profit = $profit + floatval($item->getPrice()) * floatval($item->getSold());

        if ($item->getPrice() != 0)
        {
          array_push($items, $item);
        }
      }
      $profit = $profit + 5.94; # adjustment
      $projected_profit = 2374.57;
      $percent_profit = intval(($profit / $projected_profit) * 100);

      return compact('items', 'profit', 'percent_profit');
    }

    /**
     * @Route("/progress")
     * @Template
     */
    public function progressAction()
    {
      $em = $this->getDoctrine()->getManager();
      $items_unparsed = $em->getRepository('InventoryBundle:Item')->findAll();

      $profit = floatval(0);
      $items = array();
      foreach ($items_unparsed as $item) {
        $profit = $profit - floatval($item->getCostAcquisition()) * floatval($item->getInitialQuantity());
        $profit = $profit + floatval($item->getPrice()) * floatval($item->getSold());

        if ($item->getPrice() != 0)
        {
          array_push($items, $item);
        }
      }
      $profit = $profit + 5.94; # adjustment
      $projected_profit = 2374.57;
      $percent_profit = intval(($profit / $projected_profit) * 100);

      return compact('items', 'profit', 'percent_profit');
    }
}
