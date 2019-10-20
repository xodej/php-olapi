<?php

declare(strict_types=1);

namespace Xodej\Olapi;

/**
 * Class SystemDatabase.
 */
class SystemDatabase extends Database
{
    public $reservedAccounts = ['admin', 'etl', '_internal_suite'];

    /**
     * SystemDatabase constructor.
     *
     * @param Connection $conn      connection object
     * @param array      $meta_info array of database parameters
     *
     * @throws \Exception
     */
    public function __construct(Connection $conn, array $meta_info)
    {
        parent::__construct($conn, $meta_info);
        $this->reservedAccounts[] = $this->getConnection()->getConnectionUserName();
    }

    /**
     * Returns user object from database
     *
     * @param string $user_name user name
     *
     * @throws \Exception
     *
     * @return User
     */
    public function getUser(string $user_name): User
    {
        /* @noinspection PhpIncompatibleReturnTypeInspection */ // @todo inspection
        return $this->getUserDimension()
            ->getElement($user_name)
        ;
    }

    /**
     * Returns array of all user names
     *
     * @throws \Exception
     *
     * @return string[]
     */
    public function getUsers(): array
    {
        return $this->getUserDimension()
            ->getAllBaseElements()
        ;
    }

    /**
     * Creates user and adds groups to the user account
     *
     * @param string     $user_name   user name
     * @param null|array $group_names array of group names
     *
     * @throws \Exception
     *
     * @return User
     */
    public function createUser(string $user_name, ?array $group_names = null): User
    {
        if ($this->hasUser($user_name)) {
            throw new \InvalidArgumentException('given user account '.$user_name.' already exist.');
        }

        $user_dim = $this->getUserDimension();
        $user_dim->addElement($user_name);

        // hack to circumvent invalid state of current object
        // throws Exception if user not exists
        /** @var SystemDatabase $this_as_new_obj */
        $this_as_new_obj = $this->reload();
        $user = $this_as_new_obj->getUser($user_name);

        if (null !== $group_names) {
            $user->addGroups($group_names);
        }

        return $user;
    }

    /**
     * @param string $user_name
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function deleteUser(string $user_name): bool
    {
        if (!$this->hasUser($user_name)) {
            throw new \InvalidArgumentException('given user account '.$user_name.' already exist.');
        }

        return $this->getUserDimension()->deleteElementByName($user_name);
    }

    /**
     * @param string $user_name
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function hasUser(string $user_name): bool
    {
        return $this->getUserDimension()
            ->hasElementByName($user_name)
        ;
    }

    /**
     * @param string $group_name
     *
     * @throws \Exception
     *
     * @return Group
     */
    public function getGroup(string $group_name): Group
    {
        /* @noinspection PhpIncompatibleReturnTypeInspection */ // @todo inspection
        return $this->getGroupDimension()
            ->getElementByName($group_name)
        ;
    }

    /**
     * @throws \Exception
     *
     * @return string[]
     */
    public function getGroups(): array
    {
        return $this->getGroupDimension()
            ->getAllBaseElements()
        ;
    }

    /**
     * @param string $group_name
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function hasGroup(string $group_name): bool
    {
        return $this->getDimension('#_GROUP_')
            ->hasElementByName($group_name)
        ;
    }

    /**
     * @param string    $group_name
     * @param null|bool $ignore_user
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function deleteGroup(string $group_name, ?bool $ignore_user = null): bool
    {
        // check if users are tied to group
        $ignore_user = $ignore_user ?? false;
        if (!$ignore_user && 0 !== count($this->getGroup($group_name)->getUsers())) {
            return false;
        }

        return $this->getDimension('#_GROUP_')
            ->deleteElementByName($group_name)
        ;
    }

    /**
     * @throws \Exception
     *
     * @return Dimension
     */
    public function getUserDimension(): Dimension
    {
        return $this->getDimensionByName('#_USER_');
    }

    /**
     * @throws \Exception
     *
     * @return Dimension
     */
    public function getGroupDimension(): Dimension
    {
        return $this->getDimensionByName('#_GROUP_');
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    public function resetLicenseAssociation(): bool
    {
        $all_accounts = $this->getUserDimension()->getAllBaseElements();

        $accounts = \array_diff($all_accounts, $this->reservedAccounts);

        $values = [];
        $paths = [];

        foreach ($accounts as $account) {
            $values[] = null;
            $paths[] = [$account, 'licenses'];
        }

        return $this->getConnection()
            ->getSystemDatabase()
            ->getCubeByName('#_USER_USER_PROPERTIES')
            ->setBulk($values, $paths)
        ;
    }

    /**
     * @param string $role_name
     *
     * @throws \Exception
     *
     * @return Role
     */
    public function getRole(string $role_name): Role
    {
        /* @noinspection PhpIncompatibleReturnTypeInspection */ // @todo inspection
        return $this->getRoleDimension()
            ->getElementByName($role_name)
            ;
    }

    /**
     * @throws \Exception
     *
     * @return Dimension
     */
    public function getRoleDimension(): Dimension
    {
        return $this->getDimensionByName('#_ROLE_');
    }

    /**
     * @param string $role_name
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function hasRole(string $role_name): bool
    {
        return $this->getDimension('#_ROLE_')
            ->hasElementByName($role_name)
            ;
    }

    /**
     * @param string        $group_name
     * @param null|string[] $roles
     *
     * @throws \Exception
     *
     * @return Group
     */
    public function createGroup(string $group_name, ?array $roles = null): Group
    {
        if ($this->hasGroup($group_name)) {
            throw new \InvalidArgumentException('failed to create group '.$group_name.': group already exist.');
        }

        $group_dim = $this->getGroupDimension();
        $group_dim->addElement($group_name);

        // hack to circumvent invalid state of current object
        // throws Exception if group not exists
        /** @var SystemDatabase $this_as_new_obj */
        $this_as_new_obj = $this->reload();

        return $this_as_new_obj->getGroup($group_name);
    }

    /**
     * @param string        $role_name
     * @param null|string[] $rights_permissions
     *
     * @throws \Exception
     *
     * @return Role
     */
    public function createRole(string $role_name, ?array $rights_permissions = null): Role
    {
        // do not create Role if it already exist
        if ($this->hasRole($role_name)) {
            throw new \InvalidArgumentException('failed to create role '.$role_name.': role already exist.');
        }

        if (!Role::isRightsPermissionsValid($rights_permissions)) {
            throw new \InvalidArgumentException('given rights <-> permission data set not valid');
        }

        $role_dim = $this->getRoleDimension();
        $role_dim->addElement($role_name);

        // hack to circumvent invalid state of current object
        // throws Exception if role not exists
        /** @var SystemDatabase $this_as_new_obj */
        $this_as_new_obj = $this->reload();

        $ret_obj = $this_as_new_obj->getRole($role_name);
        $ret_obj->setRightsPermissions($rights_permissions);

        return $ret_obj;
    }
}
