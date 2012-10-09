<?php

namespace BNS\App\CoreBundle\Role;

use Exception;

use BNS\App\CoreBundle\Access\BNSAccess;
use BNS\App\CoreBundle\Model\Group;
use BNS\App\CoreBundle\Model\User;
use BNS\App\CoreBundle\Model\GroupTypeQuery;
use BNS\App\CoreBundle\Model\GroupType;

/**
 * @author Eric Chau
 * Gestion des rôles dans BNS 3 et relation avec la centrale d'Auth
 */
class BNSRoleManager
{	
	protected $api;
	protected $domainId;
	protected $groupTypeRole;
	private static $groupTypes = array();

	public function __construct($api, $domainId)
	{
		$this->api = $api;
		$this->domain_id = $domainId;
	}
	
	/**
	 * Vérifie si on peut utiliser le groupeType en tant que rôle
	 * @param GroupType $groupTypeRole : $groupType à tester
	 * @return boolean
	 */
	public function checkGroupType(GroupType $groupTypeRole)
	{
		if ($groupTypeRole->getSimulateRole() === false && $groupTypeRole->getIsRecursive() === true) {
			throw new Exception('You can not use this groupType as a Role');
		}
		
		return true;
	}
	
	/**
	 * Set du GroupType depuis un un objet
	 */
	public function setGroupTypeRole(GroupType $groupTypeRole)
	{
		$this->checkGroupType($groupTypeRole);
		$this->groupTypeRole = $groupTypeRole;
		
		//Renvoie this pour faire des imbrications
		return $this;
	}
	
	/**
	 * Set du groupType depuis une id
	 */
	public function setGroupTypeRoleFromId($groupTypeRoleId)
	{
		foreach ($this->getGroupTypes() as $groupType) {
			if ($groupTypeRoleId == $groupType->getId()) {
				$this->setGroupTypeRole($groupType);
				
				return $this;
			}
		}
		
		throw new InvalidArgumentException('You provide an invalid group type ID ('.$groupTypeRoleId.')!');
	}
	
	/**
	 * Set du groupType depuis un type
	 */
	public function setGroupTypeRoleFromType($groupTypeRoleType)
	{	
		foreach ($this->getGroupTypes() as $groupType) {
			if ($groupTypeRoleType == $groupType->getType()) {
				$this->setGroupTypeRole($groupType);
				return $this;
			}
		}
		
		throw new InvalidArgumentException('You provide an invalid group type TYPE ('.$groupTypeRoleType.')!');
	}
	
	/**
	 * Renvoie et set le role depuis une id
	 * @param int $groupTypeRoleId
	 * @return GroupType
	 */
	public function getGroupTypeRoleFromId($groupTypeRoleId)
	{
		$this->setGroupTypeRoleFromId($groupTypeRoleId);
		
		return $this->getGroupTypeRole();
	}
	
	/**
	 * Renvoie le Role en cours
	 * @return Grouptype
	 * @throws Exception
	 */
	public function getGroupTypeRole()
	{
		if(!isset($this->groupTypeRole)) {
			throw new Exception('the role is not set');
		}
		
		return $this->groupTypeRole;
	}
	
	public function findGroupTypeRoleByType($typeUniqueName)
	{
		$groupTypeToReturn = null;
		foreach ($this->getGroupTypes() as $groupType) {
			if (0 === strcmp($typeUniqueName, $groupType->getType())) {
				$groupTypeToReturn = $groupType;
			}
		}
		
		return $groupTypeToReturn;
	}
	
	/**
	 * Assigne le role en cours à l'utilisateur dans le groupe donné
	 * @param type $groupTypeRole
	 * @param User $user
	 * @param Group $group
	 */
	public function assignRole(User $user, $groupId)
	{
		$currentGroupTypeRole = $this->getGroupTypeRole();
		if (null == $user) {
			throw new \Exception('You must provide an User Propel Object, null given!');
		}
		
		$route = array(
			'group_id'	=> $groupId,
			'role_id'	=> $currentGroupTypeRole->getId()
		);
		$values = array(
			'user_id'	=> $user->getId()
		);
		
		$response = $this->api->send('group_assign_role_user', array('values' => $values,'route' => $route));
		$this->updateUserHighestRole($user, $currentGroupTypeRole);
	}
	
	/**
	 * Permet de mettre à jour si nécessaire le champ high_role de l'utilisateur passé en paramètre
	 * 
	 * @param User $user utilisateur à mettre à jour si besoin
	 * @param GroupType $newRole le nouveau rôle sur lequel on va vérifier si oui ou non il faut faire une mise à jour
	 */
	public function updateUserHighestRole(User $user, GroupType $newRole)
	{
		if (0 == $user->getHighRoleId() || $newRole->getId() < $user->getHighRoleId()) {
			$user->setHighRoleId($newRole->getId());
			
			// Finally
			$user->save();
		}
	}
	
	/**
	 * Assigne le role en cours aux utilisateurs du groupe donné : exemple élèves de la classe dans les équipes de la classe
	 * @param type $srcGroupTypeRoleId par exemple CLASSROOM
	 * @param type $srcGroupId $classroom_id
	 * @param type $destGroupTypeRoleId TEAM
	 * @param type $destGroupId $team_id
	 */
	public function assignRoleForUsersInGroup($srcGroupTypeRoleId, $srcGroupId, $destGroupTypeRoleId, $destGroupId)
	{
		$route = array(
			'group_id'	=> $destGroupId,
			'role_id'	=> $destGroupTypeRoleId
		);
		$values = array(
			'group_id'	=> $srcGroupId,
			'role_id'	=> $srcGroupTypeRoleId
		);
		$this->api->send('group_assign_role_group', array('values' => $values,'route' => $route));
	}
	
	private static function getGroupTypes()
	{
		if (!isset(self::$groupTypes[0])) {
			self::$groupTypes = GroupTypeQuery::create()
				->joinWithI18n(BNSAccess::getRequest() != null ? BNSAccess::getLocale() : '')
			->find();
		}
		
		return self::$groupTypes;
	}
}