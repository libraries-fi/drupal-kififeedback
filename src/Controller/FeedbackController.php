<?php

namespace Drupal\kififeedback\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\kififeedback\ChannelInterface;

// exit('controller loaded');

class FeedbackController extends ControllerBase {


  public function getTitle(ChannelInterface $kififeedback_channel) {
    return  $this->t('Send feedback') . ' - ' . $kififeedback_channel->getName();
  }

  public function addForm(ChannelInterface $kififeedback_channel) {
    $feedback = $this->entityManager()->getStorage('kififeedback')->create([
      'channel' => $kififeedback_channel->id(),
    ]);

    $accessibility = \Drupal::request()->query->has('accessibility');

    return $this->entityFormBuilder()->getForm($feedback, 'default',[
      'description_normal' => $kififeedback_channel->getContent(),
      'description_accessibility' => $kififeedback_channel->getContentAccessibility(),
      'is_accessibility_feedback' => $accessibility
    ]);
  }
}
