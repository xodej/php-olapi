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
     * @param Connection $conn
     * @param array      $meta_info
     *
     * @throws \Exception
     */
    public function __construct(Connection $conn, array $meta_info)
    {
        parent::__construct($conn, $meta_info);
        $this->reservedAccounts[] = $this->getConnection()->getConnectionUserName();
    }

    /**
     * @param string $user_name
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
     * @param string      $user_name
     * @param null|array  $group_names
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
}
