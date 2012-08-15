<?php

use MistyDoctrine\Model;

/**
 * @Entity
 * @Table( name="cars" )
 */
class Car extends Model
{
	/**
	 * @Id @GeneratedValue
	 * @Column(type="integer")
	 */
	protected $id;

	/** @Column(length=100) */
	protected $model;

	/** @Column(type="integer") */
	protected $speed;

	/**
     * @ManyToOne(targetEntity="Owner", inversedBy="cars")
     */
	protected $owner;
}