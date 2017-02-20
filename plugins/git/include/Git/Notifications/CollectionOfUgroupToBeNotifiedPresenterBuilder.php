<?php
/**
 * Copyright Enalean (c) 2017. All rights reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Tuleap\Git\Notifications;

use GitRepository;
use User_ForgeUserGroupFactory;

class CollectionOfUgroupToBeNotifiedPresenterBuilder
{
    /**
     * @var UgroupsToNotifyDao
     */
    private $dao;

    /**
     * @var User_ForgeUserGroupFactory
     */
    private $ugroup_factory;

    public function __construct(UgroupsToNotifyDao $dao, User_ForgeUserGroupFactory $ugroup_factory)
    {
        $this->dao            = $dao;
        $this->ugroup_factory = $ugroup_factory;
    }

    public function getCollectionOfUgroupToBeNotifiedPresenter(GitRepository $repository)
    {
        $presenters = array();
        foreach ($this->dao->searchUgroupsByRepositoryId($repository->getId()) as $row) {
            $ugroup       = $this->ugroup_factory->instantiateFromRow($row);
            $presenters[] = new UgroupToBeNotifiedPresenter($ugroup);
        }
        $this->sortUgroupAlphabetically($presenters);

        return $presenters;
    }

    private function sortUgroupAlphabetically(&$presenters)
    {
        usort($presenters, function (UgroupToBeNotifiedPresenter $a, UgroupToBeNotifiedPresenter $b) {
            return strnatcasecmp($a->label, $b->label);
        });
    }
}