<?php

namespace Drupal\kififeedback;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;

class ChannelListBuilder extends ConfigEntityListBuilder {
  public function buildHeader() {
    $header = [];
    $header['name'] = $this->t('Name');
    $header['url'] = [
      'data' => $this->t('Url'),
      'class' => [RESPONSIVE_PRIORITY_MEDIUM],
    ];

    return $header + parent::buildHeader();
  }

  public function buildRow(EntityInterface $channel) {
    $row = [];
    $row['name'] = [
      'data' => $channel->label(),
      'class' => ['menu-label']
    ];

    if ($url = $channel->getUrl()) {
      $row['url']['data'] = [
        '#type' => 'link',
        '#title' => $url,
        '#url' => Url::fromUri($url),
      ];
    } else {
      $row['url'] = NULL;
    }

    return $row + parent::buildRow($channel);
  }
}
