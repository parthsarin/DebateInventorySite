<?php

namespace InventoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use InventoryBundle\Entity\Item;

class MainController extends Controller
{
    /**
     * @Route("/", name="index")
     * @Template
     */
    public function indexAction()
    {
      $em = $this->getDoctrine()->getManager();
      $items_unparsed = $em->getRepository('InventoryBundle:Item')->findAll();

      list($profit, $items, $money_made, $percent_profit) = $this->parse_items($items_unparsed);

      return compact('items', 'profit', 'percent_profit', 'money_made');
    }

    /**
     * @Route("/progress", name="progress")
     * @Template
     */
    public function progressAction()
    {
      $em = $this->getDoctrine()->getManager();
      $items_unparsed = $em->getRepository('InventoryBundle:Item')->findAll();

      list($profit, $items, $money_made, $percent_profit) = $this->parse_items($items_unparsed);

      return compact('items', 'profit', 'percent_profit', 'money_made');
    }

    /**
     * @Route("/post/{id}/{count}", name="update")
     */
    public function updateAction(Item $item, $count)
    {
      // Sanity Check
      if (is_null($item))
      {
        throw new Exception('Item not found');
      }
      if (!is_numeric($count))
      {
        throw new Exception('Sanity check on count integer failed');
      }

      // Persist
      $em = $this->getDoctrine()->getManager();
      $item->setSold($item->getSold() + $count);
      $em->flush();

      $this->addFlash(
        'notice-success',
        'Successfully updated database.'
      );

      return $this->redirect($this->generateUrl('index'));
    }

    public function parse_items($items_unparsed)
    {
      $profit = floatval(0);
      $items = array();
      $money_made = floatval(0);
      foreach ($items_unparsed as $item) {
        $profit = $profit - floatval($item->getCostAcquisition()) * floatval($item->getInitialQuantity());
        $profit = $profit + floatval($item->getPrice()) * floatval($item->getSold());

        $money_made = $money_made + floatval($item->getPrice()) * floatval($item->getSold());

        if ($item->getPrice() != 0)
        {
          array_push($items, $item);
        }
      }
      $profit = $profit + 5.94; # adjustment
      $projected_profit = 2374.57;
      $percent_profit = intval(($profit / $projected_profit) * 100);

      return array($profit, $items, $money_made, $percent_profit);
    }
}
