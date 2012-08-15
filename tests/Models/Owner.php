<?php

use MistyDoctrine\Model;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity
 * @Table( name="owners" )
 */
class Owner extends Model
{
	/**
	 * @Id @GeneratedValue
	 * @Column(type="integer")
	 */
	protected $id;

	/** @Column(length=100) */
	protected $name;

	/**
     * @OneToMany(targetEntity="Car", mappedBy="owner")
     */
	protected $cars;

	public function __construct()
	{
		$this->cars = new ArrayCollection();
	}
}