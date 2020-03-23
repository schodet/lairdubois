<?php

namespace Ladb\CoreBundle\Manager\Event;

use Ladb\CoreBundle\Entity\Event\Event;
use Ladb\CoreBundle\Manager\AbstractPublicationManager;
use Ladb\CoreBundle\Utils\FeedbackableUtils;
use Ladb\CoreBundle\Utils\JoinableUtils;

class EventManager extends AbstractPublicationManager {

	const NAME = 'ladb_core.event_manager';

	/////

	public function publish(Event $event, $flush = true) {

		$event->getUser()->getMeta()->incrementPrivateEventCount(-1);
		$event->getUser()->getMeta()->incrementPublicEventCount();

		parent::publishPublication($event, $flush);
	}

	public function unpublish(Event $event, $flush = true) {

		$event->getUser()->getMeta()->incrementPrivateEventCount(1);
		$event->getUser()->getMeta()->incrementPublicEventCount(-1);

		parent::unpublishPublication($event, $flush);
	}

	public function delete(Event $event, $withWitness = true, $flush = true) {

		// Decrement user event count
		if ($event->getIsDraft()) {
			$event->getUser()->getMeta()->incrementPrivateEventCount(-1);
		} else {
			$event->getUser()->getMeta()->incrementPublicEventCount(-1);
		}

		// Delete joins
		$joinableUtils = $this->get(JoinableUtils::NAME);
		$joinableUtils->deleteJoins($event, false);

		// Delete feedbacks
		$feedbackableUtils = $this->get(FeedbackableUtils::NAME);
		$feedbackableUtils->deleteFeedbacks($event, false);

		parent::deletePublication($event, $withWitness, $flush);
	}

}