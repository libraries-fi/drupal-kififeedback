<?php

namespace Drupal\kififeedback\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\kififeedback\ChannelInterface;

// exit('controller loaded');

class FeedbackController extends ControllerBase {
  public function addForm(ChannelInterface $kififeedback_channel) {
    $feedback = $this->entityManager()->getStorage('kififeedback')->create([
      'channel' => $kififeedback_channel->id()
    ]);
    return $this->entityFormBuilder()->getForm($feedback);
  }
}
