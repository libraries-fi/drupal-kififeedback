<?php

namespace Drupal\kififeedback;

use Drupal\Core\Entity\ContentEntityInterface;

interface LogEntryInterface extends ContentEntityInterface {
  const ACTION_RESPOND = 1;
  const ACTION_FORWARD = 2;
}
