<?php

namespace Drupal\kififeedback;

use Drupal\Core\Entity\ContentEntityInterface;

interface LogEntryInterface extends ContentEntityInterface {
  public const ACTION_RESPOND = 1;
  public const ACTION_FORWARD = 2;
}
