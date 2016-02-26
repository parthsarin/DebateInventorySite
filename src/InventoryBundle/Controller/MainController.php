<?php

namespace InventoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\Request;

use InventoryBundle\Entity\Item;

class MainController extends Controller
{
    const PASSWD = 'ef2d127de37b942baad06145e54b0c619a1f22327b2ebbcfbec78f5564afe39d';

    /**
     * @Route("/", name="index")
     * @Template
     */
    public function indexAction()
    {
      $em = $this->getDoctrine()->getManager();

      $categories_unparsed = $em->getRepository('InventoryBundle:Category')->findAll();

      list($profit, $categories, $money_made, $percent_profit) = $this->parse_categories($categories_unparsed);
      $mainmenu = $this->compile_menu();
      $money = $this->get_money_in_box();

      return compact('profit', 'percent_profit', 'money_made', 'categories', 'mainmenu', 'money');
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
      $mainmenu = $this->compile_menu();
      $money = $this->get_money_in_box();

      return compact('items', 'profit', 'percent_profit', 'money_made', 'mainmenu', 'money');
    }

    /**
     * @Route("/admin", name="admin")
     * @Template
     */
    public function adminAction()
    {
      $mainmenu = $this->compile_menu();
      $money = $this->get_money_in_box();
      return compact('mainmenu', 'money');
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
        $boxMoney = $em->getRepository('InventoryBundle:Box')->findAll()[0];
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
        $item->setSold($item->getSold() + $count);
        $boxMoney->setMoney($boxMoney->getMoney() + $count * $item->getPrice());
        $em->flush();
      }

      return $this->redirect($this->generateUrl('index'));
    }

    /**
     * @Route("/login", name="login")
     * @Template
     */
    public function loginAction(Request $request)
    {
      $authenticationUtils = $this->get('security.authentication_utils');

      $error = $authenticationUtils->getLastAuthenticationError();
      $lastUsername = $authenticationUtils->getLastUsername();

      $mainmenu = $this->compile_menu();
      return compact('error', 'lastUsername', 'mainmenu');
    }

    /**
     * @Route("/update_box", name="update_box")
     * @Method("POST")
     */
    public function updateBoxAction(Request $request)
    {
      if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
      {
        $boxMoney = $request->get('boxMoney');
        $em = $this->getDoctrine()->getManager();

        $money = $em->getRepository('InventoryBundle:Box')->findAll()[0];
        $money->setMoney($boxMoney);

        $em->flush();

        $this->addFlash(
            'notice',
            'Successfully updated database'
        );

        return $this->redirect($this->generateUrl('admin'));
      } else {
        return $this->redirect($this->generateUrl('login'));
      }
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

    private function authenticate_password($test)
    {
    	for ($i = 0; $i < 500; $i++) {
    		$test = hash('sha256', $test + 'keevin');
    	}

      if ($test == self::PASSWD) {
        return True;
      } else { return False; }
    }

    private function compile_menu()
    {
      if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
      {
        return array(
          'index' => array('url' => $this->generateUrl('index'), 'title' => 'Home'),
          'progress' => array('url' => $this->generateUrl('progress'), 'title' => 'Progress'),
          'admin' => array('url' => $this->generateUrl('admin'), 'title' => 'Admin'),
          'logout' => array('url' => $this->generateUrl('logout'), 'title' => 'Logout')
        );
      } else {
        return array(
          'index' => array('url' => $this->generateUrl('index'), 'title' => 'Home'),
          'progress' => array('url' => $this->generateUrl('progress'), 'title' => 'Progress'),
          'login' => array('url' => $this->generateUrl('login'), 'title' => 'Login')
        );
      }
    }

    private function get_money_in_box()
    {
      $em = $this->getDoctrine()->getManager();
      $money = $em->getRepository('InventoryBundle:Box')->findAll()[0]->getMoney();

      return $money;
    }
}
