<?php

namespace InventoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Item
 *
 * @ORM\Table(name="item")
 * @ORM\Entity(repositoryClass="InventoryBundle\Repository\ItemRepository")
 */
class Item
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=6, scale=2)
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="cost_acquisition", type="decimal", precision=6, scale=2)
     */
    private $costAcquisition;

    /**
     * @var int
     *
     * @ORM\Column(name="initial_quantity", type="integer")
     */
    private $initialQuantity;

    /**
     * @var int
     *
     * @ORM\Column(name="sold", type="integer")
     */
    private $sold;


    /**
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    private $category;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Item
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set price
     *
     * @param string $price
     *
     * @return Item
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set price
     *
     * @param string $costAcquisition
     *
     * @return Item
     */
    public function setCostAcquisition($costAcquisition)
    {
        $this->costAcquisition = $costAcquisition;

        return $this;
    }

    /**
     * Get CostAcquisition
     *
     * @return string
     */
    public function getCostAcquisition()
    {
        return $this->costAcquisition;
    }

    /**
     * Set initialQuantity
     *
     * @param integer $initialQuantity
     *
     * @return Item
     */
    public function setInitialQuantity($initialQuantity)
    {
        $this->initialQuantity = $initialQuantity;

        return $this;
    }

    /**
     * Get initialQuantity
     *
     * @return int
     */
    public function getInitialQuantity()
    {
        return $this->initialQuantity;
    }

    /**
     * Set sold
     *
     * @param integer $sold
     *
     * @return Item
     */
    public function setSold($sold)
    {
        $this->sold = $sold;

        return $this;
    }

    /**
     * Get sold
     *
     * @return int
     */
    public function getSold()
    {
        return $this->sold;
    }
}
