<?php

namespace InventoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

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

      $categories_unparsed = $em->getRepository('InventoryBundle:Category')->findAll();

      list($profit, $categories, $money_made, $percent_profit) = $this->parse_categories($categories_unparsed);

      return compact('profit', 'percent_profit', 'money_made', 'categories');
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
     * @Route("/post", name="update")
     * @Method("POST")
     */
    public function updateAction()
    {
      // Unpack Data
      $data = $_POST['data'];
      $em = $this->getDoctrine()->getManager();

      foreach ($data as $key => $individual) {
        $itemId = $individual['id'];
        $count = $individual['count'];

        $item = $em->getRepository('InventoryBundle:Item')->findOneBy(array('id' => $itemId));
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
      }

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

    public function parse_categories($categories_unparsed)
    {
      $items = array();
      foreach ($categories_unparsed as $keyCategory => $category) {
        foreach ($category->getItems() as $keyItem => $item)
        {
          array_push($items, $item);
          if ($item->getPrice() == 0)
          {
            unset($category->getItems()[$keyItem]);
          }
        }

        if (empty($category))
        {
          unset($categories_unparsed[$keyItem]);
        }
      }

      list($profit, $items, $money_made, $percent_profit) = $this->parse_items($items);

      return array($profit, $categories_unparsed, $money_made, $percent_profit);
    }
}
