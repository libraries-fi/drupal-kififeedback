<?php

namespace Drupal\kififeedback;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Unicode;

class FeedbackListBuilder extends EntityListBuilder {
  protected $channelStorage;
  protected $dateFormatter;

  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('entity.manager')->getStorage('kififeedback_channel'),
      $container->get('date.formatter')
    );
  }

  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, EntityStorageInterface $channel_storage, DateFormatterInterface $date_formatter) {
    parent::__construct($entity_type, $storage);
    $this->channelStorage = $channel_storage;
    $this->dateFormatter = $date_formatter;
  }

  public function buildHeader() {
    $type = $this->storage->getEntityType();

    $header['channel'] = $this->t('Channel');
    $header['subject'] = $this->t('Subject');
    $header['sender'] = $this->t('Sender');
    $header['action'] = $this->t('Lastest action');
    return $header + parent::buildHeader();
  }

  public function buildRow(EntityInterface $feedback) {
    $row['channel'] = $feedback->getChannel()->label();
    $row['title']['data']['subject'] = ['#type' => 'item', '#plain_text' => $feedback->label()];
    $row['title']['data']['snippet'] = [
      '#markup' => Unicode::truncate(strip_tags($feedback->getBody()), 30, true, true),
      '#prefix' => '<em>',
      '#suffix' => '</em>',
    ];

    $row['sender']['data']['time'] = [
      '#type' => 'item',
      '#plain_text' => $this->dateFormatter->format($feedback->getCreatedTime()),
      '#weight' => 10,
    ];

    if ($feedback->getUser()->isAuthenticated()) {
      $row['sender']['data']['name'] = [
        '#type' => 'link',
        '#url' => $feedback->getUser()->urlInfo(),
        '#title' => $feedback->getName(),
      ];

      $row['sender']['data']['email'] = [
        '#plain_text' => new FormattableMarkup(' <@email>', ['@email' => $feedback->getEmail()]),
      ];
    } elseif ($feedback->getEmail()) {
      $row['sender']['data']['name'] = [
        '#type' => 'item',
        '#plain_text' => new FormattableMarkup('@name <@email>', ['@name' => $feedback->getName(), '@email' => $feedback->getEmail()])
      ];
    } else {
      $row['sender']['data']['name'] = [
        '#type' => 'item',
        '#plain_text' => $feedback->getName()
      ];
    }

    if ($action = $feedback->getLatestAction()) {
      $row['action']['data']['type'] = ['#plain_text' => $action->label()];
      $row['action']['data']['user'] = [
        '#type' => 'link',
        '#title' => $action->getUser()->label(),
        '#url' => $action->getUser()->urlInfo(),
        '#prefix' => ' (',
        '#suffix' => ')',
      ];
      $row['action']['data']['time'] = [
        '#type' => 'item',
        '#plain_text' => $this->dateFormatter->format($action->getCreatedTime()),
      ];
    } else {
      $row['action'] = NULL;
    }

    return $row + parent::buildRow($feedback);
  }

  protected function getEntityIds() {
    $query = $this->getStorage()->getQuery()
      ->sort($this->entityType->getKey('id'), 'desc');

    $query->pager($this->limit);
    return $query->execute();
  }
}
