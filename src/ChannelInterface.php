<?php

namespace Drupal\kififeedback;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

interface ChannelInterface extends ConfigEntityInterface {
  public function getName();
  public function getUrl();
  public function getDescription();
  public function getDescriptionAccessibility();
}
