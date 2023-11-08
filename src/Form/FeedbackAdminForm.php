<?php

namespace Drupal\kififeedback\Form;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\kififeedback\LogEntryInterface;

class FeedbackAdminForm extends FeedbackAdminFormBase {
  public function form(array $form, FormStateInterface $form_state) {
    $feedback = $this->entity;
    $form = parent::form($form, $form_state);

    $form['message'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Response'),
      '#format' => 'basic_html_without_ckeditor',
      '#rows' => 10,
    ];

    if ($message = $feedback->getResponseDraft()) {
      $form['preview_no_send_alert'] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#attributes' => [
          'class' => ['messages', 'messages--warning'],
        ],
        '#weight' => -200,
        '#value' => $this->t('Response is saved as a draft. It has not been sent yet.'),
      ];

      $form['message']['#default_value'] = $message;
      $form['message']['#format'] = $feedback->getResponseDraftFormat();
    } else if ($entry = $feedback->getLatestResponse()) {
      $form['message']['#default_value'] = $entry->getMessage();
      $form['message']['#format'] = $entry->getMessageFormat();
    }

    return $form;
  }

  public function sendMessage(array $form, FormStateInterface $form_state) {
    $feedback = $this->entity;
    $message = $form_state->getValue('message');
    $langcode = $feedback->language()->getId();

    $feedback->setResponseDraft(['value' => NULL, 'format' => NULL]);

    $log_entry = $this->entityTypeManager->getStorage('kififeedback_log')->create([
      'action' => LogEntryInterface::ACTION_RESPOND,
      'message' => $message,
    ]);

    $feedback->addActionToLog($log_entry);

    $this->mailer->mail('kififeedback', 'reply', $feedback->getEmail(), $langcode, [
      'kififeedback' => $feedback,
    ], $this->currentUser());

    $this->messenger()->addStatus($this->t('Response to feedback was submitted.'));
  }

  public function saveDraft(array &$form, FormStateInterface $form_state) {
    $message = $form_state->getValue('message');
    $this->entity->setResponseDraft($message);
  }

  public function saveSuccessMessage(array $form, FormStateInterface $form_state) {
    $this->messenger()->addStatus($this->t('The changes have been saved.'));
  }

  public function validateHasEmail(array $form, FormStateInterface $form_state) {
    $email = $form_state->getValue('email')[0]['value'];
    if (empty($email)) {
      $form_state->setError($form['user_info']['email'], $this->t('Cannot respond without an email address.'));
    }
  }

  public function validateHasResponse(array &$form, FormStateInterface $form_state) {
    $response = $form_state->getValue('message')['value'];

    if (empty($response)) {
      $form_state->setError($form['message']['value'], $this->t('Write a response in order to send response email.'));
    }
  }

  public function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#dropbutton'] = 'save';

    $actions['send'] = $actions['submit'];
    $actions['send']['#value'] = $this->t('Send message');
    $actions['send']['#validate'] = ['::validateForm', '::validateHasEmail', '::validateHasResponse'];

    $actions['submit']['#submit'][] = '::saveSuccessMessage';

    $pos = array_search('::save', $actions['send']['#submit']);
    array_splice($actions['send']['#submit'], $pos, 0, '::sendMessage');

    $pos = array_search('::save', $actions['submit']['#submit']);
    array_splice($actions['submit']['#submit'], $pos, 0, '::saveDraft');

    return $actions;
  }
}
