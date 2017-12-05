<?php
/**
 * Copyright (c) Enalean, 2017. All Rights Reserved.
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

namespace Tuleap\AgileDashboard\RealTime;

use AgileDashboard_KanbanFactory;
use PFUser;
use Tracker_Artifact;
use Tuleap\AgileDashboard\Kanban\RealTime\KanbanArtifactMessageSender;

class RealTimeArtifactMessageController
{
    const EVENT_NAME_ARTIFACT_CREATED   = 'created';
    const EVENT_NAME_ARTIFACT_UPDATED   = 'updated';
    const EVENT_NAME_ARTIFACT_REORDERED = 'reordered';
    /**
     * @var AgileDashboard_KanbanFactory
     */
    private $kanban_factory;
    /**
     * @var KanbanArtifactMessageSender
     */
    private $kanban_artifact_message_sender;

    public function __construct(
        AgileDashboard_KanbanFactory $kanban_factory,
        KanbanArtifactMessageSender $kanban_artifact_message_sender
    ) {
        $this->kanban_factory                 = $kanban_factory;
        $this->kanban_artifact_message_sender = $kanban_artifact_message_sender;
    }

    public function sendMessageForKanban(PFUser $user, Tracker_Artifact $artifact, $event_name_artifact)
    {
        $kanban_id = $this->kanban_factory->getKanbanIdByTrackerId($artifact->getTrackerId());

        if (! $kanban_id) {
            return;
        }

        switch ($event_name_artifact) {
            case self::EVENT_NAME_ARTIFACT_CREATED:
                $this->kanban_artifact_message_sender->sendMessageArtifactCreated(
                    $user,
                    $artifact,
                    $kanban_id
                );
                break;
            case self::EVENT_NAME_ARTIFACT_UPDATED:
                $this->kanban_artifact_message_sender->sendMessageArtifactUpdated(
                    $user,
                    $artifact,
                    $kanban_id
                );

                $this->kanban_artifact_message_sender->sendMessageArtifactMoved(
                    $user,
                    $artifact,
                    $kanban_id
                );
                break;
            case self::EVENT_NAME_ARTIFACT_REORDERED:
                $this->kanban_artifact_message_sender->sendMessageArtifactReordered(
                    $user,
                    $artifact,
                    $kanban_id
                );
                break;
        }
    }
}