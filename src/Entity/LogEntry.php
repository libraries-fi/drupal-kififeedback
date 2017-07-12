<?php

namespace Drupal\kififeedback\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\kififeedback\LogEntryInterface;

/**
 * @ContentEntityType(
 *   id = "kififeedback_log",
 *   label = @Translation("Feedback log"),
 *   base_table = "kififeedback_log",
 *   translatable = FALSE,
 *   entity_keys = {
 *     "id" = "id",
 *     "uid" = "user",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *   },
 * )
 */
class LogEntry extends ContentEntityBase implements LogEntryInterface {
  public function getUser() {
    return $this->get('user')->entity;
  }

  public function getUserId() {
    return $this->get('user')->value;
  }

  public function getAction() {
    return $this->get('action')->value;
  }

  public function label() {
    if ($value = $this->getAction()) {
      $options = $this->get('action')->first()->getPossibleOptions();
      return $options[$value];
    }
  }

  public function getMessage() {
    return $this->get('message')->value;
  }

  public function getMessageFormat() {
    return $this->get('message')->format;
  }

  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  public function getForwardEmail() {
    return $this->get('forward_email')->value;
  }

  public function getForwardUser() {
    return $this->get('forward_user')->entity;
  }

  public function getAssociatedFeedback() {
    return $this->get('feedback')->entity;
  }

  public function setAssociatedFeedback($feedback) {
    $this->set('feedback', $feedback);
  }

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['feedback'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Feedback'))
      ->setSettings(['target_type' => 'kififeedback'])
      ->setRequired(TRUE);

    $fields['user'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User'))
      ->setSettings(['target_type' => 'user'])
      ->setDefaultValueCallback('Drupal\kififeedback\Entity\Feedback::getCurrentUserId')
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'weight' => 0,
      ]);

    $fields['action'] = BaseFieldDefinition::create('list_integer')
      ->setLabel(t('Action'))
      ->setRequired(TRUE)
      ->setSettings(['allowed_values' => [
        self::ACTION_RESPOND => t('Responded'),
        self::ACTION_FORWARD => t('Forwarded'),
      ]]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Timestamp'))
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'weight' => 0,
      ]);

    $fields['message'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Message'))
      ->setSetting('max_length', 10000)
      ->setDisplayOptions('form', [
        'weight' => 0,
        'format' => 'basic_html_without_ckeditor',
        'handler_settings' => [
          'format' => 'basic_html_without_ckeditor'
        ]
      ]);

    $fields['forward_email'] = BaseFieldDefinition::create('email')
      ->setLabel(t('Email address'))
      ->setRequired(FALSE);

    $fields['forward_user'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Recipient'))
      ->setSettings(['target_type' => 'user'])
      ->setRequired(FALSE);

    return $fields;
  }
}
