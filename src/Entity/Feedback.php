<?php

namespace Drupal\kififeedback\Entity;

use Drupal;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\kififeedback\LogEntryInterface;

/**
 * @ContentEntityType(
 *   id = "kififeedback",
 *   label = @Translation("Feedback"),
 *   handlers = {
 *     "access" = "Drupal\kififeedback\FeedbackAccessControlHandler",
 *     "list_builder" = "Drupal\kififeedback\FeedbackListBuilder",
 *     "form" = {
 *       "edit" = "Drupal\kififeedback\Form\FeedbackAdminForm",
 *       "default" = "Drupal\kififeedback\Form\FeedbackForm",
 *       "delete" = "Drupal\kififeedback\Form\FeedbackDeleteForm",
 *       "forward" = "Drupal\kififeedback\Form\FeedbackForwardForm",
 *     },
 *   },
 *   base_table = "kififeedback",
 *   translatable = FALSE,
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "channel",
 *     "uid" = "user",
 *     "uuid" = "uuid",
 *     "label" = "subject",
 *     "langcode" = "langcode",
 *   },
 *   links = {
 *     "add-form" = "/kififeedback",
 *     "canonical" = "/admin/content/feedback/{kififeedback}/preview",
 *     "collection" = "/admin/content/feedback",
 *     "delete-form" = "/admin/content/feedback/{kififeedback}/delete",
 *     "edit-form" = "/admin/content/feedback/{kififeedback}",
 *     "forward-form" = "/admin/content/feedback/{kififeedback}/forward",
 *   },
 *   permission_granularity = "bundle",
 *   bundle_label = @Translation("Feedback channel"),
 *   bundle_entity_type = "kififeedback_channel",
 * )
 */
class Feedback extends ContentEntityBase {
  public function getChannel() {
    return $this->get('channel')->entity;
  }

  public function getSubject() {
    return $this->get('subject')->value;
  }

  public function setSubject($subject) {
    $this->set('subject', $subject);
  }

  public function getBody() {
    return $this->get('body')->value;
  }

  public function setBody($body) {
    $this->set('body', $body);
  }

  public function getBodyFormat() {
    return $this->get('body')->format;
  }

  public function getEmail() {
    return $this->get('email')->value;
  }

  public function setEmail($email) {
    $this->set('email', $email);
  }

  public function getName() {
    return $this->get('name')->value;
  }

  public function setName($name) {
    $this->set('name', $name);
  }

  public function getUser() {
    return $this->get('user')->entity;
  }

  public function getUserId() {
    return $this->get('user')->value;
  }

  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  public function getActions() {
    return $this->get('actions');
  }

  public function getComment() {
    return $this->get('comment')->value;
  }

  public function setComment($comment) {
    $this->set('comment', $comment);
  }

  public function getLatestAction() {
    $actions = $this->get('actions');
    return $actions->isEmpty() ? NULL : $actions[count($actions) - 1]->entity;
  }

  public function getLatestResponse() {
    foreach ($this->get('actions') as $field) {
      if ($field->entity->getAction() == LogEntryInterface::ACTION_RESPOND) {
        return $field->entity;
      }
    }
  }

  public function addActionToLog($log_entry) {
    $log_entry->setAssociatedFeedback($this);
    $this->get('actions')->appendItem($log_entry);
  }

  public function getResponseDraft() {
    return $this->get('temp_reply')->value;
  }

  public function setResponseDraft($text) {
    $this->set('temp_reply', $text);
  }

  public function setResponseDraftFormat($format) {
    $this->get('temp_reply')->format = $format;
  }

  public function getResponseDraftFormat() {
    return $this->get('temp_reply')->format;
  }

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Author'))
      ->setDescription(t('User who sent the feedback.'))
      ->setSettings(['target_type' => 'user'])
      ->setDefaultValueCallback('Drupal\kififeedback\Entity\Feedback::getCurrentUserId')
      ->setDisplayOptions('view', [
        'type' => 'hidden',
        'format' => 'hidden',
      ]);

    $fields['subject'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Subject'))
      ->setDisplayOptions('form', [
        'weight' => 0,
      ])
      ->setDisplayOptions('view', [
        'type' => 'hidden',
      ]);

    $fields['body'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Message'))
      ->setSetting('max_length', 10000)
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'weight' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
      ]);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'weight' => 0,
      ])
      ->setDisplayOptions('view', [
        'type' => 'hidden',
        'format' => 'hidden',
      ]);

    $fields['email'] = BaseFieldDefinition::create('email')
      ->setLabel(t('Email'))
      ->setRequired(FALSE)
      ->setDisplayOptions('form', [
        'weight' => 0,
      ])
      ->setDisplayOptions('view', [
        'type' => 'hidden',
        'format' => 'hidden',
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Received on'))
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'timestamp',
        'weight' => 0,
      ]);

    $fields['comment'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Internal comment'))
      ->setDescription(t('Additional information for administrators.'))
      ->setSetting('max_length', 10000)
      ->setRequired(FALSE)
      ->setDisplayOptions('form', [
        'weight' => 10,
      ]);

    $fields['actions'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Event log'))
      ->setSettings(['target_type' => 'kififeedback_log'])
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('view', [
        'type' => 'hidden',
        'format' => 'hidden',
      ]);

    $fields['temp_reply'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Temporary reply'))
      ->setDescription(t('Message is stored temporarily until response is sent.'))
      ->setSetting('max_length', 10000)
      ->setRequired(FALSE);

    $fields['captcha'] = BaseFieldDefinition::create('kifiform_captcha')
      ->setLabel(t('Captcha'))
      ->setDisplayOptions('form', [
        'weight' => 1000,
      ])
      ->setComputed(TRUE);

    return $fields;
  }

  /**
   * Not great but this is how it's done in core.
   */
  public static function getCurrentUserId() {
    return [Drupal::currentUser()->id()];
  }
}
